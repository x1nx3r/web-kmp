<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Notifications\ForecastNotificationService;
use Illuminate\Support\Facades\Log;

class SendForecastPendingReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forecast:notify-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification reminder untuk forecast pending ke Manager & Staff Purchasing (Senin 07:00 WIB)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔔 Mengirim notifikasi forecast pending reminder...');

        try {
            $count = ForecastNotificationService::notifyPendingForecastReminder();

            if ($count > 0) {
                $this->info("✅ Berhasil mengirim {$count} notifikasi ke Manager & Staff Purchasing");
                Log::info("Forecast pending reminder sent", [
                    'notifications_sent' => $count,
                    'timestamp' => now()->toDateTimeString()
                ]);
            } else {
                $this->info("ℹ️ Tidak ada forecast pending untuk dinotifikasi");
                Log::info("No pending forecast to notify");
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Gagal mengirim notifikasi: {$e->getMessage()}");
            Log::error("Failed to send forecast pending reminder", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        }
    }
}
