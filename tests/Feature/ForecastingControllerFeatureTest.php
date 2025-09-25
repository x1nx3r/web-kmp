<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderBahanBaku;
use App\Models\Forecast;
use App\Models\ForecastDetail;
use App\Models\BahanBakuSupplier;
use App\Models\BahanBakuKlien;
use App\Models\Klien;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class ForecastingControllerFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user for authentication
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_forecast_index_page_loads_successfully()
    {
        // Arrange
        $klien = Klien::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create([
            'klien_id' => $klien->id,
            'status' => 'siap'
        ]);

        // Act
        $response = $this->get('/forecast'); // Adjust route as needed

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('pages.purchasing.forecast');
        $response->assertViewHas(['purchaseOrders', 'pendingForecasts', 'suksesForecasts', 'gagalForecasts']);
    }

    public function test_forecast_index_displays_purchase_orders_with_correct_status()
    {
        // Arrange
        $klien = Klien::factory()->create();
        
        // Create PO with status 'siap' (should be displayed)
        $siapPO = PurchaseOrder::factory()->create([
            'klien_id' => $klien->id,
            'status' => 'siap',
            'no_po' => 'PO-SIAP-001'
        ]);

        // Create PO with status 'selesai' (should not be displayed)
        $selesaiPO = PurchaseOrder::factory()->create([
            'klien_id' => $klien->id,
            'status' => 'selesai',
            'no_po' => 'PO-SELESAI-001'
        ]);

        // Act
        $response = $this->get('/forecast');

        // Assert
        $response->assertStatus(200);
        $response->assertSeeText('PO-SIAP-001');
        $response->assertDontSeeText('PO-SELESAI-001');
    }

    public function test_forecast_index_search_functionality()
    {
        // Arrange
        $klien1 = Klien::factory()->create(['nama' => 'PT ABC']);
        $klien2 = Klien::factory()->create(['nama' => 'PT XYZ']);
        
        $po1 = PurchaseOrder::factory()->create([
            'klien_id' => $klien1->id,
            'status' => 'siap',
            'no_po' => 'PO-ABC-001'
        ]);

        $po2 = PurchaseOrder::factory()->create([
            'klien_id' => $klien2->id,
            'status' => 'siap',
            'no_po' => 'PO-XYZ-001'
        ]);

        // Act
        $response = $this->get('/forecast?search=ABC');

        // Assert
        $response->assertStatus(200);
        $response->assertSeeText('PO-ABC-001');
        $response->assertDontSeeText('PO-XYZ-001');
    }

    public function test_get_bahan_baku_suppliers_endpoint_returns_json()
    {
        // Arrange
        $bahanBakuKlien = BahanBakuKlien::factory()->create();
        $purchaseOrderBahanBaku = PurchaseOrderBahanBaku::factory()->create([
            'bahan_baku_klien_id' => $bahanBakuKlien->id
        ]);

        $supplier = Supplier::factory()->create();
        $bahanBakuSupplier = BahanBakuSupplier::factory()->create([
            'supplier_id' => $supplier->id
        ]);

        // Act
        $response = $this->get("/forecast/bahan-baku-suppliers/{$purchaseOrderBahanBaku->id}");

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'purchase_order_bahan_baku',
            'bahan_baku_suppliers'
        ]);
    }

    public function test_get_bahan_baku_suppliers_returns_404_for_invalid_id()
    {
        // Act
        $response = $this->get('/forecast/bahan-baku-suppliers/999');

        // Assert
        $response->assertStatus(404);
        $response->assertJson([
            'error' => 'Data tidak ditemukan'
        ]);
    }

    public function test_create_forecast_with_valid_data()
    {
        // Arrange
        $klien = Klien::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create([
            'klien_id' => $klien->id,
            'status' => 'siap'
        ]);

        $bahanBakuKlien = BahanBakuKlien::factory()->create();
        $purchaseOrderBahanBaku = PurchaseOrderBahanBaku::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'bahan_baku_klien_id' => $bahanBakuKlien->id,
            'harga_satuan' => 10000
        ]);

        $supplier = Supplier::factory()->create();
        $bahanBakuSupplier = BahanBakuSupplier::factory()->create([
            'supplier_id' => $supplier->id
        ]);

        $forecastData = [
            'purchase_order_id' => $purchaseOrder->id,
            'tanggal_forecast' => '2025-09-24',
            'hari_kirim_forecast' => 'Senin',
            'catatan' => 'Test forecast creation',
            'details' => [
                [
                    'purchase_order_bahan_baku_id' => $purchaseOrderBahanBaku->id,
                    'bahan_baku_supplier_id' => $bahanBakuSupplier->id,
                    'qty_forecast' => 100,
                    'harga_satuan_forecast' => 12000,
                    'catatan_detail' => 'Test detail forecast'
                ]
            ]
        ];

        // Act
        $response = $this->postJson('/forecast/create', $forecastData);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Forecast berhasil dibuat'
        ]);

        // Verify database
        $this->assertDatabaseHas('forecasts', [
            'purchase_order_id' => $purchaseOrder->id,
            'tanggal_forecast' => '2025-09-24',
            'hari_kirim_forecast' => 'Senin',
            'status' => 'pending',
            'catatan' => 'Test forecast creation'
        ]);

        $forecast = Forecast::where('purchase_order_id', $purchaseOrder->id)->first();
        
        $this->assertDatabaseHas('forecast_details', [
            'forecast_id' => $forecast->id,
            'purchase_order_bahan_baku_id' => $purchaseOrderBahanBaku->id,
            'bahan_baku_supplier_id' => $bahanBakuSupplier->id,
            'qty_forecast' => 100,
            'harga_satuan_forecast' => 12000,
            'total_harga_forecast' => 1200000, // 100 * 12000
            'harga_satuan_po' => 10000,
            'total_harga_po' => 1000000, // 100 * 10000
            'catatan_detail' => 'Test detail forecast'
        ]);
    }

    public function test_create_forecast_with_multiple_details()
    {
        // Arrange
        $klien = Klien::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create([
            'klien_id' => $klien->id,
            'status' => 'siap'
        ]);

        $bahanBakuKlien1 = BahanBakuKlien::factory()->create();
        $bahanBakuKlien2 = BahanBakuKlien::factory()->create();

        $purchaseOrderBahanBaku1 = PurchaseOrderBahanBaku::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'bahan_baku_klien_id' => $bahanBakuKlien1->id,
            'harga_satuan' => 10000
        ]);

        $purchaseOrderBahanBaku2 = PurchaseOrderBahanBaku::factory()->create([
            'purchase_order_id' => $purchaseOrder->id,
            'bahan_baku_klien_id' => $bahanBakuKlien2->id,
            'harga_satuan' => 15000
        ]);

        $supplier = Supplier::factory()->create();
        $bahanBakuSupplier = BahanBakuSupplier::factory()->create([
            'supplier_id' => $supplier->id
        ]);

        $forecastData = [
            'purchase_order_id' => $purchaseOrder->id,
            'tanggal_forecast' => '2025-09-24',
            'hari_kirim_forecast' => 'Selasa',
            'details' => [
                [
                    'purchase_order_bahan_baku_id' => $purchaseOrderBahanBaku1->id,
                    'bahan_baku_supplier_id' => $bahanBakuSupplier->id,
                    'qty_forecast' => 50,
                    'harga_satuan_forecast' => 11000
                ],
                [
                    'purchase_order_bahan_baku_id' => $purchaseOrderBahanBaku2->id,
                    'bahan_baku_supplier_id' => $bahanBakuSupplier->id,
                    'qty_forecast' => 30,
                    'harga_satuan_forecast' => 16000
                ]
            ]
        ];

        // Act
        $response = $this->postJson('/forecast/create', $forecastData);

        // Assert
        $response->assertStatus(200);

        $forecast = Forecast::where('purchase_order_id', $purchaseOrder->id)->first();
        
        // Verify totals: (50 * 11000) + (30 * 16000) = 550000 + 480000 = 1030000
        $this->assertEquals(80, $forecast->total_qty_forecast); // 50 + 30
        $this->assertEquals(1030000, $forecast->total_harga_forecast);

        // Verify both details exist
        $this->assertDatabaseCount('forecast_details', 2);
    }

    public function test_create_forecast_validation_errors()
    {
        // Test missing required fields
        $response = $this->postJson('/forecast/create', []);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Validation failed'
        ]);
        $response->assertJsonValidationErrors([
            'purchase_order_id',
            'tanggal_forecast',
            'hari_kirim_forecast',
            'details'
        ]);
    }

    public function test_create_forecast_with_invalid_purchase_order_id()
    {
        $forecastData = [
            'purchase_order_id' => 999, // Non-existent ID
            'tanggal_forecast' => '2025-09-24',
            'hari_kirim_forecast' => 'Senin',
            'details' => [
                [
                    'purchase_order_bahan_baku_id' => 1,
                    'bahan_baku_supplier_id' => 1,
                    'qty_forecast' => 100,
                    'harga_satuan_forecast' => 12000
                ]
            ]
        ];

        $response = $this->postJson('/forecast/create', $forecastData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['purchase_order_id']);
    }

    public function test_create_forecast_with_invalid_date()
    {
        $klien = Klien::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create([
            'klien_id' => $klien->id
        ]);

        $forecastData = [
            'purchase_order_id' => $purchaseOrder->id,
            'tanggal_forecast' => 'invalid-date',
            'hari_kirim_forecast' => 'Senin',
            'details' => [
                [
                    'purchase_order_bahan_baku_id' => 1,
                    'bahan_baku_supplier_id' => 1,
                    'qty_forecast' => 100,
                    'harga_satuan_forecast' => 12000
                ]
            ]
        ];

        $response = $this->postJson('/forecast/create', $forecastData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tanggal_forecast']);
    }

    public function test_create_forecast_with_empty_details()
    {
        $klien = Klien::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create([
            'klien_id' => $klien->id
        ]);

        $forecastData = [
            'purchase_order_id' => $purchaseOrder->id,
            'tanggal_forecast' => '2025-09-24',
            'hari_kirim_forecast' => 'Senin',
            'details' => [] // Empty array
        ];

        $response = $this->postJson('/forecast/create', $forecastData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['details']);
    }

    public function test_create_forecast_with_negative_quantities()
    {
        $klien = Klien::factory()->create();
        $purchaseOrder = PurchaseOrder::factory()->create([
            'klien_id' => $klien->id
        ]);

        $forecastData = [
            'purchase_order_id' => $purchaseOrder->id,
            'tanggal_forecast' => '2025-09-24',
            'hari_kirim_forecast' => 'Senin',
            'details' => [
                [
                    'purchase_order_bahan_baku_id' => 1,
                    'bahan_baku_supplier_id' => 1,
                    'qty_forecast' => -10, // Negative quantity
                    'harga_satuan_forecast' => 12000
                ]
            ]
        ];

        $response = $this->postJson('/forecast/create', $forecastData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['details.0.qty_forecast']);
    }

    public function test_forecast_generates_unique_forecast_numbers()
    {
        // Arrange
        $klien = Klien::factory()->create();
        $purchaseOrder1 = PurchaseOrder::factory()->create(['klien_id' => $klien->id]);
        $purchaseOrder2 = PurchaseOrder::factory()->create(['klien_id' => $klien->id]);

        $bahanBakuKlien = BahanBakuKlien::factory()->create();
        $purchaseOrderBahanBaku1 = PurchaseOrderBahanBaku::factory()->create([
            'purchase_order_id' => $purchaseOrder1->id,
            'bahan_baku_klien_id' => $bahanBakuKlien->id
        ]);
        $purchaseOrderBahanBaku2 = PurchaseOrderBahanBaku::factory()->create([
            'purchase_order_id' => $purchaseOrder2->id,
            'bahan_baku_klien_id' => $bahanBakuKlien->id
        ]);

        $supplier = Supplier::factory()->create();
        $bahanBakuSupplier = BahanBakuSupplier::factory()->create([
            'supplier_id' => $supplier->id
        ]);

        $baseData = [
            'tanggal_forecast' => '2025-09-24',
            'hari_kirim_forecast' => 'Senin',
            'details' => [
                [
                    'bahan_baku_supplier_id' => $bahanBakuSupplier->id,
                    'qty_forecast' => 100,
                    'harga_satuan_forecast' => 12000
                ]
            ]
        ];

        // Act - Create first forecast
        $forecastData1 = array_merge($baseData, [
            'purchase_order_id' => $purchaseOrder1->id,
            'details' => [
                array_merge($baseData['details'][0], [
                    'purchase_order_bahan_baku_id' => $purchaseOrderBahanBaku1->id
                ])
            ]
        ]);

        $response1 = $this->postJson('/forecast/create', $forecastData1);

        // Create second forecast
        $forecastData2 = array_merge($baseData, [
            'purchase_order_id' => $purchaseOrder2->id,
            'details' => [
                array_merge($baseData['details'][0], [
                    'purchase_order_bahan_baku_id' => $purchaseOrderBahanBaku2->id
                ])
            ]
        ]);

        $response2 = $this->postJson('/forecast/create', $forecastData2);

        // Assert
        $response1->assertStatus(200);
        $response2->assertStatus(200);

        $forecast1 = json_decode($response1->getContent(), true)['forecast'];
        $forecast2 = json_decode($response2->getContent(), true)['forecast'];

        // Both should have forecast numbers but they should be different
        $this->assertNotEquals($forecast1['no_forecast'], $forecast2['no_forecast']);
        
        // Both should follow the pattern FC-YYYYMM-NNNN
        $pattern = '/^FC-\d{6}-\d{4}$/';
        $this->assertMatchesRegularExpression($pattern, $forecast1['no_forecast']);
        $this->assertMatchesRegularExpression($pattern, $forecast2['no_forecast']);
    }
}
