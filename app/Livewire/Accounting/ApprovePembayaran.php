<?php

namespace App\Livewire\Accounting;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ApprovalPembayaran as ApprovalPembayaranModel;
use App\Models\ApprovalPenagihan;
use App\Models\ApprovalHistory;
use App\Models\InvoicePenagihan;
use App\Models\PembayaranPiutang;
use App\Models\CatatanPiutang;
use App\Services\Notifications\ApprovalPenagihanNotificationService;
use App\Livewire\Accounting\Traits\WithPaymentApproval;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ApprovePembayaran extends Component
{
    use WithFileUploads, WithPaymentApproval;

    public $approvalId;
    public $approval;
    public $pengiriman;
    public $invoicePenagihan;
    public $approvalHistory;
    public $notes = '';
    public $canManage = false;

    public function mount($approvalId)
    {
        $this->approvalId = $approvalId;
        $this->canManage = in_array(Auth::user()->role, ['staff_accounting', 'manager_accounting', 'direktur', 'superadmin']);
        $this->loadApproval();
    }

    public function loadApproval()
    {
        $this->approval = ApprovalPembayaranModel::with([
            'pengiriman.pengirimanDetails.bahanBakuSupplier.supplier',
            'pengiriman.purchaseOrder', 'histories.user', 'staff', 'manager', 'superadmin', 'expenses'
        ])->findOrFail($this->approvalId);

        $this->pengiriman = $this->approval->pengiriman;
        $this->invoicePenagihan = InvoicePenagihan::where('pengiriman_id', $this->pengiriman->id)->first();
        $this->approvalHistory = $this->approval->histories()->orderByDesc('created_at')->get();

        $this->piutangForm = [
            'catatan_piutang_id' => $this->approval->catatan_piutang_id,
            'amount' => $this->approval->piutang_amount ?? 0,
            'notes' => $this->approval->piutang_notes ?? '',
        ];

        $this->refraksiForm = [
            'type' => $this->approval->refraksi_type ?? 'qty',
            'value' => floatval($this->approval->refraksi_value ?? 0),
        ];

        $this->loadExpensesForm();
        $this->recalculatePembayaranTotals($this->approval);
    }

    private function loadExpensesForm(): void
    {
        $this->expenseForm = ['truk' => 0, 'kuli' => 0, 'fee' => 0, 'others' => []];
        foreach ($this->approval->expenses as $e) {
            $type = trim((string)($e->type ?? ''));
            if (in_array($type, ['truk', 'kuli', 'fee'])) {
                $this->expenseForm[$type] = floatval($e->amount);
            } else {
                $this->expenseForm['others'][] = ['type' => $type, 'amount' => floatval($e->amount)];
            }
        }
        if (empty($this->expenseForm['others'])) $this->expenseForm['others'][] = ['type' => '', 'amount' => 0];
    }

    public function approve()
    {
        if (!$this->approval || !$this->ensureCanManage()) return;

        DB::beginTransaction();
        try {
            $role = $this->getApprovalUserRole();
            if (!$role) throw new \Exception('Anda tidak memiliki akses');
            if ($this->approval->status !== 'pending') throw new \Exception('Approval tidak valid');
            
            $buktiPath = $this->handleBuktiUploads();

            $updateData = ['status' => 'completed', 'bukti_pembayaran' => $buktiPath];
            $updateData[in_array($role, ['manager_keuangan', 'superadmin']) ? 'manager_id' : 'staff_id'] = Auth::id();
            $updateData[in_array($role, ['manager_keuangan', 'superadmin']) ? 'manager_approved_at' : 'staff_approved_at'] = now();
            $this->approval->update($updateData);

            $this->processPiutangDeduction();
            $this->createInvoiceAndApprovalPenagihan(Auth::id());

            ApprovalHistory::create([
                'approval_type' => 'pembayaran', 'approval_id' => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id, 'role' => $role,
                'user_id' => Auth::id(), 'action' => 'approved', 'notes' => $this->notes,
            ]);

            DB::commit();
            Session::flash('message', 'Approval berhasil disimpan');
            return redirect()->route('accounting.approval-pembayaran');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Approve Process Error: " . $e->getMessage());
            Session::flash('error', $e->getMessage());
        }
    }

    public function reject()
    {
        if (!$this->approval || !$this->ensureCanManage()) return;
        if (empty($this->notes)) { Session::flash('error', 'Catatan penolakan harus diisi'); return; }

        DB::beginTransaction();
        try {
            $this->approval->update(['status' => 'rejected']);
            ApprovalHistory::create([
                'approval_type' => 'pembayaran', 'approval_id' => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id, 'role' => $this->getApprovalUserRole(),
                'user_id' => Auth::id(), 'action' => 'rejected', 'notes' => $this->notes,
            ]);
            DB::commit();
            Session::flash('message', 'Approval berhasil ditolak');
            return redirect()->route('accounting.approval-pembayaran');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Reject Process Error: " . $e->getMessage());
            Session::flash('error', $e->getMessage());
        }
    }

    private function handleBuktiUploads()
    {
        if (empty($this->buktiPembayaran)) throw new \Exception('Bukti pembayaran wajib diupload');
        $this->validate(['buktiPembayaran.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:20480']);
        
        if (collect($this->buktiPembayaran)->sum(fn($f) => $f->getSize()) > 20971520) {
            throw new \Exception('Total ukuran file melebihi 20 MB');
        }
        
        return json_encode(array_map(fn($f) => $f->store('bukti-pembayaran', 'public'), $this->buktiPembayaran));
    }

    private function processPiutangDeduction()
    {
        if ($this->approval->catatan_piutang_id && $this->approval->piutang_amount > 0) {
            $catatan = CatatanPiutang::find($this->approval->catatan_piutang_id);
            if ($catatan) {
                PembayaranPiutang::create([
                    'catatan_piutang_id' => $catatan->id,
                    'no_pembayaran' => PembayaranPiutang::generateNoPembayaran(),
                    'tanggal_bayar' => now(),
                    'jumlah_bayar' => $this->approval->piutang_amount,
                    'metode_pembayaran' => 'potong_pembayaran',
                    'catatan' => 'Pemotongan dari pengiriman ' . $this->approval->pengiriman->no_pengiriman,
                    'created_by' => Auth::id(),
                ]);
                $catatan->updateSisaPiutang();
            }
        }
    }

    private function createInvoiceAndApprovalPenagihan($userId)
    {
        $this->approval->loadMissing(['pengiriman.pengirimanDetails.purchaseOrderBahanBaku', 'pengiriman.purchaseOrder.klien', 'expenses']);
        if (!$this->approval->pengiriman || InvoicePenagihan::where('pengiriman_id', $this->approval->pengiriman_id)->exists()) return;

        $pengiriman = $this->approval->pengiriman;
        $totalSellingPrice = 0;
        $items = [];

        foreach ($pengiriman->pengirimanDetails as $detail) {
            $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
            $itemTotal = floatval($detail->qty_kirim) * floatval($orderDetail->harga_jual ?? 0);
            $totalSellingPrice += $itemTotal;

            $items[] = [
                'description' => $detail->bahanBakuSupplier->nama ?? ($orderDetail->bahanBakuKlien->nama ?? 'Bahan Baku'),
                'quantity' => floatval($detail->qty_kirim),
                'unit_price' => floatval($orderDetail->harga_jual ?? 0),
                'refraksi_kg' => 0,
                'total' => $itemTotal,
            ];
        }

        $subtotal = $this->approval->amount_after_refraksi ?? ($totalSellingPrice - floatval($this->approval->refraksi_amount));
        $expensesTotal = floatval($this->approval->additional_expenses_total ?? 0);
        $finalTotal = max(0, $subtotal + $expensesTotal);

        $invoice = InvoicePenagihan::create([
            'pengiriman_id' => $pengiriman->id,
            'invoice_number' => InvoicePenagihan::generateInvoiceNumber(),
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'customer_name' => $pengiriman->purchaseOrder->klien->nama ?? 'Customer',
            'items' => $items,
            'subtotal' => $finalTotal,
            'additional_expenses_total' => $expensesTotal,
            'total_amount' => $finalTotal,
            'status' => 'pending',
            'created_by' => $userId,
            // (Memasukkan nilai refraksi sesuai state dari approval)
            'refraksi_type' => $this->approval->refraksi_type,
            'refraksi_value' => $this->approval->refraksi_value ?? 0,
            'refraksi_amount' => $this->approval->refraksi_amount ?? 0,
        ]);

        $pengiriman->update(['invoice_penagihan_id' => $invoice->id]);
        foreach ($this->approval->expenses as $e) $invoice->expenses()->create($e->only(['type', 'amount']));
        
        $approvalPenagihan = ApprovalPenagihan::create(['pengiriman_id' => $pengiriman->id, 'invoice_id' => $invoice->id, 'status' => 'pending']);
        if ($approvalPenagihan) ApprovalPenagihanNotificationService::notifyPendingApproval($approvalPenagihan);
    }

    public function render() { return view('livewire.accounting.approve-pembayaran'); }
}