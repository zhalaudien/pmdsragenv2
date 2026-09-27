<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 17 Kecamatan in Kabupaten Karanganyar (ID: 3313).
     */
    protected array $karanganyarDistricts = [
        21 => 'Colomadu',
        22 => 'Gondangrejo',
        23 => 'Jaten',
        24 => 'Jatipuro',
        25 => 'Jatiyoso',
        26 => 'Jenawi',
        27 => 'Jumapolo',
        28 => 'Jumantono',
        29 => 'Karanganyar',
        30 => 'Karangpandan',
        31 => 'Kebakkramat',
        32 => 'Kerjo',
        33 => 'Matesih',
        34 => 'Mojogedang',
        35 => 'Ngargoyoso',
        36 => 'Tasikmadu',
        37 => 'Tawangmangu',
    ];

    /**
     * 177 Desa & Kelurahan across 17 Kecamatan in Kabupaten Karanganyar.
     */
    protected array $karanganyarVillages = [
        21 => ['Baturan', 'Blulukan', 'Bolon', 'Gajahan', 'Gawanan', 'Gedongan', 'Klodran', 'Malangjiwan', 'Ngasem', 'Paulan', 'Tohudan'],
        22 => ['Bulurejo', 'Dayu', 'Jatikuwung', 'Jeruksawit', 'Karangturi', 'Kragan', 'Krendowahono', 'Plesungan', 'Rejosari', 'Selokaton', 'Tuban', 'Wonorejo', 'Wonosari'],
        23 => ['Brujul', 'Dagen', 'Jaten', 'Jati', 'Jetis', 'Ngringo', 'Sroyo', 'Suruhkalang'],
        24 => ['Jatiharjo', 'Jatikuwung', 'Jatimulyo', 'Jatipuro', 'Jatipurwo', 'Jatiroyo', 'Jatisobo', 'Jatisuko', 'Jatiwarno', 'Ngepungsari'],
        25 => ['Beruk', 'Jatisawit', 'Jatiyoso', 'Karangsari', 'Petung', 'Tlobo', 'Wonokeling', 'Wonorejo', 'Wukirsawit'],
        26 => ['Anggrasmanis', 'Balong', 'Gumeng', 'Jenawi', 'Lempong', 'Menjing', 'Seloromo', 'Sidomukti', 'Trengguli'],
        27 => ['Bakalan', 'Giriwondo', 'Jatirejo', 'Jumantoro', 'Jumapolo', 'Kadipiro', 'Karangbangun', 'Kedawung', 'Kwangsan', 'Lemahbang', 'Paseban', 'Ploso'],
        28 => ['Blorong', 'Gemantar', 'Genengan', 'Kebak', 'Ngunut', 'Sambirejo', 'Sedayu', 'Sringin', 'Sukosari', 'Tugu', 'Tunggulrejo'],
        29 => ['Bejen', 'Bolong', 'Cangakan', 'Delingan', 'Gayamdompo', 'Gedong', 'Jantiharjo', 'Jungke', 'Karanganyar', 'Lalung', 'Popongan', 'Tegalgede'],
        30 => ['Bangsri', 'Dayu', 'Doplang', 'Gerdu', 'Gondangmanis', 'Harjosari', 'Karang', 'Karangpandan', 'Ngemplak', 'Salam', 'Tohkuning'],
        31 => ['Alastuwo', 'Banjarharjo', 'Kaliwuluh', 'Kebak', 'Kemiri', 'Macanan', 'Malanggaten', 'Nangsri', 'Pulosari', 'Waru'],
        32 => ['Botok', 'Ganten', 'Gempolan', 'Karangrejo', 'Kuto', 'Kwadungan', 'Plosorejo', 'Sumberejo', 'Tamansari', 'Tawangsari'],
        33 => ['Dawung', 'Gantiwarno', 'Girilayu', 'Karangbangun', 'Koripan', 'Matesih', 'Ngadiluwih', 'Pablengan', 'Plosorejo'],
        34 => ['Buntar', 'Gebyok', 'Gentungan', 'Kaliboto', 'Kedungjeruk', 'Mojogedang', 'Mojoroto', 'Munggur', 'Ngadirejo', 'Pendem', 'Pereng', 'Pojok', 'Sewurejo'],
        35 => ['Berjo', 'Dukuh', 'Girimulyo', 'Jatirejo', 'Kemuning', 'Ngargoyoso', 'Nglegok', 'Puntukrejo', 'Segorogunung'],
        36 => ['Buran', 'Gaum', 'Kalijirak', 'Kaling', 'Karangmojo', 'Ngijo', 'Pandeyan', 'Papahan', 'Suruh', 'Wonolopo'],
        37 => ['Bandardawung', 'Blumbang', 'Gondosuli', 'Kalisoro', 'Karanglo', 'Nglebak', 'Plumbon', 'Sepanjang', 'Tawangmangu', 'Tengklik'],
    ];

    public function up(): void
    {
        // 1. Ensure Regency Kabupaten Karanganyar exists
        DB::table('regencies')->updateOrInsert(
            ['id' => 3313],
            ['province_id' => 33, 'name' => 'Kabupaten Karanganyar']
        );

        // 2. Insert/Update Districts
        foreach ($this->karanganyarDistricts as $districtId => $districtName) {
            DB::table('districts')->updateOrInsert(
                ['id' => $districtId],
                ['regency_id' => 3313, 'name' => $districtName]
            );
        }

        // 3. Insert/Update Villages
        foreach ($this->karanganyarVillages as $districtId => $villages) {
            foreach ($villages as $villageName) {
                DB::table('villages')->updateOrInsert(
                    ['district_id' => $districtId, 'name' => $villageName],
                    []
                );
            }
        }
    }

    public function down(): void
    {
        $districtIds = array_keys($this->karanganyarDistricts);

        // Check if any village or district is used in alamat
        $usedVillageIds = DB::table('alamat')->whereNotNull('village_id')->pluck('village_id')->toArray();
        $usedDistrictIds = DB::table('alamat')->whereNotNull('district_id')->pluck('district_id')->toArray();

        // Delete unused Karanganyar villages
        DB::table('villages')
            ->whereIn('district_id', $districtIds)
            ->whereNotIn('id', $usedVillageIds)
            ->delete();

        // Delete unused Karanganyar districts
        DB::table('districts')
            ->whereIn('id', $districtIds)
            ->whereNotIn('id', $usedDistrictIds)
            ->delete();

        // Only delete regency if no alamat references 3313
        $usedRegency = DB::table('alamat')->where('regency_id', 3313)->exists();
        if (!$usedRegency) {
            DB::table('regencies')->where('id', 3313)->delete();
        }
    }
};
