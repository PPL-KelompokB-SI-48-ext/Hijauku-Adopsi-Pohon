# Baseline estimasi pekerjaan

Dokumen ini mencatat proposal estimasi awal yang diisikan ke field **Original estimate** Jira pada 22 Oktober 2026. Angka ini dipakai untuk refinement dan perencanaan. Anggota pemilik modul perlu meninjau setiap angka sebelum mulai mengerjakan story. Estimasi bukan janji durasi presisi dan bukan waktu aktual.

Jira tetap menjadi sumber status dan field kerja harian. Jika pemilik mengubah Original estimate atau sprint setelah refinement, perbarui tabel ini pada PR yang sama. Waktu aktual hanya dicatat oleh orang yang benar-benar mengerjakan melalui work log Jira. Pada audit ini, 45 story dan HAP-61 tidak memiliki work log aktual yang dibuat oleh pembaruan dokumentasi.

## Estimasi per story

Angka dalam kolom estimasi mengikuti urutan HJK pada kolom key. Seluruh story HAP-7 sampai HAP-51 sudah memiliki parent epic sesuai modul.

| Modul | Epic | Pemilik | Sprint 1 | Sprint 2 | Total modul |
| --- | --- | --- | --- | --- | ---: |
| M01 Program penghijauan | HAP-2 | Yoga | HAP-7 / HJK-001 (4h), HAP-8 / HJK-002 (4h), HAP-9 / HJK-003 (4h) | - | 12h |
| M02 Lokasi tanam | HAP-2 | Yoga | - | HAP-10 / HJK-004 (4h), HAP-11 / HJK-005 (3h), HAP-12 / HJK-006 (5h) | 12h |
| M03 Petak tanam | HAP-2 | Yoga | - | HAP-13 / HJK-007 (4h), HAP-14 / HJK-008 (6h), HAP-15 / HJK-009 (4h) | 14h |
| M04 Jenis pohon | HAP-2 | Rofii | HAP-16 / HJK-010 (3h), HAP-17 / HJK-011 (4h), HAP-18 / HJK-012 (4h) | - | 11h |
| M05 Kelompok penanaman | HAP-2 | Rofii | - | HAP-19 / HJK-013 (4h), HAP-20 / HJK-014 (5h), HAP-21 / HJK-015 (5h) | 14h |
| M06 Inventaris pohon | HAP-2 | Rofii | - | HAP-22 / HJK-016 (6h), HAP-23 / HJK-017 (5h), HAP-24 / HJK-018 (5h) | 16h |
| M07 Kegiatan lapangan | HAP-3 | Bitari | HAP-25 / HJK-019 (4h), HAP-26 / HJK-020 (5h), HAP-27 / HJK-021 (5h) | - | 14h |
| M08 Pendaftaran kegiatan | HAP-3 | Bitari | - | HAP-28 / HJK-022 (5h), HAP-29 / HJK-023 (4h), HAP-30 / HJK-024 (4h) | 13h |
| M09 Penugasan lapangan | HAP-3 | Bitari | - | HAP-31 / HJK-025 (5h), HAP-32 / HJK-026 (5h), HAP-33 / HJK-027 (5h) | 15h |
| M10 Permohonan adopsi | HAP-4 | Arif | HAP-34 / HJK-028 (5h), HAP-35 / HJK-029 (5h), HAP-36 / HJK-030 (4h) | - | 14h |
| M11 Kesepakatan adopsi | HAP-4 | Arif | - | HAP-37 / HJK-031 (5h), HAP-38 / HJK-032 (7h), HAP-39 / HJK-033 (6h) | 18h |
| M12 Laporan masalah pohon | HAP-4 | Arif | - | HAP-40 / HJK-034 (6h), HAP-41 / HJK-035 (4h), HAP-42 / HJK-036 (5h) | 15h |
| M13 Rencana perawatan | HAP-5 | Wira | HAP-43 / HJK-037 (5h), HAP-44 / HJK-038 (6h), HAP-45 / HJK-039 (4h) | - | 15h |
| M14 Realisasi perawatan | HAP-5 | Wira | - | HAP-46 / HJK-040 (6h), HAP-47 / HJK-041 (4h), HAP-48 / HJK-042 (4h) | 14h |
| M15 Pengamatan pertumbuhan | HAP-5 | Wira | - | HAP-49 / HJK-043 (5h), HAP-50 / HJK-044 (5h), HAP-51 / HJK-045 (4h) | 14h |
| **Total 45 story** |  | **5 anggota** | **66h / 15 story** | **145h / 30 story** | **211h** |

HAP-61 adalah task Sprint 0 di bawah HAP-1. PM menerima fondasi pada 25 Oktober 2026 setelah PR #4 lulus CI dan di-merge. Original estimate 18h tetap sebagai estimasi awal dan remaining estimate kini 0h. Tidak ada work log aktual yang dibuat. Total rencana awal backlog bersama HAP-61 tetap 229h.

## Rencana per orang dan sprint

Tabel berikut menjumlahkan estimasi per story berdasarkan penempatan sprint yang terlihat di Jira. Jumlah story bukan ukuran beban yang sama; jam adalah ringkasan proposal saat ini.

| Anggota | Sprint 1 | Sprint 2 | Total story aktif |
| --- | ---: | ---: | ---: |
| Yoga | 12h / 3 story | 26h / 6 story | 38h / 9 story |
| Rofii | 11h / 3 story | 30h / 6 story | 41h / 9 story |
| Bitari | 14h / 3 story | 28h / 6 story | 42h / 9 story |
| Arif | 14h / 3 story | 33h / 6 story | 47h / 9 story |
| Wira | 15h / 3 story | 28h / 6 story | 43h / 9 story |
| **Tim** | **66h / 15 story** | **145h / 30 story** | **211h / 45 story** |

Penempatan diperbarui pada 25 Oktober 2026 agar setiap modul tetap utuh dalam satu sprint. Rencana awal memakai minggu 6 untuk backlog dan menjadwalkan coding Sprint 1 mulai minggu 7. Alur HJK-001 masuk ke `main` melalui PR #2 pada 22 Oktober; tes Dusk dan penerimaan PM menyusul lewat PR #4 pada 25 Oktober. HAP-16 dan HAP-25 juga sudah In Progress pada akhir minggu 6, sebelum tanggal mulai rencana minggu 7. Pemilik perlu mencocokkan tanggal mulai Jira dengan waktu kerja sebenarnya. Total estimasi Sprint 2 lebih besar karena tiap orang merencanakan dua modul di sana, jadi pemilik perlu menilai kapasitas saat refinement. Sprint 1 mengikuti kalender kuliah dan aktif, Sprint 2 berlangsung pada minggu 11-15. Jangan mengisi work log dari angka estimasi.

## Cara memperbarui angka

1. Pemilik membaca acceptance criteria dan memecah pekerjaan menjadi implementasi, validasi, tes, dan dokumentasi yang memang diperlukan.
2. Pemilik membahas perubahan estimate dengan PM saat refinement. Bila masih berupa proposal, jangan menyebutnya angka yang telah disepakati.
3. Perbarui Original estimate Jira dan tabel ini bersamaan. Jika story sudah berjalan, remaining estimate menggambarkan pekerjaan yang tersisa, bukan waktu yang sudah dipakai.
4. Pelaksana mencatat work log untuk waktu aktual setelah melakukan pekerjaan. Jangan menyalin estimate ke work log atau memperkirakan jam anggota lain.
5. Setelah push, periksa panel Development pada issue. Branch/commit lokal belum tertaut ke Jira sebelum push dan sinkronisasi repository.

Lihat [alur Jira dan pencatatan waktu](jira-workflow.md) untuk format deskripsi, commit, PR, dan review.

Start date dan Due date per story mengikuti rentang mingguan di [jadwal backlog](task-schedule.md). Tanggal merupakan target perencanaan dan tidak mengubah angka Original estimate maupun work log.
