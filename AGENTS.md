# AGENTS.md — Sistem Pendataan Pemuda

## 1. Ringkasan Project

Project ini adalah aplikasi web **Sistem Pendataan Pemuda** yang berfungsi untuk:

- Mengumpulkan data pemuda melalui form online.
- Mengelola data pemuda dari dashboard admin.
- Membagi pemuda berdasarkan **Wilayah** dan **Cabang**.
- Menyediakan hak akses bertingkat:
  - `superadmin`
  - `admin_wilayah`
  - `admin_cabang`
- Menyediakan pencarian, filter, verifikasi, statistik, dan laporan.
- Menyediakan export data, terutama Excel/CSV.
- Mendukung form dinamis seperti Google Forms pada tahap pengembangan berikutnya.

Project menggunakan:

- **Backend:** CodeIgniter 4
- **Language:** PHP 8.2+
- **Database:** MySQL
- **Frontend:** HTML5, CSS, JavaScript
- **UI:** Bootstrapm 5 AdminLte3
- **Chart:** Chart.js
- **Export:** PhpSpreadsheet
- **Database access:** CodeIgniter Model / Query Builder
- **Authentication:** CodeIgniter Session
- **Time Zone:** Asia/Jakarta

---

## 2. Prinsip Utama Pengembangan

### 2.1 Gunakan CodeIgniter 4 secara native

Utamakan fitur bawaan CodeIgniter 4:

- Controllers
- Models
- Views
- Filters
- Validation
- Migrations
- Seeders
- Query Builder
- Sessions
- Routes
- Services

Jangan membuat framework atau abstraction layer sendiri jika CodeIgniter 4 sudah menyediakan solusinya.

### 2.2 Gunakan MVC dengan jelas

Pisahkan tanggung jawab:

- **Controller:** menerima request, validasi/alur proses, memanggil model/service, mengembalikan response.
- **Model:** akses dan operasi database.
- **View:** tampilan HTML.
- **Filter:** authentication dan authorization.
- **Migration:** struktur database.
- **Seeder:** data awal/reference data.

Jangan menaruh query database kompleks langsung di View.

### 2.3 Database-first dan migration-first

Semua perubahan struktur database wajib dilakukan melalui migration.

Jangan mengandalkan perubahan manual pada database development/production.

Setiap tabel baru atau perubahan struktur harus memiliki migration CodeIgniter 4 yang jelas.

---

# 3. Struktur Organisasi Data

Struktur hierarki utama:

```text
Superadmin
    |
    +-- Wilayah 1
    |      +-- Cabang A
    |      +-- Cabang B
    |
    +-- Wilayah 2
    |      +-- Cabang C
    |      +-- Cabang D
    |
    +-- Wilayah 3
    |
    +-- Wilayah 4
```

Setiap pemuda berada pada satu cabang:

```text
Pemuda
  -> Cabang
      -> Wilayah
```

**Jangan menyimpan `wilayah_id` pada tabel `pemuda`** jika wilayah dapat ditentukan dari `cabang_id`. Hal ini mencegah inkonsistensi data.

---

# 4. Role dan Authorization

Role utama:

| Role                   | Scope               | Gender Filter                 |
| ---------------------- | ------------------- | ----------------------------- |
| `superadmin`           | Seluruh sistem      | Semua (Laki-laki & Perempuan) |
| `admin_pemuda`         | Seluruh Sragen      | Khusus Laki-laki (`L`)        |
| `admin_pemudi`         | Seluruh Sragen      | Khusus Perempuan (`P`)        |
| `admin_wilayah`        | Satu wilayah        | Semua (Laki-laki & Perempuan) |
| `admin_wilayah_pemuda` | Satu wilayah        | Khusus Laki-laki (`L`)        |
| `admin_cabang`         | Satu cabang         | Semua (Laki-laki & Perempuan) |

## 4.1 Superadmin

Superadmin mengelola seluruh sistem dan data:

- Mengelola seluruh wilayah.
- Mengelola seluruh cabang.
- Mengelola seluruh pemuda.
- Mengelola user/admin.
- Mengelola form.
- Melihat seluruh statistik.
- Import Data Pemuda dari excel.
- Export seluruh data.
- Mengubah konfigurasi sistem.

Pada tabel `users`:

```text
role_id    -> role superadmin (1)
wilayah_id -> NULL
cabang_id  -> NULL
```

## 4.2 Admin Pemuda & Admin Pemudi (Tingkat Kabupaten / Seluruh Sragen)

- **`admin_pemuda`**: Administrator seluruh Sragen yang mengelola/menghandle data pemuda (Laki-laki / `gender = 'L'`).
- **`admin_pemudi`**: Administrator seluruh Sragen yang mengelola/menghandle data pemudi (Perempuan / `gender = 'P'`).

Pada tabel `users`:

```text
role_id    -> admin_pemuda (4) / admin_pemudi (5)
wilayah_id -> NULL
cabang_id  -> NULL
```

Scope data:

```text
users (admin_pemuda / admin_pemudi)
    |
    +-- Seluruh Wilayah & Cabang di Sragen (bisa filter cabang & wilayah manapun)
          |
          +-- pemuda (filtered by gender: 'L' untuk admin_pemuda, 'P' untuk admin_pemudi)
```

## 4.3 Admin Wilayah & Admin Wilayah Pemuda

- **`admin_wilayah`**: mengelola seluruh data pemuda (Laki-laki & Perempuan) pada wilayahnya.
- **`admin_wilayah_pemuda`**: mengelola hanya data pemuda berjenis kelamin Laki-laki (`gender = 'L'`) pada wilayahnya.

Pada tabel `users`:

```text
role_id    -> admin_wilayah (2) / admin_wilayah_pemuda (6)
wilayah_id -> wilayah yang dikelola
cabang_id  -> NULL
```

Scope data:

```text
users.wilayah_id
    |
    +-- cabang
          |
          +-- pemuda (filtered by gender for admin_wilayah_pemuda)
```

## 4.4 Admin Cabang

- **`admin_cabang`**: manajemen data pada cabang tersebut (mengelola seluruh data pemuda & pemudi berjenis kelamin Laki-laki maupun Perempuan pada cabang yang dikelolanya).

Pada tabel `users`:

```text
role_id    -> admin_cabang (3)
wilayah_id -> wilayah cabang tersebut
cabang_id  -> cabang yang dikelola
```

Scope data:

```text
users.cabang_id
    |
    +-- pemuda (seluruh pemuda & pemudi pada cabang tersebut)
```

## 4.4 Authorization wajib dilakukan di server

Jangan hanya menyembunyikan menu berdasarkan role.

Contoh yang tidak cukup:

```php
if ($userRole === 'admin_cabang') {
    // hide menu
}
```

Data juga harus dibatasi pada query/database layer.

Admin cabang tidak boleh dapat mengakses:

```text
/pemuda/123
```

jika ID 123 bukan milik cabangnya, walaupun URL tersebut diketahui.

---

# 5. Struktur Database

Tabel utama yang direncanakan:

```text
user_roles
users

wilayah
cabang

provinces
regencies
districts
villages

pemuda
alamat
pendidikan
pekerjaan
organisasi

skills
interests
pemuda_skills
pemuda_interests

education_levels
job_statuses

forms
questions
responses
answers
```

## 5.1 Relasi wilayah dan cabang

```text
wilayah
    1
    |
    N
cabang
    1
    |
    N
pemuda
```

### 5.1.1 Detail Data Cabang

Tabel `cabang` menyimpan data struktural dan operasional setiap cabang pemuda:

- `id`: INT UNSIGNED AUTO_INCREMENT
- `wilayah_id`: INT UNSIGNED (FK ke `wilayah.id`)
- `code`: VARCHAR(50) (Kode cabang, misal: CBG-001)
- `name`: VARCHAR(100) (Nama cabang)
- `description`: TEXT (Deskripsi/catatan cabang)
- `alamat`: TEXT (Alamat lengkap/sekretariat cabang)
- `maps_url`: VARCHAR(500) (Tautan/link Google Maps atau lokasi cabang)
- `pimpinan_nama`: VARCHAR(100) (Nama pimpinan cabang)
- `no_wa`: VARCHAR(20) (Nomor WhatsApp/kontak pimpinan)
- `has_gelombang`: ENUM('sudah', 'belum') (Status ketersediaan gelombang pemuda)
- `gelombang_hari`: VARCHAR(100) (Hari pelaksanaan pengajian/gelombang pemuda)
- `gelombang_jam`: VARCHAR(50) (Waktu/jam masuk pelaksanaan kegiatan)
- `gelombang_ustadz`: VARCHAR(150) (Nama ustadz yang mengampu)

## 5.2 Relasi user

```text
user_roles
    1
    |
    N
users
```

User memiliki scope:

```text
users.wilayah_id
users.cabang_id
```

## 5.3 Data pemuda

`pemuda` adalah tabel utama data individu.

Informasi tambahan dipisahkan:

```text
pemuda
    |
    +-- alamat
    +-- pendidikan
    +-- pekerjaan
    +-- organisasi
    +-- pemuda_skills
    +-- pemuda_interests
```

---

# 6. Aturan Database

## 6.1 Primary Key

Gunakan:

```sql
INT UNSIGNED AUTO_INCREMENT
```

untuk primary key tabel utama, kecuali ada alasan kuat untuk menggunakan tipe lain.

## 6.2 Foreign Key

Semua relasi penting wajib menggunakan foreign key.

Contoh:

```text
cabang.wilayah_id -> wilayah.id
pemuda.cabang_id  -> cabang.id
users.role_id     -> user_roles.id
```

## 6.3 Index

Kolom yang sering digunakan untuk:

- filter
- search
- join
- authorization scope

harus memiliki index yang sesuai.

Contoh:

```text
pemuda.cabang_id
pemuda.status_verifikasi
pemuda.status_data
cabang.wilayah_id
users.role_id
users.wilayah_id
users.cabang_id
```

## 6.4 Unique

Gunakan unique constraint untuk identifier yang memang harus unik:

```text
users.username
users.email
wilayah.code
cabang.code
pemuda.registration_number
```

---

# 7. Data Sensitif

Data seperti:

- nomor HP
- email
- alamat

harus diperlakukan sebagai data sensitif.

Aturan:

1. Jangan mencatat password dalam log.
2. Gunakan HTTPS pada production.
3. Password wajib menggunakan hashing.
4. Jangan pernah menyimpan password plaintext.
5. Batasi akses data berdasarkan role dan scope.
6. Jangan mengirim seluruh data pribadi ke frontend jika tidak diperlukan.

---

# 8. Authentication

Authentication menggunakan session CodeIgniter 4.

Password:

```php
password_hash($password, PASSWORD_DEFAULT)
```

dan verifikasi:

```php
password_verify($password, $hash)
```

Jangan membuat algoritma hashing password sendiri.

Setelah login, session minimal menyimpan informasi yang diperlukan:

```text
user_id
role
wilayah_id
cabang_id
is_logged_in
```

Jangan menyimpan password dalam session.

---

# 9. Route dan Filter

Gunakan route group untuk area admin.

Contoh konsep:

```php
$routes->group('admin', ['filter' => 'auth'], function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('pemuda', 'Pemuda::index');
});
```

Authorization dapat menggunakan filter khusus:

```text
auth
role
scope
```

Contoh:

```text
/admin/dashboard
/admin/pemuda
/admin/cabang
/admin/wilayah
/admin/users
/admin/laporan
```

Area public:

```text
/
/pendataan
/pendataan/simpan
/pendataan/sukses
```

---

# 10. Form Pendataan Public

Pemuda tidak perlu login untuk mengisi form public, kecuali kebutuhan bisnis berubah.

Alur:

```text
Landing Page
    |
    v
Form Pendataan
    |
    v
Validasi
    |
    v
Simpan Transaction
    |
    +-- pemuda
    +-- alamat
    +-- pendidikan
    +-- pekerjaan
    +-- organisasi
    +-- skills
    +-- interests
    |
    v
Nomor Registrasi
    |
    v
Halaman Sukses
```

Penyimpanan beberapa tabel harus menggunakan database transaction.

Contoh:

```php
$db->transStart();

// insert pemuda
// insert alamat
// insert pendidikan
// insert pekerjaan
// insert organisasi
// insert skills
// insert interests

$db->transComplete();
```

Jika salah satu proses gagal, seluruh transaksi harus rollback.

---

# 11. Validasi

Semua input public dan admin harus divalidasi server-side.

Jangan hanya mengandalkan:

```html
required
```

di HTML.

Validasi harus dilakukan di backend.

Contoh:

```php
$rules = [
    'name' => 'required|min_length[3]|max_length[150]',
    'gender' => 'required|in_list[L,P]',
    'phone' => 'permit_empty|max_length[20]',
];
```

Untuk data cabang:

```text
cabang_id harus valid
cabang_id harus aktif
cabang_id harus berada pada wilayah yang sesuai
```

Admin cabang tidak boleh memanipulasi request untuk memasukkan pemuda ke cabang lain.

---

# 12. Query Scope

Ini adalah aturan penting.

## Superadmin

Tidak ada filter scope.

```php
$query = $this->pemudaModel;
```

## Admin Wilayah

Filter berdasarkan:

```php
->where('cabang.wilayah_id', session('wilayah_id'))
```

## Admin Cabang

Filter berdasarkan:

```php
->where('pemuda.cabang_id', session('cabang_id'))
```

Jangan hanya mengambil `pemuda` berdasarkan ID lalu memeriksa scope setelah data diambil jika hal tersebut berpotensi menyebabkan kebocoran data.

Lebih baik scope menjadi bagian query.

---

# 13. Model

Gunakan model terpisah.

Contoh:

```text
UserRoleModel
UserModel

WilayahModel
CabangModel

PemudaModel
AlamatModel
PendidikanModel
PekerjaanModel
OrganisasiModel

SkillModel
InterestModel
PemudaSkillModel
PemudaInterestModel

EducationLevelModel
JobStatusModel

FormModel
QuestionModel
ResponseModel
AnswerModel
```

Model harus mendefinisikan:

```php
protected $table;
protected $primaryKey;
protected $allowedFields;
protected $useTimestamps;
```

Jangan menggunakan:

```php
$builder->set($request->getPost());
```

secara mentah tanpa whitelist field.

---

# 14. Controller

Controller harus tetap tipis.

Contoh alur:

```text
Controller
    |
    +-- validasi request
    |
    +-- authorization/scope
    |
    +-- panggil model/service
    |
    +-- response/redirect
```

Hindari controller dengan ratusan baris query database.

Jika proses mulai kompleks, pindahkan business logic ke Service.

Contoh:

```text
app/Services/
    PemudaService.php
    AuthService.php
    FormService.php
    ReportService.php
```

---

# 15. UI/UX

Gunakan Bootstrap 5.

Prioritas:

1. Mobile responsive.
2. Form mudah diisi.
3. Dashboard mudah dibaca.
4. Tabel memiliki search dan filter.
5. Gunakan modal untuk operasi ringan.
6. Berikan confirmation sebelum delete/archive.
7. Gunakan alert/toast untuk feedback.
8. Jangan membuat form terlalu padat.

Form pendataan sebaiknya menggunakan section:

```text
Data Pribadi
Alamat
Pendidikan
Pekerjaan
Organisasi
Keahlian
Minat
Konfirmasi
```

---

# 16. Status Data Pemuda

Gunakan:

```text
status_verifikasi:
    verified (Terverifikasi — jika data sinkron/tercatat di database MTA Pusat)
    pending  (Belum Terverifikasi — jika data belum sinkron/tidak tercatat di database MTA Pusat)
```

Aturan Ketat Status Verifikasi:
1. Status verifikasi **HANYA ADA 2**: `verified` (Terverifikasi) dan `pending` (Belum Terverifikasi).
2. Status verifikasi ditentukan secara otomatis oleh sistem berdasarkan hasil sinkronisasi dengan API MTA Pusat.
3. Status verifikasi **TIDAK DAPAT diubah secara manual**, baik oleh superadmin, admin wilayah, maupun admin cabang.

dan:

```text
status_data:
    active
    archived
```

`archived` lebih disukai daripada hard delete untuk data operasional yang masih perlu dipertahankan.

Delete permanen hanya boleh dilakukan oleh role yang berwenang dan harus dipertimbangkan dengan kebijakan retensi data.

---

# 17. Form Dinamis

Fitur form dinamis direncanakan menggunakan:

```text
forms
    |
    +-- questions
             |
             +-- responses
                    |
                    +-- answers
```

Jenis pertanyaan:

```text
text
textarea
number
date
radio
checkbox
select
file
```

`questions.options` dapat menggunakan JSON untuk pilihan.

Contoh:

```json
["Olahraga", "Seni", "Teknologi", "Wirausaha"]
```

---

# 18. Naming Convention

## PHP class

Gunakan PascalCase:

```text
PemudaModel
WilayahModel
CabangController
```

## Method

Gunakan camelCase:

```php
getByCabang()
getByWilayah()
savePemuda()
```

## Database

Gunakan snake_case:

```text
registration_number
wilayah_id
cabang_id
status_verifikasi
created_at
```

## Table

Gunakan lowercase snake_case:

```text
pemuda
wilayah
cabang
user_roles
pemuda_skills
```

---

# 19. Migration

Migration harus:

- dapat dijalankan dari database kosong.
- memiliki `up()`.
- memiliki `down()`.
- membuat foreign key.
- membuat index.
- tidak bergantung pada data manual di database.

Urutan migration harus memperhatikan dependency.

Contoh:

```text
001 user_roles
002 wilayah
003 cabang
004 users
005 provinces
006 regencies
007 districts
008 villages
009 education_levels
010 job_statuses
011 skills
012 interests
013 pemuda
014 alamat
015 pendidikan
016 pekerjaan
017 organisasi
018 pemuda_skills
019 pemuda_interests
020 forms
021 questions
022 responses
023 answers
```

Jika migration digabung menjadi satu file, tetap pastikan urutan pembuatan tabel benar.

---

# 20. Seeder

Seeder digunakan untuk data awal/reference.

Minimal:

```text
UserRoleSeeder
WilayahSeeder
EducationLevelSeeder
JobStatusSeeder
SkillSeeder
InterestSeeder
```

Wilayah awal berjumlah 4:

```text
W01
W02
W03
W04
```

Nama wilayah dapat diubah sesuai struktur organisasi sebenarnya.

Jangan membuat password admin default yang permanen pada production.

---

# 21. Error Handling

Jangan menampilkan:

```text
SQL error
stack trace
database credentials
file path
```

kepada user production.

Gunakan halaman/error response yang sesuai.

Untuk development, error detail boleh diaktifkan.

---

# 22. Security Checklist

Sebelum production:

- [ ] `.env` tidak masuk repository.
- [ ] Password menggunakan hashing.
- [ ] CSRF protection aktif untuk form yang sesuai.
- [ ] Session dikonfigurasi dengan aman.
- [ ] Input divalidasi server-side.
- [ ] Output HTML di-escape.
- [ ] Query menggunakan Query Builder/parameter binding.
- [ ] Authorization diterapkan di server.
- [ ] Scope wilayah/cabang diterapkan pada query.
- [ ] Upload file divalidasi tipe dan ukurannya.
- [ ] HTTPS digunakan.
- [ ] Database user production memiliki privilege minimum.
- [ ] Backup database tersedia.

---

# 23. Testing

Minimal test untuk:

### Authentication

- Login valid.
- Login password salah.
- User nonaktif tidak dapat login.
- Logout.

### Authorization

- Superadmin dapat melihat semua data.
- Admin wilayah hanya dapat melihat wilayahnya.
- Admin cabang hanya dapat melihat cabangnya.
- Admin cabang tidak dapat mengakses cabang lain melalui URL/API.

### Pendataan

- Form valid dapat disimpan.
- Form invalid ditolak.
- Transaction rollback jika insert gagal.
- Registration number unik.

### CRUD

- Create.
- Read.
- Update.
- Archive.
- Restore jika fitur tersedia.

---

# 24. Laporan

Laporan harus mengikuti scope user.

Contoh:

```text
Superadmin
    -> semua wilayah

Admin Wilayah
    -> wilayah sendiri

Admin Cabang
    -> cabang sendiri
```

Jangan membuat endpoint export yang mengabaikan authorization.

Contoh buruk:

```text
/admin/export-all
```

yang dapat diakses semua role.

Export harus menggunakan scope yang sama dengan halaman data.

---

# 25. Dashboard

Dashboard menampilkan statistik sesuai scope.

Superadmin:

```text
Total Pemuda
Total Wilayah
Total Cabang
Pemuda per Wilayah
Pemuda per Cabang
Pendidikan
Pekerjaan
Gender
```

Admin Wilayah:

```text
Total Pemuda Wilayah
Total Cabang
Pemuda per Cabang
Pendidikan
Pekerjaan
Gender
```

Admin Cabang:

```text
Total Pemuda Cabang
Pendidikan
Pekerjaan
Gender
Keahlian
Minat
```

---

# 26. API / AJAX

Untuk dependent dropdown alamat:

```text
Provinsi
    ↓
Kabupaten
    ↓
Kecamatan
    ↓
Desa
```

Gunakan endpoint terpisah.

Contoh:

```text
GET /api/regencies/{provinceId}
GET /api/districts/{regencyId}
GET /api/villages/{districtId}
```

Untuk cabang:

```text
GET /api/cabang/by-wilayah/{wilayahId}
```

Semua endpoint tetap harus memiliki validation dan authorization yang sesuai.

---

# 27. Workflow Pengembangan

Urutan pengembangan yang disarankan:

```text
1. Database
   ↓
2. Migration
   ↓
3. Seeder
   ↓
4. Models
   ↓
5. Authentication
   ↓
6. Authorization / Scope
   ↓
7. Admin Dashboard
   ↓
8. CRUD Wilayah
   ↓
9. CRUD Cabang
   ↓
10. CRUD Pemuda
   ↓
11. Form Public
   ↓
12. Verifikasi
   ↓
13. Laporan
   ↓
14. Export
   ↓
15. Form Builder
```

Jangan mengembangkan form builder terlebih dahulu sebelum CRUD data utama dan authorization stabil.

---

# 28. Git Workflow

Gunakan branch berdasarkan fitur:

```text
main
develop
feature/auth
feature/wilayah-cabang
feature/pemuda
feature/form-pendataan
feature/report
```

Commit harus jelas:

```text
feat: add wilayah and cabang management
feat: add youth registration form
fix: restrict admin cabang data scope
refactor: extract pemuda service
```

Jangan commit:

```text
.env
writable/logs/*
database credentials
password
API keys
```

---

# 29. Definition of Done

Sebuah fitur dianggap selesai jika:

- [ ] Database migration tersedia jika diperlukan.
- [ ] Seeder tersedia jika diperlukan.
- [ ] Model tersedia.
- [ ] Validation tersedia.
- [ ] Authorization tersedia.
- [ ] Scope wilayah/cabang diperiksa.
- [ ] UI responsive.
- [ ] Error handling tersedia.
- [ ] Tidak ada data sensitif di log.
- [ ] Test dasar tersedia.
- [ ] Tidak merusak fitur existing.

---

# 30. Aturan Khusus untuk Agent/AI Coding

Saat mengerjakan project ini:

1. **Baca `AGENTS.md` sebelum mengubah kode.**
2. Jangan mengubah arsitektur utama tanpa alasan.
3. Jangan membuat tabel baru jika relasi yang diperlukan sudah dapat ditangani tabel existing.
4. Jangan menambahkan `wilayah_id` ke `pemuda` tanpa alasan kuat; wilayah diturunkan dari `cabang_id`.
5. Jangan bypass authorization.
6. Jangan mengandalkan UI untuk security.
7. Jangan menulis password plaintext.
8. Jangan menggunakan `SELECT *` jika hanya beberapa kolom diperlukan untuk response sensitif.
9. Gunakan transaction untuk operasi multi-tabel.
10. Semua perubahan database harus melalui migration.
11. Gunakan seeders untuk reference data.
12. Jangan menghapus data production secara permanen tanpa mekanisme dan konfirmasi yang sesuai.
13. Pertahankan backward compatibility jika memungkinkan.
14. Jika requirement ambigu dan dapat memengaruhi database/security, jelaskan asumsi sebelum melakukan perubahan besar.
15. Untuk perubahan besar, kerjakan secara bertahap dan pastikan setiap tahap tetap dapat dijalankan.
16. Jangan memasukkan dependency baru jika fitur dapat dibuat menggunakan CodeIgniter 4 atau dependency yang sudah ada.
17. Prioritaskan keamanan, integritas data, dan authorization dibanding kemudahan implementasi sementara.

---

# 31. Target Arsitektur Akhir

```text
                    SISTEM PENDATAAN PEMUDA
                              |
             +----------------+----------------+
             |                                 |
          PUBLIC                            ADMIN
             |                                 |
       Form Pendataan                    Authentication
             |                                 |
             v                                 v
       +-----------+                    +-------------+
       |  PEMUDA   |                    |   RBAC      |
       +-----+-----+                    +------+------+
             |                                 |
             v                         +-------+-------+
        CABANG                        |       |       |
             |                    Superadmin Wilayah Cabang
             v
        WILAYAH
             |
             v
        DATABASE
             |
    +--------+--------+
    |        |        |
 Alamat Pendidikan Pekerjaan
    |
 Organisasi / Skill / Minat
             |
             v
        REPORTING
             |
       +-----+-----+
       |           |
     Excel       Dashboard
```

## Prioritas implementasi

**Phase 1 — Fondasi**

- Migration
- Seeder
- Models
- Authentication
- Role
- Wilayah
- Cabang

**Phase 2 — Pendataan**

- CRUD Pemuda
- Alamat
- Pendidikan
- Pekerjaan
- Organisasi
- Skill
- Minat

**Phase 3 — Public Form**

- Form pendataan
- Validasi
- Transaction
- Nomor registrasi
- Halaman sukses

**Phase 4 — Dashboard**

- Statistik
- Search
- Filter
- Detail
- Verifikasi

**Phase 5 — Reporting**

- Export Excel
- CSV
- Print
- Statistik per wilayah/cabang

**Phase 6 — Form Builder**

- Form dinamis
- Question builder
- Response
- Answer
- Public form URL

---

# 32. Catatan Perubahan & Pembaruan Fitur (Changelog)

Setiap penambahan atau pengurangan fitur wajib dicatat pada bagian ini.

### 2026-09-27 — Penyederhanaan Input Tanggal Lahir (Pilihan 3 Dropdown: Tanggal, Bulan, Tahun Maksimal 40 Tahun)

- **Penyederhanaan Input Tanggal Lahir pada Autentikasi (`auth.blade.php`) & Formulir Pendataan (`form.blade.php`):**
  - Mengganti pemilih tanggal native (`<input type="date">`) menjadi 3 komponen dropdown terstruktur dan user-friendly:
    1. **Tanggal:** Angka 1 s/d 31 dengan penyesuaian otomatis terhadap jumlah hari dalam bulan/tahun kabisat (`adjustDaysInMonth`).
    2. **Bulan:** Nama bulan dalam Bahasa Indonesia (`Januari` s/d `Desember`).
    3. **Tahun:** Dibatasi maksimal 40 tahun dari tahun sekarang (`date('Y')` menurun hingga `date('Y') - 40`, misal 2026 s/d 1986).
  - Ketiga nilai dropdown secara otomatis disinkronkan ke elemen hidden input `<input type="hidden" name="birth_date">` berformat standar ISO `YYYY-MM-DD`, menjaga kompatibilitas penuh dengan controller backend, validasi request, dan database.
  - Nilai prefill (dari `old('birth_date')`, sesi autentikasi, maupun autofill pemuda/warga MTA) otomatis dipetakan ke dropdown Tanggal, Bulan, dan Tahun saat form dimuat atau saat data dipilih.
- **Penambahan Label Jenis Kelamin (L/P) pada Sugesti Nama Autentikasi (`auth.blade.php`):**
  - Pada daftar item sugesti nama autocomplete, ditambahkan badge dan label jenis kelamin **L/P** (Laki-laki berwarna biru, Perempuan berwarna merah muda) baik pada avatar lingkaran, samping nama lengkap, maupun keterangan di bawah nama (`Laki-laki (L)` / `Perempuan (P)`).
  - Saat nama dipilih, kotak konfirmasi terpilih juga menampilkan kode jenis kelamin `[L]` atau `[P]` secara jelas.
- **Pengujian & Jaminan Mutu:**
  - Memperbarui `tests/Feature/PendataanAuthGateTest.php` untuk memvalidasi keberadaan 3 dropdown tanggal lahir (`birth_day`, `birth_month`, `birth_year`, `form_birth_day`, `form_birth_month`, `form_birth_year`) dan batas tahun 40 tahun mundur. Seluruh 79 test suite lulus 100%.

### 2026-09-26 — Implementasi Gerbang Autentikasi Awal Pendataan Pemuda (Anti Data Ganda & Proteksi Akses Formulir)

- **Gerbang Autentikasi Awal Sebelum Akses Formulir Pendataan:**
  - Route `/pendataan` (`pendataan.index`) dialihkan menjadi halaman **Autentikasi & Verifikasi Awal** (`resources/views/pendataan/auth.blade.php`).
  - Form pendataan (`/pendataan/form`) dilindungi secara ketat di server-side dan tidak dapat dibuka tanpa melalui autentikasi awal terlebih dahulu. Kunjungan tanpa sesi diarahkan kembali ke `/pendataan` dengan notifikasi kesalahan.
  - Parameter URL `?cabang_id=` (misal dari tautan pantau Guru Daerah / blast WhatsApp) otomatis memilih cabang pada formulir autentikasi.
- **Smart Autocomplete & Anti-Duplikasi Data (Hanya Tampilkan Nama & Umur):**
  - Input nama pada halaman autentikasi secara cerdas melakukan pencarian autocomplete ketika mengetik minimal 4 karakter (`length >= 4`).
  - Mencari gabungan data dari **Database Pemuda Lokal Cabang** dan **Database Warga MTA Pusat** (`MtaApiService`), menghitung usia (`age`), dan pada item dropdown **hanya menampilkan Nama Lengkap dan Umur** (tanpa menampilkan tanggal lahir lengkap, jenis kelamin, tempat lahir, atau no HP untuk menjaga privasi data).
- **Verifikasi Keamanan Identitas (Input Tanggal Lahir Wajib Manual):**
  - Saat nama pada sugesti dipilih, kolom tanggal lahir **TIDAK diisi secara otomatis**. Pengguna diwajibkan mengetikkan tanggal lahir sendiri secara manual sebagai verifikasi kecocokan identitas pemilik data.
  - Server-side verification pada `PendataanController::authenticate` memvalidasi kecocokan tanggal lahir yang diinput manual dengan data tanggal lahir pemuda yang tercatat di database. Jika tanggal lahir tidak cocok, proses autentikasi ditolak.
- **Logika Percabangan Otomatis (Update vs Pendaftaran Baru):**
  - **Data Sudah Ada:** Jika nama dan tanggal lahir cocok dengan data pemuda yang sudah ada (atau dipilih dari sugesti pemuda dan tanggal lahir terverifikasi), sistem masuk ke **Mode Pembaruan / Update Data**, seluruh isian pemuda di-prefill otomatis pada formulir pendataan.
  - **Warga MTA Pusat:** Jika dipilih dari data warga MTA yang belum tercatat di data pemuda, data warga disinkronkan otomatis.
  - **Nama Belum Ada:** Jika nama dan tanggal lahir belum tercatat pada cabang tersebut, sistem masuk ke **Mode Pendaftaran Pemuda Baru**.
- **Integritas & Proteksi Formulir Pendataan:**
  - Cabang, Nama, dan Tanggal Lahir dikunci (`readonly` / `hidden`) sesuai hasil verifikasi autentikasi awal agar tidak dapat dimanipulasi di sisi klien.
  - Disediakan tombol "Ganti Identitas / Keluar" (`/pendataan/keluar`) untuk mengakhiri sesi autentikasi dan kembali ke halaman verifikasi awal.
  - Endpoint simpan (`/pendataan/simpan`) memvalidasi kecocokan cabang dan ID terhadap sesi `pendataan_auth`, serta membersihkan sesi setelah transaksi database berhasil di-commit.
- **Testing & Jaminan Mutu:**
  - Menambahkan test suite `tests/Feature/PendataanAuthGateTest.php` (11 test case) mencakup verifikasi tampilan autentikasi, proteksi redirect form unauthenticated, autocomplete >= 4 huruf dengan atribut `age`, penolakan autentikasi jika tanggal lahir manual tidak cocok, deteksi update vs create mode, session isolation, dan logout. Seluruh test lulus 100%.

### 2026-09-21 — Implementasi REST API Mobile "Presensi PMD" & Fitur Pengaturan API Superadmin

- **Pembangunan Modul REST API Backend untuk Aplikasi Mobile Android Flutter (`Presensi PMD`):**
  - **Database Migration:**
    - Tabel `kegiatan_presensi`: Menyimpan sesi kegiatan/pengajian presensi cabang (nama kegiatan, tanggal, jam mulai/selesai, lokasi, pemateri, target peserta, status, catatan, pembuat, cabang_id).
    - Tabel `presensi_detail`: Menyimpan catatan kehadiran anggota per kegiatan (`pemuda_id`, `kegiatan_presensi_id`, `status_kehadiran` ENUM `hadir`, `izin`, `sakit`, `alpa`, `keterangan`, `waktu_presensi`, `device_info`, `created_by`).
    - Tabel `api_settings`: Pengaturan konfigurasi, status API, maintenance mode, versi minimum aplikasi, download URL APK, dan preset chips izin/sakit.
    - Tabel `personal_access_tokens`: Migrasi Laravel Sanctum untuk autentikasi Bearer Token mobile.
  - **Model Eloquent & Business Logic:**
    - Model `KegiatanPresensi`: Relasi ke `Cabang`, `User` (creator), `PresensiDetail`. Method `getRekapSummary()` untuk kalkulasi kehadiran realtime, dan `generateWhatsAppText()` untuk menghasilkan format laporan teks WhatsApp siap kirim ke pengurus.
    - Model `PresensiDetail`: Relasi ke `KegiatanPresensi`, `Pemuda`, dan `User`.
    - Model `ApiSetting`: Key-value configuration helper dengan method `get()`, `set()`, `getAllSettings()`, dan `seedDefaults()`.
    - Update Model `User`, `Cabang`, dan `Pemuda` dengan trait `HasApiTokens` dan relasi presensi.
  - **REST API Endpoints (`routes/api.php`):**
    - `GET /api/v1/config`: Konfigurasi publik aplikasi mobile (status online, min app version, broadcast banner, quick chips).
    - `POST /api/v1/auth/login`: Otentikasi petugas presensi/sekretaris cabang, mengembalikan profil user, info cabang, dan Sanctum Bearer Token.
    - `POST /api/v1/auth/logout`: Revoke token aktif pada perangkat.
    - `GET /api/v1/auth/me`: Informasi user login dan cabang yang dikelola.
    - `GET /api/v1/cabang/pemuda`: Pengambilan data pemuda cabang untuk instant search & cache lokal SQLite/Hive.
    - `GET /api/v1/kegiatan`: Daftar sesi kegiatan presensi cabang.
    - `POST /api/v1/kegiatan`: Pembuatan sesi kegiatan presensi baru.
    - `GET /api/v1/kegiatan/{id}`: Detail kegiatan presensi beserta seluruh checklist anggota pemuda.
    - `PUT /api/v1/kegiatan/{id}/status`: Kunci/selesaikan sesi kegiatan presensi (`selesai`).
    - `POST /api/v1/kegiatan/{id}/presensi/single`: Pencatatan realtime presensi per individu (Hadir/Izin/Sakit/Alpa).
    - `POST /api/v1/kegiatan/{id}/presensi/bulk`: Sinkronisasi massal antrean presensi offline ponsel.
    - `GET /api/v1/kegiatan/{id}/rekap`: Data statistik & teks generator laporan WhatsApp.
  - **Security & Middleware:**
    - `CheckApiMaintenance`: Menolak request dengan HTTP 503 jika status API dinonaktifkan oleh Superadmin.
    - `EnforceCabangScope`: Memastikan petugas cabang hanya dapat mengakses data pemuda dan kegiatan milik cabangnya sendiri.
- **Fitur Pengaturan API (API Settings) pada Panel Superadmin:**
  - Menambahkan menu **Seting API Presensi** pada sidebar menu Superadmin (kelompok Integrasi & Web).
  - Controller `Admin/ApiSettingController.php` dan View `resources/views/admin/api_settings/index.blade.php`:
    - Dashboard statistik API: Status API, jumlah token mobile aktif, total sesi kegiatan, dan total kehadiran tercatat.
    - Form konfigurasi: Status API (Online vs Maintenance Mode), custom pesan pemeliharaan, siaran pengumuman mobile (broadcast message), versi minimum aplikasi (force update), versi rilis terkini, URL download APK, izin sinkronisasi offline, batas bulk sync, dan editor preset tombol cepat (quick chips) izin/sakit.
    - Manajemen Token Aktif: Menampilkan daftar perangkat terhubung, akun sekretaris, nama cabang, waktu aktif, tombol cabut sesi individu, serta tombol cabut seluruh sesi (force logout semua perangkat).
    - Katalog & Dokumentasi Endpoint: Tabel dokumentasi lengkap seluruh endpoint REST API mobile disertai badge HTTP method, otorisasi, deskripsi, dan tombol salin URL endpoint.

### 2026-09-21 — Standardisasi Huruf Kapital (Uppercase) Seluruh Elemen Dakwah

- **Standarisasi Tipografi UPPERCASE pada Seluruh Tampilan & Input Elemen Dakwah:**
  - **Formulir Pendataan Publik (`/pendataan`):**
    - Seluruh kartu checkbox elemen dakwah resmi (SATGAS, BANKOM, SAR MTA, TIM PARKIR, ELFATA, TIM IKHROM) dan elemen tambahan server di Langkah 5 dirender dalam huruf besar kapital penuh (`UPPERCASE`) dengan styling Tailwind `uppercase tracking-wider`.
    - Input penambahan elemen baru (`#input_new_org`) otomatis menampilkan huruf kapital (`uppercase placeholder:normal-case`).
    - Penambahan kartu elemen dinamis (`addNewOrganization`) otomatis mengubah input menjadi kapital (`.toUpperCase()`) dan merender teks kartu dengan `uppercase tracking-wider`.
    - Mode pembaruan data (`populateFormWithData`) menormalisasi pencocokan elemen dakwah secara case-insensitive / uppercase.
    - Pratinjau tag elemen dakwah di Langkah 7 Ringkasan (`summaryOrgs`) dirender dalam format `UPPERCASE` (`uppercase tracking-wider`).
  - **Manajemen Admin (`/admin/pemuda`):**
    - Pada formulir tambah/edit pemuda, seluruh pilihan elemen dakwah, badge kustom, serta input elemen baru dikonversi dan ditampilkan dalam format `UPPERCASE`.
    - Pada halaman detail pemuda (`detail.blade.php`), badge elemen dakwah yang diikuti ditampilkan dengan huruf kapital `UPPERCASE` (`uppercase tracking-wider`).
    - Pada format cetak biodata pemuda (`cetak.blade.php`), daftar elemen dakwah diformat menggunakan `array_map('strtoupper', ...)`.
  - **Backend Controller & Service Layer:**
    - `PendataanController.php` (`simpan` dan `getPemudaData`): Menyimpan dan mengembalikan nama elemen dakwah dalam format `UPPERCASE` (`mb_strtoupper`).
    - `Admin/PemudaController.php` (`store` dan `update`): Menyimpan nama elemen dakwah dalam format `UPPERCASE` (`mb_strtoupper`).
    - `PemudaImportService.php` & `PemudaExportService.php`: Memastikan import dari Excel dan export ke spreadsheet selalu diformat dalam huruf kapital `UPPERCASE`.

### 2026-09-20 — Penyeragaman Tampilan Kartu Pilihan Elemen Dakwah di Formulir Pendataan Publik (`/pendataan`)

- **Penyeragaman Total Tampilan Elemen Dakwah Default & Tambahan:**
  - Teks deskripsi subtitle panjang di bawah masing-masing nama elemen dakwah resmi (seperti *"Satuan Tugas Pengamanan & Ketertiban Pengajian"*, *"Bantuan Komunikasi Radio & Informasi Lapangan"*, dst.) telah dihapus.
  - Tampilan kartu checkbox elemen dakwah default, elemen tambahan dari server (`$customOrgs`), serta elemen baru yang ditambahkan secara dinamis (`addNewOrganization`) diseragamkan 100% tanpa perbedaan format, badge, atau border.
  - Seluruh kartu kini menggunakan struktur yang identik: ikon elemen, nama elemen yang tebal, layout grid (`grid-cols-2 sm:grid-cols-3`), border halus dengan sorotan merah saat dipilih (`has-[:checked]:border-red-600 has-[:checked]:bg-red-50/40`), dan efek hover yang selaras.

### 2026-09-20 — Opsi Input Mandiri Bakat & Minat di Formulir Pendataan Publik (`/pendataan`)

- **Fitur Penambahan Bakat / Keahlian & Minat di Luar Daftar Pilihan:**
  - Pada Langkah 6 formulir pendataan publik (*Potensi Bakat, Keahlian & Minat Diri*), ditambahkan opsi input mandiri di bawah masing-masing kolom:
    - **Bakat / Keahlian:** Input teks `input_new_skill` (`name="custom_skills"`) dilengkapi tombol "+ Tambah".
    - **Minat Diri:** Input teks `input_new_interest` (`name="custom_interests"`) dilengkapi tombol "+ Tambah".
  - Pengguna dapat mengetik nama keahlian atau minat baru dan menekan tombol Tambah atau menekan tombol Enter:
    - Jika keahlian/minat yang diketik sudah tersedia di daftar pilihan, sistem secara cerdas mencentang pilihan yang ada, menggulir ke item tersebut, dan menampilkan umpan balik sukses.
    - Jika benar-benar baru, sistem menambahkan elemen checkbox baru secara dinamis ke dalam kontainer pilihan lengkap dengan badge `(Baru)` dan tombol hapus silang (`x`).
  - **Pencegah Lupa Input:** Jika pengguna mengetik nama di kolom input lalu langsung menekan tombol "Lanjutkan" atau mengirim form tanpa menekan tombol "Tambah", validasi form secara otomatis memasukkan keahlian/minat tersebut ke dalam data pendaftaran.
- **Pembaruan Backend Controller (`PendataanController` & `Admin/PemudaController`):**
  - Parameter `custom_skills` dan `custom_interests` (serta nilai string baru di dalam array `skills[]` / `interests[]`) diproses secara otomatis.
  - Menggunakan pencarian case-insensitive ke tabel master `skills` dan `interests`, dan otomatis membuat record baru (`firstOrCreate`) jika belum tercatat di database.
  - Mengaitkan relasi ke tabel pivot `pemuda_skills` dan `pemuda_interests`.
  - Endpoint `GET /pendataan/get-pemuda/{id}` kini mengembalikan `skills_data` dan `interests_data` (objek id dan nama) sehingga saat mode pembaruan (update) aktif, seluruh keahlian/minat kustom tetap terpilih dan ter-render sempurna di form.
- **Ringkasan Formulir Langkah 7:**
  - Ditambahkan kartu pratinjau ringkasan pilihan Bakat/Keahlian (`#summary_skills`) dan Minat (`#summary_interests`) sebelum pengiriman formulir.
- **Pengujian Otomatis:**
  - Ditambahkan automated feature test `test_custom_skills_and_interests_can_be_submitted_and_persisted` di `tests/Feature/PendataanFlowTest.php`. Seluruh 43 tests lulus 100% (307 assertions).

### 2026-09-20 — Tampilan Progres Real-Time Antrean Sinkronisasi MTA Pusat (Laju: 40 Data/Menit & Jeda Istirahat 10 Detik / 40 Data)

- **Sistem Antrean Sinkronisasi Massal & Laju Aman API (Rate Limit & Rest Period):**
  - **Laju Pemrosesan:** Ditetapkan secara ketat pada laju **40 data / menit** (rata-rata 1.5 detik per item) guna menghindari limit kuota (*rate limiting / HTTP 429*) dari server MTA Pusat (`api.mta.or.id`).
  - **Jeda Istirahat Otomatis:** Setiap kali mencapai pemrosesan 40 data (`item % 40 === 0`), sistem secara otomatis melakukan jeda istirahat selama **10 detik** sebelum melanjutkan batch antrean berikutnya.
- **Tampilan Interaktif Proses yang Sedang Berjalan (`resources/views/admin/mta_sync/index.blade.php`):**
  - **Banner Status Dinamis:** Menampilkan status real-time (`Memproses Antrean`, `Sedang Istirahat 10 Detik`, `Dijeda`, `Selesai`, `Dibatalkan`) lengkap dengan animasi ping, countdown timer istirahat 10 detik interaktif (`10s... 1s`), dan detail laju aman.
  - **Progress Bar Real-Time & Estimasi Waktu:** Animasi gradien warna dengan persentase real-time, counter data diproses terhadap total, serta kalkulasi sisa waktu (*estimated remaining time*) yang mengikutsertakan jeda istirahat 10 detik per 40 data.
  - **4 KPI Metric Live Cards:** Menampilkan metrik langsung: *Sudah Diproses* (beserta *Sisa*), *Terverifikasi* (Hijau), *Belum Terdata* (Kuning), dan *Gagal/Error* (Merah).
  - **Spotlight Item Sedang Diproses:** Menampilkan secara transparan nama pemuda, cabang asal, badge hasil verifikasi, dan pesan respons API yang sedang aktif diproses.
  - **Kontrol Interaktif Antrean:** Tombol **Jeda Sementara (Pause)** / **Lanjutkan (Resume)**, tombol **Batalkan Antrean (Cancel)**, dan tombol **Selesai/Refresh Halaman**.
  - **Activity Stream Log Real-Time:** Feed aktivitas bergulir yang menampilkan 50 riwayat terakhir data pemuda yang baru saja selesai diproses dengan timestamp, nama, cabang, dan badge status.
  - **Deteksi Otomatis & Pemulihan Sesi:** Halaman secara otomatis mendeteksi jika terdapat antrean pending dari sesi sebelumnya, dan menyediakan tombol langsung *"Lanjutkan Antrean Tersisa"* tanpa harus mengulang dari awal.
- **Dukungan Artisan CLI (`php artisan mta:sync-queue`):**
  - Perintah konsol CLI juga diperbarui untuk mematuhi laju yang sama (1.5 detik/data) serta jeda istirahat 10 detik dengan countdown terminal interaktif setiap 40 data.
- **Database & Model:**
  - Migration `2026_09_20_151000_alter_result_in_mta_sync_queue_table.php` memperluas kolom `result` pada `mta_sync_queue` dari ENUM ke `VARCHAR(50)` untuk fleksibilitas status.
  - `MtaSyncService` dilengkapi pemulihan otomatis item macet (*stuck processing recovery*) dan pengembalian detail lengkap item (`name`, `cabang_name`, `gender`, dsb.).
  - `MtaSyncQueue::getQueueSummary()` memperhitungkan jeda istirahat 10 detik dalam penghitungan estimasi waktu.
- **Pengujian Otomatis:**
  - Seluruh pengujian di `tests/Feature/MtaSyncQueueFlowTest.php` dan suite lengkap aplikasi lulus 100% (42 tests, 295 assertions).

### 2026-09-20 — Sinkronisasi Menyeluruh Data MTA Pusat ke Database Pemuda (Pemuda, Alamat, Pendidikan, Pekerjaan)

- **Sinkronisasi Lengkap Seluruh Entitas Terkait Pemuda:**
  - Sebelumnya, sinkronisasi MTA Pusat hanya memperbarui `mta_warga_uuid` dan `status_verifikasi = 'verified'`.
  - Sekarang diperluas secara menyeluruh menarik seluruh field data yang tersedia dari MTA Pusat API (`api.mta.or.id/api/v1/warga/{uuid}`) dan memetakan/menyimpannya ke dalam tabel-tabel database:
    - **Tabel `pemuda`:** `marital_status`, `blood_type`, `birth_place`, `birth_date`, `phone`, `email`, `mta_ayah_uuid`, `mta_ibu_uuid`, `mta_foto_url`, `mta_warga_uuid`, `mta_status_warga`, dan `status_verifikasi = verified`.
    - **Tabel `alamat`:** Mencari atau membuat (`updateOrCreate`) relasi alamat dengan pencocokan nama provinsi, kabupaten/kota (misal: "KABUPATEN SRAGEN"), kecamatan (`districts`), dan desa/kelurahan (`villages`), serta mem-parsing dusun, RT, RW dari field `alamat_rtrw` / `alamat`, dan mencatat detail alamat lengkap.
    - **Tabel `pendidikan`:** Memetakan string pendidikan MTA ke ID `education_levels` (1: SD/MI, 2: SMP/MTs, 3: SMA/SMK/MA, 4: D1-D3, 5: S1/D4, 6: S2, 7: S3), mengisi `school_name` dan `education_status = 'Lulus'`.
    - **Tabel `pekerjaan`:** Memetakan string pekerjaan MTA ke ID `job_statuses` (1: Belum Bekerja, 2: Pelajar/Mahasiswa, 3: Karyawan Swasta, 4: PNS/ASN/TNI/Polri/Sipil, 5: Wirausaha/Toko, 6: Freelancer, 7: Petani/Peternak, 8: Lainnya) dan mengisi `job_title`.
- **Dukungan Alur Publik & Admin:**
  - **Formulir Pendataan Publik (`/pendataan`):** Saat warga MTA terverifikasi dipilih di form publik, endpoint `GET /pendataan/get-warga/{uuid}` kini mengembalikan seluruh field (biodata, kontak, status nikah, goldar, alamat lengkap, pendidikan, pekerjaan, foto, dan UUID orang tua). Data tersebut langsung terisi otomatis (*autofill*) ke dalam 7 langkah wizard form publik.
  - **Verifikasi Mandiri Admin (`syncSinglePemuda`):** Admin dapat memverifikasi pemuda secara individu, dan sistem secara otomatis mengambil detail lengkap warga dari API MTA Pusat lalu menyinkronkan seluruh tabel terkait pemuda.
  - **Import / Batch Sync Cabang (`syncWargaToPemuda`):** Sinkronisasi massal warga per cabang otomatis memanggil detail warga dan menyinkronkan data pemuda, alamat, pendidikan, dan pekerjaan.
- **Migration Perbaikan Kolom `mta_sync_logs`:**
  - Migration `2026_09_20_143559_alter_sync_type_in_mta_sync_logs_table.php` memperluas kolom `sync_type` dari ENUM ke `VARCHAR(50)` untuk mendukung tipe sinkronisasi baru (`pemuda_single`, dsb.) tanpa peringatan/truncation MySQL.
- **Pembaruan Tampilan UI:**
  - `resources/views/pendataan/form.blade.php`: Input tersembunyi `mta_ayah_uuid`, `mta_ibu_uuid`, `mta_foto_url` dan penanganan autofill JavaScript untuk alamat, pendidikan, pekerjaan, dan foto preview.
  - `resources/views/admin/warga_mta/detail.blade.php`: Tampilan detail warga MTA lengkap dengan foto, tempat/tanggal lahir, kontak, status pernikahan, golongan darah, alamat (RT/RW, desa, kecamatan, kabupaten, provinsi), pendidikan, pekerjaan, dan nama orang tua.
  - `resources/views/admin/pemuda/detail.blade.php`: Informasi status MTA dan timestamp sinkronisasi terakhir.
- **Pengujian Otomatis:**
  - Seluruh test di `tests/Feature/MtaSyncDataPullTest.php` dan suite pengujian lainnya lulus 100% (36 tests, 250 assertions).

### 2026-09-17 — Alur Pendataan Pemuda: Pemilihan Langsung Cabang Saja, Autocomplete Pencarian Nama, & Pembaruan Navigasi Form

- **Alur Baru Form Pendataan Publik (`/pendataan`):**
  - **Pemilihan Langsung Cabang (Tanpa Wilayah):** Menghapus dropdown filter Wilayah pada formulir pendataan publik. Pengguna langsung memilih **Cabang MTA** binaan/domisili. Disediakan kolom saring cepat (*quick search filter*) nama cabang untuk memudahkan pencarian di antara 70 cabang se-Sragen tanpa perlu me-reload halaman.
  - **Autocomplete Pencarian Nama Berdasarkan Cabang:** Saat Cabang telah dipilih dan pemuda mengetikkan nama (minimal 2 karakter), sistem secara real-time (debounced 250ms) mencari data pemuda yang aktif dan terdaftar khusus di cabang tersebut melalui endpoint `GET /pendataan/search-nama?cabang_id=...&q=...`.
  - **Mode Pembaruan Data (Update):** Jika nama dipilih dari daftar hasil pencarian, sistem mengambil detail lengkap via endpoint terisolasi `GET /pendataan/get-pemuda/{id}?cabang_id=...` dan otomatis mengisi (pre-populate) seluruh field formulir (Data Diri, Alamat Domisili, Pendidikan, Pekerjaan, Organisasi/Element Dakwah, Keahlian, dan Minat). Ditampilkan banner status mode update berwarna hijau dengan opsi tombol *"Bukan Anda? / Daftar Baru"* jika ingin membatalkan.
  - **Mode Pendaftaran Baru:** Jika nama tidak ditemukan dalam cabang tersebut atau pemuda memilih mendaftar baru, sistem mengosongkan `existing_pemuda_id` dan memproses pendaftaran baru seperti biasa.
  - **Peningkatan Navigasi & UX Form Wizard:**
    - Penambahan atribut `novalidate` pada form agar browser tidak memblokir tombol submit secara diam-diam akibat constraint validation native pada input di step-step yang tersembunyi.
    - Navigasi stepper yang fleksibel: pengguna dapat melompat kembali ke langkah sebelumnya secara bebas untuk memeriksa isian.
    - Indikator langkah visual dengan nomor langkah aktif dan ikon centang hijau (`bi bi-check-lg`) pada langkah yang telah selesai.
    - Tombol navigasi bawah interaktif dengan teks dinamis ("Kembali ke Data Diri", "Lanjut ke Alamat", dsb.), penghitung langkah (*step counter*), serta tombol submit yang dilengkapi pencegah klik ganda (*double-submit prevention*) dengan animasi loading spinner.
    - Alert banner notifikasi visual lembut (`#step_alert_box`) di dalam form menggantikan `alert()` browser pop-up.
    - Tampilan ringkasan data pendaftaran (*summary card*) secara dinamis di Langkah 7 sebelum pengguna menyetujui pernyataan dan mengirim formulir.
    - *Smooth scroll* otomatis ke bagian atas formulir setiap kali berganti langkah.
  - **Keamanan & Isolasi Data:**
    - Endpoint `search-nama` dan `get-pemuda` dibatasi rate limiter (`throttle:60,1`).
    - Endpoint `get-pemuda` dan method `simpan` mewajibkan validasi pencocokan ID pemuda terhadap `cabang_id` terpilih, mencegah modifikasi lintas cabang (cross-cabang isolation).
    - Tidak ada data NIK yang diekspos maupun disimpan (telah dihapus total dari database dan formulir).
  - **Pengujian Otomatis:** Seluruh 25 unit/feature tests di `tests/Feature/PendataanFlowTest.php` dan `tests/Feature/PmdSragenRoutesTest.php` lulus 100% (110 assertions).

### 2026-09-16 — Peningkatan Tampilan & Dashboard Admin Menjadi AdminLTE 4 (Bootstrap 5)

- **Migrasi Arsitektur Layout Admin (`app/Views/admin/layouts/main.php`):**
  - Mengadopsi struktur resmi AdminLTE 4: `app-wrapper`, `app-header`, `app-sidebar`, `sidebar-brand`, `sidebar-wrapper`, `app-main`, `app-content-header`, `app-content`, dan `app-footer`.
  - Integrasi tipografi & dependensi resmi: Source Sans 3 (`@fontsource/source-sans-3`), Bootstrap 5.3.3 Bundle, Popper.js 2.11.8, OverlayScrollbars 2.10.1, Bootstrap Icons 1.11.3 (native AdminLTE 4), serta Font Awesome 6.5.2 untuk backward-compatibility.
  - Sidebar interaktif modern berbasis `[data-lte-toggle="treeview"]` dengan OverlayScrollbars halus untuk scrolling yang elegan dan ikon native Bootstrap Icons.
  - Navbar responsif dengan tombol toggle sidebar AdminLTE 4 `[data-lte-toggle="sidebar"]`, fullscreen toggle `[data-lte-toggle="fullscreen"]`, user profile dropdown Bootstrap 5, serta badge role & scope dinamis.
- **Pembaruan Dashboard Admin Sesuai Standar Resmi AdminLTE 4 (`app/Views/admin/dashboard/index.php`):**
  - Hero banner modern dengan gradient slate/navy gelap (`dashboard-hero-card`) dan badge identitas role.
  - 4 Small-Box signature AdminLTE 4 dalam grid `col-lg-3 col-6` (`text-bg-primary`, `text-bg-success`, `text-bg-warning`, `text-bg-danger`) lengkap dengan SVG `.small-box-icon` beranimasi zoom dan footer link interaktif.
  - 4 Info-Box standar AdminLTE 4 (`dist/pages/widgets/info-box.html`) dengan ikon Bootstrap Icons shadow-sm (`text-bg-primary`, `text-bg-info`, `text-bg-success`, `text-bg-secondary`).
  - Card outline AdminLTE 4 (`card-primary card-outline`, `card-info card-outline`, dsb.) dengan fitur collapse interaktif native `[data-lte-toggle="card-collapse"]` serta ikon expand/collapse toggle (`bi-plus-lg` & `bi-dash-lg`).
  - Tabel pemuda terakhir diedit (Last Edit) yang bersih dan responsif dengan Bootstrap 5 (`table-hover table-striped align-middle`).
  - Visualisasi Chart.js 4.4 (Wilayah, Demografi Gender, Status Nikah, Pendidikan, Pekerjaan) yang tetap responsif dan konsisten.
- **Compatibility Bridge & Mobile Navigation:**
  - Jembatan kompatibilitas jQuery untuk Bootstrap 5 Modal (`$.fn.modal`, `data-toggle="modal"` & `data-bs-toggle="modal"`, `data-dismiss="modal"` & `data-bs-dismiss="modal"`).
  - Jembatan kompatibilitas collapse, alert dismiss, dan pushmenu toggle.
  - Mempertahankan navigasi bawah mobile admin (`.admin-mobile-bottom-nav`) dan fitur PWA (`manifest.json`, `pwa-install.js`) agar tetap mulus pada layar smartphone.
- **Testing & Verifikasi:**
  - Seluruh 118 unit test pada PHPUnit lulus 100% tanpa regresi.

### 2026-09-15 — Penyederhanaan Halaman Utama (Clean & Minimalist Homepage)

- **Konsep & Pendekatan:** Menata ulang halaman depan menjadi bersih, sederhana, dan fokus (*to-the-point*) pada tujuan utama pendataan pemuda tanpa membebani pengunjung dengan informasi yang terlalu padat.
- **Komponen Inti yang Dipertahankan:**
  - **Hero Minimalis:** Judul resmi sistem, ringkasan 1-2 kalimat, tombol aksi utama langsung ke formulir pendataan (`Isi Form Pendataan Pemuda`) dan tombol sekunder ke `Portal Admin`, serta chip ringkas (*4 Wilayah & 61 Cabang*, *Satgas*, *Bankom*, *Kajian & Tarbiyah*).
  - **Statistik Inti:** Baris ringkas 4 angka (Wilayah Koordinasi, Cabang Binaan, Pemuda Terdata & Terverifikasi, serta Bidang Khidmah).
  - **Alur 3 Langkah Sederhana:** Panduan singkat (*Buka Form Online*, *Lengkapi Data & Cabang*, *Terima Nomor Registrasi*).
  - **Wilayah & Cabang:** Kartu ringkas 4 Wilayah Koordinasi dengan tombol langsung memilih cabang dan mendaftar.
  - **Program Kerja Singkat:** Tampilan kartu ringkas bidang pengabdian pemuda.
  - **Visi & Call-to-Action:** Kutipan visi organisasi dan tombol aksi cepat.
  - **FAQ Ringkas & Kontak:** Akordeon tanya jawab ringkas dan tombol bantuan cepat WhatsApp Helpdesk.
- **Navigasi Bersih:** Menyederhanakan menu navbar di `layouts/main.php` agar rapi dan tidak terlalu banyak menu.

### 2026-09-15 — Perbaikan Kritis: Pencegahan Penghapusan Data Pemuda saat User/Admin Dihapus

- **Akar Masalah (Root Cause):**
  - Pada definisi `addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE')` di migration awal, urutan parameter CodeIgniter 4 adalah `($fieldName, $tableName, $tableField, $onUpdate, $onDelete)`.
  - Akibat tertukarnya posisi parameter `'SET NULL'` dan `'CASCADE'`, MySQL mendefinisikan foreign key `pemuda_created_by_foreign` dengan `ON DELETE CASCADE` dan `ON UPDATE SET NULL`.
  - Ketika sebuah akun admin/user dihapus, MySQL otomatis mengeksekusi cascading delete ke seluruh baris tabel `pemuda` yang dibuat atau diimpor oleh user tersebut, yang kemudian merembet menghapus tabel anak (`alamat`, `pendidikan`, `pekerjaan`, `organisasi`, `pemuda_skills`, `pemuda_interests`).
- **Langkah Perbaikan (Fix & Hardening):**
  - **Migration Baru (`2026-09-15-223000_FixCreatedByForeignKeyOnDeleteSetNull.php`):** Menghapus foreign key lama pada `pemuda`, `forms`, `mta_sync_logs`, dan `mta_sync_queue`, lalu merekonstruksinya dengan `ON UPDATE CASCADE ON DELETE SET NULL`.
  - **Koreksi Migration Awal:** Memperbaiki urutan argumen `addForeignKey` pada migration `CreateYouthDataSystem`, `AddMtaSyncFields`, dan `CreateMtaSyncQueueTable` agar instalasi baru atau `migrate:refresh` tidak membawa bug tersebut.
  - **Lapisan Pertahanan Aplikasi (`Admin\Users::delete`):** Menambahkan query eksplisit untuk mengosongkan referensi `created_by = NULL` di tabel `pemuda`, `forms`, `mta_sync_logs`, dan `mta_sync_queue` sebelum eksekusi penghapusan user, menjamin data pemuda 100% aman dan tidak tersentuh.
  - **Unit Testing Otomatis (`UserDeleteProtectionTest.php`):** Menambahkan 2 pengujian otomatis yang menguji langsung penghapusan user di MySQL dan memastikan data pemuda tetap utuh dengan `created_by = NULL`.

### 2026-09-15 — Redesain Halaman Utama (Homepage) Bergaya Portal Universitas (ums.ac.id) dengan Nuansa Tone Crimson

- **Tata Letak & Arsitektur Homepage (Mengadopsi Elemen Unggulan ums.ac.id):**
  - **Top Utility Bar:** Ditambahkan strip navigasi utilitas atas khas portal universitas pada template utama `layouts/main.php` (identitas resmi Perwakilan MTA Sragen, tautan cepat Berita & Kegiatan, Rubrik Khusus, Portal Admin, dan Helpdesk WA).
  - **Hero Section Modern:** Tagline pill resmi, tipografi judul berdampak tinggi, ringkasan sistem, serta tombol aksi ganda (Form Pendataan & Eksplorasi Cabang).
  - **Interactive Cabang Quick-Finder Widget:** Widget pencarian cepat cabang & wilayah terinspirasi dari fitur pencarian program studi UMS, dilengkapi filter wilayah dan pencarian instan nama cabang.
  - **Statistik & Reputasi Strip:** Counter statistik bergaya reputasi UMS (Wilayah Koordinasi, Cabang Binaan, Pemuda Terdata & Terverifikasi, serta Bidang Khidmah).
  - **Program Showcase Berfilter:** Filter pill dinamis (*Semua, Dakwah & Tarbiyah, Satgas & Kesiapsiagaan, Bankom Radio, Skill & Wirausaha, Tim Ikhrom*) dengan kartu modern.
  - **Split Highlight Cards:** Dua kartu unggulan berskala besar bergaya beasiswa & riset UMS (Kaderisasi Berkelanjutan & Pemetaan Potensi/Kemandirian Pemuda).
  - **Eksplorasi Wilayah & Cabang:** Navigasi tab 4 Wilayah dengan pencarian live filter nama cabang, status gelombang pemuda, jadwal, nama pimpinan, dan link pendaftaran per cabang.
  - **Berita & Agenda Kegiatan (Hallmark Grid UMS):** Grid terintegrasi yang memadukan warta/berita kegiatan pemuda terkini dengan kalender agenda bertanggal khas UMS (kotak tanggal besar + bulan).
  - **4 Rubrik Unggulan:** Mengadopsi 4 rubrik khas UMS (*Tarbiyah & Kajian, Kiprah Pemuda, Teropong Khidmah, Cerita Kader*).
  - **Newsletter & Komunitas Pemuda:** Banner ajakan langganan informasi dan saluran resmi WhatsApp.
  - **Tone Warna & Integrasi:** Mempertahankan palet warna crimson/maroon khas Pemuda MTA Sragen (`#700f2b`, `#991b1b`, `#dc2626`, aksen emas `#f59e0b`), tetap terhubung penuh dengan konfigurasi `HomepageSettingModel` serta lulus 100% seluruh unit test (116 tests).

### 2026-08-29 — Penambahan Detail Informasi Cabang & Gelombang Pemuda

- **Penambahan Kolom Database pada Tabel `cabang`:**
  - `alamat` (TEXT): Alamat lengkap atau sekretariat cabang.
  - `pimpinan_nama` (VARCHAR 100): Nama pimpinan cabang.
  - `no_wa` (VARCHAR 20): Nomor WhatsApp atau kontak pimpinan cabang.
  - `has_gelombang` (ENUM 'sudah', 'belum'): Status ketersediaan gelombang pemuda di cabang terkait.
  - `gelombang_hari` (VARCHAR 100): Hari pengajian / kegiatan gelombang pemuda (jika sudah ada).
  - `gelombang_jam` (VARCHAR 50): Jam masuk / waktu pelaksanaan kegiatan (jika sudah ada).
  - `gelombang_ustadz` (VARCHAR 150): Nama ustadz yang mengampu kegiatan pemuda (jika sudah ada).
- **Migration & Model:**
  - Dibuat migration `2026-08-29-185000_AddDetailsToCabang.php` beserta index `idx_cabang_has_gelombang`.
  - Diperbarui `CabangModel.php` whitelist `$allowedFields`.
- **Controller & UI Cabang:**
  - Ditambahkan filter status gelombang pemuda (`sudah` / `belum`) dan pencarian pimpinan/ustadz/alamat di `Admin\Cabang::index`.
  - Ditambahkan endpoint `Admin\Cabang::detail($id)` untuk AJAX detail modal.
  - Diperbarui UI `app/Views/admin/cabang/index.php`:
    - Ringkasan statistik (Total Cabang, Sudah Ada Gelombang, Belum Ada Gelombang).
    - Tampilan tabel dengan informasi pimpinan, link WhatsApp langsung, status gelombang beserta jadwal/ustadz, dan alamat.
    - Modal Tambah & Edit Cabang dengan toggle interaktif untuk detail jadwal dan ustadz pengampu gelombang.
    - Modal Detail Cabang interaktif untuk melihat informasi lengkap cabang dalam satu klik.

### 2026-08-30 — Audit & Penguatan\*

- Dilengkapi otomatisasi sinkronisasi token CSRF pada form public (`public/js/pendataan.js`) saat verifikasi duplikasi berlangsung.
- Ditambahkan meta tag CSRF dan konfigurasi `$.ajaxSetup` global pada template admin (`app/Views/admin/layou Keamanan Sistem (Security Hardening)
- **Pengaktifan Global Security Filters:**
  - `csrf`: Proteksi Cross-Site Request Forgery diaktifkan secara global di `app/Config/Filters.php`.
  - `secureheaders`: Header keamanan HTTP (`X-Frame-Options`, `X-Content-Type-Options: nosniff`, `Referrer-Policy`) diaktifkan di global after filter.
  - `invalidchars`: Filter pembersih karakter kontrol berbahaya diaktifkan di global before filter.
- \**CSRF Token Synchronization & AJAX:*ts/main.php`).
- **Pencegahan Brute-Force & DoS (Rate Limiting / Throttling):**
  - Ditambahkan throttler pada `Auth::login` (maksimal 5 percobaan per menit per IP).
  - Ditambahkan throttler pada `Pendataan::simpan` (maksimal 10 pendaftaran per menit per IP) dan `Pendataan::checkDuplicate` (maksimal 30 pengecekan per menit per IP).
- **Pengamanan Manajemen Pengguna (Users & Roles):**
  - Validasi wajib scope wilayah untuk `admin_wilayah` dan scope cabang untuk `admin_cabang`.
  - Proteksi anti self-lockout: Admin yang sedang login tidak dapat menurunkan role atau menonaktifkan akunnya sendiri.
  - Proteksi penghapusan Superadmin terakhir: Mencegah sistem kehilangan seluruh akun Superadmin aktif.
- **Integritas Relasional Penghapusan Data:**
  - Ditambahkan validasi cek akun admin terkait sebelum menghapus data wilayah atau cabang.
- **Pencegahan CSV / Spreadsheet Formula Injection (CWE-1236):**
  - Ditambahkan helper `sanitizeCsvField()` pada `app/Common.php` dan diimplementasikan pada `Admin\Pemuda::export()` untuk menetralkan karakter formula (`=`, `+`, `-`, `@`, `\t`, `\r`).
- **Pengamanan Upload File Spreadsheet:**
  - Ditambahkan validasi MIME type (`mime_in`) pada `Admin\Pemuda::prosesImport()`.
- **Penguatan Session & Cookie:**
  - Diaktifkan `$regenerateDestroy = true` di `app/Config/Session.php` untuk mencegah session fixation.
  - Dikonfigurasi `$appTimezone = 'Asia/Jakarta'` di `app/Config/App.php`.

### 2026-08-31 — Integrasi & Sinkronisasi Database Warga MTA API (v1) — Khusus Perwakilan Sragen

- **Konfigurasi & Scope Wilayah:**
  - Dibuat `app/Config/MtaApi.php` dan ditambahkan konfigurasi environment (`MTA_API_BASE_URL`, `MTA_API_TOKEN`, `MTA_API_TIMEOUT`, `MTA_API_ENABLED`, `MTA_PERWAKILAN_UUID`, `MTA_PERWAKILAN_NAMA`) di `.env` dan `env`.
  - **Penguncian Scope Data:** Seluruh proses pencarian warga, pengambilan daftar cabang, dan sinkronisasi data dikunci secara ketat hanya untuk **Perwakilan Sragen** (Kode: `86`, UUID: `3246792b-f0a7-48ca-95fa-379e3bee777d`).
- **Service Layer:**
  - `app/Services/MtaApiService.php`: Ditambahkan helper `getSragenUuid()`, `getPerwakilanSragenDetail()`, `getCabangSragenList()`, `getCabangWarga()`, dan default filter Perwakilan Sragen pada `searchWarga()` dan `getWargaList()`.
  - `app/Services/MtaSyncService.php`:
    - Sinkronisasi cabang otomatis diarahkan ke 65+ cabang Perwakilan Sragen.
    - Ditambahkan `verifyYouthAgainstMta(array $inputData)`: Pengecekan otomatis apakah pemuda yang diinput sudah ada di Database Warga MTA Pusat. Jika ada -> status `verified`, jika tidak ada -> status `pending`.
    - Ditambahkan `syncAndVerifyAllPemudaSragen(?int $cabangId, bool $onlyPending)`: Fitur verifikasi dan sinkronisasi massal seluruh data pemuda terdaftar di PMD Sragen terhadap Database Warga MTA Pusat.
- **Database & Migration:**
  - Dibuat migration `2026-08-31-220000_AddMtaSyncFields.php`:
    - Tabel `wilayah`: penambahan kolom `mta_uuid`, `mta_code` (indexed).
    - Tabel `cabang`: penambahan kolom `mta_uuid`, `mta_last_synced_at` (indexed).
    - Tabel `pemuda`: penambahan kolom `mta_warga_uuid`, `mta_status_warga`, `mta_ayah_uuid`, `mta_ibu_uuid`, `mta_foto_url`, `mta_synced_at` (indexed).
    - Tabel baru `mta_sync_logs` untuk audit trail riwayat sinkronisasi.
  - Diperbarui `$allowedFields` pada `WilayahModel.php`, `CabangModel.php`, dan `PemudaModel.php`.
  - Dibuat model `MtaSyncLogModel.php`.
- **Form Public & Controller:**
  - `app/Controllers/Pendataan.php`: Pada saat pemuda mendaftar mandiri via form publik (`simpan()`), sistem langsung memverifikasi otomatis ke API MTA Pusat. Jika ditemukan di server MTA, status registrasi langsung menjadi `verified`, jika tidak ditemukan berstatus `pending`.
  - `app/Views/pendataan/sukses.php`: Badge status dinamis ("Terverifikasi Otomatis (Tercatat di MTA Pusat)" vs "Menunggu Verifikasi Admin").
  - `app/Controllers/Admin/MtaSync.php`: Ditambahkan endpoint `POST admin/mta-sync/sync-verify-all` untuk pemindaian dan verifikasi massal seluruh pemuda.
- **User Interface & UX Admin Panel:**
  - `app/Views/admin/mta_sync/index.php`: Ditambahkan card fitur & modal "Sinkronisasi & Verifikasi Otomatis Pemuda Sragen".
  - `app/Views/admin/pemuda/index.php`: Ditambahkan shortcut tombol "Sinkron & Verifikasi MTA".

### 2026-08-31 — Penyederhanaan & Prioritas Data Import Excel Pemuda

- **Prioritas 5 Data Inti Wajib:**
  - Fitur Import Excel difokuskan pada 5 kolom data utama yang esensial:
    1. `name` (Nama Lengkap)
    2. `cabang` (Nama / Kode Cabang)
    3. `gender` (Jenis Kelamin: `L` / `P`)
    4. `marital_status` (Status Pernikahan: `belum_menikah`, `sudah_menikah`, `janda`, `duda`)
    5. `birth_date` (Tanggal Lahir: `YYYY-MM-DD` / `DD/MM/YYYY`)
- **Data Pelengkap Bersifat Opsional & Menyusul:**
  - `phone` (Nomor Telepon/WA), tempat lahir, email, golongan darah, alamat lengkap, jenjang pendidikan, pekerjaan, organisasi, keahlian, dan minat dijadikan opsional (nullable/fallback otomatis) sehingga tidak memblokir proses import jika belum terisi.
- **Template Excel & UI Panduan:**
  - Template `Template_Import_Pemuda_MTA_Sragen.xlsx` diperbarui dengan visualisasi header hijau untuk 5 kolom utama wajib dan warna netral untuk kolom pelengkap yang bisa menyusul.
  - Halaman `app/Views/admin/pemuda/import.php` diperbarui dengan panduan prioritas data yang jelas.

### 2026-09-01 — Fitur Pengecekan Data Pemuda & Pelengkapan Data Otomatis pada Form Pendataan

- **Pengecekan Data Pemuda (Nama, Jenis Kelamin, Tanggal Lahir, dan Cabang):**
  - Ditambahkan method `findExistingPemuda($name, $gender, $birthDate, $cabangId, $excludeId)` pada `PemudaModel.php` untuk mencocokkan data pemuda secara akurat.
  - Ditambahkan endpoint AJAX `POST /pendataan/check-data` (serta alias legacy `POST /pendataan/check-duplicate`) pada `Pendataan::checkData`.
  - Jika data **sudah terdaftar** di cabang terkait:
    - Mengembalikan `status: 'found'` beserta data lengkap pemuda (identitas pribadi, alamat, pendidikan, pekerjaan, organisasi, keahlian, dan minat).
    - Form secara otomatis dimuat dan diisikan dengan data yang ada di database.
    - Menampilkan notifikasi visual interaktif mode "Melengkapi & Memperbarui Data Terdaftar" dengan No. Registrasi.
    - User dapat langsung melanjutkan ke langkah berikutnya untuk melengkapi atau memperbarui kolom yang belum terisi.
  - Jika data **belum terdaftar**:
    - Mengembalikan `status: 'not_found'`.
    - Menampilkan feedback informatif "Data Belum Terdaftar" dan mengizinkan user melanjutkan pengisian form pendataan baru sampai langkah konfirmasi selesai.
- **Pembaruan Alur Penyimpanan (`Pendataan::simpan`):**
  - Mendukung penyelesaian/pembaruan data terdaftar (`isUpdate = true`) dengan operasi upsert pada `alamat`, `pendidikan`, `pekerjaan`, serta sinkronisasi ulang `organisasi`, `skills`, dan `interests` tanpa memicu penolakan duplikasi.
  - Pembuatan data baru tetap meng-generate nomor registrasi unik `PMD-YYYYMMDD-XXXX`.
- **UI/UX Form Pendataan Publik (`app/Views/pendataan/form.php` & `public/js/pendataan.js`):**
  - Penataan 4 parameter verifikasi utama (Cabang, Nama Lengkap, Jenis Kelamin, Tanggal Lahir) di bagian atas Step 1.
  - Tombol aksi interaktif "Cek Data Pemuda" beserta indikator spinner dan kontainer feedback dinamis.
  - Integrasi otomatis saat klik "Selanjutnya: Alamat" jika pengecekan data belum dijalankan secara manual.
  - Adaptasi dinamis tombol konfirmasi dan halaman sukses (`app/Views/pendataan/sukses.php`).

### 2026-09-01 — Standarisasi Penyimpanan Data Pemuda dalam Format Huruf Kecil (Lowercase)

- **Format Lowercase pada Database:**
  - Seluruh data teks pemuda (nama lengkap, tempat lahir, email, golongan darah, status pernikahan, dusun, RT, RW, alamat detail, nama sekolah/kampus, jurusan, status pendidikan, profesi/jabatan, nama perusahaan/usaha, bidang usaha, nama organisasi, posisi/jabatan, deskripsi) distandarisasi untuk disimpan dalam format **lowercase** (huruf kecil) menggunakan UTF-8 `mb_strtolower()`.
- **Implementasi Multi-Layer:**
  1. **Helper & Form Public/Admin:** Helper `toLowerTrim()` di `app/Common.php` digunakan pada `Pendataan::simpan` dan `Admin\Pemuda::simpan` serta `Admin\Pemuda::update`.
  2. **Model Callbacks (`beforeInsert` & `beforeUpdate`):** Diterapkan otomatis pada `PemudaModel`, `AlamatModel`, `PendidikanModel`, `PekerjaanModel`, dan `OrganisasiModel` sehingga seluruh penyimpanan data dijamin konsisten berformat lowercase.
  3. **Import Spreadsheet (`PemudaImportService`):** Seluruh data hasil parsing file Excel/CSV otomatis dinormalisasi ke format lowercase sebelum disimpan ke database.

### 2026-09-01 — Penambahan Menu Warga MTA pada Superadmin (Data Warga Sragen dari api.mta.or.id)

- **Menu Navigasi Sidebar Superadmin:**
  - Ditambahkan menu **Warga MTA** pada navigasi sidebar (`app/Views/admin/layouts/main.php`) di bawah Menu Utama khusus bagi user dengan role `superadmin`, dilengkapi ikon kartu identitas (`fas fa-id-card text-success`) dan label badge `Sragen`.
- **Routing & Controller:**
  - Didaftarkan route group `admin/warga-mta` dengan proteksi filter `auth` dan `role:superadmin` pada `app/Config/Routes.php`.
  - Dibuat controller `App\Controllers\Admin\WargaMta.php` yang berinteraksi langsung dengan API Pusat `api.mta.or.id`:
    - `index()`: Mengambil daftar warga MTA khusus Perwakilan Sragen (Kode `86`, UUID: `3246792b-f0a7-48ca-95fa-379e3bee777d`). Menyediakan pagination terintegrasi, pencarian cepat (nama/no. HP/alamat), filter 70 Cabang MTA di Sragen, filter jenis kelamin (Putra/Putri), dan filter status PMD lokal.
    - Cross-referencing otomatis dengan database PMD Sragen lokal untuk mendeteksi apakah warga MTA tersebut sudah tercatat sebagai pemuda atau belum.
    - `detail($uuid)`: Mengambil profil lengkap warga MTA (foto, identitas, kontak, orang tua, pernikahan, pekerjaan, dan domisili) dari API MTA baik melalui AJAX Modal interaktif maupun halaman detail mandiri.
    - `import()`: Mendaftarkan/mengimpor warga MTA terpilih menjadi pemuda PMD Sragen ke cabang lokal tujuan secara instan, lengkap dengan alamat, pekerjaan, dan verifikasi otomatis (`verified`).
- **User Interface & UX:**
  - Dibuat view `app/Views/admin/warga_mta/index.php` dan `app/Views/admin/warga_mta/detail.php`.
  - Dilengkapi widget statistik ringkas (Total Warga MTA Sragen, Total Cabang MTA di Sragen, Warga Tersinkron PMD, Status Sumber API).
  - Tampilan tabel responsif dengan badge status PMD, tautan WhatsApp instan, modal detail AJAX, dan modal impor ke PMD dengan auto-match cabang lokal.
- **Testing & Validasi:**
  - Dibuat unit test `tests/unit/WargaMtaTest.php` untuk memverifikasi controller, endpoint routes, ketersediaan view, keberadaan menu sidebar, dan scope API Sragen. Seluruh 39 unit test berjalan sukses 100%.

### 2026-09-01 — Autocomplete Pencarian Warga MTA pada Form Pendataan Publik Berdasarkan Cabang Terpilih

- **Alur & Interaksi Pengguna (User Flow):**
  - Pada formulir pendataan publik (`/pendataan`), setelah pengguna memilih **Cabang Pemuda MTA**, kolom **Nama Lengkap** mengaktifkan pencarian live autocomplete yang terhubung langsung ke API MTA Pusat (`api.mta.or.id/api/v1/warga/search`) dengan filter cabang lokal yang dipilih (`mta_uuid`).
  - Saat pengguna mengetikkan huruf/nama (minimal 2 karakter) dengan mekanisme debouncing (300ms), muncul dropdown interaktif yang menampilkan daftar nama warga MTA di cabang tersebut beserta nomor warga, jenis kelamin (Putra/Putri), usia, alamat, serta penanda status apakah sudah terdaftar di sistem PMD lokal atau belum.
  - Pengguna dapat mengeklik salah satu warga yang sesuai dari daftar saran.
- **Auto-Populate Data Formulir:**
  - Begitu warga dipilih, sistem memanggil endpoint detail dan otomatis mengisikan data ke formulir:
    - Nama Lengkap (`name`)
    - Jenis Kelamin (`gender`: L / P)
    - Tanggal Lahir (`birth_date`)
    - Tempat Lahir (`birth_place`)
    - Nomor HP / WhatsApp (`phone`)
    - Status Pernikahan (`marital_status`)
    - Golongan Darah (`blood_type`)
    - Alamat Lengkap (`address_detail`), Dusun (`dusun`), RT (`rt`), RW (`rw`)
    - Pencocokan otomatis Kecamatan (`district_id`) dan Desa (`village_id`) di Sragen
    - Identitas keterhubungan UUID Warga MTA (`mta_warga_uuid`)
  - Jika warga tersebut sudah pernah terdaftar di PMD Sragen, sistem otomatis memuat data profil lengkapnya (pendidikan, pekerjaan, organisasi, keahlian, minat) dalam mode "Melengkapi & Memperbarui Data Terdaftar".
  - Jika belum terdaftar di PMD Sragen, ditampilkan banner notifikasi hijau bahwa data warga MTA berhasil dimuat dan pengguna tinggal melengkapi langkah data berikutnya (Pendidikan, Pekerjaan, Organisasi, Keahlian, Minat) sampai selesai.
  - Pengguna tetap memiliki fleksibilitas untuk membatalkan pilihan atau melanjutkan pendaftaran baru secara mandiri jika nama yang diketik tidak terdaftar di data warga MTA cabang tersebut.
- **Backend & Endpoint:**
  - Ditambahkan endpoint publik:
    - `GET /pendataan/search-warga`: menerima parameter `cabang_id` dan `q`, memetakan UUID cabang ke API MTA, serta melakukan cross-check dengan data pemuda lokal.
    - `GET /pendataan/warga-detail/(:segment)`: mengambil detail warga MTA berdasarkan UUID dan memformatnya sesuai kebutuhan field formulir pendaftaran.
  - Ditambahkan proteksi rate limiting/throttling pada endpoint AJAX pencarian.
- **Testing & Validasi:**
  - Dibuat unit test `tests/unit/WargaMtaAutocompleteTest.php` untuk memverifikasi metode controller, definisi rute publik, komponen UI view, dan ketersediaan fungsi JavaScript.
  - Seluruh 43 unit test proyek lulus 100% tanpa error.

### 2026-09-01 — Portal Pengaturan Konten Beranda (Homepage) Khusus Superadmin

- **Database Migration & Seeder:**
  - Dibuat migration `app/Database/Migrations/2026-09-01-231500_CreateHomepageSettingsTable.php` untuk tabel `homepage_settings` dengan kolom:
    - `id` (INT UNSIGNED AUTO_INCREMENT PRIMARY KEY)
    - `group` (VARCHAR 50, indexed)
    - `key` (VARCHAR 100, UNIQUE)
    - `value` (LONGTEXT, nullable)
    - `type` (ENUM 'text', 'textarea', 'json', 'number', 'boolean', 'image')
    - `label` (VARCHAR 255)
    - `created_at` & `updated_at` (DATETIME)
  - Dibuat model `App\Models\HomepageSettingModel` yang memuat konfigurasi nilai bawaan (`getDefaults()`), getter/setter dinamis (`getAllSettings()`, `getSetting()`, `setSetting()`), dan fungsi pemulihan (`resetToDefaults()`).
  - Dibuat seeder `App\Database\Seeds\HomepageSettingSeeder` yang didaftarkan ke `DatabaseSeeder.php` untuk menginisialisasi 41 item pengaturan bawaan landing page.
- **Controller & Authorization Superadmin:**
  - Dibuat controller `App\Controllers\Admin\HomepageSetting.php` dengan pembatasan hak akses strictly khusus role `superadmin` via route filter `role:superadmin` dan method guard `ensureSuperadmin()`.
  - Metode `index()`: Membaca seluruh pengaturan dari database, mendekode format JSON (highlight chips, 4 misi strategis, 6 divisi program kerja, 4 langkah alur pendataan, dan daftar tanya jawab FAQ) untuk dimuat ke dalam tab portal.
  - Metode `update()`: Memvalidasi dan menyimpan perubahan teks, textarea, serta struktur array/JSON secara terpadu, dilengkapi token CSRF dan flash notification.
  - Metode `reset()`: Mengembalikan seluruh konten halaman muka ke setelan bawaan sistem.
- **Tampilan Portal Pengaturan AdminLTE 3:**
  - Dibuat view `app/Views/admin/homepage/index.php` yang terstruktur dalam 7 tab navigasi tematik:
    1. **Header & Hero Banner:** Badge pill, Judul Hero, Subjudul, Teks Tombol Pendaftaran, Kartu Samping (Keuntungan/Manfaat), Angka Counter Bidang Pengabdian, dan Highlight Chips interaktif (bisa tambah/hapus baris).
    2. **Tentang & Visi Misi:** Tag Section, Judul Profil, Paragraf 1 & 2, Teks Visi Organisasi, dan 4 Pilar Misi Strategis.
    3. **Struktur Wilayah:** Tag Section, Judul Wilayah, dan Deskripsi Pengantar Wilayah & Cabang.
    4. **Bidang & Program Kerja:** Tag Section, Judul Program, Deskripsi Pengantar, serta Kartu Program Kerja (ikon, warna tema, judul, deskripsi, dan jadwal/badge) dengan tombol tambah/hapus kartu.
    5. **Alur & Banner CTA:** Tag Section, Judul Alur, 4 Tahapan Langkah Pengisian Form, dan Banner Ajakan Besar (CTA strip).
    6. **Tanya Jawab (FAQ):** Tag Section, Judul FAQ, Deskripsi Pengantar, serta accordion Q&A dengan tombol tambah/hapus pertanyaan baru.
    7. **Kontak & Sekretariat:** Alamat Fisik Kantor Sekretariat, Nomor WhatsApp Helpdesk, dan Label Keterangan Layanan.
  - Ditambahkan menu navigasi baru **Kelola Homepage** (`fas fa-desktop text-warning`) pada sidebar superadmin (`app/Views/admin/layouts/main.php`).
- **Penerapan Dinamis pada Halaman Depan Publik:**
  - Diperbarui `App\Controllers\Home::index()` untuk memuat data pengaturan homepage dan mengirimkannya ke view.
  - Diperbarui `app/Views/landing.php` agar setiap bagian teks, badge, kartu, misi, program, FAQ, dan link WhatsApp mengambil data dari database secara dinamis dengan fallback nilai default yang aman.
- **Testing & Validasi:**
  - Dibuat unit test suite `tests/unit/HomepageSettingTest.php` (6 test, 40 assertions) untuk menguji model, controller, route definitions, ketersediaan menu sidebar, integritas view portal, dan binding pada landing page.
  - Seluruh 49 unit test proyek lulus 100%.
  - Diverifikasi secara langsung via HTTP curl ke server lokal, pengujian update konten secara live, dan pengujian reset ke default.

### 2026-09-01 — Penyesuaian Kode Cabang Mengikuti Data Resmi API Pusat (api.mta.or.id)

- **Standarisasi Format Kode Cabang:**
  - Menyelaraskan seluruh kode cabang pemuda di database dari format lama (`CBG-xxx`) ke format resmi API Pusat MTA (`86.0`, `86.1`, `86.2`, ..., `86.69`) di mana `86` merupakan kode Perwakilan MTA Sragen dan digit di belakang titik adalah nomor cabang resmi di sistem pusat.
- **Database Migration:**
  - Dibuat migration `app/Database/Migrations/2026-09-01-235000_UpdateCabangCodeFromMtaApi.php` untuk memperbarui seluruh 70 cabang pemuda di tabel `cabang` dengan kode resmi dan UUID dari API Pusat MTA (`api.mta.or.id`).
  - Migration berhasil dieksekusi, sehingga 100% data cabang di database lokal kini menggunakan format kode resmi pusat.
- **Sinkronisasi Database Cabang (`MtaSyncService`):**
  - Diperbarui metode `MtaSyncService::syncCabang()` pada `app/Services/MtaSyncService.php` agar setiap kali proses sinkronisasi cabang dijalankan dari admin panel (`/admin/mta-sync`), kode cabang lokal otomatis disinkronkan dengan data terbaru dari API pusat.
- **Pembaruan Seeder (`CabangSeeder`):**
  - Diperbarui `app/Database/Seeds/CabangSeeder.php` untuk mencakup seluruh 70 cabang lengkap dengan kode resmi `86.x`, pemetaan wilayah 1-4, deskripsi, dan `mta_uuid`.
- **Integrasi Import & Form Input:**
  - Diperbarui `app/Services/PemudaImportService.php` (panduan template, fallback lookups, dan pemetaan cabang) untuk mendukung dan mereferensikan kode `86.x`.
  - Diperbarui `app/Views/admin/cabang/index.php` (modal tambah & edit cabang: placeholder `Contoh: 86.1`).
  - Diperbarui `app/Views/admin/pemuda/import.php` (keterangan format kode cabang).
  - Diperbarui `app/Views/pendataan/form.php` dan `app/Views/admin/pemuda/form.php` agar dropdown pilihan cabang menampilkan kode cabang resmi pusat (contoh: `[86.1] Gemolong 1 (Wilayah 2)`).
- **Testing & Validasi:**
  - Dibuat unit test suite `tests/unit/CabangApiCodeTest.php` (4 test, 16 assertions) untuk menguji format kode seeder, verifikasi database lokal, pemetaan lookups import pemuda, dan view placeholder.
  - Seluruh 53 unit test proyek lulus 100%.

### 2026-09-02 — Pembaruan Format Nomor Registrasi Pemuda (IdPerwakilanIdCabangtanggallahirRandomNomor)

- **Standarisasi Format Nomor Registrasi Pemuda:**
  - Format nomor registrasi diubah dari format lama (`PMD-YYYYMMDD-XXXX`) menjadi format terstruktur 16 digit: `IdPerwakilanIdCabangtanggallahirRandomNomor`.
  - **Struktur Komponen (16 Digit, Tanpa Pemisah):**
    - `IdPerwakilan` (2 digit): Kode Perwakilan MTA Sragen (`86`).
    - `IdCabang` (2 digit): Nomor/kode cabang resmi MTA dengan padding 2 digit (contoh: `86.1` Gemolong 1 -> `01`, `86.6` Gesi -> `06`, `86.10` Jenar -> `10`, `86.42` Sambungmacan 2 -> `42`, `86.0` Sragen Perwakilan -> `00`).
    - `tanggallahir` (8 digit): Tanggal lahir pemuda format `YYYYMMDD` (contoh: `20000517` untuk 17 Mei 2000).
    - `RandomNomor` (4 digit): Angka acak 4 digit unik (`0001` - `9999`) yang diverifikasi keunikannya secara otomatis di database.
  - **Contoh:** Pemuda lahir 17 Mei 2000 di Cabang Gemolong 1 (Kode 86.1) mendapatkan No. Registrasi: `8601200005178234`.
- **Implementasi Model & Controller:**
  - Diperbarui `PemudaModel::generateRegistrationNumber(?int $cabangId = null, ?string $birthDate = null)` di `app/Models/PemudaModel.php`.
  - Diperbarui pemanggilan di `app/Controllers/Pendataan.php` (pendaftaran mandiri).
  - Diperbarui pemanggilan di `app/Controllers/Admin/Pemuda.php` (tambah pemuda oleh admin).
  - Diperbarui pemanggilan di `app/Services/MtaSyncService.php` (sinkronisasi dari API MTA).
  - Diperbarui pemanggilan di `app/Services/PemudaImportService.php` (import massal Excel).
  - Diperbarui fallback tampilan di `app/Views/pendataan/sukses.php` dan contoh teks di `HomepageSettingModel.php`.
- **Testing & Validasi:**
  - Diperbarui `tests/unit/PemudaManagementTest.php` untuk menguji struktur format 16 digit, kecocokan kode cabang & tanggal lahir, serta format default.
  - Seluruh 53 unit test di `tests/unit/` lulus 100%.

### 2026-09-02 — Penegakan Otomatisasi Status Verifikasi (2 Status Berdasarkan Sinkronisasi MTA Pusat)

- **Kebijakan & Ketentuan Status Verifikasi:**
  - Status verifikasi dipangkas menjadi **hanya 2 status**:
    1. **`verified` (Terverifikasi)**: jika data pemuda tersinkronisasi / cocok dengan Database Warga MTA Pusat (`api.mta.or.id`).
    2. **`pending` (Belum Terverifikasi)**: jika data pemuda belum tersinkronisasi / tidak ditemukan di MTA Pusat.
  - Status `rejected` (Ditolak) ditiadakan.
  - **Larangan Modifikasi Manual:** Status verifikasi tidak dapat diubah atau dimanipulasi secara manual oleh siapapun, baik Superadmin, Admin Wilayah, maupun Admin Cabang.
- **Implementasi Backend & Controller:**
  - `Admin\Pemuda::save()` & `Admin\Pemuda::update()`: Menghapus input manual `status_verifikasi` dari formulir. Status ditentukan secara otomatis melalui panggilan `MtaSyncService::verifyYouthAgainstMta()`.
  - `Admin\Pemuda::verifikasi($id)`: Diubah dari endpoint toggle status manual menjadi aksi pemeriksaan & sinkronisasi live terhadap API MTA Pusat.
  - `Pendataan::simpan()`: Status verifikasi pendaftar mandiri secara ketat mengikuti hasil pencocokan API MTA Pusat.
  - `PemudaImportService`: Seluruh pemuda hasil impor spreadsheet di-set default `pending` (Belum Terverifikasi) sampai disinkronkan dengan API MTA.
  - `PemudaModel::getCountsSummary()`: Ringkasan statistik hanya menghitung `verified` dan `pending`.
- **Implementasi Antarmuka (UI/UX):**
  - `app/Views/admin/pemuda/form.php`: Dropdown pilihan verifikasi dihapus dan diganti dengan informasi status read-only (badge + indikator sinkronisasi pusat).
  - `app/Views/admin/pemuda/index.php`: Dropdown toggle manual pada baris tabel diganti dengan badge status informatif. Tab filter "Ditolak" dihapus. Ditambahkan opsi "Sinkronkan MTA" pada dropdown aksi baris.
  - `app/Views/admin/pemuda/detail.php`: Dropdown ubah status verifikasi dihapus.
  - `app/Views/admin/dashboard/index.php`: Kotak statistik utama disederhanakan menjadi 3 card: Total Pemuda, Terverifikasi (Sinkron Pusat), dan Belum Terverifikasi.
  - `app/Views/admin/pemuda/cetak.php`: Format cetak menampilkan status "TERVERIFIKASI (SINKRON PUSAT)" atau "BELUM TERVERIFIKASI".
  - `app/Views/pendataan/sukses.php`: Keterangan status sukses pendaftaran diperjelas menjadi Terverifikasi Otomatis vs Belum Terverifikasi.
- **Pengujian:**
  - Dibuat unit test suite `tests/unit/VerificationPolicyTest.php` (3 test, 7 assertions).
  - Seluruh 56 unit test proyek lulus 100%.

### 2026-09-02 — Penggabungan Fitur Check Data dengan Search Warga & Autocomplete Terpadu

- **Pencarian Terpadu (Unified Search & Check Data):**
  - Menggabungkan fitur pemeriksaan data pemuda dengan pencarian warga MTA saat mengetik nama pada formulir pendataan publik.
  - Endpoint `Pendataan::searchWarga` diperbarui untuk mencari secara simultan di dua sumber data:
    1. **Database MTA Pusat** (via `MtaApiService::searchWarga` berdasarkan cabang terpilih).
    2. **Database Lokal Pemuda PMD** (tabel `pemuda` dan `alamat` untuk cabang terpilih).
  - Hasil pencarian digabungkan (merge) dan dideduplikasi secara cerdas:
    - `both`: Warga tercatat di MTA Pusat dan sudah terdaftar di PMD Lokal (badge *Terdaftar di PMD* & *Terhubung MTA*).
    - `pmd`: Pemuda terdaftar di database PMD lokal cabang terkait (badge *Terdaftar di PMD (Lokal)*).
    - `mta`: Warga tercatat di MTA Pusat namun belum terdaftar di PMD (badge *Warga MTA Pusat (Belum Terdaftar PMD)*).
- **Penanganan Pemuda Terdaftar & Endpoint Detail Lokal:**
  - Ditambahkan endpoint `GET /pendataan/pemuda-detail/(:num)` pada `Pendataan::pemudaDetail($id)` untuk memuat data lengkap pemuda lokal (identitas, alamat, pendidikan, pekerjaan, organisasi, keahlian, dan minat) secara instan.
  - Ketika memilih hasil pemuda yang sudah terdaftar di PMD, formulir otomatis terisi penuh dan mode formulir berganti ke "Mode Melengkapi & Memperbarui Data Terdaftar" dengan nomor registrasi yang bersangkutan.
- **Dukungan Pendaftaran / Input Data Baru:**
  - Jika nama belum ditemukan di database MTA Pusat maupun PMD Lokal:
    - Dropdown menampilkan kartu interaktif: *"Nama [nama] Belum Ada di MTA Pusat maupun PMD Cabang... Silakan lanjutkan untuk mendaftar sebagai pemuda baru"*.
    - Tombol aksi `[+ Input Data Baru dengan Nama Ini]` (`selectNewPemudaInput()`) mengaktifkan formulir baru, membersihkan ID terkait, menampilkan konfirmasi Mode Pendaftaran Baru, dan mengarahkan fokus user ke input berikutnya.
  - Jika hasil pencarian ada tetapi nama pendaftar berbeda, disediakan opsi di bagian bawah dropdown: `[+ Nama Tidak Tercantum? Input Data Baru]`.
- **UI/UX, Cache-Busting & Bugfix Data Pemuda:**
  - Penyederhanaan antarmuka Step 1 `app/Views/pendataan/form.php`: kotak menu "Fitur Pengecekan Data Terpadu" dihapus dari form sehingga antarmuka lebih bersih dan terfokus pada pencarian otomatis saat mengetik nama. Kontainer feedback dinamis `#check-data-result-wrapper` tetap dipertahankan untuk notifikasi mode data.
  - Ditambahkan cache-busting `?v=filemtime` pada pemanggilan `js/pendataan.js` di `app/Views/pendataan/form.php` untuk mencegah browser menggunakan cache berkas JS lama.
  - **Perbaikan Bug Pemilihan Data Pemuda Lokal:**
    - Pada `Pendataan::wargaDetail($uuid)`, ditambahkan pengecekan prioritas ke database lokal pemuda (`findByMtaWargaUuid`, pencarian ID numerik, dan `registration_number`). Jika ditemukan di database pemuda lokal, sistem langsung mengembalikan data profil lengkap pemuda tanpa perlu memanggil API MTA Pusat.
    - Pada `Pendataan::searchWarga`, pemetaan UUID untuk data lokal pemuda dijamin valid (menggunakan `mta_warga_uuid` atau ID pemuda lokal sebagai fallback) agar tidak terjadi pencarian `null` ke MTA Pusat.
    - Pada `public/js/pendataan.js`, fungsi pemilihan item dropdown diubah menjadi `selectSuggestionByIndex(idx)` yang membaca objek data langsung dari array tanpa risiko kesalahan parsing parameter atau event bubbling button, serta dilengkapi fallback multi-layer ke `selectLocalPemuda` dan `selectWargaMta`.
  - **Pembatasan Ketat Pencarian Berdasarkan Cabang Terpilih:**
    - Pada `MtaApiService::searchWarga`, parameter `cabang_uuid` kini diteruskan ke query parameter `cabang` API MTA Pusat.
    - Pada `Pendataan::searchWarga`, pencocokan kode cabang MTA dilakukan dengan memverifikasi kesamaan nomor urut cabang (misal Gemolong 1 vs Gemolong 2).
    - Ditambahkan filter server-side ketat pada hasil respon API MTA Pusat sehingga data warga dari cabang lain (misal Sragen Kota, Masaran, dsb.) secara otomatis disaring dan tidak akan ditampilkan.
    - Setiap item hasil pencarian kini memuat atribut cabang yang dipilih dan menampilkan badge cabang pada dropdown.
    - Pada `public/js/pendataan.js`, perubahan cabang pada dropdown TomSelect otomatis menutup saran lama dan memicu ulang pencarian khusus untuk cabang yang baru dipilih.
  - Diperbarui suite pengujian `tests/unit/WargaMtaAutocompleteTest.php` untuk memvalidasi method baru, rute baru, elemen view, fungsi JavaScript, serta penanganan validasi.

### 2026-09-03 — Optimalisasi Penuh Tampilan & Pengalaman Pengguna Ponsel (Mobile Phone Friendly)

- **Mobile Viewport & Global UX (`public/css/main.css`):**
  - Mencegah pergeseran / overflow horizontal liar dengan `overflow-x: hidden`, `-webkit-text-size-adjust: 100%`, dan `touch-action: manipulation` (menghilangkan delay tap 300ms di peramban seluler).
  - Penyesuaian safe-area insets (`padding-bottom: env(safe-area-inset-bottom)`) untuk perangkat mobile modern.
  - Navbar responsif: penyesuaian ukuran brand title (`0.92rem`) dan icon (`32px`) pada layar < 576px agar tidak mendesak tombol hamburger menu.
  - Menu hamburger dropdown di ponsel dibuat lebih elegan menyerupai bottom sheet / card dengan gradient merah, padding nyaman, dan tombol aksi full-width.
  - Penataan ukuran font input & select minimal 16px pada layar mobile (<= 768px) untuk **mencegah bug auto-zoom otomatis iOS Safari**.
  - Ukuran target sentuh (touch target) tombol dan kontrol minimal 46px-48px untuk kenyamanan jari tangan.

- **Formulir Pendataan Publik (`app/Views/pendataan/form.php`, `public/css/pendataan.css`, `public/js/pendataan.js`):**
  - **Mobile Step Progress Header:** Ditambahkan banner pelacak langkah responsif khusus tampilan mobile (`.mobile-stepper-header`) yang menampilkan nomor langkah ("Langkah X/8"), nama langkah, persentase penyelesaian, dan progress bar dinamis.
  - **Horizontal Scrollable Stepper:** Stepper 8 lingkaran pada layar mobile diubah menjadi scrolling horizontal yang mulus dengan scrollbar tersembunyi tanpa tumpang tindih / gepeng.
  - **Sinkronisasi Otomatis:** Saat berpindah langkah, JavaScript (`updateProgress`) otomatis memperbarui badge, nama langkah, persentase, dan menggulir lingkaran langkah aktif ke tengah layar secara horizontal (`scrollIntoView`).
  - **Form Step Actions:** Tombol "Kembali" dan "Selanjutnya" diubah menjadi responsif (`.form-step-actions` dengan `flex-column-reverse flex-sm-row`), sehingga pada layar ponsel tombol utama "Selanjutnya" berada di atas dengan lebar penuh (full-width) yang mudah dijangkau ibu jari, disusul tombol "Kembali" di bawahnya.
  - **Card Padding Responsif:** Mengubah padding kartu formulir dari `p-4 p-md-5` menjadi `p-3 p-sm-4 p-md-5` agar area input di layar ponsel 320px–400px lebih luas dan tidak sempit.
  - **Autocomplete Dropdown Mobile:** Penataan kartu hasil pencarian nama warga MTA / pemuda lokal agar otomatis berganti tata letak vertikal bertumpuk (`flex-column`), tombol "Pilih / Lengkapi" menjadi full-width, serta teks rincian tidak terpotong.
  - **Komponen Interaktif:** Kartu jenis kelamin (`.gender-card-select`), checkbox organisasi (`.org-card`), matriks keahlian (`.skill-card`), dan pills minat (`.interest-tag-label`) dioptimalkan dengan padding dan touch target yang ramah sentuhan jari.
  - **Scroll Halus ke Header:** Navigasi langkah di ponsel menggulir otomatis ke posisi awal kartu langkah dengan memperhitungkan tinggi sticky header.

- **Halaman Beranda / Landing Page (`public/css/landing.css`):**
  - Hero section responsif: font title disesuaikan (`1.65rem`), tombol CTA utama dan outline full-width pada ponsel dengan padding nyaman.
  - Stats strip: grid 2x2 pada layar kecil dengan ukuran angka (`1.55rem`) dan padding kompak untuk mencegah angka terpotong.
  - Struktur 4 Wilayah: tombol navigasi tab diubah menjadi grid 2x2 yang ringkas pada tablet/ponsel agar tidak memakan ruang vertikal terlalu panjang.
  - Banner CTA & kartu program kerja: padding responsif dan tombol full-width di ponsel.

- **Halaman Sukses & Login (`app/Views/pendataan/sukses.php`, `app/Views/auth/login.php`, `public/css/auth.css`):**
  - Halaman sukses: padding kartu `p-3 p-sm-4 p-md-5`, ukuran font nomor registrasi responsif (`text-break`, `fs-2 fs-sm-1`), dan tombol aksi full-width bertumpuk di ponsel.
  - Halaman login: kartu login `col-12 col-sm-9`, input font 16px untuk iOS Safari, dan tombol login dengan touch target 48px.

- **Dashboard Admin (`public/css/admin.css`):**
  - Scrolling sentuh lancar pada `.table-responsive` (`-webkit-overflow-scrolling: touch`).
  - Penataan `.btn-group` pada toolbar agar membungkus rapi (wrap) di ponsel tanpa memecah batas layar horizontal.

### 2026-09-04 — Implementasi Versi Mobile Penuh & Progressive Web App (PWA)

- **Progressive Web App (PWA) & Web App Manifest (`public/manifest.json`, `public/sw.js`, `public/offline.html`, `public/icons/`):**
  - Dibuat Web App Manifest lengkap (`public/manifest.json`) dengan konfigurasi `standalone`, orientasi portrait, tema warna brand `#dc2626`, dan pintasan aplikasi (Shortcuts: Form Pendataan, 4 Wilayah & Cabang, Portal Admin).
  - Dibuat set ikon aplikasi PWA lengkap di `public/icons/` (`icon-72x72.png`, `icon-96x96.png`, `icon-128x128.png`, `icon-144x144.png`, `icon-152x152.png`, `icon-192x192.png`, `icon-384x384.png`, `icon-512x512.png`, `apple-touch-icon.png`, dan `maskable-icon-512x512.png`).
  - Dibuat Service Worker (`public/sw.js`) dengan pre-caching aset inti, strategi stale-while-revalidate untuk aset statis, network-first untuk navigasi halaman, dan pembersihan cache otomatis saat versi diperbarui.
  - Dibuat halaman fallback offline (`public/offline.html`) yang informatif dan ramah pengguna saat koneksi internet terputus.
  - Dibuat skrip instalasi mobile pintar (`public/js/pwa-install.js`) yang menangani `beforeinstallprompt` dengan banner instalasi elegan ("Pasang Aplikasi Pemuda MTA di HP") serta instruksi visual untuk pengguna Safari iOS.

- **Mobile Bottom Navigation Bar (Bilah Navigasi Bawah Khusus Ponsel):**
  - **Area Publik (`app/Views/layouts/main.php`, `public/css/main.css`):**
    - Ditambahkan bilah navigasi bawah sticky bergaya aplikasi native (`.mobile-bottom-nav`) dengan 5 tab: Beranda, Cabang, Form Pendataan (dengan Floating Action Button / FAB merah mencolok di tengah), FAQ, dan Admin.
    - Penyesuaian `safe-area-inset-bottom` dan padding bottom dinamis pada `body` agar konten halaman tidak tertutup bilah navigasi.
  - **Area Admin (`app/Views/admin/layouts/main.php`, `public/css/admin.css`):**
    - Ditambahkan bilah navigasi bawah khusus pengurus (`.admin-mobile-bottom-nav`) dengan 5 tab: Dashboard, Data Pemuda, Tambah Pemuda (center FAB biru), Cabang, dan Menu (tombol drawer sidebar).
    - Ditambahkan indikator badge scope ringkas (`d-inline-block d-md-none`) pada navbar atas agar pengurus cabang/wilayah tetap dapat melihat scope akses aktifnya di layar sempit.

- **Tampilan Kartu Adaptif Ponsel (Mobile Card View) untuk Data Admin:**
  - **Manajemen Data Pemuda (`app/Views/admin/pemuda/index.php`):**
    - Menggantikan tabel 9 kolom yang harus digulir horizontal di ponsel dengan tampilan kartu (`.pemuda-mobile-cards` dan `.pemuda-card-item`).
    - Kartu menampilkan inisial avatar, status verifikasi, nomor registrasi, wilayah & cabang, demografi, tombol cepat WhatsApp langsung, serta aksi Detail, Edit, Cetak, dan dropdown Arsip/Hapus.
  - **Master Cabang (`app/Views/admin/cabang/index.php`):**
    - Menghadirkan tampilan kartu khusus ponsel (`.cabang-mobile-cards`) lengkap dengan status ketersediaan gelombang, jadwal, ustadz, kontak pimpinan WhatsApp, serta aksi Detail modal, Edit, dan Hapus.
  - **Dashboard Admin (`app/Views/admin/dashboard/index.php`):**
    - Menata ulang info-box statistik pada baris kedua menjadi grid 2x2 responsif yang proporsional di layar ponsel.

- **Pengujian Unit (`tests/unit/MobileVersionTest.php`):**
  - Ditambahkan 10 metode pengujian unit yang memvalidasi integritas `manifest.json`, seluruh ikon PWA, service worker, halaman offline, skrip instalasi, meta tag di seluruh layout, tampilan kartu ponsel, dan aturan CSS responsif. Seluruh 69 pengujian unit (382 asersi) berhasil 100%.

### 2026-09-06 — Penambahan Tautan Google Maps / Lokasi Cabang

- **Penambahan Kolom Database pada Tabel `cabang`:**
  - `maps_url` (VARCHAR 500, NULL): Tautan / link Google Maps atau koordinat lokasi cabang / sekretariat pemuda.
- **Migration & Model:**
  - Dibuat migration `2026-09-06-034500_AddMapsUrlToCabang.php` yang menambahkan kolom `maps_url` setelah `alamat`.
  - Diperbarui `CabangModel.php` whitelist `$allowedFields` menyertakan `maps_url`.
- **Helper Normalisasi URL Google Maps (`app/Common.php`):**
  - Ditambahkan fungsi helper `formatMapsUrl(?string $input): ?string` untuk menangani berbagai bentuk masukan admin:
    - URL penuh HTTPS/HTTP (`https://maps.app.goo.gl/...`, `https://goo.gl/maps/...`).
    - Domain tanpa protokol (otomatis diberi prefiks `https://`).
    - Kode sematan iframe (`<iframe src="...">` diekstrak URL-nya secara otomatis).
    - Format koordinat lintang/bujur (dikonversi ke query pencarian Google Maps).
    - Kata kunci lokasi umum (dikonversi ke Google Maps search URL).
- **Controller Admin Cabang (`app/Controllers/Admin/Cabang.php`):**
  - Whitelist aturan validasi `maps_url` (`permit_empty|max_length[500]`) pada method `simpan()` dan `update()`.
  - Normalisasi otomatis menggunakan `formatMapsUrl()` sebelum data disimpan ke database.
- **Tampilan Antarmuka Master Cabang (`app/Views/admin/cabang/index.php`):**
  - **Tampilan Kartu Ponsel (`.cabang-mobile-cards`):** Ditambahkan badge/tombol buka Google Maps langsung pada informasi alamat cabang.
  - **Tabel Desktop/Tablet:** Menampilkan badge Google Maps di kolom Alamat yang dapat diklik langsung membuka peta lokasi di tab baru.
  - **Modal Detail Cabang:** Seksi khusus "Lokasi Google Maps" dengan tombol interaktif "Buka di Google Maps" serta teks URL tujuan.
  - **Modal Tambah & Edit Cabang:** Formulir input "Link Google Maps / Lokasi Cabang" dengan ikon, placeholder informatif, dan petunjuk penggunaan.
  - **JavaScript Handler:** Penyesuaian modal detail dan modal edit untuk mengisi dan menampilkan data `maps_url` secara dinamis.
- **Pengujian Unit (`tests/unit/CabangDetailTest.php`):**
  - Pengujian CRUD kolom `maps_url` pada `CabangModel`.
  - Pengujian fungsi helper `formatMapsUrl` untuk berbagai jenis format link, kode iframe, koordinat, dan string kosong.
  - Pengujian rendering elemen `maps_url` pada antarmuka master cabang.
  - Seluruh 71 unit test (398 assertions) lulus 100%.

### 2026-09-06 — Penghapusan Masa Keanggotaan & Jabatan pada Keikutsertaan Organisasi

- **Pembaruan Struktur Database Tabel `organisasi`:**
  - Dibuat migration `2026-09-06-040500_RemoveMasaKeanggotaanDanJabatanFromOrganisasi.php` yang menghapus kolom `position` (jabatan), `join_date`, dan `end_date` (masa keanggotaan).
  - Diperbarui `OrganisasiModel.php` whitelist `$allowedFields` hanya mencakup `pemuda_id`, `organization_name`, dan `description`.
  - Diperbarui `PemudaModel::getDetail()` pengurutan relasi organisasi diubah menjadi `orderBy('id', 'ASC')`.
- **Formulir Pendaftaran Publik (`app/Views/pendataan/form.php`, `public/js/pendataan.js`):**
  - Pada langkah 5 (Keikutsertaan Organisasi & Penugasan), sub-panel detail (`.org-detail-wrapper`) berisi input "Jabatan/Posisi" dan "Tahun Bergabung" dihilangkan.
  - Pilihan organisasi kini berupa kartu centang/seleksi langsung yang elegan dan responsif.
  - Diperbarui `public/js/pendataan.js`:
    - Fungsi `toggleOrgDetail()` disederhanakan untuk menandai status seleksi kartu (`.selected`).
    - Skrip review langkah 8 (`prepareReview`) menampilkan nama organisasi murni tanpa embel-embel jabatan.
    - Pengisian otomatis (autofill) saran warga MTA langsung mencentang kartu organisasi terkait.
- **Formulir Admin Pemuda (`app/Views/admin/pemuda/form.php`, `app/Controllers/Admin/Pemuda.php`):**
  - Dihapus panel subform input Jabatan dan Tahun Gabung pada modal/halaman tambah & edit pemuda.
  - Method `store()` dan `update()` di `Admin\Pemuda.php` diperbarui untuk menyimpan `organization_name` secara ringkas tanpa atribut posisi/tanggal.
- **Tampilan Detail & Cetak Pemuda (`app/Views/admin/pemuda/detail.php`, `app/Views/admin/pemuda/cetak.php`):**
  - Pada halaman detail pemuda, tabel riwayat organisasi dengan kolom Jabatan dan Masa Keanggotaan digantikan dengan badge daftar unit tugas/organisasi yang bersih dan rapi.
  - Pada lembar cetak data pemuda (`cetak.php`), daftar organisasi ditampilkan murni sebagai nama-nama organisasi tanpa teks `(Anggota)`.
- **Layanan Import Pemuda (`app/Services/PemudaImportService.php`):**
  - Dihapus pengisian default posisi `'anggota'` saat import data organisasi dari file Excel/CSV.
- **Pengujian Unit (`tests/unit/PendataanFormTest.php`):**
  - Diperbarui pengujian pemrosesan organisasi `testOrganizationFilterOnlySelected`.
  - Ditambahkan metode pengujian `testOrganisasiFieldsRemovedFromViewsAndModel` untuk memverifikasi ketiadaan field `position` dan `join_year/join_date` di form publik, form admin, view detail, dan model.
  - Seluruh 72 unit test (408 assertions) berjalan sukses 100%.

### 2026-09-06 — Penggunaan Logo Pemuda MTA sebagai Logo Utama Sistem

- **Penetapan Logo Utama (`public/icons/pemudamta.png`):**
  - Menggunakan logo resmi Pemuda MTA (`public/icons/pemudamta.png`) sebagai identitas visual utama pada seluruh antarmuka aplikasi publik dan dashboard admin.
- **Penerapan pada Antarmuka Publik & Formulir:**
  - **Navbar Publik (`app/Views/layouts/main.php`, `public/css/main.css`):** Menggantikan ikon font generic dengan logo resmi Pemuda MTA pada lingkaran badge putih berkontras tinggi (`.navbar-brand-icon` & `.navbar-brand-img`).
  - **Footer Publik (`app/Views/layouts/main.php`):** Menampilkan logo Pemuda MTA pada identitas lembaga di footer halaman.
  - **Formulir Pendataan (`app/Views/pendataan/form.php`):** Menambahkan logo resmi Pemuda MTA di atas hero header formulir untuk memperkuat kredibilitas pendaftaran.
  - **Landing Page (`app/Views/landing.php`):** Menampilkan logo resmi pada kartu informasi portal pendataan.
- **Penerapan pada Area Admin & Dokumen:**
  - **Sidebar AdminLTE (`app/Views/admin/layouts/main.php`):** Menggantikan ikon generic pengguna dengan logo resmi Pemuda MTA berlatar putih melingkar di panel Brand Logo navigasi admin.
  - **Halaman Login Admin (`app/Views/auth/login.php`):** Mengintegrasikan logo Pemuda MTA di dalam brand header kartu login (`.brand-icon`).
  - **Lembar Cetak Biodata Pemuda (`app/Views/admin/pemuda/cetak.php`):** Menambahkan logo resmi Pemuda MTA berdampingan dengan kop surat resmi Majlis Tafsir Al-Qur'an (MTA) Perwakilan Sragen.
- **PWA & Identitas Tab Browser:**
  - Menambahkan `<link rel="shortcut icon">` dan `<link rel="icon">` yang mengarah ke `icons/pemudamta.png` pada seluruh layout (publik, admin, login, cetak, dan `offline.html`).
  - Meregenerasi seluruh variasi resolusi icon PWA di `public/icons/` (`icon-72x72.png` s/d `icon-512x512.png`, `apple-touch-icon.png`, dan `maskable-icon-512x512.png`) bersumber dari master logo resmi `pemudamta.png`.
- **Pengujian Unit (`tests/unit/MainLogoTest.php`):**
  - Ditambahkan 5 pengujian unit untuk memvalidasi keberadaan master file logo `pemudamta.png`, tipe gambar PNG, serta referensi logo pada layout publik, layout admin, halaman login, dan kop cetak biodata.
  - Seluruh 77 unit test (418 asersi) berhasil 100%.

### 2026-09-06 — Standardisasi Penulisan Kata "Majelis" Menjadi "Majlis"

- **Standardisasi Penulisan:**
  - Mengubah seluruh kata "majelis" / "Majelis" / "MAJELIS" menjadi "majlis" / "Majlis" / "MAJLIS" (Majlis Tafsir Al-Qur'an / MTA) di seluruh kode, view, database, dan antarmuka.
- **Pembaruan View & Template:**
  - `app/Views/layouts/main.php`: Penulisan identitas lembaga pada footer diubah menjadi *Majlis Tafsir Al-Qur'an (MTA)*.
  - `app/Views/admin/pemuda/cetak.php`: Kop surat resmi diubah menjadi *MAJLIS TAFSIR AL-QUR'AN (MTA)*.
  - `app/Views/landing.php`: Teks pill hero badge dan deskripsi pengantar diubah menjadi *Majlis Tafsir Al-Qur'an (MTA)*.
  - `app/Views/admin/homepage/index.php`: Contoh teks placeholder pada form manajemen hero badge diubah menjadi *Majlis Tafsir Al-Qur'an (MTA)*.
- **Pembaruan Model & Nilai Default:**
  - `app/Models/HomepageSettingModel.php`: Memperbarui nilai *default* `hero_badge`, `tentang_desc_1`, dan `faq_list` menjadi "Majlis Tafsir Al-Qur'an".
- **Sinkronisasi Database MySQL:**
  - Memperbarui data yang tersimpan pada tabel `homepage_settings` (`hero_badge`, `tentang_desc_1`, dan `faq_list`) di database MySQL.

### 2026-09-06 — Penambahan Kolom Detail Usaha / Wirausaha pada Form Pendataan

- **Penambahan Kolom Database pada Tabel `pekerjaan`:**
  - Dibuat migration `2026-09-06-051000_AddWirausahaDetailFieldsToPekerjaan.php`.
  - Kolom baru:
    - `business_name` (VARCHAR 255, NULL): Nama usaha / brand / unit bisnis.
    - `business_address` (TEXT, NULL): Alamat tempat usaha / operasional.
    - `business_contact` (VARCHAR 50, NULL): Kontak person usaha / nomor WhatsApp bisnis.
    - `business_social` (VARCHAR 255, NULL): Media sosial usaha (Instagram, Facebook, TikTok, Website, dll).
- **Pembaruan Model (`PekerjaanModel.php` & `PemudaModel.php`):**
  - Diperbarui `$allowedFields` dan callback normalisasi huruf kecil (lowercase) pada `PekerjaanModel`.
  - Diperbarui `PemudaModel::getDetail()` dan `PemudaModel::getPaginatedScoped()` untuk menyertakan kolom detail usaha pada query select, relasi join, dan fitur pencarian multi-kolom admin.
- **Formulir Pendataan Publik (`app/Views/pendataan/form.php` & `public/js/pendataan.js`):**
  - **Panel Dinamis `#panel-detail-wirausaha`:** Ditampilkan secara otomatis ketika pengguna memilih status pekerjaan **Wirausaha / Pemilik Usaha** (ID `5` atau opsi berlabel wirausaha/pemilik usaha).
  - Menyediakan input:
    - Nama Usaha (`business_name`)
    - Jenis / Bidang Usaha (`business_field`)
    - Alamat Tempat Usaha (`business_address`)
    - Kontak Person / No. WA Usaha (`business_contact`)
    - Media Sosial Usaha (`business_social`)
  - **Interaktivitas & UX Cerdas:**
    - Input umum perusahaan / instansi disembunyikan saat mode wirausaha aktif untuk mencegah duplikasi pertanyaan.
    - Input di dalam panel dinonaktifkan (`disabled = true`) saat tersembunyi agar validasi HTML5 langkah tidak terblokir.
    - Autofill data pemuda terdaftar (`fillFormFromExisting`) memuat detail usaha secara otomatis.
    - Ringkasan review langkah 8 (`prepareReview`) menampilkan kartu rincian usaha secara lengkap dan rapi.
- **Formulir & Tampilan Admin Pemuda:**
  - `app/Views/admin/pemuda/form.php`: Ditambahkan panel input dinamis detail wirausaha lengkap dengan handler JavaScript `toggleAdminWirausaha()`.
  - `app/Controllers/Admin/Pemuda.php`: Menangani penyimpanan dan pembaruan kolom detail usaha pada method `store()` dan `update()`.
  - `app/Views/admin/pemuda/detail.php`: Menampilkan seksi kartu informasi detail usaha lengkap dengan tautan interaktif WhatsApp bisnis (`https://wa.me/...`).
  - `app/Views/admin/pemuda/cetak.php`: Menyertakan rincian data wirausaha pada lembar cetak biodata pemuda resmi.
- **Pengujian Unit (`tests/unit/WirausahaDetailTest.php`):**
  - Dibuat 5 metode pengujian unit (31 asersi) yang menguji struktur kolom database, normalisasi lowercase model, elemen form publik & admin, tampilan detail & cetak, serta fungsi toggle JavaScript.
  - Seluruh 82 unit test proyek (449 asersi) lulus 100%.
### 2026-09-07 — Penyederhanaan Tampilan Hasil Pencarian Nama pada Form Pendataan

- **Penyederhanaan Tampilan Dropdown Saran Nama (`public/js/pendataan.js` & `public/css/pendataan.css`):**
  - Mengubah tampilan item hasil pencarian nama warga MTA / pemuda (`renderWargaSuggestions`) menjadi lebih ringkas, bersih, dan sederhana.
  - Hanya menampilkan data esensial:
    - **Nama Pemuda / Warga**: Dicetak tebal dan menyorot kata kunci yang dicari.
    - **L/P (Jenis Kelamin)**: Badge visual kompak (`L` / `P`).
    - **Umur**: Menampilkan usia pemuda (misal `24 Th`) dengan kalkulasi otomatis dari tanggal lahir jika belum tersedia.
    - **Tanggal Lahir**: Menampilkan tanggal lahir dengan format `DD/MM/YYYY`.
    - **Tombol Aksi**: Tombol sederhana `Pilih` dan seluruh baris tetap dapat diklik untuk memilih data secara instan.
  - Menghilangkan badge dan informasi yang memadati tampilan sebelumnya (seperti badge panjang "Terdaftar di PMD", "No. Reg", "Terhubung MTA", "Warga MTA Pusat", status verifikasi, dan teks alamat panjang).
  - Mengoptimalkan styling CSS mobile pada `public/css/pendataan.css` agar baris hasil pencarian tetap tampil rapi, ringkas, dan proporsional di layar ponsel.

### 2026-09-07 — Penyederhanaan Dropdown Pilihan Cabang pada Form Pendataan

- **Penyederhanaan Pilihan Cabang (`app/Views/pendataan/form.php`, `app/Controllers/Pendataan.php`, & `public/js/pendataan.js`):**
  - Dropdown cabang disederhanakan dengan **hanya menampilkan nama cabang saja** (misal: "Sragen Kota", "Gemolong 1", "Masaran 2").
  - Menghapus pembagian grup wilayah (`<optgroup>`), nama wilayah `(Wilayah 1)`, dan kode/id cabang `[CBG-001]`.
  - Mengubah label form dari `Cabang Pemuda MTA (Wilayah)` menjadi `Cabang Pemuda MTA`.
  - Mengurutkan daftar cabang secara alfabetis berdasarkan nama cabang.
  - Memperbarui konfigurasi TomSelect (`searchField: ['text']`) agar pencarian nama cabang lebih cepat dan akurat.

### 2026-09-07 — Pencegahan Data Duplikat / Double Nama pada Menu Import

- **Skip Otomatis Data Duplikat (`app/Services/PemudaImportService.php`):**
  - Mengimplementasikan pengecekan duplikasi ketat berdasarkan kombinasi 4 data: **Nama**, **Jenis Kelamin (L/P)**, **Tanggal Lahir**, dan **Cabang**.
  - Jika terdapat data yang sama dengan data yang sudah ada di database, baris tersebut otomatis **diskip (dilewati)** dan tidak diimport ke database.
  - Jika terdapat data ganda pada berkas Excel yang sama, baris berikutnya otomatis dilewati sehingga tidak ada data kembar yang tersimpan.
  - Data yang diskip karena duplikat tidak membatalkan proses import data valid lainnya (tidak digolongkan sebagai fatal syntax error yang memblokir file).
- **Controller & Tampilan Admin (`app/Controllers/Admin/Pemuda.php`, `app/Views/admin/layouts/main.php`, & `app/Views/admin/pemuda/import.php`):**
  - Notifikasi sukses secara spesifik menampilkan rincian data yang berhasil diimport serta jumlah data duplikat yang dilewati.
  - Menampilkan alert daftar rincian baris yang dilewati beserta nomor registrasi yang sudah terdaftar.
  - Ditambahkan informasi panduan pencegahan duplikasi pada halaman unggah import Excel.
- **Pengujian Unit (`tests/unit/PemudaImportTest.php`):**
  - Ditambahkan pengujian unit `testDuplicateDetectionWithMatchingNameGenderBirthDateAndCabang` untuk memverifikasi pencocokan kunci 4 atribut.

### 2026-09-07 — Penetapan Status Verifikasi Default Import Menjadi Belum Terverifikasi (Pending)

- **Penetapan Status Default & Penghapusan Opsi Dropdown (`app/Views/admin/pemuda/import.php` & `app/Controllers/Admin/Pemuda.php`):**
  - Menghapus dropdown pemilihan status verifikasi default pada halaman import Excel.
  - Menetapkan seluruh data yang diimport otomatis berstatus **Belum Terverifikasi (`pending`)**, sesuai aturan sistem bahwa status `verified` hanya diberikan setelah data sinkron/tercatat di database MTA Pusat.
  - Menampilkan indikator statis bahwa data hasil import berstatus "Belum Terverifikasi (Pending)" dan akan diverifikasi otomatis saat proses sinkronisasi MTA Pusat dijalankan.
  - Memperbarui petunjuk format spreadsheet pada sheet panduan template Excel (`PemudaImportService::generateTemplate()`).

### 2026-09-07 — Pembuatan Menu Export Kustom Data Pemuda (Pilihan Elemen, Bakat, Minat, Filter Lengkap)

- **Menu & Halaman Ekspor Kustom (`app/Views/admin/pemuda/export.php` & `app/Views/admin/layouts/main.php`):**
  - Disediakan menu "Export Data" pada sidebar admin (`admin/pemuda/export`) yang dapat diakses oleh semua role sesuai scope masing-masing.
  - Fitur kustomisasi elemen data (kolom) fleksibel yang terbagi dalam 7 kategori: Data Pribadi, Wilayah & Cabang, Alamat & Domisili, Pendidikan, Pekerjaan & Wirausaha, Organisasi / Bakat / Minat, serta Status & Sistem.
  - Disediakan tombol preset cepat: Standar (13 kolom), Lengkap/Semua (37 kolom), Kontak & Alamat (12 kolom), Bakat & Potensi (13 kolom), serta Wirausaha / Usaha (12 kolom).
  - Saringan khusus Bakat & Keahlian (Skills) dan Minat (Interests) dengan kotak pencarian interaktif dan multi-seleksi.
  - Filter demografi dan wilayah komprehensif: Wilayah & Cabang (dependent dropdown AJAX), Jenis Kelamin, Status Verifikasi, Status Pekerjaan, Jenjang Pendidikan, Rentang Usia (min & max), Riwayat Organisasi, Status Data, dan Periode Registrasi.
  - Penghitung data real-time (*live count preview*) via AJAX (`admin/pemuda/export/count`) saat filter diubah.
  - Pilihan format unduhan: Microsoft Excel (`.xlsx`) dengan styling rapi dan Comma-Separated Values (`.csv`) UTF-8 BOM.
- **Service Ekspor (`app/Services/PemudaExportService.php`):**
  - Membangun file spreadsheet Excel secara native menggunakan PhpSpreadsheet dengan header hijau emerald, border rapi, auto-fit lebar kolom, dan pemformatan teks nomor telepon.
  - Mengimplementasikan batch relational loading untuk tabel `organisasi`, `pemuda_skills`, dan `pemuda_interests` guna efisiensi query.
  - Menyediakan `streamCsv()` untuk keluaran CSV efisien dan `countFiltered()` untuk AJAX counter.
- **Model Query Builder (`app/Models/PemudaModel.php`):**
  - Memperkaya `getFilteredQuery()` dengan filter `skill_id` (tunggal/array), `interest_id` (tunggal/array), `organization_name`, `min_age`, dan `max_age`.
- **Controller & Routing (`app/Controllers/Admin/Pemuda.php` & `app/Config/Routes.php`):**
  - Registrasi rute GET `admin/pemuda/export`, POST `admin/pemuda/export` (unduh), dan GET `admin/pemuda/export/count`.
  - Penegakan otorisasi dan scope: `admin_wilayah` hanya mengekspor wilayahnya; `admin_cabang` hanya mengekspor cabangnya; `superadmin` dapat mengekspor seluruh atau sebagian data.
- **Pengujian Unit (`tests/unit/PemudaExportTest.php`):**
  - 5 unit tests (133 assertions) berhasil menguji registrasi rute, struktur kategori & preset kolom, pembuatan spreadsheet Excel, filtering bakat/minat model, dan batasan scope role.

### 2026-09-08 — Sistem Antrian Sinkronisasi Data Pemuda API Pusat (Laju Terkendali 40 Data / Menit)

- **Latar Belakang & Masalah:**
  - Server API MTA Pusat membatasi permintaan maksimal **60 request / menit**.
  - Jika sinkronisasi massal dijalankan sekaligus tanpa kontrol laju, request ke-61 dan seterusnya terkena limit kuota (HTTP 429 Too Many Requests), menyebabkan banyak data pemuda gagal disinkronkan dan web server mengalami *gateway timeout*.
- **Solusi & Arsitektur Antrian (Queue):**
  - Diterapkan antrian terkendali dengan laju aman **40 data / menit** (1 data setiap **1.5 detik / 1500 ms**).
  - Laju 40 data/menit berada 33.3% di bawah batas maksimal 60 req/menit server pusat sehingga koneksi API Pusat selalu terjaga stabil tanpa risiko terputus atau terblokir.
- **Database & Migration (`mta_sync_queue`):**
  - Dibuat migration `2026-09-08-060000_CreateMtaSyncQueueTable.php` untuk mencatat antrian pemuda yang akan disinkronkan.
  - Kolom: `id`, `pemuda_id` (FK cascade), `cabang_id` (FK cascade), `status` (`pending`, `processing`, `completed`, `failed`), `result` (`verified`, `pending`, `error`), `message`, `mta_warga_uuid`, `attempts`, `created_by`, `processed_at`.
  - Dibuat model `MtaSyncQueueModel.php` dengan method kalkulasi ringkasan antrian (`getQueueSummary`), estimasi sisa waktu, pemanggilan item pending berikutnya (`getNextPendingItem`), dan pembersihan antrian (`clearPendingQueue`).
- **Service Layer (`app/Services/MtaSyncService.php`):**
  - Ditambahkan method `initSyncQueue(?int $cabangId, bool $onlyPending, ?int $userId, bool $clearExisting)` untuk menyiapkan antrian data pemuda aktif.
  - Ditambahkan method `processNextQueueItem(?int $userId)`: Memproses 1 item antrian dengan proteksi deteksi HTTP 429. Jika limit kuota terdeteksi, status dikembalikan ke `pending` dan antrian melakukan jeda pendinginan aman (cooldown) otomatis selama 10 detik tanpa menghilangkan data.
  - Ditambahkan method `getQueueStatus()` dan `cancelQueue(?int $userId)`.
  - Pembaruan `syncAndVerifyAllPemudaSragen()`: Diberikan *throttling* jeda 1.5 detik per iterasi untuk eksekusi server-side/CLI.
- **Spark CLI Command (`app/Commands/MtaSyncQueue.php`):**
  - Disediakan perintah `php spark mta:sync-queue` dengan opsi `--cabang`, `--only-pending`, dan `--init` untuk menjalankan antrian sinkronisasi via konsol terminal atau cron job di background.
- **Controller & Routing (`app/Controllers/Admin/MtaSync.php` & `app/Config/Routes.php`):**
  - Didaftarkan endpoint AJAX baru di bawah route group `admin/mta-sync`:
    - `POST admin/mta-sync/queue-init`
    - `POST admin/mta-sync/queue-process-item`
    - `GET admin/mta-sync/queue-status`
    - `POST admin/mta-sync/queue-cancel`
  - Seluruh endpoint dilindungi filter `csrf`, `auth`, dan `role:superadmin` serta selalu menyinkronkan token hash CSRF pada response JSON.
- **User Interface & UX Monitor Real-time (`app/Views/admin/mta_sync/index.php`):**
  - **Banner Peringatan Antrian Tersimpan:** Jika admin memiliki antrian belum selesai dari sesi sebelumnya, sistem menampilkan alert informatif dengan tombol "Lanjutkan Antrian" dan "Hapus Antrian".
  - **Modal Antrian Interaktif (Modal-LG):**
    - Panel pengaturan cakupan cabang dan opsi khusus pemuda pending disertai penjelasan laju 40 data/menit.
    - Panel Monitor Kemajuan Real-time:
      - Badge status koneksi API Pusat dan indikator kecepatan 40 data/menit.
      - Alert pendinginan otomatis jika terjadi limit 429 dengan hitung mundur detik (countdown timer).
      - Progress bar animasi bergaris lengkap dengan persentase dan estimasi sisa waktu.
      - 4 Kartu Metrik: Total Antrian, Terverifikasi (hijau), Belum Terdata (kuning), Gagal/Error (merah).
      - Kotak streaming log real-time dengan auto-scroll untuk memantau pemrosesan setiap individu pemuda.
      - Kontrol penuh: Tombol "Jeda Antrian" (Pause), "Lanjutkan Antrian" (Resume), dan "Batalkan Antrian" (Stop).
### 2026-09-09 — Penambahan Fitur Backup dan Hapus Semua Data Pemuda (Role Superadmin)

- **Latar Belakang & Kebutuhan:**
  - Kebutuhan administrasi bagi Super Administrator untuk mencadangkan (backup) seluruh basis data pemuda sebelum peremajaan sistem, ekspor berkala, atau migrasi.
  - Kebutuhan pembersihan/penghapusan menyeluruh data pemuda (reset total) saat pengujian selesai, sebelum peluncuran resmi sistem, atau reset data tahunan.
- **Service Layer (`app/Services/PemudaBackupService.php`):**
  - Dibuat `PemudaBackupService` yang menangani 3 format cadangan:
    1. **SQL Database Dump (`generateSqlBackup`):** Menghasilkan skrip dump SQL berformat `.sql` berisi DDL/DML lengkap tabel `pemuda`, `alamat`, `pendidikan`, `pekerjaan`, `organisasi`, `pemuda_skills`, `pemuda_interests`, dan `mta_sync_queue`. Dilengkapi `SET FOREIGN_KEY_CHECKS = 0` dan batch per 50 baris untuk kompatibilitas import MySQL/phpMyAdmin.
    2. **JSON Structured Export (`generateJsonBackup`):** Menghasilkan berkas `.json` hierarkis terstruktur memuat data induk pemuda beserta child relation lengkap dan raw database tables.
    3. **Excel Spreadsheet Backup (`generateXlsxBackup`):** Memanfaatkan PhpSpreadsheet untuk menghasilkan berkas `.xlsx` komprehensif berisi seluruh atribut pemuda dari seluruh cabang dan wilayah MTA Sragen.
  - Manajemen berkas cadangan internal di server (`writable/backups/`) dengan proteksi keamanan `.htaccess` (`Deny from all`) dan `index.html`.
  - Fungsi `getBackupList()`, `getBackupFilePath()`, dan `deleteBackupFile()` dengan validasi ketat anti-*directory traversal*.
  - Fungsi `deleteAllYouthData(int $superadminUserId, string $password)`:
    - Verifikasi otentikasi password akun superadmin menggunakan `password_verify()`.
    - Pengecekan ketersediaan data pemuda sebelum eksekusi.
    - **Proteksi Auto-Backup Darurat:** Secara otomatis membuat dan menyimpan snapshot cadangan SQL (`auto_backup_sebelum_reset_YYYYMMDD_HHmmss.sql`) di server sebelum penghapusan dilakukan.
    - Menjalankan penghapusan menyeluruh dalam database transaction (`transStart()`, `transComplete()`) secara berurutan: tabel relasi anak (`pemuda_interests`, `pemuda_skills`, `organisasi`, `pekerjaan`, `pendidikan`, `alamat`, `mta_sync_queue`), pemutusan FK pada `responses` (SET NULL), dan tabel induk `pemuda`.
    - Reset `AUTO_INCREMENT` kembali ke 1.
    - Pencatatan log audit keamanan.
- **Controller & Routing (`app/Controllers/Admin/Pemuda.php` & `app/Config/Routes.php`):**
  - Didaftarkan rute khusus di bawah filter `auth` dan `role:superadmin`:
    - `GET admin/pemuda/backup` (`Admin\Pemuda::backup`)
    - `POST admin/pemuda/backup/generate` (`Admin\Pemuda::generateBackup`)
    - `GET admin/pemuda/backup/download/(:segment)` (`Admin\Pemuda::downloadBackup`)
    - `POST admin/pemuda/backup/delete-file/(:segment)` (`Admin\Pemuda::deleteBackupFile`)
    - `POST admin/pemuda/hapus-semua` (`Admin\Pemuda::hapusSemua`)
  - Validasi multi-layer pada controller: pemeriksaan role session, verifikasi teks konfirmasi (`HAPUS SEMUA PEMUDA`), dan validasi server-side.
- **User Interface & UX Admin Panel:**
  - Halaman baru `app/Views/admin/pemuda/backup.php`:
    - Ringkasan metrik data pemuda, data terverifikasi, status pending, dan total relasi.
    - 3 Kartu Format Backup (SQL, JSON, Excel) dengan opsi "Download Langsung" dan "Simpan di Server".
    - Tabel Riwayat Berkas Cadangan di Server dengan badge format, ukuran berkas, waktu pembuatan, tombol unduh, dan hapus berkas.
    - Kartu Zona Bahaya (*Danger Zone*) dengan peringatan berbingkai merah dan tombol buka modal.
    - Modal Konfirmasi Keamanan Ganda: Input teks `HAPUS SEMUA PEMUDA`, input password Superadmin dengan tombol show/hide eye, checkbox persetujuan tanggung jawab, dan tombol eksekusi yang dinonaktifkan otomatis sampai seluruh syarat terpenuhi.
  - Diperbarui `app/Views/admin/pemuda/index.php`: Ditambahkan tombol "Backup & Hapus Data" pada header action bar khusus Superadmin.
  - Diperbarui `app/Views/admin/layouts/main.php`: Ditambahkan item menu sidebar "Backup & Reset Data" (`fas fa-database text-warning`) di bawah Master & Pengaturan khusus Superadmin.
- **Pengujian Unit (`tests/unit/PemudaBackupTest.php`):**
  - 7 unit tests (58 asersi) memverifikasi direktori backup dan proteksi, integritas SQL dump, struktur JSON hierarkis, instansiasi Excel spreadsheet, penyimpanan dan penghapusan berkas cadangan aman, pencegahan directory traversal, validasi password superadmin, auto-backup darurat, dan alur transaksi reset pemuda.

### 2026-09-09 — Penambahan Field Terakhir Upload Foto Profil pada Form Pendataan

- **Latar Belakang & Kebutuhan:**
  - Penambahan input berkas pas foto profil pada form pendataan pemuda di bagian akhir formulir (Step 8 sebelum persetujuan & kirim data).
  - Kebijakan syar'i & privasi organisasi: Wajib diunggah bagi pendaftar Laki-laki ('L'), dan tidak diwajibkan / opsional bagi pendaftar Perempuan ('P').
- **Database & Migration:**
  - Migration `2026-09-09-201500_AddFotoToPemuda.php`: Menambahkan kolom `foto` (VARCHAR 255, NULL, default NULL) pada tabel `pemuda` setelah kolom `mta_foto_url`.
  - Whitelist model: Ditambahkan kolom `foto` pada `$allowedFields` di `app/Models/PemudaModel.php`.
- **Public Form Pendataan (`app/Views/pendataan/form.php` & `public/js/pendataan.js`):**
  - Form ditambahkan atribut `enctype="multipart/form-data"`.
  - Pada Step 8 (Konfirmasi & Ringkasan Data), ditambahkan kartu upload foto profil sebagai field input terakhir sebelum checkbox persetujuan dan tombol submit.
  - Komponen Upload Foto Profil:
    - Indikator dinamis berdasarkan jenis kelamin yang dipilih: Badge merah "Wajib untuk Laki-laki" dengan tanda bintang merah vs badge abu-abu "Opsional untuk Perempuan".
    - Kotak preview interaktif dengan placeholder avatar, tampilan gambar langsung saat memilih berkas, dan tombol hapus/batal.
    - Validasi client-side: Maksimal ukuran 2MB, format file didukung (JPG, JPEG, PNG, WEBP).
    - Status foto ditampilkan pada tabel ringkasan review data pribadi.
    - Dukungan mode update / warga MTA: Foto yang sudah ada di sistem otomatis ditampilkan dengan notifikasi.
- **Backend Controller (`app/Controllers/Pendataan.php`):**
  - Validasi server-side pada `Pendataan::simpan()`:
    - Memastikan pendaftar laki-laki ('L') wajib memiliki foto (baik unggahan baru atau foto yang sudah tersimpan pada mode pembaruan data). Pendaftar perempuan ('P') bebas mengunggah atau mengosongkan foto.
    - Validasi berkas: Format (JPG, JPEG, PNG, WEBP), MIME type gambar, dan ukuran maksimal 2 MB.
  - Penyimpanan file: Disimpan pada direktori aman `public/uploads/pemuda/` dengan penamaan acak unik (`getRandomName()`).
  - Pembersihan file: Menghapus berkas foto lama saat diganti baru pada mode pembaruan, dan auto-cleanup berkas jika transaksi database mengalami rollback.
- **Integrasi Panel Admin & Dokumen:**
  - `app/Views/pendataan/sukses.php`: Menampilkan foto profil pemuda pada ringkasan bukti pendaftaran.
  - `app/Views/admin/pemuda/detail.php`: Menampilkan foto profil pemuda pada hero card.
  - `app/Views/admin/pemuda/index.php`: Menampilkan thumbnail foto profil pada kartu daftar pemuda.
  - `app/Views/admin/pemuda/cetak.php`: Menampilkan foto profil pada dokumen cetak biodata 3x4.
  - `app/Views/admin/pemuda/form.php` & `Admin\Pemuda.php`: Admin dapat mengunggah dan memperbarui foto profil dari panel admin.
- **Pengujian Unit (`tests/unit/PendataanFormTest.php`):**
  - Pengujian whitelist kolom `foto` pada `PemudaModel`.
  - Pengujian keberadaan elemen input foto, multipart form, dan logika verifikasi kewajiban foto profil berdasarkan jenis kelamin (wajib bagi laki-laki, tidak wajib bagi perempuan).

### 2026-09-09 — Penambahan Role Baru: Admin Pemuda, Admin Pemudi, dan Admin Wilayah Pemuda

- **Latar Belakang & Kebutuhan:**
  - Penambahan hak akses berorientasi gender sesuai struktur organisasi pemuda:
    1. `admin_pemuda`: Administrator tingkat Cabang yang **hanya dapat mengakses dan mengelola data pemuda Laki-laki (`gender = 'L'`)**.
    2. `admin_pemudi`: Administrator tingkat Cabang yang **hanya dapat mengakses dan mengelola data pemuda Perempuan (`gender = 'P'`)**.
    3. `admin_wilayah_pemuda`: Administrator tingkat Wilayah yang **hanya dapat mengakses dan mengelola data pemuda Laki-laki (`gender = 'L'`)**.
- **Database & Migration:**
  - Migration `2026-09-09-205000_AddNewRolesAdminPemudaPemudi.php`: Menambahkan 3 role baru ke tabel `user_roles` (`admin_pemuda` id 4, `admin_pemudi` id 5, `admin_wilayah_pemuda` id 6).
  - Diperbarui `app/Database/Seeds/UserRoleSeeder.php` dengan entri 3 role baru tersebut.
- **Enforcement Scope & Keamanan Server-Side (`app/Models/PemudaModel.php`):**
  - `applyScope()`: Ditambahkan klausul filter query builder server-side:
    - `admin_wilayah_pemuda`: `cabang.wilayah_id = $scope['wilayah_id'] AND pemuda.gender = 'L'`.
    - `admin_pemuda`: `pemuda.cabang_id = $scope['cabang_id'] AND pemuda.gender = 'L'`.
    - `admin_pemudi`: `pemuda.cabang_id = $scope['cabang_id'] AND pemuda.gender = 'P'`.
  - `getFilteredQuery()`: Enforce gender override jika user ber-role gender khusus, mencegah manipulasi query parameter `?gender=...`.
  - `getDashboardStats()`: Scope wilayah, cabang, users, statistik pemuda per wilayah, dan statistik cabang teratas disinkronkan dengan gender scope masing-masing role.
- **Controller Enforcement (`app/Controllers/Admin/`):**
  - `Admin\Users.php`: Validasi simpan & update user baru untuk role 4, 5, 6 (role cabang mewajibkan pemilihan cabang, role wilayah mewajibkan pemilihan wilayah).
  - `Admin\Pemuda.php`:
    - `index()`: Filter list dan summary counter dibatasi scope role dan gender terkunci.
    - `tambah()` & `simpan()`: Server-side validation menolak jika role gender khusus mencoba mendaftarkan jenis kelamin yang berlawanan.
    - `edit()` & `update()`: Verifikasi akses data `getPemudaDetail` dan server-side validation gender pada update.
    - `detail()`, `verifikasi()`, `archive()`, `cetak()`: Memakai `getPemudaDetail` yang menerapkan `applyScope`, otomatis memblokir ID yang berbeda gender / cabang / wilayah.
    - `export()`, `exportDownload()`, `exportCount()`: Mengunci filter gender dan cabang/wilayah sesuai role scope.
  - `Admin\Ajax.php`: Pembatasan lookup cabang berdasarkan role admin cabang / wilayah.
  - `Auth.php`: Pengambilan otomatis `wilayah_id` dari cabang jika user adalah `admin_pemuda` atau `admin_pemudi`.
- **UI & Layout Panel Admin:**
  - `app/Views/admin/users/index.php`:
    - Badge role dan scope yang informatif (`Admin Pemuda (L)`, `Admin Pemudi (P)`, `Admin Wilayah Pemuda (L)`).
    - Dynamic dropdown JavaScript untuk menampilkan input Wilayah/Cabang pada form Tambah & Edit User.
  - `app/Views/admin/layouts/main.php`:
    - Badge scope di header navbar, badge role di dropdown profil, dan badge user panel di sidebar.
  - `app/Views/admin/dashboard/index.php`:
    - Header banner, deskripsi ringkasan, dan kartu cabang teratas disesuaikan untuk role baru.
  - `app/Views/admin/pemuda/index.php`:
    - Filter dropdown gender dan cabang otomatis terkunci (disabled dengan hidden input) sesuai role yang login.
  - `app/Views/admin/pemuda/form.php`:
    - Dropdown jenis kelamin terkunci sesuai izin role (Laki-laki untuk admin pemuda/wilayah pemuda, Perempuan untuk admin pemudi).
    - Proteksi lookup modal Warga MTA: jika data warga yang dipilih tidak cocok dengan gender role, sistem menampilkan peringatan dan mencegah form diisi.
  - `app/Views/admin/pemuda/export.php`:
    - Filter gender dan cabang pada menu export otomatis terkunci sesuai role scope.
- **Pengujian Unit (`tests/unit/PemudaManagementTest.php`):**
  - Ditambahkan unit test verifikasi keberadaan role baru di database, dan pengujian filter query builder SQL untuk `admin_pemuda`, `admin_pemudi`, dan `admin_wilayah_pemuda` termasuk proteksi tamper filter parameter.

### 2026-09-09 — Penyesuaian Terminologi: "Organisasi" Menjadi "Element Dakwah"

- **Latar Belakang & Kebutuhan:**
  - Penyesuaian terminologi keikutsertaan pemuda dalam unit-unit tugas (seperti Satgas, Bankom, Tim Parkir, Kepengurusan Pemuda, Tim Ikhrom, dll.) dari istilah "Organisasi" menjadi **"Element Dakwah"**.
  - Sesuai prinsip *backward compatibility* dan arsitektur database (`AGENTS.md` Bab 30), nama tabel (`organisasi`), kolom (`organization_name`), model (`OrganisasiModel`), dan nama input form (`organizations`, `other_organization`) tetap dipertahankan agar tidak merusak relasi dan data tersimpan.
- **Formulir Pendataan Publik (`app/Views/pendataan/form.php` & `public/js/pendataan.js`):**
  - Judul Bagian 5: "5. Keikutsertaan Element Dakwah" beserta panduan "Pilih element dakwah yang Anda ikuti...".
  - Field Element Dakwah Tambahan: "Element Dakwah Lainnya (Jika ada)".
  - Ringkasan / Review Step 8: "Keikutsertaan Element Dakwah / Unit Tugas".
  - Stepper wizard & progress label: "Element Dakwah & Penugasan".
- **Panel Admin Data Pemuda (`app/Views/admin/pemuda/`):**
  - Formulir Tambah/Edit Pemuda (`form.php`): Bagian 5 diubah menjadi "5. Keikutsertaan Element Dakwah / Unit Tugas" dan label "Element Dakwah / Komunitas Lainnya".
  - Tampilan Detail Pemuda (`detail.php`): Header kartu informasi diubah menjadi "Keikutsertaan Element Dakwah" dan empty state menjadi "Tidak ada keikutsertaan element dakwah yang tercatat".
  - Dokumen Cetak Biodata (`cetak.php`): Bagian IV diubah menjadi "IV. ELEMENT DAKWAH & KEAHLIAN" dan baris "Element Dakwah yang Diikuti".
  - Modul Export Data (`export.php` & `PemudaExportService.php`):
    - Filter form: "Riwayat Element Dakwah Yang Diikuti" beserta placeholder yang relevan.
    - Kategori kolom export: "Element Dakwah, Bakat & Minat".
    - Label kolom spreadsheet: "Element Dakwah Yang Diikuti".
  - Modul Import Excel (`import.php` & `PemudaImportService.php`):
    - Badge kolom opsional pada panduan import: "Element Dakwah".
    - Header template unduhan Excel: `Element Dakwah (Opsional)`.
    - Algoritma pemetaan header fleksibel: mengenali "Element Dakwah", "Elemen Dakwah", dan tetap mempertahankan kompatibilitas dengan header lama "Organisasi" / "Organization".
    - Mengamankan deteksi kolom nomor telepon/WhatsApp agar tidak false-positive mendeteksi substring `wa` dalam kata `dakwah`.
  - Modul Backup & Restore (`backup.php`):
    - Keterangan ringkasan dan rincian tabel cadangan disesuaikan menjadi "keaktifan element dakwah".
- **Pengaturan Beranda (`app/Models/HomepageSettingModel.php`):**
  - Deskripsi alur pendaftaran tahap 3 disesuaikan menjadi "... serta pilihan element dakwah (Satgas, Bankom, dll)".
- **Pengujian Unit (`tests/unit/`):**
  - Diperbarui `PemudaExportTest.php` untuk memvalidasi nama kategori dan label kolom "Element Dakwah".
  - Diperbarui `PemudaImportTest.php` untuk memverifikasi pemetaan kolom "Element Dakwah (Opsional)" dan "Elemen Dakwah" secara akurat.

### 2026-09-13 — Penyempurnaan Definisi & Lingkup Role (Superadmin, Admin Pemudi, Admin Pemuda, Admin Wilayah Pemuda, Admin Cabang)

- **Perubahan Spesifikasi Lingkup Role:**
  1. `superadmin`: Mengelola seluruh sistem dan data (seluruh wilayah, seluruh cabang MTA di Sragen, semua gender pemuda & pemudi).
  2. `admin_pemudi`: Administrator tingkat Kabupaten (seluruh Sragen) yang mengelola/menghandle seluruh data pemudi berjenis kelamin Perempuan (`gender = 'P'`). Memiliki akses lintas seluruh wilayah dan cabang di Sragen tanpa terikat pada cabang/wilayah tertentu (`wilayah_id = NULL`, `cabang_id = NULL`).
  3. `admin_pemuda`: Administrator tingkat Kabupaten (seluruh Sragen) yang mengelola/menghandle seluruh data pemuda berjenis kelamin Laki-laki (`gender = 'L'`). Memiliki akses lintas seluruh wilayah dan cabang di Sragen tanpa terikat pada cabang/wilayah tertentu (`wilayah_id = NULL`, `cabang_id = NULL`).
  4. `admin_wilayah_pemuda`: Administrator tingkat Wilayah yang mengelola data pemuda berjenis kelamin Laki-laki (`gender = 'L'`) pada cabang-cabang dalam wilayah yang dinaunginya (`wilayah_id = [id]`, `cabang_id = NULL`).
  5. `admin_cabang`: Manajemen data pada cabang tersebut (mengelola seluruh data pemuda & pemudi, Laki-laki & Perempuan pada cabang yang dinaunginya) (`cabang_id = [id]`, `wilayah_id = [cabang.wilayah_id]`).
- **Database & Seeder:**
  - Migration `2026-09-12-223000_UpdateRoleDefinitionsAndScopes.php` dan `UserRoleSeeder.php` menyinkronkan deskripsi role resmi.
  - `UserSeeder.php` diperbarui dengan akun contoh untuk semua 6 role.
- **Backend & Controller Logic:**
  - `app/Controllers/Admin/Users.php`: Validasi create/update user tidak lagi mewajibkan cabang untuk `admin_pemuda` dan `admin_pemudi` (keduanya kini global se-Sragen dengan `wilayah_id = null` dan `cabang_id = null`).
  - `app/Controllers/Auth.php`: Normalisasi session data saat login untuk role se-Sragen (`superadmin`, `admin_pemuda`, `admin_pemudi`).
  - `app/Controllers/Admin/Ajax.php`: `getCabangByWilayah` mengizinkan `admin_pemuda` dan `admin_pemudi` mengambil cabang dari wilayah mana pun di Sragen (hanya `admin_cabang` yang dibatasi ke cabangnya sendiri).
  - `app/Controllers/Admin/Pemuda.php` & `app/Models/PemudaModel.php`:
    - Enforce server-side gender lock: `admin_pemuda` (`gender = 'L'`), `admin_pemudi` (`gender = 'P'`).
    - Filter wilayah & cabang: `admin_pemuda` dan `admin_pemudi` dapat memfilter wilayah dan cabang mana pun di Sragen.
- **UI & Views:**
  - `app/Views/admin/users/index.php`: Tabel menampilkan label scope "Seluruh Sragen (L)" / "Seluruh Sragen (P)", dan modal form otomatis menyembunyikan input Wilayah & Cabang untuk `admin_pemuda` & `admin_pemudi`.
  - `app/Views/admin/layouts/main.php`: Badge navbar desktop dan mobile menampilkan badge "Scope: Seluruh Sragen" dengan penanda gender L/P.
  - `app/Views/admin/dashboard/index.php`: Banner selamat datang dan kartu 10 Cabang Terbanyak disesuaikan untuk `admin_pemuda` (Pemuda L) dan `admin_pemudi` (Pemudi P).
  - `app/Views/admin/pemuda/index.php`, `form.php`, `export.php`: Dropdown wilayah dan cabang terbuka untuk `admin_pemuda` dan `admin_pemudi`, sedangkan filter gender terkunci sesuai gender yang diizinkan.
- **Testing:**
  - Unit test `PemudaManagementTest.php` dan `PemudaExportTest.php` memvalidasi fungsionalitas dan otorisasi seluruh role.

### 2026-09-13 — Penambahan Dashboard Persebaran Data Pemuda & Visualisasi Multidimensi

- **Dashboard Persebaran Data Pemuda (`/admin/persebaran`):**
  - Dibuat fitur analisis persebaran data pemuda komprehensif yang memetakan seluruh dimensi data:
    1. **Element Dakwah / Unit Tugas:** Distribusi penugasan pemuda dalam Satgas, Bankom, Tim Parkir, Pengurus Pemuda, Tim Ikhrom, serta rasio keikutsertaan unit tugas.
    2. **Pendidikan & Sekolah:** Jenjang pendidikan terakhir, status pendidikan (Aktif menempuh, Lulus, Putus sekolah), Top 10 Sekolah/Kampus, dan Top 10 Jurusan/Prodi.
    3. **Bakat & Keahlian (Skills):** Top 10 keahlian terbanyak, distribusi tingkat kemahiran (Pemula, Menengah, Mahir), dan persentase pemuda yang memiliki keahlian tercatat.
    4. **Minat & Hobi (Interests):** Top 10 minat terbanyak yang digemari pemuda untuk pemetaan program kerja dan pembinaan.
    5. **Ketenagakerjaan & Wirausaha:** Distribusi status pekerjaan, jumlah pelaku usaha/wirausaha mandiri, dan Top bidang usaha/bisnis yang digeluti.
    6. **Demografi Usia & Wilayah:** Rentang usia (<17, 17-21, 22-25, 26-30, >30 tahun), rata-rata usia pemuda, sebaran kecamatan di Kabupaten Sragen, serta sebaran wilayah dan Top cabang.
- **Backend & Model:**
  - Ditambahkan method `getPersebaranStats(array $scope, array $filters)` dan `applyScopeAndCustomFilters()` pada `app/Models/PemudaModel.php` dengan query builder native CodeIgniter 4 yang aman dan efisien.
  - Memperbaiki pemanggilan Query Builder agar murni menggunakan agregasi native CI4 (`COUNT(DISTINCT ...)`) menggantikan fungsi non-eksisten `whereExists()` pada driver MySQLi CodeIgniter 4.
  - Dibuat controller `app/Controllers/Admin/Persebaran.php` dengan penegakan scope RBAC ketat (Superadmin, Admin Pemuda, Admin Pemudi, Admin Wilayah, Admin Cabang).
- **Rute & Navigasi:**
  - Didaftarkan route `admin/persebaran` dan `admin/dashboard/persebaran` pada `app/Config/Routes.php`.
  - Ditambahkan menu navigasi "Persebaran Data" pada sidebar `app/Views/admin/layouts/main.php` dan tombol pintasan di Dashboard Admin `app/Views/admin/dashboard/index.php`.
- **Pengujian Unit (`tests/unit/PersebaranDashboardTest.php`):**
  - Dibuat 6 test case unit pengujian struktur return data `getPersebaranStats()`, isolasi scope `admin_pemuda` (hanya L), `admin_pemudi` (hanya P), `admin_cabang` (terkunci ke cabang bersangkutan), fungsionalitas parameter filter multi-kriteria, dan verifikasi tampilan foto profil diperbesar pada detail pemuda.

### 2026-09-13 — Peningkatan Tampilan Foto Profil pada Detail Data Pemuda

- **Pembaruan Tampilan Foto Profil (`app/Views/admin/pemuda/detail.php`):**
  - Ukuran foto profil diperbesar secara signifikan dari 70px menjadi **130px × 130px** (`width: 130px; height: 130px; object-fit: cover;`) dengan border tematik gender (`border-primary` untuk pemuda laki-laki, `border-danger` untuk pemudi perempuan) dan efek bayangan lembut (`shadow`).
  - Ditambahkan efek hover interaktif (`scale(1.04)` dan elevasi bayangan) dengan badge zoom icon `fas fa-search-plus`.
  - Ditambahkan tombol pintas *"Perbesar Foto"* di bawah foto profil.
  - Ditambahkan **Modal Lightbox Preview** (`#modalFotoPreview`): Saat foto profil diklik, sistem membuka modal popup beresolusi penuh dalam aspek rasio aslinya yang dilengkapi tombol *"Unduh Foto"* untuk keperluan arsip/administrasi.
  - Avatar placeholder tanpa foto diperbesar menjadi 130px dengan inisial bergradien dan icon penanda status foto.

### 2026-09-13 — Penambahan Persebaran Data Golongan Darah & Kesiapsiagaan Donor

- **Integrasi Golongan Darah pada Dashboard Persebaran (`/admin/persebaran`):**
  - Ditambahkan **Section 7: Persebaran Golongan Darah & Kesiapsiagaan Donor**:
    - **Chart Donut (`chartGolDarah`):** Komposisi pemuda berdasarkan golongan darah (A, B, AB, O, dan Belum Tercatat) dengan persentase kelengkapan data.
    - **Kartu Ringkasan Tiap Golongan Darah:** Rincian jumlah pemuda per golongan darah, kode badge tematik, peruntukan donor (`Donor untuk`), dan kompatibilitas penerimaan (`Menerima dari`), termasuk label Resipien Universal (AB) dan Donor Universal (O).
    - **Kartu Tindakan Data Belum Tercatat:** Ringkasan pemuda yang belum mengetahui/mencatatkan golongan darah beserta tautan cepat untuk memfilter data.
  - Ditambahkan filter interaktif **Golongan Darah** pada form filter atas (`A`, `B`, `AB`, `O`, `Belum Tercatat`).
- **Backend & Model (`app/Models/PemudaModel.php` & `app/Controllers/Admin/Persebaran.php`):**
  - Ditambahkan kalkulasi `bloodData`, `totalWithBlood`, `totalUnknownBlood`, dan `percentWithBlood` pada method `getPersebaranStats()`.
  - Ditambahkan filter `blood_type` pada `applyScopeAndCustomFilters()`.
- **Pengujian Unit (`tests/unit/PersebaranDashboardTest.php`):**
  - Ditambahkan unit test `testGolonganDarahStatsAndFilter()` yang memvalidasi integritas data golongan darah terhadap total pemuda, fungsionalitas filtering golongan darah O, dan kehadiran elemen UI serta Chart.js golongan darah pada view.

### 2026-09-15 — Penyesuaian Tabel Pendaftaran Pemuda Terbaru Menjadi Last Edit pada Dashboard Admin

- **Dashboard Utama Admin (`app/Views/admin/dashboard/index.php`):**
  - Mengubah judul kartu dari *"Pendaftaran Pemuda Terbaru"* menjadi *"Data Pemuda Terakhir Diedit (Last Edit)"*.
  - Mengubah kolom tabel dari *"Tgl Daftar"* menjadi *"Terakhir Diedit"*.
  - Memperbarui tampilan waktu dengan menampilkan waktu terakhir pembaruan data (`last_edited_at` / `updated_at` / `created_at`) beserta label indikator status data (`Diedit` atau `Baru`).
  - Menggunakan fallback list `recentUpdates ?? recentRegistrations` untuk kompatibilitas data.
- **Backend & Model (`app/Models/PemudaModel.php`):**
  - Memastikan query data dashboard mengurutkan 10 data pemuda aktif berdasarkan waktu terakhir pembaruan: `ORDER BY COALESCE(pemuda.updated_at, pemuda.created_at) DESC, pemuda.id DESC`.
  - Menyediakan field alias `last_edited_at` dan key `recentUpdates` serta `recentRegistrations` pada array statistik dashboard.
- **Pengujian Unit (`tests/unit/SuperAdminDashboardTest.php`):**
  - Menambahkan assertion kunci `recentUpdates` dan `recentRegistrations` pada pengujian statistik dashboard.

### 2026-09-15 — Koreksi Data Kelurahan/Desa pada Kecamatan Masaran (Pilangsari -> Pilang)

- **Database Migration (`app/Database/Migrations/2026-09-15-070000_UpdateVillagePilangInMasaran.php`):**
  - Mengoreksi record pada tabel `villages` untuk Kecamatan Masaran (`district_id = 7`), mengubah nama dari `Pilangsari` menjadi `Pilang`.
  - Mempertahankan `Pilangsari` pada Kecamatan Ngrampal (`district_id = 12`).
- **Seeder (`app/Database/Seeds/RegionalSeeder.php`):**
  - Memperbarui daftar desa pada Kecamatan Masaran (`7 => [...]`) mengganti `"Pilangsari"` menjadi `"Pilang"`.
- **Frontend JavaScript (`public/js/pendataan.js`):**
  - Memperbarui dictionary desa dropdown publik pada key `"7"` (Masaran), mengubah `{ id: 75, name: "Pilangsari" }` menjadi `{ id: 75, name: "Pilang" }`.
- **Pengujian Unit (`tests/unit/PendataanFormTest.php`):**
  - Menambahkan unit test `testKecamatanMasaranDesaPilang()` untuk memastikan desa `Pilang` terdaftar di database, seeder, dan form JavaScript, serta memastikan `Pilangsari` tidak ada di Masaran namun tetap ada di Ngrampal.

### 2026-09-17 — Penguatan Keamanan Data & Isolasi Ketat Warga MTA Perwakilan Sragen

- **Audit & Keamanan Data Sensitif:**
  - Menutup dan menghapus rute serta endpoint publik tanpa proteksi yang rentan scraping/IDOR: `pemuda-detail/{id}`, `warga-detail/{uuid}`, `search-warga`, `check-data`, dan `check-duplicate`.
  - Menerapkan pembatasan kolom (*whitelist*) pada endpoint AJAX (`getCabangByWilayah` dan `getVillagesByDistrict`) guna mencegah kebocoran data sensitif operasional dan kontak cabang.
  - Membatasi visibilitas kontak nomor telepon pimpinan cabang hanya untuk peran administratif yang berhak.
  - Menambahkan file `public/uploads/.htaccess` untuk menonaktifkan eksekusi skrip PHP/CGI dan directory indexing di folder upload foto.
  - Memperkuat mekanisme backup di `PemudaBackupService` dengan sanitasi ketat nama file, validasi ekstensi (`sql`, `json`, `xlsx`), serta verifikasi direktori berbasis `realpath()`.
  - Memperbarui model `User`: penambahan `remember_token` pada `$hidden` dan casting `password => 'hashed'`. Menambahkan proteksi penonaktifan diri sendiri dan pencegahan penghapusan superadmin aktif terakhir di `UsersController`.
  - Mengubah metode rute `admin/logout` menjadi HTTP `POST` dengan proteksi token CSRF.
  - Menyesuaikan validasi Rule 16: `status_verifikasi` hanya memiliki status `verified` dan `pending` yang diperbarui secara otomatis lewat sinkronisasi API MTA Pusat (`MtaSyncService::syncSinglePemuda`), tidak dapat dimanipulasi manual.

- **Isolasi Data Warga MTA Khusus Perwakilan Sragen:**
  - Mengoreksi UUID Perwakilan Sragen pada `config/mta.php`, `.env`, `.env.example`, dan `MtaApiService` menjadi UUID resmi: `3246792b-f0a7-48ca-95fa-379e3bee777d` (Kode 86, Perwakilan Sragen).
  - Mengunci parameter `perwakilan` secara mutlak di `MtaApiService::getWargaList()` dan `MtaApiService::searchWarga()` pada UUID Perwakilan Sragen.
  - Menerapkan filter keamanan berlapis (*security safeguard*) pada `WargaMtaController` dan `MtaSyncController` untuk memverifikasi bahwa seluruh data warga yang diambil, ditampilkan, dilihat detailnya, maupun diimpor berasal dari Perwakilan Sragen.
  - Menerapkan pengecekan scope RBAC ketat pada proses import warga ke data pemuda lokal, memastikan Admin Cabang dan Admin Wilayah tidak dapat memanipulasi cabang target di luar wewenang mereka.
  - Menstandarkan daftar pilihan cabang Sragen pada view `admin/warga_mta/index.blade.php` dan memperbaiki kartu metrik statistik integrasi database lokal PMD.
  - **Import Otomatis Sesuai Cabang MTA Pusat:** Mengubah alur import warga ke data pemuda (`WargaMtaController`, `MtaSyncController`, `MtaSyncService`, serta modal pada index dan detail) sehingga sistem secara otomatis memetakan dan menentukan cabang lokal berdasarkan `cabang_uuid` atau nama cabang resmi dari MTA Pusat tanpa mengharuskan admin memilih cabang secara manual.
  - **Penghapusan Total Data NIK:** Menghapus seluruh atribut dan input NIK dari basis data (`2026_09_17_060000_drop_nik_from_pemuda_table.php`), formulir pendaftaran publik (`pendataan/form.blade.php`), formulir admin (`admin/pemuda/form.blade.php`), tampilan detail pemuda (`admin/pemuda/detail.blade.php`), cetak lembar profil (`admin/pemuda/cetak.blade.php`), tabel index pemuda (`admin/pemuda/index.blade.php`), serta tampilan data warga MTA (`admin/warga_mta/index.blade.php` dan `detail.blade.php`) demi privasi data pemuda.
  - Menambahkan suite pengujian otomatis `tests/Feature/WargaMtaSecurityTest.php` (5 test cases) untuk memvalidasi isolasi data, auto-resolusi cabang, dan keamanan endpoint MTA Sragen (seluruh 18 tests fitur lulus 100%).

### 2026-09-17 — Formulir Pendataan Terintegrasi: Dropdown Pencarian Cabang Langsung, Autocomplete Gabungan (Pemuda + Warga MTA Pusat), Auto-Verifikasi, & 6 Elemen Dakwah

- **Dropdown Cabang Terintegrasi (Search Inside Dropdown):**
  - Mengubah dropdown Cabang pada formulir pendataan publik (`/pendataan`) menjadi komponen interaktif dengan kotak pencarian instan langsung di dalam dropdown menu (*trigger button + dropdown panel with internal search box*).
  - Pengguna dapat mengetik nama cabang langsung di dalam menu dropdown dan opsi terfilter secara real-time dari 70 cabang se-Sragen tanpa input terpisah di luar.
- **Autocomplete Cerdas Gabungan (Data Pemuda & Warga MTA Pusat):**
  - Autocomplete nama pemuda di Step 1 terkunci secara ketat hanya pada cabang yang dipilih (`cabang_id`).
  - Menggabungkan data dari 2 sumber:
    1. **Data Pemuda Lokal** (label hijau zamrud: `Data Pemuda`),
    2. **Warga MTA Pusat** via API MTA Sragen (label biru langit: `Warga MTA Pusat`).
  - Jika nama yang dipilih berasal dari **Data Pemuda**: formulir beralih ke mode update (`is_update`), data profil lengkap dimuat via endpoint `GET /pendataan/get-pemuda/{id}?cabang_id=...`, dan `existing_pemuda_id` dipasang.
  - Jika nama yang dipilih berasal dari **Warga MTA Pusat**: data warga diambil via endpoint terlindungi `GET /pendataan/get-warga/{uuid}?cabang_id=...` (dilengkapi validasi otorisasi cabang), formulir otomatis mengisi nama, kelamin, tanggal/tempat lahir, HP, status pernikahan, golongan darah, alamat, dan foto. Saat disimpan, data otomatis terverifikasi (`status_verifikasi = 'verified'`) dan tercatat waktu sinkronisasinya (`mta_synced_at`).
  - Jika nama diketik manual dan tidak ada di daftar: formulir berjalan dalam mode pendaftaran baru (*new registration*).
- **Pembaruan 6 Elemen Dakwah Resmi:**
  - Menstandarkan opsi checkbox elemen dakwah baik pada form publik (`resources/views/pendataan/form.blade.php`) maupun form admin (`resources/views/admin/pemuda/form.blade.php`) secara presisi ke 6 elemen:
    1. `SATGAS`
    2. `BANKOM`
    3. `SAR MTA`
    4. `TIM PARKIR`
    5. `ELFATA`
    6. `TIM IKHROM`
- **Penyempurnaan Tampilan & Antarmuka Formulir (UI/UX Redesign):**
  - **Stepper Progress Bar Interaktif:** Penambahan visual track bar penghubung antar langkah (*connecting progress track line*), indikator status (lingkaran merah untuk langkah aktif, lingkaran hijau centang untuk langkah selesai, dan nomor bersih untuk langkah berikutnya).
  - **Komponen Dropdown Cabang Lebih Bersih:** Penambahan ikon gedung pada tombol pemicu dropdown, transisi halus, kotak pencarian instan dengan ikon kaca pembesar, dan indikator cabang tidak ditemukan yang lebih ramah.
  - **Kartu Mode Interaktif Modern:** Tampilan banner status mode pendaftaran baru, pembaruan data pemuda, maupun integrasi warga MTA Pusat didesain ulang dengan kartu bertema warna kontras lembut (*soft tinted background*), ikon badge, dan tombol aksi "Bukan Anda? / Daftar Baru".
  - **Elemen Dakwah Kartu Interaktif:** Checkbox 6 elemen dakwah (`SATGAS`, `BANKOM`, `SAR MTA`, `TIM PARKIR`, `ELFATA`, `TIM IKHROM`) didesain dalam bentuk kartu interaktif (*interactive selectable cards*) dengan ikon khas masing-masing elemen, deskripsi singkat peran, dan sorotan warna merah saat dipilih (`has-[:checked]`).
  - **Tombol Navigasi Bawah Lebih Responsif:** Penataan ulang tombol kembali dan lanjut dengan padding yang nyaman untuk perangkat mobile maupun desktop, teks tombol yang dinamis sesuai nama langkah tujuan, serta animasi pemrosesan pada tombol kirim.
- **Opsi Input Elemen Dakwah Baru & Tampilan Dinamis:**
  - **Input Elemen Baru:** Menambahkan kotak input *"Elemen Tidak Tersedia? Tambahkan Elemen Baru"* pada formulir publik (`/pendataan`) dan formulir admin (`/admin/pemuda`), memungkinkan pendaftar atau admin menambahkan satuan tugas/elemen kustom (misal: `TIM LOGISTIK`, `KOKAM`, `PANDU`, dll.).
  - **Tampilan Langsung di Formulir (Instant Card Generation):** Saat pengguna mengetik nama elemen dan menekan tombol *"Tambahkan"* (atau menekan tombol Enter), sistem secara dinamis menambahkan kartu elemen baru ke dalam daftar, otomatis mencentangnya, dan menampilkannya pada ringkasan Langkah 7.
  - **Penampilan Elemen Baru yang Tersimpan (Database Persistence):** Setiap elemen baru yang tersimpan pada tabel `organisasi` secara otomatis dimuat dan ditampilkan sebagai kartu pilihan elemen tambahan pada formulir pendataan untuk seluruh pendaftar berikutnya (`Organisasi::distinct()`).
- **Perbaikan Looping Input Nama Saat Tombol Kirim Pendaftaran Ditekan:**
  - **Identifikasi Penyebab Utama:**
    1. Validasi foto di server (`PendataanController::simpan()`) mewajibkan foto bagi pemuda laki-laki (`$gender === 'L' && !$hasUploadedFoto && !$hasExistingFoto`). Dari 733 data pemuda di database, terdapat 727 pemuda yang belum memiliki foto (`foto = NULL`). Akibatnya, saat pemuda laki-laki lama melakukan pembaruan data tanpa mengunggah foto baru, validasi menolak request dan me-redirect balik dengan kode 302 ke `/pendataan`.
    2. Formulir pada `pendataan/form.blade.php` sebelumnya tidak merender notifikasi server-side `session('error')` dan `$errors->any()`, serta state JavaScript otomatis kembali ke Langkah 1 (`currentStep = 1`), sehingga tampak seolah-olah tombol kirim "looping" kembali ke input nama tanpa ada pesan kesalahan.
  - **Penyelesaian & Peningkatan:**
    - Mengubah aturan validasi foto profil di `PendataanController::simpan()` agar foto **hanya wajib untuk pendaftaran pemuda baru laki-laki** (`!$isUpdate && empty($mta_warga_uuid) && $gender === 'L'`). Untuk pembaruan data pemuda lama (`$isUpdate = true`) maupun sinkronisasi warga MTA Pusat, unggah foto bersifat opsional (jika diunggah diperbarui, jika tidak maka mempertahankan foto yang ada/null).
    - Menambahkan banner notifikasi error server-side `@if(session('error') || $errors->any())` yang jelas dan menonjol di bagian atas formulir.
    - Menambahkan deteksi otomatis langkah error (`$initialStep`) agar saat terjadi kesalahan validasi input, tampilan formulir langsung terbuka pada langkah yang bersangkutan (bukan selalu terpental ke Langkah 1).
    - Menjaga persistensi mode pembaruan (`mode_update_existing`) dan sinkronisasi warga MTA pada `DOMContentLoaded` jika formulir di-reload dengan `old()`.
    - Menambahkan pengujian otomatis komprehensif pada `tests/Feature/PendataanFlowTest.php` untuk memverifikasi pembaruan pemuda laki-laki tanpa foto lama berhasil dialihkan ke halaman sukses tanpa loop.

### 2026-09-23 — Portal Pemantauan Pendataan Pemuda untuk Guru Daerah & Indikator Kelengkapan Data

- **Fitur Baru: Portal Pemantauan Guru Daerah (`/pantau-pemuda`):**
  - Dibuat khusus untuk Guru Daerah dan Pembina Cabang guna memantau progres pendataan pemuda di tingkat cabang tanpa memerlukan akun user/password admin.
  - **Alur Akses (Gerbang Verifikasi):**
    - Sebelum masuk, pengguna memasukkan **Kode Akses Guru Daerah** (default: `GURUPMD` atau `PMDSRAGEN`, dapat diubah oleh Superadmin pada menu Kelola Konten Beranda) serta memilih Cabang yang ingin dipantau (dropdown terstruktur per Wilayah).
    - Dilengkapi rate limiting untuk mencegah brute force percobaan kode akses.
    - Sesi otentikasi disimpan dengan aman (`session('guru_daerah_authenticated')`), memungkinkan Guru Daerah beralih antar cabang via switcher instan tanpa memasukkan ulang kode akses.
  - **Tampilan Dashboard Pemantauan Cabang (`resources/views/guru_daerah/monitoring.blade.php`):**
    - **Header Informasi Cabang:** Menampilkan nama cabang, kode cabang, wilayah binaan, pimpinan, kontak WA, tombol switcher cepat cabang, dan tombol keluar.
    - **6 Kartu Ringkasan Metrik:**
      1. Total Pemuda Terdata
      2. Jumlah & Persentase Data Sudah Komplit (≥ 80%)
      3. Jumlah & Persentase Belum Komplit (< 80%)
      4. Rata-Rata Progres Kelengkapan Cabang (Visual progress bar)
      5. Komposisi Gender (Laki-laki vs Perempuan)
      6. Status Sinkronisasi Database MTA Pusat (Terverifikasi vs Pending)
    - **Rangkuman Kebutuhan Follow Up:** Peringatan otomatis menampilkan aspek yang paling banyak belum diisi oleh pemuda cabang tersebut (misal: jumlah pemuda belum unggah foto, belum isi pendidikan, belum isi pekerjaan, dsb).
    - **Toolbar Interaktif:**
      - Pencarian instan (nama, nomor registrasi, nomor telepon, dusun/alamat).
      - Filter status kelengkapan data (Semua, Komplit, Belum Komplit).
      - Filter jenis kelamin (Semua, Laki-laki, Perempuan).
      - Tombol salin tautan formulir pendaftaran khusus cabang tersebut (`/pendataan?cabang_id=...`).
      - Tombol cetak lembar rekapitulasi ramah printer (`@media print`).
    - **Daftar Tabel Pemuda & Progres Data:**
      - Menampilkan foto profil/avatar, nama, nomor registrasi, status verifikasi MTA.
      - Gender dan usia.
      - Kontak WhatsApp dan alamat dusun/desa/kecamatan.
      - **Indikator Progres Kelengkapan Data:**
        - Persentase kelengkapan data (%) dan status badge (`Komplit` vs `Belum Komplit`).
        - Progress bar dinamis (hijau, kuning, merah).
        - 7 mini checklist badges (Biodata, Alamat, Pendidikan, Pekerjaan, Elemen Dakwah, Keahlian/Minat, Pas Foto).
        - Rincian item data yang masih kurang.
      - **Aksi Cepat Guru Daerah:**
        - Tombol **Detail:** Membuka modal interaktif yang menampilkan checklist lengkap data pemuda serta item yang belum diisi.
        - Tombol **WA Pengingat:** Otomatis membuka aplikasi WhatsApp dengan template pesan sopan dan terformat rapi yang merinci apa saja kekurangan data pemuda tersebut dan link untuk memperbaruinya.
- **Logika Penilaian Kelengkapan Data (`Pemuda::evaluateCompleteness`):**
  - Menghitung skor kelengkapan data pemuda secara komprehensif (0-100 poin):
    - Biodata pribadi (30 poin): Nama, gender, tempat/tanggal lahir, no HP/WA, status nikah, golongan darah, foto profil.
    - Alamat lengkap (20 poin): Kecamatan, desa, detail alamat/dukuh/RT/RW.
    - Riwayat pendidikan (20 poin): Jenjang, nama sekolah/kampus (bukan strip `-`), status kelulusan.
    - Pekerjaan (15 poin): Status pekerjaan, profesi/usaha.
    - Elemen dakwah (10 poin): Keikutsertaan SATGAS, Bankom, Tim Parkir, SAR MTA, Elfata, Tim Ikhrom, dsb.
    - Keahlian & minat (5 poin): Skill dan ketertarikan bidang dakwah/pengembangan diri.
- **Integrasi Navigasi & Admin Setting:**
  - Menambahkan tautan *"Guru Daerah"* pada top utility bar, navbar publik, dan footer.
  - Menambahkan tombol aksi *"Pantau Cabang (Guru Daerah)"* pada hero section landing page.
  - Menambahkan pengaturan `kode_akses_guru_daerah` pada `HomepageSetting` dan form kelola konten beranda admin (`admin.homepage.index`).
- **Pengujian Otomatis:**
  - Menambahkan test suite `tests/Feature/GuruDaerahMonitoringTest.php` (10 test cases, 62 assertions) yang memvalidasi auth gate, validasi kode akses, evaluasi kelengkapan, pergantian cabang, endpoint detail modal, dan logout. Seluruh 64 tests di repository lulus 100%.

### 2026-09-23 — Perbaikan Bug Penghapusan Info Kegiatan Mobile Perwakilan (Data Tidak Kembali Lagi)

- **Identifikasi Penyebab Bug:**
  - Pada `KegiatanPerwakilanController::index()` dan `AuthController::kegiatanPerwakilan()`, terdapat pemanggilan otomatis `KegiatanPerwakilan::seedDefaults();`.
  - Di dalam fungsi `seedDefaults()`, terdapat pengecekan `if (static::count() > 0) return;`.
  - Akibatnya, saat admin menghapus seluruh agenda kegiatan hingga tabel kosong (`count() === 0`), sistem secara otomatis menganggap basis data belum diinisialisasi dan langsung men-generate ulang 5 data bawaan. Hal ini menyebabkan data yang telah dihapus muncul kembali secara otomatis dan tidak bisa dikosongkan sepenuhnya.
- **Langkah Perbaikan:**
  - Menghapus pemanggilan otomatis `KegiatanPerwakilan::seedDefaults()` dari siklus HTTP request di `KegiatanPerwakilanController::index()` dan endpoint API mobile `AuthController::kegiatanPerwakilan()`.
  - Mengubah fungsi `KegiatanPerwakilan::seedDefaults(bool $force = false)` agar proses seeding hanya berjalan jika dipanggil secara eksplisit (seeder atau tombol reset bawaan).
  - Membuat seeder tersendiri `database/seeders/KegiatanPerwakilanSeeder.php` dan mendaftarkannya pada `DatabaseSeeder.php`.
  - Menambahkan endpoint dan tombol **"Hapus Semua"** (`hapusSemua()`) dengan dialog konfirmasi untuk mempermudah admin membersihkan seluruh agenda kegiatan sekaligus.
  - Menambahkan endpoint dan tombol **"Template Bawaan"** (`resetDefaults()`) agar admin tetap memiliki opsi memuat ulang 5 template kegiatan resmi perwakilan kapan saja saat dibutuhkan.
  - Memperbaiki tampilan kondisi kosong (*empty state*) pada halaman admin agar menampilkan pesan ramah serta tombol aksi yang jelas.
  - Menambahkan test suite pengujian otomatis `tests/Feature/KegiatanPerwakilanDeletionBugTest.php` (4 test cases, 22 assertions) yang memvalidasi bahwa setelah seluruh kegiatan dihapus, basis data tetap kosong dan API mobile mengembalikan array kosong tanpa melakukan auto-reseed (seluruh 68 tests lulus 100%).


