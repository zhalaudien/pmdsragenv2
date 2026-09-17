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
            $rawList   = $wargaResponse['data'] ?? [];
            $sragenUuid = $this->apiService->getSragenUuid();

            // Strict security filter: only keep citizens belonging to Perwakilan Sragen
            $wargaList = array_values(array_filter($rawList, function ($item) use ($sragenUuid) {
                if (!empty($item['perwakilan_uuid']) && $item['perwakilan_uuid'] !== $sragenUuid) {
                    return false;
                }
                if (!empty($item['perwakilan']) && stripos($item['perwakilan'], 'Sragen') === false) {
                    return false;
                }
                return true;
            }));

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

        $cabangOptions = [];
        if (!empty($mtaSragenCabangList)) {
            foreach ($mtaSragenCabangList as $c) {
                $cabangOptions[] = [
                    'uuid' => $c['uuid'] ?? '',
                    'name' => $c['nama'] ?? ($c['name'] ?? ''),
                    'code' => $c['kode'] ?? ($c['code'] ?? ''),
                ];
            }
        } else {
            foreach ($localCabangList as $c) {
                $cabangOptions[] = [
                    'uuid' => $c->mta_uuid ?? '',
                    'name' => $c->name,
                    'code' => $c->code ?? '',
                ];
            }
        }

        $stats = [
            'totalPemudaSyncedMta' => Pemuda::whereNotNull('mta_warga_uuid')->count(),
            'totalPemudaLokal'     => Pemuda::count(),
        ];

        return view('admin.warga_mta.index', [
            'title'               => 'Data Warga MTA Sragen (api.mta.or.id)',
            'wargaList'           => $wargaList,
            'meta'                => $meta,
            'apiError'            => $apiError,
            'mtaSragenCabangList' => $mtaSragenCabangList,
            'localCabangList'     => $localCabangList,
            'cabangList'          => $cabangOptions,
            'stats'               => $stats,
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
        $sragenUuid  = $this->apiService->getSragenUuid();

        // Enforce data security: only allow warga from Perwakilan Sragen
        $isSragen = (
            (!empty($warga['perwakilan_uuid']) && $warga['perwakilan_uuid'] === $sragenUuid) ||
            (!empty($warga['perwakilan']) && stripos($warga['perwakilan'], 'Sragen') !== false) ||
            (!empty($warga['kabupaten']) && stripos($warga['kabupaten'], 'Sragen') !== false)
        );

        if (!$isSragen) {
            return redirect()->route('admin.warga-mta.index')
                ->with('error', 'Hanya data warga dari Perwakilan Sragen yang diizinkan untuk diakses.');
        }

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

        if (empty($wargaUuid)) {
            return redirect()->back()->with('error', 'Parameter warga tidak valid.');
        }

        $detailRes = $this->apiService->getWargaDetail($wargaUuid);
        if (!($detailRes['success'] ?? false) || empty($detailRes['data'])) {
            return redirect()->back()->with('error', 'Gagal mengambil data lengkap warga dari server API MTA.');
        }

        $wargaData  = $detailRes['data'];
        $sragenUuid = $this->apiService->getSragenUuid();

        // Enforce data security: only allow importing warga from Perwakilan Sragen
        $isSragen = (
            (!empty($wargaData['perwakilan_uuid']) && $wargaData['perwakilan_uuid'] === $sragenUuid) ||
            (!empty($wargaData['perwakilan']) && stripos($wargaData['perwakilan'], 'Sragen') !== false) ||
            (!empty($wargaData['kabupaten']) && stripos($wargaData['kabupaten'], 'Sragen') !== false)
        );

        if (!$isSragen) {
            return redirect()->back()->with('error', 'Hanya data warga dari Perwakilan Sragen yang dapat diimpor ke sistem ini.');
        }

        // Resolusi cabang otomatis sesuai data cabang resmi dari MTA Pusat
        $cabangUuid = $wargaData['cabang_uuid'] ?? null;
        $cabangName = trim($wargaData['cabang'] ?? ($wargaData['cabang_nama'] ?? ''));

        $cabang = null;
        if (!empty($cabangUuid)) {
            $cabang = Cabang::where('mta_uuid', $cabangUuid)->first();
        }
        if (!$cabang && !empty($cabangName)) {
            $cabang = Cabang::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($cabangName)])->first();
        }

        // Fallback jika dikirim eksplisit
        if (!$cabang && $request->filled('cabang_id')) {
            $cabang = Cabang::find((int) $request->input('cabang_id'));
        }

        if (!$cabang) {
            return redirect()->back()->with('error', "Cabang '{$cabangName}' dari data MTA Pusat tidak ditemukan pada basis data cabang lokal.");
        }

        $cabangId = (int) $cabang->id;

        // Authorization scope check
        $user = auth()->user();
        if ($user && (int) $user->role_id === 3 && (int) $user->cabang_id !== $cabangId) {
            return redirect()->back()->with('error', "Warga ini tercatat di cabang '{$cabang->name}'. Anda hanya memiliki wewenang untuk mengelola cabang Anda sendiri.");
        }
        if ($user && in_array((int) $user->role_id, [2, 6], true)) {
            if ((int) $cabang->wilayah_id !== (int) $user->wilayah_id) {
                return redirect()->back()->with('error', "Cabang '{$cabang->name}' berada di luar wilayah wewenang Anda.");
            }
        }
        if ($user && (int) $user->role_id === 4 && strtoupper($wargaData['kelamin'] ?? 'L') !== 'L') {
            return redirect()->back()->with('error', 'Admin Pemuda hanya dapat mengimpor data berjenis kelamin Laki-laki.');
        }
        if ($user && (int) $user->role_id === 5 && strtoupper($wargaData['kelamin'] ?? '') !== 'P') {
            return redirect()->back()->with('error', 'Admin Pemudi hanya dapat mengimpor data berjenis kelamin Perempuan.');
        }
        if ($user && (int) $user->role_id === 6 && strtoupper($wargaData['kelamin'] ?? 'L') !== 'L') {
            return redirect()->back()->with('error', 'Admin Wilayah Pemuda hanya dapat mengimpor data berjenis kelamin Laki-laki.');
        }

        $syncRes = $this->syncService->syncWargaToPemuda($wargaData, $cabangId, auth()->id());

        if ($syncRes['success']) {
            return redirect()->route('admin.pemuda.detail', $syncRes['pemuda_id'])
                ->with('success', "Data warga '{$wargaData['nama']}' berhasil diimpor ke cabang {$cabang->name} sesuai data cabang MTA Pusat.");
        }

        return redirect()->back()->with('error', $syncRes['message']);
    }
}
