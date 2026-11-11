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
- Sprint 1 berisi 30 story, enam per anggota dari dua modul utuh. HAP-62 sampai HAP-66 menandai satu PR personal per anggota yang siap ditinjau, due 22 November. HAP-71/HAP-72 adalah integrasi dan penutupan Sprint 1 oleh PM, juga due 22 November. HAP-70 dihapus karena pemeriksaan kesiapan sudah menjadi bagian task integrasi.
- Sprint 2 berisi 15 story, tiga per anggota dari satu modul. Setiap anggota menargetkan dua story di minggu 11 (23-29 November) dan satu story di minggu 12 (30 November-6 Desember). Minggu 13 untuk integrasi PR, minggu 14 untuk UAT, pemeriksaan deploy, dan dokumen rilis, lalu minggu 15 untuk perbaikan temuan, laporan akhir, dan presentasi.
- Perbedaan nama mencerminkan cakupan: HAP-71/HAP-72 menutup Sprint 1 karena pekerjaan coding, persiapan PR, integrasi, demo, dan retrospektif selesai pada minggu 10. Sprint 2 memisahkan integrasi teknis (HAP-67/HAP-68, minggu 13), verifikasi sistem/UAT dan kesiapan rilis (HAP-69, minggu 14), serta temuan dan laporan/presentasi akhir (HAP-73, minggu 15). Jadi HAP-67/HAP-68 bukan penutupan Sprint 2 sendirian.
- Tujuan sprint di Jira sudah disegarkan agar perbedaan fase terlihat: Sprint 1 mengakhiri 30 story dan dua batch merge pada minggu 10; Sprint 2 menyelesaikan 15 story pada minggu 11-12, integrasi minggu 13, UAT/deploy minggu 14, dan ruang perbaikan/laporan minggu 15.
- HAP-67 dan HAP-68 adalah task integrasi Sprint 2 pada minggu 13. HAP-69 mencakup UAT, pemeriksaan deploy, dan dokumentasi rilis pada minggu 14. HAP-73 memakai minggu 15 untuk temuan, regresi, laporan, dan persiapan presentasi.
- Urutan integrasi dicatat dengan issue link Jira **blocks**: HAP-62/63/64 memblokir HAP-71; HAP-65/66 dan HAP-71 memblokir HAP-72; HAP-72 memblokir HAP-67; HAP-67 memblokir HAP-68, HAP-68 memblokir HAP-69, lalu HAP-69 memblokir HAP-73.
- Alur HJK-001 masuk ke `main` lewat PR #2 pada 22 Oktober. PR #4 menambahkan tes Dusk dan perbaikan terkait; setelah CI lulus, PM menerima HAP-7 pada 25 Oktober. HAP-16 dan HAP-25 juga sudah In Progress di akhir minggu 6, sebelum tanggal mulai rencana minggu 7. Pemilik perlu mencocokkan tanggal mulai Jira dengan pekerjaan nyata; kalender sprint tetap.
- Setiap anggota memiliki enam story dari dua modul utuh pada Sprint 1, lalu tiga story dari satu modul pada Sprint 2. Detail tanggal ada di [jadwal mingguan](task-schedule.md).
- Pemilik modul tetap perlu menilai kompleksitas fitur dan dependensi saat refinement. Jumlah PBI saja belum membuktikan pemenuhan rubrik kelas.

## Pemeriksaan yang sudah tersedia

- [x] Roster lima orang dan 45 story aktif dicocokkan dengan Jira.
- [x] Laravel 13 + Filament 5 serta tiga panel role disiapkan.
- [x] Policy dasar, migration, seeder demo, `.env.example`, README, dan panduan kontribusi tersedia.
- [x] CI memeriksa SQLite, MySQL, route panel, tes PHP, build aset, dan Dusk.
- [x] Dusk menguji login manager, pengisian form HJK-001, kalender Filament, penyimpanan program Draf, dan hasil pada daftar.
- [x] HAP-61 dan HAP-1 diterima. HAP-7 masuk lewat PR #2; PR #4 menambahkan tes Dusk dan perbaikan validasi, lalu PM menerima story setelah pemeriksaan terkait lulus.
- [ ] Dosen mengonfirmasi bahwa roster lima orang memenuhi ketentuan kelas.
- [ ] Pemilik modul memastikan pemetaan fitur memenuhi tingkat kompleksitas yang diminta.
- [ ] Pemilik branch Rofii, Arif, dan Bitari memulihkan commit unik dari PR #12/#14/#15 yang ditutup tanpa merge, menyinkronkan `main`, menyelesaikan konflik, menjalankan CI baru, lalu membuka PR pengganti.

CI PR #4 pada head `7a5efae` lulus di [run 40181326721](https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon/actions/runs/40181326721): 11 tes Unit, 20 tes Feature dengan 99 assertions, 2 tes Dusk dengan 9 assertions, migration/seeder SQLite dan MySQL, route panel, serta build Vite.

CI PR dokumentasi #7 juga lulus di [run 40190337974](https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon/actions/runs/40190337974). PR ini tidak mengubah kode aplikasi atau menambah tes.

HAP-7 mempertahankan Original estimate 4 jam dan remaining 0 jam. HAP-61 mempertahankan Original estimate 18 jam dan remaining 0 jam. Keduanya tidak memiliki work log aktual dari pembaruan ini. Jangan mengubah tanggal rencana menjadi jam kerja.

## Pembacaan ulang 11 November 2026 setelah PR #20

M01 (HAP-7 sampai HAP-9), M02 (HAP-10 sampai HAP-12), HJK-019/HAP-25, dan M13 (HAP-43 sampai HAP-45) berstatus Done. M04 HAP-16/17/18, M07 HAP-26/27, dan M10 HAP-34/35 berstatus In Progress pada audit. Total story di Jira: 10 Done, 7 In Progress, dan 28 To Do. HAP-16/HAP-34 due 1 November, sementara HAP-17/HAP-26/HAP-35 due 8 November; pemilik perlu menyepakati replan di Jira.

PR #20 Yoga sudah merged ke `main` dari branch personal `yoga`. Merge commit `232e6a213897f87d1e081274767efa61dff6c63c`; CI run `42930124355` lulus pada head `99176cd`. PR #12, #14, dan #15 ditutup tanpa merge; branch masing-masing masih memiliki commit unik dan perlu disinkronkan sebelum integrasi. Pemilik mempertahankan commit, menyelesaikan konflik, menanggapi temuan review yang masih berlaku, dan menjalankan CI baru. Jangan menghapus branch.

Sprint 0 yang dipakai adalah Sprint 0 - Persiapan, id 6, status Closed. Sprint 0 duplikat id 9 yang berstatus Future dan berisi HAP-61 saja sudah dihapus; HAP-61 tetap berada di Sprint 0 tertutup.

Jira masih menyimpan remaining estimate 4 jam pada HAP-25 dan 5 jam pada HAP-43 meskipun keduanya Done. Keduanya tidak memiliki work log. Pemilik perlu meninjau remaining estimate; angka aktual tetap dicatat hanya oleh pelaksana untuk waktu yang benar-benar dipakai.

Jangan membaca atau mengganti `.env` pemilik dan jangan menjalankan migration ke database lokal pemilik. Dusk CI memakai database SQLite sementara yang terisolasi.
