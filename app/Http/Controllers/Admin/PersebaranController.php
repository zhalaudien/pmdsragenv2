<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemuda;
use App\Models\Wilayah;
use App\Models\Cabang;
use Illuminate\Http\Request;

class PersebaranController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $scope = [
            'role'       => $user?->role?->name ?? session('role'),
            'wilayah_id' => $user?->wilayah_id ?? session('wilayah_id'),
            'cabang_id'  => $user?->cabang_id ?? session('cabang_id'),
        ];

        $filters = [
            'wilayah_id'  => $request->input('wilayah_id'),
            'cabang_id'   => $request->input('cabang_id'),
            'gender'      => $request->input('gender'),
            'blood_type'  => $request->input('blood_type'),
            'status_data' => $request->input('status_data', 'active'),
        ];

        if (in_array($scope['role'], ['admin_wilayah', 'admin_wilayah_pemuda'], true)) {
            $filters['wilayah_id'] = $scope['wilayah_id'];
        } elseif ($scope['role'] === 'admin_cabang') {
            $filters['wilayah_id'] = $scope['wilayah_id'];
            $filters['cabang_id']  = $scope['cabang_id'];
        }

        if (in_array($scope['role'], ['admin_wilayah_pemuda', 'admin_pemuda'], true)) {
            $filters['gender'] = 'L';
        } elseif ($scope['role'] === 'admin_pemudi') {
            $filters['gender'] = 'P';
        }

        $stats = Pemuda::getPersebaranStats($scope, $filters);

        $wilayahQuery = Wilayah::orderBy('id', 'ASC');
        if (in_array($scope['role'], ['admin_wilayah', 'admin_wilayah_pemuda', 'admin_cabang'], true) && !empty($scope['wilayah_id'])) {
            $wilayahQuery->where('id', (int) $scope['wilayah_id']);
        }
        $wilayahList = $wilayahQuery->get();

        $cabangQuery = Cabang::orderBy('name', 'ASC');
        if ($scope['role'] === 'admin_cabang' && !empty($scope['cabang_id'])) {
            $cabangQuery->where('id', (int) $scope['cabang_id']);
        } elseif (in_array($scope['role'], ['admin_wilayah', 'admin_wilayah_pemuda'], true) && !empty($scope['wilayah_id'])) {
            $cabangQuery->where('wilayah_id', (int) $scope['wilayah_id']);
        } elseif (!empty($filters['wilayah_id'])) {
            $cabangQuery->where('wilayah_id', (int) $filters['wilayah_id']);
        }
        $cabangList = $cabangQuery->get();

        return view('admin.persebaran.index', [
            'title'       => 'Dashboard Persebaran Data Pemuda',
            'stats'       => $stats,
            'filters'     => $filters,
            'scope'       => $scope,
            'wilayahList' => $wilayahList,
            'cabangList'  => $cabangList,
            'user'        => session()->all(),
        ]);
    }
}
