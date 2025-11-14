<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Supplier;
use App\Models\BahanBakuKlien;
use App\Models\BahanBakuSupplier;

class OrdersSuppliersApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_top_suppliers_for_material_ordered_by_price()
    {
        // create a client material
        $material = BahanBakuKlien::factory()->create(['nama' => 'Tepung Terigu']);

        // create suppliers and bahan_baku_supplier records
        $supA = Supplier::factory()->create(['nama' => 'Supplier A']);
        $supB = Supplier::factory()->create(['nama' => 'Supplier B']);
        $supC = Supplier::factory()->create(['nama' => 'Supplier C']);

        BahanBakuSupplier::factory()->create([
            'supplier_id' => $supA->id,
            'nama' => 'Tepung Terigu',
            'harga_per_satuan' => 20000,
        ]);
        BahanBakuSupplier::factory()->create([
            'supplier_id' => $supB->id,
            'nama' => 'Tepung Terigu Special',
            'harga_per_satuan' => 18000,
        ]);
        BahanBakuSupplier::factory()->create([
            'supplier_id' => $supC->id,
            'nama' => 'Tepung Terigu Premium',
            'harga_per_satuan' => 22000,
        ]);

        $response = $this->getJson(route('orders.material.suppliers', ['material' => $material->id]));

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertArrayHasKey('data', $json);
        $this->assertIsArray($json['data']);
        // should return suppliers ordered by price asc: 18000, 20000, 22000
        $this->assertCount(3, $json['data']);
        $prices = array_map(fn($i) => $i['price'], $json['data']);
        $this->assertEquals([18000.0, 20000.0, 22000.0], $prices);

        // verify canonical keys exist on the first item
        $first = $json['data'][0];
        $this->assertArrayHasKey('bahan_baku_supplier_id', $first);
        $this->assertArrayHasKey('supplier_id', $first);
        $this->assertArrayHasKey('price', $first);
        $this->assertArrayHasKey('satuan', $first);
    }
}
