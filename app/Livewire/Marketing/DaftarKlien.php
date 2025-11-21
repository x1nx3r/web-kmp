<?php

namespace App\Livewire\Marketing;

use App\Models\Klien;
use App\Models\KontakKlien;
use App\Models\BahanBakuKlien;
use App\Models\RiwayatHargaKlien;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DaftarKlien extends Component
{
    use WithPagination;

    // Search and filters
    public $search = '';
    public $location = '';
    public $sort = 'nama';
    public $direction = 'asc';

    // UI state
    public $openGroups = [];
    public $openBahanBaku = [];

    // CRUD states
    public $showCompanyModal = false;
    public $showBranchModal = false;
    public $showConfirmModal = false;
    public $editingCompany = null;
    public $editingBranch = null;

    // Form data
    public $companyForm = [
        'nama' => '',
    ];

    public $branchForm = [
        'id' => null,
        'company_type' => 'existing',
        'company_nama' => '',
        'cabang' => '',
        'alamat_lengkap' => '',
        'contact_person_id' => '',
    ];

    public $availableContacts;

    // Confirmation modal
    public $confirmModal = [
        'title' => '',
        'message' => '',
        'warning' => '',
        'confirmText' => 'Hapus',
        'action' => null,
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'location' => ['except' => ''],
        'sort' => ['except' => 'nama'],
        'direction' => ['except' => 'asc'],
        'page' => ['except' => 1],
    ];

    public function mount()
    {
        $this->openGroups = [];
        $this->openBahanBaku = [];
    }

    public function render()
    {
        $query = Klien::query();

        // Apply search
        if ($this->search) {
            $query->search($this->search);
        }

        // Apply location filter
        if ($this->location) {
            $query->where('cabang', 'like', '%' . $this->location . '%');
        }

        // Get unique client names for pagination
        $page = $this->getPage();
        $perPage = 10;

        $uniqueNamesQuery = Klien::query()
            ->select('nama')
            ->distinct();

        if ($this->search) {
            $uniqueNamesQuery->search($this->search);
        }
        if ($this->location) {
            $uniqueNamesQuery->where('cabang', 'like', '%' . $this->location . '%');
        }

        // Apply sorting
        if ($this->sort === 'cabang_count') {
            $uniqueNames = $uniqueNamesQuery
                ->selectRaw('nama, COUNT(*) as branch_count')
                ->groupBy('nama')
                ->orderBy('branch_count', $this->direction)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('nama')
                ->toArray();
        } elseif ($this->sort === 'lokasi') {
            $uniqueNames = $uniqueNamesQuery
                ->selectRaw('nama, MIN(cabang) as first_location')
                ->groupBy('nama')
                ->orderBy('first_location', $this->direction)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('nama')
                ->toArray();
        } else {
            $uniqueNames = $uniqueNamesQuery
                ->orderBy($this->sort === 'updated_at' ? 'updated_at' : 'nama', $this->direction)
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->pluck('nama')
                ->toArray();
        }

        // Get total count for pagination
        $totalUniqueNames = Klien::query()
            ->when($this->search, function($q) {
                $q->search($this->search);
            })
            ->when($this->location, function($q) {
                $q->where('cabang', 'like', '%' . $this->location . '%');
            })
            ->distinct('nama')
            ->count();

        // Get all records for these specific names
        $kliens = collect();
        if (!empty($uniqueNames)) {
            $query = Klien::query()
                ->with([
                    'contactPerson',
                    'bahanBakuKliens' => function($query) {
                        $query->with(['riwayatHarga' => function($q) {
                            $q->latest('tanggal_perubahan')->take(1);
                        }]);
                    }
                ])
                ->whereIn('nama', $uniqueNames);
            
            // Apply appropriate sorting - only sort by actual table columns
            if ($this->sort === 'cabang_count' || $this->sort === 'lokasi') {
                // For aggregated sorts, maintain the order from $uniqueNames array
                $query->orderByRaw('FIELD(nama, "' . implode('","', $uniqueNames) . '")');
            } else {
                $query->orderBy($this->sort === 'updated_at' ? 'updated_at' : 'nama', $this->direction);
            }
            
            $kliens = $query->get();
        }

        // Create custom paginator
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $kliens,
            $totalUniqueNames,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'page'
            ]
        );

        $paginator->appends(request()->only(['search', 'location', 'sort', 'direction']));

        // Get available locations
        $availableLocations = Klien::query()
            ->whereNotNull('cabang')
            ->where('cabang', '!=', '')
            ->distinct()
            ->orderBy('cabang')
            ->pluck('cabang')
            ->unique()
            ->values();

        return view('livewire.marketing.daftar-klien', [
            'kliens' => $paginator,
            'availableLocations' => $availableLocations,
            'uniqueCompanies' => $this->getUniqueCompanies(),
        ]);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedLocation()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sort === $field) {
            $this->direction = $this->direction === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $field;
            $this->direction = 'asc';
        }
        $this->resetPage();
    }

    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->location = '';
        $this->sort = 'nama';
        $this->direction = 'asc';
        $this->resetPage();
    }

    // Group and material toggles
    public function toggleGroup($groupId)
    {
        if (in_array($groupId, $this->openGroups)) {
            $this->openGroups = array_filter($this->openGroups, fn($id) => $id !== $groupId);
        } else {
            $this->openGroups[] = $groupId;
        }
    }

    public function toggleBahanBaku($detailId)
    {
        if (in_array($detailId, $this->openBahanBaku)) {
            $this->openBahanBaku = array_filter($this->openBahanBaku, fn($id) => $id !== $detailId);
        } else {
            $this->openBahanBaku[] = $detailId;
        }
    }

    // Company CRUD
    public function openCompanyModal()
    {
        $this->resetCompanyForm();
        $this->showCompanyModal = true;
    }

    public function closeCompanyModal()
    {
        $this->showCompanyModal = false;
        $this->editingCompany = null;
        $this->resetCompanyForm();
        $this->resetValidation();
    }

    public function resetCompanyForm()
    {
        $this->companyForm = [
            'nama' => '',
        ];
    }

    public function editCompany($nama)
    {
        $this->editingCompany = $nama;
        $this->companyForm['nama'] = $nama;
        $this->showCompanyModal = true;
    }

    public function submitCompanyForm()
    {
        $this->validate([
            'companyForm.nama' => 'required|string|max:255',
        ], [
            'companyForm.nama.required' => 'Nama perusahaan wajib diisi',
            'companyForm.nama.max' => 'Nama perusahaan maksimal 255 karakter',
        ]);

        try {
            if ($this->editingCompany) {
                // Update company
                $updated = Klien::where('nama', $this->editingCompany)->update([
                    'nama' => $this->companyForm['nama'],
                    'updated_at' => now()
                ]);

                if ($updated === 0) {
                    throw new \Exception('Perusahaan tidak ditemukan');
                }

                session()->flash('message', 'Perusahaan berhasil diperbarui');
            } else {
                // Create new company with placeholder
                Klien::create([
                    'nama' => $this->companyForm['nama'],
                    'cabang' => 'Kantor Pusat',
                    'no_hp' => null,
                ]);

                session()->flash('message', 'Perusahaan berhasil ditambahkan');
            }

            $this->closeCompanyModal();
        } catch (\Exception $e) {
            $this->addError('companyForm.nama', $e->getMessage());
        }
    }

    public function deleteCompany($nama)
    {
        $this->confirmModal = [
            'title' => 'Hapus Perusahaan',
            'message' => "Anda yakin ingin menghapus perusahaan \"{$nama}\"?",
            'warning' => 'Semua plant dari perusahaan ini akan ikut terhapus.',
            'confirmText' => 'Hapus',
            'action' => 'performCompanyDelete',
            'actionParams' => [$nama],
        ];
        $this->showConfirmModal = true;
    }

    public function performCompanyDelete($nama)
    {
        try {
            $deleted = Klien::where('nama', $nama)->delete();

            if ($deleted === 0) {
                throw new \Exception('Perusahaan tidak ditemukan');
            }

            session()->flash('message', 'Perusahaan berhasil dihapus');
            $this->closeConfirmModal();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            $this->closeConfirmModal();
        }
    }

    // Branch CRUD
    public function openBranchModal()
    {
        $this->resetBranchForm();
        $this->showBranchModal = true;
    }

    public function closeBranchModal()
    {
        $this->showBranchModal = false;
        $this->editingBranch = null;
        $this->resetBranchForm();
        $this->resetValidation();
    }

    public function resetBranchForm()
    {
        $this->branchForm = [
            'id' => null,
            'company_type' => 'existing',
            'company_nama' => '',
            'cabang' => '',
            'alamat_lengkap' => '',
            'contact_person_id' => '',
        ];
        $this->availableContacts = collect();
    }

    public function updatedBranchFormCompanyNama()
    {
        // Reset contact person when company changes
        $this->branchForm['contact_person_id'] = '';
        $this->updateAvailableContacts();
    }

    public function updateAvailableContacts()
    {
        if (!empty($this->branchForm['company_nama'])) {
            $this->availableContacts = KontakKlien::where('klien_nama', $this->branchForm['company_nama'])
                ->orderBy('nama')
                ->get();
        } else {
            $this->availableContacts = collect();
        }
    }

    public function editBranch($id, $nama, $cabang, $alamat_lengkap = null)
    {
        $this->editingBranch = $id;
        $klien = Klien::find($id);
        $this->branchForm = [
            'id' => $id,
            'company_type' => 'existing',
            'company_nama' => $nama,
            'cabang' => $cabang,
            'alamat_lengkap' => $alamat_lengkap ?? ($klien ? $klien->alamat_lengkap : ''),
            'contact_person_id' => $klien ? $klien->contact_person_id : '',
        ];
        $this->updateAvailableContacts();
        $this->showBranchModal = true;
    }

    public function submitBranchForm()
    {
        $rules = [
            'branchForm.company_nama' => 'required|string|max:255',
            'branchForm.cabang' => 'required|string|max:255',
            'branchForm.alamat_lengkap' => 'nullable|string',
            'branchForm.contact_person_id' => 'nullable|exists:kontak_klien,id',
        ];

        if (!$this->editingBranch) {
            $rules['branchForm.company_type'] = 'required|in:existing,new';
        }

        $this->validate($rules, [
            'branchForm.company_nama.required' => $this->branchForm['company_type'] === 'existing' ? 'Perusahaan wajib dipilih' : 'Nama perusahaan wajib diisi',
            'branchForm.cabang.required' => 'Lokasi plant wajib diisi',
        ]);

        try {
            $data = [
                'nama' => $this->branchForm['company_nama'],
                'cabang' => $this->branchForm['cabang'],
                'alamat_lengkap' => $this->branchForm['alamat_lengkap'] ?: null,
                'contact_person_id' => $this->branchForm['contact_person_id'],
            ];

            if ($this->editingBranch) {
                // Update existing branch
                $klien = Klien::findOrFail($this->branchForm['id']);

                // Check for duplicates excluding current record
                $exists = Klien::where('nama', $data['nama'])
                    ->where('cabang', $data['cabang'])
                    ->where('id', '!=', $klien->id)
                    ->exists();

                if ($exists) {
                    throw new \Exception('Cabang ini sudah terdaftar untuk perusahaan tersebut');
                }

                $klien->update($data);
                session()->flash('message', 'Cabang berhasil diperbarui');
            } else {
                // Create new branch
                $exists = Klien::where('nama', $data['nama'])
                    ->where('cabang', $data['cabang'])
                    ->exists();

                if ($exists) {
                    throw new \Exception('Cabang ini sudah terdaftar untuk perusahaan tersebut');
                }

                // Check if company exists
                $companyExists = Klien::where('nama', $data['nama'])->exists();

                if (!$companyExists) {
                    // Create placeholder first
                    Klien::create([
                        'nama' => $data['nama'],
                        'cabang' => 'Kantor Pusat',
                        'contact_person_id' => null,
                    ]);
                }

                Klien::create($data);
                session()->flash('message', 'Cabang berhasil ditambahkan');
            }

            $this->closeBranchModal();
        } catch (\Exception $e) {
            $this->addError('branchForm.cabang', $e->getMessage());
        }
    }

    public function deleteBranch($id, $displayName)
    {
        $this->confirmModal = [
            'title' => 'Hapus Cabang',
            'message' => "Anda yakin ingin menghapus cabang \"{$displayName}\"?",
            'warning' => 'Data cabang yang dihapus tidak dapat dikembalikan.',
            'confirmText' => 'Hapus',
            'action' => 'performBranchDelete',
            'actionParams' => [$id],
        ];
        $this->showConfirmModal = true;
    }

    public function performBranchDelete($id)
    {
        try {
            $klien = Klien::findOrFail($id);
            $companyName = $klien->nama;

            $klien->delete();

            // Check if this was the only real branch
            $remainingRealBranches = Klien::where('nama', $companyName)
                ->where('cabang', '!=', 'Kantor Pusat')
                ->count();

            if ($remainingRealBranches === 0) {
                // Delete placeholder too
                Klien::where('nama', $companyName)
                    ->where('cabang', 'Kantor Pusat')
                    ->whereNull('contact_person_id')
                    ->delete();
                $message = 'Cabang dan perusahaan berhasil dihapus';
            } else {
                $message = 'Cabang berhasil dihapus';
            }

            session()->flash('message', $message);
            $this->closeConfirmModal();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            $this->closeConfirmModal();
        }
    }

    // Confirmation modal
    public function closeConfirmModal()
    {
        $this->showConfirmModal = false;
        $this->confirmModal = [
            'title' => '',
            'message' => '',
            'warning' => '',
            'confirmText' => 'Hapus',
            'action' => null,
            'actionParams' => [],
        ];
    }

    public function confirmAction()
    {
        if ($this->confirmModal['action']) {
            $method = $this->confirmModal['action'];
            $params = $this->confirmModal['actionParams'] ?? [];

            if (method_exists($this, $method)) {
                $this->{$method}(...$params);
            }
        }
    }

    // Helper methods
    private function getUniqueCompanies()
    {
        return Klien::distinct('nama')
            ->orderBy('nama')
            ->pluck('nama')
            ->toArray();
    }
}