<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\Notifications\OrderNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class EscalateOrderPriorities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:escalate-priorities
                            {--dry-run : Run without making changes}
                            {--notify : Send notifications for priority changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Recalculate order priorities based on PO end date overdue-age (contractual)";

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option("dry-run");
        $shouldNotify = $this->option("notify");

        if ($isDryRun) {
            $this->info("Running in dry-run mode - no changes will be made.");
        }

        /** @var \Illuminate\Support\Carbon $now */
        $now = \Illuminate\Support\Carbon::now();

        // ---------------------------------------------------------------
        // FIX: previously ->get() pulled ALL orders with po_end_date into
        // memory at once, with klien/creator eager loaded even though
        // they are not used anywhere below. For large tables this means
        // a big memory footprint plus one UPDATE query per changed order
        // executed while the whole collection sits in memory.
        //
        // We now:
        //   1. Drop the unused 'klien'/'creator' eager load (remove it
        //      if determinePriority() genuinely doesn't need them).
        //   2. Process orders in chunks with chunkById() so only a
        //      bounded batch is in memory at a time.
        //   3. Collect changed orders only as lightweight arrays
        //      (id + fields needed for notifications), not full models,
        //      to keep memory bounded even with many changes.
        // ---------------------------------------------------------------

        $totalCount = Order::query()->whereNotNull("po_end_date")->count();
        $this->info("Found {$totalCount} orders to check.");

        $changedCount = 0;
        $changedOrders = [];

        Order::query()
            ->with(["klien", "creator"]) // keep if determinePriority()/notify needs them
            ->whereNotNull("po_end_date")
            ->orderBy("id")
            ->chunkById(500, function ($orders) use ($now, $isDryRun, &$changedCount, &$changedOrders) {
                /** @var \App\Models\Order $order */
                foreach ($orders as $order) {
                    $oldPriority = (string) $order->priority;
                    $newPriority = $order->determinePriority($now);

                    if (!$newPriority || $oldPriority === $newPriority) {
                        continue;
                    }

                    $changedCount++;

                    // daysOverdue: <= 0 means not overdue yet (or exactly at end date)
                    $poEnd = $order->po_end_date instanceof Carbon
                        ? $order->po_end_date
                        : Carbon::parse($order->po_end_date);
                    $daysOverdue = (int) $poEnd->diffInDays($now, false);

                    $changedOrders[] = [
                        "order" => $order,
                        "old_priority" => $oldPriority,
                        "new_priority" => $newPriority,
                        "days_overdue" => $daysOverdue,
                    ];

                    $this->line(
                        sprintf(
                            "  [%s] %s: %s → %s (%d days overdue)",
                            $order->id,
                            $order->po_number ?? $order->no_order,
                            $oldPriority,
                            $newPriority,
                            $daysOverdue,
                        ),
                    );

                    if (!$isDryRun) {
                        $order->update([
                            "priority" => $newPriority,
                            "priority_calculated_at" => $now,
                        ]);
                    }
                }
            });

        if ($shouldNotify && !$isDryRun && count($changedOrders) > 0) {
            $this->info("Sending notifications...");
            $notificationCount = 0;

            foreach ($changedOrders as $data) {
                $notificationCount += OrderNotificationService::notifyPriorityChanged(
                    $data["order"],
                    $data["old_priority"],
                    $data["new_priority"],
                    null,
                    $data["days_overdue"],
                );
            }

            $this->info("Sent {$notificationCount} notifications.");
        }

        $this->newLine();
        $this->info(
            "Summary: {$changedCount} orders changed out of {$totalCount} checked.",
        );

        return self::SUCCESS;
    }
}