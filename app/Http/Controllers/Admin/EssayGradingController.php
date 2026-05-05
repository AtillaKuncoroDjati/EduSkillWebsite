<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserContentProgress;
use App\Models\UserQuizAttempt;
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
                    'content_title'  => $attempt->content->title ?? '-',
                    'kursus_title'   => $attempt->content->module->kursus->judul ?? '-',
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

        $questions = $attempt->content->questions->map(fn($q) => [
            'id'       => $q->id,
            'question' => $q->question,
        ])->values()->toArray();

        $essayAnswers = $attempt->essay_answers ?? [];

        $pairs = [];
        foreach ($questions as $i => $q) {
            $key        = 'essay_' . $i;
            $stored     = $essayAnswers[$key] ?? [];
            $pairs[] = [
                'index'    => $i,
                'question' => $q['question'],
                'answer'   => $stored['answer'] ?? '',
                'score'    => $stored['score'] ?? null,
                'feedback' => $stored['feedback'] ?? '',
            ];
        }

        return view('admin.essay.show', compact('attempt', 'pairs'));
    }

    public function grade(Request $request, $id)
    {
        $attempt = UserQuizAttempt::with('content.questions')->findOrFail($id);

        if ($attempt->content->quiz_type !== 'essay' || $attempt->content->grading_type !== 'manual') {
            abort(404);
        }

        $request->validate([
            'scores'    => 'required|array',
            'scores.*'  => 'required|integer|min:0|max:100',
            'feedbacks' => 'nullable|array',
        ]);

        $questions    = $attempt->content->questions->values();
        $essayAnswers = $attempt->essay_answers ?? [];
        $totalScore   = 0;
        $passCount    = 0;

        foreach ($questions as $i => $q) {
            $key   = 'essay_' . $i;
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
        }

        $count    = max(1, $questions->count());
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
                ['user_id' => $attempt->user_id, 'content_id' => $attempt->content_id],
                ['is_completed' => true]
            );
        }

        return redirect()->route('admin.essay.index')
            ->with('success', 'Penilaian esai berhasil disimpan.');
    }
}
