# ADR 0001: Engine database pengembangan

- **Status:** Ditetapkan untuk baseline Rev 1.
- **Tanggal:** 22 Oktober 2026.
- **Pemilik keputusan:** PM proyek.
- **Scope keputusan:** pengembangan lokal dan CI starter; bukan keputusan layanan production.

## Konteks

Dokumen rancangan awal menyebut PostgreSQL 18. Konfigurasi lokal Hijauku yang sudah berjalan menggunakan MySQL, dan starter juga memakai MySQL. Mengharuskan tim menyiapkan engine berbeda pada Sprint 0 menambah beban setup serta menyimpang dari lingkungan kerja yang sudah tersedia.

## Keputusan

Gunakan MySQL 8+ sebagai database pengembangan bersama untuk starter dan fitur tim. Jangan mengganti atau menimpa .env pengguna yang sudah ada. Setiap anggota menggunakan database khusus lokal hijauku_dev; jangan berbagi kredensial lokal.

GitHub Actions memeriksa migration dan seeder pada MySQL 8 serta SQLite in-memory. SQLite dipakai untuk tes cepat, bukan pengganti engine pengembangan tim.

## Dampak

- Dokumen setup, .env.example, migration, dan CI memakai MySQL.
- Fitur database harus tetap lolos tes SQLite bila framework mendukungnya.
- PostgreSQL 18 pada dokumen arsitektur awal menjadi catatan historis, bukan target starter Rev 1.
- Jika kelak dibutuhkan deployment dengan PostgreSQL, tim membuat ADR baru dan menjalankan pemeriksaan kompatibilitas sebelum migrasi engine.
