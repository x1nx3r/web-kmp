<?php

namespace App\Livewire\Marketing;

use App\Models\Klien;
use App\Models\BahanBakuKlien;
use App\Models\RiwayatHargaKlien;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditKlien extends Component
{
    public Klien $klien;
    public $uniqueCompanies;

    // Form data
    public $klienForm = [
        'nama' => '',
        'cabang' => '',
        'no_hp' => '',
    ];

    public $materialForm = [
        'nama' => '',
        'satuan' => '',
        'spesifikasi' => '',
        'harga_approved' => '',
        'status' => 'pending',
    ];

    // UI state
    public $showMaterialModal = false;
    public $showDeleteModal = false;
    public $editingMaterial = null;

    // Confirmation modal
    public $deleteModal = [
        'title' => '',
        'message' => '',
        'action' => null,
        'actionParams' => [],
    ];

    public function mount(Klien $klien)
    {
        $this->klien = $klien;
        $this->klien->load([
            'bahanBakuKliens' => function($query) {
                $query->with(['riwayatHarga' => function($q) {
                    $q->latest('tanggal_perubahan')->take(5);
                }]);
            }
        ]);

        $this->klienForm = [
            'nama' => $klien->nama,
            'cabang' => $klien->cabang,
            'no_hp' => $klien->no_hp ?? '',
        ];

        $this->uniqueCompanies = Klien::distinct('nama')->orderBy('nama')->pluck('nama')->toArray();
    }

    public function render()
    {
        return view('livewire.marketing.edit-klien');
    }

    // Klien form methods
    public function updateKlien()
    {
        $this->validate([
            'klienForm.nama' => 'required|string|max:255',
            'klienForm.cabang' => 'required|string|max:255',
            'klienForm.no_hp' => 'nullable|string|max:30',
        ], [
            'klienForm.nama.required' => 'Nama perusahaan wajib diisi',
            'klienForm.cabang.required' => 'Lokasi cabang wajib diisi',
        ]);

        try {
            // Check for duplicates excluding current record
            $exists = Klien::where('nama', $this->klienForm['nama'])
                ->where('cabang', $this->klienForm['cabang'])
                ->where('id', '!=', $this->klien->id)
                ->exists();

            if ($exists) {
                throw new \Exception('Cabang ini sudah terdaftar untuk perusahaan tersebut');
            }

            $this->klien->update($this->klienForm);

            session()->flash('message', 'Cabang berhasil diperbarui');
        } catch (\Exception $e) {
            $this->addError('klienForm.cabang', $e->getMessage());
        }
    }

    // Material modal methods
    public function openMaterialModal()
    {
        $this->resetMaterialForm();
        $this->showMaterialModal = true;
    }

    public function closeMaterialModal()
    {
        $this->showMaterialModal = false;
        $this->editingMaterial = null;
        $this->resetMaterialForm();
        $this->resetValidation();
    }

    public function resetMaterialForm()
    {
        $this->materialForm = [
            'nama' => '',
            'satuan' => '',
            'spesifikasi' => '',
            'harga_approved' => '',
            'status' => 'pending',
        ];
    }

    public function editMaterial($materialId)
    {
        $material = BahanBakuKlien::findOrFail($materialId);
        $this->editingMaterial = $materialId;
        $this->materialForm = [
            'nama' => $material->nama,
            'satuan' => $material->satuan,
            'spesifikasi' => $material->spesifikasi ?? '',
            'harga_approved' => $material->harga_approved ?? '',
            'status' => $material->status,
        ];
        $this->showMaterialModal = true;
    }

    public function submitMaterialForm()
    {
        $this->validate([
            'materialForm.nama' => 'required|string|max:255',
            'materialForm.satuan' => 'required|string|max:50',
            'materialForm.spesifikasi' => 'nullable|string',
            'materialForm.harga_approved' => 'nullable|numeric|min:0',
            'materialForm.status' => 'required|in:aktif,non_aktif,pending',
        ], [
            'materialForm.nama.required' => 'Nama material wajib diisi',
            'materialForm.satuan.required' => 'Satuan material wajib diisi',
            'materialForm.harga_approved.numeric' => 'Harga harus berupa angka',
            'materialForm.harga_approved.min' => 'Harga tidak boleh negatif',
        ]);

        try {
            $data = $this->materialForm;
            $data['klien_id'] = $this->klien->id;

            if ($this->editingMaterial) {
                // Update existing material
                $material = BahanBakuKlien::findOrFail($this->editingMaterial);
                $oldPrice = $material->harga_approved;
                $newPrice = $data['harga_approved'];

                // Handle price approval changes
                if ($newPrice && $newPrice != $oldPrice) {
                    $data['approved_at'] = now();
                    $data['approved_by_marketing'] = Auth::id();

                    // Create price history record
                    RiwayatHargaKlien::createPriceHistory(
                        $material->id,
                        $newPrice,
                        Auth::id(),
                        'Perubahan harga approved'
                    );
                }

                $material->update($data);
                session()->flash('message', 'Material berhasil diperbarui');
            } else {
                // Create new material
                $material = new BahanBakuKlien($data);

                if ($data['harga_approved']) {
                    $material->approved_at = now();
                    $material->approved_by_marketing = Auth::id();
                }

                $material->save();

                // Create initial price history if approved price is set
                if ($data['harga_approved']) {
                    RiwayatHargaKlien::createPriceHistory(
                        $material->id,
                        $data['harga_approved'],
                        Auth::id(),
                        'Harga initial approval'
                    );
                }

                session()->flash('message', 'Material berhasil ditambahkan');
            }

            $this->closeMaterialModal();

            // Reload materials
            $this->klien->load([
                'bahanBakuKliens' => function($query) {
                    $query->with(['riwayatHarga' => function($q) {
                        $q->latest('tanggal_perubahan')->take(5);
                    }]);
                }
            ]);

        } catch (\Exception $e) {
            $this->addError('materialForm.nama', $e->getMessage());
        }
    }

    public function deleteMaterial($materialId, $materialName)
    {
        $this->deleteModal = [
            'title' => 'Hapus Material',
            'message' => "Apakah Anda yakin ingin menghapus material \"{$materialName}\"? Tindakan ini tidak dapat dibatalkan.",
            'action' => 'performMaterialDelete',
            'actionParams' => [$materialId],
        ];
        $this->showDeleteModal = true;
    }

    public function performMaterialDelete($materialId)
    {
        try {
            $material = BahanBakuKlien::findOrFail($materialId);
            $materialName = $material->nama;
            $material->delete();

            session()->flash('message', "Material '{$materialName}' berhasil dihapus");
            $this->closeDeleteModal();

            // Reload materials
            $this->klien->load([
                'bahanBakuKliens' => function($query) {
                    $query->with(['riwayatHarga' => function($q) {
                        $q->latest('tanggal_perubahan')->take(5);
                    }]);
                }
            ]);

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            $this->closeDeleteModal();
        }
    }

    public function deleteKlien()
    {
        $this->deleteModal = [
            'title' => 'Hapus Cabang',
            'message' => 'Apakah Anda yakin ingin menghapus cabang ini? Semua material yang terkait juga akan terhapus. Tindakan ini tidak dapat dibatalkan.',
            'action' => 'performKlienDelete',
            'actionParams' => [],
        ];
        $this->showDeleteModal = true;
    }

    public function performKlienDelete()
    {
        try {
            $companyName = $this->klien->nama;

            $this->klien->delete();

            // Check if this was the only real branch
            $remainingRealBranches = Klien::where('nama', $companyName)
                ->where('cabang', '!=', 'Kantor Pusat')
                ->count();

            if ($remainingRealBranches === 0) {
                // Delete placeholder too
                Klien::where('nama', $companyName)
                    ->where('cabang', 'Kantor Pusat')
                    ->whereNull('no_hp')
                    ->delete();
                $message = 'Cabang dan perusahaan berhasil dihapus';
            } else {
                $message = 'Cabang berhasil dihapus';
            }

            session()->flash('message', $message);
            return redirect()->route('klien.index');

        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            $this->closeDeleteModal();
        }
    }

    // Delete modal methods
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteModal = [
            'title' => '',
            'message' => '',
            'action' => null,
            'actionParams' => [],
        ];
    }

    public function confirmDelete()
    {
        if ($this->deleteModal['action']) {
            $method = $this->deleteModal['action'];
            $params = $this->deleteModal['actionParams'] ?? [];

            if (method_exists($this, $method)) {
                $this->{$method}(...$params);
            }
        }
    }
}