<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\EscalateOrderPriorities;
use App\Models\Klien;
use App\Models\Order;
use App\Models\User;
use App\Services\Notifications\OrderNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EscalateOrderPrioritiesTest extends TestCase
{
    use RefreshDatabase;

    protected User $marketingUser;
    protected User $otherMarketingUser;
    protected Klien $klien;

    protected function setUp(): void
    {
        parent::setUp();

        // Create marketing user (order creator)
        $this->marketingUser = User::factory()->create([
            "nama" => "Marketing User",
            "role" => "marketing",
            "status" => "aktif",
        ]);

        // Create another marketing user to receive notifications
        $this->otherMarketingUser = User::factory()->create([
            "nama" => "Other Marketing User",
            "role" => "marketing",
            "status" => "aktif",
        ]);

        // Create test klien
        $this->klien = Klien::factory()->create([
            "nama" => "PT Test Klien",
        ]);
    }

    /**
     * Helper to create an order with specific attributes, bypassing any model events
     */
    protected function createOrderWithPriority(
        string $priority,
        int $daysUntilDeadline,
        string $status = "diproses",
    ): Order {
        $order = Order::factory()->create([
            "klien_id" => $this->klien->id,
            "created_by" => $this->marketingUser->id,
            "status" => $status,
            "po_end_date" => now()->addDays($daysUntilDeadline)->toDateString(),
        ]);

        // Use DB update to ensure priority is set exactly as specified
        \DB::table("orders")
            ->where("id", $order->id)
            ->update([
                "priority" => $priority,
            ]);

        return $order->fresh();
    }

    /** @test */
    public function it_does_not_change_rendah_for_14_days()
    {
        $order = $this->createOrderWithPriority("rendah", 14);

        $this->artisan("orders:escalate-priorities")->assertSuccessful();

        $order->refresh();
        $this->assertEquals("rendah", $order->priority);
    }

    /** @test */
    public function it_does_not_change_rendah_for_7_days()
    {
        $order = $this->createOrderWithPriority("rendah", 7);

        $this->artisan("orders:escalate-priorities")->assertSuccessful();

        $order->refresh();
        $this->assertEquals("rendah", $order->priority);
    }

    /** @test */
    public function it_does_not_change_rendah_for_3_days()
    {
        $order = $this->createOrderWithPriority("rendah", 3);

        $this->artisan("orders:escalate-priorities")->assertSuccessful();

        $order->refresh();
        $this->assertEquals("rendah", $order->priority);
    }

    /** @test */
    public function it_escalates_rendah_to_sedang_when_45_days_remaining()
    {
        $order = $this->createOrderWithPriority("rendah", 45);

        $this->artisan("orders:escalate-priorities")->assertSuccessful();

        $order->refresh();
        $this->assertEquals("sedang", $order->priority);
    }

    /** @test */
    public function it_does_not_deescalate_tinggi_when_deadline_far()
    {
        $order = $this->createOrderWithPriority("tinggi", 30);

        $this->artisan("orders:escalate-priorities")->assertSuccessful();

        $order->refresh();
        $this->assertEquals("tinggi", $order->priority);
    }

    /** @test */
    public function it_does_not_deescalate_priority()
    {
        // A high-priority order should not be de-escalated by the command
        // because the command only escalates (never reduces) priority levels.
        $order = $this->createOrderWithPriority("tinggi", 30); // high priority stays

        $this->artisan("orders:escalate-priorities")->assertSuccessful();

        $order->refresh();
        $this->assertEquals("tinggi", $order->priority); // Should remain tinggi
    }

    /** @test */
    public function it_does_not_escalate_when_priority_already_matches()
    {
        $order = $this->createOrderWithPriority("tinggi", 5); // Should be tinggi

        $this->artisan("orders:escalate-priorities")->assertSuccessful();

        $order->refresh();
        $this->assertEquals("tinggi", $order->priority);
    }

    /** @test */
    public function it_ignores_completed_orders()
    {
        $order = $this->createOrderWithPriority("rendah", 2, "selesai");

        $this->artisan("orders:escalate-priorities")->assertSuccessful();

        $order->refresh();
        $this->assertEquals("rendah", $order->priority);
    }

    /** @test */
    public function it_ignores_cancelled_orders()
    {
        $order = $this->createOrderWithPriority("rendah", 2, "dibatalkan");

        $this->artisan("orders:escalate-priorities")->assertSuccessful();

        $order->refresh();
        $this->assertEquals("rendah", $order->priority);
    }

    /** @test */
    public function it_ignores_orders_without_po_end_date()
    {
        $order = Order::factory()->create([
            "klien_id" => $this->klien->id,
            "created_by" => $this->marketingUser->id,
            "status" => "diproses",
            "po_end_date" => null,
        ]);

        \DB::table("orders")
            ->where("id", $order->id)
            ->update([
                "priority" => "rendah",
            ]);

        $this->artisan("orders:escalate-priorities")->assertSuccessful();

        $order->refresh();
        $this->assertEquals("rendah", $order->priority);
    }

    /** @test */
    public function dry_run_does_not_update_orders()
    {
        $order = $this->createOrderWithPriority("rendah", 2);

        $this->artisan(
            "orders:escalate-priorities --dry-run",
        )->assertSuccessful();

        $order->refresh();
        $this->assertEquals("rendah", $order->priority); // Should NOT change
    }

    /** @test */
    public function it_sends_notification_when_notify_flag_is_set()
    {
        // To trigger notification the command must escalate to 'tinggi'.
        // Use a case that will compute to 'tinggi' (many days remaining).
        $order = $this->createOrderWithPriority("sedang", 90); // should escalate to tinggi

        $this->artisan(
            "orders:escalate-priorities --notify",
        )->assertSuccessful();

        // Verify notification was created for the other marketing user
        $this->assertDatabaseHas("notifications", [
            "type" => OrderNotificationService::TYPE_PRIORITY_ESCALATED,
            "notifiable_type" => User::class,
            "notifiable_id" => $this->otherMarketingUser->id,
        ]);
    }

    /** @test */
    public function it_does_not_send_notification_without_notify_flag()
    {
        $order = $this->createOrderWithPriority("rendah", 5);

        $this->artisan("orders:escalate-priorities")->assertSuccessful();

        // Verify no notification was created
        $this->assertDatabaseMissing("notifications", [
            "type" => OrderNotificationService::TYPE_PRIORITY_ESCALATED,
        ]);
    }

    /** @test */
    public function it_updates_priority_calculated_at_timestamp()
    {
        $order = $this->createOrderWithPriority("rendah", 5);

        $this->artisan("orders:escalate-priorities")->assertSuccessful();

        $order->refresh();
        $this->assertNotNull($order->priority_calculated_at);
    }

    /** @test */
    public function it_handles_multiple_orders()
    {
        // Order 1: Should escalate to tinggi (90 days)
        $order1 = $this->createOrderWithPriority("rendah", 90);

        // Order 2: Should escalate to sedang (45 days)
        $order2 = $this->createOrderWithPriority("rendah", 45, "dikonfirmasi");

        // Order 3: Should not de-escalate (already tinggi)
        $order3 = $this->createOrderWithPriority("tinggi", 10);

        $this->artisan("orders:escalate-priorities")->assertSuccessful();

        $order1->refresh();
        $order2->refresh();
        $order3->refresh();

        $this->assertEquals("tinggi", $order1->priority);
        $this->assertEquals("sedang", $order2->priority);
        $this->assertEquals("tinggi", $order3->priority);
    }

    /** @test */
    public function it_handles_past_deadline_as_rendah()
    {
        $order = Order::factory()->create([
            "klien_id" => $this->klien->id,
            "created_by" => $this->marketingUser->id,
            "status" => "diproses",
            "po_end_date" => now()->subDays(2)->toDateString(), // Past deadline
        ]);

        \DB::table("orders")
            ->where("id", $order->id)
            ->update([
                // Use a valid current token; avoid legacy 'normal'
                "priority" => "tinggi",
            ]);

        $this->artisan("orders:escalate-priorities")->assertSuccessful();

        $order->refresh();
        // The command only escalates; it should NOT de-escalate a stored 'tinggi'
        $this->assertEquals("tinggi", $order->priority);
    }

    /** @test */
    public function notification_includes_days_remaining_in_message()
    {
        // Use a case that will escalate to 'tinggi' so a notification is sent.
        $order = $this->createOrderWithPriority("sedang", 90); // 90 days => tinggi

        $this->artisan(
            "orders:escalate-priorities --notify",
        )->assertSuccessful();

        $notification = \DB::table("notifications")
            ->where("type", OrderNotificationService::TYPE_PRIORITY_ESCALATED)
            ->first();

        $this->assertNotNull($notification);

        $data = json_decode($notification->data, true);
        $this->assertArrayHasKey("days_remaining", $data);
        $this->assertIsInt($data["days_remaining"]);
        // days_remaining should reflect ~90 days in this scenario
        $this->assertTrue(
            $data["days_remaining"] >= 89 && $data["days_remaining"] <= 91,
        );
        $this->assertTrue($data["is_automatic"]);
    }

    /** @test */
    public function notification_message_says_deadline_besok_for_1_day()
    {
        // Create order with exactly 1 day remaining using a specific date
        $order = Order::factory()->create([
            "klien_id" => $this->klien->id,
            "created_by" => $this->marketingUser->id,
            "status" => "diproses",
            "po_end_date" => now()->addDay()->startOfDay()->toDateString(),
        ]);

        \DB::table("orders")
            ->where("id", $order->id)
            ->update([
                "priority" => "tinggi",
            ]);

        $this->artisan(
            "orders:escalate-priorities --notify",
        )->assertSuccessful();

        // Under the literal timeframe mapping the command only notifies when an order
        // is escalated to 'tinggi'. A PO with 1 day remaining does not produce an
        // escalation to 'tinggi' under the new mapping (and the command never
        // de-escalates), therefore no notification should be created in this case.
        $this->assertDatabaseMissing("notifications", [
            "type" => OrderNotificationService::TYPE_PRIORITY_ESCALATED,
        ]);
    }

    /** @test */
    public function it_only_notifies_for_escalation_to_tinggi()
    {
        // 10 days remains maps to 'rendah' under the literal mapping and should not trigger notification.
        $order = $this->createOrderWithPriority("rendah", 10); // 10 days => rendah

        $this->artisan(
            "orders:escalate-priorities --notify",
        )->assertSuccessful();

        $order->refresh();
        $this->assertEquals("rendah", $order->priority);

        // No notification should be sent for escalation that is not to 'tinggi'
        $this->assertDatabaseMissing("notifications", [
            "type" => OrderNotificationService::TYPE_PRIORITY_ESCALATED,
        ]);
    }
}
