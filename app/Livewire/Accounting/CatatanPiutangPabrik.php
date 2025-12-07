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
    public $statusFilter = 'all'; // all, belum_bayar, cicilan, lunas, overdue
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
        'statusFilter' => ['except' => 'all'],
    ];

    public function mount()
    {
        // No need to update statuses for invoice-based piutang
    }

    public function render()
    {
        // Get all completed invoices (not just overdue)
        $query = \App\Models\InvoicePenagihan::with(['pengiriman.klien', 'approvalPenagihan', 'pembayaranPabrik'])
            ->whereHas('approvalPenagihan', function($q) {
                $q->where('status', 'completed');
            });

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

        // Get all invoices first for filtering by payment status
        $allInvoices = $query->get();

        // Apply status filter
        $filteredInvoices = $allInvoices->filter(function($invoice) {
            $totalPaid = $invoice->pembayaranPabrik->sum('jumlah_bayar');
            $sisaPiutang = $invoice->total_amount - $totalPaid;
            $isOverdue = Carbon::parse($invoice->due_date)->lt(now());

            if ($this->statusFilter === 'belum_bayar') {
                return $totalPaid == 0 && $sisaPiutang > 0;
            } elseif ($this->statusFilter === 'cicilan') {
                return $totalPaid > 0 && $sisaPiutang > 0;
            } elseif ($this->statusFilter === 'lunas') {
                return $sisaPiutang <= 0;
            } elseif ($this->statusFilter === 'overdue') {
                return $isOverdue && $sisaPiutang > 0;
            }
            return true; // 'all'
        });

        // Sort by overdue days (longest overdue first, then by due date for non-overdue)
        $sortedInvoices = $filteredInvoices->sortBy(function($invoice) {
            $dueDate = Carbon::parse($invoice->due_date);
            $totalPaid = $invoice->pembayaranPabrik->sum('jumlah_bayar');
            $sisaPiutang = $invoice->total_amount - $totalPaid;
            $isOverdue = $dueDate->lt(now());

            // Lunas items go to the bottom (highest priority number)
            if ($sisaPiutang <= 0) {
                return PHP_INT_MAX;
            }

            // For overdue items: more days overdue = smaller number = higher in list
            // For non-overdue items: closer to due date = smaller number
            if ($isOverdue) {
                // Overdue: return negative of days overdue (more overdue = more negative = first)
                return -$dueDate->diffInDays(now());
            } else {
                // Not overdue yet: days until due (smaller = closer to due = after overdue but before far future)
                return now()->diffInDays($dueDate);
            }
        });

        // Manual pagination
        $page = request()->get('page', 1);
        $perPage = 10;
        $paginatedInvoices = new \Illuminate\Pagination\LengthAwarePaginator(
            $sortedInvoices->forPage($page, $perPage)->values(),
            $sortedInvoices->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $kliens = Klien::orderBy('nama')->get();

        // Summary statistics - from all completed invoices
        $allCompletedInvoices = \App\Models\InvoicePenagihan::with('pembayaranPabrik')
            ->whereHas('approvalPenagihan', function($q) {
                $q->where('status', 'completed');
            })
            ->get();

        $totalPiutang = $allCompletedInvoices->sum('total_amount');

        // Calculate total dibayar from pembayaran records
        $totalDibayar = 0;
        $totalSisa = 0;
        foreach ($allCompletedInvoices as $inv) {
            $paid = $inv->pembayaranPabrik->sum('jumlah_bayar');
            $totalDibayar += $paid;
            $sisa = $inv->total_amount - $paid;
            if ($sisa > 0) {
                $totalSisa += $sisa;
            }
        }

        // Count overdue invoices with remaining balance
        $overdueInvoices = $allCompletedInvoices->filter(function($invoice) {
            $totalPaid = $invoice->pembayaranPabrik->sum('jumlah_bayar');
            $sisaPiutang = $invoice->total_amount - $totalPaid;
            return Carbon::parse($invoice->due_date)->lt(now()) && $sisaPiutang > 0;
        });

        // Count invoices due within 7 days or already overdue (with remaining balance)
        $totalJatuhTempo = $allCompletedInvoices->filter(function($invoice) {
            $totalPaid = $invoice->pembayaranPabrik->sum('jumlah_bayar');
            $sisaPiutang = $invoice->total_amount - $totalPaid;
            $dueDate = Carbon::parse($invoice->due_date);
            // Include invoices due today or in the next 7 days, plus overdue
            return $dueDate->lte(now()->addDays(7)) && $sisaPiutang > 0;
        })->count();

        // Calculate total terlambat (already past due date)
        $totalTerlambat = $overdueInvoices->count();

        return view('livewire.accounting.catatan-piutang-pabrik', [
            'piutangs' => $paginatedInvoices,
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

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function openPembayaranModal($id)
    {
        $this->selectedPiutang = InvoicePenagihan::with(['pengiriman.klien', 'approvalPenagihan', 'pembayaranPabrik'])
            ->findOrFail($id);

        $totalPaid = $this->selectedPiutang->pembayaranPabrik->sum('jumlah_bayar');
        $sisaPiutang = $this->selectedPiutang->total_amount - $totalPaid;

        $this->tanggal_bayar = now()->format('Y-m-d');
        $this->jumlah_bayar = $sisaPiutang; // Default ke sisa piutang
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
        // Calculate sisa piutang for validation
        $totalPaidBefore = PembayaranPiutangPabrik::where('invoice_penagihan_id', $this->selectedPiutang->id)
            ->sum('jumlah_bayar');
        $sisaPiutang = $this->selectedPiutang->total_amount - $totalPaidBefore;

        $this->validate([
            'tanggal_bayar' => 'required|date',
            'jumlah_bayar' => 'required|numeric|min:0.01|max:' . $sisaPiutang,
            'catatan_pembayaran' => 'nullable|string|max:500',
            'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'jumlah_bayar.max' => 'Jumlah pembayaran tidak boleh melebihi sisa piutang (Rp ' . number_format($sisaPiutang, 0, ',', '.') . ')',
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
                // Lunas - fully paid
                $this->selectedPiutang->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                ]);
            } else {
                // Cicilan - partially paid
                $this->selectedPiutang->update([
                    'payment_status' => 'partial',
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
