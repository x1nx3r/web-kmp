<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Supplier;
use App\Models\User;
use App\Models\BahanBakuSupplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Collection;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Don't seed - create fresh data for each test
    }

    /** @test */
    public function it_can_create_a_supplier()
    {
        $supplierData = [
            'nama' => 'PT Supplier Test',
            'slug' => 'pt-supplier-test',
            'alamat' => 'Jl. Test No. 123',
            'no_hp' => '081234567890',
        ];

        $supplier = Supplier::create($supplierData);

        $this->assertInstanceOf(Supplier::class, $supplier);
        $this->assertEquals('PT Supplier Test', $supplier->nama);
        $this->assertEquals('pt-supplier-test', $supplier->slug);
        $this->assertDatabaseHas('suppliers', $supplierData);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $supplier = new Supplier();
        $expectedFillable = [
            'nama',
            'slug', 
            'alamat',
            'no_hp',
            'pic_purchasing_id'
        ];

        $this->assertEquals($expectedFillable, $supplier->getFillable());
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
        ]);

        $supplier->delete();

        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
        $this->assertNotNull($supplier->fresh()->deleted_at);
    }

    /** @test */
    public function it_belongs_to_pic_purchasing()
    {
        $user = User::factory()->create(['role' => 'purchasing']);
        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
            'pic_purchasing_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $supplier->picPurchasing);
        $this->assertEquals($user->id, $supplier->picPurchasing->id);
        $this->assertEquals('purchasing', $supplier->picPurchasing->role);
    }

    /** @test */
    public function it_has_many_bahan_baku_suppliers()
    {
        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
        ]);

        $bahanBaku1 = BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Bahan A',
            'harga_per_satuan' => 10000,
            'satuan' => 'kg',
            'stok' => 100,
        ]);

        $bahanBaku2 = BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Bahan B',
            'harga_per_satuan' => 15000,
            'satuan' => 'kg',
            'stok' => 50,
        ]);

        $this->assertInstanceOf(Collection::class, $supplier->bahanBakuSuppliers);
        $this->assertCount(2, $supplier->bahanBakuSuppliers()->get());
        $this->assertTrue($supplier->bahanBakuSuppliers->contains($bahanBaku1));
        $this->assertTrue($supplier->bahanBakuSuppliers->contains($bahanBaku2));
    }

    /** @test */
    public function it_can_search_by_name()
    {
        $supplier1 = Supplier::create([
            'nama' => 'PT Sejahtera Bersama',
            'slug' => 'pt-sejahtera-bersama',
        ]);

        $supplier2 = Supplier::create([
            'nama' => 'CV Maju Jaya',
            'slug' => 'cv-maju-jaya',
        ]);

        $supplier3 = Supplier::create([
            'nama' => 'PT Berbeda Sendiri',
            'slug' => 'pt-berbeda-sendiri',
        ]);

        // Test search by name
        $results = Supplier::search('sejahtera')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('PT Sejahtera Bersama', $results->first()->nama);

        // Test search partial match
        $results = Supplier::search('PT')->get();
        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('nama', 'PT Sejahtera Bersama'));
        $this->assertTrue($results->contains('nama', 'PT Berbeda Sendiri'));
    }

    /** @test */
    public function it_can_search_by_pic_purchasing_name()
    {
        $user = User::factory()->create([
            'nama' => 'John Purchasing',
            'role' => 'purchasing'
        ]);

        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
            'pic_purchasing_id' => $user->id,
        ]);

        $results = Supplier::search('John')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Test Supplier', $results->first()->nama);
    }

    /** @test */
    public function it_can_search_by_bahan_baku_name()
    {
        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
        ]);

        BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Tepung Terigu',
            'harga_per_satuan' => 10000,
            'satuan' => 'kg',
            'stok' => 100,
        ]);

        $results = Supplier::search('Tepung')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Test Supplier', $results->first()->nama);
    }

    /** @test */
    public function search_returns_empty_collection_when_no_match()
    {
        Supplier::create([
            'nama' => 'PT Test',
            'slug' => 'pt-test',
        ]);

        $results = Supplier::search('nonexistent')->get();
        $this->assertCount(0, $results);
        $this->assertInstanceOf(Collection::class, $results);
    }

    /** @test */
    public function it_can_calculate_total_bahan_baku_count()
    {
        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
        ]);

        // Create multiple bahan baku
        BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Bahan A',
            'harga_per_satuan' => 10000,
            'satuan' => 'kg',
            'stok' => 100,
        ]);

        BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Bahan B',
            'harga_per_satuan' => 15000,
            'satuan' => 'kg',
            'stok' => 50,
        ]);

        $this->assertEquals(2, $supplier->bahanBakuSuppliers()->count());
    }

    /** @test */
    public function it_can_calculate_total_stok()
    {
        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
        ]);

        BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Bahan A',
            'harga_per_satuan' => 10000,
            'satuan' => 'kg',
            'stok' => 100,
        ]);

        BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Bahan B',
            'harga_per_satuan' => 15000,
            'satuan' => 'kg',
            'stok' => 50,
        ]);

        $totalStok = $supplier->bahanBakuSuppliers->sum('stok');
        $this->assertEquals(150, $totalStok);
    }
}
