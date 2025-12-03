<?php

namespace Tests\Unit\Services\Notifications;

use App\Models\Klien;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\User;
use App\Services\Notifications\OrderNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $marketingUser;
    protected User $direkturUser;
    protected Klien $klien;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->marketingUser = User::factory()->create([
            "nama" => "Marketing User",
            "role" => "marketing",
            "status" => "aktif",
        ]);

        $this->direkturUser = User::factory()->create([
            "nama" => "Direktur User",
            "role" => "direktur",
            "status" => "aktif",
        ]);

        // Create test klien
        $this->klien = Klien::factory()->create([
            "nama" => "PT Test Klien",
        ]);

        // Create test order
        $this->order = Order::factory()->create([
            "no_order" => "ORD-TEST-001",
            "po_number" => "PO-TEST-001",
            "klien_id" => $this->klien->id,
            "created_by" => $this->marketingUser->id,
            "status" => "diproses",
            "total_qty" => 100,
        ]);
    }

    /** @test */
    public function it_has_correct_type_constants()
    {
        $this->assertEquals(
            "order_nearing_fulfillment",
            OrderNotificationService::TYPE_NEARING_FULFILLMENT,
        );
        $this->assertEquals(
            "order_direktur_consultation",
            OrderNotificationService::TYPE_DIREKTUR_CONSULTATION,
        );
        $this->assertEquals(
            "order_completed",
            OrderNotificationService::TYPE_COMPLETED,
        );
        $this->assertEquals(
            "order_cancelled",
            OrderNotificationService::TYPE_CANCELLED,
        );
    }

    /** @test */
    public function it_has_correct_fulfillment_threshold_constants()
    {
        $this->assertEquals(
            95,
            OrderNotificationService::FULFILLMENT_THRESHOLD_MIN,
        );
        $this->assertEquals(
            105,
            OrderNotificationService::FULFILLMENT_THRESHOLD_MAX,
        );
    }

    /** @test */
    public function notify_nearing_fulfillment_sends_notification_to_order_creator()
    {
        $fulfillmentPercentage = 98.5;

        $notificationId = OrderNotificationService::notifyNearingFulfillment(
            $this->order,
            $fulfillmentPercentage,
        );

        $this->assertNotNull($notificationId);
        $this->assertDatabaseHas("notifications", [
            "id" => $notificationId,
            "type" => OrderNotificationService::TYPE_NEARING_FULFILLMENT,
            "notifiable_type" => User::class,
            "notifiable_id" => $this->marketingUser->id,
        ]);

        // Verify notification data
        $notification = DB::table("notifications")
            ->where("id", $notificationId)
            ->first();
        $data = json_decode($notification->data, true);

        $this->assertStringContainsString("mendekati target", $data["title"]);
        $this->assertStringContainsString("98.5%", $data["title"]);
        $this->assertStringContainsString(
            $this->order->po_number,
            $data["message"],
        );
        $this->assertStringContainsString($this->klien->nama, $data["message"]);
        $this->assertEquals("/orders/{$this->order->id}", $data["url"]);
        $this->assertEquals($this->order->id, $data["order_id"]);
        $this->assertEquals(
            $fulfillmentPercentage,
            $data["fulfillment_percentage"],
        );
    }

    /** @test */
    public function notify_nearing_fulfillment_returns_null_when_order_has_no_creator()
    {
        // Create a mock Order that returns null for creator relationship
        $orderMock = $this->createPartialMock(Order::class, ["__get"]);
        $orderMock->id = 999;
        $orderMock->no_order = "ORD-MOCK-001";
        $orderMock->po_number = "PO-MOCK-001";
        $orderMock->total_qty = 100;

        $orderMock
            ->method("__get")
            ->willReturnCallback(function ($property) use ($orderMock) {
                if ($property === "creator") {
                    return null;
                }
                return $orderMock->$property ?? null;
            });

        $notificationId = OrderNotificationService::notifyNearingFulfillment(
            $orderMock,
            95.0,
        );

        $this->assertNull($notificationId);
    }

    /** @test */
    public function notify_nearing_fulfillment_uses_no_order_when_po_number_is_null()
    {
        $orderWithoutPo = Order::factory()->create([
            "no_order" => "ORD-NO-PO-001",
            "po_number" => null,
            "klien_id" => $this->klien->id,
            "created_by" => $this->marketingUser->id,
            "status" => "diproses",
        ]);

        $notificationId = OrderNotificationService::notifyNearingFulfillment(
            $orderWithoutPo,
            97.0,
        );

        $notification = DB::table("notifications")
            ->where("id", $notificationId)
            ->first();
        $data = json_decode($notification->data, true);

        $this->assertStringContainsString("ORD-NO-PO-001", $data["message"]);
    }

    /** @test */
    public function notify_nearing_fulfillment_shows_correct_styling_for_below_100_percent()
    {
        $notificationId = OrderNotificationService::notifyNearingFulfillment(
            $this->order,
            95.0,
        );

        $notification = DB::table("notifications")
            ->where("id", $notificationId)
            ->first();
        $data = json_decode($notification->data, true);

        $this->assertStringContainsString("mendekati target", $data["title"]);
        $this->assertEquals("bg-yellow-100", $data["icon_bg"]);
        $this->assertEquals("text-yellow-600", $data["icon_color"]);
    }

    /** @test */
    public function notify_nearing_fulfillment_shows_correct_styling_for_exactly_100_percent()
    {
        $notificationId = OrderNotificationService::notifyNearingFulfillment(
            $this->order,
            100.0,
        );

        $notification = DB::table("notifications")
            ->where("id", $notificationId)
            ->first();
        $data = json_decode($notification->data, true);

        $this->assertStringContainsString("mencapai target", $data["title"]);
        $this->assertEquals("bg-green-100", $data["icon_bg"]);
        $this->assertEquals("text-green-600", $data["icon_color"]);
    }

    /** @test */
    public function notify_nearing_fulfillment_shows_correct_styling_for_above_100_percent()
    {
        $notificationId = OrderNotificationService::notifyNearingFulfillment(
            $this->order,
            103.5,
        );

        $notification = DB::table("notifications")
            ->where("id", $notificationId)
            ->first();
        $data = json_decode($notification->data, true);

        $this->assertStringContainsString("melebihi target", $data["title"]);
        $this->assertEquals("bg-red-100", $data["icon_bg"]);
        $this->assertEquals("text-red-600", $data["icon_color"]);
    }

    /** @test */
    public function notify_direktur_consultation_sends_notification_to_all_active_direktur()
    {
        // Create another active direktur
        $direktur2 = User::factory()->create([
            "nama" => "Direktur 2",
            "role" => "direktur",
            "status" => "aktif",
        ]);

        // Create an inactive direktur (should not receive notification)
        User::factory()->create([
            "nama" => "Inactive Direktur",
            "role" => "direktur",
            "status" => "tidak_aktif",
        ]);

        $note = "Mohon konfirmasi apakah order ini bisa ditutup";
        $count = OrderNotificationService::notifyDirekturConsultation(
            $this->order,
            $this->marketingUser,
            $note,
        );

        $this->assertEquals(2, $count); // Should only be sent to 2 active direkturs

        // Verify both active direkturs received notification
        $this->assertDatabaseHas("notifications", [
            "type" => OrderNotificationService::TYPE_DIREKTUR_CONSULTATION,
            "notifiable_id" => $this->direkturUser->id,
        ]);

        $this->assertDatabaseHas("notifications", [
            "type" => OrderNotificationService::TYPE_DIREKTUR_CONSULTATION,
            "notifiable_id" => $direktur2->id,
        ]);

        // Verify notification content
        $notification = DB::table("notifications")
            ->where(
                "type",
                OrderNotificationService::TYPE_DIREKTUR_CONSULTATION,
            )
            ->where("notifiable_id", $this->direkturUser->id)
            ->first();

        $data = json_decode($notification->data, true);

        $this->assertStringContainsString(
            $this->order->po_number,
            $data["title"],
        );
        $this->assertStringContainsString(
            $this->marketingUser->nama,
            $data["message"],
        );
        $this->assertStringContainsString($note, $data["message"]);
        $this->assertEquals($this->marketingUser->id, $data["requested_by_id"]);
        $this->assertEquals(
            $this->marketingUser->nama,
            $data["requested_by_name"],
        );
        $this->assertEquals($note, $data["note"]);
    }

    /** @test */
    public function notify_direktur_consultation_works_without_note()
    {
        $count = OrderNotificationService::notifyDirekturConsultation(
            $this->order,
            $this->marketingUser,
            null,
        );

        $this->assertEquals(1, $count);

        $notification = DB::table("notifications")
            ->where(
                "type",
                OrderNotificationService::TYPE_DIREKTUR_CONSULTATION,
            )
            ->first();

        $data = json_decode($notification->data, true);

        $this->assertNull($data["note"]);
        $this->assertStringNotContainsString("Catatan:", $data["message"]);
    }

    /** @test */
    public function notify_direktur_consultation_returns_zero_when_no_active_direktur()
    {
        // Deactivate the existing direktur
        $this->direkturUser->update(["status" => "tidak_aktif"]);

        $count = OrderNotificationService::notifyDirekturConsultation(
            $this->order,
            $this->marketingUser,
            "Test note",
        );

        $this->assertEquals(0, $count);
        $this->assertDatabaseMissing("notifications", [
            "type" => OrderNotificationService::TYPE_DIREKTUR_CONSULTATION,
        ]);
    }

    /** @test */
    public function is_within_fulfillment_threshold_returns_true_for_95_percent()
    {
        // Create order details to simulate 95% fulfillment
        $this->order->update(["total_qty" => 100]);

        OrderDetail::factory()->create([
            "order_id" => $this->order->id,
            "qty" => 100,
            "qty_shipped" => 95,
            "total_shipped_quantity" => 95,
            "remaining_quantity" => 5,
        ]);

        $this->order->refresh();
        $this->order->load("orderDetails");

        $this->assertTrue(
            OrderNotificationService::isWithinFulfillmentThreshold(
                $this->order,
            ),
        );
    }

    /** @test */
    public function is_within_fulfillment_threshold_returns_true_for_105_percent()
    {
        $this->order->update(["total_qty" => 100]);

        OrderDetail::factory()->create([
            "order_id" => $this->order->id,
            "qty" => 100,
            "qty_shipped" => 105,
            "total_shipped_quantity" => 105,
            "remaining_quantity" => 0,
        ]);

        $this->order->refresh();
        $this->order->load("orderDetails");

        $this->assertTrue(
            OrderNotificationService::isWithinFulfillmentThreshold(
                $this->order,
            ),
        );
    }

    /** @test */
    public function is_within_fulfillment_threshold_returns_false_for_below_95_percent()
    {
        $this->order->update(["total_qty" => 100]);

        OrderDetail::factory()->create([
            "order_id" => $this->order->id,
            "qty" => 100,
            "qty_shipped" => 90,
            "total_shipped_quantity" => 90,
            "remaining_quantity" => 10,
        ]);

        $this->order->refresh();
        $this->order->load("orderDetails");

        $this->assertFalse(
            OrderNotificationService::isWithinFulfillmentThreshold(
                $this->order,
            ),
        );
    }

    /** @test */
    public function is_within_fulfillment_threshold_returns_false_for_above_105_percent()
    {
        $this->order->update(["total_qty" => 100]);

        OrderDetail::factory()->create([
            "order_id" => $this->order->id,
            "qty" => 100,
            "qty_shipped" => 110,
            "total_shipped_quantity" => 110,
            "remaining_quantity" => 0,
        ]);

        $this->order->refresh();
        $this->order->load("orderDetails");

        $this->assertFalse(
            OrderNotificationService::isWithinFulfillmentThreshold(
                $this->order,
            ),
        );
    }

    /** @test */
    public function notify_completed_sends_notification_to_order_creator()
    {
        $completedBy = User::factory()->create([
            "nama" => "Another Marketing",
            "role" => "marketing",
        ]);

        $notificationId = OrderNotificationService::notifyCompleted(
            $this->order,
            $completedBy,
        );

        $this->assertNotNull($notificationId);
        $this->assertDatabaseHas("notifications", [
            "id" => $notificationId,
            "type" => OrderNotificationService::TYPE_COMPLETED,
            "notifiable_id" => $this->marketingUser->id,
        ]);

        $notification = DB::table("notifications")
            ->where("id", $notificationId)
            ->first();
        $data = json_decode($notification->data, true);

        $this->assertEquals("Order Selesai", $data["title"]);
        $this->assertStringContainsString(
            $this->order->po_number,
            $data["message"],
        );
        $this->assertStringContainsString($completedBy->nama, $data["message"]);
        $this->assertEquals("bg-green-100", $data["icon_bg"]);
        $this->assertEquals("text-green-600", $data["icon_color"]);
    }

    /** @test */
    public function notify_completed_does_not_notify_when_creator_completes_own_order()
    {
        $notificationId = OrderNotificationService::notifyCompleted(
            $this->order,
            $this->marketingUser, // Same as creator
        );

        $this->assertNull($notificationId);
        $this->assertDatabaseMissing("notifications", [
            "type" => OrderNotificationService::TYPE_COMPLETED,
        ]);
    }

    /** @test */
    public function notify_completed_returns_null_when_order_has_no_creator()
    {
        // Create a mock Order that returns null for creator relationship
        $orderMock = $this->createPartialMock(Order::class, ["__get"]);
        $orderMock->id = 999;
        $orderMock->no_order = "ORD-MOCK-002";
        $orderMock->po_number = "PO-MOCK-002";

        $orderMock->method("__get")->willReturnCallback(function ($property) {
            if ($property === "creator") {
                return null;
            }
            return null;
        });

        $notificationId = OrderNotificationService::notifyCompleted($orderMock);

        $this->assertNull($notificationId);
    }

    /** @test */
    public function notify_cancelled_sends_notification_to_order_creator()
    {
        $cancelledBy = User::factory()->create([
            "nama" => "Direktur Cancel",
            "role" => "direktur",
        ]);

        $reason = "Klien membatalkan pesanan";

        $notificationId = OrderNotificationService::notifyCancelled(
            $this->order,
            $cancelledBy,
            $reason,
        );

        $this->assertNotNull($notificationId);
        $this->assertDatabaseHas("notifications", [
            "id" => $notificationId,
            "type" => OrderNotificationService::TYPE_CANCELLED,
            "notifiable_id" => $this->marketingUser->id,
        ]);

        $notification = DB::table("notifications")
            ->where("id", $notificationId)
            ->first();
        $data = json_decode($notification->data, true);

        $this->assertEquals("Order Dibatalkan", $data["title"]);
        $this->assertStringContainsString(
            $this->order->po_number,
            $data["message"],
        );
        $this->assertStringContainsString($cancelledBy->nama, $data["message"]);
        $this->assertStringContainsString($reason, $data["message"]);
        $this->assertEquals($reason, $data["reason"]);
        $this->assertEquals("bg-red-100", $data["icon_bg"]);
        $this->assertEquals("text-red-600", $data["icon_color"]);
    }

    /** @test */
    public function notify_cancelled_works_without_reason()
    {
        $cancelledBy = User::factory()->create([
            "nama" => "Direktur Cancel",
            "role" => "direktur",
        ]);

        $notificationId = OrderNotificationService::notifyCancelled(
            $this->order,
            $cancelledBy,
            null,
        );

        $notification = DB::table("notifications")
            ->where("id", $notificationId)
            ->first();
        $data = json_decode($notification->data, true);

        $this->assertNull($data["reason"]);
        $this->assertStringNotContainsString("Alasan:", $data["message"]);
    }

    /** @test */
    public function notify_cancelled_does_not_notify_when_creator_cancels_own_order()
    {
        $notificationId = OrderNotificationService::notifyCancelled(
            $this->order,
            $this->marketingUser, // Same as creator
            "Self cancel",
        );

        $this->assertNull($notificationId);
        $this->assertDatabaseMissing("notifications", [
            "type" => OrderNotificationService::TYPE_CANCELLED,
        ]);
    }

    /** @test */
    public function notify_cancelled_returns_null_when_order_has_no_creator()
    {
        // Create a mock Order that returns null for creator relationship
        $orderMock = $this->createPartialMock(Order::class, ["__get"]);
        $orderMock->id = 999;
        $orderMock->no_order = "ORD-MOCK-003";
        $orderMock->po_number = "PO-MOCK-003";

        $orderMock->method("__get")->willReturnCallback(function ($property) {
            if ($property === "creator") {
                return null;
            }
            return null;
        });

        $notificationId = OrderNotificationService::notifyCancelled($orderMock);

        $this->assertNull($notificationId);
    }

    /** @test */
    public function notification_includes_correct_url_format()
    {
        $notificationId = OrderNotificationService::notifyNearingFulfillment(
            $this->order,
            98.0,
        );

        $notification = DB::table("notifications")
            ->where("id", $notificationId)
            ->first();
        $data = json_decode($notification->data, true);

        // URL should be in format /orders/{id}
        $this->assertEquals("/orders/{$this->order->id}", $data["url"]);
    }

    /** @test */
    public function notification_data_includes_all_required_fields_for_nearing_fulfillment()
    {
        $notificationId = OrderNotificationService::notifyNearingFulfillment(
            $this->order,
            97.5,
        );

        $notification = DB::table("notifications")
            ->where("id", $notificationId)
            ->first();
        $data = json_decode($notification->data, true);

        $requiredFields = [
            "title",
            "message",
            "icon",
            "icon_bg",
            "icon_color",
            "url",
            "order_id",
            "no_order",
            "po_number",
            "fulfillment_percentage",
            "shipped_qty",
            "total_qty",
            "remaining_qty",
        ];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey(
                $field,
                $data,
                "Missing required field: {$field}",
            );
        }
    }

    /** @test */
    public function notification_data_includes_all_required_fields_for_direktur_consultation()
    {
        OrderNotificationService::notifyDirekturConsultation(
            $this->order,
            $this->marketingUser,
            "Test note",
        );

        $notification = DB::table("notifications")
            ->where(
                "type",
                OrderNotificationService::TYPE_DIREKTUR_CONSULTATION,
            )
            ->first();

        $data = json_decode($notification->data, true);

        $requiredFields = [
            "title",
            "message",
            "icon",
            "icon_bg",
            "icon_color",
            "url",
            "order_id",
            "no_order",
            "po_number",
            "fulfillment_percentage",
            "requested_by_id",
            "requested_by_name",
            "note",
        ];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey(
                $field,
                $data,
                "Missing required field: {$field}",
            );
        }
    }
}
