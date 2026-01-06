<?php

namespace App\Livewire\Marketing;

use App\Models\Order;
use App\Models\Klien;
use App\Models\BahanBakuKlien;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class RiwayatOrder extends Component
{
    use WithPagination;

    // Search and Filter Properties
    public $search = "";
    public $statusFilter = "";
    public $klienFilter = "";
    public $priorityFilter = "";
    public $materialFilter = "";
    public $sortBy = "priority_desc";
    public $perPage = 10;

    // Month/Year Filter
    public $selectedMonth;
    public $selectedYear;
    public $showAllOrders = true;

    // UI State
    public $showDeleteModal = false;
    public $orderToDelete = null;
    public $showCompleteModal = false;
    public $orderToComplete = null;
    public $showCancelModal = false;
    public $orderToCancel = null;
    public $cancelReason = "";
    public $expandedOrders = []; // Track which orders are expanded to show suppliers

    protected $queryString = [
        "search" => ["except" => ""],
        "statusFilter" => ["except" => ""],
        "klienFilter" => ["except" => ""],
        "priorityFilter" => ["except" => ""],
        "materialFilter" => ["except" => ""],
        "sortBy" => ["except" => "priority_desc"],
        "selectedMonth" => ["except" => ""],
        "selectedYear" => ["except" => ""],
        "showAllOrders" => ["except" => true],
    ];

    public function mount()
    {
        // Default to current month/year if not set
        if (empty($this->selectedMonth)) {
            $this->selectedMonth = now()->month;
        }
        if (empty($this->selectedYear)) {
            $this->selectedYear = now()->year;
        }
    }

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

    public function updatingMaterialFilter()
    {
        $this->resetPage();
    }

    public function updatingPriorityFilter()
    {
        $this->resetPage();
    }

    public function updatingSelectedMonth()
    {
        $this->resetPage();
    }

    public function updatingSelectedYear()
    {
        $this->resetPage();
    }

    public function goToPreviousMonth()
    {
        $date = Carbon::createFromDate(
            $this->selectedYear,
            $this->selectedMonth,
            1,
        )->subMonth();
        $this->selectedMonth = $date->month;
        $this->selectedYear = $date->year;
        $this->resetPage();
    }

    public function goToNextMonth()
    {
        $date = Carbon::createFromDate(
            $this->selectedYear,
            $this->selectedMonth,
            1,
        )->addMonth();
        $this->selectedMonth = $date->month;
        $this->selectedYear = $date->year;
        $this->resetPage();
    }

    public function goToCurrentMonth()
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
        $this->showAllOrders = false;
        $this->resetPage();
    }

    public function toggleShowAllOrders()
    {
        $this->showAllOrders = !$this->showAllOrders;
        $this->resetPage();
    }

    public function showAllPO()
    {
        $this->showAllOrders = true;
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset([
            "search",
            "statusFilter",
            "klienFilter",
            "priorityFilter",
            "materialFilter",
            "sortBy",
        ]);
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
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

    /**
     * Delete order (STRICT - only draft orders without forecasts/pengiriman)
     */
    public function confirmDelete($orderId)
    {
        $this->orderToDelete = $orderId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->orderToDelete = null;
    }

    public function deleteOrder()
    {
        try {
            $order = Order::with(['orderDetails', 'forecasts', 'pengiriman', 'consultations', 'winner'])->findOrFail($this->orderToDelete);

            // STRICT VALIDATION: Hanya bisa hapus order dengan kondisi berikut
            
            // 1. Tidak boleh ada forecast terkait (STRICT - cek dulu sebelum status)
            if ($order->forecasts()->count() > 0) {
                session()->flash('error', 'Order tidak dapat dihapus karena sudah memiliki forecasting terkait! Hapus forecasting terlebih dahulu.');
                $this->showDeleteModal = false;
                $this->orderToDelete = null;
                return;
            }

            // 2. Tidak boleh ada pengiriman terkait (STRICT - cek dulu sebelum status)
            if ($order->pengiriman()->count() > 0) {
                session()->flash('error', 'Order tidak dapat dihapus karena sudah memiliki pengiriman terkait! Hapus pengiriman terlebih dahulu.');
                $this->showDeleteModal = false;
                $this->orderToDelete = null;
                return;
            }

            // 3. Authorization: Only Marketing and Direktur can delete
            $user = auth()->user();
            $allowedRoles = ['direktur', 'manager_marketing', 'staff_marketing'];
            
            if (!in_array($user->role, $allowedRoles)) {
                session()->flash('error', 'Anda tidak memiliki akses untuk menghapus order! Hanya Direktur dan tim Marketing yang dapat menghapus order.');
                $this->showDeleteModal = false;
                $this->orderToDelete = null;
                return;
            }

            // 4. For staff marketing, can only delete own orders
            if ($user->role === 'staff_marketing' && $order->created_by !== $user->id) {
                session()->flash('error', 'Anda hanya dapat menghapus order yang Anda buat sendiri!');
                $this->showDeleteModal = false;
                $this->orderToDelete = null;
                return;
            }

            // Begin transaction
            \DB::beginTransaction();

            try {
                $orderNumber = $order->po_number ?? $order->no_order;

                // Delete related data (with soft delete if applicable)
                
                // 1. Delete order winner (if exists)
                if ($order->winner) {
                    $order->winner->delete();
                }

                // 2. Delete consultations
                \App\Models\OrderConsultation::where('order_id', $order->id)->delete();

                // 3. Delete order suppliers (nested in order details)
                foreach ($order->orderDetails as $detail) {
                    \App\Models\OrderSupplier::where('order_detail_id', $detail->id)->delete();
                }

                // 4. Delete order details
                \App\Models\OrderDetail::where('order_id', $order->id)->delete();

                // 5. Delete PO document if exists
                if ($order->po_document_path && \Storage::disk('public')->exists($order->po_document_path)) {
                    \Storage::disk('public')->delete($order->po_document_path);
                }

                // 6. Finally, delete the order itself
                $order->delete();

                \DB::commit();

                \Log::info("Order #{$orderNumber} successfully deleted by user #{$user->id}");

                session()->flash('message', "Order {$orderNumber} berhasil dihapus!");
                $this->showDeleteModal = false;
                $this->orderToDelete = null;

            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error in deleteOrder: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            session()->flash('error', 'Gagal menghapus order: ' . $e->getMessage());
            $this->showDeleteModal = false;
            $this->orderToDelete = null;
        }
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
        $this->validate(
            [
                "cancelReason" => "required|string|min:5",
            ],
            [
                "cancelReason.required" => "Alasan pembatalan harus diisi.",
                "cancelReason.min" => "Alasan pembatalan minimal 5 karakter.",
            ],
        );

        $order = Order::find($orderId);
        if ($order && !in_array($order->status, ["selesai", "dibatalkan"])) {
            $order->cancel($this->cancelReason ?: $reason);
            $this->showCancelModal = false;
            $this->orderToCancel = null;
            $this->cancelReason = "";
            session()->flash("message", "Order berhasil dibatalkan.");
        }
    }

    public function confirmCancel($orderId)
    {
        $this->orderToCancel = $orderId;
        $this->cancelReason = "";
        $this->showCancelModal = true;
    }

    public function cancelCancelation()
    {
        $this->showCancelModal = false;
        $this->orderToCancel = null;
        $this->cancelReason = "";
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
            ->when($this->materialFilter, function (Builder $query) {
                // Filter orders by material name (case-insensitive).
                // We look up the related bahanBakuKlien for each orderDetail and compare LOWER(nama)
                $query->whereHas("orderDetails", function (Builder $q) {
                    $q->whereHas("bahanBakuKlien", function (Builder $bq) {
                        $bq->whereRaw("LOWER(nama) = ?", [
                            strtolower($this->materialFilter),
                        ]);
                    });
                });
            })
            ->when(!$this->showAllOrders && $this->selectedMonth && $this->selectedYear, function (
                Builder $query,
            ) {
                $query
                    ->whereMonth("tanggal_order", $this->selectedMonth)
                    ->whereYear("tanggal_order", $this->selectedYear);
            })
            ->when($this->sortBy, function (Builder $query) {
                switch ($this->sortBy) {
                    case "priority_desc":
                        // Sort by priority: tinggi > sedang > rendah
                        $query
                            ->orderByRaw(
                                "FIELD(priority, 'tinggi', 'sedang', 'rendah')",
                            )
                            ->orderBy("tanggal_order", "desc");
                        break;
                    case "priority_asc":
                        // Sort by priority: rendah > sedang > tinggi
                        $query
                            ->orderByRaw(
                                "FIELD(priority, 'rendah', 'sedang', 'tinggi')",
                            )
                            ->orderBy("tanggal_order", "desc");
                        break;
                    case "client_asc":
                        // Sort by client (klien.nama) A → Z using correlated subquery (case-insensitive)
                        $query
                            ->orderByRaw(
                                "
                            (SELECT LOWER(COALESCE(nama, '')) FROM kliens WHERE kliens.id = orders.klien_id) ASC
                        ",
                            )
                            ->orderBy("tanggal_order", "desc");
                        break;
                    case "client_desc":
                        // Sort by client (klien.nama) Z → A (case-insensitive)
                        $query
                            ->orderByRaw(
                                "
                            (SELECT LOWER(COALESCE(nama, '')) FROM kliens WHERE kliens.id = orders.klien_id) DESC
                        ",
                            )
                            ->orderBy("tanggal_order", "desc");
                        break;
                    case "material_asc":
                        // Sort by material name (minimal alphabetic bahan_baku_klien.nama) A → Z (case-insensitive)
                        // Note: correct table name is `bahan_baku_klien` and we lower the value for case-insensitive ordering.
                        $query
                            ->orderByRaw(
                                "
                            (SELECT LOWER(COALESCE(MIN(b.nama), ''))
                             FROM bahan_baku_klien b
                             JOIN order_details od2 ON od2.bahan_baku_klien_id = b.id
                             WHERE od2.order_id = orders.id
                            ) ASC
                        ",
                            )
                            ->orderBy("tanggal_order", "desc");
                        break;
                    case "material_desc":
                        // Sort by material name (minimal alphabetic) Z → A (case-insensitive)
                        $query
                            ->orderByRaw(
                                "
                            (SELECT LOWER(COALESCE(MIN(b.nama), ''))
                             FROM bahan_baku_klien b
                             JOIN order_details od2 ON od2.bahan_baku_klien_id = b.id
                             WHERE od2.order_id = orders.id
                            ) DESC
                        ",
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
                                "FIELD(priority, 'tinggi', 'sedang', 'rendah')",
                            )
                            ->orderBy("tanggal_order", "desc");
                }
            })
            ->paginate($this->perPage);
    }

    private function getStatusCounts()
    {
        $baseQuery = Order::query()->when(
            $this->selectedMonth && $this->selectedYear,
            function (Builder $query) {
                $query
                    ->whereMonth("tanggal_order", $this->selectedMonth)
                    ->whereYear("tanggal_order", $this->selectedYear);
            },
        );

        return [
            "all" => (clone $baseQuery)->count(),
            "draft" => (clone $baseQuery)->where("status", "draft")->count(),
            "dikonfirmasi" => (clone $baseQuery)
                ->where("status", "dikonfirmasi")
                ->count(),
            "diproses" => (clone $baseQuery)
                ->where("status", "diproses")
                ->count(),
            "selesai" => (clone $baseQuery)
                ->where("status", "selesai")
                ->count(),
            "dibatalkan" => (clone $baseQuery)
                ->where("status", "dibatalkan")
                ->count(),
        ];
    }

    public function getAvailableYears()
    {
        $oldestOrder = Order::orderBy("tanggal_order", "asc")->first();
        $oldestYear = $oldestOrder
            ? $oldestOrder->tanggal_order->year
            : now()->year;
        $currentYear = now()->year;

        return range($currentYear, $oldestYear);
    }

    public function getMonthName($month)
    {
        $months = [
            1 => "Januari",
            2 => "Februari",
            3 => "Maret",
            4 => "April",
            5 => "Mei",
            6 => "Juni",
            7 => "Juli",
            8 => "Agustus",
            9 => "September",
            10 => "Oktober",
            11 => "November",
            12 => "Desember",
        ];

        return $months[$month] ?? "";
    }

    public function render()
    {
        return view("livewire.marketing.riwayat-order", [
            "orders" => $this->getOrders(),
            "statusCounts" => $this->getStatusCounts(),
            "kliens" => Klien::orderBy("nama")->get(),
            // Provide a deduplicated, case-insensitive list of material names.
            // We return only the material names (string) so the select will use the name as the value.
            "materials" => \Illuminate\Support\Facades\DB::table(
                "bahan_baku_klien",
            )
                ->selectRaw("MIN(nama) as nama")
                ->when($this->klienFilter, function ($q) {
                    $q->where("klien_id", $this->klienFilter);
                })
                ->groupBy(\Illuminate\Support\Facades\DB::raw("LOWER(nama)"))
                ->orderByRaw("LOWER(nama)")
                ->pluck("nama"),
            "availableYears" => $this->getAvailableYears(),
            "currentMonthName" => $this->getMonthName($this->selectedMonth),
        ])->layout("layouts.app");
    }
}
