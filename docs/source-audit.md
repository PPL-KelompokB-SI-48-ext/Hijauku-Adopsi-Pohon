# Audit sumber dan status proyek

Bagian awal dokumen ini menyimpan snapshot terbaru yang diperiksa pada 11 November 2026. Jira menjadi sumber status dan jadwal, GitHub menjadi sumber branch/commit/PR/CI, sedangkan proposal dan bahan kuliah menjadi sumber rancangan. Bagian setelah kondisi terkini berisi snapshot historis 25 Oktober dan catatan sebelum PR #16 digabung pada 10 November; jangan memakai status lama di sana untuk keputusan baru.

## Kondisi terkini, diperiksa 11 November 2026

Jira dan GitHub diperiksa pada 11 November 2026. `main` tetap berada pada merge commit `bee4a0d2dc3033553403ba88f3e086b5290050a1` setelah PR #19. GitHub menunjukkan 0 PR terbuka; PR #12, #14, dan #15 ditutup tanpa merge. Jira memiliki 5 epic dan tetap 45 story: 7 Done, 10 In Progress, dan 28 To Do. HAP-1 Done; HAP-2 sampai HAP-5 In Progress. Pembagian 9 story per anggota tetap.

| Pemilik / modul | Status Jira | GitHub saat audit |
| --- | --- | --- |
| Yoga / M01-M02 | HAP-7/8/9 Done; HAP-10/11/12 In Progress, dimajukan ke Sprint 1. M03 HAP-13/14/15 tetap Sprint 2. | Perubahan HJK-001/002/003 dan shell Filament sudah ada di `main`. HJK-004/005/006 sudah diimplementasikan dan di-push pada branch personal `yoga` lewat commit `9e7a695` pada 11 November; belum ada PR untuk M02. |
| Rofii / M04-M05 | HAP-16/17/18 In Progress; HAP-19/20/21 To Do, dimajukan ke Sprint 1. M06 HAP-22/23/24 tetap Sprint 2. | PR #12 ditutup tanpa merge. Branch `Rofii` 5 commit di depan dan 16 di belakang `main`. Review terakhir meminta perbaikan policy dan tes yang gagal; pemilik perlu memeriksa ulang pada branch terbaru sebelum membuka PR pengganti. |
| Bitari / M07-M08 | HAP-25 Done; HAP-26/27 In Progress; HAP-28/29/30 To Do, dimajukan ke Sprint 1. M09 HAP-31/32/33 tetap Sprint 2. | PR #15 ditutup tanpa merge. Branch `bitaridamar` 2 commit di depan dan 22 di belakang `main`. PR belum memiliki review; checklist acceptance pada PR lama belum menjadi bukti selesai. |
| Arif / M10-M11 | HAP-34/35 In Progress; HAP-36 To Do; HAP-37/38/39 To Do, dimajukan ke Sprint 1. M12 HAP-40/41/42 tetap Sprint 2. | PR #14 ditutup tanpa merge. Branch `arif` 6 commit di depan dan 16 di belakang `main`. Review terakhir meminta tes, batas query milik warga, dan pilihan pohon tersedia; pemilik perlu memeriksa ulang pada branch terbaru. |
| Wira / M13-M14 | HAP-43/44/45 Done; HAP-46/47/48 To Do, dimajukan ke Sprint 1. M15 HAP-49/50/51 tetap Sprint 2. | PR #13 dan #16 sudah merged. Pertahankan branch personal `ayuwira`; periksa apakah ada commit Sprint 1 yang belum masuk `main` sebelum penutupan. |

GitHub saat ini menunjukkan 0 PR terbuka. PR #12, #14, dan #15 tidak siap digabung: ketiganya sudah ditutup tanpa merge, branch masih tertinggal dari `main`, dan hasil CI lama tidak membuktikan kondisi setelah perubahan terbaru. Pemilik branch mempertahankan commit unik, sinkronkan `main`, selesaikan konflik, perbaiki catatan review yang masih berlaku, lalu buka PR baru. Jangan hapus branch teman.

Status HAP-16/17, HAP-26/27, dan HAP-34/35 tetap In Progress sampai acceptance, tes, CI, review, dan penerimaan PM dapat dibuktikan sesuai aturan issue. Due date HAP-16/HAP-34 (1 November) serta HAP-17/HAP-26/HAP-35 (8 November) sudah lewat. Tanggal lama dipertahankan sebagai riwayat; pemilik dan PM perlu menetapkan replan. Jangan membuat actual time atau work log dari tanggal target.

HAP-25 dan HAP-43 sudah Done tetapi masih menyimpan remaining estimate 4 jam dan 5 jam tanpa work log. Pemilik perlu meninjau sisa estimasi. Work log hanya dibuat oleh pelaksana untuk waktu nyata.

Deskripsi semua 45 story Sprint 1 dan Sprint 2 sudah memuat pedoman bersama untuk frame Stitch, tema dan komponen Filament reusable, database, batas API, tes, branch personal, review, CI, dan status Done. Story HAP-10/11/12 sudah berstatus In Progress. Pemetaan branch yang dipakai PM: Yoga `yoga`, Bitari `bitaridamar`, Arif `arif`, Wira `ayuwira`, Rofii `Rofii`.

Setelah implementasi M02 di-push, deskripsi HAP-10/11/12 diperbarui agar tidak lagi menyebut Resource lokasi belum tersedia. Instruksi itu kini meminta anggota melanjutkan dari implementasi `SiteResource`, pola form/tabel, Policy, Actions, dan tes yang ada pada branch `yoga`, lalu menyinkronkan dengan `main` pada waktu sesuai alur sprint. Ketiga story tetap In Progress sampai PR, CI, integrasi, dan penerimaan PM selesai.

Pemeriksaan lokal untuk commit `9e7a695` lulus: 86 tes Feature (463 assertion), 11 tes Unit (11 assertion), 7 tes Laravel Dusk (42 assertion), Pint untuk file terkait, dan `npm run build`. Pemeriksaan browser lokal mencakup tambah, ubah, cari, filter, dan hapus lokasi serta tampilan ponsel tanpa overflow halaman. Hasil ini belum menggantikan CI GitHub; CI integrasi berjalan setelah PR Sprint 1 dibuat.

Jadwal Sprint 2 tetap mengikuti kalender kuliah, 23 November-27 Desember. Setelah 15 story dipindahkan ke Sprint 1, Sprint 2 berisi 15 story. Targetnya dua story per orang pada minggu 11 dan satu story per orang pada minggu 12. Minggu 13 untuk integrasi PR melalui HAP-67/HAP-68; minggu 14 untuk UAT, pemeriksaan deploy, dan dokumentasi rilis melalui HAP-69; minggu 15 untuk temuan, laporan akhir, dan presentasi melalui HAP-73.

Sprint 1 memuat 30 story dan enam story per anggota dari dua modul utuh. HAP-62 sampai HAP-66 mencatat PR personal tiap anggota siap ditinjau; HAP-71/HAP-72 menangani integrasi dan penutupan sprint dalam dua batch pada minggu 10. HAP-70 dihapus karena langkah kesiapan sudah disatukan ke instruksi HAP-71/HAP-72. Sprint 2 memuat 15 story dan tiga story per anggota; dua story dijadwalkan minggu 11 dan satu minggu 12. HAP-67/HAP-68 untuk integrasi minggu 13, HAP-69 untuk UAT/deploy minggu 14, dan HAP-73 untuk temuan/laporan/presentasi minggu 15. Issue link Jira **blocks**: HAP-62/63/64 sebelum HAP-71; HAP-65/66 dan HAP-71 sebelum HAP-72; HAP-72 sebelum HAP-67; HAP-67 sebelum HAP-68, HAP-68 sebelum HAP-69, dan HAP-69 sebelum HAP-73. Story count tetap 45; task koordinasi ini bukan PBI tambahan.

Sprint 0 yang benar adalah Sprint 0 - Persiapan, id 6, dan tetap Closed dengan HAP-61 di dalamnya. Sprint 0 duplikat id 9 yang berstatus Future dan hanya berisi HAP-61 telah dihapus.

Shell tiga panel Filament menggunakan tema bersama yang mengikuti font, palet, sidebar, topbar, dan area konten Stitch. Ini belum berarti semua halaman bisnis sudah 1:1. Untuk frame yang dipetakan, target visual 1:1 dinilai pada layar yang memang ada di frame; form/detail tanpa frame memakai pola Filament yang sama dan gap dicatat. Aksi atau data baru tetap memerlukan acceptance Jira.

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

PR #17 menggabungkan HAP-8/HAP-9 ke `main` pada 10 November dengan merge commit `a41c38a`. CI run `42774855543` lulus, termasuk tes Unit, Feature, Laravel Dusk, build frontend, serta pemeriksaan migrasi SQLite dan MySQL. Pada audit awal 11 November HAP-10 sampai HAP-15 berstatus To Do; rencana sprint kemudian diperbarui pada 11 November. Kini HAP-10 jatuh tempo 15 November; HAP-11/12 jatuh tempo 22 November; HAP-13/14 jatuh tempo 29 November; dan HAP-15 jatuh tempo 6 Desember 2026. Sprint 1 tetap berjalan sampai 22 November; Sprint 2 dimulai 23 November.

## Riwayat GitHub dan branch sampai 25 Oktober 2026

Rincian di bawah adalah riwayat pada 25 Oktober, bukan status PR terbaru. Untuk kondisi setelah PR #19 dan status PR #12/#14/#15, gunakan bagian **Kondisi terkini, diperiksa 11 November 2026** di awal dokumen.

PR #7 digabung pada 25 Oktober 2026 dengan merge commit `d59db13`. PR #9 digabung pada tanggal yang sama dengan merge commit `55b4724`.

- PR #1 menambahkan starter Sprint 0 dan PR #2 menambahkan alur HJK-001. Keduanya tetap merged di `main`.
- PR #4 menambah Dusk untuk pembuatan program, memperketat validasi tanggal, dan menyegarkan dokumentasi. PR ini merged pada 25 Oktober 2026 setelah CI lulus.
- PR #7 memperjelas proposal dan menyelaraskan catatan status/timeline. PR dokumentasi ini merged pada 25 Oktober 2026 di `d59db13`; CI run `40190337974` lulus.
- PR #9 menambahkan sumber serta hasil render 49 diagram rancangan, SQL schema-only, gambar skema dengan garis FK ke PK/UK, dan bab proposal yang sudah disegarkan. Merge commit `55b4724` masuk pada 25 Oktober 2026 setelah CI run `40194679835` lulus pada head `7de32cf`.
- PR #5 yang berisi revert PR #1 dan #2 ditutup tanpa merge. Branch revert tidak digunakan untuk pengembangan.
- PR #3 Rofii ditutup dengan perubahan yang diminta. PR pengganti #6 dibuka pada branch personal `rofii` dengan commit `22737f8`.
- Pada snapshot 25 Oktober, PR #6 CI-nya lulus pada run `40184824535`, tetapi branch dibuat dari `d683407`, sebelum PR #4, #7, dan #9 masuk. CI menjalankan Dusk halaman publik, bukan alur M04. PR #6 kemudian digantikan oleh PR #12; lihat kondisi terkini di awal dokumen sebelum mengambil tindakan.
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
4. Pemilik branch Rofii, Arif, dan Bitari mempertahankan commit unik dari PR #12/#14/#15 yang ditutup tanpa merge, menyinkronkan `main`, menyelesaikan konflik, memperbaiki temuan review yang masih berlaku, lalu membuka PR pengganti. Setiap PR perlu tes dan CI baru pada head terbaru. PM menerima setelah acceptance terpenuhi dan hanya PM yang merge.

Audit tidak mengubah waktu kerja aktual dan tidak menjalankan migration pada database pemilik.
