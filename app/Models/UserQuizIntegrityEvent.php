<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuizIntegrityEvent extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'user_quiz_attempt_id',
        'user_course_id',
        'content_id',
        'event_type',
        'violation_count',
        'is_auto_submitted',
        'event_at',
    ];

    protected $casts = [
        'is_auto_submitted' => 'boolean',
        'event_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attempt()
    {
        return $this->belongsTo(UserQuizAttempt::class, 'user_quiz_attempt_id');
    }

    public function userCourse()
    {
        return $this->belongsTo(UserCourse::class, 'user_course_id');
    }

    public function content()
    {
        return $this->belongsTo(Content::class);
    }
}
