# Hijauku - Adopsi Pohon

Hijauku dipakai untuk mencatat program penghijauan, kegiatan lapangan, adopsi pohon, serta perawatan dan pertumbuhan pohon. Aplikasi dibuat dengan Laravel 13 dan Filament 5 dalam satu aplikasi dengan panel untuk pengelola, petugas, dan warga.

## Kondisi proyek

Status berikut dicek pada 11 November 2026. Jira menjadi sumber status dan tanggal pekerjaan. GitHub menjadi sumber branch, pull request, dan hasil CI.

- Backlog aktif berisi 5 epic dan 45 story HJK-001 sampai HJK-045 untuk M01 sampai M15.
- Tim berisi Yoga, Bitari, Arif, Wira, dan Rofii. Dinda bukan anggota.
- Sprint 0 sudah ditutup. Sprint 1 berlangsung 19 Oktober sampai 22 November. Sprint 2 berlangsung 23 November sampai 27 Desember 2026.
- Dari 45 story aktif, 10 Done, 7 In Progress, dan 28 To Do. HAP-10/11/12 untuk M02 sudah Done dan masuk `main` melalui PR #20; HAP-62 untuk persiapan PR juga Done.
- HAP-6 dan M16-M18 sudah di luar scope aktif. Beberapa tabel/model lama masih ada sebagai scaffold; jangan membangun halaman atau alur untuk area itu tanpa keputusan scope baru.

### Fitur yang sudah masuk ke `main`

| Modul | Isi yang sudah diterima | Status |
| --- | --- | --- |
| M01 | HJK-001 sampai HJK-003: buat program sebagai draf, aktifkan program, dan arsipkan program selesai | Selesai, PR #2, #4, dan #17 |
| M02 | HJK-004 sampai HJK-006: catat lokasi, cari/filter per program, dan ubah atau hapus lokasi dengan batas relasi | Selesai, PR #20; CI run 42930124355 lulus |
| M07 | HJK-019: catat rencana kegiatan | Selesai, PR #11 |
| M13 | HJK-037 sampai HJK-039: susun rencana perawatan, atur prioritas, koreksi atau batalkan rencana | Selesai, PR #13 dan #16 |

Story lain belum otomatis selesai hanya karena tabel atau panel dasarnya sudah tersedia. PR #12, #14, dan #15 sudah ditutup tanpa merge. Branch personal pemiliknya masih menyimpan commit unik dan perlu disinkronkan ke `main` sebelum PR pengganti dibuat. Snapshot commit dan jarak branch ada di [audit sumber](docs/source-audit.md).

## Tampilan dan API

M01 dan M02 memakai Resource Filament/Livewire, Policy, validasi server, pencatatan audit, serta tes Feature dan Dusk sesuai acceptance story. Shell tiga panel memakai tema bersama. Daftar M02 mengikuti frame Stitch yang tersedia; form dan detail belum punya frame pembanding, dan kontrol filter lanjutan belum punya acceptance. Karena itu, kesesuaian 1:1 hanya diklaim untuk elemen yang benar-benar ditunjukkan frame, bukan seluruh layar.

Story yang sudah diterapkan memakai request Livewire dari Filament. Saat ini tidak ada REST API terpisah untuk CRUD. API baru dibuat jika ada requirement dan acceptance criteria Jira yang memang membutuhkannya. Detail dan gap visual ada di [panduan UI](docs/ui-design.md).

## Teknologi

- PHP 8.3 atau lebih baru, Laravel 13, Filament 5, Blade, Vite, dan Eloquent.
- MySQL 8+ untuk database development.
- SQLite terisolasi untuk tes cepat dan browser test.
- Laravel Dusk untuk alur browser; GitHub Actions menjalankan Unit, Feature, build aset, pemeriksaan migrasi, dan Dusk.

## Persiapan lokal

1. Pasang PHP dengan ekstensi PDO MySQL, SQLite, mbstring, OpenSSL, fileinfo, zip, XML, ctype, curl, dan tokenizer; Composer 2; Node.js 22; MySQL 8+; serta Git.
2. Clone repository:

       git clone https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon.git
       cd Hijauku-Adopsi-Pohon

3. Buat database khusus proyek di MySQL, lalu ikuti [panduan setup lokal](docs/local-setup.md). Di Windows, script `.\scripts\setup.ps1` membantu menyiapkan aplikasi. Script tidak mengganti `.env` yang sudah ada dan meminta konfirmasi sebelum migrasi.
4. Jalankan aplikasi:

       php artisan serve
       npm run dev

Jangan arahkan migration, seeder, atau browser test ke database pribadi atau database bersama. Jangan jalankan `migrate:fresh` pada database yang menyimpan data.

## Akun demo lokal

Seeder menyediakan akun untuk tiga role. Password dibaca dari `DEMO_PASSWORD` pada `.env`; nilai awal `demo-hijauku` hanya untuk lokal.

| Role | Email |
| --- | --- |
| Pengelola | `pengelola@hijauku.test` |
| Petugas lapangan | `petugas@hijauku.test` |
| Warga | `warga@hijauku.test` |

Panelnya: Pengelola `/app`, Petugas `/petugas`, dan Warga `/warga`. Jangan memakai akun demo di deployment.

## Pemeriksaan

Jalankan dari root repository:

    php artisan test --testsuite=Unit
    php artisan test --testsuite=Feature
    php artisan dusk
    npm run build

Sebelum menjalankan Dusk secara lokal, siapkan database SQLite khusus Dusk sesuai [panduan setup lokal](docs/local-setup.md#menjalankan-laravel-dusk). CI menggunakan database sementara dan tidak memakai database development tim.

## Kerja tim

Setiap anggota memakai satu branch personal untuk seluruh story miliknya. Nama branch yang saat ini terlihat di GitHub adalah Yoga `yoga`, Bitari `bitaridamar`, Arif `arif`, Wira `ayuwira`, dan Rofii `Rofii`. Perbedaan huruf besar atau nama branch sedikit berbeda sudah diterima PM; jangan mengganti nama branch aktif. Sertakan key HAP kapital di setiap commit dan PR.

Story baru boleh ditandai Done setelah acceptance dan tes terpenuhi pada branch personal yang sudah di-push, CI terbaru lulus, serta review dan penerimaan PM selesai. Pada akhir sprint, setiap anggota membuka satu PR yang berisi story sprintnya. Penulis menyinkronkan `main` dan menyelesaikan konflik; PM mengatur urutan review dan menjadi satu-satunya pihak yang merge. PR anggota perlu approval PM dan CI hijau. PR PM tidak membutuhkan self-review, tetapi CI tetap harus hijau. Task integrasi sprint ditutup setelah PR terkait merged dan CI akhir lulus. Jika integrasi menemukan acceptance yang gagal, story terkait dibuka kembali.

Rencana terbaru memuat 30 story Sprint 1, enam per anggota dari dua modul utuh, dan 15 story Sprint 2, tiga per anggota dari satu modul. Tiga story Sprint 2 per anggota dijadwalkan dua di minggu 11 dan satu di minggu 12. Minggu 13 dipakai untuk integrasi PR, minggu 14 untuk UAT, pemeriksaan deploy, dan dokumentasi rilis, lalu minggu 15 untuk perbaikan temuan, laporan akhir, dan persiapan presentasi. Rincian issue dan tanggal ada di [jadwal story](docs/task-schedule.md).

Panduan lengkap ada di [CONTRIBUTING.md](CONTRIBUTING.md), termasuk cara memperbarui `main`, commit, menghubungkan pekerjaan ke Jira, dan membuat PR.

## Peta dokumentasi

| Dokumen | Isi |
| --- | --- |
| [Arsitektur](docs/architecture.md) | Batas sistem, panel role, modul, dan keputusan teknis |
| [Kebutuhan](docs/requirements.md) | Roster, pembagian 45 story, aturan kompleksitas, dan scope |
| [Jadwal story](docs/task-schedule.md) | Rencana minggu, tanggal Jira, serta status terakhir yang diperiksa |
| [Database](docs/database.md) | Tabel, relasi, constraint, dan transaksi |
| [Panduan UI](docs/ui-design.md) | Pemetaan Stitch, implementasi saat ini, dan gap visual |
| [Audit sumber](docs/source-audit.md) | Pemeriksaan Jira, GitHub, CI, roster, dan bahan kuliah |
| [Alur Jira](docs/jira-workflow.md) | Acceptance criteria, estimasi, work log, branch, commit, dan PR |
| [Setup lokal](docs/local-setup.md) | Clone, `.env`, database, seed, login, Dusk, dan perintah harian |
| [Onboarding agent](docs/agent-onboarding.md) | Urutan baca dan instruksi AI agent |

## Catatan keamanan

Jangan commit `.env`, token, password, data pribadi, atau foto asli. Foto bukti disimpan di disk privat dan hanya bisa dibuka setelah otorisasi. Baca aturan Policy dan ownership sebelum menambah Resource, Page, atau endpoint.
