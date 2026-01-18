<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MetricService;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class PushOtelMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:push-otel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push application metrics to OpenTelemetry endpoint (e.g. Grafana Cloud)';

    protected $metricService;

    public function __construct(MetricService $metricService)
    {
        parent::__construct();
        $this->metricService = $metricService;
    }

    public function handle()
    {
        $endpoint = config('services.otel.endpoint');
        $username = config('services.otel.username');
        $token = config('services.otel.token'); // API Key or Token

        if (!$endpoint || !$username || !$token) {
            $this->error('OTEL Configuration missing. Please set OTEL_ENDPOINT, OTEL_USERNAME, and OTEL_TOKEN in .env');
            return Command::FAILURE;
        }

        $metricsData = $this->metricService->getMetrics();
        $timestamp = (string)(Carbon::now()->getPreciseTimestamp(3) * 1000000); // Nano seconds string

        $otlpMetrics = [];

        // Helper to add gauge metric
        $addGauge = function ($name, $value, $asType = 'asInt', $attributes = []) use (&$otlpMetrics, $timestamp) {
            $otlpMetrics[] = [
                'name' => $name,
                'unit' => '1',
                'gauge' => [
                    'dataPoints' => [
                        [
                            $asType => $value,
                            'timeUnixNano' => $timestamp,
                            'attributes' => $attributes
                        ]
                    ]
                ]
            ];
        };

        // Map Sessions
        $addGauge('app_sessions_total', $metricsData['sessions']['total']);
        $addGauge('app_sessions_active_5m', $metricsData['sessions']['active_5m']);
        $addGauge('app_sessions_authenticated', $metricsData['sessions']['authenticated_5m']);
        $addGauge('app_sessions_guests', $metricsData['sessions']['guests_5m']);

        // Map Orders by Status
        foreach ($metricsData['orders']['by_status'] as $status => $count) {
            $addGauge('app_orders_status_total', $count, 'asInt', [
                ['key' => 'status', 'value' => ['stringValue' => $status]]
            ]);
        }

        $addGauge('app_orders_created_today', $metricsData['orders']['created_today']);
        $addGauge('app_orders_pending_value_idr', $metricsData['orders']['pending_value'], 'asDouble');

        // Map System
        $addGauge('app_jobs_failed_total', $metricsData['system']['failed_jobs']);
        $addGauge('app_users_total', $metricsData['system']['total_users']);

        // Construct Payload
        $payload = [
            'resourceMetrics' => [
                [
                    'resource' => [
                        'attributes' => [
                            ['key' => 'service.name', 'value' => ['stringValue' => config('app.name', 'laravel-app')]],
                            ['key' => 'service.namespace', 'value' => ['stringValue' => config('app.env', 'production')]],
                        ]
                    ],
                    'scopeMetrics' => [
                        [
                            'scope' => ['name' => 'laravel-metric-pusher'],
                            'metrics' => $otlpMetrics
                        ]
                    ]
                ]
            ]
        ];

        // Send Request
        // Grafana uses Basic Auth: UserID : API_Token
        $response = Http::withBasicAuth($username, $token)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($endpoint, $payload);

        if ($response->successful()) {
            $this->info('Metrics pushed successfully.');
            return Command::SUCCESS;
        } else {
            $this->error('Failed to push metrics: ' . $response->body());
            return Command::FAILURE;
        }
    }
}
