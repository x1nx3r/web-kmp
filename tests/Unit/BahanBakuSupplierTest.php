<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\BahanBakuSupplier;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;

class BahanBakuSupplierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function it_can_create_bahan_baku_supplier()
    {
        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
        ]);

        $bahanBakuData = [
            'supplier_id' => $supplier->id,
            'nama' => 'Tepung Terigu',
            'harga_per_satuan' => 10000.50,
            'satuan' => 'kg',
            'stok' => 100.75,
        ];

        $bahanBaku = BahanBakuSupplier::create($bahanBakuData);

        $this->assertInstanceOf(BahanBakuSupplier::class, $bahanBaku);
        $this->assertEquals('Tepung Terigu', $bahanBaku->nama);
        $this->assertEquals(10000.50, $bahanBaku->harga_per_satuan);
        $this->assertEquals('kg', $bahanBaku->satuan);
        $this->assertEquals(100.75, $bahanBaku->stok);
        $this->assertDatabaseHas('bahan_baku_supplier', $bahanBakuData);
    }

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $bahanBaku = new BahanBakuSupplier();
        $expectedFillable = [
            'supplier_id',
            'nama',
            'harga_per_satuan',
            'satuan',
            'stok'
        ];

        $this->assertEquals($expectedFillable, $bahanBaku->getFillable());
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
        ]);

        $bahanBaku = BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Test Bahan',
            'harga_per_satuan' => 5000,
            'satuan' => 'kg',
            'stok' => 50,
        ]);

        $bahanBaku->delete();

        $this->assertSoftDeleted('bahan_baku_supplier', ['id' => $bahanBaku->id]);
        $this->assertNotNull($bahanBaku->fresh()->deleted_at);
    }

    /** @test */
    public function it_belongs_to_supplier()
    {
        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
        ]);

        $bahanBaku = BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Test Bahan',
            'harga_per_satuan' => 5000,
            'satuan' => 'kg',
            'stok' => 50,
        ]);

        $this->assertInstanceOf(Supplier::class, $bahanBaku->supplier);
        $this->assertEquals($supplier->id, $bahanBaku->supplier->id);
        $this->assertEquals('Test Supplier', $bahanBaku->supplier->nama);
    }

    /** @test */
    public function it_has_default_stok_value()
    {
        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
        ]);

        $bahanBaku = BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Test Bahan',
            'harga_per_satuan' => 5000,
            'satuan' => 'kg',
            // stok not provided, should default to 0
        ]);

        $this->assertEquals(0, $bahanBaku->fresh()->stok);
    }

    /** @test */
    public function it_enforces_unique_constraint_for_nama_per_supplier()
    {
        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
        ]);

        // Create first bahan baku
        BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Tepung Terigu',
            'harga_per_satuan' => 5000,
            'satuan' => 'kg',
            'stok' => 50,
        ]);

        // Try to create duplicate bahan baku for same supplier
        $this->expectException(QueryException::class);
        
        BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Tepung Terigu', // Same name
            'harga_per_satuan' => 6000,
            'satuan' => 'kg',
            'stok' => 100,
        ]);
    }

    /** @test */
    public function it_allows_same_nama_for_different_suppliers()
    {
        $supplier1 = Supplier::create([
            'nama' => 'Supplier 1',
            'slug' => 'supplier-1',
        ]);

        $supplier2 = Supplier::create([
            'nama' => 'Supplier 2',
            'slug' => 'supplier-2',
        ]);

        // Create bahan baku with same name for different suppliers
        $bahanBaku1 = BahanBakuSupplier::create([
            'supplier_id' => $supplier1->id,
            'nama' => 'Tepung Terigu',
            'harga_per_satuan' => 5000,
            'satuan' => 'kg',
            'stok' => 50,
        ]);

        $bahanBaku2 = BahanBakuSupplier::create([
            'supplier_id' => $supplier2->id,
            'nama' => 'Tepung Terigu', // Same name, different supplier
            'harga_per_satuan' => 6000,
            'satuan' => 'kg',
            'stok' => 100,
        ]);

        $this->assertNotEquals($bahanBaku1->id, $bahanBaku2->id);
        $this->assertEquals('Tepung Terigu', $bahanBaku1->nama);
        $this->assertEquals('Tepung Terigu', $bahanBaku2->nama);
        $this->assertEquals($supplier1->id, $bahanBaku1->supplier_id);
        $this->assertEquals($supplier2->id, $bahanBaku2->supplier_id);
    }

    /** @test */
    public function it_can_format_harga_currency()
    {
        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
        ]);

        $bahanBaku = BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Test Bahan',
            'harga_per_satuan' => 15750.50,
            'satuan' => 'kg',
            'stok' => 50,
        ]);

        // Test that we can format the price
        $formatted = number_format((float) $bahanBaku->harga_per_satuan, 0, ',', '.');
        $this->assertEquals('15.751', $formatted);
    }

    /** @test */
    public function it_can_format_stok_number()
    {
        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
        ]);

        $bahanBaku = BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Test Bahan',
            'harga_per_satuan' => 5000,
            'satuan' => 'kg',
            'stok' => 1250.75,
        ]);

        // Test that we can format the stock
        $formatted = number_format((float) $bahanBaku->stok, 0, ',', '.');
        $this->assertEquals('1.251', $formatted);
    }

    /** @test */
    public function it_can_handle_decimal_values()
    {
        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
        ]);

        $bahanBaku = BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Test Bahan',
            'harga_per_satuan' => 999.99,
            'satuan' => 'kg',
            'stok' => 123.45,
        ]);

        $this->assertEquals(999.99, $bahanBaku->harga_per_satuan);
        $this->assertEquals(123.45, $bahanBaku->stok);
    }
}
