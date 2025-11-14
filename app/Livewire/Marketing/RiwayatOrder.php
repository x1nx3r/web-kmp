<?php

namespace App\Livewire\Marketing;

use App\Models\Order;
use App\Models\Klien;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;

class RiwayatOrder extends Component
{
    use WithPagination;

    // Search and Filter Properties
    public $search = '';
    public $statusFilter = '';
    public $klienFilter = '';
    public $priorityFilter = '';
    public $sortBy = 'tanggal_desc';
    public $perPage = 10;

    // UI State
    public $showDeleteModal = false;
    public $orderToDelete = null;
    public $expandedOrders = []; // Track which orders are expanded to show suppliers

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'klienFilter' => ['except' => ''],
        'priorityFilter' => ['except' => ''],
        'sortBy' => ['except' => 'tanggal_desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingKlienFilter()
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'statusFilter', 'klienFilter', 'priorityFilter', 'sortBy']);
        $this->resetPage();
    }

    public function toggleOrderExpansion($orderId)
    {
        if (in_array($orderId, $this->expandedOrders)) {
            $this->expandedOrders = array_diff($this->expandedOrders, [$orderId]);
        } else {
            $this->expandedOrders[] = $orderId;
        }
    }

    public function confirmDelete($orderId)
    {
        $this->orderToDelete = $orderId;
        $this->showDeleteModal = true;
    }

    public function deleteOrder()
    {
        if ($this->orderToDelete) {
            $order = Order::find($this->orderToDelete);
            if ($order && $order->status === 'draft') {
                $order->delete();
                session()->flash('message', 'Order berhasil dihapus.');
            } else {
                session()->flash('error', 'Order tidak dapat dihapus. Hanya order dengan status draft yang dapat dihapus.');
            }
        }

        $this->showDeleteModal = false;
        $this->orderToDelete = null;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->orderToDelete = null;
    }

    public function confirmOrder($orderId)
    {
        $order = Order::find($orderId);
        if ($order && $order->status === 'draft') {
            $order->confirm();
            session()->flash('message', 'Order berhasil dikonfirmasi.');
        }
    }

    public function startProcessing($orderId)
    {
        $order = Order::find($orderId);
        if ($order && $order->status === 'dikonfirmasi') {
            $order->startProcessing();
            session()->flash('message', 'Order berhasil diproses.');
        }
    }

    public function completeOrder($orderId)
    {
        $order = Order::find($orderId);
        if ($order && in_array($order->status, ['diproses', 'sebagian_dikirim'])) {
            $order->complete();
            session()->flash('message', 'Order berhasil diselesaikan.');
        }
    }

    public function cancelOrder($orderId, $reason = null)
    {
        $order = Order::find($orderId);
        if ($order && !in_array($order->status, ['selesai', 'dibatalkan'])) {
            $order->cancel($reason);
            session()->flash('message', 'Order berhasil dibatalkan.');
        }
    }

    private function getOrders()
    {
        return Order::query()
            ->with([
                'klien', 
                'creator', 
                'orderDetails' => function($query) {
                    $query->with([
                        'bahanBakuKlien',
                        'orderSuppliers' => function($supplierQuery) {
                            $supplierQuery->with('supplier.picPurchasing')
                                ->orderBy('price_rank');
                        },
                        'recommendedSupplier'
                    ]);
                }
            ])
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $q) {
                    $q->where('no_order', 'like', '%' . $this->search . '%')
                        ->orWhereHas('klien', function (Builder $klienQuery) {
                            $klienQuery->where('nama', 'like', '%' . $this->search . '%')
                                ->orWhere('cabang', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->statusFilter, function (Builder $query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->klienFilter, function (Builder $query) {
                $query->where('klien_id', $this->klienFilter);
            })
            ->when($this->priorityFilter, function (Builder $query) {
                $query->where('priority', $this->priorityFilter);
            })
            ->when($this->sortBy, function (Builder $query) {
                switch ($this->sortBy) {
                    case 'tanggal_desc':
                        $query->orderBy('tanggal_order', 'desc');
                        break;
                    case 'tanggal_asc':
                        $query->orderBy('tanggal_order', 'asc');
                        break;
                    case 'total_desc':
                        $query->orderBy('total_amount', 'desc');
                        break;
                    case 'total_asc':
                        $query->orderBy('total_amount', 'asc');
                        break;
                    case 'status_asc':
                        $query->orderBy('status', 'asc');
                        break;
                    case 'status_desc':
                        $query->orderBy('status', 'desc');
                        break;
                    default:
                        $query->orderBy('created_at', 'desc');
                }
            })
            ->paginate($this->perPage);
    }

    private function getStatusCounts()
    {
        return [
            'all' => Order::count(),
            'draft' => Order::where('status', 'draft')->count(),
            'dikonfirmasi' => Order::where('status', 'dikonfirmasi')->count(),
            'diproses' => Order::where('status', 'diproses')->count(),
            'sebagian_dikirim' => Order::where('status', 'sebagian_dikirim')->count(),
            'selesai' => Order::where('status', 'selesai')->count(),
            'dibatalkan' => Order::where('status', 'dibatalkan')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.marketing.riwayat-order', [
            'orders' => $this->getOrders(),
            'statusCounts' => $this->getStatusCounts(),
            'kliens' => Klien::orderBy('nama')->get(),
        ])->layout('layouts.app');
    }
}
