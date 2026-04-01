<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncOrderTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:sync-totals {--all : Sync all orders including soft-deleted ones} {--dry-run : Show changes without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate total_amount, total_items, and total_qty for orders from their details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Order::query();
        
        if ($this->option('all')) {
            $query->withTrashed();
        }

        $orders = $query->get();
        $count = $orders->count();
        $updated = 0;
        $mismatched = 0;

        $this->info("Checking {$count} orders...");

        foreach ($orders as $order) {
            // Calculate what it SHOULD be
            $currentTotal = floatval($order->total_amount);
            $currentItems = intval($order->total_items);
            $currentQty = floatval($order->total_qty);

            // Temporarily calculate without saving
            $details = $order->orderDetails;
            $newTotal = floatval($details->sum('total_harga'));
            $newItems = intval($details->count());
            $newQty = floatval($details->sum('qty'));

            if (abs($currentTotal - $newTotal) > 0.01 || $currentItems !== $newItems || abs($currentQty - $newQty) > 0.01) {
                $mismatched++;
                $this->line("Mismatched Order [{$order->id}] {$order->po_number}: ");
                $this->line("  Total: Rp {$currentTotal} -> Rp {$newTotal}");
                $this->line("  Items: {$currentItems} -> {$newItems}");
                $this->line("  Qty:   {$currentQty} -> {$newQty}");

                if (!$this->option('dry-run')) {
                    $order->calculateTotals();
                    $updated++;
                }
            }
        }

        $this->info("Summary:");
        $this->info("  Total Checked: {$count}");
        $this->info("  Mismatched Found: {$mismatched}");
        if ($this->option('dry-run')) {
            $this->info("  Dry run complete. No changes made.");
        } else {
            $this->info("  Successfully updated: {$updated}");
        }
    }
}
