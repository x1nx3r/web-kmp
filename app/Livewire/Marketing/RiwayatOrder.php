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
    public $search = "";
    public $statusFilter = "";
    public $klienFilter = "";
    public $priorityFilter = "";
    public $sortBy = "priority_desc";
    public $perPage = 10;

    // UI State
    public $showDeleteModal = false;
    public $orderToDelete = null;
    public $showCompleteModal = false;
    public $orderToComplete = null;
    public $showCancelModal = false;
    public $orderToCancel = null;
    public $cancelReason = '';
    public $expandedOrders = []; // Track which orders are expanded to show suppliers

    protected $queryString = [
        "search" => ["except" => ""],
        "statusFilter" => ["except" => ""],
        "klienFilter" => ["except" => ""],
        "priorityFilter" => ["except" => ""],
        "sortBy" => ["except" => "priority_desc"],
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
        $this->reset([
            "search",
            "statusFilter",
            "klienFilter",
            "priorityFilter",
            "sortBy",
        ]);
        $this->resetPage();
    }

    public function toggleOrderExpansion($orderId)
    {
        if (in_array($orderId, $this->expandedOrders)) {
            $this->expandedOrders = array_diff($this->expandedOrders, [
                $orderId,
            ]);
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
            if ($order && $order->status === "draft") {
                $order->delete();
                session()->flash("message", "Order berhasil dihapus.");
            } else {
                session()->flash(
                    "error",
                    "Order tidak dapat dihapus. Hanya order dengan status draft yang dapat dihapus.",
                );
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
        if ($order && $order->status === "draft") {
            $order->confirm();
            session()->flash("message", "Order berhasil dikonfirmasi.");
        }
    }

    public function startProcessing($orderId)
    {
        $order = Order::find($orderId);
        if ($order && $order->status === "dikonfirmasi") {
            $order->startProcessing();
            session()->flash("message", "Order berhasil diproses.");
        }
    }

    public function completeOrder($orderId)
    {
        $order = Order::find($orderId);
        if ($order && $order->status === "diproses") {
            $order->complete();
            $this->showCompleteModal = false;
            $this->orderToComplete = null;
            session()->flash("message", "Order berhasil diselesaikan.");
        }
    }

    public function confirmComplete($orderId)
    {
        $this->orderToComplete = $orderId;
        $this->showCompleteModal = true;
    }

    public function cancelComplete()
    {
        $this->showCompleteModal = false;
        $this->orderToComplete = null;
    }

    public function cancelOrder($orderId, $reason = null)
    {
        // Validate cancel reason
        $this->validate([
            'cancelReason' => 'required|string|min:5',
        ], [
            'cancelReason.required' => 'Alasan pembatalan harus diisi.',
            'cancelReason.min' => 'Alasan pembatalan minimal 5 karakter.',
        ]);

        $order = Order::find($orderId);
        if ($order && !in_array($order->status, ["selesai", "dibatalkan"])) {
            $order->cancel($this->cancelReason ?: $reason);
            $this->showCancelModal = false;
            $this->orderToCancel = null;
            $this->cancelReason = '';
            session()->flash("message", "Order berhasil dibatalkan.");
        }
    }

    public function confirmCancel($orderId)
    {
        $this->orderToCancel = $orderId;
        $this->cancelReason = '';
        $this->showCancelModal = true;
    }

    public function cancelCancelation()
    {
        $this->showCancelModal = false;
        $this->orderToCancel = null;
        $this->cancelReason = '';
    }

    private function getOrders()
    {
        return Order::query()
            ->with([
                "klien",
                "creator",
                "orderDetails" => function ($query) {
                    $query->with([
                        "bahanBakuKlien",
                        "orderSuppliers" => function ($supplierQuery) {
                            $supplierQuery
                                ->with("supplier.picPurchasing")
                                ->orderBy("price_rank");
                        },
                        "recommendedSupplier",
                    ]);
                },
            ])
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $q) {
                    $q->where(
                        "no_order",
                        "like",
                        "%" . $this->search . "%",
                    )->orWhereHas("klien", function (Builder $klienQuery) {
                        $klienQuery
                            ->where("nama", "like", "%" . $this->search . "%")
                            ->orWhere(
                                "cabang",
                                "like",
                                "%" . $this->search . "%",
                            );
                    });
                });
            })
            ->when($this->statusFilter, function (Builder $query) {
                $query->where("status", $this->statusFilter);
            })
            ->when($this->klienFilter, function (Builder $query) {
                $query->where("klien_id", $this->klienFilter);
            })
            ->when($this->priorityFilter, function (Builder $query) {
                $query->where("priority", $this->priorityFilter);
            })
            ->when($this->sortBy, function (Builder $query) {
                switch ($this->sortBy) {
                    case "priority_desc":
                        // Sort by priority: mendesak > tinggi > normal > rendah
                        $query
                            ->orderByRaw(
                                "FIELD(priority, 'mendesak', 'tinggi', 'normal', 'rendah')",
                            )
                            ->orderBy("tanggal_order", "desc");
                        break;
                    case "priority_asc":
                        // Sort by priority: rendah > normal > tinggi > mendesak
                        $query
                            ->orderByRaw(
                                "FIELD(priority, 'rendah', 'normal', 'tinggi', 'mendesak')",
                            )
                            ->orderBy("tanggal_order", "desc");
                        break;
                    case "tanggal_desc":
                        $query->orderBy("tanggal_order", "desc");
                        break;
                    case "tanggal_asc":
                        $query->orderBy("tanggal_order", "asc");
                        break;
                    case "total_desc":
                        $query->orderBy("total_amount", "desc");
                        break;
                    case "total_asc":
                        $query->orderBy("total_amount", "asc");
                        break;
                    case "status_asc":
                        $query->orderBy("status", "asc");
                        break;
                    case "status_desc":
                        $query->orderBy("status", "desc");
                        break;
                    default:
                        // Default to priority desc
                        $query
                            ->orderByRaw(
                                "FIELD(priority, 'mendesak', 'tinggi', 'normal', 'rendah')",
                            )
                            ->orderBy("tanggal_order", "desc");
                }
            })
            ->paginate($this->perPage);
    }

    private function getStatusCounts()
    {
        return [
            "all" => Order::count(),
            "draft" => Order::where("status", "draft")->count(),
            "dikonfirmasi" => Order::where("status", "dikonfirmasi")->count(),
            "diproses" => Order::where("status", "diproses")->count(),
            "selesai" => Order::where("status", "selesai")->count(),
            "dibatalkan" => Order::where("status", "dibatalkan")->count(),
        ];
    }

    public function render()
    {
        return view("livewire.marketing.riwayat-order", [
            "orders" => $this->getOrders(),
            "statusCounts" => $this->getStatusCounts(),
            "kliens" => Klien::orderBy("nama")->get(),
        ])->layout("layouts.app");
    }
}
