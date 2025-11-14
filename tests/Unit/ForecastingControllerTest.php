<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\Purchasing\ForecastingController;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Forecast;
use App\Models\ForecastDetail;
use App\Models\BahanBakuSupplier;
use App\Models\Klien;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class ForecastingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ForecastingController();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_returns_view_with_correct_data()
    {
        // Arrange
        $klien = Klien::factory()->create();
        $order = Order::factory()->create([
            'klien_id' => $klien->id,
            'status' => 'dikonfirmasi'
        ]);
        
        $orderDetail = OrderDetail::factory()->create([
            'order_id' => $order->id
        ]);

        // Act
        $response = $this->controller->index();

        // Assert
        $this->assertEquals('pages.purchasing.forecast', $response->name());
        $this->assertArrayHasKey('orders', $response->getData());
        $this->assertArrayHasKey('pendingForecasts', $response->getData());
        $this->assertArrayHasKey('suksesForecasts', $response->getData());
        $this->assertArrayHasKey('gagalForecasts', $response->getData());
    }

    public function test_getBahanBakuSuppliers_returns_correct_data()
    {
        // Arrange
        $orderDetail = OrderDetail::factory()->create();
        $bahanBakuSupplier = BahanBakuSupplier::factory()->create();

        // Act
        $response = $this->controller->getBahanBakuSuppliers($orderDetail->id);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('order_detail', $responseData);
        $this->assertArrayHasKey('bahan_baku_suppliers', $responseData);
    }

    public function test_getBahanBakuSuppliers_returns_404_when_not_found()
    {
        // Act
        $response = $this->controller->getBahanBakuSuppliers(999);

        // Assert
        $this->assertEquals(404, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Data tidak ditemukan', $responseData['error']);
    }

    public function test_createForecast_validation_fails_with_invalid_data()
    {
        // Arrange
        $request = new Request([
            'order_id' => null, // Invalid: required field
            'tanggal_forecast' => 'invalid-date', // Invalid: not a date
            'details' => [] // Invalid: array must have at least 1 item
        ]);

        // Act
        $response = $this->controller->createForecast($request);

        // Assert
        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Validation failed', $responseData['message']);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function test_createForecast_creates_forecast_successfully()
    {
        // Arrange
        $order = Order::factory()->create();
        $orderDetail = OrderDetail::factory()->create([
            'order_id' => $order->id,
            'harga_jual' => 10000
        ]);
        $bahanBakuSupplier = BahanBakuSupplier::factory()->create();

        $requestData = [
            'order_id' => $order->id,
            'tanggal_forecast' => '2025-09-24',
            'hari_kirim_forecast' => 'Senin',
            'catatan' => 'Test forecast',
            'details' => [
                [
                    'order_detail_id' => $orderDetail->id,
                    'bahan_baku_supplier_id' => $bahanBakuSupplier->id,
                    'qty_forecast' => 100,
                    'harga_satuan_forecast' => 12000,
                    'catatan_detail' => 'Test detail'
                ]
            ]
        ];

        $request = new Request($requestData);

        // Act
        $response = $this->controller->createForecast($request);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Forecast berhasil dibuat', $responseData['message']);
        $this->assertArrayHasKey('forecast', $responseData);

        // Verify database
        $this->assertDatabaseHas('forecasts', [
            'order_id' => $order->id,
            'tanggal_forecast' => '2025-09-24',
            'hari_kirim_forecast' => 'Senin',
            'status' => 'pending',
            'catatan' => 'Test forecast'
        ]);

        $this->assertDatabaseHas('forecast_details', [
            'order_detail_id' => $orderDetail->id,
            'bahan_baku_supplier_id' => $bahanBakuSupplier->id,
            'qty_forecast' => 100,
            'harga_satuan_forecast' => 12000,
            'catatan_detail' => 'Test detail'
        ]);
    }

    public function test_createForecast_generates_correct_forecast_number()
    {
        // Arrange
        // Create existing forecasts to test counter
        Forecast::factory()->count(3)->create();
        
        $order = Order::factory()->create();
        $orderDetail = OrderDetail::factory()->create([
            'order_id' => $order->id
        ]);
        $bahanBakuSupplier = BahanBakuSupplier::factory()->create();

        $requestData = [
            'order_id' => $order->id,
            'tanggal_forecast' => '2025-09-24',
            'hari_kirim_forecast' => 'Senin',
            'details' => [
                [
                    'order_detail_id' => $orderDetail->id,
                    'bahan_baku_supplier_id' => $bahanBakuSupplier->id,
                    'qty_forecast' => 100,
                    'harga_satuan_forecast' => 12000
                ]
            ]
        ];

        $request = new Request($requestData);

        // Act
        $response = $this->controller->createForecast($request);

        // Assert
        $responseData = json_decode($response->getContent(), true);
        $forecast = $responseData['forecast'];
        
        // Should be FC-{YEAR}{MONTH}-{NEXT_NUMBER}
        $expectedPattern = '/^FC-\d{6}-\d{4}$/';
        $this->assertMatchesRegularExpression($expectedPattern, $forecast['no_forecast']);
    }

    public function test_createForecast_calculates_totals_correctly()
    {
        // Arrange
        $order = Order::factory()->create();
        $orderDetail = OrderDetail::factory()->create([
            'order_id' => $order->id,
            'harga_jual' => 10000
        ]);
        $bahanBakuSupplier = BahanBakuSupplier::factory()->create();

        $requestData = [
            'order_id' => $order->id,
            'tanggal_forecast' => '2025-09-24',
            'hari_kirim_forecast' => 'Senin',
            'details' => [
                [
                    'order_detail_id' => $orderDetail->id,
                    'bahan_baku_supplier_id' => $bahanBakuSupplier->id,
                    'qty_forecast' => 100,
                    'harga_satuan_forecast' => 12000
                ]
            ]
        ];

        $request = new Request($requestData);

        // Act
        $response = $this->controller->createForecast($request);

        // Assert
        $responseData = json_decode($response->getContent(), true);
        $forecast = $responseData['forecast'];
        
        $this->assertEquals(100, $forecast['total_qty_forecast']);
        $this->assertEquals(1200000, $forecast['total_harga_forecast']); // 100 * 12000
    }

    public function test_createForecast_handles_database_error()
    {
        // Arrange
        $order = Order::factory()->create();
        
        $requestData = [
            'order_id' => $order->id,
            'tanggal_forecast' => '2025-09-24',
            'hari_kirim_forecast' => 'Senin',
            'details' => [
                [
                    'order_detail_id' => 999, // Non-existent ID
                    'bahan_baku_supplier_id' => 999, // Non-existent ID
                    'qty_forecast' => 100,
                    'harga_satuan_forecast' => 12000
                ]
            ]
        ];

        $request = new Request($requestData);

        // Act
        $response = $this->controller->createForecast($request);

        // Assert - This will be 422 due to validation failure on foreign key constraints
        $this->assertEquals(422, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Validation failed', $responseData['message']);
    }
}
