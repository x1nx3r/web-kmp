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
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderEditFkRepointingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Klien $klien;
    private BahanBakuKlien $material;
    private BahanBakuSupplier $supplierMaterial;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create base entities
        $this->user = User::factory()->create(['role' => 'marketing']);
        $this->klien = Klien::factory()->create();
        $this->material = BahanBakuKlien::factory()->create([
            'klien_id' => $this->klien->id,
            'nama' => 'Biscuit Meal',
            'satuan' => 'kg',
        ]);
        $this->supplierMaterial = BahanBakuSupplier::factory()->create([
            'nama' => 'Tepung Biskuit',
        ]);

        // Create an order with one detail
        $this->order = Order::factory()->create([
            'klien_id' => $this->klien->id,
            'created_by' => $this->user->id,
            'status' => 'diproses',
            'po_number' => 'PO-TEST-001',
            'po_start_date' => now(),
            'po_end_date' => now()->addDays(30),
        ]);
    }

    /**
     * Helper to create an order detail for the test order.
     */
    private function createOrderDetail(array $overrides = []): OrderDetail
    {
        return OrderDetail::factory()->create(array_merge([
            'order_id' => $this->order->id,
            'bahan_baku_klien_id' => $this->material->id,
            'nama_material_po' => 'Biscuit Meal',
            'qty' => 1000,
            'satuan' => 'kg',
            'harga_jual' => 4200,
            'total_harga' => 4200000,
            'status' => 'menunggu',
        ], $overrides));
    }

    /**
     * Helper to create a pengiriman and its detail linked to a given order detail.
     */
    private function createPengirimanWithDetail(OrderDetail $orderDetail, float $qty = 500): PengirimanDetail
    {
        $forecast = \App\Models\Forecast::factory()->create([
            'purchase_order_id' => $this->order->id,
            'purchasing_id' => $this->user->id,
        ]);

        $pengiriman = Pengiriman::create([
            'purchase_order_id' => $this->order->id,
            'forecast_id' => $forecast->id,
            'no_pengiriman' => 'PGR/' . now()->format('Ym') . '/' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'tanggal_kirim' => now(),
            'total_qty_kirim' => $qty,
            'total_harga_kirim' => $qty * 3900,
            'status' => 'berhasil',
        ]);

        return PengirimanDetail::create([
            'pengiriman_id' => $pengiriman->id,
            'purchase_order_bahan_baku_id' => $orderDetail->id,
            'bahan_baku_supplier_id' => $this->supplierMaterial->id,
            'qty_kirim' => $qty,
            'harga_satuan' => 3900,
            'total_harga' => $qty * 3900,
        ]);
    }

    /** @test */
    public function update_order_repoints_pengiriman_details_to_new_order_detail()
    {
        // Arrange: create original order detail and linked pengiriman details
        $oldDetail = $this->createOrderDetail();
        $pd1 = $this->createPengirimanWithDetail($oldDetail, 500);
        $pd2 = $this->createPengirimanWithDetail($oldDetail, 800);

        // Verify pengiriman details point to old order detail
        $this->assertEquals($oldDetail->id, $pd1->purchase_order_bahan_baku_id);
        $this->assertEquals($oldDetail->id, $pd2->purchase_order_bahan_baku_id);

        // Act: simulate the updateOrder logic (soft-delete old, create new, re-point)
        $this->actingAs($this->user);

        // Load order with details (mimicking what updateOrder() does)
        $order = Order::with(['orderDetails.orderSuppliers'])->findOrFail($this->order->id);

        // Capture old IDs
        $oldDetailIds = $order->orderDetails->pluck('id')->toArray();
        $this->assertContains($oldDetail->id, $oldDetailIds);

        // Soft-delete old details
        foreach ($order->orderDetails as $detail) {
            $detail->orderSuppliers()->delete();
        }
        $order->orderDetails()->delete();

        // Create new detail
        $newDetail = OrderDetail::create([
            'order_id' => $order->id,
            'bahan_baku_klien_id' => $this->material->id,
            'nama_material_po' => 'Biscuit Meal Updated',
            'qty' => 1500,
            'satuan' => 'kg',
            'harga_jual' => 4500,
            'total_harga' => 6750000,
            'status' => 'menunggu',
        ]);

        // Re-point FKs (this is the logic we added)
        if (!empty($oldDetailIds)) {
            PengirimanDetail::whereIn('purchase_order_bahan_baku_id', $oldDetailIds)
                ->update(['purchase_order_bahan_baku_id' => $newDetail->id]);
        }

        // Assert: old detail is soft-deleted
        $this->assertSoftDeleted('order_details', ['id' => $oldDetail->id]);

        // Assert: new detail exists and is active
        $this->assertDatabaseHas('order_details', [
            'id' => $newDetail->id,
            'deleted_at' => null,
            'nama_material_po' => 'Biscuit Meal Updated',
        ]);

        // Assert: pengiriman details now point to the new order detail
        $pd1->refresh();
        $pd2->refresh();
        $this->assertEquals($newDetail->id, $pd1->purchase_order_bahan_baku_id);
        $this->assertEquals($newDetail->id, $pd2->purchase_order_bahan_baku_id);

        // Assert: the relationship resolves correctly (not through withTrashed)
        $this->assertNotNull($pd1->purchaseOrderBahanBaku);
        $this->assertNull($pd1->purchaseOrderBahanBaku->deleted_at);
        $this->assertEquals('Biscuit Meal Updated', $pd1->purchaseOrderBahanBaku->nama_material_po);
    }

    /** @test */
    public function update_order_handles_multiple_old_details()
    {
        // Arrange: simulate a scenario where order was edited multiple times
        // leaving multiple soft-deleted details
        $detail1 = $this->createOrderDetail(['nama_material_po' => 'Version 1']);
        $pd1 = $this->createPengirimanWithDetail($detail1, 300);

        // Soft-delete detail1, create detail2
        $detail1->delete();
        $detail2 = $this->createOrderDetail(['nama_material_po' => 'Version 2']);
        $pd2 = $this->createPengirimanWithDetail($detail2, 400);

        // Now simulate another edit: soft-delete detail2, create detail3
        $order = Order::with(['orderDetails.orderSuppliers'])->findOrFail($this->order->id);
        $oldDetailIds = $order->orderDetails->pluck('id')->toArray();

        // pd1 still points to deleted detail1, pd2 points to active detail2
        // oldDetailIds only captures detail2 (active at time of edit)
        $this->assertContains($detail2->id, $oldDetailIds);

        foreach ($order->orderDetails as $detail) {
            $detail->orderSuppliers()->delete();
        }
        $order->orderDetails()->delete();

        $detail3 = OrderDetail::create([
            'order_id' => $order->id,
            'bahan_baku_klien_id' => $this->material->id,
            'nama_material_po' => 'Version 3',
            'qty' => 2000,
            'satuan' => 'kg',
            'harga_jual' => 5000,
            'total_harga' => 10000000,
            'status' => 'menunggu',
        ]);

        // Re-point only the ones from this edit cycle
        if (!empty($oldDetailIds)) {
            PengirimanDetail::whereIn('purchase_order_bahan_baku_id', $oldDetailIds)
                ->update(['purchase_order_bahan_baku_id' => $detail3->id]);
        }

        // Assert: pd2 now points to detail3
        $pd2->refresh();
        $this->assertEquals($detail3->id, $pd2->purchase_order_bahan_baku_id);

        // Note: pd1 still points to the old deleted detail1 (from a previous edit cycle).
        // This is the expected "orphan" scenario that the repair command handles.
        $pd1->refresh();
        $this->assertEquals($detail1->id, $pd1->purchase_order_bahan_baku_id);
    }

    /** @test */
    public function repoint_does_not_affect_other_orders()
    {
        // Arrange: create two orders, each with their own details and pengiriman
        $detail1 = $this->createOrderDetail();
        $pd1 = $this->createPengirimanWithDetail($detail1, 500);

        // Create a second order with its own detail
        $otherOrder = Order::factory()->create([
            'klien_id' => $this->klien->id,
            'created_by' => $this->user->id,
            'po_number' => 'PO-TEST-002',
            'po_start_date' => now(),
            'po_end_date' => now()->addDays(30),
        ]);
        $otherDetail = OrderDetail::factory()->create([
            'order_id' => $otherOrder->id,
            'bahan_baku_klien_id' => $this->material->id,
            'nama_material_po' => 'Other Material',
        ]);

        $otherForecast = \App\Models\Forecast::factory()->create([
            'purchase_order_id' => $otherOrder->id,
            'purchasing_id' => $this->user->id,
        ]);

        $otherPengiriman = Pengiriman::create([
            'purchase_order_id' => $otherOrder->id,
            'forecast_id' => $otherForecast->id,
            'no_pengiriman' => 'PGR/' . now()->format('Ym') . '/9999',
            'tanggal_kirim' => now(),
            'total_qty_kirim' => 600,
            'total_harga_kirim' => 2400000,
            'status' => 'berhasil',
        ]);
        $otherPd = PengirimanDetail::create([
            'pengiriman_id' => $otherPengiriman->id,
            'purchase_order_bahan_baku_id' => $otherDetail->id,
            'bahan_baku_supplier_id' => $this->supplierMaterial->id,
            'qty_kirim' => 600,
            'harga_satuan' => 3800,
            'total_harga' => 2280000,
        ]);

        // Act: update first order
        $order = Order::with(['orderDetails.orderSuppliers'])->findOrFail($this->order->id);
        $oldDetailIds = $order->orderDetails->pluck('id')->toArray();

        foreach ($order->orderDetails as $detail) {
            $detail->orderSuppliers()->delete();
        }
        $order->orderDetails()->delete();

        $newDetail = OrderDetail::create([
            'order_id' => $order->id,
            'bahan_baku_klien_id' => $this->material->id,
            'nama_material_po' => 'Updated Material',
            'qty' => 1000,
            'satuan' => 'kg',
            'harga_jual' => 4200,
            'total_harga' => 4200000,
            'status' => 'menunggu',
        ]);

        if (!empty($oldDetailIds)) {
            PengirimanDetail::whereIn('purchase_order_bahan_baku_id', $oldDetailIds)
                ->update(['purchase_order_bahan_baku_id' => $newDetail->id]);
        }

        // Assert: first order's pengiriman detail is re-pointed
        $pd1->refresh();
        $this->assertEquals($newDetail->id, $pd1->purchase_order_bahan_baku_id);

        // Assert: second order's pengiriman detail is UNTOUCHED
        $otherPd->refresh();
        $this->assertEquals($otherDetail->id, $otherPd->purchase_order_bahan_baku_id);
    }

    /**
     * Helper to create a forecast detail linked to a given order detail.
     */
    private function createForecastDetail(OrderDetail $orderDetail): ForecastDetail
    {
        $forecast = Forecast::factory()->create([
            'purchase_order_id' => $this->order->id,
            'purchasing_id' => $this->user->id,
        ]);

        return ForecastDetail::create([
            'forecast_id' => $forecast->id,
            'purchase_order_bahan_baku_id' => $orderDetail->id,
            'bahan_baku_supplier_id' => $this->supplierMaterial->id,
            'qty_forecast' => 500,
            'harga_satuan_forecast' => 3900,
            'total_harga_forecast' => 1950000,
        ]);
    }

    /** @test */
    public function update_order_repoints_forecast_details_to_new_order_detail()
    {
        // Arrange: create original order detail with linked forecast and pengiriman details
        $oldDetail = $this->createOrderDetail();
        $pd1 = $this->createPengirimanWithDetail($oldDetail, 500);
        $fd1 = $this->createForecastDetail($oldDetail);
        $fd2 = $this->createForecastDetail($oldDetail);

        // Verify both point to old detail
        $this->assertEquals($oldDetail->id, $fd1->purchase_order_bahan_baku_id);
        $this->assertEquals($oldDetail->id, $fd2->purchase_order_bahan_baku_id);
        $this->assertEquals($oldDetail->id, $pd1->purchase_order_bahan_baku_id);

        // Act: simulate updateOrder logic with BOTH re-points
        $order = Order::with(['orderDetails.orderSuppliers'])->findOrFail($this->order->id);
        $oldDetailIds = $order->orderDetails->pluck('id')->toArray();

        foreach ($order->orderDetails as $detail) {
            $detail->orderSuppliers()->delete();
        }
        $order->orderDetails()->delete();

        $newDetail = OrderDetail::create([
            'order_id' => $order->id,
            'bahan_baku_klien_id' => $this->material->id,
            'nama_material_po' => 'Updated',
            'qty' => 2000,
            'satuan' => 'kg',
            'harga_jual' => 4500,
            'total_harga' => 9000000,
            'status' => 'menunggu',
        ]);

        // Re-point BOTH tables (matching the updated updateOrder logic)
        if (!empty($oldDetailIds)) {
            PengirimanDetail::whereIn('purchase_order_bahan_baku_id', $oldDetailIds)
                ->update(['purchase_order_bahan_baku_id' => $newDetail->id]);

            ForecastDetail::whereIn('purchase_order_bahan_baku_id', $oldDetailIds)
                ->update(['purchase_order_bahan_baku_id' => $newDetail->id]);
        }

        // Assert: pengiriman_details re-pointed
        $pd1->refresh();
        $this->assertEquals($newDetail->id, $pd1->purchase_order_bahan_baku_id);

        // Assert: forecast_details re-pointed
        $fd1->refresh();
        $fd2->refresh();
        $this->assertEquals($newDetail->id, $fd1->purchase_order_bahan_baku_id);
        $this->assertEquals($newDetail->id, $fd2->purchase_order_bahan_baku_id);

        // Assert: relationships resolve to active (non-deleted) detail
        $this->assertNull($fd1->purchaseOrderBahanBaku->deleted_at);
        $this->assertEquals('Updated', $fd1->purchaseOrderBahanBaku->nama_material_po);
    }
}
