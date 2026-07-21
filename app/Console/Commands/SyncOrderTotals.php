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
        $withTrashed = $this->option('all');
        $isDryRun = $this->option('dry-run');

        $totalCount = 0;
        $updated = 0;
        $mismatched = 0;

        // ---------------------------------------------------------------
        // FIX: previously $order->orderDetails was accessed inside the
        // foreach loop WITHOUT eager loading -> classic N+1 problem
        // (1 query for orders + N queries for details, one per order).
        //
        // We now:
        //   1. Process orders in chunks (chunkById) instead of loading
        //      everything into memory at once.
        //   2. Eager load 'orderDetails' for each chunk so all detail
        //      rows for that chunk are fetched in a single query.
        // ---------------------------------------------------------------

        $query = Order::query()->with('orderDetails');

        if ($withTrashed) {
            $query->withTrashed();
        }

        // Count first (cheap, single query) just for the summary/info line.
        $totalCount = (clone $query)->toBase()->getCountForPagination();
        $this->info("Checking {$totalCount} orders...");

        $query->chunkById(500, function ($orders) use ($isDryRun, &$updated, &$mismatched) {
            foreach ($orders as $order) {
                // Calculate what it SHOULD be
                $currentTotal = floatval($order->total_amount);
                $currentItems = intval($order->total_items);
                $currentQty = floatval($order->total_qty);

                // orderDetails already eager-loaded for this chunk, no extra query
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

                    if (!$isDryRun) {
                        $order->calculateTotals();
                        $updated++;
                    }
                }
            }
        });

        $this->info("Summary:");
        $this->info("  Total Checked: {$totalCount}");
        $this->info("  Mismatched Found: {$mismatched}");
        if ($isDryRun) {
            $this->info("  Dry run complete. No changes made.");
        } else {
            $this->info("  Successfully updated: {$updated}");
        }

        return Command::SUCCESS;
    }
}