<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key');
        $this->model  = config('gemini.model', 'gemini-2.0-flash');
    }

    /**
     * Upload a PDF file to Gemini Files API and return its file URI.
     */
    private function uploadPdfToGemini(string $pdfPath): string
    {
        if (!file_exists($pdfPath)) {
            throw new \RuntimeException('File PDF tidak ditemukan di server.');
        }

        $pdfContent = file_get_contents($pdfPath);
        $boundary   = 'boundary_' . bin2hex(random_bytes(8));
        $metadata   = json_encode(['file' => ['display_name' => basename($pdfPath)]]);

        $body  = "--{$boundary}\r\n";
        $body .= "Content-Type: application/json; charset=utf-8\r\n\r\n";
        $body .= $metadata . "\r\n";
        $body .= "--{$boundary}\r\n";
        $body .= "Content-Type: application/pdf\r\n\r\n";
        $body .= $pdfContent . "\r\n";
        $body .= "--{$boundary}--";

        $response = Http::withOptions(['verify' => false])
            ->timeout(120)
            ->withHeaders([
                'Content-Type'           => "multipart/related; boundary={$boundary}",
                'X-Goog-Upload-Protocol' => 'multipart',
            ])
            ->withBody($body, "multipart/related; boundary={$boundary}")
            ->post("https://generativelanguage.googleapis.com/upload/v1beta/files?key={$this->apiKey}");

        if (!$response->successful()) {
            throw new \RuntimeException('Gagal mengupload PDF ke Gemini (HTTP ' . $response->status() . ').');
        }

        $uri = $response->json('file.uri');
        if (!$uri) {
            throw new \RuntimeException('Gemini tidak mengembalikan URI file setelah upload.');
        }

        return $uri;
    }

    /**
     * Generate multiple-choice questions from a PDF file via Gemini Files API.
     *
     * @return array<int, array{question: string, options: string[], correct_index: int}>
     */
    public function generateQuizQuestionsFromPdf(string $pdfPath, int $questionCount = 5): array
    {
        $fileUri = $this->uploadPdfToGemini($pdfPath);

        $prompt = <<<PROMPT
Berdasarkan dokumen PDF ini, buatlah {$questionCount} soal pilihan ganda dalam Bahasa Indonesia yang menguji pemahaman mendalam.

Aturan ketat:
- Setiap soal memiliki TEPAT 4 pilihan jawaban.
- Hanya ada SATU jawaban yang benar per soal.
- Soal harus bervariasi dan tidak mudah ditebak.
- Pilihan jawaban yang salah harus masuk akal.

Kembalikan HANYA JSON valid (tanpa markdown, tanpa penjelasan) dengan format persis ini:
{"questions":[{"question":"teks pertanyaan","options":["pilihan A","pilihan B","pilihan C","pilihan D"],"correct_index":0}]}

correct_index adalah indeks (0-3) dari array options yang merupakan jawaban benar.
PROMPT;

        $response = Http::withOptions(['verify' => false])
            ->timeout(60)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}",
                [
                    'contents' => [[
                        'parts' => [
                            ['file_data' => ['mime_type' => 'application/pdf', 'file_uri' => $fileUri]],
                            ['text' => $prompt],
                        ],
                    ]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature'      => 0.9,
                    ],
                ]
            );

        if (!$response->successful()) {
            throw new \RuntimeException('Gemini API gagal merespons (HTTP ' . $response->status() . ').');
        }

        $rawJson = $response->json('candidates.0.content.parts.0.text');
        if (!$rawJson) {
            throw new \RuntimeException('Gemini tidak menghasilkan teks respons dari PDF.');
        }

        $parsed = json_decode($rawJson, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($parsed['questions'])) {
            throw new \RuntimeException('Format JSON dari Gemini tidak valid.');
        }

        $questions = [];
        foreach ($parsed['questions'] as $q) {
            if (!isset($q['question'], $q['options'], $q['correct_index'])) continue;
            if (!is_array($q['options']) || count($q['options']) < 2) continue;
            $idx = (int) $q['correct_index'];
            if ($idx < 0 || $idx >= count($q['options'])) continue;
            $questions[] = [
                'question'      => (string) $q['question'],
                'options'       => array_values(array_map('strval', $q['options'])),
                'correct_index' => $idx,
            ];
        }

        if (empty($questions)) {
            throw new \RuntimeException('Gemini tidak menghasilkan soal dari PDF.');
        }

        return $questions;
    }

    /**
     * Generate essay questions from a PDF file via Gemini Files API.
     *
     * @return array<int, array{question: string}>
     */
    public function generateEssayQuestionsFromPdf(string $pdfPath, int $questionCount = 5): array
    {
        $fileUri = $this->uploadPdfToGemini($pdfPath);

        $prompt = <<<PROMPT
Berdasarkan dokumen PDF ini, buatlah {$questionCount} soal esai terbuka dalam Bahasa Indonesia yang menguji pemahaman mendalam.

Aturan ketat:
- Setiap soal harus memerlukan jawaban panjang berupa penjelasan konsep.
- Hindari soal yang bisa dijawab dengan "ya" atau "tidak".
- Soal harus bervariasi dan mencakup aspek berbeda dari materi.

Kembalikan HANYA JSON valid (tanpa markdown, tanpa penjelasan) dengan format persis ini:
{"questions":[{"question":"teks soal esai di sini"}]}
PROMPT;

        $response = Http::withOptions(['verify' => false])
            ->timeout(60)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}",
                [
                    'contents' => [[
                        'parts' => [
                            ['file_data' => ['mime_type' => 'application/pdf', 'file_uri' => $fileUri]],
                            ['text' => $prompt],
                        ],
                    ]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature'      => 0.8,
                    ],
                ]
            );

        if (!$response->successful()) {
            throw new \RuntimeException('Gemini API gagal merespons (HTTP ' . $response->status() . ').');
        }

        $rawJson = $response->json('candidates.0.content.parts.0.text');
        if (!$rawJson) {
            throw new \RuntimeException('Gemini tidak menghasilkan teks respons dari PDF.');
        }

        $parsed = json_decode($rawJson, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($parsed['questions'])) {
            throw new \RuntimeException('Format JSON dari Gemini tidak valid.');
        }

        $questions = [];
        foreach ($parsed['questions'] as $q) {
            if (empty(trim($q['question'] ?? ''))) continue;
            $questions[] = ['question' => (string) $q['question']];
        }

        if (empty($questions)) {
            throw new \RuntimeException('Gemini tidak menghasilkan soal esai dari PDF.');
        }

        return $questions;
    }

    /**
     * Grade essay answers using a PDF as the reference material.
     *
     * @param  array<int, array{question: string, answer: string}> $questionsAndAnswers
     * @return array<int, array{score: int, feedback: string}>
     */
    public function gradeEssayAnswersFromPdf(string $pdfPath, array $questionsAndAnswers): array
    {
        $fileUri = $this->uploadPdfToGemini($pdfPath);
        $count   = count($questionsAndAnswers);

        $qaText = '';
        foreach ($questionsAndAnswers as $i => $qa) {
            $qaText .= ($i + 1) . ". Pertanyaan: {$qa['question']}\n   Jawaban: {$qa['answer']}\n\n";
        }

        $prompt = <<<PROMPT
Kamu adalah asisten penilaian esai akademik yang objektif dan konstruktif.

Gunakan dokumen PDF terlampir sebagai materi referensi.

Nilai {$count} jawaban esai berikut. Setiap jawaban dinilai berdasarkan:
- Kesesuaian dengan materi referensi (40%)
- Kedalaman pemahaman konsep (40%)
- Kejelasan dan kelengkapan penjelasan (20%)

Berikan skor 0-100 dan feedback konstruktif singkat dalam Bahasa Indonesia untuk setiap jawaban.
Jika jawaban kosong atau tidak relevan, beri skor 0.

Kembalikan HANYA JSON valid (tanpa markdown) dengan format persis ini:
{"grades":[{"score":85,"feedback":"Penjelasan yang baik..."}]}

Pertanyaan dan Jawaban:
{$qaText}
PROMPT;

        $response = Http::withOptions(['verify' => false])
            ->timeout(60)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}",
                [
                    'contents' => [[
                        'parts' => [
                            ['file_data' => ['mime_type' => 'application/pdf', 'file_uri' => $fileUri]],
                            ['text' => $prompt],
                        ],
                    ]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature'      => 0.3,
                    ],
                ]
            );

        if (!$response->successful()) {
            throw new \RuntimeException('Gemini API gagal menilai jawaban (HTTP ' . $response->status() . ').');
        }

        $rawJson = $response->json('candidates.0.content.parts.0.text');
        if (!$rawJson) {
            throw new \RuntimeException('Gemini tidak menghasilkan hasil penilaian dari PDF.');
        }

        $parsed = json_decode($rawJson, true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($parsed['grades'])) {
            throw new \RuntimeException('Format JSON penilaian dari Gemini tidak valid.');
        }

        $grades = [];
        foreach ($parsed['grades'] as $g) {
            $grades[] = [
                'score'    => max(0, min(100, (int) ($g['score'] ?? 0))),
                'feedback' => (string) ($g['feedback'] ?? ''),
            ];
        }

        return $grades;
    }

    /**
     * Generate multiple-choice quiz questions from module content text.
     *
     * @return array<int, array{question: string, options: string[], correct_index: int}>
     */
    public function generateQuizQuestions(string $contentHtml, int $questionCount = 5): array
    {
        $plainText = trim(strip_tags($contentHtml));

        if (strlen($plainText) < 30) {
            throw new \RuntimeException('Konten materi terlalu singkat untuk membuat soal kuis.');
        }

        $prompt = <<<PROMPT
Berdasarkan materi berikut, buatlah {$questionCount} soal pilihan ganda dalam Bahasa Indonesia yang menguji pemahaman mendalam.

Aturan ketat:
- Setiap soal memiliki TEPAT 4 pilihan jawaban.
- Hanya ada SATU jawaban yang benar per soal.
- Soal harus bervariasi dan tidak mudah ditebak.
- Pilihan jawaban yang salah harus masuk akal (bukan jawaban jebakan yang terlalu jelas).

Kembalikan HANYA JSON valid (tanpa markdown, tanpa penjelasan) dengan format persis ini:
{"questions":[{"question":"teks pertanyaan","options":["pilihan A","pilihan B","pilihan C","pilihan D"],"correct_index":0}]}

correct_index adalah indeks (0-3) dari array options yang merupakan jawaban benar.

Materi:
{$plainText}
PROMPT;

        $response = Http::withOptions(['verify' => false])
            ->timeout(45)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}",
                [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature'      => 0.9,
                    ],
                ]
            );

        if (!$response->successful()) {
            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Gemini API gagal merespons (HTTP ' . $response->status() . ').');
        }

        $body    = $response->json();
        $rawJson = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$rawJson) {
            throw new \RuntimeException('Gemini tidak menghasilkan teks respons.');
        }

        $parsed = json_decode($rawJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($parsed['questions'])) {
            throw new \RuntimeException('Format JSON dari Gemini tidak valid.');
        }

        $questions = [];
        foreach ($parsed['questions'] as $q) {
            if (!isset($q['question'], $q['options'], $q['correct_index'])) {
                continue;
            }
            if (!is_array($q['options']) || count($q['options']) < 2) {
                continue;
            }
            $idx = (int) $q['correct_index'];
            if ($idx < 0 || $idx >= count($q['options'])) {
                continue;
            }

            $questions[] = [
                'question'      => (string) $q['question'],
                'options'       => array_values(array_map('strval', $q['options'])),
                'correct_index' => $idx,
            ];
        }

        if (empty($questions)) {
            throw new \RuntimeException('Gemini tidak menghasilkan soal yang dapat digunakan.');
        }

        return $questions;
    }

    /**
     * Generate open-ended essay questions from module content.
     *
     * @return array<int, array{question: string}>
     */
    public function generateEssayQuestions(string $contentHtml, int $questionCount = 5): array
    {
        $plainText = trim(strip_tags($contentHtml));

        if (strlen($plainText) < 30) {
            throw new \RuntimeException('Konten materi terlalu singkat untuk membuat soal esai.');
        }

        $prompt = <<<PROMPT
Berdasarkan materi berikut, buatlah {$questionCount} soal esai terbuka dalam Bahasa Indonesia yang menguji pemahaman mendalam.

Aturan ketat:
- Setiap soal harus memerlukan jawaban panjang berupa penjelasan konsep.
- Hindari soal yang bisa dijawab dengan "ya" atau "tidak".
- Soal harus bervariasi dan mencakup aspek berbeda dari materi.

Kembalikan HANYA JSON valid (tanpa markdown, tanpa penjelasan) dengan format persis ini:
{"questions":[{"question":"teks soal esai di sini"}]}

Materi:
{$plainText}
PROMPT;

        $response = Http::withOptions(['verify' => false])
            ->timeout(45)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}",
                [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature'      => 0.8,
                    ],
                ]
            );

        if (!$response->successful()) {
            Log::error('Gemini API error (essay questions)', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Gemini API gagal merespons (HTTP ' . $response->status() . ').');
        }

        $body    = $response->json();
        $rawJson = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$rawJson) {
            throw new \RuntimeException('Gemini tidak menghasilkan teks respons.');
        }

        $parsed = json_decode($rawJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($parsed['questions'])) {
            throw new \RuntimeException('Format JSON dari Gemini tidak valid.');
        }

        $questions = [];
        foreach ($parsed['questions'] as $q) {
            if (empty(trim($q['question'] ?? ''))) {
                continue;
            }
            $questions[] = ['question' => (string) $q['question']];
        }

        if (empty($questions)) {
            throw new \RuntimeException('Gemini tidak menghasilkan soal esai yang dapat digunakan.');
        }

        return $questions;
    }

    /**
     * Grade essay answers against source material.
     *
     * @param  array<int, array{question: string, answer: string}> $questionsAndAnswers
     * @return array<int, array{score: int, feedback: string}>
     */
    public function gradeEssayAnswers(string $sourceText, array $questionsAndAnswers): array
    {
        $plainSource = trim(strip_tags($sourceText));
        $count       = count($questionsAndAnswers);

        $qaText = '';
        foreach ($questionsAndAnswers as $i => $qa) {
            $qaText .= ($i + 1) . ". Pertanyaan: {$qa['question']}\n   Jawaban: {$qa['answer']}\n\n";
        }

        $prompt = <<<PROMPT
Kamu adalah asisten penilaian esai akademik yang objektif dan konstruktif.

Materi referensi:
{$plainSource}

Nilai {$count} jawaban esai berikut. Setiap jawaban dinilai berdasarkan:
- Kesesuaian dengan materi referensi (40%)
- Kedalaman pemahaman konsep (40%)
- Kejelasan dan kelengkapan penjelasan (20%)

Berikan skor 0-100 dan feedback konstruktif singkat dalam Bahasa Indonesia untuk setiap jawaban.
Jika jawaban kosong atau tidak relevan, beri skor 0.

Kembalikan HANYA JSON valid (tanpa markdown) dengan format persis ini:
{"grades":[{"score":85,"feedback":"Penjelasan yang baik..."}]}

Pertanyaan dan Jawaban:
{$qaText}
PROMPT;

        $response = Http::withOptions(['verify' => false])
            ->timeout(60)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}",
                [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]],
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'temperature'      => 0.3,
                    ],
                ]
            );

        if (!$response->successful()) {
            Log::error('Gemini API error (essay grading)', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Gemini API gagal menilai jawaban (HTTP ' . $response->status() . ').');
        }

        $body    = $response->json();
        $rawJson = $body['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (!$rawJson) {
            throw new \RuntimeException('Gemini tidak menghasilkan hasil penilaian.');
        }

        $parsed = json_decode($rawJson, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($parsed['grades'])) {
            throw new \RuntimeException('Format JSON penilaian dari Gemini tidak valid.');
        }

        $grades = [];
        foreach ($parsed['grades'] as $g) {
            $grades[] = [
                'score'    => max(0, min(100, (int) ($g['score'] ?? 0))),
                'feedback' => (string) ($g['feedback'] ?? ''),
            ];
        }

        return $grades;
    }
}
