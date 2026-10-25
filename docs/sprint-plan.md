# Catatan Sprint 0 dan jadwal development

Dokumen ini merangkum hasil Sprint 0 dan jadwal pengembangan. Jira tetap menjadi sumber status dan tanggal issue.

## Sprint 0 - persiapan

- Epic HAP-1 - Fondasi & Infrastruktur: Done setelah PM memeriksa dan menerima kriteria fondasi.
- Task HAP-61 - SETUP-001: Done, assignee Yoga.
- Sprint 0 berlangsung pada minggu 5, 12-18 Oktober 2026, dan sudah ditutup.
- PR #1 berisi starter Sprint 0. PR #4 menambahkan tes Dusk dan melengkapi dokumentasi fondasi. Keduanya sudah merged ke `main`.
- PR #5 yang membalik PR #1 dan #2 ditutup tanpa merge. `main` tetap memiliki starter dan alur HJK-001.

## Hasil HAP-61

1. Repo memiliki lima anggota aktif: Yoga, Bitari, Arif, Wira, dan Rofii. Dinda tidak termasuk.
2. Backlog aktif Jira memiliki 5 epic dan 45 story HJK-001 sampai HJK-045 pada M01 sampai M15. HAP-6 sudah dihapus; M16 sampai M18 tidak punya story aktif.
3. Sprint 0 ditutup; Sprint 1 aktif pada minggu 6-10; Sprint 2 masih future pada minggu 11-15.
4. Dokumen arsitektur, kebutuhan, database, keputusan, audit sumber, kontribusi, dan setup tersedia di repo.
5. Starter Laravel 13 + Filament 5 memiliki `.env.example`, migration, seeder demo, tiga panel role, Policy dasar, dan panduan setup.
6. CI memeriksa dependency, migration/seeder SQLite dan MySQL, route, tes PHP, build aset, serta tes Dusk.
7. Panduan tim menjelaskan satu branch per anggota, commit dengan key HAP, PR, konflik, review, merge oleh PM, dan pencatatan work log yang jujur.

HAP-61 mempertahankan Original estimate 18 jam dan remaining estimate 0 jam. Tidak ada work log aktual yang dibuat. HAP-1 ditutup setelah pemeriksaan fondasi, sedangkan pertanyaan dosen tentang jumlah anggota tetap menjadi tindak lanjut tim.

## Sprint 1 dan Sprint 2

- Kalender formal mengikuti materi kuliah: Sprint 1 minggu 6-10, 19 Oktober-22 November 2026; Sprint 2 minggu 11-15, 23 November-27 Desember 2026.
- Rencana awal memakai minggu 6, 19-25 Oktober, untuk backlog dan refinement, lalu mulai coding pada minggu 7, 26 Oktober.
- Alur HJK-001 masuk ke `main` lewat PR #2 pada 22 Oktober. PR #4 menambahkan tes Dusk dan perbaikan terkait; setelah CI lulus, PM menerima HAP-7 pada 25 Oktober. HAP-16 dan HAP-25 juga sudah In Progress di akhir minggu 6, sebelum tanggal mulai rencana minggu 7. Pemilik perlu mencocokkan tanggal mulai Jira dengan pekerjaan nyata; kalender sprint tetap.
- Setiap anggota memiliki tiga story dari satu modul pada Sprint 1, lalu enam story dari dua modul utuh pada Sprint 2. Detail tanggal ada di [jadwal mingguan](task-schedule.md).
- Pemilik modul tetap perlu menilai kompleksitas fitur dan dependensi saat refinement. Jumlah PBI saja belum membuktikan pemenuhan rubrik kelas.

## Pemeriksaan yang sudah tersedia

- [x] Roster lima orang dan 45 story aktif dicocokkan dengan Jira.
- [x] Laravel 13 + Filament 5 serta tiga panel role disiapkan.
- [x] Policy dasar, migration, seeder demo, `.env.example`, README, dan panduan kontribusi tersedia.
- [x] CI memeriksa SQLite, MySQL, route panel, tes PHP, build aset, dan Dusk.
- [x] Dusk menguji login manager, pengisian form HJK-001, kalender Filament, penyimpanan program Draf, dan hasil pada daftar.
- [x] HAP-61 dan HAP-1 diterima; HAP-7 diterima setelah tes, review PM, dan merge PR #4.
- [ ] Dosen mengonfirmasi bahwa roster lima orang memenuhi ketentuan kelas.
- [ ] Pemilik modul memastikan pemetaan fitur memenuhi tingkat kompleksitas yang diminta.
- [ ] PR #6 Rofii disinkronkan ke `main` terbaru dan mendapat tes Dusk untuk alur M04 sebelum review/merge.

CI PR #4 pada head `7a5efae` lulus di [run 40181326721](https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon/actions/runs/40181326721): 11 tes Unit, 20 tes Feature dengan 99 assertions, 2 tes Dusk dengan 9 assertions, migration/seeder SQLite dan MySQL, route panel, serta build Vite.

HAP-7 mempertahankan Original estimate 4 jam dan remaining 0 jam. HAP-61 mempertahankan Original estimate 18 jam dan remaining 0 jam. Keduanya tidak memiliki work log aktual dari pembaruan ini. Jangan mengubah tanggal rencana menjadi jam kerja.

HAP-16 masih In Progress dengan Original estimate 3 jam dan remaining 3 jam. PR #6 Rofii sudah punya CI yang lulus untuk branch lama, tetapi belum ada Dusk M04 dan branch belum memakai `main` terbaru. HAP-25 milik Bitari juga In Progress. Epic HAP-2 dan HAP-3 tetap In Progress sampai story aktifnya diterima.

Jangan membaca atau mengganti `.env` pemilik dan jangan menjalankan migration ke database lokal pemilik. Dusk CI memakai database SQLite sementara yang terisolasi.
