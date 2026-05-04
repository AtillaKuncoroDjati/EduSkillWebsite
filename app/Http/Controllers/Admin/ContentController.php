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
        return response()->json($content->append([]));
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

            $url = asset('uploads/materi/' . $folder . '/' . $filename);

            return response()->json([
                'success' => true,
                'url' => $url
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to upload image'
        ], 400);
    }

    /**
     * Extract image file paths from HTML content
     */
    private function extractImagePaths($content)
    {
        $images = [];

        // Regex untuk menangkap src dari tag img
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/', $content, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $url) {
                // Filter hanya gambar yang ada di folder uploads/materi/
                if (strpos($url, 'uploads/materi/') !== false) {
                    // Extract path relatif dari URL
                    if (preg_match('/uploads\/materi\/(.+)$/', $url, $pathMatch)) {
                        $fullPath = public_path('uploads/materi/' . $pathMatch[1]);
                        $images[] = $fullPath;
                    }
                }
            }
        }

        return $images;
    }

    /**
     * Delete images that are not in the new content
     */
    private function deleteUnusedImages($oldContent, $newContent)
    {
        $oldImages = $this->extractImagePaths($oldContent);
        $newImages = $this->extractImagePaths($newContent);

        // Cari gambar yang ada di content lama tapi tidak ada di content baru
        $imagesToDelete = array_diff($oldImages, $newImages);

        foreach ($imagesToDelete as $imagePath) {
            // Hapus file jika ada
            if (File::exists($imagePath)) {
                File::delete($imagePath);

                // Log untuk debugging
                \Log::info('Deleted image: ' . $imagePath);

                // Coba hapus folder kosong
                $directory = dirname($imagePath);
                $this->deleteEmptyDirectory($directory);
            }
        }
    }

    /**
     * Delete all images from content
     */
    private function deleteAllImagesFromContent($content)
    {
        $images = $this->extractImagePaths($content);

        foreach ($images as $imagePath) {
            if (File::exists($imagePath)) {
                File::delete($imagePath);

                // Log untuk debugging
                \Log::info('Deleted all images: ' . $imagePath);

                // Coba hapus folder kosong
                $directory = dirname($imagePath);
                $this->deleteEmptyDirectory($directory);
            }
        }
    }

    /**
     * Delete directory if empty
     */
    private function deleteEmptyDirectory($directory)
    {
        // Jangan hapus direktori utama
        $baseDir = public_path('uploads/materi');

        while ($directory != $baseDir && File::isDirectory($directory)) {
            $files = File::files($directory);
            $dirs = File::directories($directory);

            // Jika kosong, hapus
            if (empty($files) && empty($dirs)) {
                File::deleteDirectory($directory);
                \Log::info('Deleted empty directory: ' . $directory);

                // Cek parent directory
                $directory = dirname($directory);
            } else {
                break;
            }
        }
    }

    public function store(Request $request, Module $module)
    {
        $isAiGenerated = $request->type === 'quiz' && $request->boolean('is_ai_generated');

        $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required',
            'type' => 'required|in:text,quiz',
            'integrity_mode_enabled' => 'nullable|boolean',
            'require_fullscreen' => 'nullable|boolean',
            'max_violations' => 'nullable|integer|min:1|max:20',
            'ai_question_count' => 'nullable|integer|min:1|max:20',
        ]);

        // Manual quiz requires questions; AI quiz does not
        if ($request->type === 'quiz' && !$isAiGenerated) {
            $request->validate([
                'questions' => 'required|array|min:1',
                'questions.*.text' => 'required|string',
                'questions.*.options' => 'required|array|min:2',
            ], [
                'questions.required' => 'Quiz harus memiliki minimal 1 pertanyaan',
                'questions.*.text.required' => 'Pertanyaan tidak boleh kosong',
                'questions.*.options.required' => 'Setiap pertanyaan harus memiliki minimal 2 jawaban',
            ]);
        }

        $order = Content::where('module_id', $module->id)->max('order') + 1;

        $type = $request->input('type');
        $content = Content::create([
            'module_id' => $module->id,
            'title'     => $request->input('title'),
            'type'      => $type,
            'content'   => $request->input('content'),
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
                    'question' => $question['text'],
                    'order' => $qIndex,
                ]);

                foreach ($question['options'] as $opt) {
                    QuizOption::create([
                        'question_id' => $q->id,
                        'option_text' => $opt['text'],
                        'is_correct' => isset($opt['is_correct']) ? 1 : 0,
                    ]);
                }
            }
        }

        session()->flash('success_message', 'Materi berhasil ditambahkan.');
        return back();
    }

    public function update(Request $request)
    {
        $isAiGenerated = $request->input('type') === 'quiz' && $request->boolean('is_ai_generated');

        $request->validate([
            'id' => 'required|exists:contents,id',
            'title' => 'nullable|string|max:255',
            'content' => 'required',
            'type' => 'required|in:text,quiz',
            'integrity_mode_enabled' => 'nullable|boolean',
            'require_fullscreen' => 'nullable|boolean',
            'max_violations' => 'nullable|integer|min:1|max:20',
            'ai_question_count' => 'nullable|integer|min:1|max:20',
        ]);

        if ($request->input('type') === 'quiz' && !$isAiGenerated) {
            $request->validate([
                'questions' => 'required|array|min:1',
                'questions.*.text' => 'required|string',
                'questions.*.options' => 'required|array|min:2',
            ], [
                'questions.required' => 'Quiz harus memiliki minimal 1 pertanyaan',
                'questions.*.text.required' => 'Pertanyaan tidak boleh kosong',
                'questions.*.options.required' => 'Setiap pertanyaan harus memiliki minimal 2 jawaban',
            ]);
        }

        $content = Content::findOrFail($request->input('id'));

        $oldContent = $content->content;
        $oldType = $content->type;

        $content->update([
            'title' => $request->input('title'),
            'type' => $request->input('type'),
            'content' => $request->input('content'),
            'integrity_mode_enabled' => $request->input('type') === 'quiz' ? (bool) $request->integrity_mode_enabled : false,
            'require_fullscreen' => $request->input('type') === 'quiz' ? (bool) $request->require_fullscreen : false,
            'max_violations' => $request->input('type') === 'quiz' ? (int) ($request->max_violations ?? 3) : 3,
            'is_ai_generated' => $isAiGenerated,
            'ai_question_count' => $isAiGenerated ? (int) ($request->ai_question_count ?? 5) : 5,
        ]);

        if ($oldContent) {
            $this->deleteUnusedImages($oldContent, $request->input('content'));
        }

        if ($request->input('type') === 'text') {
            if ($oldType === 'quiz') {
                foreach ($content->questions as $q) {
                    $q->options()->delete();
                    $q->delete();
                }
            }

            session()->flash('success_message', 'Materi telah diperbarui.');
            return back();
        }

        // Always clear old manual questions (AI quiz has none; switching modes also clears)
        foreach ($content->questions as $q) {
            $q->options()->delete();
            $q->delete();
        }

        if ($isAiGenerated) {
            session()->flash('success_message', 'Materi & Kuis AI telah diperbarui.');
            return back();
        }

        // Buat quiz questions baru
        foreach ($request->questions as $qIndex => $question) {
            $newQ = QuizQuestion::create([
                'content_id' => $content->id,
                'question' => $question['text'],
                'order' => $qIndex,
            ]);

            foreach ($question['options'] as $opt) {
                QuizOption::create([
                    'question_id' => $newQ->id,
                    'option_text' => $opt['text'],
                    'is_correct' => isset($opt['is_correct']) ? 1 : 0,
                ]);
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
            // Hapus semua gambar yang ada di content sebelum menghapus content
            if ($content->content) {
                $this->deleteAllImagesFromContent($content->content);
            }

            // Hapus quiz questions dan options jika ada
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
