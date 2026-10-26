# Audit proposal dan presentasi, 25 Oktober 2026

Audit ini membandingkan proposal Google Docs, desain Canva, 25 frame Stitch, migration/model pada branch `yoga`, serta 45 story dan kalender sprint Jira. Ini adalah catatan revisi, bukan penerimaan seluruh fitur atau bukti bahwa semua ketentuan kuliah sudah terpenuhi.

## Dokumen kerja

- [Proposal yang direvisi langsung](https://docs.google.com/document/d/15zow58NyAsJvCN3hsBPSb4X-FUX8PYT_9a4qTHgzYE0/edit).
- [Presentasi 27 slide](https://www.canva.com/design/DAHY-0_AeTA/JKhLODlumP-Gxtk2QupLqA/edit).
- [Naskah video per pembicara dan PBI](https://docs.google.com/document/d/1jEmO2buNZwx3ggtPPC7UK3y4mZbeiIwfYhlz3-cCOPQ/edit), dengan salinan di [video-script.md](video-script.md).
- [Jadwal mingguan](task-schedule.md), [kebutuhan](requirements.md), [skema](database.md), dan [panduan UI](ui-design.md).

## Perbaikan isi

- Roster lima orang: Yoga, Rofii, Bitari, Arif, dan Wira. Scope aktif M01-M15 dengan HJK-001-HJK-045 / HAP-7-HAP-51. ID tetap, tidak dinomori ulang.
- Setiap anggota memiliki tiga modul dengan tiga PBI per modul. Sprint 1 berisi satu modul/tiga PBI per orang; Sprint 2 dua modul/enam PBI per orang. Satu modul tidak dipisah antarsprint.
- Sprint 0: persiapan minggu 5, 12-18 Oktober. Sprint 1: minggu 6-10, 19 Oktober-22 November; rencana awal memakai minggu 6 untuk backlog dan coding mulai minggu 7. Alur HJK-001 masuk ke `main` lewat PR #2 pada 22 Oktober; tes Dusk dan penerimaan PM menyusul lewat PR #4 pada 25 Oktober. HAP-16 dan HAP-25 juga sudah In Progress pada akhir minggu 6, sebelum tanggal mulai rencana minggu 7. Sprint 2: minggu 11-15, 23 November-27 Desember 2026. Tanggal target bukan work log aktual.
- Arsitektur memakai Laravel 13, tiga panel Filament 5, Blade/Vite, Eloquent, MySQL 8+, Policy/query ownership, aksi transaksi, dan Storage privat. Artikel, galeri, dan usulan bukan scope aktif.
- Bab Rancangan Sistem pada proposal kini menggabungkan 49 gambar: empat kelompok use case, satu detail include/extend, satu arsitektur dengan logo teknologi, 13 ERD field-level, 15 skema tabel dengan garis FK ke PK/UK, dan 15 class diagram untuk M01-M15. Skema adopsi dipecah agar relasinya mudah diikuti. DDL schema-only diimpor ke diagrams.net untuk pemeriksaan; gambar proposal dirender dari DDL yang sama. Daftar isi diperbarui setelah semua gambar dimasukkan. Sumber dan render ada di docs/diagrams/system-design/.
- Dua puluh lima gambar UI proposal diganti dengan render frame Stitch yang diberi label mockup/data contoh. Presentasi memakai 15 mockup modul; M09 memakai Tugas Saya, M10 Adopsi Saya, dan M11 Kesepakatan Saya agar aktornya jelas.
- Palet mengacu pada aset merek Stitch: #15803D, #22C55E, #A7F3D0, #1F2937; #F8FAFC dipakai sebagai latar presentasi. Form yang belum memiliki frame memakai komponen standar Filament. Ini belum berarti seluruh tampilan aplikasi identik dengan mockup.
- Aktor pendaftaran M08 dipertegas menjadi warga sesuai HAP-28/HAP-30. Pengelola menangani kehadiran HAP-29, sedangkan petugas menangani penugasan. Izin daftar manager pada M08 masih menjadi bagian implementasi HAP-29, karena Policy scaffold membatasi daftar ke warga.

## Koreksi Jira dan skema

Deskripsi issue diperbaiki pada issue yang sama; status, work log, dan riwayat commit tidak direkayasa.

| Issue | Perbaikan |
| --- | --- |
| HAP-8 | Sumber status aktivasi adalah `draft`, sesuai pencatatan program pada HAP-7. |
| HAP-12, HAP-24 | Jejak perubahan memakai `activity_logs.actor_id`, alasan, serta nilai sebelum/sesudah; tidak menganggap kolom `updated_by` tersedia. |
| HAP-23, HAP-36, HAP-38 | Ketersediaan mengikuti alokasi yang berlaku; penerimaan memakai constraint unik dan transaksi/row lock yang kompatibel dengan MySQL. |
| HAP-43, HAP-44, HAP-45 | FK rencana ke penugasan adalah `care_plans.assignment_id`. |
| HAP-44 | Prioritas masih memerlukan migration aman; kolom tersebut belum ada pada scaffold. |

Sprint 2 kini juga mempunyai start/end date resmi di Jira. Mapping dan tanggal seluruh story diperiksa terhadap [jadwal mingguan](task-schedule.md). Jangan memaknai due date sebagai bukti pekerjaan telah dilakukan.

## Rujukan ilmiah

Sitasi awal beberapa studi tertukar. Proposal kini mengaitkan kajian 56 studi pada Hilbert et al. (2019), studi Neniari pada Lumapuy et al. (2024), dan pengelolaan penyiraman bersama di Indianapolis pada Mincey dan Vogt (2014). Klaim efektivitas aplikasi, hasil survei komunitas yang belum dilakukan, dan penghitungan indikator SDG tidak dipakai.

- [Hilbert et al., 2019 - Urban Tree Mortality](https://research.fs.usda.gov/treesearch/58772).
- [Lumapuy et al., 2024 - Neniari](https://ijins.umsida.ac.id/index.php/ijins/article/view/1196).
- [Mincey dan Vogt, 2014 - Indianapolis](https://dlc.dlib.indiana.edu/dlc/items/511a740c-5b00-4db5-968c-07de0584c4ed).
- [Mosyaftiani et al., 2022 - inventaris pohon](https://doi.org/10.13057/biodiv/d230822).
- [SDG 15, United Nations](https://sdgs.un.org/goals/goal15).
- [UU 27 Tahun 2022 tentang Pelindungan Data Pribadi](https://peraturan.bpk.go.id/Details/229798/uu-no-27-tahun-2022).

Komentar lama pada proposal tetap menjadi riwayat diskusi. Beberapa tautannya tertukar terhadap kutipan yang sekarang diperbaiki; gunakan teks dan daftar pustaka revisi sebagai rujukan, bukan tautan pada komentar lama. Audit tidak mengirim balasan atau menghapus komentar anggota.

## Hal yang masih terbuka

1. Dosen mengonfirmasi roster lima orang karena PDF dan LMS memuat batas jumlah anggota yang berbeda.
2. Pemilik modul dan PM menilai kompleksitas sedang/tinggi menurut rubrik. Sembilan PBI per orang bukan bukti otomatis pemenuhan kompleksitas.
3. Pemilik modul menyepakati kontrak data dan fixture untuk dependensi dari modul Sprint 2. Jika acceptance tidak bisa diuji pada sprint yang sama, jadwal diperbaiki sebelum coding.
4. PR #4 sudah di-merge setelah CI lulus; HAP-61, HAP-1, dan HAP-7 diterima PM dan berstatus Done. PR dokumentasi #7 juga merged setelah CI lulus. PR #5 ditutup tanpa merge. PR #3 Rofii ditutup dengan perubahan yang diminta. PR pengganti #6 dibuka di branch personal `rofii`, tetapi perlu Dusk M04 dan sinkronisasi dengan `main` terbaru. HAP-16 dan HAP-25 aktif; epic HAP-2 dan HAP-3 berstatus In Progress.
5. Mockup berisi data contoh. Perilaku seperti validasi, izin record, locking, dan unggahan privat harus dibuktikan melalui implementasi serta tes, bukan tangkapan layar.
6. HAP-61 mempertahankan Original estimate 18 jam dengan remaining 0 jam. HAP-7 mempertahankan Original estimate 4 jam dengan remaining 0 jam. Tidak ada work log yang direkayasa.

Audit ini tidak membaca/menulis `.env`, menjalankan migration pada database pemilik, atau membuat work log aktual.
