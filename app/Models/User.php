<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'avatar',
        'password',
        'email_verified_at',
        'password_changed_at',
        'is_otp',
        'otp_code',
        'otp_expires_at',
        'permission',
        'status',
        'is_email_notification_enabled',
        'is_whatsapp_notification_enabled',
        'active_device',
        'is_suspended',
        'suspended_until',
        'suspension_reason',
        'suspended_by',
        'suspended_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'is_otp' => 'boolean',
        'is_email_notification_enabled' => 'boolean',
        'is_whatsapp_notification_enabled' => 'boolean',
        'password' => 'hashed',
        'is_suspended' => 'boolean',
        'suspended_until' => 'datetime',
        'suspended_at' => 'datetime',
    ];

    /**
     * Default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'avatar' => 'default-avatar.png',
        'permission' => 'user',
        'status' => 'aktif',
        'is_email_notification_enabled' => true,
        'is_whatsapp_notification_enabled' => true,
    ];

    // ============================================
    // RELASI UNTUK KURSUS
    // ============================================

    /**
     * Relasi ke progress content user
     */
    public function enrolledCourses()
    {
        return $this->hasMany(UserCourse::class);
    }

    /**
     * Relasi ke progress content user
     */
    public function contentProgress()
    {
        return $this->hasMany(UserContentProgress::class);
    }

    /**
     * Relasi ke quiz attempts user
     */
    public function quizAttempts()
    {
        return $this->hasMany(UserQuizAttempt::class);
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    public function isSuspended(): bool
    {
        if (!$this->is_suspended) {
            return false;
        }
        if ($this->suspended_until === null) {
            return true;
        }
        return now()->lt($this->suspended_until);
    }

    public function suspensionRemainingMinutes(): int
    {
        if (!$this->isSuspended() || $this->suspended_until === null) {
            return 0;
        }
        return (int) now()->diffInMinutes($this->suspended_until, false);
    }

    public function suspensionRemainingLabel(): string
    {
        $minutes = $this->suspensionRemainingMinutes();
        if ($minutes <= 0) {
            return '0 menit';
        }
        if ($minutes < 60) {
            return $minutes . ' menit';
        }
        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;
        if ($hours < 24) {
            return $remaining > 0 ? "{$hours} jam {$remaining} menit" : "{$hours} jam";
        }
        $days = intdiv($hours, 24);
        $remainingHours = $hours % 24;
        return $remainingHours > 0 ? "{$days} hari {$remainingHours} jam" : "{$days} hari";
    }

    /**
     * Cek apakah user sudah enroll kursus tertentu
     *
     * @param string $kursusId
     * @return bool
     */
    public function hasEnrolled($kursusId)
    {
        return $this->enrolledCourses()->where('kursus_id', $kursusId)->exists();
    }

    /**
     * Mengambil data pendaftaran kursus tertentu untuk pengguna ini.
     *
     * @param  int  $kursusId
     * @return \App\Models\UserCourse|null
     */
    public function getEnrolledCourse($kursusId)
    {
        return $this->enrolledCourses()->where('kursus_id', $kursusId)->first();
    }
}
