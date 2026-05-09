<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KeystrokeBaseline;
use App\Models\UserContentProgress;
use App\Models\UserCourse;
use App\Models\UserQuizAttempt;
use App\Services\KeystrokeAnalysisService;
use Illuminate\Http\Request;

class EssayGradingController extends Controller
{
    public function index()
    {
        return view('admin.essay.index');
    }

    public function list(Request $request)
    {
        $query = UserQuizAttempt::with(['user', 'content', 'content.module.kursus'])
            ->whereHas('content', fn($q) => $q->where('quiz_type', 'essay')->where('grading_type', 'manual'))
            ->whereIn('grading_status', ['pending_review', 'graded']);

        $statusFilter = $request->input('status_filter');
        if (in_array($statusFilter, ['pending_review', 'graded'])) {
            $query->where('grading_status', $statusFilter);
        }

        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($sub) => $sub->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%"))
                  ->orWhereHas('content', fn($sub) => $sub->where('title', 'like', "%$search%"));
            });
        }

        $total = $query->count();

        if ($request->has('start') && $request->has('length')) {
            $query->skip($request->input('start'))->take($request->input('length'));
        }

        $data = $query->orderByRaw("FIELD(grading_status, 'pending_review', 'graded')")
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($attempt) {
                return [
                    'id'             => $attempt->id,
                    'user_name'      => $attempt->user->name ?? '-',
                    'user_email'     => $attempt->user->email ?? '-',
                    'content_title'  => $attempt->content->title ?: '-',
                    'kursus_title'   => $attempt->content->module->kursus->title ?? '-',
                    'grading_status' => $attempt->grading_status,
                    'score'          => $attempt->score,
                    'is_passed'      => $attempt->is_passed,
                    'submitted_at'   => $attempt->completed_at?->format('d M Y H:i'),
                ];
            });

        return response()->json([
            'draw'            => $request->input('draw', 1),
            'recordsTotal'    => $total,
            'recordsFiltered' => $total,
            'data'            => $data,
        ]);
    }

    public function show($id)
    {
        $attempt = UserQuizAttempt::with(['user', 'content.module.kursus', 'content.questions'])
            ->findOrFail($id);

        if ($attempt->content->quiz_type !== 'essay' || $attempt->content->grading_type !== 'manual') {
            abort(404);
        }

        $essayAnswers = $attempt->essay_answers ?? [];

        // Build pairs from essay_answers (handles both manual and AI-generated essays).
        // Sort numerically by 'essay_X' index — string sort would put essay_10 before essay_2.
        $keys = array_keys($essayAnswers);
        usort($keys, fn($a, $b) => (int) substr($a, 6) <=> (int) substr($b, 6));

        $contentQuestions   = $attempt->content->questions->values();
        $generatedQuestions = $attempt->generated_questions ?? [];

        $pairs = [];
        foreach ($keys as $key) {
            if (!preg_match('/^essay_(\d+)$/', $key, $m)) continue;
            $i      = (int) $m[1];
            $stored = $essayAnswers[$key];

            $questionText = $stored['question'] ?? null;
            if (!$questionText) {
                if ($attempt->content->is_ai_generated) {
                    $questionText = $generatedQuestions[$i]['question'] ?? '(Pertanyaan tidak tersedia)';
                } else {
                    $questionText = $contentQuestions[$i]->question ?? '(Pertanyaan tidak tersedia)';
                }
            }

            $pairs[] = [
                'index'    => $i,
                'question' => $questionText,
                'answer'   => $stored['answer'] ?? '',
                'score'    => $stored['score'] ?? null,
                'feedback' => $stored['feedback'] ?? '',
            ];
        }

        // Fallback: if no essay_answers yet (edge case), seed pairs from source questions
        if (empty($pairs)) {
            $sourceQuestions = $attempt->content->is_ai_generated
                ? array_map(fn($gq) => $gq['question'], $generatedQuestions)
                : $contentQuestions->pluck('question')->all();
            foreach ($sourceQuestions as $i => $qText) {
                $pairs[] = [
                    'index'    => $i,
                    'question' => $qText,
                    'answer'   => '',
                    'score'    => null,
                    'feedback' => '',
                ];
            }
        }

        // Keystroke analytics
        $keystrokeData     = $attempt->keystroke_data ?? null;
        $keystrokeBaseline = null;
        $keystrokeTable    = [];
        $deviceType        = $keystrokeData['device_type'] ?? 'desktop';

        if ($keystrokeData) {
            $keystrokeBaseline = KeystrokeBaseline::where('user_id', $attempt->user_id)
                ->where('device_type', $deviceType)
                ->first();

            if ($keystrokeBaseline && $keystrokeBaseline->sample_sessions >= 2) {
                $keystrokeTable = app(KeystrokeAnalysisService::class)
                    ->buildComparisonTable($keystrokeData, $keystrokeBaseline);
            }
        }

        return view('admin.essay.show', compact(
            'attempt', 'pairs',
            'keystrokeData', 'keystrokeBaseline', 'keystrokeTable'
        ));
    }

    public function grade(Request $request, $id)
    {
        $attempt = UserQuizAttempt::with(['content.questions', 'userCourse'])->findOrFail($id);

        if ($attempt->content->quiz_type !== 'essay' || $attempt->content->grading_type !== 'manual') {
            abort(404);
        }

        $request->validate([
            'scores'    => 'required|array',
            'scores.*'  => 'required|integer|min:0|max:100',
            'feedbacks' => 'nullable|array',
        ]);

        $essayAnswers = $attempt->essay_answers ?? [];
        $totalScore   = 0;
        $passCount    = 0;
        $count        = 0;

        // Iterate by essay_answers keys to handle both manual and AI-generated essays
        $keys = array_keys($essayAnswers);
        usort($keys, fn($a, $b) => (int) substr($a, 6) <=> (int) substr($b, 6));

        foreach ($keys as $key) {
            if (!preg_match('/^essay_(\d+)$/', $key, $m)) continue;
            $i     = (int) $m[1];
            $score = (int) ($request->scores[$i] ?? 0);
            $fb    = (string) ($request->feedbacks[$i] ?? '');

            $essayAnswers[$key] = array_merge($essayAnswers[$key] ?? [], [
                'score'    => $score,
                'feedback' => $fb,
            ]);

            $totalScore += $score;
            if ($score >= 70) {
                $passCount++;
            }
            $count++;
        }

        $count    = max(1, $count);
        $avgScore = round($totalScore / $count);
        $isPassed = $avgScore >= 70;

        $attempt->update([
            'essay_answers'  => $essayAnswers,
            'score'          => $avgScore,
            'correct_answers'=> $passCount,
            'total_questions'=> $count,
            'is_passed'      => $isPassed,
            'grading_status' => 'graded',
            'admin_notes'    => $request->input('admin_notes'),
            'completed_at'   => now(),
        ]);

        if ($isPassed) {
            UserContentProgress::updateOrCreate(
                [
                    'user_id' => $attempt->user_id,
                    'content_id' => $attempt->content_id,
                    'user_course_id' => $attempt->user_course_id,
                ],
                [
                    'is_completed' => true,
                    'completed_at' => now(),
                ]
            );

            if ($attempt->userCourse) {
                $this->updateCourseProgress($attempt->userCourse);
            }
        }

        return redirect()->route('admin.essay.index')
            ->with('success', 'Penilaian esai berhasil disimpan.');
    }

    private function updateCourseProgress(UserCourse $userCourse): void
    {
        $kursus = $userCourse->kursus()->with('modules.contents')->first();

        if (!$kursus) {
            return;
        }

        $totalContents = $kursus->modules->sum(fn($module) => $module->contents->count());
        if ($totalContents === 0) {
            return;
        }

        $completedContents = $userCourse->contentProgress()
            ->where('is_completed', true)
            ->count();

        $percentage = round(($completedContents / $totalContents) * 100);

        $userCourse->update([
            'progress_percentage' => $percentage,
        ]);

        if ($percentage >= 100) {
            $userCourse->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }
    }
}
