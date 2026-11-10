# Audit sumber dan status proyek

Bagian awal dokumen ini menyimpan snapshot historis 25 Oktober 2026. Jira menjadi sumber status dan jadwal, GitHub menjadi sumber branch/commit/PR/CI, sedangkan proposal dan bahan kuliah menjadi sumber rancangan. Gunakan bagian **Kondisi terkini 10 November** di bawah ini untuk status terbaru; jangan memakai status pada snapshot historis untuk keputusan baru.

## Kondisi terkini 10 November 2026

Audit langsung Jira dan GitHub selesai pada 10 November 2026. `main` berada pada merge commit `8930f589a1cd31b8bbe8621b74fa95e4af0f0e36` setelah PR #16 diterima. Story Jira aktif berjumlah 45: 7 Done, 7 In Progress, dan 31 To Do. HAP-1 berstatus Done; epic HAP-2 sampai HAP-5 berstatus In Progress.

| Pemilik / modul | Status Jira yang benar | GitHub dan langkah berikutnya |
| --- | --- | --- |
| Yoga / M01 | HAP-7, HAP-8, HAP-9 Done. HAP-10 sampai HAP-15 To Do. | PR #17 dan PR dokumentasi #18 sudah merged. HJK-001 sampai HJK-003 ada di `main`. |
| Rofii / M04 | HAP-16, HAP-17, HAP-18 In Progress. | PR #12 terbuka di branch `Rofii`. Branch 7 di belakang/5 di depan `main`; CI run `41434932370` adalah run lama. Judul/body hanya menyebut HAP-16 meski diff juga memuat alur HAP-17. HAP-18 belum tampak sebagai bagian PR. Sinkronkan branch, cocokkan scope, jalankan CI baru, lalu minta review ulang. |
| Bitari / M07 | HAP-25 Done; HAP-26 dan HAP-27 In Progress. | PR #15 terbuka di `bitaridamar`, 13 di belakang/2 di depan `main`. Body hanya menyebut HAP-26, tetapi diff juga memuat tutup/batal HAP-27. CI run `42823882248` berjalan sebelum merge PR #16. Sinkronkan, cocokkan PR/Jira, lalu jalankan CI baru. |
| Arif / M10 | HAP-34 dan HAP-35 In Progress; HAP-36 To Do. | PR #14 terbuka di `arif`, 7 di belakang/6 di depan `main`. Body hanya menyebut HAP-34 sedangkan diff juga memuat keputusan pengelola HAP-35. CI run `41592514567` adalah run lama. Sinkronkan branch, cocokkan scope, jalankan CI baru, lalu minta review ulang. |
| Wira / M13 | HAP-43, HAP-44, HAP-45 Done. HAP-46 sampai HAP-51 To Do. | PR #13 dan #16 sudah merged. PR #16 berisi HAP-44/HAP-45 dan CI run `42826130678` lulus. Branch personal `ayuwira` tinggal disinkronkan ke `main` terbaru. |

Status HAP-16/17, HAP-26/27, dan HAP-34/35 dikembalikan ke In Progress saat audit karena acceptance dan tes per story belum bisa diverifikasi pada head branch terbaru. Status tersebut bukan karena PR belum merged semata. Epic HAP-4 dan HAP-5 juga dikoreksi ke In Progress agar sesuai dengan story yang berjalan. Dalam alur tim, tiap story bisa Done setelah acceptance dan tes relevan terpenuhi di branch personal yang di-push; PR per anggota baru ditinjau dan digabung pada akhir sprint. Pemilik tetap perlu memperbarui scope PR, menyinkronkan `main`, dan mendapat CI baru sebelum integrasi.

Due date HAP-16 dan HAP-34 (1 November), serta HAP-17, HAP-26, dan HAP-35 (8 November) sudah terlewati pada saat audit. Tanggal lama dipertahankan sebagai riwayat. Pemilik perlu menyepakati replan yang realistis dan memperbarui remaining estimate sesuai sisa kerja. Jangan mengarang actual time atau work log.

HAP-25 dan HAP-43 sudah merged/Done, tetapi Jira masih mencatat remaining estimate 4 jam dan 5 jam, serta tidak ada work log. Angka sisa ini perlu dikonfirmasi pemilik; audit tidak mengubahnya dan tidak membuat work log.

Branch personal yang sekarang terlihat: Yoga `yoga`, Bitari `bitaridamar`, Arif `arif`, Wira `ayuwira`, Rofii `Rofii`. Variasi nama/huruf besar diterima PM. Jangan mengganti nama atau menghapus branch aktif. PR #12, #14, dan #15 belum siap untuk integrasi akhir sprint karena tertinggal dari `main`, scope PR belum sama dengan perubahan yang dibawa, dan CI terakhirnya bukan untuk head setelah merge PR #16.

Pada halaman M01, implementasi tidak 1:1 dengan frame Stitch: panel masih memakai shell Filament standar; ukuran sidebar/header, lebar konten, filter, data contoh, dan kerapatan tabel berbeda. Aksi ubah/hapus pada mockup tidak ada dalam acceptance HJK-001 sampai HJK-003. README dan `docs/ui-design.md` menyebutkan gap ini agar tim tidak mengklaim kesesuaian visual yang belum ada.

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

## Snapshot historis 10 November sebelum PR #16 merged

Jira diperiksa ulang setelah pekerjaan M01 Yoga selesai. HAP-1, HAP-61, HAP-7, HAP-8, dan HAP-9 berstatus Done. HAP-8/HJK-002 dan HAP-9/HJK-003 selesai pada 10 November setelah target keduanya direvisi ke 9-15 November. Tanggal awal yang terlewati tetap tercatat sebagai riwayat; perubahan jadwal bukan catatan waktu kerja. Kedua issue tidak memiliki work log dan remaining estimate menjadi 0 jam.

PR #17 menggabungkan HAP-8/HAP-9 ke `main` pada 10 November dengan merge commit `a41c38a`. CI run `42774855543` lulus, termasuk tes Unit, Feature, Laravel Dusk, build frontend, serta pemeriksaan migrasi SQLite dan MySQL. HAP-10 sampai HAP-15 masih To Do di Jira. Due date berikutnya untuk Yoga adalah 29 November, 6 Desember, 13 Desember, 13 Desember, 20 Desember, dan 27 Desember 2026. Sprint 1 tetap berjalan sampai 22 November; Sprint 2 dimulai 23 November.

## GitHub dan branch

PR #7 digabung pada 25 Oktober 2026 dengan merge commit `d59db13`. PR #9 digabung pada tanggal yang sama dengan merge commit `55b4724`.

- PR #1 menambahkan starter Sprint 0 dan PR #2 menambahkan alur HJK-001. Keduanya tetap merged di `main`.
- PR #4 menambah Dusk untuk pembuatan program, memperketat validasi tanggal, dan menyegarkan dokumentasi. PR ini merged pada 25 Oktober 2026 setelah CI lulus.
- PR #7 memperjelas proposal dan menyelaraskan catatan status/timeline. PR dokumentasi ini merged pada 25 Oktober 2026 di `d59db13`; CI run `40190337974` lulus.
- PR #9 menambahkan sumber serta hasil render 49 diagram rancangan, SQL schema-only, gambar skema dengan garis FK ke PK/UK, dan bab proposal yang sudah disegarkan. Merge commit `55b4724` masuk pada 25 Oktober 2026 setelah CI run `40194679835` lulus pada head `7de32cf`.
- PR #5 yang berisi revert PR #1 dan #2 ditutup tanpa merge. Branch revert tidak digunakan untuk pengembangan.
- PR #3 Rofii ditutup dengan perubahan yang diminta. PR pengganti #6 dibuka pada branch personal `rofii` dengan commit `22737f8`.
- PR #6 CI-nya lulus pada run `40184824535`, tetapi branch tersebut dibuat dari `d683407`, sebelum PR #4, #7, dan #9 masuk. PR ini belum mendapat approval dan berstatus changes requested. CI menjalankan Dusk halaman publik, bukan alur M04. Pemilik branch perlu mengambil `origin/main` terbaru dan menambahkan Laravel Dusk untuk alur M04 sebelum meminta review lagi. Langkahnya ada di [panduan migrasi PR Rofii](rofii-pr-update.md).
- Branch lama `feature/HAP-16-jenis-pohon` masih dipertahankan sampai pemilik memastikan PR #6 aman dan tidak ada perubahan yang hilang. Jangan menghapus atau mengganti branch anggota saat PR pengganti belum diterima.
- Branch personal tetap `yoga`, `bitari`, `arif`, `wira`, dan `rofii`. Commit dan PR tetap mencantumkan key HAP kapital agar bisa ditautkan ke Jira.

## Pengujian dan kondisi implementasi

Stack aplikasi adalah Laravel 13, Filament 5, Blade, Vite, Eloquent, dan MySQL 8+. Panel manager `/app`, field staff `/petugas`, dan citizen `/warga` berbagi satu aplikasi. Policy dasar dan autentikasi sudah ada; fitur bisnis selain yang tercatat pada issue yang selesai tetap dikerjakan melalui PBI masing-masing.

CI run [40181326721](https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon/actions/runs/40181326721) lulus pada head PR #4 `7a5efae`. Hasilnya 11 tes Unit, 20 tes Feature dengan 99 assertions, dan 2 tes Dusk dengan 9 assertions. CI juga memeriksa Composer, migration/seeder SQLite dan MySQL, route panel, serta build Vite. Tes Dusk HJK-001 login sebagai manager, mengisi form Filament, memilih tanggal, menyimpan program Draf, dan memeriksa hasil pada daftar.

CI dokumentasi PR #7 juga lulus di [run 40190337974](https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon/actions/runs/40190337974). Run ini tidak menambah atau mengubah tes aplikasi.

CI PR #6 Rofii juga lulus pada head-nya, tetapi hasil itu tidak membuktikan alur jenis pohon lewat browser. PR tersebut berisi tes Feature, belum Dusk untuk M04, dan perlu diuji lagi setelah branch disinkronkan ke `main` terbaru. Tes aplikasi lokal tidak dijalankan pada database pemilik. `.env` dan database MySQL lokal tidak dibaca atau diubah.

## Hal yang masih perlu keputusan tim

1. Minta dosen memastikan apakah roster lima orang memenuhi ketentuan, karena PDF dan LMS menyebut jumlah anggota yang berbeda.
2. Tinjau kompleksitas fitur per anggota terhadap rubrik kelas. Jumlah sembilan story per orang saja belum membuktikan syarat tersebut.
3. Sepakati data contoh dan urutan integrasi untuk dependensi antar modul sebelum story terkait mulai dikerjakan.
4. Rofii menyinkronkan PR #6 dari `main` terbaru, menambahkan Dusk M04, menjalankan pemeriksaan, lalu meminta review Yoga. Penulis menyelesaikan konflik; PM yang merge setelah acceptance dan CI terpenuhi.

Audit tidak mengubah waktu kerja aktual dan tidak menjalankan migration pada database pemilik.
