<?php

namespace App\Livewire\Accounting\Traits;

use App\Models\ApprovalHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait WithPaymentApproval
{
    public $refraksiForm = ['type' => 'qty', 'value' => 0];
    public $piutangForm = ['catatan_piutang_id' => null, 'amount' => 0, 'notes' => ''];
    public $expenseForm = ['truk' => 0, 'kuli' => 0, 'fee' => 0, 'others' => []];
    public $buktiPembayaran = [];
    public $existingBuktiPembayaran = [];
    public $filesToRemove = [];

    protected function ensureCanManage(): bool
    {
        if (!$this->canManage) {
            session()->flash('error', 'Anda tidak memiliki akses untuk melakukan aksi ini');
            return false;
        }
        return true;
    }

    protected function getApprovalUserRole()
    {
        $role = Auth::user()->role;
        return match ($role) {
            'manager_accounting' => 'manager_keuangan',
            'staff_accounting'   => 'staff',
            'direktur', 'superadmin' => 'superadmin',
            default => null
        };
    }

    public function removeExistingFile($index)
    {
        if (!$this->ensureCanManage()) return;

        if (isset($this->existingBuktiPembayaran[$index])) {
            $this->filesToRemove[] = $this->existingBuktiPembayaran[$index];
            unset($this->existingBuktiPembayaran[$index]);
            $this->existingBuktiPembayaran = array_values($this->existingBuktiPembayaran);
        }
    }

    public function addOtherExpenseRow(): void
    {
        if (!$this->ensureCanManage()) return;
        $this->expenseForm['others'][] = ['type' => '', 'amount' => 0];
    }

    public function removeOtherExpenseRow(int $index): void
    {
        if (!$this->ensureCanManage()) return;
        if (isset($this->expenseForm['others'][$index])) {
            array_splice($this->expenseForm['others'], $index, 1);
        }
        if (empty($this->expenseForm['others'])) {
            $this->expenseForm['others'][] = ['type' => '', 'amount' => 0];
        }
        $this->updateExpenses();
    }

    public function updateBuktiPembayaran()
    {
        if (!$this->ensureCanManage() || !$this->getApprovalInstance()) return;

        if (!empty($this->buktiPembayaran)) {
            $this->validate(['buktiPembayaran.*' => 'file|mimes:jpg,jpeg,png,pdf|max:20480']);
            if (collect($this->buktiPembayaran)->sum(fn($f) => $f->getSize()) > 20971520) { // 20MB
                session()->flash('error', 'Total ukuran file tidak boleh melebihi 20 MB');
                return;
            }
        }

        DB::beginTransaction();
        try {
            $approval = $this->getApprovalInstance();
            $oldValue = $approval->bukti_pembayaran;
            $finalFiles = $this->existingBuktiPembayaran;

            foreach ($this->filesToRemove as $fileToRemove) {
                Storage::disk('public')->delete($fileToRemove);
            }

            foreach ($this->buktiPembayaran as $file) {
                $finalFiles[] = $file->store('bukti-pembayaran', 'public');
            }

            if (empty($finalFiles)) {
                throw new \Exception('Minimal harus ada 1 file bukti pembayaran');
            }

            $approval->update(['bukti_pembayaran' => json_encode($finalFiles)]);

            if (($this->editMode ?? false) && $approval->status === 'completed') {
                $this->logHistory($approval, 'bukti_pembayaran', $oldValue ? 'Previous files' : 'No files', count($finalFiles) . " file(s) total", 'Updated bukti pembayaran');
            }

            DB::commit();
            $this->buktiPembayaran = [];
            session()->flash('message', 'Bukti pembayaran berhasil diupdate');
            $this->reloadComponentData();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Bukti Pembayaran Error: ' . $e->getMessage());
            session()->flash('error', 'Gagal update bukti: ' . $e->getMessage());
        }
    }

    public function updateRefraksi()
    {
        if (!$this->ensureCanManage() || !$this->getApprovalInstance()) return;

        DB::beginTransaction();
        try {
            $approval = $this->getApprovalInstance();
            $pengiriman = $approval->pengiriman;

            $qtyBefore = $pengiriman->total_qty_kirim;
            $amountBefore = $approval->amount_before_refraksi ?? $pengiriman->total_harga_kirim;
            $val = floatval($this->refraksiForm['value'] ?? 0);
            $type = $val <= 0 ? null : $this->refraksiForm['type'];

            $refraksiAmount = 0;
            $qtyAfter = $qtyBefore;
            $amountAfter = $amountBefore;

            if ($type === 'qty') {
                $refQty = $qtyBefore * ($val / 100);
                $qtyAfter = $qtyBefore - $refQty;
                $hargaPerKg = $qtyBefore > 0 ? $amountBefore / $qtyBefore : 0;
                $refraksiAmount = $refQty * $hargaPerKg;
            } elseif ($type === 'rupiah') {
                $refraksiAmount = $val * $qtyBefore;
            } elseif ($type === 'lainnya') {
                $refraksiAmount = $val;
            }

            $amountAfter -= $refraksiAmount;

            $oldValues = $approval->only(['refraksi_type', 'refraksi_value', 'refraksi_amount', 'amount_after_refraksi']);

            $approval->update([
                'refraksi_type' => $type,
                'refraksi_value' => $val,
                'refraksi_amount' => $refraksiAmount,
                'qty_before_refraksi' => $qtyBefore,
                'qty_after_refraksi' => $qtyAfter,
                'amount_before_refraksi' => $amountBefore,
                'amount_after_refraksi' => $amountAfter,
            ]);

            $this->recalculatePembayaranTotals($approval);
            $approval->save();

            if (($this->editMode ?? false) && $approval->status === 'completed') {
                $newValues = $approval->only(['refraksi_type', 'refraksi_value', 'refraksi_amount', 'amount_after_refraksi']);
                $this->logHistory($approval, 'refraksi', $oldValues, $newValues, 'Updated refraksi pembayaran');
            }

            DB::commit();
            session()->flash('message', 'Refraksi berhasil diupdate');
            $this->reloadComponentData();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Refraksi Error: ' . $e->getMessage());
            session()->flash('error', 'Gagal update refraksi: ' . $e->getMessage());
        }
    }

    public function updatePiutang()
    {
        if (!$this->ensureCanManage() || !$this->getApprovalInstance()) return;

        DB::beginTransaction();
        try {
            $approval = $this->getApprovalInstance();
            $oldValues = $approval->only(['catatan_piutang_id', 'piutang_amount', 'piutang_notes']);

            if (empty($this->piutangForm['catatan_piutang_id'])) {
                $approval->update(['catatan_piutang_id' => null, 'piutang_amount' => 0, 'piutang_notes' => null]);
            } else {
                if ($this->piutangForm['amount'] <= 0) throw new \Exception('Jumlah pemotongan harus lebih dari 0');
                
                $catatanPiutang = \App\Models\CatatanPiutang::find($this->piutangForm['catatan_piutang_id']);
                if (!$catatanPiutang) throw new \Exception('Data piutang tidak ditemukan');
                if ($this->piutangForm['amount'] > $catatanPiutang->sisa_piutang) {
                    throw new \Exception('Jumlah pemotongan melebihi sisa piutang');
                }

                $approval->update([
                    'catatan_piutang_id' => $this->piutangForm['catatan_piutang_id'],
                    'piutang_amount' => $this->piutangForm['amount'],
                    'piutang_notes' => $this->piutangForm['notes'],
                ]);
            }

            $this->recalculatePembayaranTotals($approval);
            $approval->save();

            if (($this->editMode ?? false) && $approval->status === 'completed') {
                $this->logHistory($approval, 'piutang', $oldValues, $this->piutangForm, 'Updated piutang data');
            }

            DB::commit();
            session()->flash('message', 'Pemotongan piutang berhasil disimpan');
            $this->reloadComponentData();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Piutang Error: ' . $e->getMessage());
            session()->flash('error', $e->getMessage());
        }
    }

    public function updateExpenses(): void
    {
        if (!$this->ensureCanManage() || !$this->getApprovalInstance()) return;

        foreach (['truk', 'kuli', 'fee'] as $k) {
            if (floatval($this->expenseForm[$k] ?? 0) < 0) {
                session()->flash('error', ucfirst($k) . ' tidak boleh negatif');
                return;
            }
        }

        foreach (($this->expenseForm['others'] ?? []) as $i => $row) {
            $amount = floatval($row['amount'] ?? 0);
            $type = trim((string)($row['type'] ?? ''));

            if ($amount < 0) {
                session()->flash('error', "Nominal pengeluaran tidak boleh negatif (baris #".($i+1).")"); return;
            }
            if ($amount > 0 && $type === '') {
                session()->flash('error', "Jenis pengeluaran wajib diisi (baris #".($i+1).")"); return;
            }
            if ($amount > 0 && in_array(strtolower($type), ['truk', 'kuli', 'fee'], true)) {
                session()->flash('error', "Nama '$type' sudah ada di opsi utama (baris #".($i+1).")"); return;
            }
        }

        DB::beginTransaction();
        try {
            $approval = $this->getApprovalInstance();
            $oldTotal = $approval->additional_expenses_total;

            $approval->expenses()->delete();

            $expensesToCreate = [];
            foreach (['truk', 'kuli', 'fee'] as $type) {
                if (($amt = floatval($this->expenseForm[$type] ?? 0)) > 0) {
                    $expensesToCreate[] = ['type' => $type, 'amount' => $amt];
                }
            }

            foreach (($this->expenseForm['others'] ?? []) as $row) {
                if (trim($row['type']) !== '' && floatval($row['amount']) > 0) {
                    $expensesToCreate[] = ['type' => trim($row['type']), 'amount' => floatval($row['amount'])];
                }
            }

            if (!empty($expensesToCreate)) $approval->expenses()->createMany($expensesToCreate);

            $this->recalculatePembayaranTotals($approval);
            $approval->save();

            if (($this->editMode ?? false) && $approval->status === 'completed') {
                $this->logHistory($approval, 'additional_expenses', $oldTotal, $approval->additional_expenses_total, 'Updated pengeluaran tambahan');
            }

            DB::commit();
            session()->flash('message', 'Pengeluaran tambahan berhasil disimpan');
            $this->reloadComponentData();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Expenses Error: ' . $e->getMessage());
            session()->flash('error', 'Gagal menyimpan pengeluaran tambahan: ' . $e->getMessage());
        }
    }

    protected function recalculatePembayaranTotals($approval): void
    {
        $amountBefore = $approval->amount_before_refraksi ?? $approval->pengiriman->total_harga_kirim;
        $refraksiAmount = floatval($approval->refraksi_amount ?? 0);
        
        $approval->loadMissing('expenses');
        $expensesTotal = floatval($approval->expenses->sum('amount'));

        $subtotal = max(0, floatval($amountBefore) - $refraksiAmount + $expensesTotal);
        $totalDibayarkan = max(0, $subtotal - floatval($approval->piutang_amount ?? 0));

        $approval->additional_expenses_total = $expensesTotal;
        $approval->subtotal = $subtotal;
        $approval->total_dibayarkan = $totalDibayarkan;
    }

    protected function logHistory($approval, $field, $old, $new, $notes)
    {
        ApprovalHistory::create([
            'approval_type' => 'pembayaran',
            'approval_id' => $approval->id,
            'pengiriman_id' => $approval->pengiriman_id,
            'role' => $this->getApprovalUserRole(),
            'user_id' => Auth::id(),
            'action' => 'edited',
            'notes' => $notes,
            'changes' => ['field' => $field, 'old' => $old, 'new' => $new],
        ]);
    }

    protected function getApprovalInstance()
    {
        return $this->approval ?? $this->selectedPengiriman ?? null;
    }

    protected function reloadComponentData()
    {
        $id = $this->getApprovalInstance()->id;
        if (method_exists($this, 'loadApproval')) $this->loadApproval();
        else if (method_exists($this, 'showDetail')) $this->showDetail($id);
    }
}