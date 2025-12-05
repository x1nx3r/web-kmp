<?php

namespace App\Livewire\Accounting;

use App\Models\CatatanPiutangPabrik as CatatanPiutangPabrikModel;
use App\Models\Klien;
use App\Models\InvoicePenagihan;
use App\Models\PembayaranPiutangPabrik;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Carbon\Carbon;

class CatatanPiutangPabrik extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $klienFilter = 'all';
    public $sortField = 'due_date';
    public $sortDirection = 'asc';

    // Modal states
    public $showDetailModal = false;
    public $showPembayaranModal = false;

    // Detail data
    public $detailPiutang;
    public $selectedPiutang;

    // Pembayaran form
    public $tanggal_bayar;
    public $jumlah_bayar;
    public $metode_pembayaran = '';
    public $catatan_pembayaran = '';
    public $bukti_pembayaran;

    protected $queryString = [
        'search' => ['except' => ''],
        'klienFilter' => ['except' => 'all'],
    ];

    public function mount()
    {
        // No need to update statuses for invoice-based piutang
    }

    public function render()
    {
        // Get invoices from penagihan that are past due date
        $query = \App\Models\InvoicePenagihan::with(['pengiriman.klien', 'approvalPenagihan', 'pembayaranPabrik'])
            ->whereHas('approvalPenagihan', function($q) {
                $q->where('status', 'completed');
            })
            ->where('due_date', '<', now());

        // Search
        if ($this->search) {
            $query->where(function($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                  ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('pengiriman.klien', function($q2) {
                      $q2->where('nama', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Filter by klien
        if ($this->klienFilter !== 'all') {
            $query->whereHas('pengiriman', function($q) {
                $q->where('klien_id', $this->klienFilter);
            });
        }

        $piutangs = $query->orderBy('due_date', 'asc')->paginate(10);
        $kliens = Klien::orderBy('nama')->get();

        // Summary statistics
        $allOverdueInvoices = \App\Models\InvoicePenagihan::whereHas('approvalPenagihan', function($q) {
                $q->where('status', 'completed');
            })
            ->where('due_date', '<', now())
            ->get();

        $totalPiutang = $allOverdueInvoices->sum('total_amount');

        // Calculate total dibayar from pembayaran records
        $allOverdueInvoiceIds = $allOverdueInvoices->pluck('id');
        $totalDibayar = PembayaranPiutangPabrik::whereIn('invoice_penagihan_id', $allOverdueInvoiceIds)
            ->sum('jumlah_bayar');

        $totalSisa = $totalPiutang - $totalDibayar;
        $totalJatuhTempo = $allOverdueInvoices->count();

        // Calculate total terlambat (more than 7 days overdue)
        $totalTerlambat = $allOverdueInvoices->filter(function($invoice) {
            return Carbon::parse($invoice->due_date)->diffInDays(now()) > 7;
        })->count();

        return view('livewire.accounting.catatan-piutang-pabrik', [
            'piutangs' => $piutangs,
            'kliens' => $kliens,
            'totalPiutang' => $totalPiutang,
            'totalDibayar' => $totalDibayar,
            'totalSisa' => $totalSisa,
            'totalJatuhTempo' => $totalJatuhTempo,
            'totalTerlambat' => $totalTerlambat,
        ]);
    }

    public function openDetailModal($id)
    {
        $this->detailPiutang = \App\Models\InvoicePenagihan::with([
            'pengiriman.klien',
            'pengiriman.details',
            'approvalPenagihan',
            'pembayaranPabrik.creator'
        ])->findOrFail($id);

        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->detailPiutang = null;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedKlienFilter()
    {
        $this->resetPage();
    }

    public function openPembayaranModal($id)
    {
        $this->selectedPiutang = InvoicePenagihan::with(['pengiriman.klien', 'approvalPenagihan'])
            ->findOrFail($id);

        $this->tanggal_bayar = now()->format('Y-m-d');
        $this->jumlah_bayar = $this->selectedPiutang->total_amount;
        $this->metode_pembayaran = '';
        $this->catatan_pembayaran = '';
        $this->bukti_pembayaran = null;

        $this->showPembayaranModal = true;
        $this->dispatch('pembayaranModalOpened');
    }

    public function closePembayaranModal()
    {
        $this->showPembayaranModal = false;
        $this->selectedPiutang = null;
        $this->resetPembayaranForm();
    }

    private function resetPembayaranForm()
    {
        $this->tanggal_bayar = null;
        $this->jumlah_bayar = null;
        $this->metode_pembayaran = '';
        $this->catatan_pembayaran = '';
        $this->bukti_pembayaran = null;
    }

    public function savePembayaran()
    {
        $this->validate([
            'tanggal_bayar' => 'required|date',
            'jumlah_bayar' => 'required|numeric|min:0.01|max:' . $this->selectedPiutang->total_amount,
            'catatan_pembayaran' => 'nullable|string|max:500',
            'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'invoice_penagihan_id' => $this->selectedPiutang->id,
                'no_pembayaran' => $this->generateNoPembayaran(),
                'tanggal_bayar' => $this->tanggal_bayar,
                'jumlah_bayar' => $this->jumlah_bayar,
                'catatan' => $this->catatan_pembayaran,
                'created_by' => Auth::id(),
            ];

            // Upload bukti pembayaran
            if ($this->bukti_pembayaran) {
                $data['bukti_pembayaran'] = $this->bukti_pembayaran->store('bukti-pembayaran-pabrik', 'public');
            }

            PembayaranPiutangPabrik::create($data);

            // Update payment status invoice
            $totalPaid = PembayaranPiutangPabrik::where('invoice_penagihan_id', $this->selectedPiutang->id)
                ->sum('jumlah_bayar') + $this->jumlah_bayar;

            if ($totalPaid >= $this->selectedPiutang->total_amount) {
                $this->selectedPiutang->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);
            }

            DB::commit();

            session()->flash('message', 'Pembayaran berhasil dicatat');
            $this->closePembayaranModal();
            $this->resetPage();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menyimpan pembayaran: ' . $e->getMessage());
        }
    }

    private function generateNoPembayaran()
    {
        $lastPembayaran = PembayaranPiutangPabrik::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastPembayaran ? (intval(substr($lastPembayaran->no_pembayaran, -4)) + 1) : 1;
        return 'PAY-PABRIK-' . now()->format('Ym') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
