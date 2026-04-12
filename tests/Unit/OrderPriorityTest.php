<?php

namespace Tests\Unit;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class OrderPriorityTest extends TestCase
{
    public function test_priority_is_rendah_before_po_end_date_plus_one_month(): void
    {
        $order = new Order([
            'po_end_date' => Carbon::parse('2026-01-31'),
        ]);

        // One day before the +1 month (no-overflow) boundary (Feb 28)
        $this->assertSame(
            'rendah',
            $order->determinePriority(Carbon::parse('2026-02-27')),
        );
    }

    public function test_priority_is_sedang_at_po_end_date_plus_one_month_inclusive(): void
    {
        $order = new Order([
            'po_end_date' => Carbon::parse('2026-01-31'),
        ]);

        // addMonthsNoOverflow(1) from Jan 31 -> Feb 28
        $this->assertSame(
            'sedang',
            $order->determinePriority(Carbon::parse('2026-02-28')),
        );
    }

    public function test_priority_is_tinggi_at_po_end_date_plus_two_months_inclusive(): void
    {
        $order = new Order([
            'po_end_date' => Carbon::parse('2026-01-31'),
        ]);

        // addMonthsNoOverflow(2) from Jan 31 -> Mar 31
        $this->assertSame(
            'tinggi',
            $order->determinePriority(Carbon::parse('2026-03-31')),
        );
    }
}
