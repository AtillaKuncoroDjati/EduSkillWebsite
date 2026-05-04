<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

use App\Models\Kursus;

class KursusController extends Controller
{
    public function index()
    {
        return view('admin.kursus.index');
    }

    public function create()
    {
        $allKursuses = Kursus::orderBy('title')->get();
        return view('admin.kursus.create', compact('allKursuses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'thumbnail' => 'required|image|max:2048',
            'title' => 'required|string|max:255',
            'short_description' => 'required|string|max:500',
            'description' => 'required|string',
            'category' => 'required|in:programming,design,marketing,business,cybersecurity',
            'difficulty' => 'required|in:pemula,menengah,lanjutan',
            'certificate' => 'required|boolean',
            'status' => 'nullable|in:aktif,nonaktif,arsip',
        ], [
            'thumbnail.required' => 'Thumbnail kursus wajib diunggah.',
            'thumbnail.image' => 'File yang diunggah harus berupa gambar.',
            'thumbnail.max' => 'Ukuran thumbnail tidak boleh melebihi 2MB.',
            'title.required' => 'Judul kursus wajib diisi.',
            'title.max' => 'Judul kursus tidak boleh melebihi 255 karakter.',
            'short_description.required' => 'Deskripsi singkat wajib diisi.',
            'short_description.max' => 'Deskripsi singkat tidak boleh melebihi 500 karakter.',
            'description.required' => 'Deskripsi lengkap wajib diisi.',
            'category.required' => 'Kategori kursus wajib dipilih.',
            'category.in' => 'Kategori kursus yang dipilih tidak valid.',
            'difficulty.required' => 'Tingkat kesulitan wajib dipilih.',
            'difficulty.in' => 'Tingkat kesulitan yang dipilih tidak valid.',
            'certificate.required' => 'Opsi sertifikat wajib dipilih.',
            'certificate.boolean' => 'Opsi sertifikat harus berupa nilai benar atau salah.',
            'status.in' => 'Status kursus yang dipilih tidak valid.',
        ]);

        $course = new Kursus();
        $course->title = $request->title;
        $course->short_description = $request->short_description;
        $course->description = $request->description;
        $course->category = $request->category;
        $course->difficulty = $request->difficulty;
        $course->certificate = $request->certificate;
        $course->status = $request->status ?? 'aktif';

        if ($request->hasFile('thumbnail')) {
            $folder = Str::uuid()->toString();
            $path = public_path('uploads/kursus/' . $folder);

            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }

            $file = $request->file('thumbnail');
            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $file->move($path, $filename);

            $course->thumbnail = $folder . '/' . $filename;
            $course->save();
        }

        $course->save();

        $course->prerequisites()->sync($request->input('prerequisites', []));

        session()->flash('success_message', 'Berhasil menambahkan kursus baru: ' . $course->title);
        return redirect()->route('admin.kursus.index');
    }

    public function edit(Kursus $kursus)
    {
        $allKursuses = Kursus::where('id', '!=', $kursus->id)->orderBy('title')->get();
        return view('admin.kursus.edit', compact('kursus', 'allKursuses'));
    }

    public function update(Request $request, Kursus $kursus)
    {
        $request->validate([
            'thumbnail' => 'nullable|image|max:2048',
            'title' => 'required|string|max:255',
            'short_description' => 'required|string|max:500',
            'description' => 'required|string',
            'category' => 'required|in:programming,design,marketing,business,cybersecurity',
            'difficulty' => 'required|in:pemula,menengah,lanjutan',
            'certificate' => 'required|boolean',
            'status' => 'nullable|in:aktif,nonaktif,arsip',
        ]);

        $kursus->title = $request->title;
        $kursus->short_description = $request->short_description;
        $kursus->description = $request->description;
        $kursus->category = $request->category;
        $kursus->difficulty = $request->difficulty;
        $kursus->certificate = $request->certificate;
        $kursus->status = $request->status ?? 'aktif';

        if ($request->hasFile('thumbnail')) {
            if ($kursus->thumbnail) {
                $oldFolder = public_path('uploads/kursus/' . dirname($kursus->thumbnail));

                if (File::exists($oldFolder)) {
                    File::deleteDirectory($oldFolder);
                }
            }

            $folder = Str::uuid()->toString();
            $path = public_path('uploads/kursus/' . $folder);

            File::makeDirectory($path, 0755, true, true);

            $file = $request->file('thumbnail');
            $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
            $file->move($path, $filename);

            $kursus->thumbnail = $folder . '/' . $filename;
        }

        $kursus->save();

        $kursus->prerequisites()->sync($request->input('prerequisites', []));

        session()->flash('success_message', 'Berhasil memperbarui kursus: ' . $kursus->title);

        return redirect()->route('admin.kursus.index');
    }

    public function delete(Request $request)
    {
        $kursus = Kursus::find($request->id);

        if ($kursus->thumbnail) {
            $folderPath = public_path('uploads/kursus/' . dirname($kursus->thumbnail));

            if (File::exists($folderPath)) {
                File::deleteDirectory($folderPath);
            }
        }

        $kursus->delete();

        session()->flash('success_message', 'Berhasil menghapus kursus: ' . $kursus->title);
        return redirect()->route('admin.kursus.index');
    }

    public function request(Request $request)
    {
        $query = Kursus::query();

        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('short_description', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('status_filter') && in_array($request->status_filter, ['aktif', 'nonaktif', 'arsip'])) {
            $query->where('status', $request->status_filter);
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

    public function peserta(Kursus $kursus)
    {
        return view('admin.kursus.peserta', compact('kursus'));
    }

    public function pesertaRequest(Request $request, Kursus $kursus)
    {
        $query = $kursus->userCourses()->with('user');

        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('status_filter') && in_array($request->status_filter, ['enrolled', 'in_progress', 'completed'])) {
            $query->where('status', $request->status_filter);
        }

        $total = $query->count();

        if ($request->has('start') && $request->has('length')) {
            $query->skip($request->input('start'))->take($request->input('length'));
        }

        $data = $query->orderBy('enrolled_at', 'desc')->get();

        return response()->json([
            'draw' => $request->input('draw', 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ]);
    }
}
