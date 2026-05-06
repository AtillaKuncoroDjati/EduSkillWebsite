<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Judge0Service
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = config('judge0.api_key', '');
        $this->baseUrl = rtrim(config('judge0.base_url', ''), '/');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Get the Judge0 language ID for a given language key.
     */
    public function getLanguageId(string $language): int
    {
        return config("judge0.languages.{$language}.id", 71);
    }

    /**
     * Submit code to Judge0 and wait for the result.
     *
     * @return array{status_id: int, status_description: string, stdout: string|null, stderr: string|null, compile_output: string|null, time: string|null, memory: int|null}
     */
    public function runCode(string $code, string $language, ?string $stdin = null): array
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Judge0 API key belum dikonfigurasi. Hubungi admin untuk mengaktifkan fitur code runner.');
        }

        $languageId = $this->getLanguageId($language);

        $payload = [
            'source_code'         => base64_encode($code),
            'language_id'         => $languageId,
            'stdin'               => $stdin ? base64_encode($stdin) : null,
            'expected_output'     => null,
            'cpu_time_limit'      => 5,
            'memory_limit'        => 128000,
            'enable_per_process_and_thread_time_limit' => false,
        ];

        // Submit
        $submitResponse = Http::withOptions(['verify' => false])
            ->timeout(15)
            ->withHeaders([
                'X-RapidAPI-Key'  => $this->apiKey,
                'X-RapidAPI-Host' => parse_url($this->baseUrl, PHP_URL_HOST),
                'Content-Type'    => 'application/json',
            ])
            ->post("{$this->baseUrl}/submissions?base64_encoded=true&wait=false", $payload);

        if (!$submitResponse->successful()) {
            Log::error('Judge0 submit error', ['status' => $submitResponse->status(), 'body' => $submitResponse->body()]);
            throw new \RuntimeException('Gagal mengirim kode ke Judge0 (HTTP ' . $submitResponse->status() . ').');
        }

        $token = $submitResponse->json('token');
        if (!$token) {
            throw new \RuntimeException('Judge0 tidak mengembalikan token submission.');
        }

        // Poll for result (max 10 attempts, 1s apart)
        for ($i = 0; $i < 10; $i++) {
            usleep(1000000); // 1 second

            $resultResponse = Http::withOptions(['verify' => false])
                ->timeout(10)
                ->withHeaders([
                    'X-RapidAPI-Key'  => $this->apiKey,
                    'X-RapidAPI-Host' => parse_url($this->baseUrl, PHP_URL_HOST),
                ])
                ->get("{$this->baseUrl}/submissions/{$token}?base64_encoded=true&fields=status,stdout,stderr,compile_output,time,memory");

            if (!$resultResponse->successful()) {
                continue;
            }

            $result   = $resultResponse->json();
            $statusId = $result['status']['id'] ?? 0;

            // Still processing
            if (in_array($statusId, [1, 2])) {
                continue;
            }

            return [
                'status_id'          => $statusId,
                'status_description' => $result['status']['description'] ?? 'Unknown',
                'stdout'             => isset($result['stdout']) ? base64_decode($result['stdout']) : null,
                'stderr'             => isset($result['stderr']) ? base64_decode($result['stderr']) : null,
                'compile_output'     => isset($result['compile_output']) ? base64_decode($result['compile_output']) : null,
                'time'               => $result['time'] ?? null,
                'memory'             => $result['memory'] ?? null,
            ];
        }

        throw new \RuntimeException('Judge0 timeout: kode membutuhkan waktu terlalu lama untuk dieksekusi.');
    }

    /**
     * Check if the code output matches expected output (trimmed comparison).
     */
    public function checkOutput(?string $actual, ?string $expected): bool
    {
        if ($expected === null || trim($expected) === '') {
            return true; // No expected output configured — always pass
        }

        return trim((string) $actual) === trim((string) $expected);
    }
}
