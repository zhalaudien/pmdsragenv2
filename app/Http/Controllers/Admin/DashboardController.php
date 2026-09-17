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

        $wilayahQuery = Wilayah::orderBy('id', 'ASC');
        if (in_array($scope['role'], ['admin_wilayah', 'admin_wilayah_pemuda', 'admin_cabang'], true) && !empty($scope['wilayah_id'])) {
            $wilayahQuery->where('id', (int) $scope['wilayah_id']);
        }
        $wilayahList = $wilayahQuery->get();

        return view('admin.dashboard.index', [
            'title'       => 'Dashboard Sistem Pendataan Pemuda',
            'stats'       => $stats,
            'wilayahList' => $wilayahList,
            'user'        => session()->all(),
        ]);
    }
}
