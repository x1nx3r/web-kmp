<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Notifications\PengirimanNotificationService;

class SendPengirimanPendingReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pengiriman:notify-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily reminder to manager_purchasing and staff_purchasing about pending pengiriman (status: pending and menunggu_verifikasi)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for pending pengiriman...');
        
        $count = PengirimanNotificationService::notifyPendingDeliveries();
        
        if ($count > 0) {
            $this->info("✓ Sent pengiriman pending reminder to {$count} users");
        } else {
            $this->info('No pending pengiriman found or no users to notify');
        }
        
        return Command::SUCCESS;
    }
}
