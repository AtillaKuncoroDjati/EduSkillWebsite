<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

use App\Models\Content;
use App\Models\Module;
use App\Models\QuizOption;
use App\Models\QuizQuestion;

class ContentController extends Controller
{
    public function quiz(Content $content)
    {
        $content->load('questions.options');
        $data = $content->toArray();
        $data['grading_type'] = $content->grading_type ?? 'ai';
        return response()->json($data);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('image')) {
            $folder = 'content-images/' . date('Y/m');
            $path = public_path('uploads/materi/' . $folder);

            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }

            $file = $request->file('image');
            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $file->move($path, $filename);

            return response()->json([
                'success' => true,
                'url'     => asset('uploads/materi/' . $folder . '/' . $filename),
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Failed to upload image'], 400);
    }

    // ── PDF helpers ────────────────────────────────────────────────────────────

    private function storePdf(Request $request): ?string
    {
        if (!$request->hasFile('pdf_file')) {
            return null;
        }

        $dir = public_path('uploads/content-pdfs');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $file     = $request->file('pdf_file');
        $filename = Str::uuid()->toString() . '.pdf';
        $file->move($dir, $filename);

        return 'uploads/content-pdfs/' . $filename;
    }

    private function deletePdf(?string $pdfPath): void
    {
        if (!$pdfPath) return;
        $full = public_path($pdfPath);
        if (File::exists($full)) {
            File::delete($full);
        }
    }

    // ── Image helpers ──────────────────────────────────────────────────────────

    private function extractImagePaths($content)
    {
        $images = [];
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/', $content, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $url) {
                if (strpos($url, 'uploads/materi/') !== false) {
                    if (preg_match('/uploads\/materi\/(.+)$/', $url, $pathMatch)) {
                        $images[] = public_path('uploads/materi/' . $pathMatch[1]);
                    }
                }
            }
        }

        return $images;
    }

    private function deleteUnusedImages($oldContent, $newContent)
    {
        $oldImages = $this->extractImagePaths($oldContent);
        $newImages = $this->extractImagePaths($newContent);

        foreach (array_diff($oldImages, $newImages) as $imagePath) {
            if (File::exists($imagePath)) {
                File::delete($imagePath);
                $this->deleteEmptyDirectory(dirname($imagePath));
            }
        }
    }

    private function deleteAllImagesFromContent($content)
    {
        foreach ($this->extractImagePaths($content) as $imagePath) {
            if (File::exists($imagePath)) {
                File::delete($imagePath);
                $this->deleteEmptyDirectory(dirname($imagePath));
            }
        }
    }

    private function deleteEmptyDirectory($directory)
    {
        $baseDir = public_path('uploads/materi');

        while ($directory != $baseDir && File::isDirectory($directory)) {
            if (empty(File::files($directory)) && empty(File::directories($directory))) {
                File::deleteDirectory($directory);
                $directory = dirname($directory);
            } else {
                break;
            }
        }
    }

    // ── Store ──────────────────────────────────────────────────────────────────

    public function store(Request $request, Module $module)
    {
        $type          = $request->input('type');
        $quizType      = $request->input('quiz_type', 'multiple_choice');
        $isAiGenerated = $type === 'quiz' && $request->boolean('is_ai_generated');

        $request->validate([
            'title'                  => 'nullable|string|max:255',
            'content'                => 'nullable|string',
            'type'                   => 'required|in:text,quiz',
            'quiz_type'              => 'nullable|in:multiple_choice,essay',
            'integrity_mode_enabled' => 'nullable|boolean',
            'require_fullscreen'     => 'nullable|boolean',
            'max_violations'         => 'nullable|integer|min:1|max:20',
            'ai_question_count'      => 'nullable|integer|min:1|max:20',
            'pdf_file'               => 'nullable|file|mimes:pdf|max:20480',
        ]);

        // For quiz type, content is the description used by AI — still required
        if ($type === 'quiz') {
            $request->validate(['content' => 'required|string']);
        }

        if ($type === 'quiz' && !$isAiGenerated && $quizType === 'multiple_choice') {
            $request->validate([
                'questions'           => 'required|array|min:1',
                'questions.*.text'    => 'required|string',
                'questions.*.options' => 'required|array|min:2',
            ], [
                'questions.required'           => 'Quiz harus memiliki minimal 1 pertanyaan',
                'questions.*.text.required'    => 'Pertanyaan tidak boleh kosong',
                'questions.*.options.required' => 'Setiap pertanyaan harus memiliki minimal 2 jawaban',
            ]);
        }

        if ($type === 'quiz' && !$isAiGenerated && $quizType === 'essay') {
            $request->validate([
                'questions'        => 'required|array|min:1',
                'questions.*.text' => 'required|string',
            ], [
                'questions.required'        => 'Esai harus memiliki minimal 1 pertanyaan',
                'questions.*.text.required' => 'Pertanyaan tidak boleh kosong',
            ]);
        }

        $pdfPath = $this->storePdf($request);
        $order   = Content::where('module_id', $module->id)->max('order') + 1;

        $content = Content::create([
            'module_id'              => $module->id,
            'title'                  => $request->input('title'),
            'type'                   => $type,
            'content'                => $request->input('content'),
            'pdf_path'               => $pdfPath,
            'quiz_type'              => $type === 'quiz' ? $quizType : 'multiple_choice',
            'grading_type'           => ($type === 'quiz' && $quizType === 'essay') ? $request->input('grading_type', 'ai') : 'ai',
            'integrity_mode_enabled' => $type === 'quiz' ? (bool) $request->integrity_mode_enabled : false,
            'require_fullscreen'     => $type === 'quiz' ? (bool) $request->require_fullscreen : false,
            'max_violations'         => $type === 'quiz' ? (int) ($request->max_violations ?? 3) : 3,
            'is_ai_generated'        => $isAiGenerated,
            'ai_question_count'      => $isAiGenerated ? (int) ($request->ai_question_count ?? 5) : 5,
            'order'                  => $order,
        ]);

        if ($type === 'quiz' && !$isAiGenerated) {
            foreach ($request->input('questions', []) as $qIndex => $question) {
                $q = QuizQuestion::create([
                    'content_id' => $content->id,
                    'question'   => $question['text'],
                    'order'      => $qIndex,
                ]);

                if ($quizType === 'multiple_choice') {
                    foreach ($question['options'] as $opt) {
                        QuizOption::create([
                            'question_id' => $q->id,
                            'option_text' => $opt['text'],
                            'is_correct'  => isset($opt['is_correct']) ? 1 : 0,
                        ]);
                    }
                }
            }
        }

        session()->flash('success_message', 'Materi berhasil ditambahkan.');
        return back();
    }

    // ── Update ─────────────────────────────────────────────────────────────────

    public function update(Request $request)
    {
        $type          = $request->input('type');
        $quizType      = $request->input('quiz_type', 'multiple_choice');
        $isAiGenerated = $type === 'quiz' && $request->boolean('is_ai_generated');

        $request->validate([
            'id'                     => 'required|exists:contents,id',
            'title'                  => 'nullable|string|max:255',
            'content'                => 'nullable|string',
            'type'                   => 'required|in:text,quiz',
            'quiz_type'              => 'nullable|in:multiple_choice,essay',
            'integrity_mode_enabled' => 'nullable|boolean',
            'require_fullscreen'     => 'nullable|boolean',
            'max_violations'         => 'nullable|integer|min:1|max:20',
            'ai_question_count'      => 'nullable|integer|min:1|max:20',
            'pdf_file'               => 'nullable|file|mimes:pdf|max:20480',
        ]);

        if ($type === 'quiz') {
            $request->validate(['content' => 'required|string']);
        }

        if ($type === 'quiz' && !$isAiGenerated && $quizType === 'multiple_choice') {
            $request->validate([
                'questions'           => 'required|array|min:1',
                'questions.*.text'    => 'required|string',
                'questions.*.options' => 'required|array|min:2',
            ], [
                'questions.required'           => 'Quiz harus memiliki minimal 1 pertanyaan',
                'questions.*.text.required'    => 'Pertanyaan tidak boleh kosong',
                'questions.*.options.required' => 'Setiap pertanyaan harus memiliki minimal 2 jawaban',
            ]);
        }

        if ($type === 'quiz' && !$isAiGenerated && $quizType === 'essay') {
            $request->validate([
                'questions'        => 'required|array|min:1',
                'questions.*.text' => 'required|string',
            ], [
                'questions.required'        => 'Esai harus memiliki minimal 1 pertanyaan',
                'questions.*.text.required' => 'Pertanyaan tidak boleh kosong',
            ]);
        }

        $content    = Content::findOrFail($request->input('id'));
        $oldContent = $content->content;
        $oldType    = $content->type;

        // Handle PDF: replace if new file uploaded, delete if remove flag set
        $pdfPath = $content->pdf_path;
        if ($request->hasFile('pdf_file')) {
            $this->deletePdf($content->pdf_path);
            $pdfPath = $this->storePdf($request);
        } elseif ($request->boolean('remove_pdf')) {
            $this->deletePdf($content->pdf_path);
            $pdfPath = null;
        }

        $content->update([
            'title'                  => $request->input('title'),
            'type'                   => $type,
            'content'                => $request->input('content'),
            'pdf_path'               => $pdfPath,
            'quiz_type'              => $type === 'quiz' ? $quizType : 'multiple_choice',
            'grading_type'           => ($type === 'quiz' && $quizType === 'essay') ? $request->input('grading_type', 'ai') : 'ai',
            'integrity_mode_enabled' => $type === 'quiz' ? (bool) $request->integrity_mode_enabled : false,
            'require_fullscreen'     => $type === 'quiz' ? (bool) $request->require_fullscreen : false,
            'max_violations'         => $type === 'quiz' ? (int) ($request->max_violations ?? 3) : 3,
            'is_ai_generated'        => $isAiGenerated,
            'ai_question_count'      => $isAiGenerated ? (int) ($request->ai_question_count ?? 5) : 5,
        ]);

        if ($oldContent) {
            $this->deleteUnusedImages($oldContent, $request->input('content') ?? '');
        }

        if ($type === 'text') {
            if ($oldType === 'quiz') {
                foreach ($content->questions as $q) {
                    $q->options()->delete();
                    $q->delete();
                }
            }
            session()->flash('success_message', 'Materi telah diperbarui.');
            return back();
        }

        foreach ($content->questions as $q) {
            $q->options()->delete();
            $q->delete();
        }

        if ($isAiGenerated) {
            session()->flash('success_message', 'Materi & Kuis AI telah diperbarui.');
            return back();
        }

        foreach ($request->questions as $qIndex => $question) {
            $newQ = QuizQuestion::create([
                'content_id' => $content->id,
                'question'   => $question['text'],
                'order'      => $qIndex,
            ]);

            if ($quizType === 'multiple_choice') {
                foreach ($question['options'] as $opt) {
                    QuizOption::create([
                        'question_id' => $newQ->id,
                        'option_text' => $opt['text'],
                        'is_correct'  => isset($opt['is_correct']) ? 1 : 0,
                    ]);
                }
            }
        }

        session()->flash('success_message', 'Materi & Soal Kuis telah diperbarui.');
        return back();
    }

    public function updateOrder(Request $request)
    {
        foreach ($request->orders as $order => $id) {
            Content::where('id', $id)->update(['order' => $order]);
        }

        return response()->json(['status' => 'ok']);
    }

    public function delete(Request $request)
    {
        $content = Content::find($request->id);

        if ($content) {
            if ($content->content) {
                $this->deleteAllImagesFromContent($content->content);
            }

            $this->deletePdf($content->pdf_path);

            if ($content->type === 'quiz') {
                foreach ($content->questions as $q) {
                    $q->options()->delete();
                    $q->delete();
                }
            }

            $content->delete();
        }

        session()->flash('success_message', 'Materi telah dihapus.');
        return back();
    }
}
