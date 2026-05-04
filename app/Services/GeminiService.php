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
}
