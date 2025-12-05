<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Notifications\PengirimanNotificationService;

class SendPengirimanReviewReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pengiriman:notify-review';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily reminder to marketing about successful deliveries ready for review';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for unreviewed successful pengiriman...');
        
        $count = PengirimanNotificationService::notifyUnreviewedSuccessfulDeliveries();
        
        if ($count > 0) {
            $this->info("✓ Sent pengiriman review reminder to {$count} users");
        } else {
            $this->info('No unreviewed successful pengiriman found or no users to notify');
        }
        
        return Command::SUCCESS;
    }
}
