<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sungai;
use Illuminate\Http\Request;

class SungaiApiController extends Controller
{
    public function getWaterways(Request $request)
    {
        // 1. Tangkap koordinat Bounding Box dari AJAX Leaflet
        $swLat = $request->query('swLat');
        $swLng = $request->query('swLng');
        $neLat = $request->query('neLat');
        $neLng = $request->query('neLng');
        $zoom  = (int) $request->query('zoom', 5);

        // 2. Modifikasi Handling: Beri respon 400 (Bad Request) jika parameter Leaflet tidak lengkap
        if (!$swLat || !$swLng || !$neLat || !$neLng) {
            return response()->json([
                'status'  => false,
                'message' => 'Parameter Bounding Box (swLat, swLng, neLat, neLng) wajib diisi untuk memuat koordinat peta GeoJSON.',
                'type'    => 'FeatureCollection',
                'features'=> []
            ], 400);
        }

        // 3. Query menggunakan Scope Spasial
        $query = Sungai::withGeoJson();

        // 4. Buat poligon pembatas layar (WKT Polygon)
        $polygonWkt = "POLYGON(($swLng $swLat, $neLng $swLat, $neLng $neLat, $swLng $neLat, $swLng $swLat))";

        // 5. Ambil data sungai yang berpotongan (Intersects) dengan layar
        $sungais = $query->whereRaw('ST_Intersects(geometri, ST_GeomFromText(?, 4326))', [$polygonWkt])
            ->limit(400) 
            ->get();

        // 6. Mapping data sesuai nama kolom properti Model
        $features = $sungais->map(function ($sungai) {
            return [
                'type' => 'Feature',
                'properties' => [
                    'id'     => $sungai->sungai_id, // Tetap menggunakan sungai_id kustom proyekmu
                    'nama'   => $sungai->nama_sungai,
                    'alamat' => $sungai->alamat,
                    'status' => $sungai->status,
                    'tipe'   => $sungai->tipe_sungai,
                ],
                'geometry' => json_decode($sungai->geojson_string),
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ], 200);
    }
}