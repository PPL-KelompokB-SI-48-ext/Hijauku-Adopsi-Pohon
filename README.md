# Hijauku — Adopsi Pohon

Hijauku membantu pengelola program penghijauan mencatat pohon, mengatur kegiatan lapangan, menerima permohonan adopsi, serta memantau perawatan dan pertumbuhan.

## Status proyek

Starter ini menyiapkan fondasi Sprint 0, bukan aplikasi dengan seluruh fitur selesai. Scope Jira aktif sekarang adalah 5 epic, 45 story ber-ID HJK-001–HJK-045, dan 15 modul M01–M15. Tim aktif terdiri dari Yoga, Bitari, Arif, Wira, dan Rofii. Pembagiannya 3 modul × 3 story = 9 story per orang. M16–M18 ditunda; epic lamanya diarsipkan tanpa menghapus riwayat. Dinda bukan anggota tim.

Materi kuliah menyebut kelompok 6–7 orang. Kesesuaian tim lima orang perlu dikonfirmasi kepada dosen. Lihat [audit sumber](docs/source-audit.md) untuk dasar hitungan dan status backlog.

Fondasi teknis yang tersedia:

- Laravel 13, Filament 5, Blade, Vite, dan Eloquent dalam satu aplikasi.
- Login/panel terpisah untuk Pengelola (/app), Petugas (/petugas), dan Warga (/warga).
- Gate panel berdasarkan role, kebijakan akses dasar, tabel/model awal, dan akun demo.
- CI GitHub Actions untuk pemeriksaan aplikasi, migrasi/seeder, tes, dan aset.
- Beranda publik mengikuti isi dan alur utama frame Stitch; halaman domain dan pencocokan visual penuh belum dibuat.

## Perangkat yang diperlukan

- PHP 8.3 atau lebih baru dengan ekstensi PDO MySQL, SQLite, mbstring, OpenSSL, fileinfo, zip, XML, ctype, curl, dan tokenizer.
- Composer 2.
- Node.js 22 dan npm.
- MySQL 8 atau lebih baru untuk pengembangan bersama.
- Git dan browser Google Chrome bila menjalankan tes Dusk.

CI juga menguji migrasi dan seeder pada SQLite agar tes aplikasi cepat, serta MySQL agar skema yang digunakan tim ikut diperiksa.

## Menyiapkan salinan lokal

1. Clone repo dan masuk ke foldernya.

    git clone https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon.git
    cd Hijauku-Adopsi-Pohon

2. Pasang dependensi.

    composer install
    npm ci

3. Siapkan database khusus pengembangan. SQL contoh ada di docs/database-setup.mysql.sql. Jangan memakai database berisi data penting. Bila Anda sudah punya file .env, pertahankan; jangan menimpanya. Jika belum punya:

    Copy-Item .env.example .env

4. Periksa DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, dan DB_PASSWORD pada .env. Pastikan DB_DATABASE menunjuk ke database pengembangan kosong, misalnya hijauku_dev. Buat application key hanya untuk .env baru yang APP_KEY-nya masih kosong; bila .env sudah ada dan dipakai, jangan mengganti key:

    php artisan key:generate

5. Setelah yakin koneksi menunjuk ke database pengembangan yang benar, buat tabel dan data demo:

    php artisan migrate --seed

6. Build aset dan jalankan aplikasi:

    npm run build
    php artisan serve

Buka http://127.0.0.1:8000. Gunakan /app, /petugas, atau /warga untuk menguji panel sesuai role. Jangan jalankan migrate:fresh pada database yang berisi data yang ingin dipertahankan.

## Akun demo

Seeder lokal membuat tiga akun:

| Peran | Email |
| --- | --- |
| Pengelola | pengelola@hijauku.test |
| Petugas lapangan | petugas@hijauku.test |
| Warga | warga@hijauku.test |

Kata sandi dibaca dari DEMO_PASSWORD pada .env; nilai bawaan starter hanya untuk lokal. Ubah untuk penggunaan lokal dan jangan pakai di deployment. Seeder menolak berjalan pada production.

## Menjalankan pemeriksaan

Tes unit dan kebijakan dapat dijalankan tanpa mengarah ke database pengguna:

    php artisan test --testsuite=Unit
    php artisan test --testsuite=Feature

Untuk Dusk, pastikan ChromeDriver sesuai dengan Chrome, lalu jalankan server di satu terminal dan tes di terminal lain:

    php artisan dusk:chrome-driver --detect
    php artisan serve
    php artisan dusk

Periksa tiap kelompok rute panel secara terpisah:

    php artisan route:list --path=app
    php artisan route:list --path=petugas
    php artisan route:list --path=warga

CI menjalankan pemeriksaan ini pada runner terisolasi.

## Alur kontribusi

Setiap anggota mengambil issue Jira, bekerja pada branch sendiri, membuka pull request ke main, lalu meminta @mygads sebagai reviewer. Penulis PR memperbaiki kode dan menyelesaikan konflik. PM meninjau dan menggabungkan PR; anggota tidak menggabungkan PR sendiri.

Langkah dan perintah lengkap ada di [CONTRIBUTING.md](CONTRIBUTING.md). PR harus lulus CI dan memperoleh satu persetujuan sebelum dapat digabung.

## Peta dokumentasi

| Dokumen | Tujuan |
| --- | --- |
| [Arsitektur](docs/architecture.md) | Batas sistem, panel, modul, aturan akses, dan keputusan teknis |
| [Kebutuhan dan backlog](docs/requirements.md) | Tim, hitungan story, owner modul, sprint, dan status deferred |
| [Skema database](docs/database.md) | Tabel, relasi, constraint, dan aturan transaksi |
| [Audit sumber](docs/source-audit.md) | Hasil pemeriksaan proposal, bahan kuliah, Jira, GitHub, dan Stitch |
| [Rencana Sprint 0](docs/sprint-plan.md) | Acceptance HAP-1/HAP-61 dan checklist fondasi |
| [Onboarding agent](docs/agent-onboarding.md) | Konteks dan prompt kerja AI agent |
| [Keputusan database](docs/adr/0001-database-engine.md) | Alasan memakai MySQL untuk pengembangan tim |

## Konteks untuk AI agent

- AGENTS.md berisi aturan proyek yang berlaku untuk semua coding agent.
- docs/project-context.md adalah ringkasan memori proyek dan keputusan yang perlu dijaga.
- docs/agent-onboarding.md berisi prompt awal dan urutan dokumen yang perlu dibaca.
- .agents/skills/hijauku-project/SKILL.md berisi panduan domain khusus Hijauku.
- CLAUDE.md dan .github/copilot-instructions.md menyediakan konteks untuk tool terkait.
- .ai/guidelines/hijauku.md memberi panduan untuk Laravel Boost.

Minta agent bekerja pada satu Jira key, membaca acceptance criteria, menjalankan pemeriksaan relevan, dan menjelaskan mana yang sudah berfungsi serta mana yang masih scaffold.

## Catatan keamanan

Jangan commit .env, token API, kredensial, data pribadi, foto asli, atau file konfigurasi mesin lokal. Foto bukti harus disimpan di disk privat dan hanya disajikan setelah otorisasi. Baca aturan Policy dan ownership di dokumen arsitektur sebelum membuat resource atau query.
