<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Village;
use Illuminate\Http\Request;

class AjaxController extends Controller
{
    public function getCabangByWilayah(Request $request, int $wilayahId)
    {
        $user           = auth()->user();
        $scopeRole      = $user?->role?->name ?? session('role');
        $scopeWilayahId = $user?->wilayah_id ?? session('wilayah_id');
        $scopeCabangId  = $user?->cabang_id ?? session('cabang_id');

        $query = Cabang::orderBy('name', 'ASC');

        if (in_array($scopeRole, ['admin_wilayah', 'admin_wilayah_pemuda'], true) && !empty($scopeWilayahId)) {
            $query->where('wilayah_id', (int) $scopeWilayahId);
        } elseif ($scopeRole === 'admin_cabang' && !empty($scopeCabangId)) {
            $query->where('id', (int) $scopeCabangId);
        } else {
            $query->where('wilayah_id', $wilayahId);
        }

        return response()->json($query->get());
    }

    public function getVillagesByDistrict(int $districtId)
    {
        $villages = Village::where('district_id', $districtId)
            ->orderBy('name', 'ASC')
            ->get();

        return response()->json($villages);
    }
}
