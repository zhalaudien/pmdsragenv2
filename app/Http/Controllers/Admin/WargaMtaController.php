<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pemuda;
use App\Models\Cabang;
use App\Models\Wilayah;
use App\Services\MtaApiService;
use App\Services\MtaSyncService;
use Illuminate\Http\Request;

class WargaMtaController extends Controller
{
    protected MtaApiService $apiService;
    protected MtaSyncService $syncService;

    public function __construct()
    {
        $this->apiService  = new MtaApiService();
        $this->syncService = new MtaSyncService($this->apiService);
    }

    public function index(Request $request)
    {
        $page      = max(1, (int) ($request->input('page', 1)));
        $perPage   = (int) ($request->input('per_page', 20));
        $perPage   = in_array($perPage, [10, 15, 20, 25, 50, 100], true) ? $perPage : 20;
        $search    = trim((string) $request->input('search'));
        $cabang    = trim((string) $request->input('cabang'));
        $kelamin   = strtoupper(trim((string) $request->input('kelamin')));
        $status    = trim((string) $request->input('status'));
        $statusPmd = trim((string) $request->input('status_pmd'));

        $apiParams = [
            'perwakilan' => $this->apiService->getSragenUuid(),
            'page'       => $page,
            'per_page'   => $perPage,
        ];

        if ($search !== '') {
            $apiParams['search'] = $search;
        }
        if ($cabang !== '') {
            $apiParams['cabang'] = $cabang;
        }
        if (in_array($kelamin, ['L', 'P'], true)) {
            $apiParams['kelamin'] = $kelamin;
        }
        if ($status !== '') {
            $apiParams['status'] = $status;
        }

        $wargaResponse = $this->apiService->getWargaList($apiParams);
        $apiError      = null;
        $wargaList     = [];
        $meta          = [
            'page'        => $page,
            'per_page'    => $perPage,
            'total'       => 0,
            'total_pages' => 1,
        ];

        if (!($wargaResponse['success'] ?? false)) {
            $apiError = $wargaResponse['message'] ?? 'Gagal menghubungi server API MTA (api.mta.or.id).';
        } else {
            $wargaList = $wargaResponse['data'] ?? [];
            if (isset($wargaResponse['meta']) && is_array($wargaResponse['meta'])) {
                $meta = $wargaResponse['meta'];
            }
        }

        $wargaUuids = array_filter(array_column($wargaList, 'uuid'));
        $localPemudaMap = [];

        if (!empty($wargaUuids)) {
            $matchedPemuda = Pemuda::with('cabang')
                ->whereIn('mta_warga_uuid', $wargaUuids)
                ->get();

            foreach ($matchedPemuda as $mp) {
                if (!empty($mp->mta_warga_uuid)) {
                    $localPemudaMap[$mp->mta_warga_uuid] = $mp;
                }
            }
        }

        foreach ($wargaList as &$w) {
            $uuid = $w['uuid'] ?? '';
            if (!empty($uuid) && isset($localPemudaMap[$uuid])) {
                $lp = $localPemudaMap[$uuid];
                $w['is_local_registered']     = true;
                $w['local_pemuda_id']         = (int) $lp->id;
                $w['local_reg_number']        = $lp->registration_number;
                $w['local_cabang_name']       = $lp->cabang->name ?? '-';
                $w['local_status_verifikasi'] = $lp->status_verifikasi;
            } else {
                $w['is_local_registered']     = false;
                $w['local_pemuda_id']         = null;
                $w['local_reg_number']        = null;
                $w['local_cabang_name']       = null;
                $w['local_status_verifikasi'] = null;
            }
        }
        unset($w);

        if ($statusPmd === 'registered') {
            $wargaList = array_values(array_filter($wargaList, fn($item) => ($item['is_local_registered'] ?? false) === true));
        } elseif ($statusPmd === 'unregistered') {
            $wargaList = array_values(array_filter($wargaList, fn($item) => ($item['is_local_registered'] ?? false) === false));
        }

        $mtaSragenCabangList = [];
        $sragenDetail = $this->apiService->getPerwakilanSragenDetail();
        if (($sragenDetail['success'] ?? false) && isset($sragenDetail['data']['cabang'])) {
            $mtaSragenCabangList = $sragenDetail['data']['cabang'];
        }

        $localCabangList = Cabang::getWithWilayah();

        $cabangList = !empty($mtaSragenCabangList) ? $mtaSragenCabangList : $localCabangList;

        return view('admin.warga_mta.index', [
            'title'               => 'Data Warga MTA Sragen (api.mta.or.id)',
            'wargaList'           => $wargaList,
            'meta'                => $meta,
            'apiError'            => $apiError,
            'mtaSragenCabangList' => $mtaSragenCabangList,
            'localCabangList'     => $localCabangList,
            'cabangList'          => $cabangList,
            'search'              => $search,
            'cabang'              => $cabang,
            'kelamin'             => $kelamin,
            'status'              => $status,
            'statusPmd'           => $statusPmd,
            'selectedCabang'      => $cabang,
            'selectedKelamin'     => $kelamin,
            'selectedStatus'      => $status,
            'selectedStatusPmd'   => $statusPmd,
            'perPage'             => $perPage,
            'page'                => $page,
            'user'                => session()->all(),
        ]);
    }

    public function detail(string $uuid)
    {
        $res = $this->apiService->getWargaDetail($uuid);

        if (!($res['success'] ?? false) || empty($res['data'])) {
            return redirect()->route('admin.warga-mta.index')
                ->with('error', $res['message'] ?? 'Data warga tidak ditemukan di API MTA.');
        }

        $warga       = $res['data'];
        $localPemuda = Pemuda::with('cabang.wilayah')->where('mta_warga_uuid', $uuid)->first();
        $localCabang = Cabang::getWithWilayah();

        return view('admin.warga_mta.detail', [
            'title'       => 'Detail Warga: ' . ($warga['nama'] ?? 'MTA'),
            'warga'       => $warga,
            'localPemuda' => $localPemuda,
            'localCabang' => $localCabang,
            'user'        => session()->all(),
        ]);
    }

    public function import(Request $request)
    {
        $wargaUuid = trim((string) $request->input('warga_uuid'));
        $cabangId  = (int) $request->input('cabang_id');

        if (empty($wargaUuid) || $cabangId <= 0) {
            return redirect()->back()->with('error', 'Pilih cabang lokal yang valid untuk mengimpor data warga ini.');
        }

        $detailRes = $this->apiService->getWargaDetail($wargaUuid);
        if (!($detailRes['success'] ?? false) || empty($detailRes['data'])) {
            return redirect()->back()->with('error', 'Gagal mengambil data lengkap warga dari server API MTA.');
        }

        $wargaData = $detailRes['data'];
        $syncRes   = $this->syncService->syncWargaToPemuda($wargaData, $cabangId, auth()->id());

        if ($syncRes['success']) {
            return redirect()->route('admin.pemuda.detail', $syncRes['pemuda_id'])
                ->with('success', $syncRes['message']);
        }

        return redirect()->back()->with('error', $syncRes['message']);
    }
}
