<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MetricService
{
    public function getMetrics()
    {
        $activeWindow = 5;
        $lastActivityThreshold = Carbon::now()->subMinutes($activeWindow)->timestamp;

        // Session Metrics
        $totalSessions = DB::table('sessions')->count();
        $activeSessions = DB::table('sessions')
            ->where('last_activity', '>=', $lastActivityThreshold)
            ->count();
        $authenticatedSessions = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', $lastActivityThreshold)
            ->count();
        $guestSessions = $activeSessions - $authenticatedSessions;

        // Order Metrics
        $orderStats = DB::table('orders')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $ordersToday = DB::table('orders')
            ->whereDate('created_at', Carbon::today())
            ->count();

        // Uses SUM(order_details.total_harga) instead of orders.total_amount
        // because total_amount is unreliable (see total_amount_audit.md)
        $pendingValue = DB::table('orders')
            ->leftJoin('order_details', function ($join) {
                $join->on('order_details.order_id', '=', 'orders.id')
                     ->whereNull('order_details.deleted_at');
            })
            ->whereNull('orders.deleted_at')
            ->whereIn('orders.status', ['draft', 'dikonfirmasi', 'diproses', 'sebagian_dikirim'])
            ->sum(DB::raw('COALESCE(order_details.original_qty, order_details.qty) * order_details.harga_jual'));

        // System Metrics
        $failedJobs = 0;
        if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
            $failedJobs = DB::table('failed_jobs')->count();
        }

        $totalUsers = DB::table('users')->count();

        return [
            'sessions' => [
                'total' => $totalSessions,
                'active_5m' => $activeSessions,
                'authenticated_5m' => $authenticatedSessions,
                'guests_5m' => $guestSessions,
            ],
            'orders' => [
                'by_status' => $orderStats,
                'created_today' => $ordersToday,
                'pending_value' => $pendingValue,
            ],
            'system' => [
                'failed_jobs' => $failedJobs,
                'total_users' => $totalUsers,
            ]
        ];
    }
}
