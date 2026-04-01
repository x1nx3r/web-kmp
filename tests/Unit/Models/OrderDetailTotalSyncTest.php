<?php

namespace Tests\Unit\Models;

use App\Models\Klien;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDetailTotalSyncTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $klien;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->klien = Klien::factory()->create();
    }

    public function test_order_totals_recalculate_on_detail_saved()
    {
        $order = Order::create([
            'klien_id' => $this->klien->id,
            'created_by' => $this->user->id,
            'tanggal_order' => now(),
            'status' => 'draft',
        ]);

        $detail = new OrderDetail([
            'order_id' => $order->id,
            'qty' => 10,
            'harga_jual' => 1000,
            'total_harga' => 10000,
            'status' => 'menunggu',
        ]);
        $detail->save();

        $order->refresh();
        $this->assertEquals(10000, $order->total_amount);
        $this->assertEquals(1, $order->total_items);
        $this->assertEquals(10, $order->total_qty);
    }

    public function test_order_totals_recalculate_on_detail_deleted()
    {
        $order = Order::create([
            'klien_id' => $this->klien->id,
            'created_by' => $this->user->id,
            'tanggal_order' => now(),
            'status' => 'draft',
        ]);

        $detail1 = OrderDetail::create([
            'order_id' => $order->id,
            'qty' => 10,
            'harga_jual' => 1000,
            'total_harga' => 10000,
            'status' => 'menunggu',
        ]);

        $detail2 = OrderDetail::create([
            'order_id' => $order->id,
            'qty' => 5,
            'harga_jual' => 2000,
            'total_harga' => 10000,
            'status' => 'menunggu',
        ]);

        $order->refresh();
        $this->assertEquals(20000, $order->total_amount);

        // Soft delete one detail
        $detail1->delete();

        $order->refresh();
        $this->assertEquals(10000, $order->total_amount, 'Total amount should decrease after soft-delete');
        $this->assertEquals(1, $order->total_items);
        $this->assertEquals(5, $order->total_qty);
    }

    public function test_order_totals_recalculate_on_detail_restored()
    {
        $order = Order::create([
            'klien_id' => $this->klien->id,
            'created_by' => $this->user->id,
            'tanggal_order' => now(),
            'status' => 'draft',
        ]);

        $detail = OrderDetail::create([
            'order_id' => $order->id,
            'qty' => 10,
            'harga_jual' => 1000,
            'total_harga' => 10000,
            'status' => 'menunggu',
        ]);

        $detail->delete();
        $order->refresh();
        $this->assertEquals(0, $order->total_amount);

        // Restore the detail
        $detail->restore();

        $order->refresh();
        $this->assertEquals(10000, $order->total_amount, 'Total amount should increase after restore');
        $this->assertEquals(1, $order->total_items);
        $this->assertEquals(10, $order->total_qty);
    }
}
