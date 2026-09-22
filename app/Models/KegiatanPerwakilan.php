<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KegiatanPerwakilan extends Model
{
    use HasFactory;

    protected $table = 'kegiatan_perwakilan';

    protected $fillable = [
        'nama_kegiatan',
        'kategori',
        'tanggal',
        'hari_tanggal',
        'jam',
        'lokasi',
        'alamat_detail',
        'pemateri',
        'target_peserta',
        'penyelenggara',
        'deskripsi',
        'catatan_ketentuan',
        'narahubung',
        'status',
        'is_active',
        'urutan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'   => 'date',
            'is_active' => 'boolean',
            'urutan'    => 'integer',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Hitung sisa hari dari hari ini menuju tanggal kegiatan
     */
    public function getHariTersisaAttribute(): int
    {
        if (!$this->tanggal) {
            return 0;
        }

        $now = Carbon::today();
        $target = Carbon::parse($this->tanggal)->startOfDay();

        $diff = $now->diffInDays($target, false);
        return $diff > 0 ? (int)$diff : 0;
    }

    /**
     * Warna badge kategori
     */
    public function getKategoriBadgeAttribute(): string
    {
        return match (strtolower($this->kategori)) {
            'kajian akbar'       => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            'bakti dakwah'       => 'bg-teal-500/10 text-teal-400 border-teal-500/20',
            'diklat & pelatihan' => 'bg-sky-500/10 text-sky-400 border-sky-500/20',
            'olahraga'           => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            default              => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
        };
    }

    /**
     * Warna badge status
     */
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'Akan Datang'  => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
            'Segera'       => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            'Berlangsung'  => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            'Selesai'      => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
            default        => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
        };
    }

    /**
     * Seed default data bila tabel kosong
     */
    public static function seedDefaults(): void
    {
        if (static::count() > 0) {
            return;
        }

        $items = [
            [
                'nama_kegiatan'     => 'Kajian Akbar Pemuda & Pemudi Se-Perwakilan Sragen',
                'kategori'          => 'Kajian Akbar',
                'tanggal'           => '2026-10-11',
                'hari_tanggal'      => 'Ahad Wage, 11 Oktober 2026',
                'jam'               => '08.30 - 11.45 WIB',
                'lokasi'            => 'Gedung Dakwah Pusat MTA Perwakilan Sragen',
                'alamat_detail'     => 'Jl. Raya Sukowati No. 42, Kebakkramat, Kabupaten Sragen',
                'pemateri'          => 'Ustadz Pembina Pemuda & Pengurus Perwakilan MTA Sragen',
                'target_peserta'    => 'Seluruh Pemuda & Pemudi 70 Cabang se-Kabupaten Sragen',
                'penyelenggara'     => 'Bagian Kepemudaan MTA Perwakilan Sragen',
                'deskripsi'         => 'Kajian gabungan akbar yang mempertemukan seluruh generasi muda MTA se-Kabupaten Sragen. Membahas peneguhan aqidah tauhid, peran pemuda dalam dakwah kemasyarakatan, serta penguatan koordinasi antar cabang.',
                'catatan_ketentuan' => 'Wajib mengenakan busana muslim rapi/seragam kemeja pemuda, membawa mushaf Al-Qur\'an dan alat tulis. Diharapkan hadir 15 menit sebelum acara dimulai.',
                'narahubung'        => '0812-2983-4412 (Biro Dakwah Pemuda)',
                'status'            => 'Akan Datang',
                'is_active'         => true,
                'urutan'            => 1,
            ],
            [
                'nama_kegiatan'     => 'Apel Siaga & Kemah Dakwah Pemuda MTA Sragen 2026',
                'kategori'          => 'Bakti Dakwah',
                'tanggal'           => '2026-10-24',
                'hari_tanggal'      => 'Sabtu - Ahad, 24 - 25 Oktober 2026',
                'jam'               => 'Sabtu 15.00 WIB s/d Ahad 12.00 WIB',
                'lokasi'            => 'Bumi Perkemahan Lapangan Garuda, Gondang - Sragen',
                'alamat_detail'     => 'Kecamatan Gondang, Kabupaten Sragen',
                'pemateri'          => 'Tim Instruktur Kedisiplinan & Pimpinan Perwakilan MTA Sragen',
                'target_peserta'    => 'Delegasi 3-5 Orang Pemuda Pilihan dari Setiap Cabang',
                'penyelenggara'     => 'Korps Relawan & Kepemudaan MTA Perwakilan Sragen',
                'deskripsi'         => 'Kegiatan pembinaan fisik, mental, dan ruhiyah pemuda MTA dalam rangka kesiapsiagaan dakwah tanggap bencana dan bakti sosial masyarakat perdesaan.',
                'catatan_ketentuan' => 'Peserta membawa perlengkapan pribadi lapangan, matras/sleeping bag, sepatu olahraga, serta surat mandat dari pimpinan cabang masing-masing.',
                'narahubung'        => '0857-3312-9901 (Koor Relawan Lapangan)',
                'status'            => 'Akan Datang',
                'is_active'         => true,
                'urutan'            => 2,
            ],
            [
                'nama_kegiatan'     => 'Pelatihan Fardhu Kifayah & Perawatan Jenazah Sesuai Sunnah',
                'kategori'          => 'Diklat & Pelatihan',
                'tanggal'           => '2026-11-08',
                'hari_tanggal'      => 'Ahad Pon, 08 November 2026',
                'jam'               => '08.30 - 12.30 WIB',
                'lokasi'            => 'Masjid Dakwah MTA Cabang Sragen Kota',
                'alamat_detail'     => 'Jl. Kenanga No. 12, Kelurahan Sragen Wetan, Sragen Kota',
                'pemateri'          => 'Tim Bimbingan Fardhu Kifayah MTA Pusat & Perwakilan',
                'target_peserta'    => 'Utusan Petugas Cabang (Bidang Ibadah/Sosial)',
                'penyelenggara'     => 'Bidang Pembinaan Ibadah & Fardhu Kifayah Pemuda MTA Sragen',
                'deskripsi'         => 'Pelatihan teori dan praktik intensif tata cara pengurusan jenazah secara syar\'i, mulai dari memandikan, mengafani, menshalatkan hingga memakamkan sesuai tuntunan sunnah Rasulullah shallallahu \'alaihi wa sallam.',
                'catatan_ketentuan' => 'Setiap cabang mengirimkan minimal 2 orang delegasi (1 Pemuda dan 1 Pemudi untuk sesi terpisah).',
                'narahubung'        => '0813-8876-5543 (Sekretariat Diklat)',
                'status'            => 'Akan Datang',
                'is_active'         => true,
                'urutan'            => 3,
            ],
            [
                'nama_kegiatan'     => 'Turnamen Futsal & Badminton Ukhuwah Antar-Cabang Se-Sragen',
                'kategori'          => 'Olahraga',
                'tanggal'           => '2026-11-22',
                'hari_tanggal'      => 'Ahad Kliwon, 22 November 2026',
                'jam'               => '07.30 WIB - Selesai',
                'lokasi'            => 'Gelanggang Olahraga (GOR) Diponegoro Sragen',
                'alamat_detail'     => 'Kompleks Stadion Taruna, Jl. Diponegoro, Sragen',
                'pemateri'          => null,
                'target_peserta'    => 'Tim Cabang (Wilayah 1 s/d Wilayah 5 Sragen)',
                'penyelenggara'     => 'Biro Olahraga & Seni Pemuda MTA Sragen',
                'deskripsi'         => 'Ajang mempererat tali silaturahmi dan ukhuwah islamiyah antar pemuda cabang melalui olahraga yang sehat, sportif, dan berakhlakul karimah.',
                'catatan_ketentuan' => 'Kostum sopan menutup aurat (celana di bawah lutut). Menjunjung tinggi sportifitas tanpa fanatisme.',
                'narahubung'        => '0821-3456-7811 (Sie Olahraga)',
                'status'            => 'Akan Datang',
                'is_active'         => true,
                'urutan'            => 4,
            ],
            [
                'nama_kegiatan'     => 'Aksi Donor Darah & Layanan Cek Kesehatan Gratis',
                'kategori'          => 'Bakti Dakwah',
                'tanggal'           => '2026-12-06',
                'hari_tanggal'      => 'Ahad Legi, 06 Desember 2026',
                'jam'               => '08.00 - 12.00 WIB',
                'lokasi'            => 'Aula Gedung Perwakilan MTA Sragen Bekerjasama dengan PMI',
                'alamat_detail'     => 'Jl. Raya Sukowati No. 42, Sragen',
                'pemateri'          => 'Tim Medis Perwakilan MTA Sragen & PMI Sragen',
                'target_peserta'    => 'Warga & Pemuda MTA serta Masyarakat Umum',
                'penyelenggara'     => 'Biro Kesehatan & Tanggap Bencana Pemuda MTA Sragen',
                'deskripsi'         => 'Bakti sosial kemanusiaan donor darah rutin triwulanan dan pemeriksaan gula darah, asam urat, serta tensi gratis untuk warga.',
                'catatan_ketentuan' => 'Kondisi badan sehat, istirahat cukup pada malam hari minimal 6 jam, dan sudah sarapan pagi sebelum mendonor.',
                'narahubung'        => '0812-7711-4321 (Sie Sosial Medis)',
                'status'            => 'Akan Datang',
                'is_active'         => true,
                'urutan'            => 5,
            ],
        ];

        foreach ($items as $item) {
            static::create($item);
        }
    }
}
