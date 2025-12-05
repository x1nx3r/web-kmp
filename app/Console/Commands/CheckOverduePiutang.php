<?php

namespace App\Console\Commands;

use App\Models\CatatanPiutang;
use App\Models\InvoicePenagihan;
use App\Services\Notifications\PiutangNotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckOverduePiutang extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'piutang:check-overdue {--days=0 : Check piutang overdue by specific days} {--notify-near-due : Also notify near due}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and notify accounting about overdue piutang (supplier and pabrik)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking overdue piutang...');

        $today = Carbon::today();
        $notifyNearDue = $this->option('notify-near-due');

        $supplierOverdueCount = 0;
        $pabrikOverdueCount = 0;
        $supplierNearDueCount = 0;
        $pabrikNearDueCount = 0;

        // ==========================================
        // Check Piutang Supplier
        // ==========================================
        $this->info('Checking supplier piutang...');

        // Get overdue supplier piutang (belum lunas dan sudah melewati jatuh tempo)
        $overdueSupplierPiutangs = CatatanPiutang::with('supplier')
            ->where('status', '!=', 'lunas')
            ->whereNotNull('tanggal_jatuh_tempo')
            ->whereDate('tanggal_jatuh_tempo', '<', $today)
            ->get();

        foreach ($overdueSupplierPiutangs as $piutang) {
            $daysOverdue = Carbon::parse($piutang->tanggal_jatuh_tempo)->diffInDays($today);

            // Check if notification already sent today for this piutang
            $alreadyNotified = $this->hasNotifiedToday($piutang->id, PiutangNotificationService::TYPE_SUPPLIER_OVERDUE);

            if (!$alreadyNotified) {
                $count = PiutangNotificationService::notifySupplierOverdue($piutang, $daysOverdue);
                $supplierOverdueCount += $count;
                $this->line("  - Notified: {$piutang->supplier->nama} (Rp " . number_format($piutang->sisa_piutang, 0, ',', '.') . ") - {$daysOverdue} days overdue");
            }
        }

        // Check near due supplier piutang (3 hari sebelum jatuh tempo)
        if ($notifyNearDue) {
            $nearDueDate = $today->copy()->addDays(3);

            $nearDueSupplierPiutangs = CatatanPiutang::with('supplier')
                ->where('status', '!=', 'lunas')
                ->whereNotNull('tanggal_jatuh_tempo')
                ->whereDate('tanggal_jatuh_tempo', '>', $today)
                ->whereDate('tanggal_jatuh_tempo', '<=', $nearDueDate)
                ->get();

            foreach ($nearDueSupplierPiutangs as $piutang) {
                $daysUntilDue = $today->diffInDays(Carbon::parse($piutang->tanggal_jatuh_tempo));

                $alreadyNotified = $this->hasNotifiedToday($piutang->id, PiutangNotificationService::TYPE_SUPPLIER_NEAR_DUE);

                if (!$alreadyNotified) {
                    $count = PiutangNotificationService::notifySupplierNearDue($piutang, $daysUntilDue);
                    $supplierNearDueCount += $count;
                    $this->line("  - Near due: {$piutang->supplier->nama} (Rp " . number_format($piutang->sisa_piutang, 0, ',', '.') . ") - {$daysUntilDue} days until due");
                }
            }
        }

        // ==========================================
        // Check Piutang Pabrik (Invoice Penagihan)
        // ==========================================
        $this->info('Checking pabrik (klien) piutang...');

        // Get overdue pabrik piutang
        $overduePabrikInvoices = InvoicePenagihan::with(['pengiriman.klien', 'pembayaranPabrik'])
            ->where('status', '!=', 'paid')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', $today)
            ->get()
            ->filter(function ($invoice) {
                // Filter yang masih punya sisa hutang
                $totalPaid = $invoice->pembayaranPabrik->sum('jumlah_bayar');
                return $totalPaid < $invoice->total_amount;
            });

        foreach ($overduePabrikInvoices as $invoice) {
            $daysOverdue = Carbon::parse($invoice->due_date)->diffInDays($today);

            $alreadyNotified = $this->hasNotifiedToday($invoice->id, PiutangNotificationService::TYPE_PABRIK_OVERDUE);

            if (!$alreadyNotified) {
                $count = PiutangNotificationService::notifyPabrikOverdue($invoice, $daysOverdue);
                $pabrikOverdueCount += $count;
                $this->line("  - Notified: {$invoice->customer_name} ({$invoice->invoice_number}) - {$daysOverdue} days overdue");
            }
        }

        // Check near due pabrik piutang
        if ($notifyNearDue) {
            $nearDueDate = $today->copy()->addDays(3);

            $nearDuePabrikInvoices = InvoicePenagihan::with(['pengiriman.klien', 'pembayaranPabrik'])
                ->where('status', '!=', 'paid')
                ->whereNotNull('due_date')
                ->whereDate('due_date', '>', $today)
                ->whereDate('due_date', '<=', $nearDueDate)
                ->get()
                ->filter(function ($invoice) {
                    $totalPaid = $invoice->pembayaranPabrik->sum('jumlah_bayar');
                    return $totalPaid < $invoice->total_amount;
                });

            foreach ($nearDuePabrikInvoices as $invoice) {
                $daysUntilDue = $today->diffInDays(Carbon::parse($invoice->due_date));

                $alreadyNotified = $this->hasNotifiedToday($invoice->id, PiutangNotificationService::TYPE_PABRIK_NEAR_DUE);

                if (!$alreadyNotified) {
                    $count = PiutangNotificationService::notifyPabrikNearDue($invoice, $daysUntilDue);
                    $pabrikNearDueCount += $count;
                    $this->line("  - Near due: {$invoice->customer_name} ({$invoice->invoice_number}) - {$daysUntilDue} days until due");
                }
            }
        }

        // Summary
        $this->newLine();
        $this->info('Summary:');
        $this->table(
            ['Type', 'Notifications Sent'],
            [
                ['Supplier Overdue', $supplierOverdueCount],
                ['Pabrik Overdue', $pabrikOverdueCount],
                ['Supplier Near Due', $supplierNearDueCount],
                ['Pabrik Near Due', $pabrikNearDueCount],
                ['Total', $supplierOverdueCount + $pabrikOverdueCount + $supplierNearDueCount + $pabrikNearDueCount],
            ]
        );

        $this->info('Done!');

        return Command::SUCCESS;
    }

    /**
     * Check if notification already sent today for specific piutang.
     */
    private function hasNotifiedToday(int $modelId, string $type): bool
    {
        return DB::table('notifications')
            ->where('type', $type)
            ->whereDate('created_at', Carbon::today())
            ->where(function ($query) use ($modelId) {
                $query->where('data', 'like', '%"piutang_id":' . $modelId . '%')
                      ->orWhere('data', 'like', '%"invoice_id":' . $modelId . '%');
            })
            ->exists();
    }
}
