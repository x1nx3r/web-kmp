<?php
/**
 * PO Investigation: Export per-month order records with all relevant fields.
 * 
 * Run: php po-investigation/export-orders.php
 * Output: po-investigation/orders_by_month.csv
 *         po-investigation/order_details_all.csv
 */

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$outputDir = __DIR__;

// ============================================================
// 1. ORDERS (all with relevant joins)
// ============================================================
$orders = DB::table('orders')
    ->leftJoin('kliens', 'orders.klien_id', '=', 'kliens.id')
    ->leftJoin('users', 'orders.created_by', '=', 'users.id')
    ->leftJoin('order_winners', 'order_winners.order_id', '=', 'orders.id')
    ->leftJoin('users as winner_user', 'order_winners.user_id', '=', 'winner_user.id')
    ->whereNull('orders.deleted_at')
    ->select(
        'orders.id',
        'orders.no_order',
        'orders.po_number',
        'orders.status',
        'orders.priority',
        'orders.tanggal_order',
        'orders.po_start_date',
        'orders.po_end_date',
        'orders.total_amount as cached_total_amount',
        'orders.total_qty as cached_total_qty',
        'orders.total_items as cached_total_items',
        'kliens.nama as klien_nama',
        'kliens.cabang as klien_cabang',
        'users.nama as created_by_nama',
        'winner_user.nama as winner_nama',
        'orders.catatan',
        'orders.created_at',
        'orders.updated_at'
    )
    ->orderBy('orders.tanggal_order', 'asc')
    ->orderBy('orders.id', 'asc')
    ->get();

echo "Found {$orders->count()} orders.\n";

// ============================================================
// 2. ORDER DETAILS (all fields)
// ============================================================
$details = DB::table('order_details')
    ->join('orders', 'order_details.order_id', '=', 'orders.id')
    ->leftJoin('kliens', 'orders.klien_id', '=', 'kliens.id')
    ->leftJoin('bahan_baku_klien', 'order_details.bahan_baku_klien_id', '=', 'bahan_baku_klien.id')
    ->whereNull('orders.deleted_at')
    ->whereNull('order_details.deleted_at')
    ->select(
        'order_details.id as detail_id',
        'order_details.order_id',
        'orders.po_number',
        'orders.no_order',
        'orders.status as order_status',
        'orders.tanggal_order',
        'kliens.nama as klien_nama',
        'kliens.cabang as klien_cabang',
        DB::raw("COALESCE(bahan_baku_klien.nama, order_details.nama_material_po, '-') as material"),
        'order_details.qty as current_qty',
        'order_details.satuan',
        'order_details.harga_jual',
        'order_details.total_harga as current_total_harga',
        'order_details.qty_shipped',
        'order_details.total_shipped_quantity',
        'order_details.remaining_quantity',
        'order_details.status as detail_status',
        'order_details.created_at as detail_created_at',
        'order_details.updated_at as detail_updated_at'
    )
    ->orderBy('orders.tanggal_order', 'asc')
    ->orderBy('order_details.order_id', 'asc')
    ->orderBy('order_details.id', 'asc')
    ->get();

echo "Found {$details->count()} order details.\n";

// ============================================================
// 3. SHIPPED QTY per order_detail (from pengiriman_details)
// ============================================================
$shippedMap = DB::table('pengiriman_details')
    ->join('pengiriman', function ($j) {
        $j->on('pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
           ->whereNull('pengiriman.deleted_at')
           ->whereNotIn('pengiriman.status', ['gagal']);
    })
    ->whereNull('pengiriman_details.deleted_at')
    ->select(
        'pengiriman_details.purchase_order_bahan_baku_id as od_id',
        DB::raw('SUM(pengiriman_details.qty_kirim) as total_shipped_qty')
    )
    ->groupBy('pengiriman_details.purchase_order_bahan_baku_id')
    ->pluck('total_shipped_qty', 'od_id')
    ->toArray();

$shipCountMap = DB::table('pengiriman_details')
    ->join('pengiriman', function ($j) {
        $j->on('pengiriman.id', '=', 'pengiriman_details.pengiriman_id')
           ->whereNull('pengiriman.deleted_at')
           ->whereNotIn('pengiriman.status', ['gagal']);
    })
    ->whereNull('pengiriman_details.deleted_at')
    ->select(
        'pengiriman_details.purchase_order_bahan_baku_id as od_id',
        DB::raw('COUNT(DISTINCT pengiriman.id) as shipment_count')
    )
    ->groupBy('pengiriman_details.purchase_order_bahan_baku_id')
    ->pluck('shipment_count', 'od_id')
    ->toArray();

// ============================================================
// 4. FORECAST QTY per order_detail
// ============================================================
$forecastMap = DB::table('forecast_details')
    ->whereNull('deleted_at')
    ->select(
        'purchase_order_bahan_baku_id as od_id',
        DB::raw('SUM(qty_forecast) as total_forecast_qty'),
        DB::raw('COUNT(*) as forecast_count')
    )
    ->groupBy('purchase_order_bahan_baku_id')
    ->get()
    ->keyBy('od_id');

// ============================================================
// WRITE CSV 1: orders_by_month.csv
// ============================================================
$fp = fopen("{$outputDir}/orders_by_month.csv", 'w');
fputcsv($fp, [
    'order_month',
    'order_id',
    'no_order',
    'po_number',
    'status',
    'priority',
    'tanggal_order',
    'po_start_date',
    'po_end_date',
    'cached_total_amount',
    'cached_total_qty',
    'cached_total_items',
    'klien_nama',
    'klien_cabang',
    'created_by',
    'winner',
    'catatan',
    'created_at',
    'updated_at',
]);

foreach ($orders as $o) {
    $month = $o->tanggal_order
        ? Carbon::parse($o->tanggal_order)->format('Y-m')
        : 'no-date';

    fputcsv($fp, [
        $month,
        $o->id,
        $o->no_order,
        $o->po_number,
        $o->status,
        $o->priority,
        $o->tanggal_order,
        $o->po_start_date,
        $o->po_end_date,
        $o->cached_total_amount,
        $o->cached_total_qty,
        $o->cached_total_items,
        $o->klien_nama,
        $o->klien_cabang,
        $o->created_by_nama,
        $o->winner_nama,
        $o->catatan,
        $o->created_at,
        $o->updated_at,
    ]);
}
fclose($fp);
echo "✅ Written: {$outputDir}/orders_by_month.csv\n";

// ============================================================
// WRITE CSV 2: order_details_all.csv
// ============================================================
$fp2 = fopen("{$outputDir}/order_details_all.csv", 'w');
fputcsv($fp2, [
    'order_month',
    'detail_id',
    'order_id',
    'po_number',
    'no_order',
    'order_status',
    'tanggal_order',
    'klien_nama',
    'klien_cabang',
    'material',
    'current_qty',
    'satuan',
    'harga_jual',
    'current_total_harga',
    'shipped_qty_from_pengiriman',
    'shipment_count',
    'reconstructed_qty',
    'reconstructed_total_harga',
    'forecast_qty',
    'forecast_count',
    'qty_shipped_field',
    'total_shipped_quantity_field',
    'remaining_quantity_field',
    'detail_status',
    'detail_created_at',
    'detail_updated_at',
]);

foreach ($details as $d) {
    $month = $d->tanggal_order
        ? Carbon::parse($d->tanggal_order)->format('Y-m')
        : 'no-date';

    $shippedQty = $shippedMap[$d->detail_id] ?? 0;
    $shipCount = $shipCountMap[$d->detail_id] ?? 0;
    $reconstructedQty = (float)$d->current_qty + (float)$shippedQty;
    $reconstructedTotal = $reconstructedQty * (float)$d->harga_jual;
    $forecastInfo = $forecastMap[$d->detail_id] ?? null;
    $forecastQty = $forecastInfo ? $forecastInfo->total_forecast_qty : 0;
    $forecastCount = $forecastInfo ? $forecastInfo->forecast_count : 0;

    fputcsv($fp2, [
        $month,
        $d->detail_id,
        $d->order_id,
        $d->po_number,
        $d->no_order,
        $d->order_status,
        $d->tanggal_order,
        $d->klien_nama,
        $d->klien_cabang,
        $d->material,
        $d->current_qty,
        $d->satuan,
        $d->harga_jual,
        $d->current_total_harga,
        $shippedQty,
        $shipCount,
        $reconstructedQty,
        $reconstructedTotal,
        $forecastQty,
        $forecastCount,
        $d->qty_shipped,
        $d->total_shipped_quantity,
        $d->remaining_quantity,
        $d->detail_status,
        $d->detail_created_at,
        $d->detail_updated_at,
    ]);
}
fclose($fp2);
echo "✅ Written: {$outputDir}/order_details_all.csv\n";

// ============================================================
// SUMMARY
// ============================================================
$monthSummary = $orders->groupBy(function ($o) {
    return $o->tanggal_order
        ? Carbon::parse($o->tanggal_order)->format('Y-m')
        : 'no-date';
});

echo "\n=== PER-MONTH SUMMARY ===\n";
echo sprintf("%-10s | %5s | %15s | %s\n", 'Month', 'Count', 'Cached Total', 'Status Breakdown');
echo str_repeat('-', 85) . "\n";

foreach ($monthSummary->sortKeys() as $month => $group) {
    $statuses = $group->groupBy('status')->map->count()->toArray();
    $statusStr = implode(', ', array_map(fn($k, $v) => "{$k}:{$v}", array_keys($statuses), $statuses));
    echo sprintf("%-10s | %5d | %15s | %s\n",
        $month,
        $group->count(),
        number_format($group->sum('cached_total_amount')),
        $statusStr
    );
}

echo "\nDone! Files in: {$outputDir}/\n";
