<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemuda;
use App\Models\Wilayah;
use App\Models\Cabang;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $scope = [
            'role'       => $user?->role?->name ?? session('role'),
            'wilayah_id' => $user?->wilayah_id ?? session('wilayah_id'),
            'cabang_id'  => $user?->cabang_id ?? session('cabang_id'),
        ];

        $stats = Pemuda::getDashboardStats($scope);
        $wilayahList = Wilayah::orderBy('id', 'ASC')->get();

        return view('admin.dashboard.index', [
            'title'       => 'Dashboard Super Admin',
            'stats'       => $stats,
            'wilayahList' => $wilayahList,
            'user'        => session()->all(),
        ]);
    }
}
