<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Kursus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExplorePathController extends Controller
{
    private const CATEGORY_LABELS = [
        'programming' => 'Programming',
        'design' => 'Design',
        'marketing' => 'Marketing',
        'business' => 'Business',
        'cybersecurity' => 'Cybersecurity',
    ];

    private const CATEGORY_ICONS = [
        'programming' => 'ti-code',
        'design' => 'ti-palette',
        'marketing' => 'ti-speakerphone',
        'business' => 'ti-briefcase',
        'cybersecurity' => 'ti-shield-lock',
    ];

    private const CATEGORY_EXPLANATIONS = [
        'programming' => 'Kamu punya minat kuat pada logika dan membangun solusi digital lewat kode.',
        'design' => 'Kamu cenderung tertarik pada visual, kreativitas, dan pengalaman pengguna.',
        'marketing' => 'Kamu menikmati strategi promosi, konten, dan pertumbuhan brand secara digital.',
        'business' => 'Kamu tertarik pada strategi, manajemen, dan pengembangan ide usaha.',
        'cybersecurity' => 'Kamu punya ketertarikan tinggi pada perlindungan sistem, data, dan keamanan digital.',
    ];

    public function landing()
    {
        return view('public.landing', [
            'categoryLabels' => self::CATEGORY_LABELS,
            'categoryIcons' => self::CATEGORY_ICONS,
        ]);
    }

    public function questionnaire()
    {
        // Acak urutan opsi tiap soal agar kuesioner tidak mudah ditebak —
        // sebelumnya kategori selalu tampil dengan urutan yang sama.
        $questions = array_map(function ($question) {
            $question['options'] = $this->shuffleAssoc($question['options']);
            return $question;
        }, $this->questions());

        return view('public.explore-path', [
            'questions' => $questions,
            'categoryLabels' => self::CATEGORY_LABELS,
        ]);
    }

    public function submit(Request $request)
    {
        // Nilai jawaban sekarang berupa kunci kategori (bukan nomor urut opsi),
        // sehingga aman walau urutan opsi diacak.
        $validated = $request->validate([
            'answers' => ['required', 'array', 'size:5'],
            'answers.*' => ['required', Rule::in(array_keys(self::CATEGORY_LABELS))],
        ]);

        // Hitung berapa kali tiap kategori dipilih
        $scores = collect(array_keys(self::CATEGORY_LABELS))
            ->mapWithKeys(fn($category) => [$category => 0])
            ->all();

        foreach ($validated['answers'] as $category) {
            if (isset($scores[$category])) {
                $scores[$category]++;
            }
        }

        // Tentukan kategori teratas. Bila skor seri, tie-break secara adil
        // berdasarkan jawaban pengguna sendiri — bukan urutan kategori bawaan
        // sistem — supaya hasil tidak bias ke kategori tertentu:
        //   1. utamakan jawaban soal terakhir (pilihan paling konkret),
        //   2. jika tidak termasuk yang seri, ambil kategori seri yang
        //      paling awal dipilih pengguna.
        $maxScore = max($scores);
        $tiedTop = array_keys(array_filter($scores, fn($s) => $s === $maxScore));

        if (count($tiedTop) === 1) {
            $topCategory = $tiedTop[0];
        } else {
            $answers = array_values($validated['answers']);
            $lastAnswer = end($answers);

            if (in_array($lastAnswer, $tiedTop, true)) {
                $topCategory = $lastAnswer;
            } else {
                $topCategory = $tiedTop[0];
                foreach ($answers as $answer) {
                    if (in_array($answer, $tiedTop, true)) {
                        $topCategory = $answer;
                        break;
                    }
                }
            }
        }

        // Alternatif: kategori dengan skor tertinggi kedua, hanya bila cukup
        // dekat dengan kategori utama (selisih <= 1).
        $remaining = $scores;
        unset($remaining[$topCategory]);
        arsort($remaining);

        $alternativeCategory = null;
        if (!empty($remaining)) {
            $secondCategory = array_key_first($remaining);
            $secondScore = $remaining[$secondCategory];
            if ($secondScore > 0 && ($maxScore - $secondScore) <= 1) {
                $alternativeCategory = $secondCategory;
            }
        }

        $suggestedCourses = Kursus::query()
            ->where('status', 'aktif')
            ->where('category', $topCategory)
            ->latest()
            ->limit(3)
            ->get(['id', 'title', 'difficulty', 'short_description', 'thumbnail']);

        // Urutkan skor menurun agar rincian di halaman hasil tampil rapi
        arsort($scores);

        session([
            'explore_path_result' => [
                'scores' => $scores,
                'recommended_category' => $topCategory,
                'alternative_category' => $alternativeCategory,
                'explanation' => self::CATEGORY_EXPLANATIONS[$topCategory],
                'suggested_courses' => $suggestedCourses->toArray(),
            ],
        ]);

        return to_route('explore.result');
    }

    public function result()
    {
        $result = session('explore_path_result');

        if (!$result) {
            return to_route('explore.index');
        }

        return view('public.explore-result', [
            'result' => $result,
            'categoryLabels' => self::CATEGORY_LABELS,
            'categoryIcons' => self::CATEGORY_ICONS,
        ]);
    }

    /**
     * Acak urutan elemen array asosiatif tanpa kehilangan pasangan key => value.
     */
    private function shuffleAssoc(array $items): array
    {
        $keys = array_keys($items);
        shuffle($keys);

        $shuffled = [];
        foreach ($keys as $key) {
            $shuffled[$key] = $items[$key];
        }

        return $shuffled;
    }

    private function questions(): array
    {
        return [
            [
                'text' => 'Bidang apa yang paling ingin kamu pelajari?',
                'options' => [
                    'programming' => 'Membuat aplikasi atau website',
                    'design' => 'Membuat desain visual atau UI',
                    'marketing' => 'Belajar promosi digital dan media sosial',
                    'business' => 'Belajar bisnis dan strategi usaha',
                    'cybersecurity' => 'Belajar keamanan sistem dan data',
                ],
            ],
            [
                'text' => 'Aktivitas mana yang paling menarik buat kamu?',
                'options' => [
                    'programming' => 'Menyusun logika dan memecahkan masalah',
                    'design' => 'Mendesain tampilan yang menarik',
                    'marketing' => 'Membuat konten atau campaign promosi',
                    'business' => 'Mengatur rencana usaha atau proyek',
                    'cybersecurity' => 'Menganalisis risiko dan melindungi data',
                ],
            ],
            [
                'text' => 'Tujuan utama kamu belajar di EduSkill apa?',
                'options' => [
                    'programming' => 'Bisa membuat website atau aplikasi',
                    'design' => 'Bisa membuat desain yang menarik',
                    'marketing' => 'Bisa memahami digital marketing',
                    'business' => 'Bisa memahami dasar bisnis dan manajemen',
                    'cybersecurity' => 'Bisa memahami keamanan digital',
                ],
            ],
            [
                'text' => 'Kalau diberi satu tugas, mana yang paling ingin kamu kerjakan?',
                'options' => [
                    'programming' => 'Menulis kode untuk membuat fitur',
                    'design' => 'Mendesain tampilan aplikasi atau poster',
                    'marketing' => 'Menyusun strategi promosi produk',
                    'business' => 'Membuat rencana bisnis sederhana',
                    'cybersecurity' => 'Mencari celah keamanan pada sistem',
                ],
            ],
            [
                'text' => 'Kalau harus mulai dari satu topik, kamu pilih yang mana?',
                'options' => [
                    'programming' => 'HTML, CSS, dan JavaScript',
                    'design' => 'UI Design, Canva, atau Figma',
                    'marketing' => 'Social Media Marketing dan Branding',
                    'business' => 'Entrepreneurship dan Business Planning',
                    'cybersecurity' => 'Cyber Awareness dan Network Security',
                ],
            ],
        ];
    }
}
