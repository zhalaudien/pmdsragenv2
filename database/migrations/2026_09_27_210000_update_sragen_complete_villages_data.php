<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Official 208 Kelurahan & Desa across 20 Kecamatan in Kabupaten Sragen.
     */
    protected array $officialVillages = [
        1 => ['Sragen Wetan', 'Sragen Kulon', 'Sragen Tengah', 'Nglorog', 'Sine', 'Karangtengah', 'Tangkil', 'Kedungupit'],
        2 => ['Kroyo', 'Plumbungan', 'Guworejo', 'Jurangjero', 'Kedungwaduk', 'Mojorejo', 'Pelemgadung', 'Plosokerep', 'Puro', 'Saradan'],
        3 => ['Sidoharjo', 'Jetak', 'Purwosuman', 'Patihan', 'Bentak', 'Duyungan', 'Sribit', 'Taraman', 'Tenggak', 'Jambanan', 'Pandak', 'Singopadu'],
        4 => ['Gemolong', 'Kwangen', 'Ngembatpadas', 'Kragilan', 'Jenalas', 'Kaloran', 'Purworejo', 'Peleman', 'Brangkal', 'Jatibatur', 'Nganti', 'Geneng Duwur', 'Kalangan', 'Tegaldowo'],
        5 => ['Banaran', 'Bukuran', 'Donoyudan', 'Jetiskarangpung', 'Kalimacan', 'Karangjati', 'Keden', 'Krikilan', 'Ngebung', 'Samberembe', 'Saren', 'Tegalombo', 'Trobayan', 'Wonorejo'],
        6 => ['Cangkol', 'Dari', 'Gedongan', 'Gentanbanaran', 'Jabung', 'Jembangan', 'Karanganyar', 'Karangwaru', 'Karungan', 'Manyarejo', 'Ngrombo', 'Plupuh', 'Pungsari', 'Sambirejo', 'Sidokerto', 'Somomorodukuh'],
        7 => ['Masaran', 'Dawungan', 'Gebang', 'Jati', 'Jirapan', 'Karangmalang', 'Kliwonan', 'Krebet', 'Krikilan', 'Pilang', 'Pringanom', 'Sepat', 'Sidodadi'],
        8 => ['Kedawung', 'Bendungan', 'Celep', 'Jenggrik', 'Karangpelem', 'Mojodoyong', 'Mojokerto', 'Pengkok', 'Wonokerso', 'Wonorejo'],
        9 => ['Sambirejo', 'Blimbing', 'Dawung', 'Jambeyan', 'Jetis', 'Kadipiro', 'Musuk', 'Sambi', 'Sukorejo'],
        10 => ['Gondang', 'Bumiaji', 'Glonggong', 'Kaliwedi', 'Plosorejo', 'Srimulyo', 'Tegalrejo', 'Tunggul', 'Wonotolo'],
        11 => ['Sambungmacan', 'Banaran', 'Banyuurip', 'Bedoro', 'Cemeng', 'Gringging', 'Karanganyar', 'Plumbon', 'Toyogo'],
        12 => ['Bandung', 'Bener', 'Gabus', 'Karangudi', 'Kebonromo', 'Klandungan', 'Ngarum', 'Pilangsari'],
        13 => ['Tanon', 'Bonagung', 'Gabugan', 'Gading', 'Gawan', 'Jono', 'Kalikobok', 'Karangtalun', 'Karangasem', 'Kecik', 'Ketro', 'Padas', 'Pengkol', 'Sambiduwur', 'Slogo', 'Suwatu'],
        14 => ['Cepoko', 'Hadiluwih', 'Jati', 'Kacangan', 'Mojopuro', 'Ngandul', 'Ngargosari', 'Ngargotirto', 'Pagak', 'Pendem', 'Tlogotirto'],
        15 => ['Gemantar', 'Jambangan', 'Jekani', 'Kedawung', 'Pare', 'Sono', 'Sumberejo', 'Tempelrejo', 'Trombol'],
        16 => ['Baleharjo', 'Bendo', 'Gebang', 'Jatitengah', 'Juwok', 'Karang Anom', 'Majenang', 'Newung', 'Pantirejo'],
        17 => ['Gesi', 'Blangu', 'Pilangsari', 'Poleng', 'Slendro', 'Srawung', 'Tanggan'],
        18 => ['Denanyar', 'Dukuh', 'Galeh', 'Jekawal', 'Katelan', 'Ngrombo', 'Sigit'],
        19 => ['Jenar', 'Banyuurip', 'Dawung', 'Japoh', 'Kandangsapi', 'Mlale', 'Ngepringan'],
        20 => ['Bagor', 'Brojol', 'Doyong', 'Geneng', 'Gilirejo', 'Gilirejo Baru', 'Girimargo', 'Jeruk', 'Soko', 'Sunggingan'],
    ];

    public function up(): void
    {
        // 1. Ensure Province exists
        DB::table('provinces')->updateOrInsert(
            ['id' => 33],
            ['name' => 'Jawa Tengah']
        );

        // 2. Ensure Regency exists
        DB::table('regencies')->updateOrInsert(
            ['id' => 3314],
            ['province_id' => 33, 'name' => 'Kabupaten Sragen']
        );

        // 3. Ensure all 20 Districts exist
        $districts = [
            1  => 'Sragen',
            2  => 'Karangmalang',
            3  => 'Sidoharjo',
            4  => 'Gemolong',
            5  => 'Kalijambe',
            6  => 'Plupuh',
            7  => 'Masaran',
            8  => 'Kedawung',
            9  => 'Sambirejo',
            10 => 'Gondang',
            11 => 'Sambungmacan',
            12 => 'Ngrampal',
            13 => 'Tanon',
            14 => 'Sumberlawang',
            15 => 'Mondokan',
            16 => 'Sukodono',
            17 => 'Gesi',
            18 => 'Tangen',
            19 => 'Jenar',
            20 => 'Miri',
        ];

        foreach ($districts as $districtId => $districtName) {
            DB::table('districts')->updateOrInsert(
                ['id' => $districtId],
                ['regency_id' => 3314, 'name' => $districtName]
            );
        }

        // 4. Safe relocations of misplaced villages if they exist and aren't already present in target district
        $relocations = [
            // [village_name, from_district, to_district]
            ['Kroyo', 1, 2],
            ['Tegaldowo', 5, 4],
            ['Jekawal', 15, 18],
        ];

        foreach ($relocations as [$vName, $fromDist, $toDist]) {
            $existing = DB::table('villages')->where('district_id', $fromDist)->where('name', $vName)->first();
            if ($existing) {
                $targetExists = DB::table('villages')->where('district_id', $toDist)->where('name', $vName)->exists();
                if (!$targetExists) {
                    DB::table('villages')->where('id', $existing->id)->update(['district_id' => $toDist]);
                }
            }
        }

        // Rename Tlogorejo to Tlogotirto in Sumberlawang (District 14) if needed
        $tlogorejo = DB::table('villages')->where('district_id', 14)->where('name', 'Tlogorejo')->first();
        if ($tlogorejo) {
            $tlogotirtoExists = DB::table('villages')->where('district_id', 14)->where('name', 'Tlogotirto')->exists();
            if (!$tlogotirtoExists) {
                DB::table('villages')->where('id', $tlogorejo->id)->update(['name' => 'Tlogotirto']);
            }
        }

        // Rename Banyurip to Banyuurip in Jenar (District 19) if needed
        $banyuripJenar = DB::table('villages')->where('district_id', 19)->where('name', 'Banyurip')->first();
        if ($banyuripJenar) {
            $banyuuripExists = DB::table('villages')->where('district_id', 19)->where('name', 'Banyuurip')->exists();
            if (!$banyuuripExists) {
                DB::table('villages')->where('id', $banyuripJenar->id)->update(['name' => 'Banyuurip']);
            }
        }

        // 5. Ensure all official 208 villages exist
        foreach ($this->officialVillages as $districtId => $villages) {
            foreach ($villages as $villageName) {
                $exists = DB::table('villages')
                    ->where('district_id', $districtId)
                    ->where('name', $villageName)
                    ->exists();

                if (!$exists) {
                    DB::table('villages')->insert([
                        'district_id' => $districtId,
                        'name'        => $villageName,
                    ]);
                }
            }
        }

        // 6. Safely remove obsolete / erroneous villages that are NOT in the official list AND NOT referenced in alamat
        $usedVillageIds = DB::table('alamat')
            ->whereNotNull('village_id')
            ->distinct()
            ->pluck('village_id')
            ->toArray();

        $allVillagesInDb = DB::table('villages')->get();
        foreach ($allVillagesInDb as $village) {
            $allowedForDistrict = $this->officialVillages[$village->district_id] ?? [];
            if (!in_array($village->name, $allowedForDistrict, true)) {
                // Only delete if NOT used in alamat
                if (!in_array($village->id, $usedVillageIds)) {
                    DB::table('villages')->where('id', $village->id)->delete();
                }
            }
        }
    }

    public function down(): void
    {
        // Reference data fix; down does not delete complete villages to prevent foreign key errors.
    }
};
