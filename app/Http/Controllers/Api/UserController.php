<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        $perPage = $request->integer('per_page', 20);
        $perPage = min($perPage, 100);

        $users = User::with(['laporanSungais'])->latest()->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Semua data user berhasil dimuat.',
            'data' => $users
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
        // Catatan: Jika pendaftaran lewat registrasi auth biasa, fungsi ini bisa opsional digunakan.
        // Disediakan jika sewaktu-waktu API membutuhkan penyimpanan user manual.
        $validated = $request->validate([
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Data user baru berhasil ditambahkan.',
            'data' => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $user_id)
    {
        // Gunakan first() biasa agar tidak melempar error crash jika tidak ditemukan
        $user = User::where('user_id', $user_id)->with(['laporanSungais.gambarSungais'])->first();

        // Handling jika user tidak ditemukan / id tidak ada
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak terdaftar / id tidak ada'
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
                'name_word_count' => $nameWordCount
            ]
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $user_id)
    {
        $user = User::where('user_id', $user_id)->first();

        // Handling jika user tidak ditemukan / id tidak ada
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak terdaftar / id tidak ada'
            ], 404);
        }

        // Validasi hak akses: pastikan user yang mengedit adalah user itu sendiri
        if ($user->getKey() !== Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'Anda Tidak Memiliki Akses untuk melakukan edit profil ini.'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Form ubah profil user berhasil disiapkan.',
            'data' => $user
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $user_id)
    {
        // Ubah firstOrFail() menjadi first() biasa untuk mengaktifkan kustom handling 404
        $user = User::where('user_id', $user_id)->first();

        // Handling jika user tidak ditemukan / id tidak ada
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak terdaftar / id tidak ada'
            ], 404);
        }

        // Validasi hak akses: pastikan yang mengedit adalah user 
        if ($user->getKey() !== Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'Anda Tidak Memiliki Akses untuk melakukan edit profil ini.'
            ], 403);
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
            'message' => 'Profil User Berhasil Diperbarui!',
            'data' => $user
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $user_id)
    {
        // Ubah findOrFail() menjadi find() biasa agar tidak crash halaman
        $user = User::find($user_id);

        // Handling jika user tidak ditemukan / id tidak ada
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak terdaftar / id tidak ada'
            ], 404);
        }

        $user->delete();
        
        return response()->json([
            'status' => true,
            'message' => 'Profil User Berhasil Dihapus!',
        ], 200);
    }
}