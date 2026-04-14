<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * PO Investigation: Database Backfill Script
 * -----------------------------------------
 * This script restores historical "as-contracted" values into the original_total_amount
 * and original_qty fields to prevent revenue shrinkage in reports.
 * 
 * Usage:
 *   php po-investigation/apply_backfill.php           # Run live update
 *   php po-investigation/apply_backfill.php --dry-run # Preview changes
 */

$manifestPath = 'po-investigation/backfill_manifest_dec_apr.csv';
$dryRun = in_array('--dry-run', $argv);

if (!file_exists($manifestPath)) {
    die("Error: Manifest file not found at $manifestPath\n");
}

$handle = fopen($manifestPath, 'r');
$header = fgetcsv($handle); // Skip header

echo "Starting Database Backfill (Dec 2025 - Apr 2026)...\n";
echo "Mode: " . ($dryRun ? "DRY RUN (No changes will be saved)" : "LIVE UPDATE") . "\n";
echo "--------------------------------------------------\n";

$updatedCount = 0;
$errorCount = 0;
$warningCount = 0;

if (!$dryRun) {
    DB::beginTransaction();
}

try {
    while (($row = fgetcsv($handle)) !== false) {
        if (empty($row[0])) continue;
        
        $orderId = $row[0];
        $expectedPo = trim($row[1]);
        $totalAmount = $row[2];
        $qty = $row[3];

        // Include trashed orders since historical audits often include cancelled/deleted items
        $order = Order::withTrashed()->find($orderId);

        if (!$order) {
            echo "ERROR: Order ID $orderId not found in database. Skipping.\n";
            $errorCount++;
            continue;
        }

        // Robust matching with trim to handle manual entry inconsistencies
        if (trim($order->po_number) !== $expectedPo) {
            echo "ERROR: PO mismatch for ID $orderId. Expected '$expectedPo', found '{$order->po_number}'. Skipping.\n";
            $errorCount++;
            continue;
        }

        // Fetch details (including trashed) to check for multi-item orders
        $details = $order->orderDetails()->withTrashed()->get();
        
        // Selection Priority: 1. Active Details, 2. First Created Detail
        $selectedDetail = $details->whereNull('deleted_at')->first() ?? $details->first();
        $detailStatus = $selectedDetail ? ($selectedDetail->deleted_at ? "[DELETED]" : "[ACTIVE]") : "[NONE]";

        if ($details->count() > 1) {
            $msg = "INFO: Order ID $orderId has " . $details->count() . " items. ";
            $msg .= "Choice: " . ($selectedDetail ? "Detail ID {$selectedDetail->id} $detailStatus" : "None found") . "\n";
            echo $msg;
            $warningCount++;
        }

        if ($details->isEmpty()) {
            echo "WARNING: Order ID $orderId has NO items. Skipping original_qty update.\n";
            $warningCount++;
        }

        if (!$dryRun) {
            // Update Order
            $order->original_total_amount = $totalAmount;
            $order->save();

            // Apply to selected detail
            if ($selectedDetail) {
                $selectedDetail->original_qty = $qty;
                $selectedDetail->save();
            }
        }

        $label = $dryRun ? "PREVIEW" : "SUCCESS";
        echo "[$label] ID $orderId ($expectedPo) -> " . number_format($totalAmount) . " | " . number_format($qty) . " kg\n";
        $updatedCount++;
    }

    if (!$dryRun) {
        DB::commit();
        echo "--------------------------------------------------\n";
        echo "Backfill Complete!\n";
    } else {
        echo "--------------------------------------------------\n";
        echo "Dry Run Finished (No data changed).\n";
    }
    
    echo "Processed: $updatedCount orders\n";
    echo "Complexity Warnings: $warningCount\n";
    echo "Errors: $errorCount\n";

} catch (\Exception $e) {
    if (!$dryRun && DB::transactionLevel() > 0) {
        DB::rollBack();
    }
    echo "\nFATAL ERROR: " . $e->getMessage() . "\n";
    echo "Check logs for details.\n";
    die();
}

fclose($handle);
