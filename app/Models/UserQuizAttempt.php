<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuizAttempt extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'content_id',
        'user_course_id',
        'score',
        'total_questions',
        'correct_answers',
        'is_passed',
        'violation_count',
        'is_auto_submitted',
        'auto_submit_reason',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'is_passed' => 'boolean',
        'is_auto_submitted' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function userCourse()
    {
        return $this->belongsTo(UserCourse::class);
    }

    public function answers()
    {
        return $this->hasMany(UserQuizAnswer::class, 'quiz_attempt_id');
    }

    public function integrityEvents()
    {
        return $this->hasMany(UserQuizIntegrityEvent::class, 'user_quiz_attempt_id');
    }
}

