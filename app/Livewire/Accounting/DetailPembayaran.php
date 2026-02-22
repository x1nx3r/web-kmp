<?php

namespace App\Livewire\Accounting;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ApprovalPembayaran;
use App\Models\InvoicePenagihan;
use App\Models\ApprovalHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DetailPembayaran extends Component
{
    use WithFileUploads;

    public $approvalId;
    public $approval;
    public $pengiriman;
    public $invoicePenagihan;
    public $approvalHistory;
    public $editMode = false;
    public $canManage = false;

    // Forms for editable fields
    public $refraksiForm = [
        'type' => 'qty',
        'value' => 0,
    ];
    public $totalHargaBeliForm = 0;
    public $buktiPembayaran = [];
    public $existingBuktiPembayaran = [];
    public $filesToRemove = [];

    // Piutang form
    public $piutangForm = [
        'catatan_piutang_id' => null,
        'amount' => 0,
        'notes' => '',
    ];

    public function mount($approvalId, $editMode = false)
    {
        $this->approvalId = $approvalId;
        $this->editMode = $editMode;
        $this->canManage = in_array(Auth::user()->role, [
            'staff_accounting', 'manager_accounting', 'direktur', 'superadmin'
        ]);
        $this->loadApproval();
    }

    public function loadApproval()
    {
        $this->approval = ApprovalPembayaran::with([
            'pengiriman.pengirimanDetails.bahanBakuSupplier',
            'pengiriman.pengirimanDetails.bahanBakuSupplier.supplier',
            'pengiriman.purchaseOrder',
            'catatanPiutang.supplier',
            'histories' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'histories.user'
        ])->findOrFail($this->approvalId);

        $this->pengiriman = $this->approval->pengiriman;

        // Get invoice penagihan if exists
        $this->invoicePenagihan = InvoicePenagihan::where('pengiriman_id', $this->pengiriman->id)->first();

        $this->approvalHistory = $this->approval->histories;

        // Load form values
        $this->refraksiForm['type'] = $this->approval->refraksi_type ?? 'qty';
        $this->refraksiForm['value'] = $this->approval->refraksi_value ?? 0;
        $this->totalHargaBeliForm = $this->approval->amount_after_refraksi ?? $this->pengiriman->total_harga_kirim;
        $this->buktiPembayaran = []; // Reset for new uploads
        $this->filesToRemove = [];

        // Load existing bukti pembayaran
        if ($this->approval->bukti_pembayaran) {
            $this->existingBuktiPembayaran = is_array($this->approval->bukti_pembayaran)
                ? $this->approval->bukti_pembayaran
                : json_decode($this->approval->bukti_pembayaran, true) ?? [];
        } else {
            $this->existingBuktiPembayaran = [];
        }
        $this->piutangForm['notes'] = $this->approval->piutang_notes ?? '';
    }

    public function render()
    {
        return view('livewire.accounting.detail-pembayaran');
    }

    public function updateRefraksi()
    {
        if (!$this->canManage) {
            session()->flash('error', 'Anda tidak memiliki akses untuk mengedit');
            return;
        }

        DB::beginTransaction();
        try {
            $pengiriman = $this->pengiriman;

            // Store old values for history
            $oldValues = [
                'refraksi_type' => $this->approval->refraksi_type,
                'refraksi_value' => $this->approval->refraksi_value,
                'refraksi_amount' => $this->approval->refraksi_amount,
                'amount_after_refraksi' => $this->approval->amount_after_refraksi,
            ];

            // Update refraksi values
            $this->approval->refraksi_type = $this->refraksiForm['type'];
            $this->approval->refraksi_value = floatval($this->refraksiForm['value']);

            // Calculate refraksi
            $qtyBeforeRefraksi = $pengiriman->total_qty_kirim;
            $amountBeforeRefraksi = $pengiriman->total_harga_kirim;
            $qtyAfterRefraksi = $qtyBeforeRefraksi;
            $amountAfterRefraksi = $amountBeforeRefraksi;
            $refraksiAmount = 0;

            if ($this->approval->refraksi_type === 'qty') {
                $refraksiQty = $qtyBeforeRefraksi * ($this->approval->refraksi_value / 100);
                $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;
                $hargaPerKg = $amountBeforeRefraksi / $qtyBeforeRefraksi;
                $refraksiAmount = $refraksiQty * $hargaPerKg;
                $amountAfterRefraksi = $amountBeforeRefraksi - $refraksiAmount;
            } elseif ($this->approval->refraksi_type === 'rupiah') {
                $refraksiAmount = $this->approval->refraksi_value * $qtyBeforeRefraksi;
                $amountAfterRefraksi = $amountBeforeRefraksi - $refraksiAmount;
            }

            $this->approval->refraksi_amount = $refraksiAmount;
            $this->approval->qty_before_refraksi = $qtyBeforeRefraksi;
            $this->approval->qty_after_refraksi = $qtyAfterRefraksi;
            $this->approval->amount_before_refraksi = $amountBeforeRefraksi;
            $this->approval->amount_after_refraksi = $amountAfterRefraksi;
            $this->approval->save();

            // Log history if in edit mode
            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                $changes = [
                    'field' => 'refraksi',
                    'old' => $oldValues,
                    'new' => [
                        'refraksi_type' => $this->approval->refraksi_type,
                        'refraksi_value' => $this->approval->refraksi_value,
                        'refraksi_amount' => $this->approval->refraksi_amount,
                        'amount_after_refraksi' => $this->approval->amount_after_refraksi,
                    ],
                ];

                ApprovalHistory::create([
                    'approval_type' => 'pembayaran',
                    'approval_id' => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'role' => $role,
                    'user_id' => $user->id,
                    'action' => 'edited',
                    'notes' => 'Updated refraksi pembayaran: ' .
                              ($this->approval->refraksi_type === 'qty' ?
                                $this->approval->refraksi_value . '%' :
                                'Rp ' . number_format($this->approval->refraksi_value, 0, ',', '.') . '/kg'),
                    'changes' => $changes,
                ]);
            }

            DB::commit();
            session()->flash('message', 'Refraksi pembayaran berhasil diupdate');
            $this->loadApproval();
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
            $oldValue = $this->approval->bukti_pembayaran;

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
            $this->approval->bukti_pembayaran = json_encode($finalFiles);
            $this->approval->save();

            $fileCount = count($finalFiles);
            $newValue = "$fileCount file(s) total";

            // Log history if in edit mode
            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'pembayaran',
                    'approval_id' => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
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
            }

            DB::commit();
            session()->flash('message', 'Bukti pembayaran berhasil diupdate');
            $this->buktiPembayaran = []; // Reset after upload
            $this->loadApproval();
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
            $oldValues = [
                'catatan_piutang_id' => $this->approval->catatan_piutang_id,
                'piutang_amount' => $this->approval->piutang_amount,
                'piutang_notes' => $this->approval->piutang_notes,
            ];

            $this->approval->catatan_piutang_id = $this->piutangForm['catatan_piutang_id'];
            $this->approval->piutang_amount = $this->piutangForm['amount'];
            $this->approval->piutang_notes = $this->piutangForm['notes'];
            $this->approval->save();

            // Log history if in edit mode
            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                ApprovalHistory::create([
                    'approval_type' => 'pembayaran',
                    'approval_id' => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
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
            }

            DB::commit();
            session()->flash('message', 'Data piutang berhasil diupdate');
            $this->loadApproval();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update piutang: ' . $e->getMessage());
        }
    }

    public function updateTotalHargaBeli()
    {
        if (!$this->canManage) {
            session()->flash('error', 'Anda tidak memiliki akses untuk mengedit');
            return;
        }

        $this->validate([
            'totalHargaBeliForm' => 'required|numeric|min:0',
        ], [
            'totalHargaBeliForm.required' => 'Total harga beli harus diisi',
            'totalHargaBeliForm.numeric' => 'Total harga beli harus berupa angka',
            'totalHargaBeliForm.min' => 'Total harga beli tidak boleh negatif',
        ]);

        DB::beginTransaction();
        try {
            // Store old value for history
            $oldValue = $this->approval->amount_after_refraksi;

            // Update the total harga beli (amount_after_refraksi)
            $this->approval->amount_after_refraksi = floatval($this->totalHargaBeliForm);
            $this->approval->save();

            // Log history if in edit mode
            if ($this->editMode && $this->approval->status === 'completed') {
                $user = Auth::user();
                $role = $this->getUserRole($user);

                $changes = [
                    'field' => 'total_harga_beli',
                    'old' => number_format($oldValue, 2, ',', '.'),
                    'new' => number_format($this->approval->amount_after_refraksi, 2, ',', '.'),
                ];

                ApprovalHistory::create([
                    'approval_type' => 'pembayaran',
                    'approval_id' => $this->approval->id,
                    'pengiriman_id' => $this->approval->pengiriman_id,
                    'role' => $role,
                    'user_id' => $user->id,
                    'action' => 'edited',
                    'notes' => 'Updated total harga beli dari Rp ' . number_format($oldValue, 0, ',', '.') . 
                              ' menjadi Rp ' . number_format($this->approval->amount_after_refraksi, 0, ',', '.'),
                    'changes' => $changes,
                ]);
            }

            DB::commit();
            session()->flash('message', 'Total harga beli berhasil diupdate');
            $this->loadApproval();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update total harga beli: ' . $e->getMessage());
        }
    }

    private function getUserRole($user)
    {
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
