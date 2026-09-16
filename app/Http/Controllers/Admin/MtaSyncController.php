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
        $perwakilanUuid = $request->input('perwakilan_uuid');
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

        $wargaList = $res['data'];
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
        $wargaUuid = $request->input('warga_uuid');
        $cabangId  = (int) $request->input('cabang_id');

        if (empty($wargaUuid) || $cabangId <= 0) {
            return response()->json(['success' => false, 'message' => 'Parameter warga_uuid dan cabang_id wajib diisi.']);
        }

        $detailRes = $this->apiService->getWargaDetail($wargaUuid);
        if (!($detailRes['success'] ?? false) || empty($detailRes['data'])) {
            return response()->json(['success' => false, 'message' => 'Gagal mengambil data warga dari server API MTA.']);
        }

        $syncRes = $this->syncService->syncWargaToPemuda($detailRes['data'], $cabangId, auth()->id());
        return response()->json($syncRes);
    }

    public function syncPemuda(int $id)
    {
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
        $cabangId    = $request->input('cabang_id') ? (int) $request->input('cabang_id') : null;
        $onlyPending = (bool) $request->input('only_pending', true);

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
