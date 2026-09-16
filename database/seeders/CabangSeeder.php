<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CabangSeeder extends Seeder
{
    public function run(): void
    {
        $cabangList = array (
  0 => 
  array (
    'id' => 1,
    'wilayah_id' => 1,
    'code' => '86.6',
    'name' => 'Gesi',
    'description' => 'Cabang Gesi (Wilayah 1)',
    'mta_uuid' => '9f7ade17-70c6-4d3a-a4c5-5eefa60bdabe',
    'has_gelombang' => 'belum',
  ),
  1 => 
  array (
    'id' => 2,
    'wilayah_id' => 1,
    'code' => '86.10',
    'name' => 'Jenar',
    'description' => 'Cabang Jenar (Wilayah 1)',
    'mta_uuid' => '4c64e3ff-77c3-41d6-bbbf-6df45d6ce34d',
    'has_gelombang' => 'belum',
  ),
  2 => 
  array (
    'id' => 3,
    'wilayah_id' => 1,
    'code' => '86.29',
    'name' => 'Mondokan 1',
    'description' => 'Cabang Mondokan 1 (Wilayah 1)',
    'mta_uuid' => '62fecca1-af23-4f97-8e53-7bd29ba77003',
    'has_gelombang' => 'belum',
  ),
  3 => 
  array (
    'id' => 4,
    'wilayah_id' => 1,
    'code' => '86.30',
    'name' => 'Mondokan 2',
    'description' => 'Cabang Mondokan 2 (Wilayah 1)',
    'mta_uuid' => '7925be11-289c-4051-a6dc-f487d742b43e',
    'has_gelombang' => 'belum',
  ),
  4 => 
  array (
    'id' => 5,
    'wilayah_id' => 1,
    'code' => '86.31',
    'name' => 'Mondokan 3',
    'description' => 'Cabang Mondokan 3 (Wilayah 1)',
    'mta_uuid' => 'a84976a4-b534-47a6-8e78-dd14f802d576',
    'has_gelombang' => 'belum',
  ),
  5 => 
  array (
    'id' => 6,
    'wilayah_id' => 1,
    'code' => '86.49',
    'name' => 'Sukodono 1',
    'description' => 'Cabang Sukodono 1 (Wilayah 1)',
    'mta_uuid' => '36dbcb9b-202c-4a00-bfb1-8bf4a3b76454',
    'has_gelombang' => 'belum',
  ),
  6 => 
  array (
    'id' => 7,
    'wilayah_id' => 1,
    'code' => '86.50',
    'name' => 'Sukodono 2',
    'description' => 'Cabang Sukodono 2 (Wilayah 1)',
    'mta_uuid' => '1488671c-3974-479f-a825-4549bc8c159c',
    'has_gelombang' => 'belum',
  ),
  7 => 
  array (
    'id' => 8,
    'wilayah_id' => 1,
    'code' => '86.51',
    'name' => 'Sukodono 3',
    'description' => 'Cabang Sukodono 3 (Wilayah 1)',
    'mta_uuid' => '7d2f56d5-9628-401a-82c7-369b35055bb8',
    'has_gelombang' => 'belum',
  ),
  8 => 
  array (
    'id' => 9,
    'wilayah_id' => 1,
    'code' => '86.64',
    'name' => 'Sukodono 4',
    'description' => 'Cabang Sukodono 4 (Wilayah 1)',
    'mta_uuid' => '7fe11ef8-cede-477d-a33f-f2d43c5b5042',
    'has_gelombang' => 'belum',
  ),
  9 => 
  array (
    'id' => 10,
    'wilayah_id' => 1,
    'code' => '86.52',
    'name' => 'Sumberlawang 1',
    'description' => 'Cabang Sumberlawang 1 (Wilayah 1)',
    'mta_uuid' => '4199fee0-2c36-4a3d-a037-672f45c1340b',
    'has_gelombang' => 'belum',
  ),
  10 => 
  array (
    'id' => 11,
    'wilayah_id' => 1,
    'code' => '86.53',
    'name' => 'Sumberlawang 2',
    'description' => 'Cabang Sumberlawang 2 (Wilayah 1)',
    'mta_uuid' => '9e2347c4-045a-45bf-9dcd-11a262b4669d',
    'has_gelombang' => 'belum',
  ),
  11 => 
  array (
    'id' => 12,
    'wilayah_id' => 1,
    'code' => '86.54',
    'name' => 'Sumberlawang 3',
    'description' => 'Cabang Sumberlawang 3 (Wilayah 1)',
    'mta_uuid' => '18599313-38cd-4c9a-8385-2e8187d9167b',
    'has_gelombang' => 'belum',
  ),
  12 => 
  array (
    'id' => 13,
    'wilayah_id' => 1,
    'code' => '86.67',
    'name' => 'Sumberlawang 4',
    'description' => 'Cabang Sumberlawang 4 (Wilayah 1)',
    'mta_uuid' => '05c85557-8513-48cf-a06e-5b70b505fdbf',
    'has_gelombang' => 'belum',
  ),
  13 => 
  array (
    'id' => 14,
    'wilayah_id' => 1,
    'code' => '86.55',
    'name' => 'Tangen 1',
    'description' => 'Cabang Tangen 1 (Wilayah 1)',
    'mta_uuid' => '5aaefa3f-6893-4316-8b6c-f891d8af30ac',
    'has_gelombang' => 'belum',
  ),
  14 => 
  array (
    'id' => 15,
    'wilayah_id' => 1,
    'code' => '86.60',
    'name' => 'Tangen 2',
    'description' => 'Cabang Tangen 2 (Wilayah 1)',
    'mta_uuid' => 'ac576b67-8297-4bc2-ad28-7e047922bdef',
    'has_gelombang' => 'belum',
  ),
  15 => 
  array (
    'id' => 16,
    'wilayah_id' => 1,
    'code' => '86.56',
    'name' => 'Tanon 1',
    'description' => 'Cabang Tanon 1 (Wilayah 1)',
    'mta_uuid' => '8964f353-c628-412b-ab91-25fc0f2305f7',
    'has_gelombang' => 'belum',
  ),
  16 => 
  array (
    'id' => 17,
    'wilayah_id' => 1,
    'code' => '86.57',
    'name' => 'Tanon 2',
    'description' => 'Cabang Tanon 2 (Wilayah 1)',
    'mta_uuid' => 'd52f13b5-1d1b-4041-8f01-1aa4d288b05d',
    'has_gelombang' => 'belum',
  ),
  17 => 
  array (
    'id' => 18,
    'wilayah_id' => 1,
    'code' => '86.58',
    'name' => 'Tanon 3',
    'description' => 'Cabang Tanon 3 (Wilayah 1)',
    'mta_uuid' => 'ed5e631c-5ded-48cc-aec6-c0a05128b1ed',
    'has_gelombang' => 'belum',
  ),
  18 => 
  array (
    'id' => 19,
    'wilayah_id' => 2,
    'code' => '86.1',
    'name' => 'Gemolong 1',
    'description' => 'Cabang Gemolong 1 (Wilayah 2)',
    'mta_uuid' => '04442a46-7c20-475c-bb90-b9169a17cf6e',
    'has_gelombang' => 'belum',
  ),
  19 => 
  array (
    'id' => 20,
    'wilayah_id' => 2,
    'code' => '86.2',
    'name' => 'Gemolong 2',
    'description' => 'Cabang Gemolong 2 (Wilayah 2)',
    'mta_uuid' => 'b6250d27-6b4c-43f8-9bdf-64c578e6aa86',
    'has_gelombang' => 'belum',
  ),
  20 => 
  array (
    'id' => 21,
    'wilayah_id' => 2,
    'code' => '86.3',
    'name' => 'Gemolong 3',
    'description' => 'Cabang Gemolong 3 (Wilayah 2)',
    'mta_uuid' => '58557ab6-be68-4b4d-a867-4918e397c67c',
    'has_gelombang' => 'belum',
  ),
  21 => 
  array (
    'id' => 22,
    'wilayah_id' => 2,
    'code' => '86.4',
    'name' => 'Gemolong 4',
    'description' => 'Cabang Gemolong 4 (Wilayah 2)',
    'mta_uuid' => '6f834d29-a26b-41cb-8e0f-148e0242675b',
    'has_gelombang' => 'belum',
  ),
  22 => 
  array (
    'id' => 23,
    'wilayah_id' => 2,
    'code' => '86.5',
    'name' => 'Gemolong 5',
    'description' => 'Cabang Gemolong 5 (Wilayah 2)',
    'mta_uuid' => '05492929-86e0-4b86-81a0-ca3128d95b60',
    'has_gelombang' => 'belum',
  ),
  23 => 
  array (
    'id' => 24,
    'wilayah_id' => 2,
    'code' => '86.11',
    'name' => 'Kalijambe 1',
    'description' => 'Cabang Kalijambe 1 (Wilayah 2)',
    'mta_uuid' => '99d964ae-5bc2-47ae-8284-7328deb3de90',
    'has_gelombang' => 'belum',
  ),
  24 => 
  array (
    'id' => 25,
    'wilayah_id' => 2,
    'code' => '86.12',
    'name' => 'Kalijambe 2',
    'description' => 'Cabang Kalijambe 2 (Wilayah 2)',
    'mta_uuid' => '82cc0d53-2958-4c53-9ed7-3d44558ed1e4',
    'has_gelombang' => 'belum',
  ),
  25 => 
  array (
    'id' => 26,
    'wilayah_id' => 2,
    'code' => '86.59',
    'name' => 'Kalijambe 3',
    'description' => 'Cabang Kalijambe 3 (Wilayah 2)',
    'mta_uuid' => '0bad09f9-7692-4bf9-b511-d20d6f713653',
    'has_gelombang' => 'belum',
  ),
  26 => 
  array (
    'id' => 27,
    'wilayah_id' => 2,
    'code' => '86.63',
    'name' => 'Kalijambe 4',
    'description' => 'Cabang Kalijambe 4 (Wilayah 2)',
    'mta_uuid' => 'df547f3f-24df-43f0-803f-96f08cd6c072',
    'has_gelombang' => 'belum',
  ),
  27 => 
  array (
    'id' => 28,
    'wilayah_id' => 2,
    'code' => '86.27',
    'name' => 'Miri 1',
    'description' => 'Cabang Miri 1 (Wilayah 2)',
    'mta_uuid' => '42dce2e4-3250-416d-a4ef-d32b4e450100',
    'has_gelombang' => 'belum',
  ),
  28 => 
  array (
    'id' => 29,
    'wilayah_id' => 2,
    'code' => '86.28',
    'name' => 'Miri 2',
    'description' => 'Cabang Miri 2 (Wilayah 2)',
    'mta_uuid' => '0751a380-08d5-4e78-909d-b13615bcc30c',
    'has_gelombang' => 'belum',
  ),
  29 => 
  array (
    'id' => 30,
    'wilayah_id' => 2,
    'code' => '86.34',
    'name' => 'Plupuh 1',
    'description' => 'Cabang Plupuh 1 (Wilayah 2)',
    'mta_uuid' => 'c71cab70-f154-46b7-8050-fa603275d8c6',
    'has_gelombang' => 'belum',
  ),
  30 => 
  array (
    'id' => 31,
    'wilayah_id' => 2,
    'code' => '86.35',
    'name' => 'Plupuh 2',
    'description' => 'Cabang Plupuh 2 (Wilayah 2)',
    'mta_uuid' => '91d366db-c4b9-46a1-ad9a-d03e3a99e2f9',
    'has_gelombang' => 'belum',
  ),
  31 => 
  array (
    'id' => 32,
    'wilayah_id' => 2,
    'code' => '86.36',
    'name' => 'Plupuh 3',
    'description' => 'Cabang Plupuh 3 (Wilayah 2)',
    'mta_uuid' => '7c87f754-3401-4625-bfb9-2cfc00594fa1',
    'has_gelombang' => 'belum',
  ),
  32 => 
  array (
    'id' => 33,
    'wilayah_id' => 2,
    'code' => '86.37',
    'name' => 'Plupuh 4',
    'description' => 'Cabang Plupuh 4 (Wilayah 2)',
    'mta_uuid' => 'cf005c50-89b9-40d3-a742-c65f030c4b38',
    'has_gelombang' => 'belum',
  ),
  33 => 
  array (
    'id' => 34,
    'wilayah_id' => 2,
    'code' => '86.38',
    'name' => 'Plupuh 5',
    'description' => 'Cabang Plupuh 5 (Wilayah 2)',
    'mta_uuid' => '6c465474-c967-4dd4-987e-64b31dafc4eb',
    'has_gelombang' => 'belum',
  ),
  34 => 
  array (
    'id' => 35,
    'wilayah_id' => 2,
    'code' => '86.69',
    'name' => 'Plupuh 6',
    'description' => 'Cabang Plupuh 6 (Wilayah 2)',
    'mta_uuid' => '9f18c25e-9e44-4db6-9fec-4081192b1b95',
    'has_gelombang' => 'belum',
  ),
  35 => 
  array (
    'id' => 36,
    'wilayah_id' => 3,
    'code' => '86.13',
    'name' => 'Karangmalang 1',
    'description' => 'Cabang Karangmalang 1 (Wilayah 3)',
    'mta_uuid' => '4bd8abe0-48ff-4582-99b6-748f8358bd3f',
    'has_gelombang' => 'belum',
  ),
  36 => 
  array (
    'id' => 37,
    'wilayah_id' => 3,
    'code' => '86.14',
    'name' => 'Karangmalang 2',
    'description' => 'Cabang Karangmalang 2 (Wilayah 3)',
    'mta_uuid' => 'f7f2c1a1-5086-4840-9677-20389105962f',
    'has_gelombang' => 'belum',
  ),
  37 => 
  array (
    'id' => 38,
    'wilayah_id' => 3,
    'code' => '86.15',
    'name' => 'Karangmalang 3',
    'description' => 'Cabang Karangmalang 3 (Wilayah 3)',
    'mta_uuid' => '5aefe6f2-fe44-4f04-a1d6-96d3b04a6b8a',
    'has_gelombang' => 'belum',
  ),
  38 => 
  array (
    'id' => 39,
    'wilayah_id' => 3,
    'code' => '86.16',
    'name' => 'Karangmalang 4',
    'description' => 'Cabang Karangmalang 4 (Wilayah 3)',
    'mta_uuid' => 'f2a9ab6d-de50-4dea-a72f-445aa3b26732',
    'has_gelombang' => 'belum',
  ),
  39 => 
  array (
    'id' => 40,
    'wilayah_id' => 3,
    'code' => '86.66',
    'name' => 'Karangmalang 5',
    'description' => 'Cabang Karangmalang 5 (Wilayah 3)',
    'mta_uuid' => '1da48f1c-0782-4c94-b80c-022f4604e8a7',
    'has_gelombang' => 'belum',
  ),
  40 => 
  array (
    'id' => 41,
    'wilayah_id' => 3,
    'code' => '86.21',
    'name' => 'Masaran 1',
    'description' => 'Cabang Masaran 1 (Wilayah 3)',
    'mta_uuid' => '6fc86c75-5156-497e-b944-cb2494e7546e',
    'has_gelombang' => 'belum',
  ),
  41 => 
  array (
    'id' => 42,
    'wilayah_id' => 3,
    'code' => '86.22',
    'name' => 'Masaran 2',
    'description' => 'Cabang Masaran 2 (Wilayah 3)',
    'mta_uuid' => '53292c5e-fa35-4d2a-a4af-8f4792602c1c',
    'has_gelombang' => 'belum',
  ),
  42 => 
  array (
    'id' => 43,
    'wilayah_id' => 3,
    'code' => '86.23',
    'name' => 'Masaran 3',
    'description' => 'Cabang Masaran 3 (Wilayah 3)',
    'mta_uuid' => '6e14d879-d5d3-4982-8aff-a617b80bb2ce',
    'has_gelombang' => 'belum',
  ),
  43 => 
  array (
    'id' => 44,
    'wilayah_id' => 3,
    'code' => '86.24',
    'name' => 'Masaran 4',
    'description' => 'Cabang Masaran 4 (Wilayah 3)',
    'mta_uuid' => 'c6242522-6f26-403d-8b77-794f14d65461',
    'has_gelombang' => 'belum',
  ),
  44 => 
  array (
    'id' => 45,
    'wilayah_id' => 3,
    'code' => '86.25',
    'name' => 'Masaran 5',
    'description' => 'Cabang Masaran 5 (Wilayah 3)',
    'mta_uuid' => '818e1cfb-1adf-4ea7-b924-7f90e483f511',
    'has_gelombang' => 'belum',
  ),
  45 => 
  array (
    'id' => 46,
    'wilayah_id' => 3,
    'code' => '86.26',
    'name' => 'Masaran 6',
    'description' => 'Cabang Masaran 6 (Wilayah 3)',
    'mta_uuid' => 'c79f90f1-70a5-4f21-ae3e-feed38dab4b8',
    'has_gelombang' => 'belum',
  ),
  46 => 
  array (
    'id' => 47,
    'wilayah_id' => 3,
    'code' => '86.41',
    'name' => 'Sambungmacan 1',
    'description' => 'Cabang Sambungmacan 1 (Wilayah 3)',
    'mta_uuid' => '2c6c5033-e242-48aa-8b0b-d0382e15d33e',
    'has_gelombang' => 'belum',
  ),
  47 => 
  array (
    'id' => 48,
    'wilayah_id' => 3,
    'code' => '86.42',
    'name' => 'Sambungmacan 2',
    'description' => 'Cabang Sambungmacan 2 (Wilayah 3)',
    'mta_uuid' => 'd081acdd-1af7-462c-b8a6-b2a44c0d969f',
    'has_gelombang' => 'belum',
  ),
  48 => 
  array (
    'id' => 49,
    'wilayah_id' => 3,
    'code' => '86.62',
    'name' => 'Sambungmacan 3',
    'description' => 'Cabang Sambungmacan 3 (Wilayah 3)',
    'mta_uuid' => '0f3bcf6b-9dde-4bd6-a176-afbd69212a13',
    'has_gelombang' => 'belum',
  ),
  49 => 
  array (
    'id' => 50,
    'wilayah_id' => 3,
    'code' => '86.43',
    'name' => 'Sidoharjo 1',
    'description' => 'Cabang Sidoharjo 1 (Wilayah 3)',
    'mta_uuid' => '463c9579-bcf2-497f-a3a5-14af686f9b46',
    'has_gelombang' => 'belum',
  ),
  50 => 
  array (
    'id' => 51,
    'wilayah_id' => 3,
    'code' => '86.44',
    'name' => 'Sidoharjo 2',
    'description' => 'Cabang Sidoharjo 2 (Wilayah 3)',
    'mta_uuid' => '6fd79428-46b9-4cf3-b40a-ed1eae638b56',
    'has_gelombang' => 'belum',
  ),
  51 => 
  array (
    'id' => 52,
    'wilayah_id' => 3,
    'code' => '86.45',
    'name' => 'Sidoharjo 3',
    'description' => 'Cabang Sidoharjo 3 (Wilayah 3)',
    'mta_uuid' => 'fb3e5ecd-0370-4801-b1c7-8d5ee6da6d86',
    'has_gelombang' => 'belum',
  ),
  52 => 
  array (
    'id' => 53,
    'wilayah_id' => 3,
    'code' => '86.46',
    'name' => 'Sidoharjo 4',
    'description' => 'Cabang Sidoharjo 4 (Wilayah 3)',
    'mta_uuid' => '6d8cb227-3720-488c-ba22-8acb77201dc6',
    'has_gelombang' => 'belum',
  ),
  53 => 
  array (
    'id' => 54,
    'wilayah_id' => 3,
    'code' => '86.47',
    'name' => 'Sragen 1',
    'description' => 'Cabang Sragen 1 (Wilayah 3)',
    'mta_uuid' => '550d3726-dc32-48c6-b31e-d0c726cbafee',
    'has_gelombang' => 'belum',
  ),
  54 => 
  array (
    'id' => 55,
    'wilayah_id' => 3,
    'code' => '86.48',
    'name' => 'Sragen 2',
    'description' => 'Cabang Sragen 2 (Wilayah 3)',
    'mta_uuid' => 'f0a0ffc2-70d0-489a-b6ae-135cb7499d6f',
    'has_gelombang' => 'belum',
  ),
  55 => 
  array (
    'id' => 56,
    'wilayah_id' => 4,
    'code' => '86.7',
    'name' => 'Gondang 1',
    'description' => 'Cabang Gondang 1 (Wilayah 4)',
    'mta_uuid' => '370c65c2-5816-4e3b-9c53-6f0bcdca1c4e',
    'has_gelombang' => 'belum',
  ),
  56 => 
  array (
    'id' => 57,
    'wilayah_id' => 4,
    'code' => '86.8',
    'name' => 'Gondang 2',
    'description' => 'Cabang Gondang 2 (Wilayah 4)',
    'mta_uuid' => 'd60d7135-c669-40ab-abd7-a5644850278d',
    'has_gelombang' => 'belum',
  ),
  57 => 
  array (
    'id' => 58,
    'wilayah_id' => 4,
    'code' => '86.9',
    'name' => 'Gondang 3',
    'description' => 'Cabang Gondang 3 (Wilayah 4)',
    'mta_uuid' => '5dd73fb8-0353-471b-a25c-37971837b4f7',
    'has_gelombang' => 'belum',
  ),
  58 => 
  array (
    'id' => 59,
    'wilayah_id' => 4,
    'code' => '86.68',
    'name' => 'Gondang 4',
    'description' => 'Cabang Gondang 4 (Wilayah 4)',
    'mta_uuid' => 'd06ec130-fd9f-44a3-a7fc-3b2d03aaca88',
    'has_gelombang' => 'belum',
  ),
  59 => 
  array (
    'id' => 60,
    'wilayah_id' => 4,
    'code' => '86.17',
    'name' => 'Kedawung 1',
    'description' => 'Cabang Kedawung 1 (Wilayah 4)',
    'mta_uuid' => '343efee2-9ed8-4d48-81db-c0368987498a',
    'has_gelombang' => 'belum',
  ),
  60 => 
  array (
    'id' => 61,
    'wilayah_id' => 4,
    'code' => '86.18',
    'name' => 'Kedawung 2',
    'description' => 'Cabang Kedawung 2 (Wilayah 4)',
    'mta_uuid' => '40f366db-f1f8-47a3-9ae6-b63114247774',
    'has_gelombang' => 'belum',
  ),
  61 => 
  array (
    'id' => 62,
    'wilayah_id' => 4,
    'code' => '86.19',
    'name' => 'Kedawung 3',
    'description' => 'Cabang Kedawung 3 (Wilayah 4)',
    'mta_uuid' => '7957fef7-f09e-4343-ac72-89b26493375b',
    'has_gelombang' => 'belum',
  ),
  62 => 
  array (
    'id' => 63,
    'wilayah_id' => 4,
    'code' => '86.20',
    'name' => 'Kedawung 4',
    'description' => 'Cabang Kedawung 4 (Wilayah 4)',
    'mta_uuid' => '6320667c-68aa-40be-9e1f-4a14f7486394',
    'has_gelombang' => 'belum',
  ),
  63 => 
  array (
    'id' => 64,
    'wilayah_id' => 4,
    'code' => '86.65',
    'name' => 'Kedawung 5',
    'description' => 'Cabang Kedawung 5 (Wilayah 4)',
    'mta_uuid' => '51454eb5-b84a-49a6-a594-afe8c113b5a3',
    'has_gelombang' => 'belum',
  ),
  64 => 
  array (
    'id' => 65,
    'wilayah_id' => 4,
    'code' => '86.32',
    'name' => 'Ngrampal 1',
    'description' => 'Cabang Ngrampal 1 (Wilayah 4)',
    'mta_uuid' => '4003d421-88d9-4340-bdb9-8d4a47963bed',
    'has_gelombang' => 'belum',
  ),
  65 => 
  array (
    'id' => 66,
    'wilayah_id' => 4,
    'code' => '86.33',
    'name' => 'Ngrampal 2',
    'description' => 'Cabang Ngrampal 2 (Wilayah 4)',
    'mta_uuid' => '39711581-2519-406c-a7a6-6b9b5f760117',
    'has_gelombang' => 'belum',
  ),
  66 => 
  array (
    'id' => 67,
    'wilayah_id' => 4,
    'code' => '86.61',
    'name' => 'Ngrampal 3',
    'description' => 'Cabang Ngrampal 3 (Wilayah 4)',
    'mta_uuid' => '1de8ea2b-b1a5-4f19-81e5-6644daf0b788',
    'has_gelombang' => 'belum',
  ),
  67 => 
  array (
    'id' => 68,
    'wilayah_id' => 4,
    'code' => '86.39',
    'name' => 'Sambirejo 1',
    'description' => 'Cabang Sambirejo 1 (Wilayah 4)',
    'mta_uuid' => '7edaa422-fe88-46f5-b28d-320145132b6e',
    'has_gelombang' => 'belum',
  ),
  68 => 
  array (
    'id' => 69,
    'wilayah_id' => 4,
    'code' => '86.40',
    'name' => 'Sambirejo 2',
    'description' => 'Cabang Sambirejo 2 (Wilayah 4)',
    'mta_uuid' => '4050cd44-72e0-4dab-bd99-dc7a35ee52fd',
    'has_gelombang' => 'belum',
  ),
  69 => 
  array (
    'id' => 261,
    'wilayah_id' => 1,
    'code' => '86.0',
    'name' => 'Sragen Perwakilan',
    'description' => 'Cabang Sragen Perwakilan (Wilayah 1)',
    'mta_uuid' => 'e8a82b2c-77ec-41c8-b40e-c6ede00fd8b5',
    'has_gelombang' => 'belum',
  ),
);

        foreach ($cabangList as $item) {
            DB::table("cabang")->updateOrInsert(
                ["id" => $item["id"]],
                [
                    "wilayah_id"    => $item["wilayah_id"],
                    "code"          => $item["code"],
                    "name"          => $item["name"],
                    "description"   => $item["description"],
                    "mta_uuid"      => $item["mta_uuid"],
                    "has_gelombang" => $item["has_gelombang"] ?? "belum",
                    "updated_at"    => now(),
                ]
            );
        }
    }
}