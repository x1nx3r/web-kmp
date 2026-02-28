<?php

namespace Tests\Feature;

use App\Models\BahanBakuKlien;
use App\Models\BahanBakuSupplier;
use App\Models\Forecast;
use App\Models\ForecastDetail;
use App\Models\Klien;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Pengiriman;
use App\Models\PengirimanDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixOrphanedOrderDetailRefsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Klien $klien;
    private BahanBakuKlien $material;
    private BahanBakuSupplier $supplierMaterial;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'marketing']);
        $this->klien = Klien::factory()->create();
        $this->material = BahanBakuKlien::factory()->create([
            'klien_id' => $this->klien->id,
            'nama' => 'Test Material',
            'satuan' => 'kg',
        ]);
        $this->supplierMaterial = BahanBakuSupplier::factory()->create([
            'nama' => 'Test Supplier Material',
        ]);
    }

    /**
     * Create an order with one active and optional soft-deleted details.
     */
    private function createOrderWithOrphans(int $orphanCount = 2): array
    {
        $order = Order::factory()->create([
            'klien_id' => $this->klien->id,
            'created_by' => $this->user->id,
            'status' => 'diproses',
            'po_number' => 'PO-TEST-' . rand(100, 999),
            'po_start_date' => now(),
            'po_end_date' => now()->addDays(30),
        ]);

        // Create old (deleted) detail
        $deletedDetail = OrderDetail::factory()->create([
            'order_id' => $order->id,
            'bahan_baku_klien_id' => $this->material->id,
            'nama_material_po' => 'Old Name',
            'qty' => 1000,
            'harga_jual' => 4000,
        ]);

        // Create orphaned pengiriman_details pointing to the soon-to-be-deleted detail
        $orphanedPengDetails = [];
        for ($i = 0; $i < $orphanCount; $i++) {
            $forecast = Forecast::factory()->create([
                'purchase_order_id' => $order->id,
                'purchasing_id' => $this->user->id,
            ]);

            $pengiriman = Pengiriman::create([
                'purchase_order_id' => $order->id,
                'forecast_id' => $forecast->id,
                'no_pengiriman' => 'PGR/' . now()->format('Ym') . '/' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'tanggal_kirim' => now(),
                'total_qty_kirim' => 500,
                'total_harga_kirim' => 1950000,
                'status' => 'berhasil',
            ]);

            $orphanedPengDetails[] = PengirimanDetail::create([
                'pengiriman_id' => $pengiriman->id,
                'purchase_order_bahan_baku_id' => $deletedDetail->id,
                'bahan_baku_supplier_id' => $this->supplierMaterial->id,
                'qty_kirim' => 500,
                'harga_satuan' => 3900,
                'total_harga' => 1950000,
            ]);
        }

        // Soft-delete the old detail
        $deletedDetail->delete();

        // Create new active detail
        $activeDetail = OrderDetail::factory()->create([
            'order_id' => $order->id,
            'bahan_baku_klien_id' => $this->material->id,
            'nama_material_po' => 'New Name',
            'qty' => 1500,
            'harga_jual' => 4200,
        ]);

        return compact('order', 'deletedDetail', 'activeDetail', 'orphanedPengDetails');
    }

    /**
     * Create orphaned forecast_details for a given deleted order detail.
     */
    private function createOrphanedForecastDetails(Order $order, OrderDetail $deletedDetail, int $count = 2): array
    {
        $orphanedForecastDetails = [];
        for ($i = 0; $i < $count; $i++) {
            $forecast = Forecast::factory()->create([
                'purchase_order_id' => $order->id,
                'purchasing_id' => $this->user->id,
            ]);

            $orphanedForecastDetails[] = ForecastDetail::create([
                'forecast_id' => $forecast->id,
                'purchase_order_bahan_baku_id' => $deletedDetail->id,
                'bahan_baku_supplier_id' => $this->supplierMaterial->id,
                'qty_forecast' => 500,
                'harga_satuan_forecast' => 3900,
                'total_harga_forecast' => 1950000,
            ]);
        }

        return $orphanedForecastDetails;
    }

    // ─────────────────────────────────────────────
    // DRY RUN SAFETY TESTS
    // ─────────────────────────────────────────────

    /** @test */
    public function dry_run_does_not_modify_any_data()
    {
        $data = $this->createOrderWithOrphans(3);

        // Record original FK values
        $originalFks = [];
        foreach ($data['orphanedPengDetails'] as $pd) {
            $originalFks[$pd->id] = $pd->purchase_order_bahan_baku_id;
        }

        // Run dry-run
        $this->artisan('fix:orphaned-order-detail-refs', ['--dry-run' => true])
            ->assertExitCode(0);

        // Verify NO data was changed
        foreach ($data['orphanedPengDetails'] as $pd) {
            $pd->refresh();
            $this->assertEquals(
                $originalFks[$pd->id],
                $pd->purchase_order_bahan_baku_id,
                "Dry run should not modify pengiriman_detail #{$pd->id}"
            );
        }
    }

    // ─────────────────────────────────────────────
    // BASIC FIX TESTS
    // ─────────────────────────────────────────────

    /** @test */
    public function fixes_orphaned_pengiriman_details()
    {
        $data = $this->createOrderWithOrphans(3);

        $this->artisan('fix:orphaned-order-detail-refs', ['--force' => true])
            ->assertExitCode(0);

        // All pengiriman_details should now point to the active detail
        foreach ($data['orphanedPengDetails'] as $pd) {
            $pd->refresh();
            $this->assertEquals($data['activeDetail']->id, $pd->purchase_order_bahan_baku_id);
        }
    }

    /** @test */
    public function fixes_orphaned_forecast_details()
    {
        $data = $this->createOrderWithOrphans(0); // No peng orphans
        $forecastOrphans = $this->createOrphanedForecastDetails(
            $data['order'],
            $data['deletedDetail'],
            3
        );

        $this->artisan('fix:orphaned-order-detail-refs', ['--force' => true])
            ->assertExitCode(0);

        foreach ($forecastOrphans as $fd) {
            $fd->refresh();
            $this->assertEquals($data['activeDetail']->id, $fd->purchase_order_bahan_baku_id);
        }
    }

    /** @test */
    public function fixes_both_tables_in_one_run()
    {
        $data = $this->createOrderWithOrphans(2);
        $forecastOrphans = $this->createOrphanedForecastDetails(
            $data['order'],
            $data['deletedDetail'],
            2
        );

        $this->artisan('fix:orphaned-order-detail-refs', ['--force' => true])
            ->assertExitCode(0);

        // Check pengiriman_details
        foreach ($data['orphanedPengDetails'] as $pd) {
            $pd->refresh();
            $this->assertEquals($data['activeDetail']->id, $pd->purchase_order_bahan_baku_id);
        }

        // Check forecast_details
        foreach ($forecastOrphans as $fd) {
            $fd->refresh();
            $this->assertEquals($data['activeDetail']->id, $fd->purchase_order_bahan_baku_id);
        }
    }

    // ─────────────────────────────────────────────
    // CROSS-ORDER ISOLATION TESTS
    // ─────────────────────────────────────────────

    /** @test */
    public function does_not_touch_healthy_records_from_other_orders()
    {
        // Create orphaned order
        $orphanData = $this->createOrderWithOrphans(2);

        // Create a completely healthy order with its own peng details
        $healthyOrder = Order::factory()->create([
            'klien_id' => $this->klien->id,
            'created_by' => $this->user->id,
            'po_number' => 'PO-HEALTHY-001',
            'po_start_date' => now(),
            'po_end_date' => now()->addDays(30),
        ]);
        $healthyDetail = OrderDetail::factory()->create([
            'order_id' => $healthyOrder->id,
            'bahan_baku_klien_id' => $this->material->id,
            'nama_material_po' => 'Healthy Material',
        ]);
        $healthyForecast = Forecast::factory()->create([
            'purchase_order_id' => $healthyOrder->id,
            'purchasing_id' => $this->user->id,
        ]);
        $healthyPeng = Pengiriman::create([
            'purchase_order_id' => $healthyOrder->id,
            'forecast_id' => $healthyForecast->id,
            'no_pengiriman' => 'PGR/' . now()->format('Ym') . '/8888',
            'tanggal_kirim' => now(),
            'total_qty_kirim' => 1000,
            'total_harga_kirim' => 4200000,
            'status' => 'berhasil',
        ]);
        $healthyPd = PengirimanDetail::create([
            'pengiriman_id' => $healthyPeng->id,
            'purchase_order_bahan_baku_id' => $healthyDetail->id,
            'bahan_baku_supplier_id' => $this->supplierMaterial->id,
            'qty_kirim' => 1000,
            'harga_satuan' => 3800,
            'total_harga' => 3800000,
        ]);

        $this->artisan('fix:orphaned-order-detail-refs', ['--force' => true])
            ->assertExitCode(0);

        // Healthy record must be untouched
        $healthyPd->refresh();
        $this->assertEquals(
            $healthyDetail->id,
            $healthyPd->purchase_order_bahan_baku_id,
            'Healthy pengiriman_detail should NOT be modified'
        );
    }

    /** @test */
    public function does_not_cross_contaminate_between_orphaned_orders()
    {
        // Two different orders, both with orphans
        $data1 = $this->createOrderWithOrphans(2);
        $data2 = $this->createOrderWithOrphans(2);

        $this->artisan('fix:orphaned-order-detail-refs', ['--force' => true])
            ->assertExitCode(0);

        // Order 1's orphans should point to order 1's active detail (NOT order 2's)
        foreach ($data1['orphanedPengDetails'] as $pd) {
            $pd->refresh();
            $this->assertEquals(
                $data1['activeDetail']->id,
                $pd->purchase_order_bahan_baku_id,
                "Order 1's pengiriman_detail should point to Order 1's active detail"
            );
        }

        // Order 2's orphans should point to order 2's active detail (NOT order 1's)
        foreach ($data2['orphanedPengDetails'] as $pd) {
            $pd->refresh();
            $this->assertEquals(
                $data2['activeDetail']->id,
                $pd->purchase_order_bahan_baku_id,
                "Order 2's pengiriman_detail should point to Order 2's active detail"
            );
        }
    }

    // ─────────────────────────────────────────────
    // EDGE CASE: NO ACTIVE DETAIL
    // ─────────────────────────────────────────────

    /** @test */
    public function skips_unfixable_records_when_no_active_detail_exists()
    {
        $order = Order::factory()->create([
            'klien_id' => $this->klien->id,
            'created_by' => $this->user->id,
            'po_number' => 'PO-ORPHAN-ONLY',
            'po_start_date' => now(),
            'po_end_date' => now()->addDays(30),
        ]);

        $deletedDetail = OrderDetail::factory()->create([
            'order_id' => $order->id,
            'bahan_baku_klien_id' => $this->material->id,
        ]);

        $forecast = Forecast::factory()->create([
            'purchase_order_id' => $order->id,
            'purchasing_id' => $this->user->id,
        ]);
        $pengiriman = Pengiriman::create([
            'purchase_order_id' => $order->id,
            'forecast_id' => $forecast->id,
            'no_pengiriman' => 'PGR/' . now()->format('Ym') . '/7777',
            'tanggal_kirim' => now(),
            'total_qty_kirim' => 500,
            'total_harga_kirim' => 1950000,
            'status' => 'berhasil',
        ]);
        $orphanedPd = PengirimanDetail::create([
            'pengiriman_id' => $pengiriman->id,
            'purchase_order_bahan_baku_id' => $deletedDetail->id,
            'bahan_baku_supplier_id' => $this->supplierMaterial->id,
            'qty_kirim' => 500,
            'harga_satuan' => 3900,
            'total_harga' => 1950000,
        ]);

        // Delete ALL details — no active detail remains
        $deletedDetail->delete();

        $this->artisan('fix:orphaned-order-detail-refs', ['--force' => true])
            ->assertExitCode(0);

        // Orphaned record should NOT be modified (no valid target)
        $orphanedPd->refresh();
        $this->assertEquals(
            $deletedDetail->id,
            $orphanedPd->purchase_order_bahan_baku_id,
            'Unfixable record must not be changed'
        );
    }

    // ─────────────────────────────────────────────
    // IDEMPOTENCY TEST
    // ─────────────────────────────────────────────

    /** @test */
    public function running_twice_does_not_change_already_fixed_records()
    {
        $data = $this->createOrderWithOrphans(3);

        // First run
        $this->artisan('fix:orphaned-order-detail-refs', ['--force' => true])
            ->assertExitCode(0);

        // Record the fixed state
        $fixedFks = [];
        foreach ($data['orphanedPengDetails'] as $pd) {
            $pd->refresh();
            $fixedFks[$pd->id] = $pd->purchase_order_bahan_baku_id;
            $this->assertEquals($data['activeDetail']->id, $pd->purchase_order_bahan_baku_id);
        }

        // Second run — should be a no-op
        $this->artisan('fix:orphaned-order-detail-refs', ['--force' => true])
            ->assertExitCode(0);

        // Verify nothing changed
        foreach ($data['orphanedPengDetails'] as $pd) {
            $pd->refresh();
            $this->assertEquals(
                $fixedFks[$pd->id],
                $pd->purchase_order_bahan_baku_id,
                'Second run should not modify already-fixed records'
            );
        }
    }

    // ─────────────────────────────────────────────
    // NO ORPHANS SCENARIO
    // ─────────────────────────────────────────────

    /** @test */
    public function exits_cleanly_with_no_orphans()
    {
        // Create healthy data — no orphans
        $order = Order::factory()->create([
            'klien_id' => $this->klien->id,
            'created_by' => $this->user->id,
            'po_number' => 'PO-CLEAN-001',
            'po_start_date' => now(),
            'po_end_date' => now()->addDays(30),
        ]);
        $activeDetail = OrderDetail::factory()->create([
            'order_id' => $order->id,
            'bahan_baku_klien_id' => $this->material->id,
        ]);
        $forecast = Forecast::factory()->create([
            'purchase_order_id' => $order->id,
            'purchasing_id' => $this->user->id,
        ]);
        $pengiriman = Pengiriman::create([
            'purchase_order_id' => $order->id,
            'forecast_id' => $forecast->id,
            'no_pengiriman' => 'PGR/' . now()->format('Ym') . '/5555',
            'tanggal_kirim' => now(),
            'total_qty_kirim' => 500,
            'total_harga_kirim' => 1950000,
            'status' => 'berhasil',
        ]);
        PengirimanDetail::create([
            'pengiriman_id' => $pengiriman->id,
            'purchase_order_bahan_baku_id' => $activeDetail->id,
            'bahan_baku_supplier_id' => $this->supplierMaterial->id,
            'qty_kirim' => 500,
            'harga_satuan' => 3900,
            'total_harga' => 1950000,
        ]);

        $this->artisan('fix:orphaned-order-detail-refs', ['--dry-run' => true])
            ->assertExitCode(0);
    }

    // ─────────────────────────────────────────────
    // DATA INTEGRITY: OTHER COLUMNS PRESERVED
    // ─────────────────────────────────────────────

    /** @test */
    public function preserves_all_other_columns_when_fixing()
    {
        $data = $this->createOrderWithOrphans(1);
        $pd = $data['orphanedPengDetails'][0];

        // Record original values of all non-FK columns
        $originalValues = [
            'pengiriman_id' => $pd->pengiriman_id,
            'bahan_baku_supplier_id' => $pd->bahan_baku_supplier_id,
            'qty_kirim' => $pd->qty_kirim,
            'harga_satuan' => $pd->harga_satuan,
            'total_harga' => $pd->total_harga,
        ];

        $this->artisan('fix:orphaned-order-detail-refs', ['--force' => true])
            ->assertExitCode(0);

        $pd->refresh();

        // FK should be updated
        $this->assertEquals($data['activeDetail']->id, $pd->purchase_order_bahan_baku_id);

        // ALL other columns must be untouched
        $this->assertEquals($originalValues['pengiriman_id'], $pd->pengiriman_id, 'pengiriman_id should be preserved');
        $this->assertEquals($originalValues['bahan_baku_supplier_id'], $pd->bahan_baku_supplier_id, 'bahan_baku_supplier_id should be preserved');
        $this->assertEquals($originalValues['qty_kirim'], $pd->qty_kirim, 'qty_kirim should be preserved');
        $this->assertEquals($originalValues['harga_satuan'], $pd->harga_satuan, 'harga_satuan should be preserved');
        $this->assertEquals($originalValues['total_harga'], $pd->total_harga, 'total_harga should be preserved');
    }
}
