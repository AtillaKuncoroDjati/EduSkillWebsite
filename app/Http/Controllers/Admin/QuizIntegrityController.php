<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserQuizAttempt;
use Illuminate\Http\Request;

class QuizIntegrityController extends Controller
{
    public function index()
    {
        return view('admin.kursus.integrity');
    }

    public function request(Request $request)
    {
        // Tampilkan attempt yang punya indikasi masalah integritas APAPUN:
        // pelanggaran perilaku, auto-submit, ATAU anomali keystroke (suspect/caution).
        $query = UserQuizAttempt::with(['user', 'content', 'content.module.kursus'])
            ->where(function ($q) {
                $q->where('violation_count', '>', 0)
                    ->orWhere('is_auto_submitted', true)
                    ->orWhereIn('keystroke_flag', ['suspect', 'caution']);
            });

        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                })->orWhereHas('content', function ($sub) use ($search) {
                    $sub->where('title', 'like', '%' . $search . '%');
                });
            });
        }

        if ($request->has('auto_submitted_filter') && in_array($request->auto_submitted_filter, ['yes', 'no'])) {
            $query->where('is_auto_submitted', $request->auto_submitted_filter === 'yes');
        }

        // Filter berdasarkan tingkat risiko integritas
        $riskFilter = $request->input('risk_filter');
        if (in_array($riskFilter, ['high', 'medium', 'low'], true)) {
            if ($riskFilter === 'high') {
                $query->where('integrity_risk_score', '>=', 70);
            } elseif ($riskFilter === 'medium') {
                $query->where('integrity_risk_score', '>=', 40)
                    ->where('integrity_risk_score', '<', 70);
            } else {
                $query->where(function ($q) {
                    $q->where('integrity_risk_score', '<', 40)
                        ->orWhereNull('integrity_risk_score');
                });
            }
        }

        $total = $query->count();

        if ($request->has('start') && $request->has('length')) {
            $query->skip($request->input('start'))->take($request->input('length'));
        }

        $data = $query->orderBy('updated_at', 'desc')->get();

        return response()->json([
            'draw' => $request->input('draw', 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ]);
    }

    public function detail($id)
    {
        $attempt = UserQuizAttempt::with([
            'user',
            'content.module.kursus',
            'integrityEvents' => function ($q) {
                $q->orderBy('event_at', 'asc');
            }
        ])->findOrFail($id);

        return response()->json($attempt);
    }
}
