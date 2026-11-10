<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanSungai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LaporanSungaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = LaporanSungai::with(['user', 'sungai', 'gambarSungais'])->get();

        return response()->json([
            'status' => true,
            'message' => 'Semua data laporan sungai berhasil dimuat.',
            'data' => $items
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
            'user_id' => 'required|exists:users,user_id',
            'sungai_id' => 'required|exists:sungais,sungai_id',
            'status' => 'nullable|string',
            'persetujuan' => 'nullable',
        ], [
            'user_id.exists' => 'User tidak tersedia / tidak terdaftar.',
            'sungai_id.exists' => 'Sungai tidak tersedia / id tidak ada.',
        ]);

        $laporan = LaporanSungai::create($data);

        return response()->json([
            'status' => true,
            'message' => "Data laporan sungai ID {$laporan->laporansungai_id} berhasil ditambahkan."
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $laporansungai_id)
    {
        $laporanSungai = laporanSungai::where('laporansungai_id', $laporansungai_id)->first();

        //handling 404
        if (!$laporanSungai) {
            return response()->json([
                'status' => false,
                'message' => "Data laporan sungai ID $laporansungai_id tidak tersedia."
            ], 404);
        }

        //eager load memuat data relasi
        $laporanSungai->load(['user', 'sungai', 'gambarSungais']);

        return response()->json([
            'status' => true,
            'message' => 'Detail data laporan sungai berhasil dimuat.',
            'data' => $laporanSungai
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $laporansungai_id)
    {
        $laporanSungai = laporanSungai::where('laporansungai_id', $laporansungai_id)->first();

        if (!$laporanSungai) {
            return response()->json([
                'status' => false,
                'message' => "Data laporan sungai ID $laporansungai_id tidak tersedia."
            ], 404);
        }

        $data = $request->validate([
            'user_id' => 'nullable|exists:users,user_id',
            'sungai_id' => 'nullable|exists:sungais,sungai_id',
            'status' => 'nullable|string',
            'persetujuan' => 'nullable',
        ], [
            'user_id.exists' => 'User tidak tersedia / tidak terdaftar.',
            'sungai_id.exists' => 'Sungai tidak tersedia / id tidak ada.',
        ]);

        $laporanSungai->update($data);

        return response()->json([
            'status' => true,
            'message' => "Data laporan sungai ID {$laporanSungai->laporansungai_id} berhasil diperbarui.",
            'data' => $laporanSungai
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $laporansungai_id)
    {
        $laporanSungai = laporanSungai::where('laporansungai_id', $laporansungai_id)->first();

        if (!$laporanSungai) {
            return response()->json([
                'status' => false,
                'message' => "Data laporan sungai ID $laporansungai_id tidak tersedia."
            ], 404);
        }

        $laporanSungai->delete();

        return response()->json([
            'status' => true,
            'message' => "Data laporan sungai ID {$laporanSungai->laporansungai_id} berhasil dihapus!"
        ], 200);
    }
}