# Catatan Sprint 0 dan jadwal development

Dokumen ini mencatat fondasi Sprint 0 dan perpindahan dari penyusunan backlog ke pengerjaan fitur. Jira adalah sumber status dan tanggal live.

## Sprint 0 - Persiapan

- Epic: HAP-1 - Fondasi & Infrastruktur, status Jira tetap To Do sampai PM menerima keseluruhan acceptance.
- Task: HAP-61 - SETUP-001, assignee Yoga, status Jira Done.
- Sprint 0 berlangsung pada minggu 5, 12-18 Oktober 2026, dan sudah ditutup.
- PR #1 berisi starter Sprint 0 dan tercatat merged ke `main`.
- PM meminta PR #1 dan #2 direvert dari `main`, dengan hasil kerja dipertahankan di branch personal Yoga. Sampai revert PR diterima, `main` masih memuat kedua hasil merge.

## Acceptance HAP-61

1. Repo memiliki lima anggota aktif: Yoga, Bitari, Arif, Wira, dan Rofii. Dinda dikecualikan.
2. Backlog aktif memiliki 5 epic dan 45 story HJK-001 sampai HJK-045 untuk M01 sampai M15. HAP-6 dihapus permanen; M16 sampai M18 tidak memiliki story aktif.
3. Sprint 0 ditutup, Sprint 1 aktif pada minggu 6-10, dan Sprint 2 future pada minggu 11-15.
4. Dokumen arsitektur Rev 1, kebutuhan, database, keputusan, audit sumber, kontribusi, dan setup tersedia di repo.
5. Starter Laravel 13 + Filament 5 memiliki `.env.example`, migrasi, seeder demo, tiga panel role, Policy dasar, dan panduan setup lokal.
6. CI memvalidasi dependency, migrasi/seeder SQLite dan MySQL, route, tes PHP, build aset, serta tes Dusk beranda dan pembuatan program lewat panel.
7. Panduan tim menjelaskan satu branch per anggota, commit dengan key HAP, PR, penyelesaian konflik, review anggota, merge oleh PM, dan work log yang jujur.

## Sprint 1 dan Sprint 2

- Kalender kuliah tidak berubah: Sprint 1 minggu 6-10 (19 Okt-22 Nov 2026), Sprint 2 minggu 11-15 (23 Nov-27 Des 2026).
- Minggu 6, 19-25 Oktober, dipakai menyusun dan refine backlog. Tidak ada coding yang direncanakan.
- Pengerjaan story Sprint 1 dimulai minggu 7, 26 Oktober. Tanggal story Sprint 1 disesuaikan hingga minggu 9; minggu 10 untuk integrasi, demo, review, dan retrospektif.
- Setiap anggota mendapat tiga story satu modul di Sprint 1, lalu enam story dari dua modul utuh di Sprint 2. Detail dan tanggal ada di [jadwal mingguan](task-schedule.md).
- Pemilik modul memeriksa kompleksitas fitur dan dependensi lintas anggota pada refinement. Story tidak dinyatakan siap hanya berdasarkan jumlahnya.

## Yang tersedia di starter

- [x] Roster lima orang dan 45 story aktif diverifikasi.
- [x] Laravel 13 + Filament 5 dan tiga panel role disiapkan.
- [x] Policy dasar, migration, seeder demo, `.env.example`, README, dan panduan kontribusi tersedia.
- [x] CI memiliki pemeriksaan SQLite dan MySQL, tes PHP, build aset, dan Dusk.
- [x] Tes Dusk HJK-001 berjalan pada branch personal Yoga. Run CI `40170424971` di commit `7e59f30` lulus untuk login, pengisian form, pemilihan tanggal di kalender, penyimpanan program Draf, serta pemeriksaan hasil pada daftar.
- [ ] Pemilik modul dan dosen mengonfirmasi pemetaan fitur/kompleksitas per sprint dan roster lima orang.
- [ ] PM meninjau serta menerima PR yang sesuai sebelum HAP-1 diubah dari To Do.

## Status dan aturan kerja

PR #1 dan PR #2 sama-sama tercatat merged di GitHub. Sesuai arahan PM, PR draft #5 membalik keduanya, tetapi belum mengubah `main`. Revert ini menghapus starter Laravel dan workflow CI, sehingga PR #5 belum memiliki CI check dan tidak boleh digabung sebelum urutan pemulihan kode/CI diputuskan. HAP-7 sudah dikembalikan ke To Do agar pengerjaan mengikuti jadwal minggu 7. HAP-61 masih Done sampai perubahan di `main` benar-benar diterapkan; PM perlu mengevaluasi ulang acceptance setelah revert.

Original estimate adalah rencana. Jira HAP-61 masih menampilkan remaining estimate 4 jam walaupun statusnya Done; belum ada work log aktual. Jangan mengubah nilai itu menjadi actual time. Waktu aktual hanya dicatat pelaksana setelah bekerja.

Jangan membaca atau mengganti `.env` pemilik dan jangan menjalankan migrasi ke database lokal pemilik. Dusk CI memakai database SQLite file sementara yang terisolasi.
