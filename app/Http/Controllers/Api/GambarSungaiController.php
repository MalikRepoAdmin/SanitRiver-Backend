<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GambarSungai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GambarSungaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = GambarSungai::latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Semua data gambar berhasil dimuat.',
            'data' => $items
        ], 200);
    }

    /**
     * Store a newly created resource in storage (Polymorphic Upload).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'file'           => 'required|file|image|max:2048', // Batasi max 2MB
            'imageable_type' => 'nullable|string',
            'imageable_id'   => 'nullable|integer',
        ]);

        // Simpan file fisik ke folder public/gambar_sungais
        $path = $request->file('file')->store('gambar_sungais', 'public');

        $type = $request->input('imageable_type');
        $id   = $request->input('imageable_id');

        // Hubungkan otomatis jika ada pemilik Polymorphic (Sungai / Laporan)
        if ($type && $id) {
            if (!str_contains($type, '\\')) {
                $type = 'App\\Models\\' . ucfirst($type);
            }

            if (class_exists($type)) {
                $owner = $type::where((new $type)->getKeyName(), $id)->first();
                
                if ($owner) {
                    $gambar = $owner->gambarSungais()->create(['file_path' => $path]);

                    return response()->json([
                        'status'  => true,
                        // PAKE TANDA PANAH karena sukses dibuat dan datanya ada!
                        'message' => "Gambar ID {$gambar->gambar_sungai_id} berhasil ditambahkan dan dihubungkan ke data terkait.",
                        'data'    => $gambar
                    ], 201);
                }
            }
        }

        // Jika upload gambar lepasan (standalone)
        $gambar = GambarSungai::create(['file_path' => $path]);

        return response()->json([
            'status'  => true,
            // PAKE TANDA PANAH karena sukses dibuat dan datanya ada!
            'message' => "Gambar ID {$gambar->gambar_sungai_id} berhasil ditambahkan standalone.",
            'data'    => $gambar
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $gambarsungai_id)
    {
        $gambarSungai = GambarSungai::where('gambar_sungai_id', $gambarsungai_id)->first();

        // HANDLING 404
        if (!$gambarSungai) {
            return response()->json([
                'status'  => false,
                'message' => "Gambar dengan ID {$gambarsungai_id} tidak ditemukan."
            ], 404);
        }

        return response()->json([
            'status'  => true,
            // PAKE TANDA PANAH karena data valid ditemukan di database
            'message' => "Gambar berhasil dimuat dengan id {$gambarSungai->gambarsungai_id}.",
            'data'    => $gambarSungai
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $gambarsungai_id)
    {
        $gambarSungai = GambarSungai::where('gambar_sungai_id', $gambarsungai_id)->first();

        // HANDLING 404: TANPA TANDA PANAH (ambil dari parameter)
        if (!$gambarSungai) {
            return response()->json([
                'status'  => false,
                'message' => "Gagal update. Gambar dengan ID {$gambarsungai_id} tidak ditemukan."
            ], 404);
        }

        $request->validate([
            'file' => 'nullable|file|image|max:2048',
        ]);

        if ($request->hasFile('file')) {

            // Hapus file lama dari storage agar server tidak penuh
            if ($gambarSungai->file_path) {
                Storage::disk('public')->delete($gambarSungai->file_path);
            }

            // Simpan file baru
            $path = $request->file('file')->store('gambar_sungais', 'public');
            $gambarSungai->file_path = $path;
            $gambarSungai->save();
        }

        return response()->json([
            'status'  => true,
            'message' => "Gambar ID {$gambarSungai->gambarsungai_id} berhasil diperbarui.",
            'data'    => $gambarSungai
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $gambarsungai_id)
    {
        $gambarSungai = GambarSungai::where('gambar_sungai_id', $gambarsungai_id)->first();

        // HANDLING 404
        if (!$gambarSungai) {
            return response()->json([
                'status'  => false,
                'message' => "Gagal menghapus. Gambar dengan ID {$gambarsungai_id} tidak ditemukan."
            ], 404);
        }

        // Hapus file fisik dari folder storage public sebelum datanya di-delete
        if ($gambarSungai->file_path) {
            Storage::disk('public')->delete($gambarSungai->file_path);
        }

        $gambarSungai->delete();

        return response()->json([
            'status'  => true,
            'message' => "Gambar ID {$gambarSungai->gambarsungai_id} dan file fisiknya berhasil dihapus total."
        ], 200);
    }
}