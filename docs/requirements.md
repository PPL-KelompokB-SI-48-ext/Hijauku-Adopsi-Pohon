# Kebutuhan dan backlog Hijauku

Dokumen ini memisahkan aturan kuliah, roster proyek, dan backlog Jira. Jira menjadi sumber utama status, owner, sprint, dan tanggal issue.

## Tim dan jumlah story

| Anggota | Modul | Sprint 1 | Sprint 2 | Total |
| --- | --- | ---: | ---: | ---: |
| Yoga (PM) | M01-M03 | 3 | 6 | 9 |
| Rofii | M04-M06 | 3 | 6 | 9 |
| Bitari | M07-M09 | 3 | 6 | 9 |
| Arif | M10-M12 | 3 | 6 | 9 |
| Wira | M13-M15 | 3 | 6 | 9 |
| **Total** | **M01-M15** | **15** | **30** | **45** |

Tiga story per anggota per sprint adalah hitungan backlog, bukan bukti kompleksitas. Setiap kelompok tiga story direncanakan sebagai satu modul utuh. Pemilik modul perlu memastikan bahwa fitur pada tiap sprint memenuhi kriteria kompleksitas sedang/tinggi dan dapat selesai, diuji, serta direview di sprint yang sama.

## Rumus backlog

Setiap anggota memiliki tiga modul dan setiap modul memiliki tiga story/PBI: **3 modul × 3 story = 9 story per orang**. Dengan lima anggota, backlog aktif berisi **45 story**. Satu story adalah satu PBI; satu PBI tidak terdiri dari tiga modul.

ID kebutuhan HJK-001 sampai HJK-045 dan key Jira HAP-7 sampai HAP-51 sudah dipakai. Jangan menomori ulang key. Perubahan pemilik, sprint, atau tanggal dilakukan pada issue yang sama.

## Aturan kuliah dan konfirmasi

Bahan Ketentuan Proyek PPL menyebut kelompok 6-7 orang, sementara teks LMS Sprint 00 yang diberikan PM menyebut 5-6 orang. Roster proyek berisi lima anggota: Yoga, Bitari, Arif, Wira, dan Rofii. Konfirmasi dosen masih diperlukan untuk menyelesaikan perbedaan materi tersebut.

Ketentuan proyek meminta setiap orang mengerjakan setidaknya satu fitur kompleksitas sedang/tinggi pada setiap sprint formal, dengan minimal tiga PBI yang membentuk fitur tersebut. Sprint 0 adalah persiapan, bukan sprint formal development. Rencana di dokumen ini mengelompokkan tiga story setiap modul dalam satu sprint agar batas itu terlihat. Penetapan kompleksitas perlu dikonfirmasi pemilik modul saat refinement.

## Pembagian modul per sprint

| Anggota | Sprint 1 | Sprint 2 |
| --- | --- | --- |
| Yoga | M01, 3 story | M02 dan M03, 6 story |
| Rofii | M04, 3 story | M05 dan M06, 6 story |
| Bitari | M07, 3 story | M08 dan M09, 6 story |
| Arif | M10, 3 story | M11 dan M12, 6 story |
| Wira | M13, 3 story | M14 dan M15, 6 story |

Sprint 1 mengikuti kalender minggu 6-10, 19 Okt-22 Nov 2026. Minggu 6 digunakan untuk backlog/refinement, tanpa coding. Story mulai dikerjakan pada minggu 7 dan selesai paling lambat minggu 9; minggu 10 untuk integrasi, demo, review, dan retrospektif.

Sprint 2 mengikuti minggu 11-15, 23 Nov-27 Des 2026. Issue HAP-10 sampai HAP-51 dibagi per minggu dalam [jadwal mingguan](task-schedule.md). Tanggal kerja adalah target koordinasi, bukan actual time. Original estimate tetap usulan awal sampai disepakati pemilik modul.

Pembagian modul ini menjaga tiga PBI satu modul tetap di satu sprint, tetapi beberapa fitur memakai data dari modul anggota lain. Sebelum coding, pemilik perlu menyepakati model/field yang dipakai, seed untuk tes, dan urutan integrasi. Jika dependensi membuat acceptance tidak dapat diuji dalam sprint yang direncanakan, PM memperbaiki mapping sebelum pekerjaan dimulai.

## Epic aktif dan scope

| Jira | Isi | Modul |
| --- | --- | --- |
| HAP-1 | Fondasi dan infrastruktur | Sprint 0 |
| HAP-2 | Data dasar dan inventaris | M01-M06 |
| HAP-3 | Kegiatan dan penugasan | M07-M09 |
| HAP-4 | Adopsi dan masalah pohon | M10-M12 |
| HAP-5 | Perawatan dan pengamatan | M13-M15 |

HAP-6, yang dahulu menampung M16-M18, sudah dihapus permanen atas keputusan PM. Tidak ada story aktif untuk artikel, galeri, atau usulan aksi. Model/tabel yang masih ada di starter adalah scaffold rancangan awal, bukan bagian scope aktif.

## Katalog modul

| Modul | Area | Pemilik | Story |
| --- | --- | --- | --- |
| M01 | Program penghijauan | Yoga | HJK-001-HJK-003, HAP-7-HAP-9 |
| M02 | Lokasi tanam | Yoga | HJK-004-HJK-006, HAP-10-HAP-12 |
| M03 | Petak tanam | Yoga | HJK-007-HJK-009, HAP-13-HAP-15 |
| M04 | Jenis pohon | Rofii | HJK-010-HJK-012, HAP-16-HAP-18 |
| M05 | Kelompok penanaman | Rofii | HJK-013-HJK-015, HAP-19-HAP-21 |
| M06 | Inventaris pohon | Rofii | HJK-016-HJK-018, HAP-22-HAP-24 |
| M07 | Kegiatan lapangan | Bitari | HJK-019-HJK-021, HAP-25-HAP-27 |
| M08 | Pendaftaran kegiatan | Bitari | HJK-022-HJK-024, HAP-28-HAP-30 |
| M09 | Penugasan lapangan | Bitari | HJK-025-HJK-027, HAP-31-HAP-33 |
| M10 | Permohonan adopsi | Arif | HJK-028-HJK-030, HAP-34-HAP-36 |
| M11 | Kesepakatan adopsi | Arif | HJK-031-HJK-033, HAP-37-HAP-39 |
| M12 | Laporan masalah pohon | Arif | HJK-034-HJK-036, HAP-40-HAP-42 |
| M13 | Rencana perawatan | Wira | HJK-037-HJK-039, HAP-43-HAP-45 |
| M14 | Realisasi perawatan | Wira | HJK-040-HJK-042, HAP-46-HAP-48 |
| M15 | Pengamatan pertumbuhan | Wira | HJK-043-HJK-045, HAP-49-HAP-51 |
| M16 | Artikel lingkungan | - | Di luar scope aktif |
| M17 | Galeri kegiatan | - | Di luar scope aktif |
| M18 | Usulan aksi penghijauan | - | Di luar scope aktif |

Judul, tujuan, acceptance criteria, dan status story tetap dibaca dari Jira. Perubahan requirement harus memperbarui issue dan dokumen ini bila berdampak pada scope, owner, modul, atau hitungan.

## Menjaga backlog

- Acceptance criteria harus bisa diuji dan mencakup kasus berhasil, penolakan, role/ownership, serta transisi status yang relevan.
- Jangan pecah tiga story pembentuk satu modul ke dua sprint tanpa persetujuan PM dan alasan dependensi yang tercatat.
- Jika story tidak muat dalam sprint atau bergantung pada modul lain, bicarakan kontrak data dan acceptance sebelum coding.
- Sertakan key HAP pada commit dan PR agar Jira dapat menautkan Development work. Branch personal mengikuti panduan satu branch per anggota di [CONTRIBUTING.md](../CONTRIBUTING.md).
- Jangan memasukkan M16-M18 ke sprint aktif tanpa keputusan scope baru dan issue Jira yang sesuai.
