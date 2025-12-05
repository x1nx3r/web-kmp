<?php

namespace App\Observers;

use App\Models\Pengiriman;
use App\Models\ApprovalPembayaran;
use App\Services\Notifications\PengirimanNotificationService;
use App\Services\Notifications\ApprovalPembayaranNotificationService;

class PengirimanObserver
{
    /**
     * Handle the Pengiriman "created" event.
     */
    public function created(Pengiriman $pengiriman): void
    {
        // Auto-create approval pembayaran if status is 'menunggu_verifikasi'
        if ($pengiriman->status === 'menunggu_verifikasi') {
            $this->createApprovalPembayaran($pengiriman);
        }
    }

    /**
     * Handle the Pengiriman "updated" event.
     */
    public function updated(Pengiriman $pengiriman): void
    {
        // Check if status changed to 'menunggu_verifikasi'
        if ($pengiriman->isDirty('status') && $pengiriman->status === 'menunggu_verifikasi') {
            // Check if approval pembayaran already exists
            if (!$pengiriman->approvalPembayaran) {
                $this->createApprovalPembayaran($pengiriman);
            }
        }

        // Check if status changed to 'berhasil' - notify marketing for review
        if ($pengiriman->isDirty('status') && $pengiriman->status === 'berhasil') {
            PengirimanNotificationService::notifySuccessReadyForReview($pengiriman);
        }
    }

    /**
     * Create approval pembayaran record and notify accounting team
     */
    private function createApprovalPembayaran(Pengiriman $pengiriman): void
    {
        $approval = ApprovalPembayaran::create([
            'pengiriman_id' => $pengiriman->id,
            'status' => 'pending',
        ]);

        // Send notification to accounting team
        if ($approval) {
            ApprovalPembayaranNotificationService::notifyPendingApproval($approval);
        }
    }

    /**
     * Handle the Pengiriman "deleted" event.
     */
    public function deleted(Pengiriman $pengiriman): void
    {
        //
    }

    /**
     * Handle the Pengiriman "restored" event.
     */
    public function restored(Pengiriman $pengiriman): void
    {
        //
    }

    /**
     * Handle the Pengiriman "force deleted" event.
     */
    public function forceDeleted(Pengiriman $pengiriman): void
    {
        //
    }
}
