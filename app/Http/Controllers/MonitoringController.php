<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    public function metrics()
    {
        // Define activity window (e.g., 5 minutes)
        $activeWindow = 5; 
        $lastActivityThreshold = Carbon::now()->subMinutes($activeWindow)->timestamp;

        // Count all sessions (based on session lifetime, usually 120m)
        $totalSessions = DB::table('sessions')->count();

        // Count "active" sessions (interaction within last 5 minutes)
        $activeSessions = DB::table('sessions')
            ->where('last_activity', '>=', $lastActivityThreshold)
            ->count();

        // Count authenticated sessions (user_id is not null)
        $authenticatedSessions = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $lastActivityThreshold)
            ->count();

        // Count guests
        $guestSessions = $activeSessions - $authenticatedSessions;

        // Format as Prometheus metrics
        $metrics = [
            '# HELP app_sessions_total Total number of sessions stored (valid within session lifetime)',
            '# TYPE app_sessions_total gauge',
            "app_sessions_total {$totalSessions}",

            "# HELP app_sessions_active_{$activeWindow}m Number of sessions active in the last {$activeWindow} minutes",
            "# TYPE app_sessions_active_{$activeWindow}m gauge",
            "app_sessions_active_{$activeWindow}m {$activeSessions}",

            '# HELP app_sessions_authenticated Number of authenticated users active in the last 5 minutes',
            '# TYPE app_sessions_authenticated gauge',
            "app_sessions_authenticated {$authenticatedSessions}",

            '# HELP app_sessions_guests Number of guest sessions active in the last 5 minutes',
            '# TYPE app_sessions_guests gauge',
            "app_sessions_guests {$guestSessions}",
        ];

        // --- Business Metrics ---

        // 1. Orders by Status
        $orderStats = DB::table('orders')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        foreach ($orderStats as $status => $count) {
             $metrics[] = "app_orders_status_total{status=\"{$status}\"} {$count}";
        }
        // Ensure help text is present (Prometheus allows help text once per metric name)
        array_splice($metrics, count($metrics) - count($orderStats), 0, [
            '# HELP app_orders_status_total Number of orders by status',
            '# TYPE app_orders_status_total gauge'
        ]);

        // 2. Orders Created Today
        $ordersToday = DB::table('orders')
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        $metrics[] = '# HELP app_orders_created_today Total orders created today';
        $metrics[] = '# TYPE app_orders_created_today gauge';
        $metrics[] = "app_orders_created_today {$ordersToday}";

        // 3. Pending Order Value (Draft, Confirmed, Processed)
        // Adjust statuses based on your "Active" definition
        $pendingValue = DB::table('orders')
             ->whereIn('status', ['draft', 'dikonfirmasi', 'diproses', 'sebagian_dikirim'])
             ->sum('total_amount');
        
        $metrics[] = '# HELP app_orders_pending_value_idr Total IDR value of active orders';
        $metrics[] = '# TYPE app_orders_pending_value_idr gauge';
        $metrics[] = "app_orders_pending_value_idr {$pendingValue}";

        // --- System Metrics ---

        // 4. Failed Jobs
        // Check if table exists to avoid errors if queue not used/migrated
        if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
            $failedJobs = DB::table('failed_jobs')->count();
            $metrics[] = '# HELP app_jobs_failed_total Total number of failed jobs';
            $metrics[] = '# TYPE app_jobs_failed_total gauge';
            $metrics[] = "app_jobs_failed_total {$failedJobs}";
        }

        // 5. Total Users
        $totalUsers = DB::table('users')->count();
        $metrics[] = '# HELP app_users_total Total registered users';
        $metrics[] = '# TYPE app_users_total gauge';
        $metrics[] = "app_users_total {$totalUsers}";

        return response(implode("\n", $metrics))
            ->header('Content-Type', 'text/plain');
    }
}
