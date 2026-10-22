# Hijauku — Adopsi Pohon

Hijauku membantu pengelola program penghijauan mencatat lokasi dan pohon, menerima permohonan adopsi, mengatur kegiatan lapangan, lalu memantau perawatan dan pertumbuhan. Warga dapat melihat informasi yang sudah dibuka untuk publik dan mengikuti kegiatan atau mengadopsi pohon.

Dokumen ini menyiapkan cara menjalankan starter lokal. Arsitektur, keputusan yang masih perlu disepakati, dan hasil pemeriksaan bahan kuliah ada di folder [`docs/`](docs/).

## Status starter

Ini fondasi Sprint 0 untuk ditinjau tim, bukan aplikasi yang seluruh fiturnya sudah selesai. Kerangka memakai Laravel 13, Filament 5, Blade, dan Vite. Tersedia tabel/model scaffold untuk 18 modul, role dasar, akun/data demo, CI untuk instalasi, migrasi SQLite, route panel, build aset, PHPUnit, dan Dusk. Backlog Jira saat ini berisi 15 modul inti (M01–M15) dan 45 story; M16–M18 belum memiliki story dan sementara dianggap tertunda. Beranda publik sudah mengikuti isi dan alur dasar frame Stitch, sementara pencocokan visual penuh, aset merek, dan halaman lain masih perlu dikerjakan.

Pemilik proyek menyatakan MySQL lokalnya sudah dikonfigurasi dan berjalan; pertahankan `.env` lokal yang ada. Dokumen arsitektur awal menyebut PostgreSQL 18, sementara starter menggunakan MySQL lokal dan SQLite di CI. Tim tetap perlu menyepakati satu engine bersama. Jangan jalankan migrasi pada database lokal sebelum memeriksa koneksi di `.env` dan izin pemilik.

## Kebutuhan perangkat

- PHP 8.3 atau lebih baru, dengan ekstensi `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `zip`, `xml`, `ctype`, `curl`, dan `tokenizer`. `zip` diperlukan Composer untuk dependensi Dusk.
- Composer 2.
- Node.js 22 dan npm.
- MySQL/MariaDB yang dapat diakses dari perangkat pengembang, atau SQLite untuk uji instalasi lokal/CI.
- Git.

Laravel 13 memerlukan PHP 8.3 atau lebih baru. Filament 5 dipasang sebagai dependensi Composer dan menyediakan panel pengelola di `/app`.

## Menjalankan di komputer sendiri

1. Clone repo dan masuk ke folder proyek.

   ```sh
   git clone https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon.git
   cd Hijauku-Adopsi-Pohon
   ```

2. Pasang dependensi PHP dan JavaScript.

   ```sh
   composer install
   npm ci
   ```

3. Siapkan konfigurasi lokal. Salin `.env.example` menjadi `.env`, lalu sesuaikan nama database, host, port, username, dan password dengan konfigurasi lokal Anda. Jangan menimpa `.env` yang sudah berisi konfigurasi.

   ```sh
   cp .env.example .env
   php artisan key:generate
   ```

   Pada PowerShell, gunakan `Copy-Item .env.example .env` sebagai pengganti `cp`.

4. Pastikan `DB_*` pada `.env` menunjuk ke database proyek yang benar. Perintah berikut membuat tabel dan mengisi data demo; jangan jalankan sebelum memeriksa konfigurasi koneksinya.

   ```sh
   php artisan migrate --seed
   ```

5. Buat aset front-end, lalu jalankan server Laravel.

   ```sh
   npm run build
   php artisan serve
   ```

   Buka `http://127.0.0.1:8000` untuk halaman publik atau `http://127.0.0.1:8000/app` untuk panel Filament.

Jika aset panel Filament tidak muncul, jalankan `php artisan filament:assets` lalu muat ulang halaman.

## Pemeriksaan browser lokal

Starter menyediakan tes PHPUnit untuk akses role ke panel dan satu tes Dusk untuk beranda publik. Tes ini tidak menjalankan migrasi atau seeder. `phpunit.xml` mengarahkan tes PHPUnit ke SQLite in-memory; server web untuk Dusk hanya membuka route publik. Ketiga role aktif dapat masuk ke panel dasar; pembatasan per role dan kepemilikan data melalui Policy belum selesai.

1. Pastikan Google Chrome tersedia dan ekstensi PHP `zip` aktif pada PHP CLI.
2. Pasang ChromeDriver yang cocok dengan Chrome:

   ```sh
   php artisan dusk:chrome-driver --detect
   ```

3. Jalankan `php artisan serve` di terminal pertama. Di terminal kedua, jalankan:

   ```sh
   php artisan test --testsuite=Unit
   php artisan dusk
   ```

CI memasang ChromeDriver, menjalankan server dengan SQLite, lalu menjalankan kedua tes tersebut. Tidak ada migrasi yang diarahkan ke database MySQL lokal.

## Akun demo lokal

Seeder menyiapkan tiga akun lokal:

| Peran | Email |
| --- | --- |
| Pengelola | `pengelola@hijauku.test` |
| Petugas lapangan | `petugas@hijauku.test` |
| Warga | `warga@hijauku.test` |

Kata sandi demo dibaca dari `DEMO_PASSWORD` di `.env` dan default starter adalah `demo-hijauku`. Ubah nilai itu untuk lingkungan lokal Anda. Seeder menolak berjalan pada production. Jangan memakai akun atau kata sandi demo untuk deployment.

## Peta kode

| Lokasi | Isi |
| --- | --- |
| `app/Enums` | Nilai peran yang dipakai aplikasi |
| `app/Models` | Model Eloquent untuk domain Hijauku |
| `app/Providers/Filament` | Konfigurasi panel pengelola |
| `database/migrations` | Tabel framework dan 18 modul domain (M16–M18 masih scaffold tertunda) |
| `database/seeders` | Akun dan data contoh lokal |
| `resources/views` | Halaman publik Blade |
| `routes/web.php` | Rute web awal |
| `docs/architecture.md` | Rancangan arsitektur rev1 untuk tinjauan tim |
| `docs/requirements.md` | Aturan tugas, 15 modul/45 story di Jira, dan 3 modul tertunda |
| `docs/source-audit.md` | Hasil audit bahan kuliah, proposal, Jira, dan Stitch |
| `docs/database.md` | Skema, constraint, dan batas aturan yang masih perlu service |
| `docs/project-context.md` | Memori jangka panjang untuk agent proyek |
| `docs/agent-onboarding.md` | Urutan baca dan prompt awal untuk AI agent |
| `.agents/skills/hijauku-project/SKILL.md` | Konteks kerja bagi AI agent |
| `.ai/guidelines/hijauku.md` | Panduan proyek untuk Laravel Boost |

## Cara kerja tim

- Gunakan issue Jira sebagai sumber status pekerjaan; tulis keputusan dan perubahan requirement di sana.
- Setiap perubahan kode dibuat pada branch dan pull request, lalu diminta review rekan tim.
- Satu PBI mewakili hasil yang bisa ditinjau. Simpan acceptance criteria, implementasi, dan bukti verifikasi pada issue terkait.
- Jangan mengerjakan perubahan skema yang belum dibahas dengan pemilik modul dan koordinator teknis.
- Simpan kredensial, data pribadi, dan foto bukti pada konfigurasi atau storage privat yang sesuai; jangan commit `.env` atau data nyata.

Pembagian modul dan aturan sprint sementara dicatat di [`docs/sprint-plan.md`](docs/sprint-plan.md). Roster proyek sudah dikonfirmasi lima orang (Dinda tidak termasuk), tetapi materi kuliah meminta kelompok 6–7 orang. HAP-61 masih menyebut 6 anggota dan 54 story; catatan audit membandingkan tiket itu dengan backlog aktual.

## Bantuan AI

Panduan umum ada di `AGENTS.md`, `CLAUDE.md`, dan `.github/copilot-instructions.md`. Skill proyek ada di `.agents/skills/hijauku-project/SKILL.md`; Laravel Boost dipasang sebagai dependensi pengembangan untuk panduan Laravel sesuai versi. Jika editor mendukung MCP, gunakan konfigurasi `.mcp.json` dan pastikan `php` tersedia pada `PATH`.

## CI

GitHub Actions memasang dependensi pada runner, membuat database SQLite sementara, menjalankan migrasi dan seeder, memeriksa daftar rute, membangun aset Vite, menjalankan tes PHPUnit, lalu melakukan smoke test browser dengan Dusk. CI ini memeriksa fondasi instalasi; ia belum menjadi pengganti pengujian acceptance tiap PBI. Dusk menjalankan tes beranda publik, bukan alur autentikasi atau Policy.

## Referensi teknis

- [Laravel 13](https://laravel.com/docs/13.x)
- [Filament 5](https://filamentphp.com/docs/5.x)
- [Laravel Boost untuk konteks agent Laravel](https://laravel.com/framework/docs/boost)
- [Rancangan arsitektur awal](docs/architecture.md)
- [Audit kebutuhan dan isu terbuka](docs/source-audit.md)
