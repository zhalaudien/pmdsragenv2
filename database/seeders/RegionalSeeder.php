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

        // 3. Districts
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
        ];

        foreach ($districts as $d) {
            DB::table('districts')->updateOrInsert(
                ['id' => $d['id']],
                ['regency_id' => $d['regency_id'], 'name' => $d['name']]
            );
        }

        // 4. Villages
        $sragenVillages = [
            1 => ["Sragen Wetan", "Sragen Kulon", "Sragen Tengah", "Nglorog", "Sine", "Karangtengah", "Kroyo", "Tangkil"],
            2 => ["Kujon", "Plumbungan", "Puro", "Saradan", "Guworejo", "Mojorejo", "Jurangjero", "Pelemgadung", "Kedungwaduk", "Ngringkwit"],
            3 => ["Sidoharjo", "Jetak", "Purwosuman", "Patihan", "Bentak", "Duyungan", "Sribit", "Taraman", "Tenggak", "Jambanan", "Pandak", "Singopadu"],
            4 => ["Gemolong", "Kwangen", "Ngembatpadas", "Kragilan", "Jenalas", "Kaloran", "Purworejo", "Peleman", "Brangkal", "Tlogotirto", "Jatibatur", "Nganti", "Kalenan"],
            5 => ["Kalijambe", "Banaran", "Donoyudan", "Krikilan", "Ngetal", "Saren", "Tegaldowo", "Trobayan", "Wonorejo", "Bukuran", "Karangjati"],
            6 => ["Plupuh", "Dari", "Gedongan", "Gentanbanaran", "Jabung", "Karanganyar", "Karangwaru", "Krikil", "Manyarejo", "Ngrombo", "Padas", "Sambirejo", "Somomorodukuh"],
            7 => ["Masaran", "Dawungan", "Gebang", "Jati", "Karangmalang", "Kliwonan", "Krebet", "Pilang", "Pringanom", "Sepat", "Sidodadi"],
            8 => ["Kedawung", "Bendungan", "Celep", "Jatimulyo", "Karangpelem", "Mojokerto", "Pengkok", "Wonokerso", "Wonorejo"],
            9 => ["Sambirejo", "Blimbing", "Dawung", "Jambeyan", "Jetis", "Musuk", "Sukorejo"],
            10 => ["Gondang", "Banyurip", "Glonggong", "Kaliwedi", "Plosorejo", "Tegalrejo", "Tunggul", "Wonotolo"],
            11 => ["Sambungmacan", "Banaran", "Bedoro", "Cemeng", "Gringging", "Karanganyar", "Plumbon", "Toyogo"],
            12 => ["Ngrampal", "Bener", "Gabus", "Karangudi", "Kebonromo", "Klandungan", "Pilangsari", "Ngarum"],
            13 => ["Tanon", "Bonagung", "Gading", "Gentan", "Kalikobok", "Karangtalun", "Karangasem", "Ketro", "Padas", "Pengkol", "Sambiduwur", "Slogo", "Suwatu"],
            14 => ["Sumberlawang", "Cepoko", "Hadiluwih", "Jati", "Kacangan", "Mojopuro", "Ngandul", "Ngargosari", "Ngargotirto", "Pagak", "Pendem", "Tlogorejo"],
            15 => ["Mondokan", "Gemantar", "Jekawal", "Kedawung", "Pare", "Sono", "Sumberejo", "Tempelrejo", "Trombol"],
            16 => ["Sukodono", "Baleharjo", "Bendo", "Gebang", "Jatitengah", "Juwok", "Karang Anom", "Majenang", "Newung", "Pantirejo"],
            17 => ["Gesi", "Blangu", "Poleng", "Slendro", "Srawung", "Tanggan"],
            18 => ["Tangen", "Denanyar", "Dukuh", "Galeh", "Katelan", "Ngrombo", "Sigit"],
            19 => ["Jenar", "Banyurip", "Dawung", "Japoh", "Kandangsapi", "Mlale", "Ngepringan"],
            20 => ["Miri", "Bagor", "Doyong", "Geneng", "Girimargo", "Jeruk", "Soko", "Sunggingan", "Brojol"],
        ];

        $villageId = 1;
        foreach ($sragenVillages as $districtId => $villages) {
            foreach ($villages as $villageName) {
                DB::table('villages')->updateOrInsert(
                    ['id' => $villageId],
                    ['district_id' => $districtId, 'name' => $villageName]
                );
                $villageId++;
            }
        }
    }
}
