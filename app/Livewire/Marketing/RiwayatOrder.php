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

    // Role yang boleh melakukan Create/Update/Delete (CUD).
    // Role lain otomatis read-only.
    private const MANAGE_ROLES = ['direktur', 'marketing'];

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

    // Dihitung sekali di mount(), dipakai di blade supaya tidak
    // memanggil auth()->user()->... berulang kali per baris order.
    public $canManage = false;

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

        $this->canManage = $this->userCanManage();
    }

    /**
     * Cek apakah user yang login boleh melakukan aksi CUD (Create/Update/Delete).
     * Hanya Direktur dan Marketing yang diperbolehkan. Role lain read-only.
     */
    private function userCanManage(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, self::MANAGE_ROLES, true);
    }

    /**
     * Guard bersama untuk setiap method yang mengubah data.
     * Mengembalikan false + flash error kalau user tidak berhak.
     */
    private function authorizeManage(): bool
    {
        if (!$this->userCanManage()) {
            session()->flash(
                'error',
                'Anda tidak memiliki akses untuk melakukan aksi ini. Hanya Direktur dan Marketing yang dapat mengelola order.'
            );
            return false;
        }
        return true;
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
        // Read-only action, semua role boleh lihat detail supplier.
        if (in_array($orderId, $this->expandedOrders)) {
            $this->expandedOrders = array_diff($this->expandedOrders, [
                $orderId,
            ]);
        } else {
            $this->expandedOrders[] = $orderId;
        }
    }

    /**
     * Delete order using soft delete (archives order and all related data)
     */
    public function confirmDelete($orderId)
    {
        if (!$this->authorizeManage()) {
            return;
        }

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
        if (!$this->authorizeManage()) {
            $this->showDeleteModal = false;
            $this->orderToDelete = null;
            return;
        }

        try {
            $order = Order::with(['orderDetails', 'forecasts', 'pengiriman', 'consultations', 'winner'])->findOrFail($this->orderToDelete);

            $user = auth()->user();

            $orderNumber = $order->po_number ?? $order->no_order;

            // Count related data for informative message
            $forecastCount = $order->forecasts()->count();
            $pengirimanCount = $order->pengiriman()->count();

            // Soft-delete the order (cascade soft-delete happens in Order model's boot() method)
            // This archives: forecasts, forecast_details, pengiriman, pengiriman_details,
            // order_details, order_suppliers, consultations, winner
            $order->delete();

            \Log::info("Order #{$orderNumber} successfully deleted by user #{$user->id}", [
                'forecasts_deleted' => $forecastCount,
                'pengiriman_deleted' => $pengirimanCount,
            ]);

            // Build informative success message
            $message = "Order {$orderNumber} berhasil dihapus.";
            if ($forecastCount > 0 || $pengirimanCount > 0) {
                $deletedItems = [];
                if ($forecastCount > 0) {
                    $deletedItems[] = "{$forecastCount} forecast";
                }
                if ($pengirimanCount > 0) {
                    $deletedItems[] = "{$pengirimanCount} pengiriman";
                }
                $message .= " Data terkait (" . implode(", ", $deletedItems) . ") juga dihapus.";
            }

            session()->flash('message', $message);
            $this->showDeleteModal = false;
            $this->orderToDelete = null;

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
        if (!$this->authorizeManage()) {
            return;
        }

        $order = Order::find($orderId);
        if ($order && $order->status === "draft") {
            $order->confirm();
            session()->flash("message", "Order berhasil dikonfirmasi.");
        }
    }

    public function startProcessing($orderId)
    {
        if (!$this->authorizeManage()) {
            return;
        }

        $order = Order::find($orderId);
        if ($order && $order->status === "dikonfirmasi") {
            $order->startProcessing();
            session()->flash("message", "Order berhasil diproses.");
        }
    }

    public function completeOrder($orderId)
    {
        if (!$this->authorizeManage()) {
            $this->showCompleteModal = false;
            $this->orderToComplete = null;
            return;
        }

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
        if (!$this->authorizeManage()) {
            return;
        }

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
        if (!$this->authorizeManage()) {
            return;
        }

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
        if (!$this->authorizeManage()) {
            return;
        }

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
        $orders = Order::query()
            ->with([
                "klien",
                "creator",
                // Dipakai di setiap baris (badge "PO Winner"), harus selalu
                // di-eager-load supaya tidak N+1.
                "winner.user",
                "orderDetails" => function ($query) {
                    $query->with([
                        "bahanBakuKlien",
                        "orderSuppliers" => function ($supplierQuery) {
                            $supplierQuery->orderBy("price_rank");
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
                        $query
                            ->orderByRaw(
                                "FIELD(priority, 'tinggi', 'sedang', 'rendah')",
                            )
                            ->orderBy("tanggal_order", "desc");
                        break;
                    case "priority_asc":
                        $query
                            ->orderByRaw(
                                "FIELD(priority, 'rendah', 'sedang', 'tinggi')",
                            )
                            ->orderBy("tanggal_order", "desc");
                        break;
                    case "client_asc":
                        $query
                            ->orderByRaw(
                                "
                            (SELECT LOWER(COALESCE(nama, '')) FROM kliens WHERE kliens.id = orders.klien_id) ASC
                        ",
                            )
                            ->orderBy("tanggal_order", "desc");
                        break;
                    case "client_desc":
                        $query
                            ->orderByRaw(
                                "
                            (SELECT LOWER(COALESCE(nama, '')) FROM kliens WHERE kliens.id = orders.klien_id) DESC
                        ",
                            )
                            ->orderBy("tanggal_order", "desc");
                        break;
                    case "material_asc":
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
                        $query
                            ->orderByRaw(
                                "FIELD(priority, 'tinggi', 'sedang', 'rendah')",
                            )
                            ->orderBy("tanggal_order", "desc");
                }
            })
            ->paginate($this->perPage);

        if (!empty($this->expandedOrders)) {
            // supplier.picPurchasing DAN bahanBakuSupplier dipakai di expanded
            // view (`$orderSupplier->bahanBakuSupplier->nama`), keduanya
            // harus ikut di-load supaya tidak N+1 saat expand.
            $orders->load([
                "orderDetails.orderSuppliers.supplier.picPurchasing",
                "orderDetails.orderSuppliers.bahanBakuSupplier",
            ]);
        }

        return $orders;
    }

    private function getStatusCounts()
    {
        $baseQuery = Order::query()->when(
            !$this->showAllOrders && $this->selectedMonth && $this->selectedYear,
            function (Builder $query) {
                $query
                    ->whereMonth("tanggal_order", $this->selectedMonth)
                    ->whereYear("tanggal_order", $this->selectedYear);
            },
        );

        $total = (clone $baseQuery)->count();
        $grouped = (clone $baseQuery)
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy("status")
            ->pluck("count", "status");

        return [
            "all" => $total,
            "draft" => $grouped["draft"] ?? 0,
            "dikonfirmasi" => $grouped["dikonfirmasi"] ?? 0,
            "diproses" => $grouped["diproses"] ?? 0,
            "selesai" => $grouped["selesai"] ?? 0,
            "dibatalkan" => $grouped["dibatalkan"] ?? 0,
        ];
    }

    public function getAvailableYears()
    {
        return \Illuminate\Support\Facades\Cache::remember("riwayat-order-available-years", 3600, function () {
            $oldestOrder = Order::orderBy("tanggal_order", "asc")->first();
            $oldestYear = $oldestOrder
                ? $oldestOrder->tanggal_order->year
                : now()->year;
            $currentYear = now()->year;

            return range($currentYear, $oldestYear);
        });
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
            "kliens" => \Illuminate\Support\Facades\Cache::remember("riwayat-order-kliens", 3600, fn() => Klien::orderBy("nama")->get(["id", "nama", "cabang"])),
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