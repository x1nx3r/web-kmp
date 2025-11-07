<?php

namespace App\Livewire\Accounting;

use App\Models\Pengiriman;
use App\Models\InvoicePenagihan;
use App\Models\ApprovalPenagihan as ApprovalPenagihanModel;
use App\Models\ApprovalHistory;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class ApprovalPenagihan extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $selectedData = null;
    public $showDetailModal = false;
    public $showCreateInvoiceModal = false;
    public $notes = '';

    // Invoice form
    public $invoiceForm = [
        'customer_name' => '',
        'customer_address' => '',
        'customer_phone' => '',
        'customer_email' => '',
        'refraksi_type' => 'qty', // 'qty' or 'rupiah'
        'refraksi_value' => 0,
        'discount_amount' => 0,
        'notes' => '',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

    protected $rules = [
        'invoiceForm.customer_name' => 'required|string|max:255',
        'invoiceForm.customer_address' => 'required|string',
        'invoiceForm.customer_phone' => 'nullable|string|max:20',
        'invoiceForm.customer_email' => 'nullable|email|max:255',
        'invoiceForm.refraksi_type' => 'required|in:qty,rupiah',
        'invoiceForm.refraksi_value' => 'required|numeric|min:0',
        'invoiceForm.discount_amount' => 'required|numeric|min:0',
        'invoiceForm.notes' => 'nullable|string',
    ];

    public function render()
    {
        // Get pengiriman with status 'berhasil' (yang belum ada invoice)
        $pengirimansWithoutInvoice = Pengiriman::where('status', 'berhasil')
            ->doesntHave('invoicePenagihan')
            ->with(['purchaseOrder.klien', 'forecast', 'purchasing'])
            ->when($this->search, function ($q) {
                $q->where('no_pengiriman', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10, ['*'], 'page_without_invoice');

        // Get approval penagihan (yang sudah ada invoice)
        $query = ApprovalPenagihanModel::with([
            'invoice',
            'pengiriman.purchaseOrder.klien',
            'pengiriman.forecast',
            'pengiriman.purchasing',
            'staff',
            'manager',
            'superadmin'
        ]);

        if ($this->search) {
            $query->whereHas('pengiriman', function ($q) {
                $q->where('no_pengiriman', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $approvals = $query->latest()->paginate(10, ['*'], 'page_approval');

        return view('livewire.accounting.approval-penagihan', [
            'pengirimansWithoutInvoice' => $pengirimansWithoutInvoice,
            'approvals' => $approvals,
        ]);
    }

    public function showCreateInvoice($pengirimanId)
    {
        $pengiriman = Pengiriman::with([
            'purchaseOrder.klien',
            'forecast',
            'details.bahanBakuKlien'
        ])->findOrFail($pengirimanId);

        // Pre-fill form with pengiriman data
        $klien = $pengiriman->purchaseOrder->klien ?? null;

        $this->invoiceForm = [
            'customer_name' => $klien->nama ?? '',
            'customer_address' => $klien->alamat ?? '',
            'customer_phone' => $klien->no_hp ?? '',
            'customer_email' => $klien->email ?? '',
            'discount_amount' => 0,
            'notes' => '',
        ];

        $this->selectedData = $pengiriman;
        $this->showCreateInvoiceModal = true;
    }

    public function createInvoice()
    {
        $this->validate();

        $pengiriman = $this->selectedData;

        if (!$pengiriman) {
            session()->flash('error', 'Data pengiriman tidak ditemukan');
            return;
        }

        DB::beginTransaction();
        try {
            $companySetting = CompanySetting::getSettings();

            // Generate invoice number
            $invoiceNumber = InvoicePenagihan::generateInvoiceNumber();

            // Prepare items
            $items = [[
                'item_name' => 'Pengiriman ' . $pengiriman->no_pengiriman,
                'description' => 'No. Pengiriman: ' . $pengiriman->no_pengiriman .
                                 '\nTanggal Kirim: ' . $pengiriman->tanggal_kirim->format('d M Y'),
                'quantity' => 1,
                'unit' => 'paket',
                'unit_price' => $pengiriman->total_harga_kirim,
                'amount' => $pengiriman->total_harga_kirim,
            ]];

            // Calculate refraksi
            $qtyBeforeRefraksi = $pengiriman->total_qty_kirim;
            $qtyAfterRefraksi = $qtyBeforeRefraksi;
            $refraksiAmount = 0;
            $subtotal = $pengiriman->total_harga_kirim;

            if ($this->invoiceForm['refraksi_type'] === 'qty') {
                // Refraksi Qty: persentase dari qty
                // Contoh: 1% dari 5000kg = 50kg refraksi, jadi 4950kg
                $refraksiQty = $qtyBeforeRefraksi * ($this->invoiceForm['refraksi_value'] / 100);
                $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;

                // Hitung refraksi amount berdasarkan harga per kg
                $hargaPerKg = $subtotal / $qtyBeforeRefraksi;
                $refraksiAmount = $refraksiQty * $hargaPerKg;
                $subtotal = $subtotal - $refraksiAmount;
            } elseif ($this->invoiceForm['refraksi_type'] === 'rupiah') {
                // Refraksi Rupiah: potongan rupiah per kg
                // Contoh: potongan 40 rupiah/kg dari 5000kg = 200,000 rupiah
                $refraksiAmount = $this->invoiceForm['refraksi_value'] * $qtyBeforeRefraksi;
                $subtotal = $subtotal - $refraksiAmount;
            }

            // Calculate amounts
            $taxAmount = $subtotal * ($companySetting->tax_percentage / 100);
            $discountAmount = floatval($this->invoiceForm['discount_amount']);
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            // Create invoice
            $invoice = InvoicePenagihan::create([
                'pengiriman_id' => $pengiriman->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now(),
                'due_date' => now()->addDays($companySetting->invoice_due_days),
                'customer_name' => $this->invoiceForm['customer_name'],
                'customer_address' => $this->invoiceForm['customer_address'],
                'customer_phone' => $this->invoiceForm['customer_phone'],
                'customer_email' => $this->invoiceForm['customer_email'],
                'items' => $items,
                'refraksi_type' => $this->invoiceForm['refraksi_type'],
                'refraksi_value' => $this->invoiceForm['refraksi_value'],
                'refraksi_amount' => $refraksiAmount,
                'qty_before_refraksi' => $qtyBeforeRefraksi,
                'qty_after_refraksi' => $qtyAfterRefraksi,
                'subtotal' => $subtotal,
                'tax_percentage' => $companySetting->tax_percentage,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'notes' => $this->invoiceForm['notes'],
                'payment_status' => 'unpaid',
                'created_by' => Auth::id(),
            ]);

            // Create approval penagihan
            ApprovalPenagihanModel::create([
                'invoice_id' => $invoice->id,
                'pengiriman_id' => $pengiriman->id,
                'status' => 'pending',
            ]);

            DB::commit();

            session()->flash('message', 'Invoice berhasil dibuat');
            $this->closeModal();
            $this->render();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal membuat invoice: ' . $e->getMessage());
        }
    }

    public function showDetail($approvalId)
    {
        $approval = ApprovalPenagihanModel::with([
            'invoice',
            'pengiriman.purchaseOrder.klien',
            'pengiriman.forecast',
            'pengiriman.purchasing',
            'pengiriman.details.bahanBakuKlien',
            'staff',
            'manager',
            'superadmin',
            'histories.user'
        ])->findOrFail($approvalId);

        $this->selectedData = $approval;
        $this->showDetailModal = true;
        $this->notes = '';

        // Load refraksi values to form for editing
        if ($approval->invoice) {
            $this->invoiceForm['refraksi_type'] = $approval->invoice->refraksi_type ?? 'qty';
            $this->invoiceForm['refraksi_value'] = $approval->invoice->refraksi_value ?? 0;
            $this->invoiceForm['discount_amount'] = $approval->invoice->discount_amount ?? 0;
        }
    }

    public function updateDiscount()
    {
        if (!$this->selectedData || !$this->selectedData->invoice) {
            return;
        }

        DB::beginTransaction();
        try {
            $invoice = $this->selectedData->invoice;
            $pengiriman = $invoice->pengiriman;

            // Update refraksi values
            $invoice->refraksi_type = $this->invoiceForm['refraksi_type'];
            $invoice->refraksi_value = floatval($this->invoiceForm['refraksi_value']);

            // Recalculate refraksi
            $qtyBeforeRefraksi = $pengiriman->total_qty_kirim;
            $qtyAfterRefraksi = $qtyBeforeRefraksi;
            $refraksiAmount = 0;
            $subtotal = $pengiriman->total_harga_kirim;

            if ($invoice->refraksi_type === 'qty') {
                // Refraksi Qty
                $refraksiQty = $qtyBeforeRefraksi * ($invoice->refraksi_value / 100);
                $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;

                $hargaPerKg = $subtotal / $qtyBeforeRefraksi;
                $refraksiAmount = $refraksiQty * $hargaPerKg;
                $subtotal = $subtotal - $refraksiAmount;
            } elseif ($invoice->refraksi_type === 'rupiah') {
                // Refraksi Rupiah
                $refraksiAmount = $invoice->refraksi_value * $qtyBeforeRefraksi;
                $subtotal = $subtotal - $refraksiAmount;
            }

            // Update invoice
            $invoice->refraksi_amount = $refraksiAmount;
            $invoice->qty_before_refraksi = $qtyBeforeRefraksi;
            $invoice->qty_after_refraksi = $qtyAfterRefraksi;
            $invoice->subtotal = $subtotal;

            $invoice->recalculateTotal();

            DB::commit();
            session()->flash('message', 'Refraksi berhasil diupdate');

            // Reload data
            $this->showDetail($this->selectedData->id);
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update refraksi: ' . $e->getMessage());
        }
    }

    public function approve()
    {
        $user = Auth::user();
        $approval = $this->selectedData;

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

            // Check permission based on role
            if ($role === 'staff' && $approval->canStaffApprove()) {
                $approval->update([
                    'staff_id' => $user->id,
                    'staff_approved_at' => now(),
                    'status' => 'staff_approved',
                ]);
            } elseif ($role === 'manager_keuangan' && $approval->canManagerApprove()) {
                $approval->update([
                    'manager_id' => $user->id,
                    'manager_approved_at' => now(),
                    'status' => 'manager_approved',
                ]);
            } elseif ($role === 'superadmin' && $approval->canSuperadminApprove()) {
                $approval->update([
                    'superadmin_id' => $user->id,
                    'superadmin_approved_at' => now(),
                    'status' => 'completed',
                ]);
            } else {
                throw new \Exception('Anda tidak dapat melakukan approval pada tahap ini');
            }

            // Save history
            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $approval->id,
                'pengiriman_id' => $approval->pengiriman_id,
                'invoice_id' => $approval->invoice_id,
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
    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->showCreateInvoiceModal = false;
        $this->selectedData = null;
        $this->notes = '';
        $this->invoiceForm = [
            'customer_name' => '',
            'customer_address' => '',
            'customer_phone' => '',
            'customer_email' => '',
            'discount_amount' => 0,
            'notes' => '',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }
}
