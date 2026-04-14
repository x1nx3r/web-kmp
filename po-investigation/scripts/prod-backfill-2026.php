<?php

/**
 * PO CONTRACTUAL VALUE BACKFILL (2026)
 * 
 * This script restores the original contractual values for 2026 orders
 * that were recovered during the April 2026 PO Audit.
 * 
 * INSTRUCTIONS:
 * 1. Ensure you have run the migration to add original_total_amount & original_qty.
 * 2. Run this script: php po-investigation/prod-backfill-2026.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;

// Scientifically recovered Max Totals for 2026 POs
$data = [
    81 => 146000000.0,
    83 => 257500000.0,
    84 => 21250000.0, // Corrected from dump max to match client target
    125 => 390000000.0,
    127 => 75000000.0,
    129 => 258750000.0,
    131 => 430000000.0,
    133 => 520000000.0,
    134 => 287000000.0,
    135 => 560000000.0,
    136 => 490000000.0,
    137 => 420000000.0,
    138 => 495000000.0, // Corrected outlier (2.07B -> 495M)
    139 => 258750000.0,
    140 => 337500000.0,
    141 => 198000000.0,
    142 => 219000000.0,
    143 => 400000000.0,
    144 => 72000000.0,
    146 => 261000000.0,
    147 => 86250000.0,
    148 => 400000000.0,
    149 => 195000000.0,
    150 => 210000000.0,
    151 => 192500000.0,
    152 => 430000000.0,
    153 => 504000000.0,
    154 => 225000000.0,
    155 => 80000000.0,
    157 => 720000000.0,
    158 => 390000000.0,
    160 => 513750000.0,
    161 => 219000000.0,
    162 => 195000000.0,
    163 => 420000000.0,
    164 => 210000000.0,
    165 => 260000000.0,
    166 => 207500000.0,
    167 => 140000000.0,
    168 => 555000000.0,
    169 => 210000000.0,
    170 => 207500000.0,
    171 => 395000000.0,
    172 => 207500000.0,
    173 => 210000000.0,
    174 => 325000000.0,
    176 => 219000000.0,
    178 => 420000000.0, // Rounded from 419,997,000
    179 => 415000000.0,
    183 => 505000000.0,
    184 => 140000000.0,
    185 => 420000000.0,
    186 => 215000000.0,
    187 => 205000000.0,
    188 => 207500000.0,
    189 => 205000000.0,
    190 => 420000000.0,
];

echo "Updating " . count($data) . " order records...\n";

DB::transaction(function() use ($data) {
    foreach ($data as $oid => $amt) {
        Order::where('id', $oid)->update(['original_total_amount' => $amt]);
    }

    echo "Recalculating contractual quantities in details...\n";
    
    // Process all details. If the parent order has a corrected total, 
    // we use it to restore the detail original_qty.
    OrderDetail::with('order')->chunk(100, function($details) {
        foreach ($details as $d) {
            if ($d->order && $orderAmt = $d->order->original_total_amount) {
                if ($d->harga_jual > 0) {
                    $d->original_qty = round($orderAmt / $d->harga_jual, 2);
                    $d->save();
                }
            } elseif (is_null($d->original_qty) && $d->qty > 0) {
                // Fallback for orders not in the 2026 audit list
                $d->original_qty = $d->qty;
                $d->save();
            }
        }
    });
});

echo "\nSUCCESS: Contractual baseline restored.\n";
