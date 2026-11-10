<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sungai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SungaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sungais = Sungai::with(['gambarSungais', 'laporanSungais'])->get();

        return response()->json([
            'status' => true,
            'message' => 'Semua data sungai berhasil dimuat.',
            'data' => $sungais
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_sungai' => 'required|string',
            'alamat' => 'nullable|string',
            'status' => 'nullable|string',
            'tipe_sungai' => 'nullable|string',
            'geometri' => 'nullable',
        ]);

        $sungai = Sungai::create($data);

        return response()->json([
            'status' => true,
            'message' => "Data sungai id {$sungai->sungai_id} berhasil ditambahkan.",
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $sungai_id)
    {
        $sungai = Sungai::where('sungai_id', $sungai_id)->first();

        if (!$sungai) {
            return response()->json([
                'status' => false,
                'message' => "ID Sungai $sungai_id tidak ada!"
            ], 404);
        }

        $sungai->load(['gambarSungais', 'laporanSungais']);

        // Pengecekan kondisi jika gambar atau laporan belum ada
        $infoGambar = $sungai->gambarSungais->isEmpty() ? 'Gambar belum tersedia.' : 'Gambar tersedia.';
        $infoLaporan = $sungai->laporanSungais->isEmpty() ? 'Laporan belum tersedia.' : 'Laporan tersedia.';

        return response()->json([
            'status' => true,
            'message' => "Detail data sungai ditemukan. [{$infoGambar} | {$infoLaporan}]",
            'data' => $sungai
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $sungai_id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $sungai_id)
    {
        $sungai = Sungai::where('sungai_id', $sungai_id)->first();

        if (!$sungai) {
            return response()->json([
                'status' => false,
                'message' => "Gagal memperbarui: ID Sungai $sungai_id tidak ditemukan!"
            ], 404);
        }

        $data = $request->validate([
            'nama_sungai' => 'nullable|string',
            'alamat' => 'nullable|string',
            'status' => 'nullable|string',
            'tipe_sungai' => 'nullable|string',
            'geometri' => 'nullable',
        ]);

        $sungai->update($data);

        return response()->json([
            'status' => true,
            'message' => "Data sungai id {$sungai->sungai_id} berhasil diperbarui.",
            'data' => $sungai
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $sungai_id)
    {
        $sungai = Sungai::where('sungai_id', $sungai_id)->first();

        if (!$sungai) {
            return response()->json([
                'status' => false,
                'message' => "Gagal menghapus: ID Sungai $sungai_id tidak ditemukan!"
            ], 404);
        }

        // Hapus file gambar terkait di storage jika ada
        foreach ($sungai->gambarSungais as $g) {
            if ($g->file_path) {
                Storage::disk('public')->delete($g->file_path);
            }
            $g->delete();
        }

        $sungai->delete();

        return response()->json([
            'status' => true,
            'message' => "Data sungai id {$sungai->sungai_id} beserta file gambar terkait berhasil dihapus."
        ], 200);
    }

    /**
     * Get all river locations for API Map rendering.
     */
    public function getLocations()
    {
        // Ambil data sungai beserta koordinat (geometri) dan relasi gambarnya
        $sungais = Sungai::with(['gambarSungais'])->latest()->get();

        // Jika database sungai kosong, kembalikan respon empty array yang rapi
        if ($sungais->isEmpty()) {
            return response()->json([
                'status'  => true,
                'message' => 'Data lokasi sungai masih kosong.',
                'data'    => []
            ], 200);
        }

        // Return JSON murni yang siap dirender oleh Front-End sebagai koordinat peta
        return response()->json([
            'status'  => true,
            'message' => 'Seluruh koordinat dan lokasi peta sungai berhasil dimuat.',
            'data'    => $sungais
        ], 200);
    }
}