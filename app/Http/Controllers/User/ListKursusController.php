<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Kursus;
use App\Models\User;
use App\Models\UserContentProgress;
use App\Models\UserCourse;
use App\Models\UserQuizAnswer;
use App\Models\UserQuizAttempt;
use App\Models\UserQuizIntegrityEvent;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListKursusController extends Controller
{
    public function index()
    {
        return view('user.kursus.index');
    }

    public function show($id)
    {
        $kursus = Kursus::with([
            'modules' => function ($query) {
                $query->orderBy('order', 'asc');
            },
            'modules.contents' => function ($query) {
                $query->orderBy('order', 'asc');
            },
            'modules.contents.questions' => function ($query) {
                $query->orderBy('order', 'asc');
            },
            'modules.contents.questions.options',
            'prerequisites',
        ])
            ->where('id', $id)
            ->where('status', 'aktif')
            ->firstOrFail();

        $totalModules = $kursus->modules->count();
        $totalContents = $kursus->modules->sum(function ($module) {
            return $module->contents->count();
        });
        $estimatedDuration = $totalContents * 15;

        $isEnrolled = false;
        $userCourse = null;
        $isLocked = false;
        $unmetPrerequisites = collect();

        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            $isEnrolled = $user->hasEnrolled($kursus->id);
            $userCourse = $user->getEnrolledCourse($kursus->id);

            foreach ($kursus->prerequisites as $prereq) {
                $completed = UserCourse::where('user_id', $user->id)
                    ->where('kursus_id', $prereq->id)
                    ->where('status', 'completed')
                    ->exists();
                if (!$completed) {
                    $unmetPrerequisites->push($prereq);
                }
            }
            $isLocked = $unmetPrerequisites->isNotEmpty();
        } elseif ($kursus->prerequisites->isNotEmpty()) {
            $isLocked = true;
            $unmetPrerequisites = $kursus->prerequisites;
        }

        return view('user.kursus.show', compact(
            'kursus',
            'totalModules',
            'totalContents',
            'estimatedDuration',
            'isEnrolled',
            'userCourse',
            'isLocked',
            'unmetPrerequisites'
        ));
    }

    public function enroll($id)
    {
        if (!Auth::check()) {
            return redirect()->route('auth.view')->with('error', 'Silakan login terlebih dahulu');
        }

        /** @var User $user */
        $user = Auth::user();

        $kursus = Kursus::with('prerequisites')->where('id', $id)->where('status', 'aktif')->firstOrFail();

        if ($user->hasEnrolled($kursus->id)) {
            return redirect()->route('user.kursus.learn', $kursus->id);
        }

        foreach ($kursus->prerequisites as $prereq) {
            $completed = UserCourse::where('user_id', $user->id)
                ->where('kursus_id', $prereq->id)
                ->where('status', 'completed')
                ->exists();
            if (!$completed) {
                return redirect()->route('user.kursus.show', $kursus->id)
                    ->with('error', 'Anda harus menyelesaikan semua kursus prasyarat terlebih dahulu.');
            }
        }

        UserCourse::create([
            'user_id' => Auth::id(),
            'kursus_id' => $kursus->id,
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        return redirect()->route('user.kursus.learn', $kursus->id)
            ->with('success', 'Berhasil mendaftar kursus!');
    }

    public function learn($id)
    {
        if (!Auth::check()) {
            return redirect()->route('auth.view')->with('error', 'Silakan login terlebih dahulu');
        }

        /** @var User $user */
        $user = Auth::user();

        $kursus = Kursus::with([
            'modules.contents.questions.options'
        ])->findOrFail($id);

        $userCourse = $user->getEnrolledCourse($kursus->id);

        if (!$userCourse) {
            return redirect()->route('user.kursus.show', $kursus->id)
                ->with('error', 'Anda belum mendaftar kursus ini');
        }

        if ($userCourse->status === 'enrolled') {
            $userCourse->update([
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        $contentProgress = $userCourse->contentProgress()->pluck('is_completed', 'content_id')->toArray();

        return view('user.kursus.learn', compact('kursus', 'userCourse', 'contentProgress'));
    }

    public function getContent($kursusId, $contentId)
    {
        $content = Content::with(['questions.options', 'module'])
            ->findOrFail($contentId);

        /** @var User $user */
        $user = Auth::user();

        $userCourse = $user->getEnrolledCourse($kursusId);
        if (!$userCourse) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        if ($content->type === 'text') {
            return response()->json([
                'id'      => $content->id,
                'title'   => $content->title,
                'type'    => $content->type,
                'content' => nl2br($content->content),
            ]);
        }

        $integritySettings = [
            'enabled'          => (bool) $content->integrity_mode_enabled,
            'require_fullscreen' => (bool) $content->require_fullscreen,
            'max_violations'   => (int) ($content->max_violations ?? 3),
        ];

        // ── AI-generated quiz ──────────────────────────────────────────────
        if ($content->is_ai_generated) {
            $passedAttempt = UserQuizAttempt::where('user_id', Auth::id())
                ->where('content_id', $contentId)
                ->where('user_course_id', $userCourse->id)
                ->where('is_passed', true)
                ->whereNotNull('generated_questions')
                ->latest()
                ->first();

            if ($passedAttempt) {
                $quizDetails = $this->buildAiReview($passedAttempt);
                return response()->json([
                    'id'               => $content->id,
                    'title'            => $content->title,
                    'type'             => 'quiz',
                    'is_ai_generated'  => true,
                    'already_passed'   => true,
                    'integrity_settings' => $integritySettings,
                    'attempt'          => [
                        'score'           => $passedAttempt->score,
                        'correct_answers' => $passedAttempt->correct_answers,
                        'total_questions' => $passedAttempt->total_questions,
                        'completed_at'    => $passedAttempt->completed_at->format('d M Y H:i'),
                        'violation_count' => $passedAttempt->violation_count,
                        'is_auto_submitted' => $passedAttempt->is_auto_submitted,
                    ],
                    'quiz_details' => $quizDetails,
                ]);
            }

            // Reuse an existing incomplete attempt (questions already generated)
            $pendingAttempt = UserQuizAttempt::where('user_id', Auth::id())
                ->where('content_id', $contentId)
                ->where('user_course_id', $userCourse->id)
                ->whereNull('completed_at')
                ->whereNotNull('generated_questions')
                ->latest()
                ->first();

            if ($pendingAttempt) {
                $generatedQuestions = $pendingAttempt->generated_questions;
            } else {
                try {
                    $isEssay = $content->quiz_type === 'essay';
                    $generatedQuestions = $isEssay
                        ? app(GeminiService::class)->generateEssayQuestions(
                            $this->resolveAiSourceText($content),
                            $content->ai_question_count ?? 5
                        )
                        : app(GeminiService::class)->generateQuizQuestions(
                            $this->resolveAiSourceText($content),
                            $content->ai_question_count ?? 5
                        );
                } catch (\RuntimeException $e) {
                    return response()->json(['error' => $e->getMessage()], 500);
                }

                $pendingAttempt = UserQuizAttempt::create([
                    'user_id'             => Auth::id(),
                    'content_id'          => $contentId,
                    'user_course_id'      => $userCourse->id,
                    'total_questions'     => count($generatedQuestions),
                    'started_at'          => now(),
                    'violation_count'     => 0,
                    'is_auto_submitted'   => false,
                    'generated_questions' => $generatedQuestions,
                ]);
            }

            if ($content->quiz_type === 'essay') {
                return response()->json([
                    'id'              => $content->id,
                    'title'           => $content->title,
                    'type'            => 'quiz',
                    'quiz_type'       => 'essay',
                    'is_ai_generated' => true,
                    'already_passed'  => false,
                    'integrity_settings' => $integritySettings,
                    'questions'       => array_map(fn($q, $i) => ['id' => (string) $i, 'question' => $q['question']], $generatedQuestions, array_keys($generatedQuestions)),
                ]);
            }

            return response()->json([
                'id'              => $content->id,
                'title'           => $content->title,
                'type'            => 'quiz',
                'quiz_type'       => 'multiple_choice',
                'is_ai_generated' => true,
                'already_passed'  => false,
                'integrity_settings' => $integritySettings,
                'questions'       => $this->formatAiQuestionsForFrontend($generatedQuestions),
            ]);
        }

        // ── Manual essay quiz ──────────────────────────────────────────────
        if ($content->quiz_type === 'essay') {
            $isManualGrading = $content->grading_type === 'manual';

            // Check for pending admin review
            if ($isManualGrading) {
                $pendingAttempt = UserQuizAttempt::where('user_id', Auth::id())
                    ->where('content_id', $contentId)
                    ->where('user_course_id', $userCourse->id)
                    ->where('grading_status', 'pending_review')
                    ->latest()
                    ->first();

                if ($pendingAttempt) {
                    return response()->json([
                        'id'              => $content->id,
                        'title'           => $content->title,
                        'type'            => 'quiz',
                        'quiz_type'       => 'essay',
                        'grading_type'    => 'manual',
                        'already_passed'  => false,
                        'pending_review'  => true,
                        'integrity_settings' => $integritySettings,
                    ]);
                }

                $gradedAttempt = UserQuizAttempt::where('user_id', Auth::id())
                    ->where('content_id', $contentId)
                    ->where('user_course_id', $userCourse->id)
                    ->where('grading_status', 'graded')
                    ->latest()
                    ->first();

                if ($gradedAttempt) {
                    return response()->json([
                        'id'             => $content->id,
                        'title'          => $content->title,
                        'type'           => 'quiz',
                        'quiz_type'      => 'essay',
                        'grading_type'   => 'manual',
                        'already_passed' => (bool) $gradedAttempt->is_passed,
                        'pending_review' => false,
                        'integrity_settings' => $integritySettings,
                        'attempt'        => [
                            'score'           => $gradedAttempt->score,
                            'correct_answers' => $gradedAttempt->correct_answers,
                            'total_questions' => $gradedAttempt->total_questions,
                            'completed_at'    => $gradedAttempt->completed_at?->format('d M Y H:i'),
                            'violation_count' => $gradedAttempt->violation_count,
                            'is_auto_submitted' => $gradedAttempt->is_auto_submitted,
                            'admin_notes'     => $gradedAttempt->admin_notes,
                        ],
                        'quiz_details'   => $gradedAttempt->essay_answers ?? [],
                    ]);
                }

                return response()->json([
                    'id'             => $content->id,
                    'title'          => $content->title,
                    'type'           => 'quiz',
                    'quiz_type'      => 'essay',
                    'grading_type'   => 'manual',
                    'already_passed' => false,
                    'pending_review' => false,
                    'integrity_settings' => $integritySettings,
                    'questions'      => $content->questions->map(fn($q) => [
                        'id'       => $q->id,
                        'question' => $q->question,
                    ]),
                ]);
            }

            // AI-graded manual essay (grading_type = 'ai')
            $latestAttempt = UserQuizAttempt::where('user_id', Auth::id())
                ->where('content_id', $contentId)
                ->where('user_course_id', $userCourse->id)
                ->where('is_passed', true)
                ->latest()
                ->first();

            if ($latestAttempt) {
                return response()->json([
                    'id'             => $content->id,
                    'title'          => $content->title,
                    'type'           => 'quiz',
                    'quiz_type'      => 'essay',
                    'grading_type'   => 'ai',
                    'already_passed' => true,
                    'integrity_settings' => $integritySettings,
                    'attempt'        => [
                        'score'           => $latestAttempt->score,
                        'correct_answers' => $latestAttempt->correct_answers,
                        'total_questions' => $latestAttempt->total_questions,
                        'completed_at'    => $latestAttempt->completed_at->format('d M Y H:i'),
                        'violation_count' => $latestAttempt->violation_count,
                        'is_auto_submitted' => $latestAttempt->is_auto_submitted,
                    ],
                    'quiz_details'   => $latestAttempt->essay_answers ?? [],
                ]);
            }

            return response()->json([
                'id'             => $content->id,
                'title'          => $content->title,
                'type'           => 'quiz',
                'quiz_type'      => 'essay',
                'grading_type'   => 'ai',
                'already_passed' => false,
                'integrity_settings' => $integritySettings,
                'questions'      => $content->questions->map(fn($q) => [
                    'id'       => $q->id,
                    'question' => $q->question,
                ]),
            ]);
        }

        // ── Manual multiple-choice quiz ────────────────────────────────────
        $latestAttempt = UserQuizAttempt::where('user_id', Auth::id())
            ->where('content_id', $contentId)
            ->where('user_course_id', $userCourse->id)
            ->where('is_passed', true)
            ->latest()
            ->first();

        if ($latestAttempt) {
            $quizDetails = [];
            foreach ($content->questions as $question) {
                $userAnswer = UserQuizAnswer::where('quiz_attempt_id', $latestAttempt->id)
                    ->where('question_id', $question->id)
                    ->with('selectedOption')
                    ->first();

                $quizDetails[] = [
                    'question' => $question->question,
                    'options'  => $question->options->map(function ($option) use ($userAnswer) {
                        return [
                            'id'          => $option->id,
                            'option_text' => $option->option_text,
                            'is_correct'  => $option->is_correct,
                            'is_selected' => $userAnswer && $userAnswer->selected_option_id == $option->id,
                        ];
                    }),
                    'user_is_correct' => $userAnswer ? $userAnswer->is_correct : false,
                ];
            }

            return response()->json([
                'id'             => $content->id,
                'title'          => $content->title,
                'type'           => $content->type,
                'quiz_type'      => 'multiple_choice',
                'already_passed' => true,
                'integrity_settings' => $integritySettings,
                'attempt'        => [
                    'score'           => $latestAttempt->score,
                    'correct_answers' => $latestAttempt->correct_answers,
                    'total_questions' => $latestAttempt->total_questions,
                    'completed_at'    => $latestAttempt->completed_at->format('d M Y H:i'),
                    'violation_count' => $latestAttempt->violation_count,
                    'is_auto_submitted' => $latestAttempt->is_auto_submitted,
                ],
                'quiz_details' => $quizDetails,
            ]);
        }

        return response()->json([
            'id'             => $content->id,
            'title'          => $content->title,
            'type'           => $content->type,
            'quiz_type'      => 'multiple_choice',
            'already_passed' => false,
            'integrity_settings' => $integritySettings,
            'questions'      => $content->questions->map(function ($question) {
                return [
                    'id'       => $question->id,
                    'question' => $question->question,
                    'options'  => $question->options->map(function ($option) {
                        return [
                            'id'          => $option->id,
                            'option_text' => $option->option_text,
                        ];
                    }),
                ];
            }),
        ]);
    }

    /**
     * Build the source text for AI question generation.
     * Uses all text-type contents in the same module; falls back to the quiz's own content field.
     */
    private function resolveAiSourceText(Content $content): string
    {
        $content->loadMissing('module.contents');

        $moduleText = $content->module->contents
            ->where('type', 'text')
            ->map(fn($c) => trim(strip_tags($c->content)))
            ->filter(fn($t) => strlen($t) > 0)
            ->implode("\n\n");

        return strlen(trim($moduleText)) >= 30 ? $moduleText : $content->content;
    }

    private function formatAiQuestionsForFrontend(array $generatedQuestions): array
    {
        $out = [];
        foreach ($generatedQuestions as $qi => $gq) {
            $options = [];
            foreach ($gq['options'] as $oi => $opt) {
                $options[] = ['id' => (string) $oi, 'option_text' => $opt];
            }
            $out[] = ['id' => (string) $qi, 'question' => $gq['question'], 'options' => $options];
        }
        return $out;
    }

    private function buildAiReview(UserQuizAttempt $attempt): array
    {
        $generatedQuestions = $attempt->generated_questions ?? [];
        $aiAnswers          = collect($attempt->ai_answers ?? []);
        $details            = [];

        foreach ($generatedQuestions as $qi => $gq) {
            $userAnswer  = $aiAnswers->firstWhere('question_index', $qi);
            $selectedIdx = $userAnswer ? (int) $userAnswer['selected_option_index'] : null;

            $options = [];
            foreach ($gq['options'] as $oi => $opt) {
                $options[] = [
                    'id'          => (string) $oi,
                    'option_text' => $opt,
                    'is_correct'  => $oi === (int) $gq['correct_index'],
                    'is_selected' => $oi === $selectedIdx,
                ];
            }

            $details[] = [
                'question'       => $gq['question'],
                'options'        => $options,
                'user_is_correct' => $userAnswer ? (bool) $userAnswer['is_correct'] : false,
            ];
        }

        return $details;
    }

    public function startQuizAttempt($kursusId, $contentId)
    {
        /** @var User $user */
        $user = Auth::user();
        $userCourse = $user->getEnrolledCourse($kursusId);

        if (!$userCourse) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $content = Content::with('questions')->findOrFail($contentId);

        if ($content->type !== 'quiz') {
            return response()->json(['error' => 'Not a quiz'], 400);
        }

        $integritySettings = [
            'enabled'            => (bool) $content->integrity_mode_enabled,
            'require_fullscreen' => (bool) $content->require_fullscreen,
            'max_violations'     => (int) ($content->max_violations ?? 3),
        ];

        if ($content->is_ai_generated) {
            // Reuse the pending attempt created in getContent()
            $attempt = UserQuizAttempt::where('user_id', Auth::id())
                ->where('content_id', $contentId)
                ->where('user_course_id', $userCourse->id)
                ->whereNull('completed_at')
                ->whereNotNull('generated_questions')
                ->latest()
                ->first();

            if (!$attempt) {
                // Edge case: getContent() wasn't called first — generate now
                try {
                    $generatedQuestions = app(GeminiService::class)->generateQuizQuestions(
                        $this->resolveAiSourceText($content),
                        $content->ai_question_count ?? 5
                    );
                } catch (\RuntimeException $e) {
                    return response()->json(['error' => $e->getMessage()], 500);
                }

                $attempt = UserQuizAttempt::create([
                    'user_id'             => Auth::id(),
                    'content_id'          => $contentId,
                    'user_course_id'      => $userCourse->id,
                    'total_questions'     => count($generatedQuestions),
                    'started_at'          => now(),
                    'violation_count'     => 0,
                    'is_auto_submitted'   => false,
                    'generated_questions' => $generatedQuestions,
                ]);
            }

            return response()->json([
                'success'            => true,
                'attempt_id'         => $attempt->id,
                'violation_count'    => $attempt->violation_count,
                'integrity_settings' => $integritySettings,
            ]);
        }

        // Manual quiz
        $attempt = UserQuizAttempt::where('user_id', Auth::id())
            ->where('content_id', $contentId)
            ->where('user_course_id', $userCourse->id)
            ->whereNull('completed_at')
            ->latest()
            ->first();

        if (!$attempt) {
            $attempt = UserQuizAttempt::create([
                'user_id'         => Auth::id(),
                'content_id'      => $contentId,
                'user_course_id'  => $userCourse->id,
                'total_questions' => $content->questions->count(),
                'started_at'      => now(),
                'violation_count' => 0,
                'is_auto_submitted' => false,
            ]);
        }

        return response()->json([
            'success'            => true,
            'attempt_id'         => $attempt->id,
            'violation_count'    => $attempt->violation_count,
            'integrity_settings' => $integritySettings,
        ]);
    }

    public function logIntegrityViolation($kursusId, $contentId, Request $request)
    {
        $request->validate([
            'attempt_id' => 'required|exists:user_quiz_attempts,id',
            'event_type' => 'required|in:tab_switch,window_blur,fullscreen_exit',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $userCourse = $user->getEnrolledCourse($kursusId);

        if (!$userCourse) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $content = Content::with('questions')->findOrFail($contentId);
        $attempt = UserQuizAttempt::where('id', $request->attempt_id)
            ->where('user_id', Auth::id())
            ->where('content_id', $contentId)
            ->where('user_course_id', $userCourse->id)
            ->firstOrFail();

        if ($attempt->completed_at) {
            return response()->json([
                'success' => false,
                'message' => 'Attempt already completed',
                'already_completed' => true,
            ], 422);
        }

        $attempt->increment('violation_count');
        $attempt->refresh();

        $maxViolations    = (int) ($content->max_violations ?? 3);
        $shouldAutoSubmit = $content->integrity_mode_enabled && $attempt->violation_count >= $maxViolations;

        UserQuizIntegrityEvent::create([
            'user_id'              => Auth::id(),
            'user_quiz_attempt_id' => $attempt->id,
            'user_course_id'       => $userCourse->id,
            'content_id'           => $contentId,
            'event_type'           => $request->event_type,
            'violation_count'      => $attempt->violation_count,
            'is_auto_submitted'    => $shouldAutoSubmit,
            'event_at'             => now(),
        ]);

        if (!$shouldAutoSubmit) {
            return response()->json([
                'success'          => true,
                'violation_count'  => $attempt->violation_count,
                'max_violations'   => $maxViolations,
                'is_auto_submitted' => false,
            ]);
        }

        // Auto-submit scoring
        if ($content->is_ai_generated) {
            $totalQuestions = count($attempt->generated_questions ?? []);
            $correctAnswers = 0;
            $score          = 0;
            $isPassed       = false;

            $attempt->update([
                'correct_answers'    => 0,
                'score'              => 0,
                'is_passed'          => false,
                'is_auto_submitted'  => true,
                'auto_submit_reason' => 'max_violations_reached',
                'ai_answers'         => [],
                'completed_at'       => now(),
            ]);
        } else {
            $totalQuestions = $content->questions->count();
            $correctAnswers = $attempt->answers()->where('is_correct', true)->count();
            $score          = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
            $isPassed       = $score >= 70;

            $attempt->update([
                'correct_answers'    => $correctAnswers,
                'score'              => round($score),
                'is_passed'          => $isPassed,
                'is_auto_submitted'  => true,
                'auto_submit_reason' => 'max_violations_reached',
                'completed_at'       => now(),
            ]);
        }

        if ($isPassed) {
            $this->markContentProgressComplete($contentId, $userCourse);
        }

        return response()->json([
            'success' => true,
            'is_auto_submitted' => true,
            'message' => 'Kuis otomatis dikirim karena melebihi batas pelanggaran integritas.',
            'is_passed' => $isPassed,
            'score' => round($score),
            'correct_answers' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'progress' => $userCourse->fresh()->progress_percentage,
            'auto_submit_reason' => 'max_violations_reached',
        ]);
    }

    public function markContentComplete($kursusId, $contentId)
    {
        /** @var User $user */
        $user = Auth::user();
        $userCourse = $user->getEnrolledCourse($kursusId);

        if (!$userCourse) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $progress = UserContentProgress::where('user_id', Auth::id())
            ->where('content_id', $contentId)
            ->where('user_course_id', $userCourse->id)
            ->first();

        if (!$progress) {
            $progress = UserContentProgress::create([
                'user_id' => Auth::id(),
                'content_id' => $contentId,
                'user_course_id' => $userCourse->id,
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        } else if (!$progress->is_completed) {
            $progress->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);
        }

        $this->updateCourseProgress($userCourse);

        return response()->json([
            'success' => true,
            'progress' => $userCourse->fresh()->progress_percentage,
        ]);
    }

    public function submitQuiz($kursusId, $contentId, Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $userCourse = $user->getEnrolledCourse($kursusId);

        if (!$userCourse) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $content = Content::with('questions.options')->findOrFail($contentId);

        if ($content->type !== 'quiz') {
            return response()->json(['error' => 'Not a quiz'], 400);
        }

        $answers = $request->input('answers', []);

        DB::beginTransaction();
        try {
            $attempt = null;
            if ($request->filled('attempt_id')) {
                $attempt = UserQuizAttempt::where('id', $request->attempt_id)
                    ->where('user_id', Auth::id())
                    ->where('content_id', $contentId)
                    ->where('user_course_id', $userCourse->id)
                    ->first();
            }

            if ($attempt && $attempt->completed_at) {
                return response()->json(['error' => 'Attempt already completed'], 422);
            }

            // ── Essay quiz scoring (AI or manual) ─────────────────────────
            if ($content->quiz_type === 'essay') {
                // Collect questions
                if ($content->is_ai_generated) {
                    if (!$attempt || !$attempt->generated_questions) {
                        DB::rollBack();
                        return response()->json(['error' => 'Attempt esai AI tidak valid'], 422);
                    }
                    $questions = array_values(array_map(fn($q) => $q['question'], $attempt->generated_questions));
                } else {
                    if (!$attempt) {
                        $attempt = UserQuizAttempt::create([
                            'user_id'           => Auth::id(),
                            'content_id'        => $contentId,
                            'user_course_id'    => $userCourse->id,
                            'total_questions'   => $content->questions->count(),
                            'started_at'        => now(),
                            'violation_count'   => 0,
                            'is_auto_submitted' => false,
                        ]);
                    }
                    $questions = $content->questions->map(fn($q) => $q->question)->values()->all();
                }

                // Build Q&A pairs
                $qas = [];
                foreach ($questions as $i => $questionText) {
                    $qas[] = [
                        'question' => $questionText,
                        'answer'   => trim($answers['essay_' . $i] ?? ''),
                    ];
                }

                // ── Manual grading: save and notify user to wait ───────────
                if (!$content->is_ai_generated && $content->grading_type === 'manual') {
                    $essayAnswers = [];
                    foreach ($qas as $i => $qa) {
                        $essayAnswers['essay_' . $i] = [
                            'question' => $qa['question'],
                            'answer'   => $qa['answer'],
                        ];
                    }

                    $attempt->update([
                        'total_questions'   => count($qas),
                        'essay_answers'     => $essayAnswers,
                        'grading_status'    => 'pending_review',
                        'is_auto_submitted' => false,
                        'completed_at'      => now(),
                    ]);

                    DB::commit();

                    return response()->json([
                        'success'        => true,
                        'pending_review' => true,
                        'message'        => 'Jawaban kamu telah dikirim. Tunggu penilaian admin dalam 24 jam.',
                    ]);
                }

                // ── AI grading ─────────────────────────────────────────────
                try {
                    $grades = app(GeminiService::class)->gradeEssayAnswers(
                        $this->resolveAiSourceText($content),
                        $qas
                    );
                } catch (\RuntimeException $e) {
                    DB::rollBack();
                    return response()->json(['error' => $e->getMessage()], 500);
                }

                $essayAnswers = [];
                $totalScore   = 0;
                foreach ($qas as $i => $qa) {
                    $grade          = $grades[$i] ?? ['score' => 0, 'feedback' => ''];
                    $totalScore    += $grade['score'];
                    $essayAnswers[] = [
                        'question' => $qa['question'],
                        'answer'   => $qa['answer'],
                        'score'    => $grade['score'],
                        'feedback' => $grade['feedback'],
                    ];
                }

                $totalQuestions = count($qas);
                $avgScore       = $totalQuestions > 0 ? $totalScore / $totalQuestions : 0;
                $isPassed       = $avgScore >= 70;
                $passed         = count(array_filter($grades, fn($g) => $g['score'] >= 70));

                $attempt->update([
                    'correct_answers'    => $passed,
                    'score'              => round($avgScore),
                    'is_passed'          => $isPassed,
                    'is_auto_submitted'  => false,
                    'auto_submit_reason' => null,
                    'essay_answers'      => $essayAnswers,
                    'completed_at'       => now(),
                ]);

                if ($isPassed) {
                    $this->markContentProgressComplete($contentId, $userCourse);
                }

                DB::commit();

                return response()->json([
                    'success'         => true,
                    'is_passed'       => $isPassed,
                    'score'           => round($avgScore),
                    'correct_answers' => $passed,
                    'total_questions' => $totalQuestions,
                    'progress'        => $userCourse->fresh()->progress_percentage,
                    'essay_answers'   => $essayAnswers,
                    'is_auto_submitted' => false,
                    'violation_count' => $attempt->violation_count,
                ]);
            }

            // ── AI quiz scoring ────────────────────────────────────────────
            if ($content->is_ai_generated) {
                if (!$attempt || !$attempt->generated_questions) {
                    return response()->json(['error' => 'Attempt AI tidak valid'], 422);
                }

                $generatedQuestions = $attempt->generated_questions;
                $correctAnswers     = 0;
                $aiAnswers          = [];

                foreach ($generatedQuestions as $qi => $gq) {
                    $raw = $answers['question_' . $qi] ?? null;
                    if ($raw !== null) {
                        $selectedIdx = (int) $raw;
                        $isCorrect   = $selectedIdx === (int) $gq['correct_index'];
                        if ($isCorrect) $correctAnswers++;
                        $aiAnswers[] = [
                            'question_index'       => $qi,
                            'selected_option_index' => $selectedIdx,
                            'is_correct'           => $isCorrect,
                        ];
                    }
                }

                $totalQuestions = count($generatedQuestions);
                $score    = $totalQuestions > 0 ? ($correctAnswers / $totalQuestions) * 100 : 0;
                $isPassed = $score >= 70;

                $attempt->update([
                    'correct_answers'    => $correctAnswers,
                    'score'              => round($score),
                    'is_passed'          => $isPassed,
                    'is_auto_submitted'  => false,
                    'auto_submit_reason' => null,
                    'ai_answers'         => $aiAnswers,
                    'completed_at'       => now(),
                ]);

                if ($isPassed) {
                    $this->markContentProgressComplete($contentId, $userCourse);
                }

                DB::commit();

                return response()->json([
                    'success'          => true,
                    'is_passed'        => $isPassed,
                    'score'            => round($score),
                    'correct_answers'  => $correctAnswers,
                    'total_questions'  => $totalQuestions,
                    'progress'         => $userCourse->fresh()->progress_percentage,
                    'is_auto_submitted' => false,
                    'violation_count'  => $attempt->violation_count,
                ]);
            }

            // ── Manual quiz scoring ────────────────────────────────────────
            if (!$attempt) {
                $attempt = UserQuizAttempt::create([
                    'user_id'         => Auth::id(),
                    'content_id'      => $contentId,
                    'user_course_id'  => $userCourse->id,
                    'total_questions' => $content->questions->count(),
                    'started_at'      => now(),
                    'violation_count' => 0,
                    'is_auto_submitted' => false,
                ]);
            }

            UserQuizAnswer::where('quiz_attempt_id', $attempt->id)->delete();

            $correctAnswers = 0;

            foreach ($content->questions as $question) {
                $selectedOptionId = $answers['question_' . $question->id] ?? null;

                if ($selectedOptionId) {
                    $selectedOption = $question->options->where('id', $selectedOptionId)->first();
                    $isCorrect = $selectedOption && $selectedOption->is_correct;

                    if ($isCorrect) $correctAnswers++;

                    UserQuizAnswer::create([
                        'user_id'            => Auth::id(),
                        'quiz_attempt_id'    => $attempt->id,
                        'question_id'        => $question->id,
                        'selected_option_id' => $selectedOptionId,
                        'is_correct'         => $isCorrect,
                    ]);
                }
            }

            $score    = ($correctAnswers / $content->questions->count()) * 100;
            $isPassed = $score >= 70;

            $attempt->update([
                'correct_answers'    => $correctAnswers,
                'score'              => round($score),
                'is_passed'          => $isPassed,
                'is_auto_submitted'  => false,
                'auto_submit_reason' => null,
                'completed_at'       => now(),
            ]);

            if ($isPassed) {
                $this->markContentProgressComplete($contentId, $userCourse);
            }

            DB::commit();

            return response()->json([
                'success'          => true,
                'is_passed'        => $isPassed,
                'score'            => round($score),
                'correct_answers'  => $correctAnswers,
                'total_questions'  => $content->questions->count(),
                'progress'         => $userCourse->fresh()->progress_percentage,
                'is_auto_submitted' => false,
                'violation_count'  => $attempt->violation_count,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to submit quiz'], 500);
        }
    }

    private function markContentProgressComplete(string $contentId, UserCourse $userCourse): void
    {
        $progress = UserContentProgress::where('user_id', Auth::id())
            ->where('content_id', $contentId)
            ->where('user_course_id', $userCourse->id)
            ->first();

        if (!$progress) {
            UserContentProgress::create([
                'user_id'        => Auth::id(),
                'content_id'     => $contentId,
                'user_course_id' => $userCourse->id,
                'is_completed'   => true,
                'completed_at'   => now(),
            ]);
        } elseif (!$progress->is_completed) {
            $progress->update(['is_completed' => true, 'completed_at' => now()]);
        }

        $this->updateCourseProgress($userCourse);
    }

    private function updateCourseProgress(UserCourse $userCourse)
    {
        $kursus = $userCourse->kursus()->with('modules.contents')->first();

        $totalContents = 0;
        foreach ($kursus->modules as $module) {
            $totalContents += $module->contents->count();
        }

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

    public function request(Request $request)
    {
        $query = Kursus::where('status', 'aktif');

        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('short_description', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $total = $query->count();

        if ($request->has('start') && $request->has('length')) {
            $query->skip($request->input('start'))->take($request->input('length'));
        }

        $data = $query->get();

        return response()->json([
            'draw' => $request->input('draw', 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ]);
    }
}
