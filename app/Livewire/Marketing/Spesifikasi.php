<?php

namespace App\Livewire\Marketing;

use App\Models\BahanBakuKlien;
use App\Models\Klien;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Spesifikasi extends Component
{
    use WithPagination;

    // Search and filters
    public $search = '';
    public $materialSearch = '';
    public $klienFilter = '';
    public $cabangFilter = '';
    public $statusFilter = '';
    public $sort = 'nama';
    public $direction = 'asc';

    // UI state
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $editingMaterial = null;

    // Form data
    public $editForm = [
        'nama' => '',
        'satuan' => '',
        'spesifikasi' => '',
        'status' => 'aktif',
    ];

    // Delete confirmation
    public $deleteModal = [
        'title' => '',
        'message' => '',
        'materialId' => null,
    ];

    protected $paginationTheme = 'tailwind';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingMaterialSearch()
    {
        $this->resetPage();
    }

    public function updatingKlienFilter()
    {
        $this->resetPage();
        // Reset cabang filter when klien changes
        $this->cabangFilter = '';
    }

    public function updatingCabangFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        // Reset modal states when sorting to prevent conflicts
        $this->resetModalStates();
        
        if ($this->sort === $field) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $field;
            $this->direction = 'asc';
        }
        $this->resetPage();
    }

    public function resetModalStates()
    {
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->editingMaterial = null;
        $this->deleteModal = [
            'title' => '',
            'message' => '',
            'materialId' => null,
        ];
        $this->resetValidation();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->materialSearch = '';
        $this->klienFilter = '';
        $this->cabangFilter = '';
        $this->statusFilter = '';
        $this->sort = 'nama';
        $this->direction = 'asc';
        $this->resetPage();
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function clearMaterialSearch()
    {
        $this->materialSearch = '';
        $this->resetPage();
    }

    // Edit modal methods
    public function editMaterial($materialId)
    {
        try {
            $material = BahanBakuKlien::with('klien')->findOrFail($materialId);
            $this->editingMaterial = $materialId;
            $this->editForm = [
                'nama' => $material->nama,
                'satuan' => $material->satuan,
                'spesifikasi' => $material->spesifikasi ?? '',
                'status' => $material->status,
            ];
            $this->showEditModal = true;
            $this->resetValidation();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuka modal edit: ' . $e->getMessage());
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingMaterial = null;
        $this->resetEditForm();
        $this->resetValidation();
    }

    public function resetEditForm()
    {
        $this->editForm = [
            'nama' => '',
            'satuan' => '',
            'spesifikasi' => '',
            'status' => 'aktif',
        ];
    }

    public function submitEditForm()
    {
        $this->validate([
            'editForm.nama' => 'required|string|max:255',
            'editForm.satuan' => 'required|string|max:50',
            'editForm.spesifikasi' => 'nullable|string',
            'editForm.status' => 'required|in:aktif,non_aktif,pending',
        ], [
            'editForm.nama.required' => 'Nama material wajib diisi',
            'editForm.satuan.required' => 'Satuan material wajib diisi',
        ]);

        try {
            $material = BahanBakuKlien::findOrFail($this->editingMaterial);
            
            $material->update([
                'nama' => $this->editForm['nama'],
                'satuan' => $this->editForm['satuan'],
                'spesifikasi' => $this->editForm['spesifikasi'],
                'status' => $this->editForm['status'],
            ]);

            $this->closeEditModal();
            session()->flash('message', 'Spesifikasi material berhasil diperbarui');
        } catch (\Exception $e) {
            $this->addError('editForm.nama', 'Gagal memperbarui material: ' . $e->getMessage());
        }
    }

    // Delete modal methods
    public function confirmDelete($materialId)
    {
        $material = BahanBakuKlien::with('klien')->findOrFail($materialId);
        $this->deleteModal = [
            'title' => 'Hapus Spesifikasi Material',
            'message' => "Apakah Anda yakin ingin menghapus spesifikasi untuk material \"{$material->nama}\" dari klien \"{$material->klien->nama}\"?",
            'materialId' => $materialId,
        ];
        $this->showDeleteModal = true;
    }

    public function deleteMaterial()
    {
        try {
            if ($this->deleteModal['materialId']) {
                $material = BahanBakuKlien::findOrFail($this->deleteModal['materialId']);
                $material->delete();
                session()->flash('message', 'Spesifikasi material berhasil dihapus');
            }
            $this->cancelDelete();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus spesifikasi: ' . $e->getMessage());
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deleteModal = [
            'title' => '',
            'message' => '',
            'materialId' => null,
        ];
    }

    public function getMaterialsQuery()
    {
        $query = BahanBakuKlien::with(['klien', 'approvedByMarketing']);

        // Apply general search filter (searches across multiple fields)
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nama', 'like', '%' . $this->search . '%')
                  ->orWhere('spesifikasi', 'like', '%' . $this->search . '%')
                  ->orWhere('satuan', 'like', '%' . $this->search . '%')
                  ->orWhereHas('klien', function ($subQ) {
                      $subQ->where('nama', 'like', '%' . $this->search . '%')
                           ->orWhere('cabang', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Apply specific material search filter
        if ($this->materialSearch) {
            $query->where('nama', 'like', '%' . $this->materialSearch . '%');
        }

        // Apply klien filter
        if ($this->klienFilter) {
            $query->where('klien_id', $this->klienFilter);
        }

        // Apply cabang filter (location filter)
        if ($this->cabangFilter) {
            $query->whereHas('klien', function ($subQ) {
                $subQ->where('cabang', 'like', '%' . $this->cabangFilter . '%');
            });
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Apply sorting - Fixed to avoid query conflicts
        if ($this->sort === 'klien') {
            $query->select('bahan_baku_klien.*')
                  ->leftJoin('kliens', 'bahan_baku_klien.klien_id', '=', 'kliens.id')
                  ->orderBy('kliens.nama', $this->direction)
                  ->orderBy('kliens.cabang', $this->direction);
        } elseif ($this->sort === 'cabang') {
            $query->select('bahan_baku_klien.*')
                  ->leftJoin('kliens', 'bahan_baku_klien.klien_id', '=', 'kliens.id')
                  ->orderBy('kliens.cabang', $this->direction)
                  ->orderBy('kliens.nama', $this->direction);
        } elseif ($this->sort === 'material_type') {
            // Sort by material name grouped by type/category
            $query->orderBy('nama', $this->direction);
        } elseif ($this->sort === 'harga_approved') {
            // Handle NULL values properly in sorting
            if ($this->direction === 'asc') {
                $query->orderByRaw('harga_approved IS NULL, harga_approved ASC');
            } else {
                $query->orderByRaw('harga_approved IS NULL, harga_approved DESC');
            }
        } elseif ($this->sort === 'updated_at') {
            $query->orderBy('updated_at', $this->direction);
        } elseif ($this->sort === 'status') {
            $query->orderBy('status', $this->direction);
        } elseif ($this->sort === 'satuan') {
            $query->orderBy('satuan', $this->direction);
        } else {
            $query->orderBy($this->sort, $this->direction);
        }

        return $query;
    }

    public function render()
    {
        $materials = $this->getMaterialsQuery()->paginate(15);
        $kliens = Klien::orderBy('nama')->orderBy('cabang')->get();
        
        // Get unique cabangs (locations) for filter dropdown
        $cabangs = Klien::distinct('cabang')->orderBy('cabang')->pluck('cabang');
        
        // Get unique material names for suggestions
        $materialNames = BahanBakuKlien::distinct('nama')->orderBy('nama')->pluck('nama');
        
        // Get cabangs for selected klien (for dependent dropdown)
        $selectedKlienCabangs = collect();
        if ($this->klienFilter) {
            $selectedKlienCabangs = Klien::where('id', $this->klienFilter)
                ->distinct('cabang')
                ->orderBy('cabang')
                ->pluck('cabang');
        }
        
        // Get status counts for filter badges
        $statusCounts = [
            'all' => BahanBakuKlien::count(),
            'aktif' => BahanBakuKlien::where('status', 'aktif')->count(),
            'pending' => BahanBakuKlien::where('status', 'pending')->count(),
            'non_aktif' => BahanBakuKlien::where('status', 'non_aktif')->count(),
        ];

        return view('livewire.marketing.spesifikasi', [
            'materials' => $materials,
            'kliens' => $kliens,
            'cabangs' => $cabangs,
            'materialNames' => $materialNames,
            'selectedKlienCabangs' => $selectedKlienCabangs,
            'statusCounts' => $statusCounts,
        ]);
    }
}