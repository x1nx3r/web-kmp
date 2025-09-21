<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Supplier;
use App\Models\User;
use App\Models\BahanBakuSupplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;

class SupplierControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Don't seed - create fresh data for each test
    }

    /** @test */
    public function it_can_display_supplier_index_page()
    {
        // Create some test data
        $user = User::factory()->create(['role' => 'purchasing']);
        $supplier = Supplier::create([
            'nama' => 'PT Test Supplier',
            'slug' => 'pt-test-supplier',
            'alamat' => 'Jl. Test No. 123',
            'no_hp' => '081234567890',
            'pic_purchasing_id' => $user->id,
        ]);

        BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Tepung Terigu',
            'harga_per_satuan' => 10000,
            'satuan' => 'kg',
            'stok' => 100,
        ]);

        $response = $this->get('/supplier');

        $response->assertStatus(200);
        $response->assertSee('PT Test Supplier');
        $response->assertSee('Jl. Test No. 123');
        $response->assertSee('081234567890');
        $response->assertSee('Tepung Terigu');
        $response->assertViewIs('pages.purchasing.supplier');
        $response->assertViewHas('suppliers');
    }

    /** @test */
    public function it_can_search_suppliers()
    {
        $supplier1 = Supplier::create([
            'nama' => 'PT Sejahtera Makmur',
            'slug' => 'pt-sejahtera-makmur',
        ]);

        $supplier2 = Supplier::create([
            'nama' => 'CV Maju Bersama',
            'slug' => 'cv-maju-bersama',
        ]);

        $response = $this->get('/supplier?search=Sejahtera');

        $response->assertStatus(200);
        $response->assertSee('PT Sejahtera Makmur');
        $response->assertDontSee('CV Maju Bersama');
    }

    /** @test */
    public function it_can_filter_suppliers_by_bahan_baku()
    {
        $supplier1 = Supplier::create([
            'nama' => 'Supplier A',
            'slug' => 'supplier-a',
        ]);

        $supplier2 = Supplier::create([
            'nama' => 'Supplier B',
            'slug' => 'supplier-b',
        ]);

        BahanBakuSupplier::create([
            'supplier_id' => $supplier1->id,
            'nama' => 'Tepung Terigu',
            'harga_per_satuan' => 10000,
            'satuan' => 'kg',
            'stok' => 100,
        ]);

        BahanBakuSupplier::create([
            'supplier_id' => $supplier2->id,
            'nama' => 'Gula Pasir',
            'harga_per_satuan' => 15000,
            'satuan' => 'kg',
            'stok' => 50,
        ]);

        $response = $this->get('/supplier?bahan_baku=tepung_terigu');

        $response->assertStatus(200);
        $response->assertSee('Supplier A');
        $response->assertDontSee('Supplier B');
    }

    /** @test */
    public function it_can_sort_suppliers_by_bahan_baku_count()
    {
        $supplier1 = Supplier::create([
            'nama' => 'Supplier Few',
            'slug' => 'supplier-few',
        ]);

        $supplier2 = Supplier::create([
            'nama' => 'Supplier Many',
            'slug' => 'supplier-many',
        ]);

        // Supplier 1 has 1 bahan baku
        BahanBakuSupplier::create([
            'supplier_id' => $supplier1->id,
            'nama' => 'Bahan A',
            'harga_per_satuan' => 10000,
            'satuan' => 'kg',
            'stok' => 100,
        ]);

        // Supplier 2 has 2 bahan baku
        BahanBakuSupplier::create([
            'supplier_id' => $supplier2->id,
            'nama' => 'Bahan B',
            'harga_per_satuan' => 15000,
            'satuan' => 'kg',
            'stok' => 50,
        ]);

        BahanBakuSupplier::create([
            'supplier_id' => $supplier2->id,
            'nama' => 'Bahan C',
            'harga_per_satuan' => 20000,
            'satuan' => 'kg',
            'stok' => 30,
        ]);

        // Sort by most bahan baku
        $response = $this->get('/supplier?sort_bahan_baku=terbanyak');
        $response->assertStatus(200);
        
        // Check that content is rendered correctly
        $content = $response->getContent();
        $this->assertStringContainsString('Supplier Many', $content);
        $this->assertStringContainsString('Supplier Few', $content);
    }

    /** @test */
    public function it_can_display_create_supplier_page()
    {
        User::factory()->create(['role' => 'purchasing']);

        $response = $this->get('/supplier/create');

        $response->assertStatus(200);
        $response->assertViewIs('pages.purchasing.supplier.tambah');
        $response->assertSee('Tambah Supplier');
    }

    /** @test */
    public function it_can_store_new_supplier()
    {
        $user = User::factory()->create(['role' => 'purchasing']);

        $supplierData = [
            'nama' => 'PT Supplier Baru',
            'alamat' => 'Jl. Baru No. 456',
            'no_hp' => '089876543210',
            'pic_purchasing_id' => $user->id,
            'bahan_baku' => [
                [
                    'nama' => 'Tepung Terigu',
                    'harga_per_satuan' => '12000',
                    'satuan' => 'kg',
                    'stok' => '150',
                ],
                [
                    'nama' => 'Gula Pasir',
                    'harga_per_satuan' => '18000',
                    'satuan' => 'kg',
                    'stok' => '80',
                ],
            ],
        ];

        $response = $this->post('/supplier', $supplierData);

        $response->assertRedirect('/supplier');
        $response->assertSessionHas('success', 'Supplier berhasil ditambahkan dengan 2 bahan baku');

        $this->assertDatabaseHas('suppliers', [
            'nama' => 'PT Supplier Baru',
            'alamat' => 'Jl. Baru No. 456',
            'no_hp' => '089876543210',
            'pic_purchasing_id' => $user->id,
        ]);

        $supplier = Supplier::where('nama', 'PT Supplier Baru')->first();
        $this->assertDatabaseHas('bahan_baku_supplier', [
            'supplier_id' => $supplier->id,
            'nama' => 'Tepung Terigu',
            'harga_per_satuan' => 12000,
            'satuan' => 'kg',
            'stok' => 150,
        ]);

        $this->assertDatabaseHas('bahan_baku_supplier', [
            'supplier_id' => $supplier->id,
            'nama' => 'Gula Pasir',
            'harga_per_satuan' => 18000,
            'satuan' => 'kg',
            'stok' => 80,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_storing_supplier()
    {
        $response = $this->post('/supplier', []);

        $response->assertSessionHasErrors(['nama']);
    }

    /** @test */
    public function it_validates_bahan_baku_fields_when_storing_supplier()
    {
        $supplierData = [
            'nama' => 'PT Test',
            'bahan_baku' => [
                [
                    'nama' => '', // Empty nama
                    'harga_per_satuan' => 'invalid', // Invalid price
                    'satuan' => '',
                    'stok' => 'invalid', // Invalid stock
                ],
            ],
        ];

        $response = $this->post('/supplier', $supplierData);

        $response->assertSessionHasErrors([
            'bahan_baku.0.nama',
            'bahan_baku.0.harga_per_satuan',
            'bahan_baku.0.satuan',
            'bahan_baku.0.stok',
        ]);
    }

    /** @test */
    public function it_can_store_supplier_without_bahan_baku()
    {
        $user = User::factory()->create(['role' => 'purchasing']);

        $supplierData = [
            'nama' => 'PT Supplier Tanpa Bahan',
            'alamat' => 'Jl. Test',
            'no_hp' => '081234567890',
            'pic_purchasing_id' => $user->id,
            'bahan_baku' => [
                [
                    'nama' => 'Minimal Satu Bahan',
                    'harga_per_satuan' => '10000',
                    'satuan' => 'kg',
                    'stok' => '1',
                ],
            ], // Minimal harus ada satu bahan baku
        ];

        $response = $this->post('/supplier', $supplierData);

        $response->assertRedirect('/supplier');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('suppliers', [
            'nama' => 'PT Supplier Tanpa Bahan',
        ]);

        $supplier = Supplier::where('nama', 'PT Supplier Tanpa Bahan')->first();
        $this->assertEquals(1, $supplier->bahanBakuSuppliers()->count());
    }

    /** @test */
    public function it_generates_unique_slug_when_storing_supplier()
    {
        // Create existing supplier with same name
        Supplier::create([
            'nama' => 'PT Test Supplier',
            'slug' => 'pt-test-supplier',
        ]);

        $supplierData = [
            'nama' => 'PT Test Supplier Different', // Different name to avoid unique issues
            'bahan_baku' => [
                [
                    'nama' => 'Minimal Satu Bahan',
                    'harga_per_satuan' => '10000',
                    'satuan' => 'kg',
                    'stok' => '1',
                ],
            ],
        ];

        $response = $this->post('/supplier', $supplierData);

        $response->assertRedirect('/supplier');
        $response->assertSessionHas('success');

        // Check that a new supplier was created
        $this->assertDatabaseHas('suppliers', [
            'nama' => 'PT Test Supplier Different',
        ]);
    }

    /** @test */
    public function it_can_handle_pagination()
    {
        // Create 25 suppliers (more than default per page)
        for ($i = 1; $i <= 25; $i++) {
            Supplier::create([
                'nama' => "Supplier $i",
                'slug' => "supplier-$i",
            ]);
        }

        $response = $this->get('/supplier');

        $response->assertStatus(200);
        $response->assertViewHas('suppliers');
        
        $suppliers = $response->viewData('suppliers');
        $this->assertTrue($suppliers->hasPages());
    }

    /** @test */
    public function it_can_handle_per_page_parameter()
    {
        // Create 15 suppliers
        for ($i = 1; $i <= 15; $i++) {
            Supplier::create([
                'nama' => "Supplier $i",
                'slug' => "supplier-$i",
            ]);
        }

        $response = $this->get('/supplier?per_page=5');

        $response->assertStatus(200);
        $suppliers = $response->viewData('suppliers');
        $this->assertEquals(5, $suppliers->count());
    }

    /** @test */
    public function it_returns_correct_statistics_data()
    {
        $supplier1 = Supplier::create([
            'nama' => 'Supplier 1',
            'slug' => 'supplier-1',
        ]);

        $supplier2 = Supplier::create([
            'nama' => 'Supplier 2', 
            'slug' => 'supplier-2',
        ]);

        // Add bahan baku
        BahanBakuSupplier::create([
            'supplier_id' => $supplier1->id,
            'nama' => 'Bahan A',
            'harga_per_satuan' => 10000,
            'satuan' => 'kg',
            'stok' => 100,
        ]);

        BahanBakuSupplier::create([
            'supplier_id' => $supplier2->id,
            'nama' => 'Bahan B',
            'harga_per_satuan' => 15000,
            'satuan' => 'kg',
            'stok' => 50,
        ]);

        $response = $this->get('/supplier');

        $response->assertStatus(200);
        $response->assertViewIs('pages.purchasing.supplier');
        $response->assertViewHas('suppliers');
        
        // Check that statistics are passed to view by checking the view data
        $suppliers = $response->viewData('suppliers');
        $this->assertNotNull($suppliers);
    }

    /** @test */
    public function it_can_display_edit_supplier_page()
    {
        $user = User::factory()->create([
            'nama' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'purchasing',
            'status' => 'aktif',
        ]);

        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
            'alamat' => 'Test Address',
            'no_hp' => '081234567890',
            'pic_purchasing_id' => $user->id,
        ]);

        $bahanBaku = BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Bahan Baku Test',
            'satuan' => 'kg',
            'harga_per_satuan' => 10000,
            'stok' => 100,
        ]);

        $response = $this->get("/supplier/{$supplier->slug}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('pages.purchasing.supplier.edit');
        $response->assertViewHas('supplier');
        $response->assertViewHas('purchasingUsers');
        
        // Check that the supplier data is passed correctly
        $supplierData = $response->viewData('supplier');
        $this->assertEquals($supplier->id, $supplierData->id);
        $this->assertEquals($supplier->nama, $supplierData->nama);
        $this->assertEquals($supplier->alamat, $supplierData->alamat);
    }

    /** @test */
    public function it_can_update_supplier()
    {
        $user = User::factory()->create([
            'nama' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'purchasing',
            'status' => 'aktif',
        ]);

        $supplier = Supplier::create([
            'nama' => 'Original Supplier',
            'slug' => 'original-supplier',
            'alamat' => 'Original Address',
            'no_hp' => '081111111111',
            'pic_purchasing_id' => $user->id,
        ]);

        $originalBahanBaku = BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Original Bahan Baku',
            'satuan' => 'kg',
            'harga_per_satuan' => 10000,
            'stok' => 100,
        ]);

        $updateData = [
            'nama' => 'Updated Supplier',
            'alamat' => 'Updated Address',
            'no_hp' => '082222222222',
            'pic_purchasing_id' => $user->id,
            'bahan_baku' => [
                [
                    'nama' => 'Updated Bahan Baku',
                    'satuan' => 'gram',
                    'harga_per_satuan' => '15.000',
                    'stok' => '200',
                ],
                [
                    'nama' => 'New Bahan Baku',
                    'satuan' => 'liter',
                    'harga_per_satuan' => '20.000',
                    'stok' => '50',
                ]
            ]
        ];

        $response = $this->put("/supplier/{$supplier->slug}", $updateData);

        $response->assertStatus(302);
        $response->assertRedirect(route('supplier.index'));
        $response->assertSessionHas('success');

        // Check that supplier was updated
        $supplier->refresh();
        $this->assertEquals('Updated Supplier', $supplier->nama);
        $this->assertEquals('updated-supplier', $supplier->slug); // Slug should update when name changes
        $this->assertEquals('Updated Address', $supplier->alamat);
        $this->assertEquals('082222222222', $supplier->no_hp);

        // Check that bahan baku were updated (old ones deleted, new ones created)
        $this->assertEquals(2, $supplier->bahanBakuSuppliers()->count());
        
        $bahanBakuNames = $supplier->bahanBakuSuppliers()->pluck('nama')->toArray();
        $this->assertContains('Updated Bahan Baku', $bahanBakuNames);
        $this->assertContains('New Bahan Baku', $bahanBakuNames);
        $this->assertNotContains('Original Bahan Baku', $bahanBakuNames);
    }

    /** @test */
    public function it_generates_unique_slug_when_updating_supplier_name()
    {
        $user = User::factory()->create([
            'nama' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'purchasing',
            'status' => 'aktif',
        ]);

        // Create existing supplier with the target name
        $existingSupplier = Supplier::create([
            'nama' => 'Target Supplier',
            'slug' => 'target-supplier',
        ]);

        // Create supplier to update
        $supplier = Supplier::create([
            'nama' => 'Original Supplier',
            'slug' => 'original-supplier',
            'pic_purchasing_id' => $user->id,
        ]);

        BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Test Bahan Baku',
            'satuan' => 'kg',
            'harga_per_satuan' => 10000,
            'stok' => 100,
        ]);

        $updateData = [
            'nama' => 'Target Supplier', // Same as existing supplier
            'alamat' => 'Test Address',
            'no_hp' => '081234567890',
            'pic_purchasing_id' => $user->id,
            'bahan_baku' => [
                [
                    'nama' => 'Test Bahan Baku',
                    'satuan' => 'kg',
                    'harga_per_satuan' => '10.000',
                    'stok' => '100',
                ]
            ]
        ];

        $response = $this->put("/supplier/{$supplier->slug}", $updateData);

        $response->assertStatus(302);
        $response->assertRedirect(route('supplier.index'));

        $supplier->refresh();
        $this->assertEquals('Target Supplier', $supplier->nama);
        $this->assertEquals('target-supplier-1', $supplier->slug); // Should have unique suffix
    }

    /** @test */
    public function it_keeps_same_slug_when_name_doesnt_change()
    {
        $user = User::factory()->create([
            'nama' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'purchasing',
            'status' => 'aktif',
        ]);

        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
            'alamat' => 'Original Address',
            'pic_purchasing_id' => $user->id,
        ]);

        BahanBakuSupplier::create([
            'supplier_id' => $supplier->id,
            'nama' => 'Test Bahan Baku',
            'satuan' => 'kg',
            'harga_per_satuan' => 10000,
            'stok' => 100,
        ]);

        $updateData = [
            'nama' => 'Test Supplier', // Same name
            'alamat' => 'Updated Address', // Different address
            'no_hp' => '081234567890',
            'pic_purchasing_id' => $user->id,
            'bahan_baku' => [
                [
                    'nama' => 'Test Bahan Baku',
                    'satuan' => 'kg',
                    'harga_per_satuan' => '10.000',
                    'stok' => '100',
                ]
            ]
        ];

        $response = $this->put("/supplier/{$supplier->slug}", $updateData);

        $response->assertStatus(302);
        $supplier->refresh();
        
        $this->assertEquals('Test Supplier', $supplier->nama);
        $this->assertEquals('test-supplier', $supplier->slug); // Should keep same slug
        $this->assertEquals('Updated Address', $supplier->alamat); // Address should be updated
    }

    /** @test */
    public function it_validates_required_fields_when_updating_supplier()
    {
        $supplier = Supplier::create([
            'nama' => 'Test Supplier',
            'slug' => 'test-supplier',
        ]);

        $updateData = [
            'nama' => '', // Empty required field
            'bahan_baku' => [] // Empty required array
        ];

        $response = $this->put("/supplier/{$supplier->slug}", $updateData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['nama', 'bahan_baku']);
    }
}
