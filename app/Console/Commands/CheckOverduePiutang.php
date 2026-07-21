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
     * Cache notified IDs per type, loaded once per run instead of per-row.
     * Structure: [type => [id => true, id => true, ...]]
     */
    private array $notifiedCache = [];

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

        // Preload today's notified IDs ONCE for this type, instead of querying per-row
        $this->loadNotifiedIds(PiutangNotificationService::TYPE_SUPPLIER_OVERDUE, $today);

        $overdueSupplierPiutangs = CatatanPiutang::with('supplier')
            ->where('status', '!=', 'lunas')
            ->whereNotNull('tanggal_jatuh_tempo')
            ->whereDate('tanggal_jatuh_tempo', '<', $today)
            ->get();

        foreach ($overdueSupplierPiutangs as $piutang) {
            $daysOverdue = Carbon::parse($piutang->tanggal_jatuh_tempo)->diffInDays($today);

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

            $this->loadNotifiedIds(PiutangNotificationService::TYPE_SUPPLIER_NEAR_DUE, $today);

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

        $this->loadNotifiedIds(PiutangNotificationService::TYPE_PABRIK_OVERDUE, $today);

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

            $this->loadNotifiedIds(PiutangNotificationService::TYPE_PABRIK_NEAR_DUE, $today);

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
     * Load all today's notified model IDs for a given type in ONE query,
     * and cache the result in memory for the rest of this run.
     *
     * This replaces the old approach of running a LIKE query per row
     * inside the foreach loop (N+1 problem — this was the main cause
     * of the slowdown, since `data LIKE '%...%'` can't use an index
     * and forces a full table scan on every single call).
     */
    private function loadNotifiedIds(string $type, Carbon $today): array
    {
        if (isset($this->notifiedCache[$type])) {
            return $this->notifiedCache[$type];
        }

        $rows = DB::table('notifications')
            ->where('type', $type)
            ->whereDate('created_at', $today)
            ->pluck('data');

        $ids = [];

        foreach ($rows as $raw) {
            $decoded = json_decode($raw, true);

            if (!is_array($decoded)) {
                continue;
            }

            if (isset($decoded['piutang_id'])) {
                $ids[(int) $decoded['piutang_id']] = true;
            }

            if (isset($decoded['invoice_id'])) {
                $ids[(int) $decoded['invoice_id']] = true;
            }
        }

        return $this->notifiedCache[$type] = $ids;
    }

    /**
     * Check if notification already sent today for specific piutang.
     * Now an O(1) in-memory lookup instead of a DB query.
     */
    private function hasNotifiedToday(int $modelId, string $type): bool
    {
        return isset($this->notifiedCache[$type][$modelId]);
    }
}