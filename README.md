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
  <img src="https://img.shields.io/badge/Tests-146%20Passed-brightgreen?style=flat-square&logo=github-actions&logoColor=white" alt="Test Status">
  <img src="https://img.shields.io/badge/Timezone-Asia%2FJakarta-blue?style=flat-square" alt="Timezone">
</p>

---

## 📌 Tentang Proyek

**Sistem Pendataan Pemuda (PMD) MTA Perwakilan Sragen v2** adalah aplikasi berbasis web yang dirancang khusus untuk mengelola basis data potensi generasi muda MTA (Pemuda & Pemudi) di 70 Cabang dan 4 Wilayah se-Kabupaten Sragen.

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

| Username          | Password Default | Peran (Role)    | Cakupan (Scope)                                                  |
| :---------------- | :--------------- | :-------------- | :--------------------------------------------------------------- |
| `superadmin`      | `password`       | Superadmin      | Seluruh Kabupaten (70 Cabang)                                    |
| `koordinator_gdm` | `password`       | Koordinator GDM | Seluruh Sragen (Manajemen GDM, Cabang Binaan, Presensi & Pemuda) |
| `admin_pemuda`    | `password`       | Admin Pemuda    | Seluruh Sragen (Putra / L)                                       |
| `admin_pemudi`    | `password`       | Admin Pemudi    | Seluruh Sragen (Putri / P)                                       |
| `admin_w1`        | `password`       | Admin Wilayah   | Wilayah 1                                                        |
| `admin_w2`        | `password`       | Admin Wilayah   | Wilayah 2                                                        |
| `admin_w3`        | `password`       | Admin Wilayah   | Wilayah 3                                                        |
| `admin_w4`        | `password`       | Admin Wilayah   | Wilayah 4                                                        |

> _Catatan: Wajib mengubah password akun di atas pada menu Pengguna setelah proses instalasi di lingkungan produksi._

---

## 📖 Dokumentasi Lengkap Proyek

Dokumentasi komprehensif yang memuat rincian arsitektur, diagram ERD, skema tabel, integrasi API MTA, dan SOP pengoperasian tersedia di file:
👉 **[`AGENTS.md`](./AGENTS.md)**

---

## 📄 Lisensi

Aplikasi ini dikembangkan untuk kepentingan organisasi Pemuda MTA Perwakilan Sragen dan dilindungi di bawah lisensi internal.
