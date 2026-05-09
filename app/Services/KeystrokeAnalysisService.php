<?php

namespace App\Services;

use App\Models\KeystrokeBaseline;
use App\Models\UserQuizAttempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KeystrokeAnalysisService
{
    // Minimum keystrokes in a session before analysis is meaningful
    const MIN_KEYSTROKES = 30;

    // How many sessions needed before we trust the baseline enough to flag
    const MIN_SESSIONS_FOR_BASELINE = 2;

    // Z-score thresholds → anomaly score bands
    const THRESHOLD_SUSPECT = 70;  // score >= 70 → suspect
    const THRESHOLD_CAUTION = 40;  // score >= 40 → caution

    // ── Public API ─────────────────────────────────────────────────────────────

    /**
     * Called right after an essay attempt is saved.
     * Stores keystroke stats, updates the user's baseline, then scores the session.
     */
    public function processSession(UserQuizAttempt $attempt, array $keystrokeData): void
    {
        if (empty($keystrokeData)) {
            return;
        }

        // ── Paste detection ─────────────────────────────────────────────
        // Jika siswa paste langsung (atau jawaban panjang tapi total ketukan
        // sangat sedikit), tandai sesi sebagai SUSPECT walaupun belum ada baseline.
        $pasteIndicator = $this->detectPaste($keystrokeData);
        $keystrokeData['paste_indicator'] = $pasteIndicator;

        // 1. Persist raw stats on the attempt
        $attempt->keystroke_data = $keystrokeData;
        $attempt->save();

        $userId     = $attempt->user_id;
        $deviceType = $keystrokeData['device_type'] ?? 'desktop';

        // 2. Paste = langsung suspect, skip baseline analysis
        if ($pasteIndicator['detected']) {
            $attempt->keystroke_anomaly_score = 100.0;
            $attempt->keystroke_flag          = 'suspect';
            $attempt->save();
            return; // jangan masukkan ke baseline — perilaku abnormal
        }

        // 3. Skip biometric analysis kalau ketukan terlalu sedikit
        $totalKeystrokes = (int) ($keystrokeData['total_keystrokes'] ?? 0);
        if ($totalKeystrokes < self::MIN_KEYSTROKES) {
            return;
        }

        // 4. Pull or build baseline
        $baseline = KeystrokeBaseline::where('user_id', $userId)
            ->where('device_type', $deviceType)
            ->first();

        // 5. Compute anomaly score if we have a mature baseline
        if ($baseline && $baseline->sample_sessions >= self::MIN_SESSIONS_FOR_BASELINE) {
            $score = $this->computeAnomalyScore($keystrokeData, $baseline);
            $flag  = $this->resolveFlag($score);

            $attempt->keystroke_anomaly_score = $score;
            $attempt->keystroke_flag          = $flag;
            $attempt->save();
        }

        // 6. Incorporate this session into the rolling baseline
        $this->updateBaseline($userId, $deviceType, $keystrokeData, $baseline);
    }

    /**
     * Detects if the session likely contains pasted content.
     *
     * Returns ['detected' => bool, 'reason' => string, 'ratio' => float]
     */
    private function detectPaste(array $data): array
    {
        $pasteCount   = (int)   ($data['paste_count']        ?? 0);
        $pastedChars  = (int)   ($data['pasted_chars']       ?? 0);
        $totalChars   = (int)   ($data['total_answer_chars'] ?? 0);
        $totalKeys    = (int)   ($data['total_keystrokes']   ?? 0);

        // 1. Paste event tertangkap secara eksplisit
        if ($pasteCount > 0) {
            return [
                'detected' => true,
                'reason'   => 'paste_event',
                'paste_count'  => $pasteCount,
                'pasted_chars' => $pastedChars,
            ];
        }

        // 2. Jawaban panjang tapi ketukan terlalu sedikit (mis. drop file, drag-drop teks,
        //    atau JS injection bypass paste event) — rasio < 0.3 = mencurigakan
        if ($totalChars >= 50 && $totalKeys < ($totalChars * 0.3)) {
            $ratio = $totalChars > 0 ? round($totalKeys / $totalChars, 2) : 0;
            return [
                'detected' => true,
                'reason'   => 'low_keystroke_ratio',
                'ratio'    => $ratio,
                'total_answer_chars' => $totalChars,
                'total_keystrokes'   => $totalKeys,
            ];
        }

        return ['detected' => false];
    }

    // ── Anomaly score ──────────────────────────────────────────────────────────

    /**
     * Returns 0–100 where higher = more anomalous.
     *
     * Uses z-scores on three metrics (dwell time, flight time, typing speed)
     * with guards for missing or zero std-dev values.
     */
    private function computeAnomalyScore(array $session, KeystrokeBaseline $baseline): float
    {
        $zDwell  = $this->zScore(
            $session['mean_dwell']     ?? 0,
            $baseline->mean_dwell      ?? 0,
            $baseline->std_dwell       ?? 1
        );
        $zFlight = $this->zScore(
            $session['mean_flight']    ?? 0,
            $baseline->mean_flight     ?? 0,
            $baseline->std_flight      ?? 1
        );
        $zSpeed  = $this->zScore(
            $session['mean_speed_cps'] ?? 0,
            $baseline->mean_speed_cps  ?? 1,
            $baseline->std_speed_cps   ?? 0.5
        );

        // Weighted combination: dwell + flight carry equal weight, speed lighter
        $zCombined = ($zDwell * 0.40) + ($zFlight * 0.40) + ($zSpeed * 0.20);

        // Scale so z = 4 → score 100
        return round(min(100.0, $zCombined * 25.0), 1);
    }

    private function zScore(float $value, float $mean, float $std): float
    {
        if ($std < 0.001) {
            $std = 1.0; // guard against divide-by-zero / near-zero std
        }
        return abs($value - $mean) / $std;
    }

    private function resolveFlag(float $score): string
    {
        if ($score >= self::THRESHOLD_SUSPECT) return 'suspect';
        if ($score >= self::THRESHOLD_CAUTION) return 'caution';
        return 'normal';
    }

    // ── Rolling baseline update ────────────────────────────────────────────────

    /**
     * Incrementally update (or create) the baseline using a rolling average
     * so it adapts slowly over time without requiring all raw data.
     *
     * Formula: new_mean = (old_mean * n + new_value) / (n + 1)
     *          new_var  = (old_var  * n + (new_value - new_mean)^2) / (n + 1)
     */
    private function updateBaseline(string $userId, string $deviceType, array $session, ?KeystrokeBaseline $baseline): void
    {
        $metrics = [
            'mean_dwell'      => $session['mean_dwell']     ?? null,
            'std_dwell'       => $session['std_dwell']      ?? null,
            'mean_flight'     => $session['mean_flight']    ?? null,
            'std_flight'      => $session['std_flight']     ?? null,
            'mean_speed_cps'  => $session['mean_speed_cps'] ?? null,
            'std_speed_cps'   => $session['std_speed_cps']  ?? null,
            'mean_error_rate' => $session['error_rate']     ?? null,
        ];

        if (!$baseline) {
            KeystrokeBaseline::create(array_merge($metrics, [
                'user_id'         => $userId,
                'device_type'     => $deviceType,
                'sample_sessions' => 1,
            ]));
            return;
        }

        $n      = $baseline->sample_sessions;
        $nPlus1 = $n + 1;

        $updates = ['sample_sessions' => $nPlus1];

        foreach (['mean_dwell', 'std_dwell', 'mean_flight', 'std_flight', 'mean_speed_cps', 'std_speed_cps', 'mean_error_rate'] as $col) {
            $incoming = $metrics[$col];
            if ($incoming === null) continue;
            $old = $baseline->$col ?? $incoming;
            $updates[$col] = round((($old * $n) + $incoming) / $nPlus1, 3);
        }

        $baseline->update($updates);
    }

    // ── Helper for admin display ───────────────────────────────────────────────

    /**
     * Returns a human-readable comparison array for the admin essay-show view.
     * Each entry: [ label, baseline_val, session_val, unit, z_score ]
     */
    public function buildComparisonTable(array $sessionData, KeystrokeBaseline $baseline): array
    {
        $rows = [];

        $metrics = [
            ['Dwell Time (rata-rata)',  'mean_dwell',     'mean_dwell',     'ms'],
            ['Dwell Time (std dev)',    'std_dwell',      'std_dwell',      'ms'],
            ['Flight Time (rata-rata)', 'mean_flight',    'mean_flight',    'ms'],
            ['Flight Time (std dev)',   'std_flight',     'std_flight',     'ms'],
            ['Kecepatan Ketik',         'mean_speed_cps', 'mean_speed_cps', 'CPS'],
            ['Error Rate',             'mean_error_rate','error_rate',     '%'],
        ];

        foreach ($metrics as [$label, $baselineCol, $sessionKey, $unit]) {
            $baseVal = $baseline->$baselineCol ?? null;
            $sessVal = $sessionData[$sessionKey] ?? null;

            $display = $unit === '%'
                ? fn($v) => $v !== null ? round($v * 100, 1) : '-'
                : fn($v) => $v !== null ? round($v, 1) : '-';

            $z = null;
            if ($baseVal !== null && $sessVal !== null) {
                // Use corresponding std column for z-score on mean metrics
                $stdCol = str_replace('mean_', 'std_', $baselineCol);
                $std    = $baseline->$stdCol ?? 1;
                $z      = round($this->zScore((float)$sessVal, (float)$baseVal, (float)$std), 2);
            }

            $rows[] = [
                'label'       => $label,
                'unit'        => $unit,
                'baseline'    => $display($baseVal),
                'session'     => $display($sessVal),
                'z_score'     => $z,
            ];
        }

        return $rows;
    }
}
