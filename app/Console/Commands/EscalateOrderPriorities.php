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
                            {--notify : Send notifications for escalated orders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Automatically escalate order priorities based on PO end date proximity";

    /**
     * Priority thresholds in days.
     */
    protected const PRIORITY_THRESHOLDS = [
        "mendesak" => 3, // ≤3 days
        "tinggi" => 7, // ≤7 days
        "normal" => 14, // ≤14 days
        "rendah" => PHP_INT_MAX, // >14 days
    ];

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

        // Get all active orders (not selesai or dibatalkan) with a PO end date
        $orders = Order::with(["klien", "creator"])
            ->whereNotIn("status", ["selesai", "dibatalkan"])
            ->whereNotNull("po_end_date")
            ->get();

        $this->info("Found {$orders->count()} active orders to check.");

        $escalatedCount = 0;
        $escalatedOrders = [];

        foreach ($orders as $order) {
            $oldPriority = $order->priority;
            $newPriority = $this->calculatePriority($order->po_end_date);

            // Check if priority should be escalated (only escalate, never de-escalate)
            if ($this->shouldEscalate($oldPriority, $newPriority)) {
                $escalatedCount++;
                $daysRemaining = $this->getDaysRemaining($order->po_end_date);

                $escalatedOrders[] = [
                    "order" => $order,
                    "old_priority" => $oldPriority,
                    "new_priority" => $newPriority,
                    "days_remaining" => $daysRemaining,
                ];

                $this->line(
                    sprintf(
                        "  [%s] %s: %s → %s (%d days remaining)",
                        $order->id,
                        $order->po_number ?? $order->no_order,
                        $oldPriority,
                        $newPriority,
                        $daysRemaining,
                    ),
                );

                if (!$isDryRun) {
                    $order->update([
                        "priority" => $newPriority,
                        "priority_calculated_at" => now(),
                    ]);
                }
            }
        }

        // Send notifications for escalated orders
        if ($shouldNotify && !$isDryRun && count($escalatedOrders) > 0) {
            $this->info("Sending notifications...");
            $notificationCount = $this->sendEscalationNotifications(
                $escalatedOrders,
            );
            $this->info("Sent {$notificationCount} notifications.");
        }

        $this->newLine();
        $this->info(
            "Summary: {$escalatedCount} orders escalated out of {$orders->count()} checked.",
        );

        return self::SUCCESS;
    }

    /**
     * Calculate priority based on days remaining until PO end date.
     */
    protected function calculatePriority(string $poEndDate): string
    {
        $days = $this->getDaysRemaining($poEndDate);

        if ($days <= self::PRIORITY_THRESHOLDS["mendesak"]) {
            return "mendesak";
        } elseif ($days <= self::PRIORITY_THRESHOLDS["tinggi"]) {
            return "tinggi";
        } elseif ($days <= self::PRIORITY_THRESHOLDS["normal"]) {
            return "normal";
        }

        return "rendah";
    }

    /**
     * Get remaining days until PO end date.
     */
    protected function getDaysRemaining(string $poEndDate): int
    {
        $end = Carbon::parse($poEndDate);
        return (int) now()->diffInDays($end, false);
    }

    /**
     * Check if priority should be escalated.
     * Only returns true if new priority is higher than old priority.
     */
    protected function shouldEscalate(
        string $oldPriority,
        string $newPriority,
    ): bool {
        $priorityLevels = OrderNotificationService::PRIORITY_LEVELS;

        $oldLevel = $priorityLevels[$oldPriority] ?? 0;
        $newLevel = $priorityLevels[$newPriority] ?? 0;

        return $newLevel > $oldLevel;
    }

    /**
     * Send notifications for escalated orders.
     *
     * @param array $escalatedOrders
     * @return int Number of notifications sent
     */
    protected function sendEscalationNotifications(array $escalatedOrders): int
    {
        $totalSent = 0;

        foreach ($escalatedOrders as $data) {
            $count = OrderNotificationService::notifyPriorityEscalated(
                $data["order"],
                $data["old_priority"],
                $data["new_priority"],
                null, // System-triggered, no user
                $data["days_remaining"],
            );
            $totalSent += $count;
        }

        return $totalSent;
    }
}
