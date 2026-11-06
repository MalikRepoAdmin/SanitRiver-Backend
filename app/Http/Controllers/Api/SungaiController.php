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
        return response()->json([
            'status' => true,
            'message' => 'Display create form for Sungai'
        ], 200);
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
            'message' => 'Data sungai baru berhasil ditambahkan.',
            'data' => $sungai
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $sungai = Sungai::find($id);

        if (!$sungai) {
            return response()->json([
                'status' => false,
                'message' => 'id tidak ada'
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
    public function edit($id)
    {
        $sungai = Sungai::find($id);

        if (!$sungai) {
            return response()->json([
                'status' => false,
                'message' => 'id tidak ada'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Form edit data sungai berhasil disiapkan.',
            'data' => $sungai
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $sungai = Sungai::find($id);

        if (!$sungai) {
            return response()->json([
                'status' => false,
                'message' => 'id tidak ada!'
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
            'message' => 'Data sungai berhasil diperbarui.',
            'data' => $sungai
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $sungai = Sungai::find($id);

        if (!$sungai) {
            return response()->json([
                'status' => false,
                'message' => 'id tidak ada!'
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
            'message' => 'Data sungai beserta file gambar terkait berhasil dihapus.'
        ], 200);
    }

    /**
     * Display the map view for Sungai.
     */
    public function map()
    {
        $sungais = Sungai::with(['gambarSungais'])->get();
    
        return response()->json([
            'status' => true,
            'message' => 'Data peta sungai berhasil dimuat!',
            'data' => $sungais
        ], 200);
    }
}