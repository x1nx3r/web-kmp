<?php

namespace App\Livewire\Accounting;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\ApprovalPembayaran as ApprovalPembayaranModel;
use App\Models\ApprovalPenagihan;
use App\Models\ApprovalHistory;
use App\Models\InvoicePenagihan;
use App\Services\Notifications\ApprovalPenagihanNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class ApprovePembayaran extends Component
{
    use WithFileUploads;

    public $approvalId;
    public $approval;
    public $pengiriman;
    public $invoicePenagihan;
    public $approvalHistory;
    public $notes = '';
    public $buktiPembayaran = [];
    public $canManage = false;

    // Piutang form
    public $piutangForm = [
        'catatan_piutang_id' => null,
        'amount' => 0,
        'notes' => '',
    ];

    // Refraksi form
    public $refraksiForm = [
        'type' => 'qty',
        'value' => 0,
    ];

    // Additional expenses (pengeluaran tambahan)
    public $expenseForm = [
        'truk' => 0,
        'kuli' => 0,
        'fee' => 0,
        'others' => [], // each: ['type' => '', 'amount' => 0]
    ];

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
            'pengiriman.purchaseOrder',
            'histories' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'histories.user',
            'staff',
            'manager',
            'superadmin'
        ])->findOrFail($this->approvalId);

        $this->pengiriman = $this->approval->pengiriman;

        // Get invoice penagihan if exists
        $this->invoicePenagihan = InvoicePenagihan::where('pengiriman_id', $this->pengiriman->id)->first();

        $this->approvalHistory = $this->approval->histories;

        // Load piutang values from approval pembayaran
        $this->piutangForm['catatan_piutang_id'] = $this->approval->catatan_piutang_id;
        $this->piutangForm['amount'] = $this->approval->piutang_amount ?? 0;
        $this->piutangForm['notes'] = $this->approval->piutang_notes ?? '';

        // Load refraksi values from approval pembayaran - default 0 jika tidak ada
        $this->refraksiForm['type'] = $this->approval->refraksi_type ?? 'qty';
        $this->refraksiForm['value'] = floatval($this->approval->refraksi_value ?? 0);

        // Load additional expenses
        $this->loadExpenses();

        // Ensure calculated fields are up to date on load
        $this->recalculatePembayaranTotals();
    }

    private function loadExpenses(): void
    {
        $this->expenseForm = [
            'truk' => 0,
            'kuli' => 0,
            'fee' => 0,
            'others' => [],
        ];

        if (!$this->approval) {
            return;
        }

        $this->approval->loadMissing('expenses');

        foreach ($this->approval->expenses as $e) {
            $type = trim((string)($e->type ?? ''));
            $amount = floatval($e->amount ?? 0);

            if ($type === 'truk') {
                $this->expenseForm['truk'] = $amount;
            } elseif ($type === 'kuli') {
                $this->expenseForm['kuli'] = $amount;
            } elseif ($type === 'fee') {
                $this->expenseForm['fee'] = $amount;
            } else {
                $this->expenseForm['others'][] = [
                    'type' => $type,
                    'amount' => $amount,
                ];
            }
        }

        if (empty($this->expenseForm['others'])) {
            $this->expenseForm['others'][] = ['type' => '', 'amount' => 0];
        }
    }

    public function addOtherExpenseRow(): void
    {
        if (!$this->ensureCanManage()) {
            return;
        }

        $this->expenseForm['others'][] = ['type' => '', 'amount' => 0];
    }

    public function removeOtherExpenseRow(int $index): void
    {
        if (!$this->ensureCanManage()) {
            return;
        }

        if (isset($this->expenseForm['others'][$index])) {
            array_splice($this->expenseForm['others'], $index, 1);
        }

        if (empty($this->expenseForm['others'])) {
            $this->expenseForm['others'][] = ['type' => '', 'amount' => 0];
        }

        $this->updateExpenses();
    }

    public function updateExpenses(): void
    {
        if (!$this->ensureCanManage()) {
            return;
        }

        if (!$this->approval) {
            Session::flash('error', 'Data approval tidak ditemukan');
            return;
        }

        // Validate fixed amounts
        foreach (['truk', 'kuli', 'fee'] as $k) {
            $val = floatval($this->expenseForm[$k] ?? 0);
            if ($val < 0) {
                Session::flash('error', ucfirst($k) . ' tidak boleh negatif');
                return;
            }
        }

        // Validate custom rows: if amount>0 => type required
        foreach (($this->expenseForm['others'] ?? []) as $i => $row) {
            $amount = floatval($row['amount'] ?? 0);
            $type = trim((string)($row['type'] ?? ''));

            if ($amount < 0) {
                Session::flash('error', 'Nominal pengeluaran lainnya tidak boleh negatif (baris #' . ($i + 1) . ')');
                return;
            }
            if ($amount > 0 && $type === '') {
                Session::flash('error', 'Nama/Jenis pengeluaran lainnya wajib diisi (baris #' . ($i + 1) . ')');
                return;
            }

            // Prevent using reserved names for others to avoid duplicates
            if ($amount > 0 && in_array(strtolower($type), ['truk', 'kuli', 'fee'], true)) {
                Session::flash('error', 'Nama pengeluaran "' . $type . '" sudah ada di opsi utama. Gunakan field Truk/Kuli/Fee di atas (baris #' . ($i + 1) . ')');
                return;
            }
        }

        DB::beginTransaction();
        try {
            $this->approval->expenses()->delete();

            $fixed = [
                'truk' => floatval($this->expenseForm['truk'] ?? 0),
                'kuli' => floatval($this->expenseForm['kuli'] ?? 0),
                'fee' => floatval($this->expenseForm['fee'] ?? 0),
            ];

            foreach ($fixed as $type => $amount) {
                if ($amount > 0) {
                    $this->approval->expenses()->create([
                        'type' => $type,
                        'amount' => $amount,
                    ]);
                }
            }

            foreach (($this->expenseForm['others'] ?? []) as $row) {
                $type = trim((string)($row['type'] ?? ''));
                $amount = floatval($row['amount'] ?? 0);

                if ($type === '' || $amount <= 0) {
                    continue;
                }

                $this->approval->expenses()->create([
                    'type' => $type,
                    'amount' => $amount,
                ]);
            }

            $this->recalculatePembayaranTotals();
            $this->approval->save();

            DB::commit();
            Session::flash('message', 'Pengeluaran tambahan berhasil disimpan');
            $this->loadApproval();
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('error', 'Gagal menyimpan pengeluaran tambahan: ' . $e->getMessage());
        }
    }

    private function recalculatePembayaranTotals(): void
    {
        if (!$this->approval || !$this->pengiriman) {
            return;
        }

        $amountBefore = $this->approval->amount_before_refraksi;
        if ($amountBefore === null) {
            $amountBefore = $this->pengiriman->total_harga_kirim;
        }

        $refraksiAmount = floatval($this->approval->refraksi_amount ?? 0);

        $this->approval->loadMissing('expenses');
        $expensesTotal = floatval($this->approval->expenses->sum('amount'));

        $subtotal = floatval($amountBefore) - $refraksiAmount + $expensesTotal;
        if ($subtotal < 0) {
            $subtotal = 0;
        }

        $piutang = floatval($this->approval->piutang_amount ?? 0);
        $totalDibayarkan = $subtotal - $piutang;
        if ($totalDibayarkan < 0) {
            $totalDibayarkan = 0;
        }

        // assign as numeric strings to satisfy decimal casts
        $this->approval->additional_expenses_total = $expensesTotal;
        $this->approval->subtotal = $subtotal;
        $this->approval->total_dibayarkan = $totalDibayarkan;
    }

    public function approve()
    {
        $user = Auth::user();

        if (!$this->approval) {
            Session::flash('error', 'Data approval tidak ditemukan');
            return;
        }

        if (!$this->ensureCanManage()) {
            return;
        }

        DB::beginTransaction();
        try {
            $role = $this->getUserRole($user);

            if (!$role) {
                throw new \Exception('Anda tidak memiliki akses untuk melakukan approval');
            }

            // Check if approval can be processed
            if ($this->approval->status !== 'pending') {
                throw new \Exception('Approval ini sudah diproses atau tidak dapat diapprove');
            }

            // Validasi bukti pembayaran wajib untuk semua anggota keuangan
            if (empty($this->buktiPembayaran)) {
                throw new \Exception('Bukti pembayaran wajib diupload untuk approval');
            }

            // Validate file type and size - multiple files with total max 20MB
            $this->validate([
                'buktiPembayaran.*' => 'required|file|mimes:jpg,jpeg,png,pdf|max:20480', // max 20MB per file
            ]);

            // Check total file size (max 20MB = 20480 KB)
            $totalSize = 0;
            foreach ($this->buktiPembayaran as $file) {
                $totalSize += $file->getSize();
            }

            if ($totalSize > 20 * 1024 * 1024) { // 20MB in bytes
                throw new \Exception('Total ukuran file tidak boleh melebihi 20 MB');
            }

            // Upload bukti pembayaran - store multiple files
            $buktiPaths = [];
            foreach ($this->buktiPembayaran as $index => $file) {
                $buktiPaths[] = $file->store('bukti-pembayaran', 'public');
            }
            $buktiPath = json_encode($buktiPaths);

            // Langsung complete untuk semua anggota keuangan
            $updateData = [
                'status' => 'completed',
                'bukti_pembayaran' => $buktiPath,
            ];

            // Set approver based on role
            if ($role === 'manager_keuangan') {
                $updateData['manager_id'] = $user->id;
                $updateData['manager_approved_at'] = now();
            } elseif ($role === 'direktur' || $role === 'superadmin') {
                // Direktur dan superadmin menggunakan manager_id field
                $updateData['manager_id'] = $user->id;
                $updateData['manager_approved_at'] = now();
            } else {
                $updateData['staff_id'] = $user->id;
                $updateData['staff_approved_at'] = now();
            }

            $this->approval->update($updateData);

            // Process piutang as pembayaran if exists
            if ($this->approval->catatan_piutang_id && $this->approval->piutang_amount > 0) {
                $catatanPiutang = \App\Models\CatatanPiutang::find($this->approval->catatan_piutang_id);

                if ($catatanPiutang) {
                    // Create pembayaran piutang record
                    \App\Models\PembayaranPiutang::create([
                        'catatan_piutang_id' => $catatanPiutang->id,
                        'no_pembayaran' => \App\Models\PembayaranPiutang::generateNoPembayaran(),
                        'tanggal_bayar' => now(),
                        'jumlah_bayar' => $this->approval->piutang_amount,
                        'metode_pembayaran' => 'potong_pembayaran',
                        'catatan' => 'Pemotongan dari pembayaran pengiriman ' . $this->approval->pengiriman->no_pengiriman . ($this->approval->piutang_notes ? ' - ' . $this->approval->piutang_notes : ''),
                        'created_by' => $user->id,
                    ]);

                    // Update sisa piutang
                    $catatanPiutang->updateSisaPiutang();
                }
            }

            // Create Invoice Penagihan and Approval Penagihan automatically
            $this->createInvoiceAndApprovalPenagihan($user->id);

            // Save history
            ApprovalHistory::create([
                'approval_type' => 'pembayaran',
                'approval_id' => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'role' => $role,
                'user_id' => $user->id,
                'action' => 'approved',
                'notes' => $this->notes,
            ]);

            DB::commit();

            Session::flash('message', 'Approval berhasil disimpan');
            return redirect()->route('accounting.approval-pembayaran');

        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('error', $e->getMessage());
        }
    }

    public function reject()
    {
        $user = Auth::user();

        if (!$this->approval) {
            Session::flash('error', 'Data approval tidak ditemukan');
            return;
        }

        if (!$this->ensureCanManage()) {
            return;
        }

        if (empty($this->notes)) {
            Session::flash('error', 'Catatan penolakan harus diisi');
            return;
        }

        DB::beginTransaction();
        try {
            $role = $this->getUserRole($user);

            if (!$role) {
                throw new \Exception('Anda tidak memiliki akses untuk melakukan approval');
            }

            $this->approval->update(['status' => 'rejected']);

            // Save history
            ApprovalHistory::create([
                'approval_type' => 'pembayaran',
                'approval_id' => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'role' => $role,
                'user_id' => $user->id,
                'action' => 'rejected',
                'notes' => $this->notes,
            ]);

            DB::commit();

            Session::flash('message', 'Approval berhasil ditolak');
            return redirect()->route('accounting.approval-pembayaran');

        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('error', $e->getMessage());
        }
    }

    public function updatePiutang()
    {
        if (!$this->ensureCanManage()) {
            return;
        }

        if (!$this->approval) {
            Session::flash('error', 'Data approval tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            // Jika tidak ada catatan_piutang_id dipilih (kosong/null), set semua field piutang menjadi null/0
            if (empty($this->piutangForm['catatan_piutang_id'])) {
                $this->approval->update([
                    'catatan_piutang_id' => null,
                    'piutang_amount' => 0,
                    'piutang_notes' => null,
                ]);

                DB::commit();
                Session::flash('message', 'Pemotongan piutang berhasil dihapus');
                $this->loadApproval();
                return;
            }

            // Validate amount if piutang is selected
            if ($this->piutangForm['amount'] <= 0) {
                throw new \Exception('Jumlah pemotongan harus lebih dari 0');
            }

            // Get catatan piutang if selected
            $catatanPiutang = \App\Models\CatatanPiutang::find($this->piutangForm['catatan_piutang_id']);

            if (!$catatanPiutang) {
                throw new \Exception('Data piutang tidak ditemukan');
            }

            // Validate amount tidak melebihi sisa piutang
            if ($this->piutangForm['amount'] > $catatanPiutang->sisa_piutang) {
                throw new \Exception('Jumlah pemotongan tidak boleh melebihi sisa piutang Rp ' . number_format(floatval($catatanPiutang->sisa_piutang), 0, ',', '.'));
            }

            $this->approval->update([
                'catatan_piutang_id' => $this->piutangForm['catatan_piutang_id'],
                'piutang_amount' => $this->piutangForm['amount'],
                'piutang_notes' => $this->piutangForm['notes'],
            ]);

            // Update total_dibayarkan (subtotal - piutang)
            $this->approval->refresh();
            $this->recalculatePembayaranTotals();
            $this->approval->save();

            DB::commit();
            Session::flash('message', 'Pemotongan piutang berhasil disimpan');
            $this->loadApproval();
        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('error', $e->getMessage());
        }
    }

    public function updateRefraksi()
    {
        if (!$this->ensureCanManage()) {
            return;
        }

        if (!$this->approval) {
            Session::flash('error', 'Data approval tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            // Update refraksi values untuk approval pembayaran - default 0 untuk tidak ada refraksi
            $refraksiValue = floatval($this->refraksiForm['value'] ?? 0);

            // Calculate refraksi untuk pembayaran
            $qtyBeforeRefraksi = $this->pengiriman->total_qty_kirim;
            $amountBeforeRefraksi = $this->pengiriman->total_harga_kirim;
            $qtyAfterRefraksi = $qtyBeforeRefraksi;
            $amountAfterRefraksi = $amountBeforeRefraksi;
            $refraksiAmount = 0;

            // Jika value 0 atau kosong, set semua refraksi menjadi 0/null (tanpa refraksi)
            if ($refraksiValue <= 0) {
                $this->approval->refraksi_type = null;
                $this->approval->refraksi_value = 0;
                $this->approval->refraksi_amount = 0;
                $this->approval->qty_after_refraksi = $qtyBeforeRefraksi;
                $this->approval->amount_after_refraksi = $amountBeforeRefraksi;
            } else {
                $this->approval->refraksi_type = $this->refraksiForm['type'];
                $this->approval->refraksi_value = $refraksiValue;

                if ($this->approval->refraksi_type === 'qty') {
                    // Refraksi Qty: potong berdasarkan persentase qty
                    $refraksiQty = $qtyBeforeRefraksi * ($this->approval->refraksi_value / 100);
                    $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;

                    // Hitung potongan amount berdasarkan qty refraksi
                    $hargaPerKg = $amountBeforeRefraksi / $qtyBeforeRefraksi;
                    $refraksiAmount = $refraksiQty * $hargaPerKg;
                    $amountAfterRefraksi = $amountBeforeRefraksi - $refraksiAmount;
                } elseif ($this->approval->refraksi_type === 'rupiah') {
                    // Refraksi Rupiah: potongan harga per kg
                    $refraksiAmount = $this->approval->refraksi_value * $qtyBeforeRefraksi;
                    $amountAfterRefraksi = $amountBeforeRefraksi - $refraksiAmount;
                } elseif ($this->approval->refraksi_type === 'lainnya') {
                    // Refraksi Lainnya: input manual langsung nominal total potongan
                    $refraksiAmount = $this->approval->refraksi_value;
                    $amountAfterRefraksi = $amountBeforeRefraksi - $refraksiAmount;
                }

                $this->approval->qty_after_refraksi = $qtyAfterRefraksi;
                $this->approval->amount_after_refraksi = $amountAfterRefraksi;
                $this->approval->refraksi_amount = $refraksiAmount;
            }

            $this->approval->qty_before_refraksi = $qtyBeforeRefraksi;
            $this->approval->amount_before_refraksi = $amountBeforeRefraksi;

            $this->approval->save();

            // Update subtotal/total after refraksi change
            $this->recalculatePembayaranTotals();
            $this->approval->save();

            DB::commit();

            Session::flash('message', 'Refraksi pembayaran berhasil diupdate');
            $this->loadApproval();

        } catch (\Exception $e) {
            DB::rollBack();
            Session::flash('error', 'Gagal mengupdate refraksi: ' . $e->getMessage());
        }
    }

    private function createInvoiceAndApprovalPenagihan($userId)
    {
        // Check if invoice already exists
        $existingInvoice = InvoicePenagihan::where('pengiriman_id', $this->approval->pengiriman_id)->first();

        if ($existingInvoice) {
            // If invoice already exists, just return
            return;
        }

        $pengiriman = $this->approval->pengiriman;

        // Load pengiriman details with order detail for harga_jual
        $pengiriman->load('pengirimanDetails.purchaseOrderBahanBaku', 'pengirimanDetails.orderDetail');

        $purchaseOrder = $pengiriman->purchaseOrder;
        $klien = $purchaseOrder->klien ?? null;

        // Generate invoice number using model method with duplicate prevention
        $invoiceNumber = InvoicePenagihan::generateInvoiceNumber();

        // Calculate total selling price (harga jual) instead of buying price (harga beli)
        $totalSellingPrice = 0;
        $items = [];

        foreach ($pengiriman->pengirimanDetails as $detail) {
            $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
            $hargaJual = $orderDetail ? floatval($orderDetail->harga_jual) : 0;
            $qtyKirim = floatval($detail->qty_kirim);
            $itemTotal = $qtyKirim * $hargaJual;
            $totalSellingPrice += $itemTotal;

            // Collect item details for invoice
            $bahanBakuName = $detail->bahanBakuSupplier->nama ?? ($orderDetail->bahanBakuKlien->nama ?? 'Bahan Baku');
            $items[] = [
                'description' => $bahanBakuName,
                'quantity' => $qtyKirim,
                'unit_price' => $hargaJual,
                'total' => $itemTotal,
            ];
        }

        // Get refraksi from approval pembayaran (opsional)
        $refraksiType = $this->approval->refraksi_type;
        $refraksiValue = $this->approval->refraksi_value ?? 0;

        // Calculate amounts based on refraksi using selling price
        $qtyBeforeRefraksi = $pengiriman->total_qty_kirim;
        $amountBeforeRefraksi = $totalSellingPrice; // Use selling price
        $qtyAfterRefraksi = $qtyBeforeRefraksi;
        $refraksiAmount = 0;

        // Hanya hitung refraksi jika ada type dan value > 0
        if ($refraksiType && $refraksiValue > 0) {
            if ($refraksiType === 'qty') {
                $refraksiQty = $qtyBeforeRefraksi * ($refraksiValue / 100);
                $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;
                $hargaPerKg = $qtyBeforeRefraksi > 0 ? $amountBeforeRefraksi / $qtyBeforeRefraksi : 0;
                $refraksiAmount = $refraksiQty * $refraksiAmount;
            } elseif ($refraksiType === 'rupiah') {
                $refraksiAmount = $refraksiValue * $qtyBeforeRefraksi;
            } elseif ($refraksiType === 'lainnya') {
                $refraksiAmount = $refraksiValue;
            }
        }

        $subtotal = $amountBeforeRefraksi - $refraksiAmount;

        // Subtotal should also take additional expenses from approval pembayaran
        $approvalExpensesTotal = floatval($this->approval->additional_expenses_total ?? 0);
        $subtotalAfterExpenses = $subtotal + $approvalExpensesTotal;
        if ($subtotalAfterExpenses < 0) {
            $subtotalAfterExpenses = 0;
        }

        // Create Invoice with selling price
        $invoice = InvoicePenagihan::create([
            'pengiriman_id' => $pengiriman->id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'customer_name' => $klien->nama ?? 'Customer',
            'customer_address' => $klien->alamat_lengkap ?? '-',
            'customer_phone' => $klien->no_hp ?? null,
            'customer_email' => null,
            'items' => $items,
            'subtotal' => $subtotalAfterExpenses,
            'tax_percentage' => 0,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $subtotalAfterExpenses,
            'refraksi_type' => $refraksiType,
            'refraksi_value' => $refraksiValue,
            'refraksi_amount' => $refraksiAmount,
            'qty_before_refraksi' => $qtyBeforeRefraksi,
            'qty_after_refraksi' => $qtyAfterRefraksi,
            'amount_before_refraksi' => $amountBeforeRefraksi,
            'amount_after_refraksi' => $subtotal,
            'additional_expenses_total' => $approvalExpensesTotal,
            'status' => 'pending',
            'notes' => 'Invoice dibuat otomatis dari approval pembayaran',
            'created_by' => $userId,
        ]);

        // Copy expense detail rows into invoice
        try {
            $this->approval->loadMissing('expenses');
            foreach ($this->approval->expenses as $e) {
                $invoice->expenses()->create([
                    'type' => $e->type,
                    'amount' => $e->amount,
                ]);
            }
        } catch (\Exception $e) {
            // ignore, invoice still valid
        }

        // Create Approval Penagihan
        $approvalPenagihan = ApprovalPenagihan::create([
            'pengiriman_id' => $pengiriman->id,
            'invoice_id' => $invoice->id,
            'status' => 'pending',
        ]);

        // Send notification to accounting team
        if ($approvalPenagihan) {
            ApprovalPenagihanNotificationService::notifyPendingApproval($approvalPenagihan);
        }
    }

    protected function ensureCanManage(): bool
    {
        if (!$this->canManage) {
            Session::flash('error', 'Anda tidak memiliki akses untuk melakukan aksi ini');
            return false;
        }

        return true;
    }

    private function getUserRole($user)
    {
        if ($user->role === 'manager_accounting') {
            return 'manager_keuangan';
        } elseif ($user->role === 'staff_accounting') {
            return 'staff';
        } elseif ($user->role === 'direktur') {
            return 'direktur';
        } elseif ($user->role === 'superadmin') {
            return 'superadmin';
        }
        return null;
    }

    public function render()
    {
        return view('livewire.accounting.approve-pembayaran');
    }
}
