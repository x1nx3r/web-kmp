<?php

namespace App\Livewire\Marketing;

use App\Models\KontakKlien;
use App\Models\Klien;
use Livewire\Component;
use Livewire\WithPagination;

class DaftarKontak extends Component
{
    use WithPagination;

    // Form properties
    public $kontakForm = [
        "nama" => "",
        "klien_nama" => "",
        "nomor_hp" => "",
        "jabatan" => "",
        "catatan" => "",
    ];

    // UI state
    public $showKontakModal = false;
    public $showDeleteModal = false;
    public $editingKontak = null;

    // Search
    public $search = "";
    public $selectedClient = "";
    public $clientOptions = [];

    // Delete confirmation
    public $deleteModal = [
        "title" => "",
        "message" => "",
        "action" => null,
        "actionParams" => [],
    ];

    public function mount($klien = null)
    {
        // Require client parameter - redirect if not provided
        if (!$klien) {
            return redirect()
                ->route("klien.index")
                ->with(
                    "error",
                    "Silakan pilih klien terlebih dahulu untuk mengelola kontak.",
                );
        }

        $this->loadClientOptions();
        $this->selectedClient = $klien;
        $this->kontakForm["klien_nama"] = $klien;
    }

    public function render()
    {
        // Only show contacts for the selected client
        $contacts = KontakKlien::where("klien_nama", $this->selectedClient)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where("nama", "like", "%" . $this->search . "%")
                        ->orWhere("nomor_hp", "like", "%" . $this->search . "%")
                        ->orWhere("jabatan", "like", "%" . $this->search . "%");
                });
            })
            ->orderBy("nama")
            ->paginate(15);

        return view("livewire.marketing.daftar-kontak", [
            "contacts" => $contacts,
        ]);
    }

    public function loadClientOptions()
    {
        $this->clientOptions = Klien::distinct("nama")
            ->orderBy("nama")
            ->pluck("nama")
            ->toArray();
    }

    // Modal methods
    public function openKontakModal()
    {
        $this->resetKontakForm();
        $this->showKontakModal = true;
    }

    public function closeKontakModal()
    {
        $this->showKontakModal = false;
        $this->editingKontak = null;
        $this->resetKontakForm();
        $this->resetValidation();
    }

    public function resetKontakForm()
    {
        $this->kontakForm = [
            "nama" => "",
            // Ensure the hidden klien_nama stays set to the currently selected client
            "klien_nama" => $this->selectedClient ?? "",
            "nomor_hp" => "",
            "jabatan" => "",
            "catatan" => "",
        ];
    }

    public function editKontak($kontakId)
    {
        $kontak = KontakKlien::findOrFail($kontakId);
        $this->editingKontak = $kontakId;
        $this->kontakForm = [
            "nama" => $kontak->nama,
            "klien_nama" => $kontak->klien_nama,
            "nomor_hp" => $kontak->nomor_hp ?? "",
            "jabatan" => $kontak->jabatan ?? "",
            "catatan" => $kontak->catatan ?? "",
        ];
        $this->showKontakModal = true;
    }

    public function submitKontakForm()
    {
        $this->validate(
            [
                "kontakForm.nama" => "required|string|max:255",
                "kontakForm.klien_nama" => "required|string|max:255",
                "kontakForm.nomor_hp" => "nullable|string|max:20",
                "kontakForm.jabatan" => "nullable|string|max:255",
                "kontakForm.catatan" => "nullable|string|max:1000",
            ],
            [
                "kontakForm.nama.required" => "Nama kontak wajib diisi",
                "kontakForm.klien_nama.required" => "Nama klien wajib diisi",
            ],
        );

        try {
            if ($this->editingKontak) {
                // Update existing contact
                $kontak = KontakKlien::findOrFail($this->editingKontak);
                $kontak->update($this->kontakForm);
                session()->flash("message", "Kontak berhasil diperbarui");
            } else {
                // Create new contact
                KontakKlien::create($this->kontakForm);
                session()->flash("message", "Kontak berhasil ditambahkan");
            }

            $this->closeKontakModal();
            $this->loadClientOptions();
        } catch (\Exception $e) {
            $this->addError("kontakForm.nama", $e->getMessage());
        }
    }

    public function deleteKontak($kontakId, $kontakName)
    {
        $this->deleteModal = [
            "title" => "Hapus Kontak",
            "message" => "Apakah Anda yakin ingin menghapus kontak \"{$kontakName}\"? Tindakan ini tidak dapat dibatalkan.",
            "action" => "performKontakDelete",
            "actionParams" => [$kontakId],
        ];
        $this->showDeleteModal = true;
    }

    public function performKontakDelete($kontakId)
    {
        try {
            $kontak = KontakKlien::findOrFail($kontakId);
            $kontakName = $kontak->nama;
            $kontak->delete();

            session()->flash(
                "message",
                "Kontak '{$kontakName}' berhasil dihapus",
            );
            $this->closeDeleteModal();
        } catch (\Exception $e) {
            session()->flash(
                "error",
                "Gagal menghapus kontak: " . $e->getMessage(),
            );
        }
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteModal = [
            "title" => "",
            "message" => "",
            "action" => null,
            "actionParams" => [],
        ];
    }

    // Search methods
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function clearSearch()
    {
        $this->search = "";
        $this->resetPage();
    }
}
