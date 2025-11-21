<?php

namespace App\Livewire\Accounting;

use App\Models\CatatanPiutangPabrik as CatatanPiutangPabrikModel;
use App\Models\Klien;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class CatatanPiutangPabrik extends Component
{
    use WithPagination;

    public $search = '';
    public $klienFilter = 'all';
    public $sortField = 'due_date';
    public $sortDirection = 'asc';

    // Modal states
    public $showDetailModal = false;

    // Detail data
    public $detailPiutang;

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
        $query = \App\Models\InvoicePenagihan::with(['pengiriman.klien', 'approvalPenagihan'])
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
        $totalDibayar = 0; // Bisa ditambahkan jika ada sistem pembayaran
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
        $this->detailPiutang = \App\Models\InvoicePenagihan::with(['pengiriman.klien', 'pengiriman.details', 'approvalPenagihan'])
            ->findOrFail($id);

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
}
