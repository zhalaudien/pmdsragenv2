<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageSetting;
use Illuminate\Http\Request;

class HomepageSettingController extends Controller
{
    public function index()
    {
        $settings = HomepageSetting::getAllSettings();

        $heroChips = json_decode($settings['hero_chips'] ?? '[]', true) ?: [];
        $misiList  = json_decode($settings['misi_list'] ?? '[]', true) ?: [];
        $programs  = json_decode($settings['program_list'] ?? '[]', true) ?: [];
        $alurSteps = json_decode($settings['alur_steps'] ?? '[]', true) ?: [];
        $faqs      = json_decode($settings['faq_list'] ?? '[]', true) ?: [];

        return view('admin.homepage.index', [
            'title'     => 'Kelola Konten Beranda (Homepage)',
            'settings'  => $settings,
            'heroChips' => $heroChips,
            'misiList'  => $misiList,
            'programs'  => $programs,
            'alurSteps' => $alurSteps,
            'faqs'      => $faqs,
            'activeTab' => session('active_tab', 'hero'),
            'user'      => session()->all(),
        ]);
    }

    public function update(Request $request)
    {
        $tab = $request->input('active_tab', 'hero');

        $fields = [
            'hero_badge', 'hero_title', 'hero_subtitle', 'hero_btn_text', 'hero_card_title', 'hero_card_desc', 'hero_card_features',
            'stats_bidang_num', 'stats_bidang_label', 'stats_bidang_desc',
            'tentang_tag', 'tentang_title', 'tentang_desc_1', 'tentang_desc_2', 'visi_text',
            'wilayah_tag', 'wilayah_title', 'wilayah_desc',
            'program_tag', 'program_title', 'program_desc',
            'alur_tag', 'alur_title', 'alur_desc', 'cta_title', 'cta_desc', 'cta_btn_text',
            'faq_tag', 'faq_title', 'faq_desc',
            'kontak_tag', 'kontak_title', 'kontak_desc', 'alamat_kantor', 'whatsapp_number', 'whatsapp_label',
            'kode_akses_guru_daerah',
        ];

        foreach ($fields as $field) {
            $val = $request->input($field);
            if ($val !== null) {
                HomepageSetting::setSetting($field, trim((string) $val));
            }
        }

        // Hero Chips
        $chipsPost = $request->input('hero_chips');
        if (is_array($chipsPost)) {
            $chips = [];
            foreach ($chipsPost as $item) {
                if (!empty($item['text'])) {
                    $chips[] = [
                        'icon' => trim((string) ($item['icon'] ?? 'bi-check-circle')),
                        'text' => trim((string) $item['text']),
                    ];
                }
            }
            HomepageSetting::setSetting('hero_chips', json_encode($chips, JSON_UNESCAPED_UNICODE), 'hero', 'json', 'Highlight Chips');
        }

        // Misi List
        $misiPost = $request->input('misi_list');
        if (is_array($misiPost)) {
            $misi = [];
            $no   = 1;
            foreach ($misiPost as $item) {
                if (!empty($item['title'])) {
                    $misi[] = [
                        'number' => $no++,
                        'title'  => trim((string) $item['title']),
                        'desc'   => trim((string) ($item['desc'] ?? '')),
                    ];
                }
            }
            HomepageSetting::setSetting('misi_list', json_encode($misi, JSON_UNESCAPED_UNICODE), 'tentang', 'json', 'Daftar Misi');
        }

        // Program List
        $progPost = $request->input('program_list');
        if (is_array($progPost)) {
            $programs = [];
            foreach ($progPost as $item) {
                if (!empty($item['title'])) {
                    $programs[] = [
                        'icon'  => trim((string) ($item['icon'] ?? 'bi-grid')),
                        'color' => trim((string) ($item['color'] ?? 'primary')),
                        'title' => trim((string) $item['title']),
                        'desc'  => trim((string) ($item['desc'] ?? '')),
                        'badge' => trim((string) ($item['badge'] ?? '')),
                    ];
                }
            }
            HomepageSetting::setSetting('program_list', json_encode($programs, JSON_UNESCAPED_UNICODE), 'program', 'json', 'Daftar Program Kerja');
        }

        // Alur Steps
        $alurPost = $request->input('alur_steps');
        if (is_array($alurPost)) {
            $steps = [];
            $no    = 1;
            foreach ($alurPost as $item) {
                if (!empty($item['title'])) {
                    $steps[] = [
                        'step'  => $no++,
                        'title' => trim((string) $item['title']),
                        'desc'  => trim((string) ($item['desc'] ?? '')),
                    ];
                }
            }
            HomepageSetting::setSetting('alur_steps', json_encode($steps, JSON_UNESCAPED_UNICODE), 'alur', 'json', 'Daftar 4 Tahapan Alur');
        }

        // FAQ List
        $faqPost = $request->input('faq_list');
        if (is_array($faqPost)) {
            $faqs = [];
            foreach ($faqPost as $item) {
                if (!empty($item['q'])) {
                    $faqs[] = [
                        'q' => trim((string) $item['q']),
                        'a' => trim((string) ($item['a'] ?? '')),
                    ];
                }
            }
            HomepageSetting::setSetting('faq_list', json_encode($faqs, JSON_UNESCAPED_UNICODE), 'faq', 'json', 'Daftar Tanya Jawab FAQ');
        }

        return redirect()->route('admin.homepage.index')
            ->with('success', 'Konten beranda (homepage) berhasil diperbarui!')
            ->with('active_tab', $tab);
    }

    public function reset()
    {
        HomepageSetting::resetToDefaults();
        return redirect()->route('admin.homepage.index')
            ->with('success', 'Seluruh konten beranda berhasil dikembalikan ke format default sistem.');
    }
}
