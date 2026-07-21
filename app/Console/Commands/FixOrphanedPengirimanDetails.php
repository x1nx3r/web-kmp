<?php

namespace App\Console\Commands;

use App\Models\ForecastDetail;
use App\Models\OrderDetail;
use App\Models\PengirimanDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FixOrphanedPengirimanDetails extends Command
{
    protected $signature = 'fix:orphaned-order-detail-refs 
                            {--dry-run : Show what would be fixed without making changes}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Fix pengiriman_details and forecast_details that reference soft-deleted order_details by re-pointing them to the active order_detail for the same order';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE — no changes will be made');
            $this->newLine();
        }

        $totalFixed = 0;
        $totalUnfixable = 0;

        // ── 1. Fix pengiriman_details ──
        $this->info('━━━ pengiriman_details ━━━');
        [$fixed, $unfixable] = $this->fixTable(
            PengirimanDetail::class,
            'purchase_order_bahan_baku_id',
            'purchaseOrderBahanBaku',
            'pengiriman_id',
            $isDryRun
        );
        $totalFixed += $fixed;
        $totalUnfixable += $unfixable;

        $this->newLine();

        // ── 2. Fix forecast_details ──
        $this->info('━━━ forecast_details ━━━');
        [$fixed, $unfixable] = $this->fixTable(
            ForecastDetail::class,
            'purchase_order_bahan_baku_id',
            'purchaseOrderBahanBaku',
            'forecast_id',
            $isDryRun
        );
        $totalFixed += $fixed;
        $totalUnfixable += $unfixable;

        // ── Summary ──
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total fixable', $totalFixed],
                ['Total unfixable', $totalUnfixable],
            ]
        );

        if ($totalFixed === 0) {
            $this->info('✅ Nothing to fix. All references are healthy!');
            return 0;
        }

        if ($isDryRun) {
            $this->info('🔍 Dry run complete. Run without --dry-run to apply fixes.');
            return 0;
        }

        $this->info("✅ All fixes applied successfully.");
        return 0;
    }

    /**
     * Generic method to find and fix orphaned FK references in a given table.
     *
     * @return array [fixedCount, unfixableCount]
     */
    private function fixTable(
        string $modelClass,
        string $fkColumn,
        string $relationName,
        string $contextColumn,
        bool $isDryRun
    ): array {
        // Find records pointing to soft-deleted order_details
        $orphaned = $modelClass::whereHas($relationName, function ($q) {
            $q->onlyTrashed();
        })->with([$relationName => function ($q) {
            $q->withTrashed();
        }])->get();

        if ($orphaned->isEmpty()) {
            $this->info('  ✅ No orphans found.');
            return [0, 0];
        }

        $this->warn("  Found {$orphaned->count()} orphaned record(s)");

        // Group by the order_id of the deleted order_detail
        $grouped = $orphaned->groupBy(function ($record) use ($relationName) {
            return $record->{$relationName}->order_id ?? 'unknown';
        });

        // ---------------------------------------------------------------
        // FIX: previously OrderDetail::where('order_id', $orderId)->first()
        // was run INSIDE the foreach loop -> one query per distinct
        // order_id (N+1 problem).
        //
        // Now we collect all distinct order_ids first, then fetch all
        // the active order_details for those orders in a SINGLE query,
        // keyed by order_id for O(1) lookup inside the loop below.
        // ---------------------------------------------------------------
        $orderIds = $grouped->keys()->filter(fn ($id) => $id !== 'unknown')->values();

        $activeDetailsByOrder = OrderDetail::whereIn('order_id', $orderIds)
            ->get()
            ->groupBy('order_id')
            ->map(fn ($details) => $details->first()); // first active detail per order

        $fixes = [];
        $unfixable = 0;

        foreach ($grouped as $orderId => $records) {
            $activeDetail = $orderId !== 'unknown' ? ($activeDetailsByOrder[$orderId] ?? null) : null;
            $deletedIds = $records->pluck("{$relationName}.id")->unique()->implode(', ');

            if ($activeDetail) {
                $this->line("  Order #{$orderId} — {$records->count()} record(s) → will re-point to OD #{$activeDetail->id} ({$activeDetail->nama_material_po})");

                foreach ($records as $record) {
                    $fixes[$record->id] = $activeDetail->id;
                }
            } else {
                $this->error("  Order #{$orderId} — {$records->count()} record(s) → NO active detail (unfixable)");
                $unfixable += $records->count();
            }
        }

        $fixable = count($fixes);

        if ($fixable === 0 || $isDryRun) {
            return [$fixable, $unfixable];
        }

        // Confirm before applying
        if (!$this->option('force') && !$this->confirm("  Apply {$fixable} fix(es) to " . class_basename($modelClass) . "?")) {
            $this->info('  Skipped.');
            return [0, $unfixable];
        }

        // ---------------------------------------------------------------
        // Apply fixes in a transaction. Grouped by target order_detail_id
        // so records sharing the same target are updated with a single
        // whereIn(...)->update(...) call instead of one UPDATE per row.
        // ---------------------------------------------------------------
        DB::beginTransaction();
        try {
            $updated = 0;

            $fixesByTarget = collect($fixes)->groupBy(fn ($targetId) => $targetId);

            foreach ($fixesByTarget as $targetOrderDetailId => $group) {
                $recordIds = $group->keys()->all();

                $modelClass::whereIn('id', $recordIds)
                    ->update([$fkColumn => $targetOrderDetailId]);

                $updated += count($recordIds);
            }

            DB::commit();

            $this->info("  ✅ Fixed {$fixable} record(s)");
            Log::info("FixOrphanedOrderDetailRefs: Fixed {$fixable} " . class_basename($modelClass) . " records", [
                'fixes' => $fixes,
            ]);

            return [$fixable, $unfixable];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("  ❌ Failed: {$e->getMessage()}");
            Log::error("FixOrphanedOrderDetailRefs: Failed on " . class_basename($modelClass), ['error' => $e->getMessage()]);
            return [0, $unfixable];
        }
    }
}