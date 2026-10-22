# Jadwal mingguan backlog

Dokumen ini merinci tanggal yang dipasang pada Jira untuk 45 story aktif. Jira tetap menjadi sumber tanggal dan status harian; tabel ini adalah snapshot jadwal supaya anggota mudah melihat pembagian mingguan.

## Cara membaca tanggal

- **Start date** adalah hari Senin pada minggu kerja story.
- **Due date** adalah hari Minggu pada minggu kerja story. Jira menampilkan field ini sebagai tenggat.
- Story dari anggota berbeda berjalan paralel pada minggu yang sama. Dalam minggu tertentu, seorang anggota dapat menangani sampai dua story secara paralel jika prasyaratnya sudah tersedia dan alurnya tidak saling mengunci.
- Story yang saling bergantung tetap ditempatkan di minggu berbeda. Target tanggal bukan bukti pekerjaan sudah dimulai atau selesai.
- Rentang ini adalah target perencanaan, bukan catatan durasi aktual. Pelaksana tetap mencatat jam sebenarnya melalui work log Jira setelah bekerja.
- Pemilik meninjau tanggal dan estimasi saat refinement. Jika tanggal berubah, ubah Jira dan snapshot ini bersama-sama melalui PR.

## Sprint 0

| Issue | Pemilik | Start date | Due date | Catatan |
| --- | --- | --- | --- | --- |
| HAP-1 | Yoga (PM) | Senin, 12 Okt 2026 | Minggu, 18 Okt 2026 | Rentang epic fondasi Sprint 0 |
| HAP-61 | Yoga (PM) | Senin, 12 Okt 2026 | Minggu, 18 Okt 2026 | Masih In Progress pada 22 Okt, sehingga tenggat tampil lewat. Estimasi tersisa 4 jam bukan work log. PM memperbarui tenggat bila target Sprint 0 resmi diperpanjang. |

## Sprint 1 - Pekan 6-10

Sprint 1 di Jira berlangsung 19 Okt sampai 22 Nov 2026. Tanggal di kolom rentang adalah nilai Start date dan Due date yang sudah disetel pada setiap issue.

Jam pada kolom Total adalah penjumlahan Original estimate usulan per story, bukan waktu aktual. Work log tetap dicatat masing-masing pelaksana setelah bekerja.

| Pekan | Rentang kerja | Yoga | Rofii | Bitari | Arif | Wira | Total |
| --- | --- | --- | --- | --- | --- | --- | ---: |
| 6 | 19-25 Okt 2026 | HAP-7 / HJK-001 | HAP-16 / HJK-010 | HAP-25 / HJK-019 | HAP-34 / HJK-028 | HAP-43 / HJK-037 | 5 story / 21h |
| 7 | 26 Okt-1 Nov 2026 | HAP-10 / HJK-004 | HAP-17 / HJK-011, HAP-19 / HJK-013 | HAP-26 / HJK-020 | HAP-35 / HJK-029 | HAP-46 / HJK-040, HAP-49 / HJK-043 | 7 story / 33h |
| 8 | 2-8 Nov 2026 | HAP-11 / HJK-005, HAP-13 / HJK-007 | HAP-20 / HJK-014 | HAP-28 / HJK-022, HAP-31 / HJK-025 | HAP-37 / HJK-031 | HAP-50 / HJK-044 | 7 story / 32h |
| 9 | 9-15 Nov 2026 | HAP-14 / HJK-008 | HAP-22 / HJK-016 | - | HAP-38 / HJK-032 | - | 3 story / 19h |
| 10 | 16-22 Nov 2026 | - | - | - | - | - | Buffer integrasi dan review |
| **Total** |  | **5 story / 21h** | **5 story / 22h** | **4 story / 19h** | **4 story / 22h** | **4 story / 21h** | **22 story / 105h** |

## Sprint 2 - Pekan 11-15

Sprint 2 di Jira berlangsung 23 Nov sampai 27 Des 2026.

| Pekan | Rentang kerja | Yoga | Rofii | Bitari | Arif | Wira | Total |
| --- | --- | --- | --- | --- | --- | --- | ---: |
| 11 | 23-29 Nov 2026 | HAP-8 / HJK-002 | HAP-18 / HJK-012 | HAP-27 / HJK-021 | HAP-36 / HJK-030 | HAP-44 / HJK-038 | 5 story / 23h |
| 12 | 30 Nov-6 Des 2026 | HAP-9 / HJK-003, HAP-12 / HJK-006 | HAP-21 / HJK-015, HAP-23 / HJK-017 | HAP-29 / HJK-023, HAP-32 / HJK-026 | HAP-39 / HJK-033, HAP-40 / HJK-034 | HAP-45 / HJK-039, HAP-47 / HJK-041 | 10 story / 48h |
| 13 | 7-13 Des 2026 | HAP-15 / HJK-009 | HAP-24 / HJK-018 | HAP-30 / HJK-024, HAP-33 / HJK-027 | HAP-41 / HJK-035 | HAP-48 / HJK-042 | 6 story / 26h |
| 14 | 14-20 Des 2026 | - | - | - | HAP-42 / HJK-036 | HAP-51 / HJK-045 | 2 story / 9h |
| 15 | 21-27 Des 2026 | - | - | - | - | - | Buffer integrasi, demo, dan review |
| **Total** |  | **4 story / 17h** | **4 story / 19h** | **5 story / 23h** | **5 story / 25h** | **5 story / 22h** | **23 story / 106h** |

## Epik dan keadaan sprint

HAP-1 memakai rentang Sprint 0, 12-18 Oktober 2026. Epik HAP-2 sampai HAP-5 memakai rentang tingkat tinggi 19 Oktober-27 Desember 2026 karena modulnya memiliki story di Sprint 1 dan Sprint 2. Epik tidak menggantikan jadwal per story.

Status Sprint 0, Sprint 1, dan Sprint 2 Jira masih **Future** pada audit 22 Okt 2026, meskipun tanggal Sprint 0 sudah lewat dan Sprint 1 sedang berlangsung menurut kalender. Jadwal issue ini tidak memulai atau menutup sprint. PM menentukan perubahan status sprint setelah memeriksa keadaan tim.
