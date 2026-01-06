<?php

namespace Tests\Feature\Livewire\Marketing;

use App\Livewire\Marketing\OrderCreate;
use App\Livewire\Marketing\OrderEdit;
use App\Models\BahanBakuKlien;
use App\Models\BahanBakuSupplier;
use App\Models\Klien;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class OrderFormComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_create_component_can_create_order_with_auto_suppliers(): void
    {
        Storage::fake('public');
        Carbon::setTestNow(Carbon::create(2025, 11, 1, 8));

        $user = User::factory()->create(['role' => 'marketing']);
        $this->actingAs($user);

        $klien = Klien::factory()->create([
            'nama' => 'PT Pakan Sejahtera',
            'cabang' => 'Jakarta',
        ]);

        $material = BahanBakuKlien::factory()
            ->for($klien)
            ->create([
                'nama' => 'Jagung Super',
                'satuan' => 'kg',
                'status' => 'aktif',
                'harga_approved' => 12500,
            ]);

        $supplier = Supplier::factory()->create(['nama' => 'Supplier Unggulan']);

        BahanBakuSupplier::factory()
            ->for($supplier)
            ->create([
                'nama' => $material->nama,
                'satuan' => $material->satuan,
                'harga_per_satuan' => 8000,
            ]);

        $component = Livewire::test(OrderCreate::class);

        $component->call('selectKlien', $klien->nama . '|' . $klien->cabang);
        $component->call('selectMaterial', $material->id);

        $component->set('currentQuantity', 10);
        $component->set('currentHargaJual', 10000);
        $component->call('addOrderItem');

        $component->set('poNumber', 'PO-TEST-001');
        $component->set('poStartDate', Carbon::now()->format('Y-m-d'));
        $component->set('poEndDate', Carbon::now()->addDays(5)->format('Y-m-d'));
        $component->set('poDocument', UploadedFile::fake()->image('po.png'));
        $component->set('catatan', 'Catatan pengujian otomatis.');

        $component->call('createOrder');

        $order = Order::latest('id')->first();
        $this->assertNotNull($order, 'Order should have been created.');

        $component->assertRedirect(route('orders.show', $order));

        $this->assertEquals($klien->id, $order->klien_id);
        $this->assertEquals('PO-TEST-001', $order->po_number);
        // 5 days <= 30 => tinggi (urgent!)
        $this->assertEquals('tinggi', $order->priority);
        $this->assertCount(1, $order->orderDetails);

        $detail = $order->orderDetails->first();
        $this->assertEquals(10.0, (float) $detail->qty);
        $this->assertTrue($detail->supplier_options_populated);
        $this->assertGreaterThan(0, $detail->orderSuppliers()->count());

        $recommendedSupplier = $detail->orderSuppliers()->where('is_recommended', true)->first();
        $this->assertNotNull($recommendedSupplier, 'Recommended supplier should be flagged.');
        $this->assertEquals($detail->recommended_supplier_id, $recommendedSupplier->supplier_id);

        Storage::disk('public')->assertExists($order->po_document_path);

        Carbon::setTestNow();
    }

    public function test_order_edit_component_updates_existing_order(): void
    {
        Storage::fake('public');
        Carbon::setTestNow(Carbon::create(2025, 11, 1, 9));

        $user = User::factory()->create(['role' => 'marketing']);
        $this->actingAs($user);

        $klien = Klien::factory()->create([
            'nama' => 'PT Pakan Jaya',
            'cabang' => 'Bandung',
        ]);

        $material = BahanBakuKlien::factory()
            ->for($klien)
            ->create([
                'nama' => 'Dedak Halus',
                'satuan' => 'kg',
                'status' => 'aktif',
                'harga_approved' => 9500,
            ]);

        $supplier = Supplier::factory()->create(['nama' => 'Supplier Dedak']);

        $bahanBakuSupplier = BahanBakuSupplier::factory()
            ->for($supplier)
            ->create([
                'nama' => $material->nama,
                'satuan' => $material->satuan,
                'harga_per_satuan' => 7000,
            ]);

        Storage::disk('public')->put('po-documents/original-po.png', 'original-content');

        $order = Order::factory()
            ->draft()
            ->create([
                'klien_id' => $klien->id,
                'created_by' => $user->id,
                'status' => 'draft',
                'priority' => 'sedang', // Will be recalculated based on deadline
                'po_number' => 'PO-ORIGINAL-001',
                'po_start_date' => Carbon::now()->format('Y-m-d'),
                'po_end_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'po_document_path' => 'po-documents/original-po.png',
                'po_document_original_name' => 'original-po.png',
                'catatan' => 'Catatan awal order.',
            ]);

        $detail = $order->orderDetails()->create([
            'bahan_baku_klien_id' => $material->id,
            'qty' => 5,
            'satuan' => $material->satuan,
            'harga_jual' => 12000,
            'total_harga' => 60000,
            'status' => 'menunggu',
        ]);

        $detail->populateSupplierOptions();
        $detail->refresh();

        $order->calculateTotals();
        $order->refresh();

        $component = Livewire::test(OrderEdit::class, [
            'order' => $order->fresh([
                'klien',
                'orderDetails.bahanBakuKlien',
                'orderDetails.orderSuppliers.supplier.picPurchasing',
                'orderDetails.orderSuppliers.bahanBakuSupplier',
            ]),
        ]);

        $component->assertSet('selectedKlienId', $klien->id);
        $this->assertCount(1, $component->get('selectedOrderItems'));

        $items = $component->get('selectedOrderItems');
        $bestPrice = (float) ($items[0]['best_supplier_price'] ?? 0);
        $items[0]['qty'] = 8;
        $items[0]['harga_jual'] = 15000;
        $items[0]['total_harga'] = 120000;
        $items[0]['best_hpp'] = $bestPrice * 8;
        $items[0]['total_margin'] = $items[0]['total_harga'] - $items[0]['best_hpp'];
        $items[0]['margin_percentage'] = $items[0]['total_harga'] > 0
            ? ($items[0]['total_margin'] / $items[0]['total_harga']) * 100
            : 0;

        $component->set('selectedOrderItems', $items);
        $component->set('poNumber', 'PO-UPDATED-001');
        $component->set('poEndDate', Carbon::now()->addDays(2)->format('Y-m-d'));
        $component->set('catatan', 'Catatan setelah pembaruan.');

        $component->call('updateOrder')
            ->assertRedirect(route('orders.show', $order->id));

        $order->refresh();

        $this->assertEquals('PO-UPDATED-001', $order->po_number);
        // 2 days <= 30 => tinggi (urgent!)
        $this->assertEquals('tinggi', $order->priority);
        $this->assertEquals('Catatan setelah pembaruan.', $order->catatan);
        $this->assertCount(1, $order->orderDetails);

        $updatedDetail = $order->orderDetails->first();
        $this->assertEquals(8.0, (float) $updatedDetail->qty);
        $this->assertEquals(15000.0, (float) $updatedDetail->harga_jual);
        $this->assertTrue($updatedDetail->orderSuppliers()->exists());
        $this->assertTrue($updatedDetail->supplier_options_populated);
        $this->assertGreaterThan(0, $updatedDetail->available_suppliers_count);
        $this->assertEquals($updatedDetail->recommended_supplier_id, optional($updatedDetail->orderSuppliers()->where('is_recommended', true)->first())->supplier_id);

        Storage::disk('public')->assertExists('po-documents/original-po.png');

        Carbon::setTestNow();
    }
}
