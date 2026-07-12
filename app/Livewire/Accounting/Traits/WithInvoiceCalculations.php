<?php

namespace App\Livewire\Accounting\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait WithInvoiceCalculations
{
    public function updateRefraksi()
    {
        if (!$this->ensureCanManage()) return;
        if (!$this->invoice) { session()->flash('error', 'Data invoice tidak ditemukan'); return; }

        DB::beginTransaction();
        try {
            $totalSelling = 0;
            $qtyBeforeRefraksi = 0;
            
            $isMerged = $this->invoice->pengirimans && $this->invoice->pengirimans->count() > 0;
            $shipments = $isMerged ? $this->invoice->pengirimans : collect([$this->pengiriman]);
            
            foreach ($shipments as $s) {
                if (!$s) continue;
                $qtyBeforeRefraksi += floatval($s->total_qty_kirim);
                $details = $s->pengirimanDetails ?? $s->details ?? collect();
                foreach ($details as $detail) {
                    $orderDetail = $detail->purchaseOrderBahanBaku ?? $detail->orderDetail;
                    if ($orderDetail && $orderDetail->harga_jual) {
                        $totalSelling += floatval($detail->qty_kirim) * floatval($orderDetail->harga_jual);
                    }
                }
            }

            $refraksiValue = floatval($this->refraksiForm['value'] ?? 0);
            $updateData = [
                'amount_before_refraksi' => $totalSelling,
                'qty_before_refraksi'    => $qtyBeforeRefraksi,
            ];

            if ($refraksiValue <= 0) {
                $updateData = array_merge($updateData, [
                    'refraksi_type'         => null,
                    'refraksi_value'        => 0,
                    'refraksi_amount'       => 0,
                    'qty_after_refraksi'    => $qtyBeforeRefraksi,
                    'amount_after_refraksi' => $totalSelling,
                    'subtotal'              => $totalSelling,
                    'total_amount'          => max(0, $totalSelling + floatval($this->invoice->additional_expenses_total ?? 0) + floatval($this->invoice->tax_amount ?? 0) - floatval($this->invoice->discount_amount ?? 0)),
                ]);
            } else {
                $refraksiType = $this->refraksiForm['type'];
                $refraksiAmount = 0;
                $subtotal = $totalSelling;
                $qtyAfterRefraksi = $qtyBeforeRefraksi;

                if ($refraksiType === 'qty') {
                    $refraksiQty = $qtyBeforeRefraksi * ($refraksiValue / 100);
                    $qtyAfterRefraksi = $qtyBeforeRefraksi - $refraksiQty;
                    $refraksiAmount = $refraksiQty * ($qtyBeforeRefraksi > 0 ? $subtotal / $qtyBeforeRefraksi : 0);
                } elseif ($refraksiType === 'rupiah') {
                    $refraksiAmount = $refraksiValue * $qtyBeforeRefraksi;
                } else {
                    $refraksiAmount = $refraksiValue;
                }

                $subtotal -= $refraksiAmount;
                $updateData = array_merge($updateData, [
                    'refraksi_type'         => $refraksiType,
                    'refraksi_value'        => $refraksiValue,
                    'refraksi_amount'       => $refraksiAmount,
                    'qty_after_refraksi'    => $qtyAfterRefraksi,
                    'amount_after_refraksi' => $subtotal,
                    'subtotal'              => $subtotal,
                    'total_amount'          => max(0, $subtotal + floatval($this->invoice->additional_expenses_total ?? 0) + floatval($this->invoice->tax_amount ?? 0) - floatval($this->invoice->discount_amount ?? 0)),
                ]);
            }

            $this->invoice->update($updateData);

            if (isset($this->editMode) && $this->editMode && $this->approval->status === 'completed') {
                $this->logInvoiceHistory(
                    $this->approval->id, $this->approval->pengiriman_id, $this->invoice->id, 'edited',
                    'Refraksi diubah: ' . ($refraksiValue <= 0 ? 'tidak ada' : $this->refraksiForm['type'] . ' - ' . $refraksiValue)
                );
            }

            DB::commit();
            session()->flash('message', 'Refraksi berhasil diupdate');
            $this->invoice->refresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Refraksi Error: ' . $e->getMessage());
            session()->flash('error', 'Gagal mengupdate refraksi: ' . $e->getMessage());
        }
    }

    public function updateRefraksiPerItem()
    {
        $this->validate([
            'refraksiPerItem.*.type'   => 'nullable|in:qty,rupiah,lainnya',
            'refraksiPerItem.*.value'  => 'nullable|numeric|min:0',
            'refraksiPerItem.*.amount' => 'nullable|numeric|min:0',
        ]);

        if (!$this->ensureCanManage()) return;

        DB::beginTransaction();
        try {
            $items = $this->invoice->items ?? [];
            if (empty($items)) {
                $items = [[
                    'item_name' => 'Pengiriman #1',
                    'amount'    => (float) ($this->invoice->amount_before_refraksi ?? $this->invoice->subtotal ?? 0),
                    'details'   => [], 'expenses' => [],
                ]];
            }

            $totalSellingPrice = 0; $totalRefraksiAmount = 0; $totalRefraksiQty = 0; $totalQty = 0;

            foreach ($items as $i => &$item) {
                $type  = $this->refraksiPerItem[$i]['type']  ?? 'qty';
                $value = (float) ($this->refraksiPerItem[$i]['value'] ?? 0);
                
                $item['refraksi_type']  = $type;
                $item['refraksi_value'] = $value;
                $total = (float) ($this->refraksiPerItem[$i]['amount'] ?? $item['amount'] ?? $item['total'] ?? 0);
                $item['amount'] = $total;
                $totalSellingPrice += $total;

                $qty = 0;
                $shipments = ($this->invoice->pengirimans && $this->invoice->pengirimans->count() > 0) ? $this->invoice->pengirimans : collect([$this->pengiriman]);
                $shipmentArray = $shipments->values();
                if (isset($shipmentArray[$i])) $qty = floatval($shipmentArray[$i]->total_qty_kirim ?? 0);
                if ($qty <= 0) $qty = floatval($item['quantity'] ?? 0);
                if ($qty <= 0) {
                    foreach ($item['details'] ?? [] as $d) $qty += floatval($d['qty_kirim'] ?? $d['qty'] ?? 0);
                }
                $totalQty += $qty;

                if ($value > 0) {
                    if ($type === 'qty' && $qty > 0) {
                        $refQty = $qty * ($value / 100);
                        $item['refraksi_amount'] = $refQty * ($total / $qty);
                        $totalRefraksiQty += $refQty;
                    } elseif ($type === 'rupiah' && $qty > 0) {
                        $item['refraksi_amount'] = $value * $qty;
                    } else {
                        $item['refraksi_amount'] = $value;
                    }
                } else {
                    $item['refraksi_amount'] = 0;
                }
                $totalRefraksiAmount += (float) $item['refraksi_amount'];
            }
            unset($item);

            $amountAfterRefraksi = $totalSellingPrice - $totalRefraksiAmount;
            $expensesTotal = array_reduce($items, fn($carry, $item) => $carry + collect($item['expenses'] ?? [])->sum('amount'), 0);

            $this->invoice->update([
                'items'                     => $items,
                'refraksi_type'             => $this->refraksiPerItem[0]['type'] ?? 'qty',
                'refraksi_value'            => (float) ($this->refraksiPerItem[0]['value'] ?? 0),
                'refraksi_amount'           => $totalRefraksiAmount,
                'qty_before_refraksi'       => $totalQty,
                'qty_after_refraksi'        => $totalQty - $totalRefraksiQty,
                'amount_before_refraksi'    => $totalSellingPrice,
                'amount_after_refraksi'     => $amountAfterRefraksi,
                'subtotal'                  => $amountAfterRefraksi,
                'additional_expenses_total' => $expensesTotal,
                'total_amount'              => max(0, $amountAfterRefraksi + $expensesTotal + floatval($this->invoice->tax_amount ?? 0) - floatval($this->invoice->discount_amount ?? 0)),
            ]);

            if (isset($this->editMode) && $this->editMode && $this->approval->status === 'completed') {
                $this->logInvoiceHistory($this->approval->id, $this->approval->pengiriman_id, $this->invoice->id, 'edited', 'Update harga jual dan refraksi per pengiriman');
            }

            DB::commit();
            session()->flash('message', 'Harga jual / refraksi per pengiriman berhasil diupdate');
            $this->invoice->refresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Refraksi Per Item Error: ' . $e->getMessage());
            session()->flash('error', 'Gagal update refraksi: ' . $e->getMessage());
        }
    }

    public function updateExpensesPerItem()
    {
        if (!$this->ensureCanManage()) return;
        if (empty($this->expensePerItem)) { session()->flash('error', 'Tidak ada data pengeluaran'); return; }

        foreach ($this->expensePerItem as $i => $exp) {
            foreach (['truk', 'kuli', 'fee'] as $k) {
                if (floatval($exp[$k] ?? 0) < 0) { session()->flash('error', 'Item #' . ($i + 1) . ': ' . ucfirst($k) . ' tidak boleh negatif'); return; }
            }
            foreach (($exp['others'] ?? []) as $j => $row) {
                $amount = floatval($row['amount'] ?? 0);
                $type = trim((string) ($row['type'] ?? ''));
                if ($amount < 0) { session()->flash('error', 'Item #' . ($i + 1) . ', baris #' . ($j + 1) . ': nominal tidak boleh negatif'); return; }
                if ($amount > 0 && $type === '') { session()->flash('error', 'Item #' . ($i + 1) . ', baris #' . ($j + 1) . ': nama pengeluaran wajib diisi'); return; }
            }
        }

        DB::beginTransaction();
        try {
            $items = $this->invoice->items ?? [];
            $allExpensesFlat = [];
            $expensesTotal = 0;

            foreach ($items as $i => &$item) {
                $exp = $this->expensePerItem[$i] ?? [];
                $itemExpenses = [];

                foreach (['truk', 'kuli', 'fee'] as $type) {
                    if (($amount = floatval($exp[$type] ?? 0)) > 0) {
                        $itemExpenses[] = ['type' => $type, 'amount' => $amount];
                        $expensesTotal += $amount;
                    }
                }
                foreach (($exp['others'] ?? []) as $row) {
                    $type = trim((string) ($row['type'] ?? ''));
                    $amount = floatval($row['amount'] ?? 0);
                    if ($type === '' || $amount <= 0) continue;
                    $itemExpenses[] = ['type' => $type, 'amount' => $amount];
                    $expensesTotal += $amount;
                }
                $item['expenses'] = $itemExpenses;
                $allExpensesFlat = array_merge($allExpensesFlat, $itemExpenses);
            }
            unset($item);

            $amountAfterRefraksi = collect($items)->sum(fn($i) => (float)($i['amount'] ?? 0)) - collect($items)->sum(fn($i) => (float)($i['refraksi_amount'] ?? 0));

            $this->invoice->update([
                'items' => $items,
                'additional_expenses_total' => $expensesTotal,
                'subtotal' => $amountAfterRefraksi,
                'total_amount' => max(0, $amountAfterRefraksi + $expensesTotal + floatval($this->invoice->tax_amount ?? 0) - floatval($this->invoice->discount_amount ?? 0))
            ]);

            $this->invoice->expenses()->delete();
            foreach ($allExpensesFlat as $e) $this->invoice->expenses()->create($e);

            if (isset($this->editMode) && $this->editMode && $this->approval->status === 'completed') {
                $this->logInvoiceHistory($this->approval->id, $this->approval->pengiriman_id, $this->invoice->id, 'edited', 'Pengeluaran tambahan per pengiriman diubah');
            }

            DB::commit();
            session()->flash('message', 'Pengeluaran tambahan per pengiriman berhasil disimpan');
            $this->invoice->refresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Expenses Per Item Error: ' . $e->getMessage());
            session()->flash('error', 'Gagal menyimpan pengeluaran: ' . $e->getMessage());
        }
    }

    public function addOtherExpenseRow($itemIndex): void
    {
        if (!$this->ensureCanManage()) return;
        if (isset($this->expensePerItem[$itemIndex])) $this->expensePerItem[$itemIndex]['others'][] = ['type' => '', 'amount' => 0];
    }

    public function removeOtherExpenseRow($itemIndex, $rowIndex): void
    {
        if (!$this->ensureCanManage()) return;
        if (isset($this->expensePerItem[$itemIndex]['others'][$rowIndex])) {
            array_splice($this->expensePerItem[$itemIndex]['others'], $rowIndex, 1);
            if (empty($this->expensePerItem[$itemIndex]['others'])) $this->expensePerItem[$itemIndex]['others'][] = ['type' => '', 'amount' => 0];
            $this->updateExpensesPerItem();
        }
    }
}