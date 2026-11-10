# Jadwal mingguan backlog

Jira adalah sumber tanggal, sprint, dan status issue. Tabel minggu di bawah menunjukkan rencana, bukan catatan actual time. Status live pada bagian ini diperiksa 10 November 2026.

Alur HJK-001 masuk ke `main` melalui PR #2 pada 22 Oktober. Tes Dusk dan penerimaan PM menyusul melalui PR #4 pada 25 Oktober. HAP-8 dan HAP-9 diterima melalui PR #17 pada 10 November. PR #16 untuk HAP-44/45 juga sudah merged pada 10 November.

### Status story terakhir

| Status | Issue |
| --- | --- |
| Done | HAP-7, HAP-8, HAP-9, HAP-25, HAP-43, HAP-44, HAP-45 |
| In Progress | HAP-16, HAP-17, HAP-18, HAP-26, HAP-27, HAP-34, HAP-35 |
| To Do | 31 story lainnya |

HAP-16/17, HAP-26/27, dan HAP-34/35 saat ini In Progress karena audit belum memverifikasi acceptance dan tes setiap story pada head branch terbaru, bukan semata-mata karena PR belum digabung. Ketiga PR perlu disinkronkan dengan `main` dan mendapat CI baru sebelum integrasi akhir sprint. Jika pemilik sudah memastikan acceptance dan tes pada branch personal yang di-push, Jira dapat diperbarui ke Done sebelum PR akhir sprint di-merge. HAP-16/HAP-34 jatuh tempo 1 November; HAP-17/HAP-26/HAP-35 jatuh tempo 8 November. Pemilik perlu menetapkan tanggal replan di Jira. Jangan mengubah tanggal lama sebelum ada kesepakatan, dan jangan mengisi work log dari tanggal rencana.

## Aturan tanggal

- Start date tiap story adalah hari Senin. Due date adalah hari Minggu pada minggu yang ditetapkan.
- Tanggal adalah target kerja, bukan durasi aktual atau bukti bahwa pekerjaan sudah dimulai.
- Pekerjaan lintas anggota berjalan paralel. Satu orang mendapat maksimal dua story dalam satu minggu.
- Jangan mengisi work log dari tanggal, estimasi, atau laporan rencana. Pelaksana mencatat waktu nyata setelah bekerja.
- Perubahan jadwal harus diperbarui di Jira dan dokumen ini pada perubahan yang sama.

## Sprint 0 - Persiapan, minggu 5

| Issue | Pemilik | Start date | Due date | Status dan catatan |
| --- | --- | --- | --- | --- |
| HAP-1 | Yoga (PM) | Senin, 12 Okt 2026 | Minggu, 18 Okt 2026 | Epic fondasi, Done setelah PM menerima kriteria HAP-61. |
| HAP-61 | Yoga (PM) | Senin, 12 Okt 2026 | Minggu, 18 Okt 2026 | SETUP-001, Done pada pembacaan akhir 25 Oktober. Tanggal ini rencana persiapan, bukan actual time. |

Sprint 0 adalah persiapan tim dan bukan sprint development formal. Rencana awal memakai minggu 6 untuk menyusun/refine backlog dan merapikan rencana, tanpa coding yang dijadwalkan saat itu.

## Pembagian fitur per anggota

Setiap anggota memiliki tiga modul dengan tiga story per modul, total sembilan story. Agar tiga PBI pembentuk satu modul tetap berada dalam satu sprint, rencana menempatkan satu modul anggota di Sprint 1 dan dua modul di Sprint 2.

| Anggota | Sprint 1, 3 story | Sprint 2, 6 story |
| --- | --- | --- |
| Yoga | M01, HAP-7 sampai HAP-9 | M02, HAP-10 sampai HAP-12; M03, HAP-13 sampai HAP-15 |
| Rofii | M04, HAP-16 sampai HAP-18 | M05, HAP-19 sampai HAP-21; M06, HAP-22 sampai HAP-24 |
| Bitari | M07, HAP-25 sampai HAP-27 | M08, HAP-28 sampai HAP-30; M09, HAP-31 sampai HAP-33 |
| Arif | M10, HAP-34 sampai HAP-36 | M11, HAP-37 sampai HAP-39; M12, HAP-40 sampai HAP-42 |
| Wira | M13, HAP-43 sampai HAP-45 | M14, HAP-46 sampai HAP-48; M15, HAP-49 sampai HAP-51 |

Pemetaan ini memberi setiap anggota satu kelompok tiga story pada tiap sprint formal. Pemilik modul tetap perlu mengonfirmasi bahwa salah satu kelompok fitur per sprint memenuhi kompleksitas sedang/tinggi menurut rubrik kelas. Modul dengan dependensi lintas anggota perlu menyepakati kontrak data, seed, dan urutan integrasi sebelum coding.

## Sprint 1 - minggu 6-10

Sprint formal tetap mengikuti kalender kuliah: 19 Okt sampai 22 Nov 2026. Minggu 6 untuk backlog/refinement; pengerjaan story dimulai minggu 7. Minggu 10 disediakan untuk integrasi, demo, dan review.

| Pekan | Rentang kerja | Issue yang dijadwalkan |
| --- | --- | --- |
| 6 | 19-25 Okt 2026 | Rencana: backlog dan refinement, tanpa jadwal coding story. HJK-001, HAP-16, dan HAP-25 tercatat mulai/aktif lebih awal; lihat catatan pelaksanaan di atas. |
| 7 | 26 Okt-1 Nov 2026 | HAP-7 / HJK-001 (M01); HAP-16 / HJK-010 (M04); HAP-25 / HJK-019 (M07); HAP-34 / HJK-028 (M10); HAP-43 / HJK-037 (M13). |
| 8 | 2-8 Nov 2026 | Rencana awal HAP-8 / HJK-002 (M01); HAP-17 / HJK-011 (M04); HAP-26 / HJK-020 (M07); HAP-35 / HJK-029 (M10); HAP-44 / HJK-038 (M13). HAP-8 kemudian dijadwalkan ulang ke minggu 9. |
| 9 | 9-15 Nov 2026 | HAP-8 / HJK-002 dan HAP-9 / HJK-003 (M01); HAP-18 / HJK-012 (M04); HAP-27 / HJK-021 (M07); HAP-36 / HJK-030 (M10); HAP-45 / HJK-039 (M13). |
| 10 | 16-22 Nov 2026 | Integrasi, demo, review, dan retrospektif Sprint 1. |
| **Total** |  | **15 story, 3 per anggota** |

## Sprint 2 - minggu 11-15

Sprint 2 berlangsung 23 Nov sampai 27 Des 2026. Setiap modul tetap berada seluruhnya di sprint ini. Dua story per orang pada minggu 13 berjalan paralel sesuai urutan prasyarat.

| Pekan | Rentang kerja | Issue yang dijadwalkan |
| --- | --- | --- |
| 11 | 23-29 Nov 2026 | HAP-10 / HJK-004 (M02); HAP-19 / HJK-013 (M05); HAP-28 / HJK-022 (M08); HAP-37 / HJK-031 (M11); HAP-46 / HJK-040 (M14). |
| 12 | 30 Nov-6 Des 2026 | HAP-11 / HJK-005 (M02); HAP-20 / HJK-014 (M05); HAP-29 / HJK-023 (M08); HAP-38 / HJK-032 (M11); HAP-47 / HJK-041 (M14). |
| 13 | 7-13 Des 2026 | HAP-12 / HJK-006 (M02); HAP-13 / HJK-007 (M03); HAP-21 / HJK-015 (M05); HAP-22 / HJK-016 (M06); HAP-30 / HJK-024 (M08); HAP-31 / HJK-025 (M09); HAP-39 / HJK-033 (M11); HAP-40 / HJK-034 (M12); HAP-48 / HJK-042 (M14); HAP-49 / HJK-043 (M15). |
| 14 | 14-20 Des 2026 | HAP-14 / HJK-008 (M03); HAP-23 / HJK-017 (M06); HAP-32 / HJK-026 (M09); HAP-41 / HJK-035 (M12); HAP-50 / HJK-044 (M15). |
| 15 | 21-27 Des 2026 | HAP-15 / HJK-009 (M03); HAP-24 / HJK-018 (M06); HAP-33 / HJK-027 (M09); HAP-42 / HJK-036 (M12); HAP-51 / HJK-045 (M15); demo, review, dan retrospektif Sprint 2. |
| **Total** |  | **30 story, 6 per anggota** |

## Sprint di Jira

- Sprint 0: ditutup, 12-18 Okt 2026.
- Sprint 1: aktif, kalender 19 Okt-22 Nov 2026. Minggu 6 dipakai untuk backlog, coding mulai 26 Okt.
- Sprint 2: future, kalender 23 Nov-27 Des 2026.
- Tanggal resmi sprint tidak digeser karena mengikuti kalender kuliah. Yang digeser adalah tanggal kerja story Sprint 1 agar mencerminkan mulai coding pada minggu 7.
- Epic HAP-2 sampai HAP-5 mencakup rentang story lintas Sprint 1 dan Sprint 2; tanggal epic tidak menggantikan Start date dan Due date story.

## Replan HAP-8 dan HAP-9 pada 10 November

Pada audit 10 November, target sebelumnya untuk HAP-8 (1 November) dan HAP-9 (8 November) sudah lewat. Keduanya dijadwalkan ulang ke minggu 9, 9-15 November 2026. Perubahan ini tercatat di riwayat Jira dan komentar kedua issue; tanggal tersebut adalah rencana baru, bukan catatan waktu kerja.

| Issue | Target sebelumnya | Target baru | Status setelah audit |
| --- | --- | --- | --- |
| HAP-8 / HJK-002 | 26 Okt-1 Nov | 9-15 Nov | Done pada 10 Nov setelah PR #17 merged, CI lulus, dan PM menerima hasil. |
| HAP-9 / HJK-003 | 2-8 Nov | 9-15 Nov | Done pada 10 Nov setelah PR #17 merged, CI lulus, dan PM menerima hasil. |

M01 memiliki dua story dalam minggu ini, masih di bawah batas dua story per anggota per minggu. Target awal keduanya terlewati dan replan ke minggu 9 tercatat di Jira sebelum selesai. Implementasi digabung pada 10 November, sebelum target revisi 15 November. Tidak ada work log yang dibuat; sisa estimasi Jira menjadi 0 jam.
