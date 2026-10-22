# Setup lokal Hijauku

Panduan ini untuk anggota tim yang baru mengambil salinan repo. Gunakan database MySQL khusus proyek, bukan database yang berisi data penting. Perintah setup meminta konfirmasi nama database sebelum menjalankan migration dan seeder.

## Prasyarat

- Git.
- PHP 8.3 atau lebih baru dengan ekstensi PDO MySQL, SQLite, mbstring, OpenSSL, fileinfo, zip, XML, ctype, curl, dan tokenizer.
- Composer 2.
- Node.js 22 atau lebih baru dan npm.
- MySQL 8 atau lebih baru.
- Google Chrome dan ChromeDriver yang sesuai bila ingin menjalankan tes browser Dusk.

Pastikan `php`, `composer`, `node`, dan `npm` dapat dipanggil dari PowerShell. Di Windows, Laragon dapat menyediakan PHP dan MySQL, lalu Composer dan Node.js dipasang terpisah.

## Clone repo

Clone repo satu kali, lalu masuk ke folder proyek:

```powershell
git clone https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon.git
Set-Location Hijauku-Adopsi-Pohon
```

Pastikan berada di branch `main` untuk membuat branch pekerjaan baru:

```powershell
git switch main
git pull --ff-only origin main
git switch -c feature/HAP-KEY-nama-singkat
```

Ganti `HAP-KEY` dengan issue yang benar-benar ditugaskan di Jira. Jangan membuat branch untuk issue milik anggota lain.

## Siapkan database MySQL

1. Buat database dan user lokal khusus Hijauku. Contoh SQL ada di [database-setup.mysql.sql](database-setup.mysql.sql). Ganti password contoh sebelum dijalankan.
2. Beri user hanya akses ke database pengembangan proyek.
3. Pastikan MySQL berjalan dan alamat lokal cocok dengan `.env`, biasanya `127.0.0.1:3306`.

Jangan gunakan user MySQL dengan hak global jika tersedia user lokal dengan hak terbatas. SQL contoh hanya dijalankan sekali; bila database atau user sudah ada, jangan mengulang perintah `CREATE` tanpa memeriksa kondisinya.

## Setup otomatis di Windows

Sebelum menjalankan script, periksa bahwa target database pada `.env` memang database pengembangan yang boleh dimigrasi. Script tidak menimpa `.env` yang sudah ada, tidak menampilkan password database, hanya membuat `APP_KEY` bila masih kosong, menolak nilai `APP_ENV=production`, dan tidak pernah menjalankan `migrate:fresh`.

Jalankan:

```powershell
.\scripts\setup.ps1
```

Script akan memasang dependency PHP dan JavaScript, membuat key bila perlu, membangun aset, lalu menampilkan host dan nama database yang akan dipakai. Migrasi baru berjalan setelah Anda mengetik persis nama `DB_DATABASE` dari `.env`.

Kalau PowerShell menolak file script karena execution policy, izinkan script hanya untuk sesi terminal saat ini, lalu jalankan ulang:

```powershell
Set-ExecutionPolicy -Scope Process -ExecutionPolicy Bypass
.\scripts\setup.ps1
```

Untuk memasang dependency dan membangun aset tanpa koneksi database:

```powershell
.\scripts\setup.ps1 -SkipDatabase
```

Opsi `-AllowRemoteDatabase` hanya untuk anggota yang memang memakai server database bersama/jarak jauh. Periksa host dan nama database dengan cermat sebelum memakai opsi itu.

## Setup manual

Jika tidak memakai script, jalankan langkah ini dari folder repo.

```powershell
if (-not (Test-Path .env)) { Copy-Item .env.example .env }
composer install
npm ci
```

Edit `.env` secara lokal. Atur `DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`. Jangan commit `.env`. Pastikan database khususnya sudah dibuat dan `APP_ENV=local`.

Buat application key hanya saat `APP_KEY` masih kosong. Mengganti key pada instalasi yang sudah dipakai dapat membuat cookie atau data terenkripsi yang lama tidak terbaca.

```powershell
php artisan key:generate
npm run build
```

Setelah memeriksa nama target database, jalankan migration dan seeder:

```powershell
php artisan migrate --seed
```

Jangan gunakan `php artisan migrate:fresh` pada database yang mungkin berisi data yang ingin dipertahankan.

## Jalankan aplikasi

Buka dua terminal dari folder repo. Terminal pertama menjalankan Laravel:

```powershell
php artisan serve
```

Terminal kedua menjalankan Vite selama pengembangan UI:

```powershell
npm run dev
```

Buka <http://127.0.0.1:8000>. Panel dan halaman login:

| Peran | URL | Akun demo |
| --- | --- | --- |
| Pengelola | `/app/login` | `pengelola@hijauku.test` |
| Petugas | `/petugas/login` | `petugas@hijauku.test` |
| Warga | `/warga/login` | `warga@hijauku.test` |

Password ketiga akun mengikuti `DEMO_PASSWORD` pada `.env`. Nilai awal starter adalah `demo-hijauku`, khusus penggunaan lokal. Ganti nilai itu bila ingin membagikan database dev, lalu jalankan `php artisan db:seed --class=DemoWorkspaceSeeder` untuk memperbarui akun demo. Seeder bersifat idempotent untuk data demo dan menolak `APP_ENV=production`.

Setiap akun hanya boleh masuk ke panel yang sesuai dengan role dan status aktifnya. Filament mengelola login berbasis session, pembatasan percobaan login, logout, invalidasi session, dan token CSRF. Session lokal disimpan di tabel `sessions` karena konfigurasi menggunakan `SESSION_DRIVER=database`.

## Perintah harian

Perbarui `main` lokal sebelum membuat pekerjaan baru:

```powershell
git switch main
git pull --ff-only origin main
```

Untuk branch yang masih dikerjakan, ambil perubahan terbaru dan gabungkan ke branch sendiri:

```powershell
git fetch origin
git merge origin/main
```

Penulis branch menyelesaikan konflik, menjalankan pemeriksaan, membuat commit, lalu push kembali. Jangan push langsung ke `main` dan jangan memakai `git push --force`.

Perintah pemeriksaan utama:

```powershell
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
npm run build
```

Tes PHP memakai SQLite in-memory dari konfigurasi PHPUnit. Tes tersebut tidak memigrasikan MySQL lokal. Panduan pull request dan perubahan fitur ada di [CONTRIBUTING.md](../CONTRIBUTING.md).

## Jika setup gagal

- **`php` atau `composer` tidak ditemukan:** tambahkan folder bin ke PATH, tutup PowerShell, buka lagi, lalu cek `php -v` dan `composer --version`.
- **`npm ci` gagal:** pastikan Node.js 22+ terpasang dan `package-lock.json` ikut ter-clone.
- **Koneksi MySQL ditolak:** cek service MySQL, host/port, user/password, serta grant pada database yang sama dengan `.env`.
- **`Unknown database`:** buat database lebih dulu memakai SQL contoh, lalu cek ejaan `DB_DATABASE`.
- **Tabel `sessions` tidak ada:** jalankan `php artisan migrate --seed` pada database proyek yang telah dikonfirmasi.
- **Akun tidak dapat masuk:** cek alamat panel, nilai `DEMO_PASSWORD`, status aktif dan role akun. Seeder lokal dapat dijalankan ulang untuk memulihkan akun demo.
- **Aset tidak muncul:** pastikan `npm run dev` berjalan saat pengembangan, atau jalankan `npm run build` untuk aset hasil build.
