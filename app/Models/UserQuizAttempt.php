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
        'generated_questions',
        'ai_answers',
        'essay_answers',
        'grading_status',
        'admin_notes',
        'keystroke_data',
        'keystroke_anomaly_score',
        'keystroke_flag',
        'integrity_risk_score',
        'submitted_code',
        'coding_language',
        'actual_output',
        'judge0_status_id',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'is_passed'                => 'boolean',
        'is_auto_submitted'        => 'boolean',
        'generated_questions'      => 'array',
        'ai_answers'               => 'array',
        'essay_answers'            => 'array',
        'keystroke_data'           => 'array',
        'keystroke_anomaly_score'  => 'float',
        'integrity_risk_score'     => 'float',
        'judge0_status_id'         => 'integer',
        'started_at'               => 'datetime',
        'completed_at'             => 'datetime',
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

    /**
     * Hitung ulang skor risiko integritas (0-100) dari seluruh sinyal yang tercatat:
     * pelanggaran perilaku, anomali keystroke, copy-paste, dan auto-submit.
     * Dipanggil setelah submit kuis, setelah analisis keystroke, dan setelah auto-submit.
     */
    public function recomputeIntegrityRisk(): void
    {
        $content       = $this->relationLoaded('content') ? $this->content : $this->content()->first();
        $maxViolations = (int) ($content->max_violations ?? 3);
        if ($maxViolations < 1) {
            $maxViolations = 3;
        }

        // Komponen perilaku: rasio pelanggaran terhadap batas maksimal → 0-100
        $behavior = min(100.0, ((int) $this->violation_count / $maxViolations) * 100);

        // Komponen keystroke: skor anomali biometrik → 0-100
        $keystroke = (float) ($this->keystroke_anomaly_score ?? 0);

        // Risiko dasar = sinyal terkuat di antara keduanya
        $risk = max($behavior, $keystroke);

        // Eskalasi jika dua jenis sinyal sama-sama mencurigakan
        if ($behavior >= 40 && $keystroke >= 40) {
            $risk = min(100.0, $risk + 15);
        }

        // Copy-paste terdeteksi = kecurangan eksplisit
        $ks = $this->keystroke_data;
        if (is_array($ks) && ($ks['paste_indicator']['detected'] ?? false)) {
            $risk = max($risk, 90.0);
        }

        // Auto-submit (batas pelanggaran / waktu habis) = minimal risiko tinggi
        if ($this->is_auto_submitted) {
            $risk = max($risk, 70.0);
        }

        $this->integrity_risk_score = round(min(100.0, $risk), 1);
        $this->save();
    }

    /**
     * Kategori risiko untuk ditampilkan: low / medium / high.
     */
    public function riskLevel(): string
    {
        $score = (float) ($this->integrity_risk_score ?? 0);
        if ($score >= 70) return 'high';
        if ($score >= 40) return 'medium';
        return 'low';
    }
}
