<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($denied = $this->ensureAuthorized($request->user(), 'list-users', Admin::class, true)) {
            return $denied;
        }

        $perPage = $request->integer('per_page', 20);
        $perPage = min($perPage, 100);

        $users = User::with(['laporanSungais'])->latest()->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Semua data user berhasil dimuat.',
            'data' => $users,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($denied = $this->ensureAuthorized($request->user(), 'create-users', Admin::class, true)) {
            return $denied;
        }

        // Catatan: Jika pendaftaran lewat registrasi auth biasa, fungsi ini bisa opsional digunakan.
        // Disediakan jika sewaktu-waktu API membutuhkan penyimpanan user manual.
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'username' => 'required|max:255|unique:users,username',
            'password' => 'required|min:6|confirmed',
            'nama_lengkap' => 'required|max:255',
            'tgl_lahir' => 'required',
            'bio' => 'sometimes|nullable',
            'pekerjaan' => 'sometimes|nullable|max:255',
            'domisili' => 'sometimes|nullable',
        ], [
            'email.unique' => 'Email ini sudah terdaftar, silakan login.',
            'username.unique' => 'Username ini sudah terdaftar, silakan login.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'required' => 'Kolom ini wajib diisi dan tidak boleh kosong.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 255 karakter.',
            'pekerjaan.max' => 'Pekerjaan maksimal 255 karakter.',
        ]);

        $user = User::create([
            'email' => $request->email,
            'username' => $request->username,
            'password' => bcrypt($request->password), // Memastikan password ter-hash dengan aman
            'nama_lengkap' => $request->nama_lengkap,
            'tgl_lahir' => $request->tgl_lahir,
            'bio' => $request->whenFilled('bio', fn ($value) => $value),
            'pekerjaan' => $request->whenFilled('pekerjaan', fn ($value) => $value),
            'domisili' => $request->whenFilled('domisili', fn ($value) => $value),
        ]);

        return response()->json([
            'status' => true,
            'message' => "Profil user id {$user->user_id} berhasil ditambahkan.",
            'data' => $user,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $user_id)
    {
        if ($denied = $this->ensureAuthorized(Admin::findOrFail($user_id), 'show-user', Admin::class, true)) {
            return $denied;
        }

        // Gunakan first() biasa agar tidak melempar error crash jika tidak ditemukan
        $user = User::where('user_id', $user_id)->with(['laporanSungais.gambarSungais'])->first();

        // Handling jika user tidak ditemukan / id tidak ada
        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => "Profil user id $user_id tidak ada.",
            ], 404);
        }

        $nameWordCount = str($user->username)->wordCount() > 1 ? 2 : 1;

        // Pengecekan status kontribusi laporan dari user
        $infoLaporan = $user->laporanSungais->isEmpty() ? 'User belum pernah membuat laporan.' : 'User aktif berkontribusi membuat laporan.';

        return response()->json([
            'status' => true,
            'message' => "Profil data user berhasil ditemukan. [{$infoLaporan}]",
            'data' => [
                'user' => $user,
                'name_word_count' => $nameWordCount,
            ],
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $user_id)
    {
        if ($denied = $this->ensureAuthorized(Admin::findOrFail($user_id), 'edit-users', Admin::class, true)) {
            return $denied;
        }

        $user = User::where('user_id', $user_id)->first();

        // Handling jika user tidak ditemukan / id tidak ada
        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => "Profil user id $user_id tidak ada.",
            ], 404);
        }

        // Validasi hak akses: pastikan user yang mengedit adalah user itu sendiri
        if ($user->getKey() !== Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'Anda Tidak Memiliki Akses untuk melakukan edit profil ini.',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Form ubah profil user berhasil disiapkan.',
            'data' => $user,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $user_id)
    {
        if ($denied = $this->ensureAuthorized($request->user(), 'edit-users', Admin::class, true)) {
            return $denied;
        }

        // Ubah firstOrFail() menjadi first() biasa untuk mengaktifkan kustom handling 404
        $user = User::where('user_id', $user_id)->first();

        // Handling jika user tidak ditemukan / id tidak ada
        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => "Profil user id $user_id tidak ada.",
            ], 404);
        }

        $validated = $request->validate([
            'nama_lengkap' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'tgl_lahir' => 'nullable|string',
            'pekerjaan' => 'nullable|string|max:255',
            'domisili' => 'nullable|string',
        ], [
            'nama_lengkap.max' => 'Nama Lengkap maksimal 255 karakter',
            'pekerjaan.max' => 'Pekerjaan maksimal 255 karakter',
        ]);

        $user->update($validated);

        return response()->json([
            'status' => true,
            'message' => "Profil user id {$user->user_id} berhasil diperbarui!.",
            'data' => $user,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $user_id)
    {
        if ($denied = $this->ensureAuthorized(Admin::findOrFail($user_id), 'delete-users', Admin::class, true)) {
            return $denied;
        }

        // Ubah firstOrFail() menjadi first() biasa untuk mengaktifkan kustom handling 404
        $user = User::where('user_id', $user_id)->first();

        // Handling jika user tidak ditemukan / id tidak ada
        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menghapus. Profil user id $user_id tidak ada.",
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status' => true,
            'message' => "Profil user id {$user->user_id} berhasil dihapus!",
        ], 200);
    }
}
