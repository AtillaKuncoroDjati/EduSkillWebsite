<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.user.index');
    }

    public function detail($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function request(Request $request)
    {
        $query = User::query();

        if ($request->has('search') && !empty($request->search['value'])) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%');
            });
        }

        if ($request->has('status_filter') && in_array($request->status_filter, ['aktif', 'nonaktif', 'banned'])) {
            $query->where('status', $request->status_filter);
        }

        if ($request->has('permission_filter') && in_array($request->permission_filter, ['admin', 'user'])) {
            $query->where('permission', $request->permission_filter);
        }

        $total = $query->count();

        if ($request->has('start') && $request->has('length')) {
            $query->skip($request->input('start'))->take($request->input('length'));
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'draw' => $request->input('draw', 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'permission' => 'required|in:admin,user',
            'status' => 'required|in:aktif,nonaktif,banned',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => 'Nama lengkap wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'username.unique' => 'Username sudah digunakan',
            'phone.unique' => 'Nomor telepon sudah terdaftar',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'avatar.image' => 'File harus berupa gambar',
            'avatar.max' => 'Ukuran gambar maksimal 2MB',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->permission = $request->permission;
        $user->status = $request->status;
        $user->email_verified_at = now();

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = public_path('uploads/avatar');

            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }

            $file->move($path, $filename);
            $user->avatar = $filename;
        }

        $user->save();

        session()->flash('success_message', 'Berhasil menambahkan pengguna: ' . $user->name);
        return redirect()->route('admin.user.index');
    }

    public function update(Request $request)
    {
        $user = User::findOrFail($request->id);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($user->id)
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'phone')->ignore($user->id)
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'permission' => 'required|in:admin,user',
            'status' => 'required|in:aktif,nonaktif,banned',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => 'Nama lengkap wajib diisi',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'username.unique' => 'Username sudah digunakan',
            'phone.unique' => 'Nomor telepon sudah terdaftar',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'avatar.image' => 'File harus berupa gambar',
            'avatar.max' => 'Ukuran gambar maksimal 2MB',
        ]);

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->permission = $request->permission;
        $user->status = $request->status;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
            $user->password_changed_at = now();
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar && $user->avatar !== 'default-avatar.png') {
                $oldPath = public_path('uploads/avatar/' . $user->avatar);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $file = $request->file('avatar');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = public_path('uploads/avatar');

            if (!File::exists($path)) {
                File::makeDirectory($path, 0755, true);
            }

            $file->move($path, $filename);
            $user->avatar = $filename;
        }

        $user->save();

        session()->flash('success_message', 'Berhasil memperbarui data pengguna: ' . $user->name);
        return redirect()->route('admin.user.index');
    }

    public function delete(Request $request)
    {
        $user = User::findOrFail($request->id);

        if ($user->id === auth()->id()) {
            session()->flash('error_message', 'Anda tidak dapat menghapus akun Anda sendiri');
            return redirect()->route('admin.user.index');
        }

        if ($user->avatar && $user->avatar !== 'default-avatar.png') {
            $avatarPath = public_path('uploads/avatar/' . $user->avatar);
            if (File::exists($avatarPath)) {
                File::delete($avatarPath);
            }
        }

        $userName = $user->name;
        $user->delete();

        session()->flash('success_message', 'Berhasil menghapus pengguna: ' . $userName);
        return redirect()->route('admin.user.index');
    }

    public function toggleStatus(Request $request)
    {
        $user = User::findOrFail($request->id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat mengubah status akun Anda sendiri'
            ], 403);
        }

        $user->status = $request->status;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Status pengguna berhasil diubah',
            'user' => $user
        ]);
    }

    public function suspend(Request $request)
    {
        $request->validate([
            'id' => 'required|uuid|exists:users,id',
            'duration' => 'required|integer|min:1|max:43200',
            'reason' => 'nullable|string|max:500',
        ], [
            'duration.required' => 'Durasi suspensi wajib diisi.',
            'duration.min' => 'Durasi minimal 1 menit.',
            'duration.max' => 'Durasi maksimal 30 hari (43200 menit).',
        ]);

        $user = User::findOrFail($request->id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat mensuspend akun Anda sendiri.',
            ], 403);
        }

        $user->is_suspended = true;
        $user->suspended_until = now()->addMinutes((int) $request->duration);
        $user->suspension_reason = $request->reason;
        $user->suspended_by = auth()->id();
        $user->suspended_at = now();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil disuspend.',
            'user' => $user,
        ]);
    }

    public function unsuspend(Request $request)
    {
        $request->validate([
            'id' => 'required|uuid|exists:users,id',
        ]);

        $user = User::findOrFail($request->id);

        $user->is_suspended = false;
        $user->suspended_until = null;
        $user->suspension_reason = null;
        $user->suspended_by = null;
        $user->suspended_at = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Suspensi pengguna berhasil dicabut.',
            'user' => $user,
        ]);
    }
}
