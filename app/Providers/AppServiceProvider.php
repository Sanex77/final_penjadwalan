<?php

namespace App\Providers;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       
        // Tambahkan ini: Jika aplikasi dibuka lewat Ngrok/HTTPS, paksa link ke https
        if (config('app.env') !== 'local' || isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            URL::forceScheme('https');
        
    VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
        return (new MailMessage)
            ->subject('Aktivasi Akun Lab Komputer 🚀') // Judul Email
            ->greeting('Halo, ' . $notifiable->name . '!') // Sapaan
            ->line('Terima kasih sudah mendaftar di Sistem Lab Komputer.')
            ->line('Silakan klik tombol di bawah ini untuk memverifikasi email kamu.')
            ->action('Verifikasi Akun Sekarang', $url) // Teks Tombol
            ->line('Jika kamu tidak merasa mendaftar, abaikan saja email ini.')
            ->salutation('Salam, Admin Lab'); // Penutup
    });
}
    }}