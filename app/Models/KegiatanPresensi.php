<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class KegiatanPresensi extends Model
{
    protected $table = 'kegiatan_presensi';

    protected $fillable = [
        'cabang_id',
        'nama_kegiatan',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'lokasi',
        'pemateri',
        'target_peserta',
        'status',
        'catatan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal'    => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function presensiDetails()
    {
        return $this->hasMany(PresensiDetail::class, 'kegiatan_presensi_id');
    }

    /**
     * Hitung ringkasan statistik kehadiran sesi ini
     */
    public function getRekapSummary(): array
    {
        // Total pemuda aktif di cabang ini
        $queryPemuda = Pemuda::where('cabang_id', $this->cabang_id)
            ->where('status_data', 'active');

        if ($this->target_peserta === 'pemuda') {
            $queryPemuda->where('gender', 'L');
        } elseif ($this->target_peserta === 'pemudi') {
            $queryPemuda->where('gender', 'P');
        }

        $totalPemuda = $queryPemuda->count();

        // Hitung presensi yang sudah tercatat
        $details = $this->presensiDetails()->with('pemuda:id,name,gender')->get();

        $hadir = $details->where('status_kehadiran', 'hadir')->count();
        $izin  = $details->where('status_kehadiran', 'izin')->count();
        $sakit = $details->where('status_kehadiran', 'sakit')->count();
        $alpaRecorded = $details->where('status_kehadiran', 'alpa')->count();

        // Alpa adalah selisih atau yang tercatat
        $belumTercatat = max(0, $totalPemuda - ($hadir + $izin + $sakit + $alpaRecorded));
        $totalAlpa = $alpaRecorded + $belumTercatat;

        $persentaseHadir = $totalPemuda > 0 ? round(($hadir / $totalPemuda) * 100, 1) : 0;

        $daftarIzin = $details->where('status_kehadiran', 'izin')->map(function ($item) {
            return [
                'pemuda_id'  => $item->pemuda_id,
                'name'       => $item->pemuda?->name ?? 'Tanpa Nama',
                'keterangan' => $item->keterangan ?: 'Tanpa keterangan',
            ];
        })->values()->toArray();

        $daftarSakit = $details->where('status_kehadiran', 'sakit')->map(function ($item) {
            return [
                'pemuda_id'  => $item->pemuda_id,
                'name'       => $item->pemuda?->name ?? 'Tanpa Nama',
                'keterangan' => $item->keterangan ?: 'Tanpa keterangan',
            ];
        })->values()->toArray();

        return [
            'total_pemuda'     => $totalPemuda,
            'hadir'            => $hadir,
            'izin'             => $izin,
            'sakit'            => $sakit,
            'alpa'             => $totalAlpa,
            'belum_presensi'   => $belumTercatat,
            'persentase_hadir' => $persentaseHadir,
            'daftar_izin'      => $daftarIzin,
            'daftar_sakit'     => $daftarSakit,
        ];
    }

    /**
     * Generate teks rekap WhatsApp standar sesuai spesifikasi presensi_pmd.md
     */
    public function generateWhatsAppText(): string
    {
        $rekap = $this->getRekapSummary();
        $cabangName = strtoupper($this->cabang?->name ?? 'CABANG');

        $tglCarbon = Carbon::parse($this->tanggal)->locale('id');
        $hariTanggal = $tglCarbon->translatedFormat('l, d F Y');

        $lines = [];
        $lines[] = "*LAPORAN PRESENSI PEMUDA CABANG {$cabangName}*";
        $lines[] = "Kegiatan: " . ($this->nama_kegiatan ?? 'Pengajian Rutin');
        $lines[] = "Hari/Tanggal: " . $hariTanggal;
        if (!empty($this->jam_mulai)) {
            $jam = substr($this->jam_mulai, 0, 5) . (!empty($this->jam_selesai) ? ' - ' . substr($this->jam_selesai, 0, 5) : ' WIB');
            $lines[] = "Waktu: " . $jam;
        }
        if (!empty($this->lokasi)) {
            $lines[] = "Tempat: " . $this->lokasi;
        }
        if (!empty($this->pemateri)) {
            $lines[] = "Pemateri: " . $this->pemateri;
        }

        $lines[] = "";
        $lines[] = "*REKAP KEHADIRAN:*";
        $lines[] = "- Total Pemuda: {$rekap['total_pemuda']} orang";
        $lines[] = "- Hadir: {$rekap['hadir']} orang ({$rekap['persentase_hadir']}%)";
        $lines[] = "- Izin: {$rekap['izin']} orang";
        $lines[] = "- Sakit: {$rekap['sakit']} orang";
        $lines[] = "- Alpa: {$rekap['alpa']} orang";

        if (!empty($rekap['daftar_izin'])) {
            $lines[] = "";
            $lines[] = "*DAFTAR IZIN:*";
            foreach ($rekap['daftar_izin'] as $idx => $item) {
                $no = $idx + 1;
                $lines[] = "{$no}. {$item['name']} ({$item['keterangan']})";
            }
        }

        if (!empty($rekap['daftar_sakit'])) {
            $lines[] = "";
            $lines[] = "*DAFTAR SAKIT:*";
            foreach ($rekap['daftar_sakit'] as $idx => $item) {
                $no = $idx + 1;
                $lines[] = "{$no}. {$item['name']} ({$item['keterangan']})";
            }
        }

        $pencatat = $this->creator?->name ?? 'Sekretaris Cabang';
        $lines[] = "";
        $lines[] = "Pencatat: " . $pencatat;

        return implode("\n", $lines);
    }
}
