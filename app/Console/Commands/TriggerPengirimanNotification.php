<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pengiriman;
use App\Services\Notifications\PengirimanNotificationService;

class TriggerPengirimanNotification extends Command
{
    protected $signature = 'pengiriman:trigger-notification {id}';
    protected $description = 'Manually trigger notification for a pengiriman';

    public function handle()
    {
        $id = $this->argument('id');
        $pengiriman = Pengiriman::with(['order.creator', 'order.klien'])->find($id);
        
        if (!$pengiriman) {
            $this->error("Pengiriman with ID {$id} not found");
            return Command::FAILURE;
        }

        $this->info("Pengiriman: {$pengiriman->no_pengiriman}");
        $this->info("Status: {$pengiriman->status}");
        $this->info("Rating: " . ($pengiriman->rating ?? 'null'));
        
        if ($pengiriman->order) {
            $this->info("Order ID: {$pengiriman->order->id}");
            $this->info("Order Creator: " . ($pengiriman->order->creator ? $pengiriman->order->creator->nama : 'NOT FOUND'));
        } else {
            $this->error("Order not found!");
            return Command::FAILURE;
        }

        $this->info("Triggering notification...");
        $result = PengirimanNotificationService::notifySuccessReadyForReview($pengiriman);
        
        if ($result) {
            $this->info("✓ Notification sent! ID: {$result}");
        } else {
            $this->error("✗ Notification failed to send");
        }
        
        return Command::SUCCESS;
    }
}
