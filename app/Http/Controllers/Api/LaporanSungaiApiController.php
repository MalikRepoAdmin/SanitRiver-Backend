<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporanSungai;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request; 

class DashboardApiController extends Controller
{
    /**
     * Tampilkan 5 laporan terbaru untuk Dashboard (RESTful API).
     */
    public function getLatestLaporans()
    {
        // Nama relasi diubah menjadi gambarSungais
        $laporanSungais = LaporanSungai::with(['sungai', 'gambarSungais'])
            ->latest()
            ->paginate(5);

        // Mengembalikan JSON murni
        return response()->json([
            'status'  => true,
            'message' => '5 data laporan terbaru berhasil dimuat untuk dashboard.',
            'data'    => $laporanSungais
        ], 200);
    }

    /**
     * Cari laporan berdasarkan nama sungai via API JSON.
     */
    public function search(Request $request)
    {
        // 1. Inisialisasi query builder
        $query = LaporanSungai::query();

        // DIPERBAIKI: Eager load relasinya sekalian biar data sungai & gambarnya ikut keluar di JSON
        $laporansungais = $this->applySearch($query, $request->query('search'))
            ->with(['sungai', 'gambarSungais'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // DIPERBAIKI: Mengembalikan JSON murni dan membuang fungsi compact() yang salah sintaks
        return response()->json([
            'status'  => true,
            'message' => "Hasil pencarian laporan dengan kata kunci '" . $request->query('search') . "' berhasil dimuat.",
            'data'    => $laporansungais
        ], 200);
    }

    /**
     * Query builder filter untuk pencarian.
     */
    private function applySearch(Builder $query, ?string $search) 
    {
        if (blank($search)) return $query;

        return $query->where(function ($q) use ($search) 
        {
            $q->whereHas('sungai', function ($sungaiQuery) use ($search)
            {
                $sungaiQuery->where('nama_sungai', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Fungsi filter opsional untuk dashboard.
     */
    public function filter(Request $request)
    {
        
    }
}