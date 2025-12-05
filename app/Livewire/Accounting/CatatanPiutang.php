<?php

namespace App\Livewire\Accounting;

use App\Models\CatatanPiutang as CatatanPiutangModel;
use App\Models\PembayaranPiutang;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class CatatanPiutang extends Component
{
    use WithPagination, WithFileUploads;

    public $activeTab = 'supplier';
    public $search = '';
    public $statusFilter = 'all';
    public $supplierFilter = 'all';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    // Modal states
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;
    public $showDetailModal = false;
    public $showPembayaranModal = false;

    // Form data
    public $piutangId;
    public $supplier_id;
    public $tanggal_piutang;
    public $tanggal_jatuh_tempo;
    public $jumlah_piutang;
    public $keterangan;

    // Pembayaran form
    public $tanggal_bayar;
    public $jumlah_bayar;
    public $metode_pembayaran = 'transfer';
    public $bukti_pembayaran;
    public $catatan_pembayaran;

    // Detail data
    public $selectedPiutang;
    public $detailPiutang;
    public $deletePiutang;

    protected $queryString = [
        'activeTab' => ['except' => 'supplier'],
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'supplierFilter' => ['except' => 'all'],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    protected $allowedSortFields = [
        'id',
        'tanggal_piutang',
        'tanggal_jatuh_tempo',
        'jumlah_piutang',
        'jumlah_dibayar',
        'sisa_piutang',
        'status',
        'created_at',
    ];

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'tanggal_piutang' => 'required|date',
        'tanggal_jatuh_tempo' => 'nullable|date|after_or_equal:tanggal_piutang',
        'jumlah_piutang' => 'required|numeric|min:0',
        'keterangan' => 'nullable|string',
    ];

    public function render()
    {
        $query = CatatanPiutangModel::with(['supplier', 'creator', 'pembayaran']);

        // Search
        if ($this->search) {
            $query->where(function($q) {
                $q->whereRaw('CAST(id AS CHAR) LIKE ?', ['%' . $this->search . '%'])
                  ->orWhereHas('supplier', function($q2) {
                      $q2->where('nama', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // Filter by status
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Filter by supplier
        if ($this->supplierFilter !== 'all') {
            $query->where('supplier_id', $this->supplierFilter);
        }

        $piutangs = $this->applySorting($query)->paginate(10);
        $suppliers = Supplier::orderBy('nama')->get();

        // Summary statistics
        $totalPiutang = CatatanPiutangModel::sum('jumlah_piutang');
        $totalDibayar = CatatanPiutangModel::sum('jumlah_dibayar');
        $totalSisa = CatatanPiutangModel::sum('sisa_piutang');
        $totalBelumLunas = CatatanPiutangModel::where('status', '!=', 'lunas')->count();

        return view('livewire.accounting.catatan-piutang', [
            'piutangs' => $piutangs,
            'suppliers' => $suppliers,
            'totalPiutang' => $totalPiutang,
            'totalDibayar' => $totalDibayar,
            'totalSisa' => $totalSisa,
            'totalBelumLunas' => $totalBelumLunas,
        ]);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($field !== 'supplier_name' && ! in_array($field, $this->allowedSortFields, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    private function applySorting($query)
    {
        $field = $this->sortField;

        if ($field === 'supplier_name') {
            $query->orderBy(
                Supplier::select('nama')->whereColumn('suppliers.id', 'catatan_piutangs.supplier_id'),
                $this->sortDirection
            );
        } else {
            if (! in_array($field, $this->allowedSortFields, true)) {
                $field = 'created_at';
            }

            $query->orderBy('catatan_piutangs.' . $field, $this->sortDirection);
        }

        return $query;
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
        $this->tanggal_piutang = now()->format('Y-m-d');
    }

    public function openEditModal($id)
    {
        $piutang = CatatanPiutangModel::findOrFail($id);

        $this->piutangId = $piutang->id;
        $this->supplier_id = $piutang->supplier_id;
        $this->tanggal_piutang = $piutang->tanggal_piutang->format('Y-m-d');
        $this->tanggal_jatuh_tempo = $piutang->tanggal_jatuh_tempo ? $piutang->tanggal_jatuh_tempo->format('Y-m-d') : null;
        $this->jumlah_piutang = $piutang->jumlah_piutang;
        $this->keterangan = $piutang->keterangan;

        $this->showEditModal = true;
    }

    public function openDeleteModal($id)
    {
        $this->piutangId = $id;
        $this->deletePiutang = CatatanPiutangModel::with('supplier')->findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function openDetailModal($id)
    {
        $this->detailPiutang = CatatanPiutangModel::with(['supplier', 'pembayaran.creator', 'creator', 'updater'])
            ->findOrFail($id);
        $this->showDetailModal = true;
    }

    public function openPembayaranModal($id)
    {
        $this->selectedPiutang = CatatanPiutangModel::with('supplier')->findOrFail($id);
        $this->piutangId = $id;
        $this->tanggal_bayar = now()->format('Y-m-d');
        $this->jumlah_bayar = $this->selectedPiutang->sisa_piutang;
        $this->showPembayaranModal = true;
        $this->dispatch('pembayaranModalOpened');
    }

    public function create()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $data = [
                'supplier_id' => $this->supplier_id,
                'tanggal_piutang' => $this->tanggal_piutang,
                'tanggal_jatuh_tempo' => $this->tanggal_jatuh_tempo,
                'jumlah_piutang' => $this->jumlah_piutang,
                'jumlah_dibayar' => 0,
                'sisa_piutang' => $this->jumlah_piutang,
                'status' => 'belum_lunas',
                'keterangan' => $this->keterangan,
                'created_by' => Auth::id(),
            ];

            CatatanPiutangModel::create($data);

            DB::commit();
            session()->flash('message', 'Catatan piutang berhasil ditambahkan');
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menambahkan catatan piutang: ' . $e->getMessage());
        }
    }

    public function update()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            $piutang = CatatanPiutangModel::findOrFail($this->piutangId);

            $data = [
                'supplier_id' => $this->supplier_id,
                'tanggal_piutang' => $this->tanggal_piutang,
                'tanggal_jatuh_tempo' => $this->tanggal_jatuh_tempo,
                'jumlah_piutang' => $this->jumlah_piutang,
                'keterangan' => $this->keterangan,
                'updated_by' => Auth::id(),
            ];

            $piutang->update($data);

            // Recalculate sisa piutang after updating jumlah_piutang
            $piutang->refresh();
            $piutang->updateSisaPiutang();

            DB::commit();
            session()->flash('message', 'Catatan piutang berhasil diperbarui');
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal memperbarui catatan piutang: ' . $e->getMessage());
        }
    }

    public function delete()
    {
        DB::beginTransaction();
        try {
            $piutang = CatatanPiutangModel::findOrFail($this->piutangId);

            // Hapus semua pembayaran terkait
            foreach ($piutang->pembayaran as $pembayaran) {
                if ($pembayaran->bukti_pembayaran) {
                    Storage::disk('public')->delete($pembayaran->bukti_pembayaran);
                }
            }

            $piutang->delete();

            DB::commit();
            session()->flash('message', 'Catatan piutang berhasil dihapus');
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menghapus catatan piutang: ' . $e->getMessage());
        }
    }

    public function addPembayaran()
    {
        $this->validate([
            'tanggal_bayar' => 'required|date',
            'jumlah_bayar' => 'required|numeric|min:0|max:' . $this->selectedPiutang->sisa_piutang,
            'metode_pembayaran' => 'required|in:tunai,transfer,cek,giro',
            'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'catatan_pembayaran' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $data = [
                'catatan_piutang_id' => $this->piutangId,
                'no_pembayaran' => PembayaranPiutang::generateNoPembayaran(),
                'tanggal_bayar' => $this->tanggal_bayar,
                'jumlah_bayar' => $this->jumlah_bayar,
                'metode_pembayaran' => $this->metode_pembayaran,
                'catatan' => $this->catatan_pembayaran,
                'created_by' => Auth::id(),
            ];

            // Upload bukti pembayaran
            if ($this->bukti_pembayaran) {
                $data['bukti_pembayaran'] = $this->bukti_pembayaran->store('bukti-pembayaran-piutang', 'public');
            }

            PembayaranPiutang::create($data);

            // Update sisa piutang
            $piutang = CatatanPiutangModel::find($this->piutangId);
            $piutang->updateSisaPiutang();

            DB::commit();
            session()->flash('message', 'Pembayaran berhasil ditambahkan');
            $this->closeModal();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal menambahkan pembayaran: ' . $e->getMessage());
        }
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->reset(['piutangId', 'deletePiutang']);
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->reset(['detailPiutang']);
    }

    public function closePembayaranModal()
    {
        $this->showPembayaranModal = false;
        $this->reset([
            'piutangId',
            'selectedPiutang',
            'tanggal_bayar',
            'jumlah_bayar',
            'metode_pembayaran',
            'bukti_pembayaran',
            'catatan_pembayaran',
        ]);
        $this->metode_pembayaran = 'transfer';
    }

    public function closeModal()
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
        $this->showDetailModal = false;
        $this->showPembayaranModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->reset([
            'piutangId',
            'supplier_id',
            'tanggal_piutang',
            'tanggal_jatuh_tempo',
            'jumlah_piutang',
            'keterangan',
            'tanggal_bayar',
            'jumlah_bayar',
            'metode_pembayaran',
            'bukti_pembayaran',
            'catatan_pembayaran',
            'selectedPiutang',
            'detailPiutang',
            'deletePiutang',
        ]);
        $this->metode_pembayaran = 'transfer';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingSupplierFilter()
    {
        $this->resetPage();
    }
}
