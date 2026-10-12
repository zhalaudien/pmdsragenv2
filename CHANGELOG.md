# Changelog

## [Unreleased] - 10 Oktober 2026

### Added (Ditambahkan)

-   Aset logo `pemudamta-inverted` untuk logo inverted pada halaman auth (CL4)
-   views/pendataan/auth.blade.php — Tambah style `back-btn` (CL3)
-   views/pendataan/auth.blade.php — Tambah image public `pemudamta-inverted` untuk inverted logo halaman (CL4)
-   views/layout/app.blade.php — Tambah custom alert SweetAlert2 (CL6)
-   views/pendataan/form.blade.php — Tambah alert dengan Swal (CL7)
-   views/pendataan/form.blade.php — Tambah custom alert: pendaftar Laki-laki wajib unggah pass-photo, logic dipindah dari backend ke JS (CL13)

### Changed (Diubah)

-   views/pendataan/auth.blade.php — Update UI `/pendataan` berorientasi layar mobile (CL5)
-   views/pendataan/form.blade.php — Sanitasi form (teks & angka) harus valid (CL8)
-   views/pendataan/form.blade.php — Ubah warna stepper jadi `bg-emerald-600` (CL11)
-   views/pendataan/form.blade.php — Penghalusan kata status pendidikan: `Lulus`, `Masih Bersekolah / Kuliah`, `Tidak Tamat` (CL12)
-   views/pendataan/sukses.blade.php — Update text button (CL15)
-   views/guru_daerah/auth.blade.php — Update small UI (CL16)

### Fixed (Diperbaiki)

-   views/pendataan/auth.blade.php — Validasi umur minimal: tidak mungkin kelahiran tahun sekarang, minimal 16 tahun sesuai UU No. 40 Tahun 2009 tentang Kepemudaan (CL1)
-   views/pendataan/auth.blade.php — Batas umur maksimal 40 terlalu hardcoded, data real bisa di atas 40 tahun (contoh: 45 tahun) (CL2)

### Notes (Catatan)

-   views/pendataan/form.blade.php — Perlu cek backend: apakah data kecamatan, kabupaten, dan provinsi sudah tersedia di DB. Form akan error jika belum ada (CL9)
-   views/pendataan/form.blade.php — [REVIEW] `Uncaught TypeError: Cannot read properties of null (reading 'contains')`. Apakah logic masih diperlukan? Input sudah disabled (CL10)
-   views/pendataan/form.blade.php — `business_maps_url` tidak otomatis ada di tabel `pekerjaan`? (CL14)
