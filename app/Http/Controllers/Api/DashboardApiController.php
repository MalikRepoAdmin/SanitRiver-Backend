<?php

namespace App\Http\Controllers\Api;

// Import Controller utama dengan path lengkap agar tidak memicu "Problems 1" di VS Code
use App\Http\Controllers\Controller; 
use App\Models\Sungai;
use App\Models\LaporanSungai;

class DashboardApiController extends Controller
{
    /**
     * Return summary statistics for the homepage.
     */
    public function getSummary()
    {
        return response()->json([
            'status'  => true,
            'message' => 'Statistik ringkasan dashboard berhasil dimuat.',
            'data'    => [
                'total_sungai'  => Sungai::count(),
                'total_laporan' => LaporanSungai::count(),
            ]
        ], 200);
    }
}