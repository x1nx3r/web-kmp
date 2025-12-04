<?php

namespace App\Livewire\Marketing;

use App\Models\Penawaran;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class RiwayatPenawaran extends Component
{
    use WithPagination;

    public $search = "";
    public $statusFilter = "";
    public $sortBy = "tanggal_desc";

    // Modal states
    public $showDetailModal = false;
    public $showDeleteModal = false;
    public $showRejectModal = false;
    public $selectedPenawaran = null;
    public $rejectReason = "";

    protected $paginationTheme = "tailwind";

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function getPenawaranQuery()
    {
        $query = Penawaran::with([
            "klien",
            "details.supplier",
            "details.bahanBakuKlien",
            "createdBy",
            "verifiedBy",
        ]);

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where("nomor_penawaran", "like", "%" . $this->search . "%")
                    ->orWhereHas("klien", function ($subQ) {
                        $subQ
                            ->where("nama", "like", "%" . $this->search . "%")
                            ->orWhere(
                                "cabang",
                                "like",
                                "%" . $this->search . "%",
                            );
                    })
                    ->orWhereHas("details.bahanBakuKlien", function ($subQ) {
                        $subQ->where("nama", "like", "%" . $this->search . "%");
                    });
            });
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where("status", $this->statusFilter);
        }

        // Apply sorting
        switch ($this->sortBy) {
            case "tanggal_asc":
                $query->orderBy("tanggal_penawaran", "asc");
                break;
            case "tanggal_desc":
                $query->orderBy("tanggal_penawaran", "desc");
                break;
            case "margin_asc":
                $query->orderBy("margin_percentage", "asc");
                break;
            case "margin_desc":
                $query->orderBy("margin_percentage", "desc");
                break;
            case "total_asc":
                $query->orderBy("total_revenue", "asc");
                break;
            case "total_desc":
                $query->orderBy("total_revenue", "desc");
                break;
            default:
                $query->orderBy("tanggal_penawaran", "desc");
        }

        return $query;
    }

    public function getDummyPenawaran()
    {
        // DEPRECATED: Keep for reference, will be removed
        $allData = [
            [
                "id" => 1,
                "nomor_penawaran" => "PNW-2025-001",
                "tanggal" => "2025-10-03",
                "klien" => [
                    "nama" => "PT Maju Bersama",
                    "cabang" => "Jakarta",
                ],
                "materials" => [
                    [
                        "nama" => "Semen Portland",
                        "quantity" => 100,
                        "satuan" => "sak",
                        "harga_klien" => 95000,
                        "supplier" => "PT Sumber Alam Jaya",
                        "pic" => "Sari Purchasing",
                        "harga_supplier" => 85000,
                    ],
                    [
                        "nama" => "Pasir Halus",
                        "quantity" => 50,
                        "satuan" => "m3",
                        "harga_klien" => 450000,
                        "supplier" => "CV Mitra Bangunan",
                        "pic" => "Ahmad Purchasing",
                        "harga_supplier" => 380000,
                    ],
                ],
                "total_revenue" => 32000000,
                "total_cost" => 27500000,
                "total_profit" => 4500000,
                "margin" => 14.1,
                "status" => "butuh_verifikasi",
                "created_by" => "Admin Marketing",
            ],
            [
                "id" => 2,
                "nomor_penawaran" => "PNW-2025-002",
                "tanggal" => "2025-10-02",
                "klien" => [
                    "nama" => "CV Sejahtera Abadi",
                    "cabang" => "Bandung",
                ],
                "materials" => [
                    [
                        "nama" => "Besi Beton 10mm",
                        "quantity" => 200,
                        "satuan" => "batang",
                        "harga_klien" => 85000,
                        "supplier" => "PT Karya Utama",
                        "pic" => "Sari Purchasing",
                        "harga_supplier" => 75000,
                    ],
                ],
                "total_revenue" => 17000000,
                "total_cost" => 15000000,
                "total_profit" => 2000000,
                "margin" => 11.8,
                "status" => "sudah_diverifikasi",
                "created_by" => "Admin Marketing",
            ],
            [
                "id" => 3,
                "nomor_penawaran" => "PNW-2025-003",
                "tanggal" => "2025-10-01",
                "klien" => [
                    "nama" => "UD Berkah Jaya",
                    "cabang" => "Surabaya",
                ],
                "materials" => [
                    [
                        "nama" => "Cat Tembok Premium",
                        "quantity" => 75,
                        "satuan" => "kaleng",
                        "harga_klien" => 125000,
                        "supplier" => "CV Berkah Sejahtera",
                        "pic" => "Ahmad Purchasing",
                        "harga_supplier" => 105000,
                    ],
                    [
                        "nama" => "Keramik 40x40",
                        "quantity" => 150,
                        "satuan" => "dus",
                        "harga_klien" => 85000,
                        "supplier" => "UD Sentosa Makmur",
                        "pic" => "Dewi Purchasing",
                        "harga_supplier" => 72000,
                    ],
                ],
                "total_revenue" => 22125000,
                "total_cost" => 18675000,
                "total_profit" => 3450000,
                "margin" => 15.6,
                "status" => "butuh_verifikasi",
                "created_by" => "Admin Marketing",
            ],
            [
                "id" => 4,
                "nomor_penawaran" => "PNW-2025-004",
                "tanggal" => "2025-09-30",
                "klien" => [
                    "nama" => "PT Konstruksi Modern",
                    "cabang" => "Jakarta",
                ],
                "materials" => [
                    [
                        "nama" => "Semen Portland",
                        "quantity" => 300,
                        "satuan" => "sak",
                        "harga_klien" => 97000,
                        "supplier" => "PT Sumber Alam Jaya",
                        "pic" => "Sari Purchasing",
                        "harga_supplier" => 86000,
                    ],
                ],
                "total_revenue" => 29100000,
                "total_cost" => 25800000,
                "total_profit" => 3300000,
                "margin" => 11.3,
                "status" => "sudah_diverifikasi",
                "created_by" => "Admin Marketing",
            ],
            [
                "id" => 5,
                "nomor_penawaran" => "PNW-2025-005",
                "tanggal" => "2025-09-28",
                "klien" => [
                    "nama" => "CV Mandiri Sentosa",
                    "cabang" => "Semarang",
                ],
                "materials" => [
                    [
                        "nama" => "Pipa PVC 3 inch",
                        "quantity" => 120,
                        "satuan" => "batang",
                        "harga_klien" => 65000,
                        "supplier" => "PT Global Supply",
                        "pic" => "Dewi Purchasing",
                        "harga_supplier" => 55000,
                    ],
                    [
                        "nama" => "Kabel NYM 2x2.5",
                        "quantity" => 80,
                        "satuan" => "roll",
                        "harga_klien" => 450000,
                        "supplier" => "CV Mandiri Jaya",
                        "pic" => "Ahmad Purchasing",
                        "harga_supplier" => 385000,
                    ],
                ],
                "total_revenue" => 43800000,
                "total_cost" => 37400000,
                "total_profit" => 6400000,
                "margin" => 14.6,
                "status" => "sudah_diverifikasi",
                "created_by" => "Admin Marketing",
            ],
        ];

        // Apply search filter
        if ($this->search) {
            $allData = array_filter($allData, function ($item) {
                return stripos($item["nomor_penawaran"], $this->search) !==
                    false ||
                    stripos($item["klien"]["nama"], $this->search) !== false ||
                    stripos($item["klien"]["cabang"], $this->search) !== false;
            });
        }

        // Apply status filter
        if ($this->statusFilter) {
            $allData = array_filter($allData, function ($item) {
                return $item["status"] === $this->statusFilter;
            });
        }

        // Apply sorting
        usort($allData, function ($a, $b) {
            switch ($this->sortBy) {
                case "tanggal_asc":
                    return strcmp($a["tanggal"], $b["tanggal"]);
                case "tanggal_desc":
                    return strcmp($b["tanggal"], $a["tanggal"]);
                case "nomor_asc":
                    return strcmp($a["nomor_penawaran"], $b["nomor_penawaran"]);
                case "nomor_desc":
                    return strcmp($b["nomor_penawaran"], $a["nomor_penawaran"]);
                default:
                    return 0;
            }
        });

        return $allData;
    }

    // View Detail
    public function viewDetail($penawaranId)
    {
        $this->selectedPenawaran = Penawaran::with([
            "klien",
            "details.supplier",
            "details.bahanBakuKlien",
            "createdBy",
            "verifiedBy",
        ])->findOrFail($penawaranId);

        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedPenawaran = null;
    }

    public function edit($id)
    {
        $penawaran = Penawaran::findOrFail($id);

        // Only draft can be edited
        if ($penawaran->status !== "draft") {
            session()->flash(
                "error",
                "Hanya penawaran dengan status draft yang dapat diedit",
            );
            return;
        }

        // Redirect to edit page or load in form
        return redirect()->route("penawaran.edit", $id);
    }

    public function duplicate($id)
    {
        try {
            $original = Penawaran::with([
                "details.alternativeSuppliers",
            ])->findOrFail($id);

            DB::beginTransaction();

            // Create new penawaran
            $newPenawaran = $original->replicate();
            $newPenawaran->status = "draft";
            $newPenawaran->nomor_penawaran = null; // Will be auto-generated
            $newPenawaran->created_by = auth()->id();
            $newPenawaran->verified_by = null;
            $newPenawaran->verified_at = null;
            $newPenawaran->tanggal_penawaran = now();
            $newPenawaran->tanggal_berlaku_sampai = now()->addDays(30);
            $newPenawaran->save();

            // Duplicate details
            foreach ($original->details as $detail) {
                $newDetail = $detail->replicate();
                $newDetail->penawaran_id = $newPenawaran->id;
                $newDetail->save();

                // Duplicate alternative suppliers
                foreach ($detail->alternativeSuppliers as $alt) {
                    $newAlt = $alt->replicate();
                    $newAlt->penawaran_detail_id = $newDetail->id;
                    $newAlt->save();
                }
            }

            DB::commit();

            session()->flash(
                "message",
                "Penawaran {$newPenawaran->nomor_penawaran} berhasil diduplikasi dari {$original->nomor_penawaran}",
            );
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash(
                "error",
                "Gagal menduplikasi penawaran: " . $e->getMessage(),
            );
        }
    }

    public function confirmDelete($id)
    {
        $this->selectedPenawaran = Penawaran::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        try {
            if (!$this->selectedPenawaran) {
                session()->flash("error", "Penawaran tidak ditemukan");
                return;
            }

            $nomorPenawaran = $this->selectedPenawaran->nomor_penawaran;

            // Only draft can be permanently deleted
            if ($this->selectedPenawaran->status === "draft") {
                $this->selectedPenawaran->forceDelete();
                session()->flash(
                    "message",
                    "Penawaran {$nomorPenawaran} berhasil dihapus",
                );
            } else {
                // Soft delete for non-draft
                $this->selectedPenawaran->delete();
                session()->flash(
                    "message",
                    "Penawaran {$nomorPenawaran} berhasil diarsipkan",
                );
            }

            $this->showDeleteModal = false;
            $this->selectedPenawaran = null;
        } catch (\Exception $e) {
            session()->flash(
                "error",
                "Gagal menghapus penawaran: " . $e->getMessage(),
            );
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->selectedPenawaran = null;
    }

    public function approve($id)
    {
        try {
            // Authorization check: Only direktur can approve
            $user = $this->getFallbackUser();
            if (!$user->canVerifyPenawaran()) {
                session()->flash(
                    "error",
                    "Anda tidak memiliki akses untuk menyetujui penawaran. Hanya Direktur yang dapat menyetujui penawaran.",
                );
                return;
            }

            $penawaran = Penawaran::findOrFail($id);

            if ($penawaran->approve($user)) {
                session()->flash(
                    "message",
                    "Penawaran {$penawaran->nomor_penawaran} berhasil disetujui",
                );
            } else {
                session()->flash(
                    "error",
                    "Penawaran tidak dalam status yang tepat untuk disetujui",
                );
            }
        } catch (\Exception $e) {
            session()->flash(
                "error",
                "Gagal menyetujui penawaran: " . $e->getMessage(),
            );
        }
    }

    public function confirmReject($id)
    {
        $this->selectedPenawaran = Penawaran::findOrFail($id);
        $this->showRejectModal = true;
        $this->rejectReason = "";
    }

    public function reject()
    {
        try {
            // Authorization check: Only direktur can reject
            $user = $this->getFallbackUser();
            if (!$user->canVerifyPenawaran()) {
                session()->flash(
                    "error",
                    "Anda tidak memiliki akses untuk menolak penawaran. Hanya Direktur yang dapat menolak penawaran.",
                );
                return;
            }

            if (!$this->selectedPenawaran) {
                session()->flash("error", "Penawaran tidak ditemukan");
                return;
            }

            if (empty($this->rejectReason)) {
                session()->flash("error", "Alasan penolakan harus diisi");
                return;
            }

            $nomorPenawaran = $this->selectedPenawaran->nomor_penawaran;

            if ($this->selectedPenawaran->reject($user, $this->rejectReason)) {
                session()->flash(
                    "message",
                    "Penawaran {$nomorPenawaran} berhasil ditolak",
                );
            } else {
                session()->flash(
                    "error",
                    "Penawaran tidak dalam status yang tepat untuk ditolak",
                );
            }

            $this->showRejectModal = false;
            $this->selectedPenawaran = null;
            $this->rejectReason = "";
        } catch (\Exception $e) {
            session()->flash(
                "error",
                "Gagal menolak penawaran: " . $e->getMessage(),
            );
        }
    }

    public function cancelReject()
    {
        $this->showRejectModal = false;
        $this->selectedPenawaran = null;
        $this->rejectReason = "";
    }

    /**
     * Return an authenticated user or a safe fallback user for dev/test flows.
     * This is a temporary bypass until the auth system is implemented.
     *
     * @return \App\Models\User
     */
    protected function getFallbackUser()
    {
        $user = auth()->user();
        if ($user) {
            return $user;
        }

        // Prefer a dedicated system user if present
        $fallback = User::where("email", "system@local")->first();
        if ($fallback) {
            return $fallback;
        }

        // Otherwise return the first existing user in the DB
        $first = User::first();
        if ($first) {
            return $first;
        }

        // As a last resort, create a temporary system user (may require fillable attrs)
        try {
            return User::create([
                "name" => "System (dev)",
                "email" => "system@local",
                "password" => bcrypt(bin2hex(random_bytes(8))),
            ]);
        } catch (\Exception $e) {
            // If creation fails (e.g., DB locked in tests), return a non-persisted User instance
            $u = new User();
            $u->id = 0;
            $u->name = "System (ephemeral)";
            $u->email = "system@local";
            return $u;
        }
    }

    public function render()
    {
        $penawaranList = $this->getPenawaranQuery()->paginate(10);

        // Get status counts for filter badges
        $statusCounts = [
            "all" => Penawaran::count(),
            "draft" => Penawaran::where("status", "draft")->count(),
            "menunggu_verifikasi" => Penawaran::where(
                "status",
                "menunggu_verifikasi",
            )->count(),
            "disetujui" => Penawaran::where("status", "disetujui")->count(),
            "ditolak" => Penawaran::where("status", "ditolak")->count(),
            "expired" => Penawaran::where("status", "expired")->count(),
        ];

        // Check if current user can verify penawaran (only direktur)
        $user = $this->getFallbackUser();
        $canVerify = $user->canVerifyPenawaran();

        return view("livewire.marketing.riwayat-penawaran", [
            "penawaranList" => $penawaranList,
            "statusCounts" => $statusCounts,
            "canVerify" => $canVerify,
        ]);
    }
}
