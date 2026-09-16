<?php

namespace App\Http\Controllers;

use App\Models\Pemuda;
use App\Models\Wilayah;
use App\Models\Cabang;
use App\Models\HomepageSetting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $totalPemuda   = 0;
        $totalVerified = 0;
        $totalCabang   = 0;
        $totalWilayah  = 4;
        $wilayahList   = [];
        $allCabang     = [];

        try {
            $totalPemuda   = Pemuda::where('status_data', 'active')->count();
            $totalVerified = Pemuda::where('status_verifikasi', 'verified')->where('status_data', 'active')->count();
            $totalCabang   = Cabang::count();
            $wilayahList   = Wilayah::getWithCabang();
            $totalWilayah  = count($wilayahList) > 0 ? count($wilayahList) : 4;
            $allCabang     = Cabang::getWithWilayah();
        } catch (\Throwable $e) {
            // Fallback graceful
        }

        $settings = HomepageSetting::getAllSettings();

        $heroChips    = json_decode($settings['hero_chips'] ?? '[]', true) ?: [];
        $heroFeatures = array_filter(array_map('trim', explode("\n", (string) ($settings['hero_card_features'] ?? ''))));
        $misiList     = json_decode($settings['misi_list'] ?? '[]', true) ?: [];
        $programs     = json_decode($settings['program_list'] ?? '[]', true) ?: [];
        $alurSteps    = json_decode($settings['alur_steps'] ?? '[]', true) ?: [];
        $faqs         = json_decode($settings['faq_list'] ?? '[]', true) ?: [];

        return view('landing', [
            'title'         => 'Pemuda MTA Perwakilan Sragen | Pusat Informasi & Pendataan Pemuda',
            'totalPemuda'   => $totalPemuda,
            'totalVerified' => $totalVerified,
            'totalCabang'   => $totalCabang > 0 ? $totalCabang : 61,
            'totalWilayah'  => $totalWilayah,
            'wilayahList'   => $wilayahList,
            'allCabang'     => $allCabang,
            'settings'      => $settings,
            'heroChips'     => $heroChips,
            'heroFeatures'  => $heroFeatures,
            'misiList'      => $misiList,
            'programs'      => $programs,
            'alurSteps'     => $alurSteps,
            'faqs'          => $faqs,
        ]);
    }
}
