<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\Kursus;
use Illuminate\Http\Request;

class ExplorePathController extends Controller
{
    private const OPTION_TO_CATEGORY = [
        '1' => 'programming',
        '2' => 'design',
        '3' => 'marketing',
        '4' => 'business',
        '5' => 'cybersecurity',
    ];

    private const CATEGORY_LABELS = [
        'programming' => 'Programming',
        'design' => 'Design',
        'marketing' => 'Marketing',
        'business' => 'Business',
        'cybersecurity' => 'Cybersecurity',
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
        return view('public.landing');
    }

    public function questionnaire()
    {
        return view('public.explore-path', [
            'questions' => $this->questions(),
            'categoryLabels' => self::CATEGORY_LABELS,
        ]);
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'answers' => ['required', 'array', 'size:5'],
            'answers.*' => ['required', 'in:1,2,3,4,5'],
        ]);

        $scores = collect(array_keys(self::CATEGORY_LABELS))
            ->mapWithKeys(fn($category) => [$category => 0])
            ->all();

        foreach ($validated['answers'] as $selectedOption) {
            $category = self::OPTION_TO_CATEGORY[(string) $selectedOption] ?? null;
            if ($category !== null) {
                $scores[$category]++;
            }
        }

        arsort($scores);
        $categories = array_keys($scores);
        $topCategory = $categories[0];
        $topScore = $scores[$topCategory];

        $alternativeCategory = null;
        if (isset($categories[1])) {
            $secondCategory = $categories[1];
            $secondScore = $scores[$secondCategory];

            if ($secondScore > 0 && ($topScore - $secondScore) <= 1) {
                $alternativeCategory = $secondCategory;
            }
        }

        $suggestedCourses = Kursus::query()
            ->where('status', 'aktif')
            ->where('category', $topCategory)
            ->latest()
            ->limit(3)
            ->get(['id', 'title', 'difficulty', 'short_description', 'thumbnail']);

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
        ]);
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
