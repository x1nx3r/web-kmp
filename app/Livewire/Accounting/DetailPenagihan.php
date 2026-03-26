<?php

namespace App\Livewire\Accounting;

use App\Models\ApprovalPenagihan;
use App\Models\ApprovalHistory;
use App\Models\CompanySetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DetailPenagihan extends Component
{
    public $approvalId;
    public $approval;
    public $invoice;
    public $pengiriman;
    public $approvalHistory;
    public $companySetting;
    public $editMode = false;
    public $canManage = false;

    // Edit forms
    public $customerForm = [
        'customer_name' => '',
        'customer_address' => '',
        'customer_phone' => '',
        'customer_email' => '',
    ];

    public $dateForm = [
        'invoice_date' => '',
        'due_date' => '',
    ];

    public $bankForm = [
        'bank_name' => '',
        'bank_account_number' => '',
        'bank_account_name' => '',
    ];

    public $invoiceForm = [
        'refraksi_type' => 'qty',
        'refraksi_value' => 0,
        'amount_before_refraksi' => null,
    ];

    public $invoiceNumberForm = '';

    public $invoiceNotesForm = '';

    protected $rules = [
        'customerForm.customer_name' => 'required|string|max:255',
        'customerForm.customer_address' => 'required|string',
        'customerForm.customer_phone' => 'nullable|string|max:20',
        'customerForm.customer_email' => 'nullable|email|max:255',
        'dateForm.invoice_date' => 'required|date',
        'dateForm.due_date' => 'required|date',
        'bankForm.bank_name' => 'required|string|max:255',
        'bankForm.bank_account_number' => 'required|string|max:50',
        'bankForm.bank_account_name' => 'required|string|max:255',
    ];

    public function mount($approvalId, $editMode = false)
    {
        $this->approvalId = $approvalId;
        $this->editMode = $editMode;

        // Check permissions
        $user = Auth::user();
        $this->canManage = in_array($user->role, ['manager_accounting', 'direktur', 'superadmin']);

        $this->loadDetail();
    }

    public function loadDetail()
    {
        $this->approval = ApprovalPenagihan::with([
            'staff',
            'manager',
            'invoice',
            'pengiriman.pengirimanDetails.bahanBakuSupplier',
            'pengiriman.pengirimanDetails.purchaseOrderBahanBaku.bahanBakuKlien',
            'pengiriman.pengirimanDetails.purchaseOrderBahanBaku',
            'pengiriman.pengirimanDetails.orderDetail',
            'pengiriman.purchaseOrder.orderDetails.orderSuppliers.supplier',
            'histories.user'
        ])->findOrFail($this->approvalId);

        $this->invoice = $this->approval->invoice;
        $this->pengiriman = $this->approval->pengiriman;
        $this->approvalHistory = $this->approval->histories()->orderBy('created_at', 'desc')->get();
        $this->companySetting = CompanySetting::first();

        // Populate edit forms
        if ($this->invoice) {
            $this->customerForm = [
                'customer_name' => $this->invoice->customer_name ?? '',
                'customer_address' => $this->invoice->customer_address ?? '',
                'customer_phone' => $this->invoice->customer_phone ?? '',
                'customer_email' => $this->invoice->customer_email ?? '',
            ];

            $this->dateForm = [
                'invoice_date' => $this->invoice->invoice_date ? Carbon::parse($this->invoice->invoice_date)->format('Y-m-d') : '',
                'due_date' => $this->invoice->due_date ? Carbon::parse($this->invoice->due_date)->format('Y-m-d') : '',
            ];

            $this->bankForm = [
                'bank_name' => $this->invoice->bank_name ?? '',
                'bank_account_number' => $this->invoice->bank_account_number ?? '',
                'bank_account_name' => $this->invoice->bank_account_name ?? '',
            ];

            $this->invoiceForm = [
                'refraksi_type' => $this->invoice->refraksi_type ?? 'qty',
                'refraksi_value' => $this->invoice->refraksi_value ?? 0,
                'amount_before_refraksi' => $this->invoice->amount_before_refraksi,
            ];

            $this->invoiceNumberForm = $this->invoice->invoice_number ?? '';

            $this->invoiceNotesForm = $this->invoice->notes ?? '';
        }
    }

    public function updateCustomerInfo()
    {
        $this->validate([
            'customerForm.customer_name' => 'required|string|max:255',
            'customerForm.customer_address' => 'required|string',
            'customerForm.customer_phone' => 'nullable|string|max:20',
            'customerForm.customer_email' => 'nullable|email|max:255',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            $changes = [
                'before' => [
                    'customer_name' => $this->invoice->customer_name,
                    'customer_address' => $this->invoice->customer_address,
                    'customer_phone' => $this->invoice->customer_phone,
                    'customer_email' => $this->invoice->customer_email,
                ],
                'after' => $this->customerForm,
            ];

            $this->invoice->update($this->customerForm);

            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id' => $this->invoice->id,
                'role' => $this->getUserRole($user),
                'user_id' => $user->id,
                'action' => 'edited',
                'changes' => $changes,
                'notes' => 'Update informasi customer',
            ]);

            DB::commit();
            session()->flash('message', 'Informasi customer berhasil diupdate');
            // Hanya refresh invoice, update form customer saja
            $this->invoice->refresh();
            $this->customerForm = [
                'customer_name' => $this->invoice->customer_name ?? '',
                'customer_address' => $this->invoice->customer_address ?? '',
                'customer_phone' => $this->invoice->customer_phone ?? '',
                'customer_email' => $this->invoice->customer_email ?? '',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update informasi customer: ' . $e->getMessage());
        }
    }

    public function updateInvoiceDates()
    {
        $this->validate([
            'dateForm.invoice_date' => 'required|date',
            'dateForm.due_date' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            $changes = [
                'before' => [
                    'invoice_date' => $this->invoice->invoice_date ? Carbon::parse($this->invoice->invoice_date)->format('Y-m-d') : null,
                    'due_date' => $this->invoice->due_date ? Carbon::parse($this->invoice->due_date)->format('Y-m-d') : null,
                ],
                'after' => $this->dateForm,
            ];

            /** @phpstan-ignore-next-line */
            $this->invoice->invoice_date = Carbon::parse($this->dateForm['invoice_date'])->format('Y-m-d'); // @phpstan-ignore-line
            /** @phpstan-ignore-next-line */
            $this->invoice->due_date = Carbon::parse($this->dateForm['due_date'])->format('Y-m-d'); // @phpstan-ignore-line
            $this->invoice->save();

            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id' => $this->invoice->id,
                'role' => $this->getUserRole($user),
                'user_id' => $user->id,
                'action' => 'edited',
                'changes' => $changes,
                'notes' => 'Update tanggal invoice',
            ]);

            DB::commit();
            session()->flash('message', 'Tanggal invoice berhasil diupdate');
            // Hanya refresh invoice, update form tanggal saja
            $this->invoice->refresh();
            $this->dateForm = [
                'invoice_date' => $this->invoice->invoice_date ? Carbon::parse($this->invoice->invoice_date)->format('Y-m-d') : '',
                'due_date' => $this->invoice->due_date ? Carbon::parse($this->invoice->due_date)->format('Y-m-d') : '',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update tanggal invoice: ' . $e->getMessage());
        }
    }

    public function updateBankInfo()
    {
        $this->validate([
            'bankForm.bank_name' => 'required|string|max:255',
            'bankForm.bank_account_number' => 'required|string|max:50',
            'bankForm.bank_account_name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            $changes = [
                'before' => [
                    'bank_name' => $this->invoice->bank_name,
                    'bank_account_number' => $this->invoice->bank_account_number,
                    'bank_account_name' => $this->invoice->bank_account_name,
                ],
                'after' => $this->bankForm,
            ];

            $this->invoice->update($this->bankForm);

            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id' => $this->invoice->id,
                'role' => $this->getUserRole($user),
                'user_id' => $user->id,
                'action' => 'edited',
                'changes' => $changes,
                'notes' => 'Update informasi bank',
            ]);

            DB::commit();
            session()->flash('message', 'Informasi bank berhasil diupdate');
            // Hanya refresh invoice, update form bank saja
            $this->invoice->refresh();
            $this->bankForm = [
                'bank_name' => $this->invoice->bank_name ?? '',
                'bank_account_number' => $this->invoice->bank_account_number ?? '',
                'bank_account_name' => $this->invoice->bank_account_name ?? '',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update informasi bank: ' . $e->getMessage());
        }
    }

    public function updateInvoiceNotes()
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $changes = [
                'before' => [
                    'notes' => $this->invoice->notes,
                ],
                'after' => [
                    'notes' => $this->invoiceNotesForm,
                ],
            ];

            $this->invoice->notes = $this->invoiceNotesForm;
            $this->invoice->save();

            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id' => $this->invoice->id,
                'role' => $this->getUserRole($user),
                'user_id' => $user->id,
                'action' => 'edited',
                'changes' => $changes,
                'notes' => 'Update catatan invoice',
            ]);

            DB::commit();
            session()->flash('message', 'Catatan invoice berhasil diupdate');
            // Hanya refresh invoice, update form notes saja
            $this->invoice->refresh();
            $this->invoiceNotesForm = $this->invoice->notes ?? '';
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update catatan invoice: ' . $e->getMessage());
        }
    }

    public function updateInvoiceNumber()
    {
        $this->validate([
            'invoiceNumberForm' => 'required|string|max:50|unique:invoice_penagihan,invoice_number,' . $this->invoice->id,
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            $changes = [
                'before' => [
                    'invoice_number' => $this->invoice->invoice_number,
                ],
                'after' => [
                    'invoice_number' => $this->invoiceNumberForm,
                ],
            ];

            $this->invoice->invoice_number = $this->invoiceNumberForm;
            $this->invoice->save();

            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id' => $this->invoice->id,
                'role' => $this->getUserRole($user),
                'user_id' => $user->id,
                'action' => 'edited',
                'changes' => $changes,
                'notes' => 'Update nomor invoice',
            ]);

            DB::commit();
            session()->flash('message', 'Nomor invoice berhasil diupdate');
            // Hanya refresh invoice, update form nomor invoice saja
            $this->invoice->refresh();
            $this->invoiceNumberForm = $this->invoice->invoice_number ?? '';
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update nomor invoice: ' . $e->getMessage());
        }
    }

    public function updateRefraksi()
    {
        DB::beginTransaction();
        try {
            $user = Auth::user();

            $this->validate([
                'invoiceForm.refraksi_type' => 'nullable|in:qty,rupiah,lainnya',
                'invoiceForm.refraksi_value' => 'nullable|numeric|min:0',
                'invoiceForm.amount_before_refraksi' => 'nullable|numeric|min:0',
            ]);

            $oldValues = [
                'refraksi_type' => $this->invoice->refraksi_type,
                'refraksi_value' => $this->invoice->refraksi_value,
                'refraksi_amount' => $this->invoice->refraksi_amount,
                'amount_before_refraksi' => $this->invoice->amount_before_refraksi,
                'amount_after_refraksi' => $this->invoice->amount_after_refraksi,
                'subtotal' => $this->invoice->subtotal,
                'total_amount' => $this->invoice->total_amount,
            ];

            // Recalculate refraksi — gunakan harga JUAL, tapi boleh dioverride manual
            $qtyBeforeRefraksi = floatval($this->pengiriman->total_qty_kirim ?? 0);
            $qtyAfterRefraksi = $qtyBeforeRefraksi;
            $refraksiAmount = 0;

            // Hitung total harga jual default dari detail (fallback)
            $totalSelling = 0;
            $this->pengiriman->load('pengirimanDetails.purchaseOrderBahanBaku', 'pengirimanDetails.orderDetail');
            foreach ($this->pengiriman->pengirimanDetails as $detail) {
                $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
                if ($orderDetail && $orderDetail->harga_jual) {
                    $totalSelling += floatval($detail->qty_kirim) * floatval($orderDetail->harga_jual);
                }
            }

            $manualAmountBefore = $this->invoiceForm['amount_before_refraksi'] ?? null;
            $amountBeforeRefraksi = ($manualAmountBefore !== null && $manualAmountBefore !== '')
                ? floatval($manualAmountBefore)
                : floatval($totalSelling);

            $refraksiType = $this->invoiceForm['refraksi_type'] ?? null;
            $refraksiValue = floatval($this->invoiceForm['refraksi_value'] ?? 0);

            // Jika refraksi 0, selalu reset refraksi; amount_before tetap mengikuti input/manual
            if ($refraksiValue <= 0) {
                $this->invoice->refraksi_type = null;

                $this->invoice->refraksi_value = 0.0; // @phpstan-ignore-line
                $this->invoice->refraksi_amount = 0.0; // @phpstan-ignore-line

                $this->invoice->qty_before_refraksi = (float) $qtyBeforeRefraksi; // @phpstan-ignore-line
                $this->invoice->qty_after_refraksi = (float) $qtyBeforeRefraksi; // @phpstan-ignore-line

                $this->invoice->amount_before_refraksi = (float) $amountBeforeRefraksi; // @phpstan-ignore-line
                $this->invoice->amount_after_refraksi = (float) $amountBeforeRefraksi; // @phpstan-ignore-line
                $this->invoice->subtotal = (float) $amountBeforeRefraksi; // @phpstan-ignore-line
            } else {
                $this->invoice->refraksi_type = $refraksiType;

                $this->invoice->refraksi_value = (float) $refraksiValue; // @phpstan-ignore-line

                $subtotal = (float) $amountBeforeRefraksi;

                if ($refraksiType === 'qty') {
                    $refraksiQty = $qtyBeforeRefraksi * ($refraksiValue / 100);
                    $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;
                    $hargaPerKg = $qtyBeforeRefraksi > 0 ? $subtotal / $qtyBeforeRefraksi : 0;
                    $refraksiAmount = $refraksiQty * $hargaPerKg;
                    $subtotal = $subtotal - $refraksiAmount;
                } elseif ($refraksiType === 'rupiah') {
                    $refraksiAmount = $refraksiValue * $qtyBeforeRefraksi;
                    $subtotal = $subtotal - $refraksiAmount;
                } elseif ($refraksiType === 'lainnya') {
                    $refraksiAmount = $refraksiValue;
                    $subtotal = $subtotal - $refraksiAmount;
                }

                // Guard: subtotal jangan negatif
                if ($subtotal < 0) {
                    $subtotal = 0;
                }

                $this->invoice->refraksi_amount = (float) $refraksiAmount; // @phpstan-ignore-line
                $this->invoice->qty_before_refraksi = (float) $qtyBeforeRefraksi; // @phpstan-ignore-line
                $this->invoice->qty_after_refraksi = (float) $qtyAfterRefraksi; // @phpstan-ignore-line

                $this->invoice->amount_before_refraksi = (float) $amountBeforeRefraksi; // @phpstan-ignore-line
                $this->invoice->amount_after_refraksi = (float) $subtotal; // @phpstan-ignore-line
                $this->invoice->subtotal = (float) $subtotal; // @phpstan-ignore-line
            }

            // Recalculate total (pajak, dll) menggunakan model method
            $this->invoice->recalculateTotal();

            $changes = [
                'before' => $oldValues,
                'after' => [
                    'refraksi_type' => $this->invoice->refraksi_type,
                    'refraksi_value' => $this->invoice->refraksi_value,
                    'refraksi_amount' => $this->invoice->refraksi_amount,
                    'amount_before_refraksi' => $this->invoice->amount_before_refraksi,
                    'amount_after_refraksi' => $this->invoice->amount_after_refraksi,
                    'subtotal' => $this->invoice->subtotal,
                    'total_amount' => $this->invoice->total_amount,
                ],
            ];

            ApprovalHistory::create([
                'approval_type' => 'penagihan',
                'approval_id' => $this->approval->id,
                'pengiriman_id' => $this->approval->pengiriman_id,
                'invoice_id' => $this->invoice->id,
                'role' => $this->getUserRole($user),
                'user_id' => $user->id,
                'action' => 'edited',
                'changes' => $changes,
                'notes' => 'Update harga jual dan refraksi invoice',
            ]);

            DB::commit();
            session()->flash('message', 'Harga jual / refraksi berhasil diupdate');

            $this->invoice->refresh();
            $this->invoiceForm = [
                'refraksi_type' => $this->invoice->refraksi_type ?? 'qty',
                'refraksi_value' => $this->invoice->refraksi_value ?? 0,
                'amount_before_refraksi' => $this->invoice->amount_before_refraksi,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Gagal update refraksi: ' . $e->getMessage());
        }
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

    public function generatePdf()
    {
        try {
            $approval = ApprovalPenagihan::with([
                'invoice',
                'pengiriman.details.bahanBakuSupplier',
                'pengiriman.details.purchaseOrderBahanBaku.bahanBakuKlien',
                'pengiriman.purchaseOrder.klien'
            ])->findOrFail($this->approvalId);

            $invoice = $approval->invoice;
            $pengiriman = $approval->pengiriman;
            $companySetting = CompanySetting::first();

            // Prepare data for PDF
            $data = [
                'invoice' => $invoice,
                'pengiriman' => $pengiriman,
                'approval' => $approval,
                'company' => $companySetting,
            ];

            // Generate PDF
            $pdf = Pdf::loadView('pdf.invoice-penagihan', $data);
            $pdf->setPaper('a4', 'portrait');

            // Clean invoice number for filename (remove / and \)
            $cleanInvoiceNumber = str_replace(['/', '\\'], '-', $invoice->invoice_number);

            // Download PDF
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, 'Invoice-' . $cleanInvoiceNumber . '.pdf');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal generate PDF: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // Calculate financial summary from order
        $order = $this->pengiriman->purchaseOrder ?? null;

        // Refresh pengiriman data untuk memastikan nilai terbaru
        $this->pengiriman->refresh();

        // Ambil total harga supplier (beli) dari total_harga_kirim pengiriman
        $totalSupplierCost = floatval($this->pengiriman->total_harga_kirim ?? 0);
        $totalSelling = 0;
        $totalMargin = 0;
        $marginPercentage = 0;

        // Hitung total harga jual berdasarkan qty kirim × harga jual
        if ($this->pengiriman->pengirimanDetails) {
            foreach ($this->pengiriman->pengirimanDetails as $detail) {
                // Ambil harga jual dari order detail
                $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
                if ($orderDetail && $orderDetail->harga_jual) {
                    // Total = qty kirim × harga jual
                    $totalSelling += floatval($detail->qty_kirim) * floatval($orderDetail->harga_jual);
                }
            }

            $totalMargin = $totalSelling - $totalSupplierCost;
            $marginPercentage = $totalSelling > 0 ? ($totalMargin / $totalSelling) * 100 : 0;
        }

        return view('livewire.accounting.detail-penagihan', [
            'order' => $order,
            'totalSupplierCost' => $totalSupplierCost,
            'totalSelling' => $totalSelling,
            'totalMargin' => $totalMargin,
            'marginPercentage' => $marginPercentage,
        ]);
    }
}
