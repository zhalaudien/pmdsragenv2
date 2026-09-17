# Sistem Pendataan Pemuda (PMD) MTA Perwakilan Sragen v2

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="Laravel Logo">
</p>

<p align="center">
  <strong>Platform Terintegrasi Pendataan, Pemetaan Potensi, & Sinkronisasi Data Pemuda-Pemudi MTA Perwakilan Sragen</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP Version">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel Version">
  <img src="https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/TailwindCSS-4.x-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Tests-18%20Passed-brightgreen?style=flat-square&logo=github-actions&logoColor=white" alt="Test Status">
  <img src="https://img.shields.io/badge/Timezone-Asia%2FJakarta-blue?style=flat-square" alt="Timezone">
</p>

---

## 📌 Tentang Proyek

**Sistem Pendataan Pemuda (PMD) MTA Perwakilan Sragen v2** adalah aplikasi berbasis web yang dirancang khusus untuk mengelola basis data potensi generasi muda MTA (Pemuda & Pemudi) di 70 Cabang dan 4 Wilayah se-Kabupaten Sragen. 

Aplikasi ini mencakup formulir registrasi publik mandiri, dashboard analitik 7 dimensi persebaran potensi pemuda, manajemen akses bertingkat (*Role-Based Access Control*), fitur ekspor-impor Excel, cadangan database otomatis, serta integrasi *real-time* ke **REST API MTA Pusat** (`api.mta.or.id`) untuk validasi dan verifikasi data warga.

---

## 🚀 Fitur-Fitur Utama

1. **Formulir Pendaftaran Publik (`/pendataan`)**
   - Multi-step wizard pendaftaran tanpa login: Data Pribadi, Alamat Domisili (dropdown bertingkat hingga desa), Pendidikan, Pekerjaan/Wirausaha, Keterlibatan Unit Tugas Dakwah (Satgas, Bankom, Parkir, Ikhrom, Pengurus), Keahlian, Minat, dan Pas Foto.
   - Menggunakan *database transaction* untuk integritas penyimpanan multi-tabel dan pembuatan nomor registrasi unik.

2. **Dashboard & Analisis Persebaran Data (`/admin/dashboard`, `/admin/persebaran`)**
   - Metrik ringkasan real-time dan tabel *Data Pemuda Terakhir Diedit (Last Edit)*.
   - Analisis persebaran komprehensif **7 Dimensi**:
     1. Unit Tugas Dakwah (Satgas, Bankom, Tim Parkir, Tim Ikhrom, Pengurus).
     2. Pendidikan & Top 10 Kampus/Sekolah serta Jurusan.
     3. Bakat & Keahlian (Tingkat Pemula, Menengah, Mahir).
     4. Minat & Hobi.
     5. Ketenagakerjaan & Pelaku Wirausaha Mandiri.
     6. Demografi Rentang Usia & Sebaran Kecamatan di Sragen.
     7. Golongan Darah & Kesiapsiagaan Donor Darah.

3. **Integrasi REST API MTA Pusat (`api.mta.or.id`)**
   - **Terkunci Khusus Perwakilan Sragen (Kode 86)** dengan UUID resmi `3246792b-f0a7-48ca-95fa-379e3bee777d`.
   - **Import Otomatis Sesuai Cabang Pusat**: Satu klik import langsung memetakan cabang pemuda dari data cabang resmi MTA Pusat tanpa memilih manual.
    - **Pencarian Cabang Terintegrasi**: Dropdown pemilihan Cabang dilengkapi kotak pencarian instan langsung di dalam menu dropdown untuk mempermudah pemilihan dari 70 cabang se-Kabupaten Sragen.
    - **Autocomplete Cerdas Gabungan (Pemuda + Warga MTA Pusat)**: Mengetik nama pemuda pada cabang yang dipilih otomatis mencari data di database lokal Pemuda maupun di server API MTA Pusat secara real-time.
    - **Mode Update & Pra-Pengisian Otomatis**: Jika memilih data pemuda yang sudah ada, form beralih ke mode update dan mengisi seluruh data secara otomatis. Jika memilih warga dari MTA Pusat, profil otomatis terisi dan berstatus langsung terverifikasi (`status_verifikasi = 'verified'`).
    - **6 Elemen Dakwah Resmi**: Pembaruan pilihan elemen dakwah khusus: `SATGAS`, `BANKOM`, `SAR MTA`, `TIM PARKIR`, `ELFATA`, dan `TIM IKHROM`.
    - **Verifikasi Otomatis (Rule 16)**: Status verifikasi hanya ada 2 (`verified` dan `pending`), ditentukan otomatis oleh sistem berdasarkan sinkronisasi API MTA Pusat, dan tidak dapat diubah manual.

4. **Sistem Peran & Hak Akses (RBAC Bertingkat)**
   - `superadmin`: Akses penuh seluruh sistem, user management, backup, dan konfigurasi.
   - `admin_wilayah`: Mengelola pemuda di wilayah wewenangnya.
   - `admin_cabang`: Mengelola pemuda di cabangnya.
   - `admin_pemuda`: Mengelola pemuda putra (`gender = 'L'`) seluruh Sragen.
   - `admin_pemudi`: Mengelola pemudi putri (`gender = 'P'`) seluruh Sragen.
   - `admin_wilayah_pemuda`: Mengelola pemuda putra (`gender = 'L'`) di wilayahnya.
   - Penegakan scope akses dilakukan di tingkat query database server-side.

5. **Keamanan Siber & Perlindungan Data Sensitif**
   - Penghapusan total field dan penampungan Nomor Induk Kependudukan (NIK) dari form dan database.
   - Penutupan rute publik tanpa autentikasi yang rentan scraping/IDOR.
   - Rate limiting ketat (`throttle:60,1`) pada seluruh endpoint AJAX publik.
   - Proteksi direktori upload (`public/uploads/.htaccess` melarang eksekusi skrip).
   - Validasi ketat nama dan ekstensi berkas pada engine backup (`PemudaBackupService`) dengan mitigasi *path traversal*.
   - Whitelist atribut pada response JSON AJAX untuk melindungi data kontak pimpinan.
   - Logout via HTTP `POST` dengan proteksi token CSRF.

---

## 🛠️ Tumpukan Teknologi (Tech Stack)

- **Backend:** Laravel 12, PHP 8.2+
- **Database:** MySQL 8.0+
- **Frontend:** Blade Templates, Tailwind CSS 4 (Vite), AdminLTE 4 components, Bootstrap Icons, Font Awesome 6
- **Chart:** Chart.js 4.4
- **Spreadsheet Engine:** PhpSpreadsheet
- **API Engine:** Laravel HTTP Client (Guzzle)

---

## 📦 Panduan Instalasi Cepat

### 1. Prasyarat
- PHP >= 8.2 (ekstensi: `pdo_mysql`, `mbstring`, `fileinfo`, `gd`/`imagick`, `curl`, `zip`)
- Composer >= 2.x
- Node.js >= 18.x & NPM
- MySQL >= 8.0

### 2. Langkah Instalasi

```bash
# 1. Clone repositori
git clone https://github.com/zhalaudien/pmdsragenv2.git
cd pmdsragenv2

# 2. Pasang dependensi
composer install
npm install

# 3. Konfigurasi environment
cp .env.example .env
php artisan key:generate

# Sesuaikan kredensial DB dan API MTA di .env:
# MTA_PERWAKILAN_UUID="3246792b-f0a7-48ca-95fa-379e3bee777d"

# 4. Migrasi & seeding database
php artisan migrate --seed

# 5. Buat symlink storage & kompilasi asset
php artisan storage:link
npm run build

# 6. Jalankan server lokal
php artisan serve
```

Aplikasi dapat diakses melalui peramban web di `http://127.0.0.1:8000`.

---

## 🔑 Akun Administrator Bawaan (Seeder)

| Username | Password Default | Peran (Role) | Cakupan (Scope) |
| :--- | :--- | :--- | :--- |
| `superadmin` | `password` | Superadmin | Seluruh Kabupaten (70 Cabang) |
| `admin_pemuda` | `password` | Admin Pemuda | Seluruh Sragen (Putra / L) |
| `admin_pemudi` | `password` | Admin Pemudi | Seluruh Sragen (Putri / P) |
| `admin_w1` | `password` | Admin Wilayah | Wilayah 1 |
| `admin_w2` | `password` | Admin Wilayah | Wilayah 2 |
| `admin_w3` | `password` | Admin Wilayah | Wilayah 3 |
| `admin_w4` | `password` | Admin Wilayah | Wilayah 4 |

> *Catatan: Wajib mengubah password akun di atas pada menu Pengguna setelah proses instalasi di lingkungan produksi.*

---

## 🧪 Menjalankan Pengujian Otomatis (Testing)

Proyek ini telah dilengkapi dengan 29 pengujian unit dan fitur otomatis (100% Passed, 134 assertions):

```bash
# Menjalankan seluruh pengujian
php artisan test

# Menjalankan pengujian alur pendataan & autocomplete
php artisan test --filter=PendataanFlowTest

# Menjalankan pengujian keamanan isolasi warga MTA Sragen
php artisan test --filter=WargaMtaSecurityTest

# Menjalankan pengujian otorisasi rute & proteksi scraping
php artisan test --filter=PmdSragenRoutesTest
```

---

## 📖 Dokumentasi Lengkap Proyek

Dokumentasi komprehensif yang memuat rincian arsitektur, diagram ERD, skema tabel, integrasi API MTA, dan SOP pengoperasian tersedia di file:
👉 **[`AGENTS.md`](./AGENTS.md)**

---

## 📄 Lisensi

Aplikasi ini dikembangkan untuk kepentingan organisasi Pemuda MTA Perwakilan Sragen dan dilindungi di bawah lisensi internal.
