<?php

namespace App\Livewire\Accounting;

use App\Models\Pengiriman;
use App\Models\ApprovalPembayaran as ApprovalPembayaranModel;
use App\Models\ApprovalHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class ApprovalPembayaran extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $statusFilter = 'all';
    public $activeTab = 'pending'; // Tab for pending approval or approved
    public $selectedPengiriman = null;
    public $showDetailModal = false;
    public $notes = '';
    public $editMode = false;
    public $canManage = false;
    public $approvalHistory = [];
    public $approvalId = null;

    // Refraksi form
    public $refraksiForm = [
        'type' => 'qty',
        'value' => 0,
    ];

    // Forms for other editable fields
    public $buktiPembayaran = [];
    public $existingBuktiPembayaran = []; // Store existing file paths
    public $filesToRemove = []; // Track files marked for removal

    // Piutang form
    public $piutangForm = [
        'catatan_piutang_id' => null,
        'amount' => 0,
        'notes' => '',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'activeTab' => ['except' => 'pending'],
    ];

    public function mount($approvalId = null, $editMode = false)
    {
        $this->approvalId = $approvalId;
        $this->editMode = $editMode;
        $this->canManage = in_array(Auth::user()->role, [
            'staff_accounting', 'manager_accounting', 'direktur', 'superadmin'
        ]);

        // If we have an approvalId from route, load it and show modal
        if ($this->approvalId) {
            $this->showDetail($this->approvalId);
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

    public function gotoPage($page, $pageName = 'page')
    {
        // Use Livewire's setPage method from WithPagination trait
        $this->setPage($page, $pageName);
    }

    public function render()
    {
        $query = ApprovalPembayaranModel::with([
            'pengiriman.purchaseOrder.klien',
            'pengiriman.forecast',
            'pengiriman.purchasing',
            'pengiriman.pengirimanDetails.bahanBakuSupplier.supplier',
            'pengiriman.pengirimanDetails.bahanBakuSupplier',
            'staff',
            'manager'
        ])
        // Filter out approvals with deleted pengiriman (whereHas ensures pengiriman exists)
        ->whereHas('pengiriman');

        // Filter by active tab
        if ($this->activeTab === 'pending') {
            // Pending approval: show items with status pending
            $query->where('status', 'pending');
        } elseif ($this->activeTab === 'approved') {
            // Approved: show only completed items
            $query->where('status', 'completed');
        }

        // Filter by search
        if ($this->search) {
            $query->whereHas('pengiriman', function ($q) {
                $q->where('no_pengiriman', 'like', '%' . $this->search . '%');
            });
        }

        // Filter by status (optional, can be removed or kept for additional filtering)
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $approvals = $query->latest()->paginate(10);

        return view('livewire.accounting.approval-pembayaran', [
            'approvals' => $approvals,
        ]);
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage(); // Reset pagination when switching tabs
    }

    public function showDetail($approvalId)
    {
        $approval = ApprovalPembayaranModel::with([
            'pengiriman.purchaseOrder.klien',
            'pengiriman.forecast',
            'pengiriman.purchasing',
            'pengiriman.details.bahanBakuKlien',
            'pengiriman.invoicePenagihan.approvalPenagihan',
            'catatanPiutang',
            'staff',
            'manager',
            'histories.user'
        ])->findOrFail($approvalId);

        $this->selectedPengiriman = $approval;
        $this->showDetailModal = true;
        $this->notes = '';

        // Load refraksi values from approval pembayaran (not from invoice)
        $this->refraksiForm['type'] = $approval->refraksi_type ?? 'qty';
        $this->refraksiForm['value'] = $approval->refraksi_value ?? 0;

        // Load piutang form values
        $this->buktiPembayaran = []; // Reset for new uploads
        $this->filesToRemove = [];
        if ($approval->bukti_pembayaran) {
            $this->existingBuktiPembayaran = is_array($approval->bukti_pembayaran)
                ? $approval->bukti_pembayaran
                : json_decode($approval->bukti_pembayaran, true) ?? [];
        } else {
            $this->existingBuktiPembayaran = [];
        }

        $this->piutangForm['catatan_piutang_id'] = $approval->catatan_piutang_id;
        $this->piutangForm['amount'] = $approval->piutang_amount ?? 0;
        $this->piutangForm['notes'] = $approval->piutang_notes ?? '';

        // Load history
        $this->approvalHistory = $approval->histories()->orderBy('created_at', 'desc')->get();
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedPengiriman = null;
        $this->notes = '';
        $this->refraksiForm = ['type' => 'qty', 'value' => 0];
        $this->buktiPembayaran = [];
        $this->existingBuktiPembayaran = [];
        $this->filesToRemove = [];
        $this->piutangForm = [
            'catatan_piutang_id' => null,
            'amount' => 0,
            'notes' => '',
        ];
        $this->approvalHistory = [];
    }

    public function approve()
    {
        $user = Auth::user();
        $approval = $this->selectedPengiriman;

        if (!$approval) {
            session()->flash('error', 'Data approval tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            $role = $this->getUserRole($user);

            if (!$role) {
                throw new \Exception('Anda tidak memiliki akses untuk melakukan approval');
            }

            // Check if approval can be processed
            if ($approval->status !== 'pending') {
                throw new \Exception('Approval ini sudah diproses atau tidak dapat diapprove');
            }

            // Langsung complete untuk semua anggota keuangan
            $updateData = [
                'status' => 'completed',
            ];

            // Set approver based on role
            if ($role === 'manager_keuangan') {
                $updateData['manager_id'] = $user->id;
                $updateData['manager_approved_at'] = now();
            } else {
                $updateData['staff_id'] = $user->id;
                $updateData['staff_approved_at'] = now();
            }

            $approval->update($updateData);

            // Save history
            ApprovalHistory::create([
                'approval_type' => 'pembayaran',
                'approval_id' => $approval->id,
                'pengiriman_id' => $approval->pengiriman_id,
                'role' => $role,
                'user_id' => $user->id,
                'action' => 'approved',
                'notes' => $this->notes,
            ]);

            DB::commit();

            session()->flash('message', 'Approval berhasil disimpan');
            $this->closeModal();
            $this->render();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
        }
    }

    public function updateRefraksi()
    {
        if (!$this->selectedPengiriman) {
            session()->flash('error', 'Data approval tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            $approval = $this->selectedPengiriman;
            $pengiriman = $approval->pengiriman;

            // Update refraksi values untuk approval pembayaran
            $approval->refraksi_type = $this->refraksiForm['type'];
            $approval->refraksi_value = floatval($this->refraksiForm['value']);

            // Calculate refraksi untuk pembayaran
            $qtyBeforeRefraksi = $pengiriman->total_qty_kirim;
            $amountBeforeRefraksi = $pengiriman->total_harga_kirim;
            $qtyAfterRefraksi = $qtyBeforeRefraksi;
            $amountAfterRefraksi = $amountBeforeRefraksi;
            $refraksiAmount = 0;

            if ($approval->refraksi_type === 'qty') {
                // Refraksi Qty: potong berdasarkan persentase qty
                $refraksiQty = $qtyBeforeRefraksi * ($approval->refraksi_value / 100);
                $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;

                // Hitung potongan amount berdasarkan qty refraksi
                $hargaPerKg = $amountBeforeRefraksi / $qtyBeforeRefraksi;
                $refraksiAmount = $refraksiQty * $hargaPerKg;
                $amountAfterRefraksi = $amountBeforeRefraksi - $refraksiAmount;
            } elseif ($approval->refraksi_type === 'rupiah') {
                // Refraksi Rupiah: potongan harga per kg
                $refraksiAmount = $approval->refraksi_value * $qtyBeforeRefraksi;
                $amountAfterRefraksi = $amountBeforeRefraksi - $refraksiAmount;
            }

            // Store old values for history
            $oldValues = [
                'refraksi_type' => $approval->refraksi_type,
                'refraksi_value' => $approval->refraksi_value,
                'refraksi_amount' => $approval->refraksi_amount,
                'amount_after_refraksi' => $approval->amount_after_refraksi,
            ];

            // Update approval pembayaran
            $approval->refraksi_amount = $refraksiAmount;
            $approval->qty_before_refraksi = $qtyBeforeRefraksi;
            $approval->qty_after_refraksi = $qtyAfterRefraksi;
            $approval->amount_before_refraksi = $amountBeforeRefraksi;
            $approval->amount_after_refraksi = $amountAfterRefraksi;
            $approval->save();

            // Log history if in edit mode and status is completed
            if ($this->editMode && $approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                $changes = [
                    'field' => 'refraksi',
                    'old' => $oldValues,
                    'new' => [
                        'refraksi_type' => $approval->refraksi_type,
                        'refraksi_value' => $approval->refraksi_value,
                        'refraksi_amount' => $approval->refraksi_amount,
                        'amount_after_refraksi' => $approval->amount_after_refraksi,
                    ],
                ];

                ApprovalHistory::create([
                    'approval_type' => 'pembayaran',
                    'approval_id' => $approval->id,
                    'pengiriman_id' => $approval->pengiriman_id,
                    'role' => $role,
                    'user_id' => $user->id,
                    'action' => 'edited',
                    'notes' => 'Updated refraksi pembayaran: ' .
                              ($approval->refraksi_type === 'qty' ?
                                $approval->refraksi_value . '%' :
                                'Rp ' . number_format($approval->refraksi_value, 0, ',', '.') . '/kg'),
                    'changes' => $changes,
                ]);

                // Send notification
                $this->sendEditNotification($approval, 'refraksi');
            }

            DB::commit();
            session()->flash('message', 'Refraksi Pembayaran berhasil diupdate');

            // Reload data
            $this->showDetail($this->selectedPengiriman->id);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update refraksi: ' . $e->getMessage());
        }
    }
    public function removeExistingFile($index)
    {
        if (!$this->canManage) {
            return;
        }

        if (isset($this->existingBuktiPembayaran[$index])) {
            $this->filesToRemove[] = $this->existingBuktiPembayaran[$index];
            unset($this->existingBuktiPembayaran[$index]);
            $this->existingBuktiPembayaran = array_values($this->existingBuktiPembayaran);
        }
    }
    public function updateBuktiPembayaran()
    {
        if (!$this->canManage) {
            session()->flash('error', 'Anda tidak memiliki akses untuk mengedit');
            return;
        }

        if (!$this->selectedPengiriman) {
            session()->flash('error', 'Data approval tidak ditemukan');
            return;
        }

        // Validate files if provided
        if (!empty($this->buktiPembayaran)) {
            $this->validate([
                'buktiPembayaran.*' => 'file|mimes:jpg,jpeg,png,pdf|max:20480',
            ]);

            // Check total file size (max 20MB)
            $totalSize = 0;
            foreach ($this->buktiPembayaran as $file) {
                $totalSize += $file->getSize();
            }

            if ($totalSize > 20 * 1024 * 1024) {
                session()->flash('error', 'Total ukuran file tidak boleh melebihi 20 MB');
                return;
            }
        }

        DB::beginTransaction();
        try {
            $approval = $this->selectedPengiriman;
            $oldValue = $approval->bukti_pembayaran;

            // Start with existing files (excluding removed ones)
            $finalFiles = $this->existingBuktiPembayaran;

            // Delete files marked for removal
            foreach ($this->filesToRemove as $fileToRemove) {
                try {
                    Storage::disk('public')->delete($fileToRemove);
                } catch (\Exception $e) {
                    // Ignore deletion errors
                }
            }

            // Upload new files if provided
            if (!empty($this->buktiPembayaran)) {
                foreach ($this->buktiPembayaran as $file) {
                    $finalFiles[] = $file->store('bukti-pembayaran', 'public');
                }
            }

            // Check if there are any files left
            if (empty($finalFiles)) {
                session()->flash('error', 'Minimal harus ada 1 file bukti pembayaran');
                DB::rollBack();
                return;
            }

            // Save final file list
            $approval->bukti_pembayaran = json_encode($finalFiles);
            $approval->save();

            $fileCount = count($finalFiles);
            $newValue = "$fileCount file(s) total";

            // Log history if in edit mode
            if ($this->editMode && $approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'pembayaran',
                    'approval_id' => $approval->id,
                    'pengiriman_id' => $approval->pengiriman_id,
                    'role' => $role,
                    'user_id' => $user->id,
                    'action' => 'edited',
                    'notes' => 'Updated bukti pembayaran: ' . $newValue,
                    'changes' => [
                        'field' => 'bukti_pembayaran',
                        'old' => $oldValue ? 'Previous files' : 'No files',
                        'new' => $newValue,
                    ],
                ]);

                $this->sendEditNotification($approval, 'bukti_pembayaran');
            }

            DB::commit();
            session()->flash('message', 'Bukti pembayaran berhasil diupdate');
            $this->buktiPembayaran = []; // Reset after upload
            $this->showDetail($this->selectedPengiriman->id);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update bukti pembayaran: ' . $e->getMessage());
        }
    }

    public function updatePiutang()
    {
        if (!$this->canManage) {
            session()->flash('error', 'Anda tidak memiliki akses untuk mengedit');
            return;
        }

        if (!$this->selectedPengiriman) {
            session()->flash('error', 'Data approval tidak ditemukan');
            return;
        }

        // Validate if catatan_piutang_id is selected, amount is required
        if ($this->piutangForm['catatan_piutang_id'] && $this->piutangForm['amount'] <= 0) {
            session()->flash('error', 'Jumlah pemotongan harus lebih dari 0 jika memilih piutang');
            return;
        }

        // Validate amount doesn't exceed sisa piutang
        if ($this->piutangForm['catatan_piutang_id']) {
            $catatanPiutang = \App\Models\CatatanPiutang::find($this->piutangForm['catatan_piutang_id']);
            if ($catatanPiutang && $this->piutangForm['amount'] > $catatanPiutang->sisa_piutang) {
                session()->flash('error', 'Jumlah pemotongan tidak boleh melebihi sisa piutang (Rp ' . number_format($catatanPiutang->sisa_piutang, 0, ',', '.') . ')');
                return;
            }
        }

        DB::beginTransaction();
        try {
            $approval = $this->selectedPengiriman;
            $oldValues = [
                'catatan_piutang_id' => $approval->catatan_piutang_id,
                'piutang_amount' => $approval->piutang_amount,
                'piutang_notes' => $approval->piutang_notes,
            ];

            $approval->catatan_piutang_id = $this->piutangForm['catatan_piutang_id'];
            $approval->piutang_amount = $this->piutangForm['amount'];
            $approval->piutang_notes = $this->piutangForm['notes'];
            $approval->save();

            // Log history if in edit mode
            if ($this->editMode && $approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'pembayaran',
                    'approval_id' => $approval->id,
                    'pengiriman_id' => $approval->pengiriman_id,
                    'role' => $role,
                    'user_id' => $user->id,
                    'action' => 'edited',
                    'notes' => 'Updated piutang data',
                    'changes' => [
                        'field' => 'piutang',
                        'old' => $oldValues,
                        'new' => [
                            'catatan_piutang_id' => $this->piutangForm['catatan_piutang_id'],
                            'piutang_amount' => $this->piutangForm['amount'],
                            'piutang_notes' => $this->piutangForm['notes'],
                        ],
                    ],
                ]);

                $this->sendEditNotification($approval, 'piutang');
            }

            DB::commit();
            session()->flash('message', 'Data piutang berhasil diupdate');
            $this->showDetail($this->selectedPengiriman->id);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update piutang: ' . $e->getMessage());
        }
    }

    private function sendEditNotification($approval, $fieldName)
    {
        // You can implement notification logic here
        // For example, notify manager or relevant stakeholders about the edit
        // This is a placeholder for future notification implementation
    }

    private function getUserRole($user)
    {
        // Tentukan role berdasarkan user
        // Sesuaikan dengan sistem role yang ada
        if ($user->role === 'direktur') {
            return 'superadmin';
        } elseif ($user->role === 'manager_accounting') {
            return 'manager_keuangan';
        } elseif ($user->role === 'staff_accounting') {
            return 'staff';
        }

        return null;
    }
}
