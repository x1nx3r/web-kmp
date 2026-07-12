<?php

namespace App\Livewire\Accounting\Traits;

use App\Models\ApprovalHistory;
use Illuminate\Support\Facades\Auth;

trait WithInvoiceShared
{
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
            default => null,
        };
    }

    protected function logInvoiceHistory($approvalId, $pengirimanId, $invoiceId, $action, $notes, $changes = null)
    {
        ApprovalHistory::create([
            'approval_type' => 'penagihan',
            'approval_id'   => $approvalId,
            'pengiriman_id' => $pengirimanId,
            'invoice_id'    => $invoiceId,
            'role'          => $this->getApprovalUserRole(),
            'user_id'       => Auth::id(),
            'action'        => $action,
            'notes'         => $notes,
            'changes'       => $changes,
        ]);
    }
}