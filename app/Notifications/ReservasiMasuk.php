<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class ReservasiMasuk extends Notification
{
    public $booking;

    public function __construct($booking) {
        $this->booking = $booking;
    }

    // Pakai channel WebPush
    public function via($notifiable) {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification) {
        return (new WebPushMessage)
            ->title('🚀 Ada Booking Baru!')
            ->body("Dosen: {$this->booking->dosen} mau pakai {$this->booking->lab}")
            ->data(['url' => url('/spv/reservasi')]) // Link buat SPV ngecek
            ->icon('/favicon.ico');
    }
}