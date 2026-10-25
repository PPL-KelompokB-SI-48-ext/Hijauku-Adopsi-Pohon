# Audit sumber dan status proyek

Snapshot ini mencatat pemeriksaan pada 25 Oktober 2026. Jira menjadi sumber status dan jadwal, GitHub menjadi sumber branch/commit/PR/CI, sedangkan proposal dan bahan kuliah menjadi sumber rancangan. Periksa kembali halaman live sebelum memakai status untuk keputusan baru.

## Sumber yang diperiksa

| Sumber | Hasil pemeriksaan |
| --- | --- |
| Ketentuan Proyek PPL dan Modul 1 | PDF menyebut kelompok 6-7 orang, sedangkan teks LMS menyebut 5-6 orang. Tim berisi lima orang; konfirmasi dosen masih diperlukan. |
| Rancangan arsitektur, proposal, dan bahan requirement/Scrum | Cocokkan kebutuhan, arsitektur, modul, jadwal, pengujian, dan batas fitur. Koreksi proposal dicatat pada [audit proposal](proposal-audit.md). |
| Jira | Ada 5 epic dan 45 story aktif HJK-001-HJK-045 untuk M01-M15. Sprint 0 sudah ditutup, Sprint 1 aktif, Sprint 2 masih future. |
| GitHub | Repo `PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon`, branch personal, pull request, protection, dan CI diperiksa. |
| Stitch | Ada 25 frame. Pemetaan mockup ke 15 modul aktif ada di [panduan UI](ui-design.md). Mockup bukan bukti implementasi. |
| Canva dan proposal | Proposal Google Docs diperbarui dengan kalimat yang lebih mudah dibaca. Presentasi 27 slide sudah disimpan setelah persetujuan PM. |

## Roster dan rencana kerja

Anggota aktif adalah Yoga, Bitari, Arif, Wira, dan Rofii. Dinda tidak termasuk. Backlog Jira memiliki lima epic aktif, 45 story, dan kode HJK-001 sampai HJK-045. HAP-6 sudah dihapus permanen; M16-M18 berada di luar scope aktif. Jangan menomori ulang issue yang sudah dibuat.

Setiap orang memiliki tiga modul dengan tiga story per modul. Rencana Sprint 1 menempatkan satu modul utuh atau tiga story per anggota. Sprint 2 menempatkan dua modul utuh atau enam story per anggota. Pembagian ini menjaga PBI satu modul tetap dalam sprint yang sama, tetapi belum membuktikan tingkat kompleksitas yang diminta rubrik. Pemilik modul perlu memeriksa kompleksitas dan dependensi saat refinement.

Sprint 1 tetap berjalan pada minggu 6-10, 19 Oktober-22 November 2026. Sprint 2 berjalan pada minggu 11-15, 23 November-27 Desember 2026. Rencana awal memakai minggu 6 untuk backlog dan memulai coding pada minggu 7. Riwayat GitHub menunjukkan alur HJK-001 masuk ke `main` lewat PR #2 pada 22 Oktober. Tes Dusk dan penerimaan PM menyusul melalui PR #4 pada 25 Oktober. HAP-16 dan HAP-25 juga In Progress pada akhir minggu 6, sebelum tanggal mulai rencana minggu 7. Kalender sprint tidak berubah; tanggal target bukan work log. Lihat [jadwal mingguan](task-schedule.md).

## Status Jira pada 25 Oktober 2026

- HAP-1 dan HAP-61 berstatus Done setelah PM menerima fondasi Sprint 0.
- HAP-7 / HJK-001 berstatus Done setelah acceptance, tes browser, CI, dan merge PR #4.
- HAP-2 dan HAP-3 berstatus In Progress karena HAP-16 dan HAP-25 sedang dikerjakan. HAP-16 dan HAP-25 berstatus In Progress; HAP-8 sampai HAP-15, HAP-17 sampai HAP-24, dan HAP-26 sampai HAP-51 masih To Do.
- HAP-61 mempertahankan Original estimate 18 jam dan memiliki remaining estimate 0 jam. HAP-7 mempertahankan Original estimate 4 jam dan memiliki remaining estimate 0 jam.
- Tidak ada work log aktual yang dibuat atau diubah untuk HAP-61 maupun HAP-7. Pelaksana mengisi work log sendiri hanya untuk waktu yang benar-benar dipakai.
- Due date HAP-7 tetap 1 November 2026 sesuai rencana. Selesai lebih awal tidak mengubah tanggal target story lain.

## GitHub dan branch

`main` berada pada merge commit `892ec723` dari PR #4.

- PR #1 menambahkan starter Sprint 0 dan PR #2 menambahkan alur HJK-001. Keduanya tetap merged di `main`.
- PR #4 menambah Dusk untuk pembuatan program, memperketat validasi tanggal, dan menyegarkan dokumentasi. PR ini merged pada 25 Oktober 2026 setelah CI lulus.
- PR #5 yang berisi revert PR #1 dan #2 ditutup tanpa merge. Branch revert tidak digunakan untuk pengembangan.
- PR #3 Rofii ditutup dengan perubahan yang diminta. PR pengganti #6 dibuka pada branch personal `rofii` dengan commit `22737f8`.
- PR #6 CI-nya lulus pada run `40184824535`, tetapi branch tersebut dibuat dari `d683407`, sebelum PR #4 masuk. PR ini belum mendapat approval dan berstatus changes requested. CI menjalankan Dusk halaman publik, bukan alur M04. Pemilik branch perlu mengambil `main` terbaru dan menambahkan Laravel Dusk untuk alur M04 sebelum meminta review lagi. Langkahnya ada di [panduan migrasi PR Rofii](rofii-pr-update.md).
- Branch lama `feature/HAP-16-jenis-pohon` masih dipertahankan sampai pemilik memastikan PR #6 aman dan tidak ada perubahan yang hilang. Jangan menghapus atau mengganti branch anggota saat PR pengganti belum diterima.
- Branch personal tetap `yoga`, `bitari`, `arif`, `wira`, dan `rofii`. Commit dan PR tetap mencantumkan key HAP kapital agar bisa ditautkan ke Jira.

## Pengujian dan kondisi implementasi

Stack aplikasi adalah Laravel 13, Filament 5, Blade, Vite, Eloquent, dan MySQL 8+. Panel manager `/app`, field staff `/petugas`, dan citizen `/warga` berbagi satu aplikasi. Policy dasar dan autentikasi sudah ada; fitur bisnis selain yang tercatat pada issue yang selesai tetap dikerjakan melalui PBI masing-masing.

CI run [40181326721](https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon/actions/runs/40181326721) lulus pada head PR #4 `7a5efae`. Hasilnya 11 tes Unit, 20 tes Feature dengan 99 assertions, dan 2 tes Dusk dengan 9 assertions. CI juga memeriksa Composer, migration/seeder SQLite dan MySQL, route panel, serta build Vite. Tes Dusk HJK-001 login sebagai manager, mengisi form Filament, memilih tanggal, menyimpan program Draf, dan memeriksa hasil pada daftar.

CI PR #6 Rofii juga lulus pada head-nya, tetapi hasil itu tidak membuktikan alur jenis pohon lewat browser. PR tersebut berisi tes Feature, belum Dusk untuk M04, dan perlu diuji lagi setelah branch disinkronkan ke `main` terbaru. Tes aplikasi lokal tidak dijalankan pada database pemilik. `.env` dan database MySQL lokal tidak dibaca atau diubah.

## Hal yang masih perlu keputusan tim

1. Minta dosen memastikan apakah roster lima orang memenuhi ketentuan, karena PDF dan LMS menyebut jumlah anggota yang berbeda.
2. Tinjau kompleksitas fitur per anggota terhadap rubrik kelas. Jumlah sembilan story per orang saja belum membuktikan syarat tersebut.
3. Sepakati data contoh dan urutan integrasi untuk dependensi antar modul sebelum story terkait mulai dikerjakan.
4. Rofii menyinkronkan PR #6 dari `main` terbaru, menambahkan Dusk M04, menjalankan pemeriksaan, lalu meminta review Yoga. Penulis menyelesaikan konflik; PM yang merge setelah acceptance dan CI terpenuhi.

Audit tidak mengubah waktu kerja aktual dan tidak menjalankan migration pada database pemilik.
