<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    protected $table = 'homepage_settings';

    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'label',
    ];

    public static function getDefaults(): array
    {
        return [
            // 1. HERO SECTION
            'hero_badge' => [
                'group' => 'hero',
                'label' => 'Badge Header Hero',
                'type'  => 'text',
                'value' => "Majlis Tafsir Al-Qur'an (MTA) Perwakilan Sragen",
            ],
            'hero_title' => [
                'group' => 'hero',
                'label' => 'Judul Utama Hero',
                'type'  => 'text',
                'value' => 'Generasi Muda Berilmu, Berakhlak Mulia & Berjiwa Pengabdian',
            ],
            'hero_subtitle' => [
                'group' => 'hero',
                'label' => 'Subjudul / Deskripsi Hero',
                'type'  => 'textarea',
                'value' => 'Pusat Informasi & Basis Data Resmi Pemuda MTA Perwakilan Sragen. Wadah pembinaan akidah, pengembangan kompetensi, kesiapsiagaan pengabdian, dan pemetaan potensi pemuda di 4 Wilayah dan 61 Cabang se-Kabupaten Sragen.',
            ],
            'hero_btn_text' => [
                'group' => 'hero',
                'label' => 'Teks Tombol Form Pendataan',
                'type'  => 'text',
                'value' => 'Isi Form Pendataan Pemuda',
            ],
            'hero_card_title' => [
                'group' => 'hero',
                'label' => 'Judul Kartu Samping Hero',
                'type'  => 'text',
                'value' => 'Mengapa Harus Mengisi Form?',
            ],
            'hero_card_desc' => [
                'group' => 'hero',
                'label' => 'Deskripsi Kartu Samping Hero',
                'type'  => 'textarea',
                'value' => 'Pendataan bertujuan memetakan potensi keilmuan, profesi, keterampilan, dan kesiapan pengabdian pemuda MTA di seluruh cabang.',
            ],
            'hero_card_features' => [
                'group' => 'hero',
                'label' => 'Poin Manfaat Kartu Hero (Pisahkan dengan baris baru)',
                'type'  => 'textarea',
                'value' => "Nomor registrasi resmi pemuda\nPenyaluran bidang pengabdian\nRekomendasi pelatihan skill",
            ],
            'hero_chips' => [
                'group' => 'hero',
                'label' => 'Highlight Chips (JSON)',
                'type'  => 'json',
                'value' => json_encode([
                    ['icon' => 'bi-shield-check', 'text' => 'Satgas & Kesiapsiagaan'],
                    ['icon' => 'bi-broadcast', 'text' => 'Bankom Radio'],
                    ['icon' => 'bi-heart-pulse-fill', 'text' => 'Tim Ikhrom & Parkir'],
                    ['icon' => 'bi-laptop', 'text' => 'Skill & Wirausaha'],
                    ['icon' => 'bi-book-half', 'text' => 'Kajian & Tarbiyah'],
                ], JSON_UNESCAPED_UNICODE),
            ],

            // 2. STATS SECTION
            'stats_bidang_num' => [
                'group' => 'stats',
                'label' => 'Angka Bidang Pengabdian',
                'type'  => 'text',
                'value' => '5+',
            ],
            'stats_bidang_label' => [
                'group' => 'stats',
                'label' => 'Label Bidang Pengabdian',
                'type'  => 'text',
                'value' => 'Bidang Pengabdian',
            ],
            'stats_bidang_desc' => [
                'group' => 'stats',
                'label' => 'Keterangan Singkat Bidang',
                'type'  => 'text',
                'value' => 'Satgas, Bankom, Ikhrom, dll',
            ],

            // 3. TENTANG & VISI MISI
            'tentang_tag' => [
                'group' => 'tentang',
                'label' => 'Tag Section Tentang',
                'type'  => 'text',
                'value' => 'Tentang Organisasi',
            ],
            'tentang_title' => [
                'group' => 'tentang',
                'label' => 'Judul Section Tentang',
                'type'  => 'text',
                'value' => 'Mengenal Pemuda MTA Perwakilan Sragen',
            ],
            'tentang_desc_1' => [
                'group' => 'tentang',
                'label' => 'Paragraf 1 Tentang',
                'type'  => 'textarea',
                'value' => "Pemuda MTA Perwakilan Sragen adalah wadah pembinaan, pengkaderan, dan penggerak kegiatan generasi muda Majlis Tafsir Al-Qur'an di tingkat Kabupaten Sragen.",
            ],
            'tentang_desc_2' => [
                'group' => 'tentang',
                'label' => 'Paragraf 2 Tentang',
                'type'  => 'textarea',
                'value' => "Dengan berlandaskan Al-Qur'an dan As-Sunnah, pemuda MTA berperan aktif dalam dakwah Islam, kegiatan sosial kemanusiaan, kesiapsiagaan kebencanaan melalui Satgas, pelayanan pengajian melalui Tim Ikhrom & Parkir, serta pengembangan ekonomi dan wirausaha generasi muda.",
            ],
            'visi_text' => [
                'group' => 'tentang',
                'label' => 'Teks Visi Organisasi',
                'type'  => 'textarea',
                'value' => "\"Terwujudnya generasi muda muslim yang kokoh dalam akidah tauhid, istiqomah mengamalkan Al-Qur'an dan As-Sunnah, cerdas berilmu, mandiri berwirausaha, berakhlak mulia, serta siap berkhidmah untuk dakwah dan kemaslahatan umat.\"",
            ],
            'misi_list' => [
                'group' => 'tentang',
                'label' => 'Daftar Misi Organisasi (JSON)',
                'type'  => 'json',
                'value' => json_encode([
                    ['number' => 1, 'title' => 'Dakwah & Tarbiyah', 'desc' => "Menanamkan pemahaman Al-Qur'an dan As-Sunnah serta adab Islami pada generasi muda."],
                    ['number' => 2, 'title' => 'Pengabdian & Khidmah', 'desc' => 'Membangun kesiapsiagaan sosial, Satgas pengamanan dakwah, Bankom, dan Tim Ikhrom.'],
                    ['number' => 3, 'title' => 'Skill & Kemandirian', 'desc' => 'Mengembangkan keterampilan vokasi, wirausaha muda, IPTEK digital, dan potensi profesi.'],
                    ['number' => 4, 'title' => 'Tata Kelola Modern', 'desc' => 'Mewujudkan pendataan pemuda berbasis digital yang terstruktur, akurat, dan terintegrasi.'],
                ], JSON_UNESCAPED_UNICODE),
            ],

            // 4. WILAYAH SECTION
            'wilayah_tag' => [
                'group' => 'wilayah',
                'label' => 'Tag Section Wilayah',
                'type'  => 'text',
                'value' => 'Struktur Wilayah Koordinasi',
            ],
            'wilayah_title' => [
                'group' => 'wilayah',
                'label' => 'Judul Section Wilayah',
                'type'  => 'text',
                'value' => '4 Wilayah & 61 Cabang Binaan',
            ],
            'wilayah_desc' => [
                'group' => 'wilayah',
                'label' => 'Deskripsi Pengantar Wilayah',
                'type'  => 'textarea',
                'value' => 'Struktur organisasi Pemuda MTA Perwakilan Sragen dibagi ke dalam 4 Wilayah koordinasi untuk memastikan pembinaan dan komunikasi berjalan efektif di seluruh cabang.',
            ],

            // 5. PROGRAM KERJA
            'program_tag' => [
                'group' => 'program',
                'label' => 'Tag Section Program',
                'type'  => 'text',
                'value' => 'Bidang & Program Kerja',
            ],
            'program_title' => [
                'group' => 'program',
                'label' => 'Judul Section Program',
                'type'  => 'text',
                'value' => 'Ruang Aktualisasi & Pengabdian Pemuda',
            ],
            'program_desc' => [
                'group' => 'program',
                'label' => 'Deskripsi Pengantar Program',
                'type'  => 'textarea',
                'value' => 'Berbagai divisi dan program dirancang untuk mewadahi minat, bakat, serta semangat juang pemuda dalam mengabdi kepada agama, bangsa, dan masyarakat.',
            ],
            'program_list' => [
                'group' => 'program',
                'label' => 'Daftar Program Kerja (JSON)',
                'type'  => 'json',
                'value' => json_encode([
                    [
                        'icon'  => 'bi-book-half',
                        'color' => 'danger',
                        'title' => 'Kajian & Tarbiyah Pemuda',
                        'desc'  => "Kajian rutin pemuda tematik, pendalaman tafsir Al-Qur'an, tahsin tilawah, pembahasan hadits shahih, dan pembentukan karakter akhlaqul karimah.",
                        'badge' => 'Rutin Bulanan & Wilayah',
                    ],
                    [
                        'icon'  => 'bi-shield-check',
                        'color' => 'primary',
                        'title' => 'Satgas Kesiapsiagaan',
                        'desc'  => 'Pasukan pengamanan kegiatan dakwah dan pengajian akbar, tanggap darurat bencana (SAR), bakti sosial kemanusiaan, dan ketertiban acara perwakilan.',
                        'badge' => 'Disiplin & Siaga 24/7',
                    ],
                    [
                        'icon'  => 'bi-broadcast-pin',
                        'color' => 'warning',
                        'title' => 'Bankom (Bantuan Komunikasi)',
                        'desc'  => 'Jaringan komunikasi radio terpadu untuk koordinasi acara besar, pemantauan arus lalu lintas jamaah, serta koordinasi cepat tanggap situasi darurat.',
                        'badge' => 'Frekuensi Radio Resmi',
                    ],
                    [
                        'icon'  => 'bi-heart-pulse-fill',
                        'color' => 'success',
                        'title' => 'Tim Ikhrom & Parkir',
                        'desc'  => 'Khidmah melayani jamaah pengajian Ahad pagi, pengaturan saf dan fasilitas jamaah, penataan kantong parkir tertib, dan kelancaran sirkulasi kendaraan.',
                        'badge' => 'Pengajian Rutin Ahad',
                    ],
                    [
                        'icon'  => 'bi-laptop',
                        'color' => 'info',
                        'title' => 'Pelatihan Skill & Wirausaha',
                        'desc'  => 'Workshop keahlian digital, desain grafis, coding, pelatihan mekanik/teknik, bimbingan wirausaha muda mandiri, dan jejaring ekonomi pemuda.',
                        'badge' => 'Pemberdayaan Ekonomi',
                    ],
                    [
                        'icon'  => 'bi-trophy-fill',
                        'color' => 'secondary',
                        'title' => 'Olahraga, Seni & Rihlah',
                        'desc'  => 'Menjalin ukhuwah dan kebugaran jasmani melalui turnamen futsal, bulutangkis, panahan, outbound / tadabbur alam, dan donor darah sukarela.',
                        'badge' => 'Solidaritas Ukhuwah',
                    ],
                ], JSON_UNESCAPED_UNICODE),
            ],

            // 6. ALUR PENDATAAN & BANNER CTA
            'alur_tag' => [
                'group' => 'alur',
                'label' => 'Tag Section Alur',
                'type'  => 'text',
                'value' => 'Alur Pendataan Pemuda',
            ],
            'alur_title' => [
                'group' => 'alur',
                'label' => 'Judul Section Alur',
                'type'  => 'text',
                'value' => '4 Langkah Mudah Pengisian Data',
            ],
            'alur_desc' => [
                'group' => 'alur',
                'label' => 'Deskripsi Pengantar Alur',
                'type'  => 'textarea',
                'value' => 'Proses pendataan dilakukan secara online, cepat, dan transparan. Ikuti 4 tahapan berikut untuk melengkapi profil Anda.',
            ],
            'alur_steps' => [
                'group' => 'alur',
                'label' => 'Daftar 4 Tahapan Alur (JSON)',
                'type'  => 'json',
                'value' => json_encode([
                    ['step' => 1, 'title' => 'Akses Formulir', 'desc' => 'Klik tombol "Form Pendataan" pada menu navigasi atau halaman ini untuk membuka form registrasi.'],
                    ['step' => 2, 'title' => 'Pilih Wilayah & Cabang', 'desc' => 'Tentukan cabang asal Anda (terintegrasi otomatis dengan 4 Wilayah di Sragen) dan isi identitas pribadi.'],
                    ['step' => 3, 'title' => 'Lengkapi Profil & Minat', 'desc' => 'Isi riwayat pendidikan, status pekerjaan, keahlian yang dikuasai, serta pilihan element dakwah (Satgas, Bankom, dll).'],
                    ['step' => 4, 'title' => 'Terima No. Registrasi', 'desc' => 'Dapatkan Nomor Registrasi resmi pemuda yang dapat dicetak sebagai bukti telah terdaftar di database perwakilan.'],
                ], JSON_UNESCAPED_UNICODE),
            ],
            'cta_title' => [
                'group' => 'alur',
                'label' => 'Judul Banner Ajakan (CTA)',
                'type'  => 'text',
                'value' => 'Sudahkah Anda Terdata di Sistem Pemuda MTA Sragen?',
            ],
            'cta_desc' => [
                'group' => 'alur',
                'label' => 'Deskripsi Banner Ajakan (CTA)',
                'type'  => 'textarea',
                'value' => 'Mari berkontribusi aktif dalam barisan dakwah dan kemaslahatan umat. Satu data pemuda untuk kemajuan bersama se-Kabupaten Sragen.',
            ],
            'cta_btn_text' => [
                'group' => 'alur',
                'label' => 'Teks Tombol Banner CTA',
                'type'  => 'text',
                'value' => 'Isi Formulir Sekarang',
            ],

            // 7. FAQ (TANYA JAWAB)
            'faq_tag' => [
                'group' => 'faq',
                'label' => 'Tag Section FAQ',
                'type'  => 'text',
                'value' => 'Tanya Jawab',
            ],
            'faq_title' => [
                'group' => 'faq',
                'label' => 'Judul Section FAQ',
                'type'  => 'text',
                'value' => 'Pertanyaan yang Sering Diajukan (FAQ)',
            ],
            'faq_desc' => [
                'group' => 'faq',
                'label' => 'Deskripsi Pengantar FAQ',
                'type'  => 'textarea',
                'value' => 'Informasi seputar sistem pendataan, validitas data, dan keanggotaan Pemuda MTA Perwakilan Sragen.',
            ],
            'faq_list' => [
                'group' => 'faq',
                'label' => 'Daftar Tanya Jawab FAQ (JSON)',
                'type'  => 'json',
                'value' => json_encode([
                    [
                        'q' => 'Siapa saja yang wajib mengisi formulir pendataan ini?',
                        'a' => "Seluruh pemuda dan pemudi warga binaan Majlis Tafsir Al-Qur'an (MTA) yang berdomisili atau beraktivitas di seluruh cabang se-Kabupaten Sragen diharapkan mengisi form pendataan ini.",
                    ],
                    [
                        'q' => 'Bagaimana jika saya belum mengetahui cabang MTA saya?',
                        'a' => 'Anda dapat memilih cabang terdekat dengan domisili tempat tinggal atau pengajian yang biasa Anda hadiri. Pada bagian Wilayah, sistem akan otomatis mengarahkan daftar cabang yang tersedia di kecamatan Anda.',
                    ],
                    [
                        'q' => 'Apakah data pribadi yang saya masukkan aman?',
                        'a' => 'Ya, sistem kami dirancang dengan standar keamanan tinggi dan akses bertingkat (Role Based Access Control). Data Anda hanya dapat diakses oleh pengurus cabang, admin wilayah, dan pengurus perwakilan yang berwenang untuk kepentingan pembinaan organisasi.',
                    ],
                    [
                        'q' => 'Apa fungsi Nomor Registrasi Pemuda?',
                        'a' => 'Nomor registrasi (contoh: 8601200005178234) adalah tanda bukti resmi bahwa profil Anda telah terdaftar dalam sistem induk Pemuda MTA Perwakilan Sragen dan dapat digunakan untuk verifikasi keikutsertaan kegiatan atau pelatihan.',
                    ],
                    [
                        'q' => 'Bisakah saya memperbarui data setelah mengirimkan formulir?',
                        'a' => 'Jika ada perubahan data penting (alamat, nomor HP, pekerjaan), Anda dapat menghubungi admin cabang atau admin wilayah setempat untuk dilakukan pembaruan data pada sistem dashboard.',
                    ],
                ], JSON_UNESCAPED_UNICODE),
            ],

            // 8. KONTAK & SEKRETARIAT
            'kontak_tag' => [
                'group' => 'kontak',
                'label' => 'Tag Section Kontak',
                'type'  => 'text',
                'value' => 'Layanan & Kontak',
            ],
            'kontak_title' => [
                'group' => 'kontak',
                'label' => 'Judul Section Kontak',
                'type'  => 'text',
                'value' => 'Sekretariat Pemuda MTA Sragen',
            ],
            'kontak_desc' => [
                'group' => 'kontak',
                'label' => 'Deskripsi Pengantar Kontak',
                'type'  => 'textarea',
                'value' => 'Butuh bantuan terkait pengisian data atau informasi kegiatan kepemudaan? Silakan hubungi kami.',
            ],
            'alamat_kantor' => [
                'group' => 'kontak',
                'label' => 'Alamat Lengkap Kantor Sekretariat',
                'type'  => 'textarea',
                'value' => "Gedung Perwakilan MTA Sragen\nJl. Raya Sukowati, Kabupaten Sragen, Jawa Tengah",
            ],
            'whatsapp_number' => [
                'group' => 'kontak',
                'label' => 'Nomor WhatsApp Helpdesk (Format: 628xxx)',
                'type'  => 'text',
                'value' => '6281234567890',
            ],
            'whatsapp_label' => [
                'group' => 'kontak',
                'label' => 'Label Keterangan WhatsApp',
                'type'  => 'text',
                'value' => 'Layanan Informasi & Helpdesk',
            ],

            // 8. AKSES GURU DAERAH
            'kode_akses_guru_daerah' => [
                'group' => 'guru_daerah',
                'label' => 'Kode Akses Pemantauan Guru Daerah',
                'type'  => 'text',
                'value' => 'GURUPMD',
            ],
        ];
    }

    public static function getAllSettings(): array
    {
        $defaults = static::getDefaults();
        $settings = [];

        foreach ($defaults as $key => $item) {
            $settings[$key] = $item['value'];
        }

        try {
            $records = static::all();
            foreach ($records as $row) {
                $settings[$row->key] = $row->value;
            }
        } catch (\Throwable $e) {
            // graceful fallback
        }

        return $settings;
    }

    public static function getSetting(string $key, $default = null)
    {
        try {
            $row = static::where('key', $key)->first();
            if ($row !== null) {
                return $row->value;
            }
        } catch (\Throwable $e) {
            // graceful fallback
        }

        $defaults = static::getDefaults();
        return $defaults[$key]['value'] ?? $default;
    }

    public static function setSetting(string $key, $value, string $group = 'general', string $type = 'text', ?string $label = null): bool
    {
        $existing = static::where('key', $key)->first();
        if ($existing) {
            $existing->value = $value;
            return $existing->save();
        }

        return (bool) static::create([
            'group' => $group,
            'key'   => $key,
            'value' => $value,
            'type'  => $type,
            'label' => $label ?: $key,
        ]);
    }

    public static function resetToDefaults(): bool
    {
        $defaults = static::getDefaults();
        foreach ($defaults as $key => $item) {
            static::setSetting($key, $item['value'], $item['group'], $item['type'], $item['label']);
        }
        return true;
    }
}
