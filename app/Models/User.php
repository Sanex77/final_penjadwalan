<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Contracts\Auth\MustVerifyEmail; // 👈 Pastikan ini aktif (tidak di-comment)
class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasPushSubscriptions;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nip',
        'nim',
        'no_wa',
        'nama_ormawa',
        'deskripsi',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    protected function noWa(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                // 1. Bersihkan semua karakter aneh (spasi, strip, tanda +) sisakan angka saja
                $cleaned = preg_replace('/[^0-9]/', '', $value);

                // 2. Ubah formatnya ke awalan 62
                if (str_starts_with($cleaned, '0')) {
                    // Kalau depannya 0, buang 0-nya, ganti 62
                    return '62' . substr($cleaned, 1);
                } elseif (str_starts_with($cleaned, '8')) {
                    // Kalau ngetiknya langsung 812... tambahin 62 depannya
                    return '62' . $cleaned;
                }

                // Kalau udah 62 dari awal, biarkan saja
                return $cleaned;
            }
        );
    }
}