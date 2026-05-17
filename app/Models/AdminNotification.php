<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AdminNotification extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'title',
        'message',
        'link',
        'related_id',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Catat notifikasi: esai siswa selesai & menunggu dinilai admin.
     */
    public static function essaySubmitted(string $relatedId, string $studentName, string $quizTitle, ?string $link): void
    {
        self::create([
            'type'       => 'essay_submitted',
            'title'      => 'Esai menunggu penilaian',
            'message'    => $studentName . ' menyelesaikan esai "' . $quizTitle . '" dan menunggu dinilai.',
            'link'       => $link,
            'related_id' => $relatedId,
            'is_read'    => false,
        ]);
    }

    /**
     * Catat / perbarui notifikasi pelanggaran integritas kuis.
     * Satu attempt = satu notifikasi yang diperbarui tiap pelanggaran baru.
     */
    public static function integrityViolation(string $relatedId, string $studentName, string $quizTitle, int $violationCount, bool $autoSubmitted, ?string $link): void
    {
        $message = $studentName . ' melakukan ' . $violationCount . ' pelanggaran integritas pada kuis "' . $quizTitle . '".';
        if ($autoSubmitted) {
            $message .= ' Kuis dikirim otomatis.';
        }

        self::updateOrCreate(
            ['type' => 'integrity_violation', 'related_id' => $relatedId],
            [
                'title'   => 'Pelanggaran integritas kuis',
                'message' => $message,
                'link'    => $link,
                'is_read' => false,
            ]
        );
    }
}
