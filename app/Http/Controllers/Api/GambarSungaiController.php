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
        $items = GambarSungai::all();

        return response()->json([
            'status' => true,
            'message' => 'Semua data gambar berhasil dimuat.',
            'data' => $items
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            'status' => true,
            'message' => 'Display create form for GambarSungai'
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'file' => 'required|file|image',
            'imageable_type' => 'nullable|string',
            'imageable_id' => 'nullable|integer',
        ]);

        $path = $request->file('file')->store('gambar_sungais', 'public');

        // Attach to polymorphic owner if provided
        $type = $request->input('imageable_type');
        $id = $request->input('imageable_id');

        if ($type && $id) {
            if (! str_contains($type, '\\')) {
                $type = 'App\\Models\\' . ucfirst($type);
            }

            if (class_exists($type)) {
                $owner = $type::find($id);
                if ($owner) {
                    $gambar = $owner->gambarSungais()->create(['file_path' => $path]);

                    return response()->json([
                        'status' => true,
                        'message' => 'Gambar berhasil ditambahkan dan dihubungkan ke data terkait.',
                        'data' => $gambar
                    ], 201);
                }
            }
        }

        $gambar = GambarSungai::create(['file_path' => $path]);

        return response()->json([
            'status' => true,
            'message' => 'Gambar berhasil ditambahkan.',
            'data' => $gambar
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $gambarSungai = GambarSungai::find($id);

        if (!$gambarSungai) {
            return response()->json([
                'status' => false,
                'message' => 'Gambar tidak ditemukan / id tidak ada.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => "Gambar berhasil dimuat dengan id {$id}.",
            'data' => $gambarSungai
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $gambarSungai = GambarSungai::find($id);

        if (!$gambarSungai) {
            return response()->json([
                'status' => false,
                'message' => 'Gambar tidak ditemukan / id tidak ada.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Form edit gambar berhasil disiapkan.',
            'data' => $gambarSungai
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $gambarSungai = GambarSungai::find($id);

        if (!$gambarSungai) {
            return response()->json([
                'status' => false,
                'message' => 'Gambar tidak ditemukan / id tidak ada.'
            ], 404);
        }

        $request->validate([
            'file' => 'nullable|file|image',
        ]);

        if ($request->hasFile('file')) {
            // delete old file if exists
            if ($gambarSungai->file_path) {
                Storage::disk('public')->delete($gambarSungai->file_path);
            }

            $path = $request->file('file')->store('gambar_sungais', 'public');
            $gambarSungai->file_path = $path;
        }

        $gambarSungai->save();

        return response()->json([
            'status' => true,
            'message' => 'Gambar berhasil diperbarui.',
            'data' => $gambarSungai
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $gambarSungai = GambarSungai::find($id);

        if (!$gambarSungai) {
            return response()->json([
                'status' => false,
                'message' => 'Gambar tidak ditemukan / id tidak ada.'
            ], 404);
        }

        if ($gambarSungai->file_path) {
            Storage::disk('public')->delete($gambarSungai->file_path);
        }

        $gambarSungai->delete();

        return response()->json([
            'status' => true,
            'message' => 'Gambar berhasil dihapus.'
        ], 200);
    }
}