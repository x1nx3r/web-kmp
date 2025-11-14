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
    public $activeTab = 'pending'; // pending or approved
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
        'notes' => '',
    ];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'activeTab' => ['except' => 'pending'],
    ];

    protected $rules = [
        'invoiceForm.customer_name' => 'required|string|max:255',
        'invoiceForm.customer_address' => 'required|string',
        'invoiceForm.customer_phone' => 'nullable|string|max:20',
        'invoiceForm.customer_email' => 'nullable|email|max:255',
        'invoiceForm.refraksi_type' => 'required|in:qty,rupiah,lainnya',
        'invoiceForm.refraksi_value' => 'required|numeric|min:0',
        'invoiceForm.notes' => 'nullable|string',
    ];

    public function render()
    {
        // Get pengiriman with status 'berhasil' AND approval_pembayaran completed
        // Show in 'pending' tab - untuk buat invoice
        $pengirimansWithoutInvoice = null;

        if ($this->activeTab === 'pending') {
            $pengirimansWithoutInvoice = Pengiriman::where('status', 'berhasil')
                ->doesntHave('invoicePenagihan')
                ->whereHas('approvalPembayaran', function($q) {
                    $q->where('status', 'completed');
                })
                ->with(['purchaseOrder.klien', 'forecast', 'purchasing'])
                ->when($this->search, function ($q) {
                    $q->where('no_pengiriman', 'like', '%' . $this->search . '%');
                })
                ->latest()
                ->paginate(10, ['*'], 'page_without_invoice');
        }

        // Get approval penagihan based on active tab
        $query = ApprovalPenagihanModel::with([
            'invoice',
            'pengiriman.purchaseOrder.klien',
            'pengiriman.forecast',
            'pengiriman.purchasing',
            'staff',
            'manager'
        ]);

        // Filter by tab
        if ($this->activeTab === 'pending') {
            $query->where('status', 'pending');
        } else {
            // approved tab - hanya yang completed
            $query->where('status', 'completed');
        }

        if ($this->search) {
            $query->whereHas('pengiriman', function ($q) {
                $q->where('no_pengiriman', 'like', '%' . $this->search . '%');
            })->orWhereHas('invoice', function ($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%');
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
            'details.bahanBakuKlien',
            'approvalPembayaran.histories' => function($query) {
                $query->where('approval_type', 'pembayaran')
                      ->orderBy('created_at', 'desc');
            }
        ])->findOrFail($pengirimanId);

        // Pre-fill form with customer data from PO's Klien
        $klien = $pengiriman->purchaseOrder->klien ?? null;

        // Get refraksi and notes from approval pembayaran if exists
        $approvalPembayaran = $pengiriman->approvalPembayaran;
        $refraksiType = 'qty';
        $refraksiValue = 0;
        $notes = '';

        if ($approvalPembayaran) {
            $refraksiType = $approvalPembayaran->refraksi_type ?? 'qty';
            $refraksiValue = $approvalPembayaran->refraksi_value ?? 0;

            // Get latest note from approval history
            $latestHistory = $approvalPembayaran->histories->first();
            if ($latestHistory && $latestHistory->notes) {
                $notes = 'Catatan dari Pembayaran: ' . $latestHistory->notes;
            }
        }

        $this->invoiceForm = [
            'customer_name' => $klien->nama ?? '',
            'customer_address' => $klien->cabang ?? '',
            'customer_phone' => $klien->no_hp ?? '',
            'customer_email' => '',
            'refraksi_type' => $refraksiType,
            'refraksi_value' => $refraksiValue,
            'notes' => $notes,
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
            } elseif ($this->invoiceForm['refraksi_type'] === 'lainnya') {
                // Refraksi Lainnya: input manual langsung nominal total
                $refraksiAmount = $this->invoiceForm['refraksi_value'];
                $subtotal = $subtotal - $refraksiAmount;
            }

            // Calculate amounts
            $taxAmount = $subtotal * ($companySetting->tax_percentage / 100);
            $totalAmount = $subtotal + $taxAmount;

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
                'discount_amount' => 0,
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
            'histories.user'
        ])->findOrFail($approvalId);

        $this->selectedData = $approval;
        $this->showDetailModal = true;
        $this->notes = '';

        // Load refraksi values to form for editing
        if ($approval->invoice) {
            $this->invoiceForm['refraksi_type'] = $approval->invoice->refraksi_type ?? 'qty';
            $this->invoiceForm['refraksi_value'] = $approval->invoice->refraksi_value ?? 0;
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
            } elseif ($invoice->refraksi_type === 'lainnya') {
                // Refraksi Lainnya: input manual langsung nominal total
                $refraksiAmount = $invoice->refraksi_value;
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
        if ($user->role === 'manager_accounting') {
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
            'refraksi_type' => 'qty',
            'refraksi_value' => 0,
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

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->statusFilter = 'all';
        $this->resetPage();
    }
}
