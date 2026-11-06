<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 20);
        $perPage = min($perPage, 100);

        $admins = Admin::with(['sungais'])->latest()->paginate($perPage);

        return response()->json([
            'status' => true,
            'message' => 'Semua data admin berhasil dimuat.',
            'data' => $admins
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:admins,username',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6',
        ]);

        $admin = Admin::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Data admin baru berhasil ditambahkan.',
            'data' => $admin
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $admin_id)
    {
        // Gunakan first() biasa agar tidak memicu crash bawaan saat data kosong
        $admin = Admin::where('admin_id', $admin_id)->with(['sungais.gambarSungais'])->first();

        // Handling jika admin tidak ditemukan / id tidak ada
        if (!$admin) {
            return response()->json([
                'status' => false,
                'message' => 'Admin tidak terdaftar / id tidak ada.'
            ], 404);
        }

        $nameWordCount = str($admin->username)->wordCount() > 1 ? 2 : 1;

        return response()->json([
            'status' => true,
            'message' => "Admin berhasil dimuat dengan id {$admin_id}.",
            'data' => [
                'admin' => $admin,
                'name_word_count' => $nameWordCount
            ]
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $admin_id)
    {
        $admin = Admin::where('admin_id', $admin_id)->first();

        if (!$admin) {
            return response()->json([
                'status' => false,
                'message' => 'Admin tidak terdaftar / id tidak ada.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Form edit data admin berhasil disiapkan.',
            'data' => $admin
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $admin_id)
    {
        $admin = Admin::where('admin_id', $admin_id)->first();

        if (!$admin) {
            return response()->json([
                'status' => false,
                'message' => 'Admin tidak terdaftar / id tidak ada.'
            ], 404);
        }

        $validated = $request->validate([
            'username' => 'nullable|string|unique:admins,username,' . $admin->id,
            'email' => 'nullable|email|unique:admins,email,' . $admin->id,
        ]);

        $admin->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Data profil admin berhasil diperbarui.',
            'data' => $admin
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $admin_id)
    {
        $admin = Admin::where('admin_id', $admin_id)->first();

        if (!$admin) {
            return response()->json([
                'status' => false,
                'message' => 'Admin tidak terdaftar / id tidak ada.'
            ], 404);
        }

        $admin->delete();

        return response()->json([
            'status' => true,
            'message' => 'Admin berhasil dihapus.'
        ], 200);
    }
}