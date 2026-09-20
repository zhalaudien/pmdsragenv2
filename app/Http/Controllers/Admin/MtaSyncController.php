<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Wilayah;
use App\Models\Pemuda;
use App\Models\MtaSyncLog;
use App\Services\MtaApiService;
use App\Services\MtaSyncService;
use Illuminate\Http\Request;

class MtaSyncController extends Controller
{
    protected MtaApiService $apiService;
    protected MtaSyncService $syncService;

    public function __construct()
    {
        $this->apiService  = new MtaApiService();
        $this->syncService = new MtaSyncService($this->apiService);
    }

    public function index()
    {
        $testConn        = $this->apiService->testConnection();
        $localCabang     = Cabang::getWithWilayah();
        $wilayahList     = Wilayah::orderBy('id', 'ASC')->get();
        $recentLogs      = MtaSyncLog::getRecentLogs(10);
        $sragenDetail    = $this->apiService->getPerwakilanSragenDetail();
        $mtaSragenCabang = ($sragenDetail['success'] ?? false) && isset($sragenDetail['data']['cabang']) ? $sragenDetail['data']['cabang'] : [];

        $syncedCabangCount = Cabang::whereNotNull('mta_uuid')->count();
        $syncedPemudaCount = Pemuda::whereNotNull('mta_warga_uuid')->count();
        $totalPemudaCount  = Pemuda::count();
        $queueStatus       = $this->syncService->getQueueStatus();

        return view('admin.mta_sync.index', [
            'title'             => 'Integrasi Database Warga MTA (Perwakilan Sragen)',
            'testConn'          => $testConn,
            'sragenDetail'      => $sragenDetail['data'] ?? null,
            'mtaSragenCabang'   => $mtaSragenCabang,
            'localCabang'       => $localCabang,
            'wilayahList'       => $wilayahList,
            'recentLogs'        => $recentLogs,
            'syncedCabangCount' => $syncedCabangCount,
            'totalCabangCount'  => count($localCabang),
            'syncedPemudaCount' => $syncedPemudaCount,
            'totalPemudaCount'  => $totalPemudaCount,
            'queueStatus'       => $queueStatus,
            'user'              => session()->all(),
        ]);
    }

    public function testConnection()
    {
        $res = $this->apiService->testConnection();
        return response()->json([
            'status'   => $res['connected'] ? 'success' : 'error',
            'data'     => $res,
            'csrfHash' => csrf_token(),
        ]);
    }

    public function syncCabang(Request $request)
    {
        // Strictly lock to Perwakilan Sragen
        $perwakilanUuid = $this->apiService->getSragenUuid();
        $wilayahId      = $request->input('wilayah_id') ? (int) $request->input('wilayah_id') : null;
        $autoCreate     = (bool) $request->input('auto_create');

        $result = $this->syncService->syncCabang($perwakilanUuid, $wilayahId, $autoCreate);

        if ($result['success']) {
            return redirect()->route('admin.mta-sync.index')->with('success', $result['message']);
        }

        return redirect()->route('admin.mta-sync.index')->with('error', $result['message']);
    }

    public function searchWarga(Request $request)
    {
        $q          = trim((string) $request->input('q'));
        $cabangUuid = $request->input('cabang_uuid');
        $kelamin    = $request->input('kelamin');
        $limit      = (int) ($request->input('limit', 20));

        if (mb_strlen($q) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Kata kunci pencarian minimal 2 karakter.',
                'data'    => [],
            ]);
        }

        $params = ['limit' => $limit];
        if ($cabangUuid) {
            $params['cabang'] = $cabangUuid;
        }
        if ($kelamin) {
            $params['kelamin'] = $kelamin;
        }

        $res = $this->apiService->searchWarga($q, $params);

        if (!($res['success'] ?? false) || empty($res['data'])) {
            return response()->json([
                'success' => true,
                'message' => $res['message'] ?? 'Tidak ada data warga yang sesuai.',
                'data'    => [],
            ]);
        }

        $rawList    = $res['data'];
        $sragenUuid = $this->apiService->getSragenUuid();

        // Enforce data security: filter out any results not belonging to Perwakilan Sragen
        $wargaList = array_values(array_filter($rawList, function ($w) use ($sragenUuid) {
            if (!empty($w['perwakilan_uuid']) && $w['perwakilan_uuid'] !== $sragenUuid) {
                return false;
            }
            if (!empty($w['perwakilan']) && stripos($w['perwakilan'], 'Sragen') === false) {
                return false;
            }
            return true;
        }));

        foreach ($wargaList as &$w) {
            $wUuid = $w['uuid'] ?? '';
            $local = !empty($wUuid) ? Pemuda::where('mta_warga_uuid', $wUuid)->first() : null;
            $w['is_local_registered'] = ($local !== null);
            $w['local_pemuda_id']     = $local?->id;
        }

        return response()->json([
            'success' => true,
            'data'    => $wargaList,
        ]);
    }

    public function wargaDetail(string $uuid)
    {
        $res = $this->apiService->getWargaDetail($uuid);
        if (($res['success'] ?? false) && !empty($res['data'])) {
            $warga      = $res['data'];
            $sragenUuid = $this->apiService->getSragenUuid();

            $isSragen = (
                (!empty($warga['perwakilan_uuid']) && $warga['perwakilan_uuid'] === $sragenUuid) ||
                (!empty($warga['perwakilan']) && stripos($warga['perwakilan'], 'Sragen') !== false) ||
                (!empty($warga['kabupaten']) && stripos($warga['kabupaten'], 'Sragen') !== false)
            );

            if (!$isSragen) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya data warga dari Perwakilan Sragen yang diizinkan untuk diakses.',
                ], 403);
            }
        }

        return response()->json($res);
    }

    public function cabangWarga(Request $request, string $cabangUuid)
    {
        $page    = (int) ($request->input('page', 1));
        $perPage = (int) ($request->input('per_page', 25));
        $gender  = $request->input('gender');

        $res = $this->apiService->getCabangWarga($cabangUuid, $page, $perPage, $gender);
        return response()->json($res);
    }

    public function importWarga(Request $request)
    {
        $wargaUuid = trim((string) $request->input('warga_uuid'));

        if (empty($wargaUuid)) {
            return response()->json(['success' => false, 'message' => 'Parameter warga_uuid wajib diisi.']);
        }

        $detailRes = $this->apiService->getWargaDetail($wargaUuid);
        if (!($detailRes['success'] ?? false) || empty($detailRes['data'])) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data warga dari server API MTA.']);
        }

        $wargaData  = $detailRes['data'];
        $sragenUuid = $this->apiService->getSragenUuid();

        // Enforce data security: only allow importing citizens from Perwakilan Sragen
        $isSragen = (
            (!empty($wargaData['perwakilan_uuid']) && $wargaData['perwakilan_uuid'] === $sragenUuid) ||
            (!empty($wargaData['perwakilan']) && stripos($wargaData['perwakilan'], 'Sragen') !== false) ||
            (!empty($wargaData['kabupaten']) && stripos($wargaData['kabupaten'], 'Sragen') !== false)
        );

        if (!$isSragen) {
            return response()->json(['success' => false, 'message' => 'Hanya data warga dari Perwakilan Sragen yang dapat diimpor ke sistem ini.'], 403);
        }

        // Resolusi cabang otomatis sesuai basis data cabang MTA Pusat
        $cabangUuid = $wargaData['cabang_uuid'] ?? null;
        $cabangName = trim($wargaData['cabang'] ?? ($wargaData['cabang_nama'] ?? ''));

        $cabang = null;
        if (!empty($cabangUuid)) {
            $cabang = Cabang::where('mta_uuid', $cabangUuid)->first();
        }
        if (!$cabang && !empty($cabangName)) {
            $cabang = Cabang::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($cabangName)])->first();
        }
        if (!$cabang && $request->filled('cabang_id')) {
            $cabang = Cabang::find((int) $request->input('cabang_id'));
        }

        if (!$cabang) {
            return response()->json(['success' => false, 'message' => "Cabang MTA '{$cabangName}' tidak ditemukan di sistem lokal."]);
        }

        $cabangId = (int) $cabang->id;

        // Authorization scope check
        $user = auth()->user();
        if ($user && (int) $user->role_id === 3 && (int) $user->cabang_id !== $cabangId) {
            return response()->json(['success' => false, 'message' => "Warga ini tercatat di cabang '{$cabang->name}'. Anda hanya memiliki wewenang untuk mengelola cabang Anda sendiri."], 403);
        }
        if ($user && in_array((int) $user->role_id, [2, 6], true)) {
            if ((int) $cabang->wilayah_id !== (int) $user->wilayah_id) {
                return response()->json(['success' => false, 'message' => "Cabang '{$cabang->name}' berada di luar wilayah wewenang Anda."], 403);
            }
        }
        if ($user && (int) $user->role_id === 4 && strtoupper($wargaData['kelamin'] ?? 'L') !== 'L') {
            return response()->json(['success' => false, 'message' => 'Admin Pemuda hanya dapat mengimpor data berjenis kelamin Laki-laki.'], 403);
        }
        if ($user && (int) $user->role_id === 5 && strtoupper($wargaData['kelamin'] ?? '') !== 'P') {
            return response()->json(['success' => false, 'message' => 'Admin Pemudi hanya dapat mengimpor data berjenis kelamin Perempuan.'], 403);
        }
        if ($user && (int) $user->role_id === 6 && strtoupper($wargaData['kelamin'] ?? 'L') !== 'L') {
            return response()->json(['success' => false, 'message' => 'Admin Wilayah Pemuda hanya dapat mengimpor data berjenis kelamin Laki-laki.'], 403);
        }

        $syncRes = $this->syncService->syncWargaToPemuda($wargaData, $cabangId, auth()->id());
        return response()->json($syncRes);
    }

    public function syncPemuda(int $id)
    {
        $pemuda = Pemuda::with('cabang')->find($id);
        if (!$pemuda) {
            return redirect()->back()->with('error', 'Data pemuda tidak ditemukan.');
        }

        // Authorization scope check
        $user = auth()->user();
        if ($user && (int) $user->role_id === 3 && (int) $pemuda->cabang_id !== (int) $user->cabang_id) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak akses ke data cabang lain.');
        }
        if ($user && in_array((int) $user->role_id, [2, 6], true)) {
            if (!$pemuda->cabang || (int) $pemuda->cabang->wilayah_id !== (int) $user->wilayah_id) {
                return redirect()->back()->with('error', 'Data pemuda berada di luar wilayah wewenang Anda.');
            }
        }

        $result = $this->syncService->syncSinglePemuda($id, auth()->id());
        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    public function syncVerifyAll(Request $request)
    {
        $cabangId    = $request->input('cabang_id') ? (int) $request->input('cabang_id') : null;
        $onlyPending = (bool) $request->input('only_pending', true);

        $result = $this->syncService->syncVerifyAll($cabangId, $onlyPending, auth()->id());

        return redirect()->route('admin.mta-sync.index')->with('success', $result['message']);
    }

    public function queueInit(Request $request)
    {
        $cabangId    = $request->filled('cabang_id') ? (int) $request->input('cabang_id') : null;
        $onlyPending = $request->has('only_pending') ? $request->boolean('only_pending') : true;

        $result = $this->syncService->initSyncQueue($cabangId, $onlyPending, auth()->id(), true);
        return response()->json($result);
    }

    public function queueProcessItem()
    {
        $result = $this->syncService->processNextQueueItem();
        return response()->json($result);
    }

    public function queueStatus()
    {
        $result = $this->syncService->getQueueStatus();
        return response()->json($result);
    }

    public function queueCancel()
    {
        $result = $this->syncService->cancelQueue();
        return response()->json($result);
    }
}
