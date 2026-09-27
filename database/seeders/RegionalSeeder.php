<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionalSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Provinces
        DB::table('provinces')->updateOrInsert(
            ['id' => 33],
            ['name' => 'Jawa Tengah']
        );

        // 2. Regencies
        DB::table('regencies')->updateOrInsert(
            ['id' => 3314],
            ['province_id' => 33, 'name' => 'Kabupaten Sragen']
        );
        DB::table('regencies')->updateOrInsert(
            ['id' => 3313],
            ['province_id' => 33, 'name' => 'Kabupaten Karanganyar']
        );

        // 3. Districts (Sragen: 1-20, Karanganyar: 21-37)
        $districts = [
            ['id' => 1, 'regency_id' => 3314, 'name' => 'Sragen'],
            ['id' => 2, 'regency_id' => 3314, 'name' => 'Karangmalang'],
            ['id' => 3, 'regency_id' => 3314, 'name' => 'Sidoharjo'],
            ['id' => 4, 'regency_id' => 3314, 'name' => 'Gemolong'],
            ['id' => 5, 'regency_id' => 3314, 'name' => 'Kalijambe'],
            ['id' => 6, 'regency_id' => 3314, 'name' => 'Plupuh'],
            ['id' => 7, 'regency_id' => 3314, 'name' => 'Masaran'],
            ['id' => 8, 'regency_id' => 3314, 'name' => 'Kedawung'],
            ['id' => 9, 'regency_id' => 3314, 'name' => 'Sambirejo'],
            ['id' => 10, 'regency_id' => 3314, 'name' => 'Gondang'],
            ['id' => 11, 'regency_id' => 3314, 'name' => 'Sambungmacan'],
            ['id' => 12, 'regency_id' => 3314, 'name' => 'Ngrampal'],
            ['id' => 13, 'regency_id' => 3314, 'name' => 'Tanon'],
            ['id' => 14, 'regency_id' => 3314, 'name' => 'Sumberlawang'],
            ['id' => 15, 'regency_id' => 3314, 'name' => 'Mondokan'],
            ['id' => 16, 'regency_id' => 3314, 'name' => 'Sukodono'],
            ['id' => 17, 'regency_id' => 3314, 'name' => 'Gesi'],
            ['id' => 18, 'regency_id' => 3314, 'name' => 'Tangen'],
            ['id' => 19, 'regency_id' => 3314, 'name' => 'Jenar'],
            ['id' => 20, 'regency_id' => 3314, 'name' => 'Miri'],
            ['id' => 21, 'regency_id' => 3313, 'name' => 'Colomadu'],
            ['id' => 22, 'regency_id' => 3313, 'name' => 'Gondangrejo'],
            ['id' => 23, 'regency_id' => 3313, 'name' => 'Jaten'],
            ['id' => 24, 'regency_id' => 3313, 'name' => 'Jatipuro'],
            ['id' => 25, 'regency_id' => 3313, 'name' => 'Jatiyoso'],
            ['id' => 26, 'regency_id' => 3313, 'name' => 'Jenawi'],
            ['id' => 27, 'regency_id' => 3313, 'name' => 'Jumapolo'],
            ['id' => 28, 'regency_id' => 3313, 'name' => 'Jumantono'],
            ['id' => 29, 'regency_id' => 3313, 'name' => 'Karanganyar'],
            ['id' => 30, 'regency_id' => 3313, 'name' => 'Karangpandan'],
            ['id' => 31, 'regency_id' => 3313, 'name' => 'Kebakkramat'],
            ['id' => 32, 'regency_id' => 3313, 'name' => 'Kerjo'],
            ['id' => 33, 'regency_id' => 3313, 'name' => 'Matesih'],
            ['id' => 34, 'regency_id' => 3313, 'name' => 'Mojogedang'],
            ['id' => 35, 'regency_id' => 3313, 'name' => 'Ngargoyoso'],
            ['id' => 36, 'regency_id' => 3313, 'name' => 'Tasikmadu'],
            ['id' => 37, 'regency_id' => 3313, 'name' => 'Tawangmangu'],
        ];

        foreach ($districts as $d) {
            DB::table('districts')->updateOrInsert(
                ['id' => $d['id']],
                ['regency_id' => $d['regency_id'], 'name' => $d['name']]
            );
        }

        // 4. Villages (Sragen: 208, Karanganyar: 177)
        $allVillages = [
            // Kabupaten Sragen
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

            // Kabupaten Karanganyar
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

        foreach ($allVillages as $districtId => $villages) {
            foreach ($villages as $villageName) {
                DB::table('villages')->updateOrInsert(
                    ['district_id' => $districtId, 'name' => $villageName],
                    []
                );
            }
        }
    }
}
