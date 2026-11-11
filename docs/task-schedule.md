# Jadwal mingguan backlog

Jira adalah sumber tanggal, sprint, dan status issue. Tabel minggu di bawah menunjukkan rencana, bukan catatan actual time. Audit Jira/GitHub dilakukan pada 11 November 2026 setelah PR #20 dan pembaruan dokumentasi PR #21 merged. Merge commit fitur PR #20 adalah `232e6a213897f87d1e081274767efa61dff6c63c`; CI run `42930124355` untuk fitur dan `42932759405` untuk dokumentasi lulus. Branch `yoga` di-fast-forward ke `main` setelahnya. PR #12, #14, dan #15 tetap ditutup tanpa merge.

Alur HJK-001 masuk ke `main` melalui PR #2 pada 22 Oktober. Tes Dusk dan penerimaan PM menyusul melalui PR #4 pada 25 Oktober. HAP-8 dan HAP-9 diterima melalui PR #17 pada 10 November. PR #16 untuk HAP-44/45 dan PR #19 untuk shell Filament juga sudah merged.

### Status story terakhir

| Status | Issue |
| --- | --- |
| Done | HAP-7, HAP-8, HAP-9, HAP-10, HAP-11, HAP-12, HAP-25, HAP-43, HAP-44, HAP-45 |
| In Progress | HAP-16, HAP-17, HAP-18, HAP-26, HAP-27, HAP-34, HAP-35 |
| To Do | 28 story lainnya |

HAP-16/17, HAP-26/27, dan HAP-34/35 saat ini In Progress karena audit belum memverifikasi acceptance, tes, CI, review, dan penerimaan PM pada head branch terbaru. Status ini bukan semata-mata karena PR belum digabung. Pemilik perlu mempertahankan perubahan unik, menyinkronkan branch personal dengan `main`, menyelesaikan konflik, meninjau feedback PR lama, lalu menyiapkan PR pengganti untuk integrasi akhir Sprint 1. HAP-16/HAP-34 jatuh tempo 1 November; HAP-17/HAP-26/HAP-35 jatuh tempo 8 November. Pemilik dan PM perlu menyepakati replan di Jira. Jangan mengubah tanggal lama sebelum ada kesepakatan, dan jangan mengisi work log dari tanggal rencana.

HAP-10/11/12 untuk M02 sudah Done setelah PR #20 merged ke `main` pada 11 November. CI run `42930124355` lulus; due date HAP-10 adalah 15 November dan HAP-11/12 22 November, tetapi ketiganya selesai lebih awal. HAP-62 juga Done sebelum start date rencana 16 November; due date 22 November tetap dipertahankan sebagai target awal. HAP-71/HAP-72 tetap menangani integrasi PR anggota lain dan penutupan Sprint 1. Tidak ada work log yang dibuat untuk menyamakan estimasi dengan waktu kerja.

## Aturan tanggal

- Start date tiap story adalah hari Senin. Due date adalah hari Minggu pada minggu yang ditetapkan.
- Tanggal adalah target kerja, bukan durasi aktual atau bukti bahwa pekerjaan sudah dimulai.
- Pekerjaan lintas anggota berjalan paralel. Setelah satu modul per anggota dimajukan, Sprint 2 direncanakan dua story per orang pada minggu 11 dan satu story pada minggu 12. Tanggal adalah target; pemilik tetap mengatur urutan implementasi dan tes agar kapasitas realistis.
- Jangan mengisi work log dari tanggal, estimasi, atau laporan rencana. Pelaksana mencatat waktu nyata setelah bekerja.
- Perubahan jadwal harus diperbarui di Jira dan dokumen ini pada perubahan yang sama.

## Sprint 0 - Persiapan, minggu 5

| Issue | Pemilik | Start date | Due date | Status dan catatan |
| --- | --- | --- | --- | --- |
| HAP-1 | Yoga (PM) | Senin, 12 Okt 2026 | Minggu, 18 Okt 2026 | Epic fondasi, Done setelah PM menerima kriteria HAP-61. |
| HAP-61 | Yoga (PM) | Senin, 12 Okt 2026 | Minggu, 18 Okt 2026 | SETUP-001, Done pada pembacaan akhir 25 Oktober. Tanggal ini rencana persiapan, bukan actual time. |

Sprint 0 adalah persiapan tim dan bukan sprint development formal. Rencana awal memakai minggu 6 untuk menyusun/refine backlog dan merapikan rencana, tanpa coding yang dijadwalkan saat itu.

## Pembagian fitur per anggota

Setiap anggota memiliki tiga modul dengan tiga story per modul, total sembilan story. Atas keputusan PM, satu modul utuh per anggota dimajukan dari Sprint 2 ke Sprint 1. Hasilnya enam story per orang di Sprint 1 dan tiga di Sprint 2; PBI satu modul tidak dipecah.

| Anggota | Sprint 1, 6 story | Sprint 2, 3 story |
| --- | --- | --- |
| Yoga | M01, HAP-7 sampai HAP-9; M02, HAP-10 sampai HAP-12 | M03, HAP-13 sampai HAP-15 |
| Rofii | M04, HAP-16 sampai HAP-18; M05, HAP-19 sampai HAP-21 | M06, HAP-22 sampai HAP-24 |
| Bitari | M07, HAP-25 sampai HAP-27; M08, HAP-28 sampai HAP-30 | M09, HAP-31 sampai HAP-33 |
| Arif | M10, HAP-34 sampai HAP-36; M11, HAP-37 sampai HAP-39 | M12, HAP-40 sampai HAP-42 |
| Wira | M13, HAP-43 sampai HAP-45; M14, HAP-46 sampai HAP-48 | M15, HAP-49 sampai HAP-51 |

Pemindahan modul utuh menjaga tiga PBI pembentuk fitur tetap satu sprint. Beberapa fitur yang dimajukan memiliki dependensi data pada modul yang tetap di Sprint 2: M05 perlu petak M03, M11 perlu inventaris pohon M06, dan M14 dapat memakai data pohon M06 serta penugasan M09. Untuk pengerjaan Sprint 1, sepakati kontrak tabel/field dan relasi berdasarkan migration awal, gunakan fixture/seed uji yang terisolasi, dan jangan menduplikasi schema. Alur lintas modul baru dinyatakan terintegrasi setelah dependensi itu masuk ke `main` pada minggu 13 dan diuji lagi di UAT minggu 14.

## Sprint 1 - minggu 6-10

Sprint formal tetap mengikuti kalender kuliah: 19 Okt sampai 22 Nov 2026. Minggu 6 dipakai untuk backlog/refinement tanpa coding terjadwal; pengerjaan story dimulai minggu 7. Minggu 10 menjadi jendela persiapan PR, integrasi PM, demo, dan retrospektif.

| Pekan | Rentang kerja | Issue yang dijadwalkan |
| --- | --- | --- |
| 6 | 19-25 Okt 2026 | Backlog dan refinement, tanpa jadwal coding story. HJK-001, HAP-16, dan HAP-25 tercatat mulai/aktif lebih awal; lihat catatan pelaksanaan di atas. |
| 7 | 26 Okt-1 Nov 2026 | HAP-7 / HJK-001 (M01); HAP-16 / HJK-010 (M04); HAP-25 / HJK-019 (M07); HAP-34 / HJK-028 (M10); HAP-43 / HJK-037 (M13). |
| 8 | 2-8 Nov 2026 | HAP-17 / HJK-011 (M04); HAP-26 / HJK-020 (M07); HAP-35 / HJK-029 (M10); HAP-44 / HJK-038 (M13). HAP-8 kemudian dijadwalkan ulang ke minggu 9. |
| 9 | 9-15 Nov 2026 | HAP-8 / HJK-002 dan HAP-9 / HJK-003 (M01); HAP-18 / HJK-012 (M04); HAP-27 / HJK-021 (M07); HAP-36 / HJK-030 (M10); HAP-45 / HJK-039 (M13). Ditambah satu story per anggota dari modul yang dimajukan: HAP-10, HAP-19, HAP-28, HAP-37, HAP-46. |
| 10 | 16-22 Nov 2026 | Sepuluh story yang tersisa dari modul yang dimajukan: HAP-11/12, HAP-20/21, HAP-29/30, HAP-38/39, HAP-47/48. HAP-62 sampai HAP-66 untuk persiapan PR personal; HAP-71/HAP-72 untuk integrasi dan penutupan Sprint 1. Demo dan retrospektif. |
| **Total** |  | **30 story, 6 per anggota; 7 task PR/integrasi/penutupan** |

### PR dan penutupan Sprint 1

HAP-62 sampai HAP-66 adalah satu task persiapan PR untuk tiap anggota. Tugasnya bukan membuat fitur kedua kali: anggota merapikan satu PR dari branch personal, memastikan story yang dicakup, commit HAP, tes, CI, dan tautan Development Jira jelas, lalu menyelesaikan konflik sendiri setelah mengambil `main` terbaru. Perubahan yang sudah di `main` cukup diverifikasi, jangan dibuat PR duplikat.

HAP-71/HAP-72 adalah pekerjaan PM untuk review dan merge dua batch. HAP-71 mengintegrasikan Yoga, Rofii, dan Bitari. HAP-72 menyusul untuk Arif dan Wira setelah batch pertama masuk `main` dan CI lulus. Pemeriksaan kesiapan branch, database, UI Stitch, responsive, Policy, Feature/Dusk, dan CI merupakan checklist dalam task integrasi, bukan task preflight tersendiri. PM melakukan merge setelah review/CI sesuai aturan repo.

| Issue | Assignee | Scope | Start | Due |
| --- | --- | --- | --- | --- |
| HAP-62 | Yoga | PR personal untuk HAP-7 sampai HAP-12 (M01-M02) | 16 Nov | 22 Nov |
| HAP-63 | Rofii | PR personal untuk HAP-16 sampai HAP-21 (M04-M05) | 16 Nov | 22 Nov |
| HAP-64 | Bitari | PR personal untuk HAP-25 sampai HAP-30 (M07-M08) | 16 Nov | 22 Nov |
| HAP-65 | Arif | PR personal untuk HAP-34 sampai HAP-39 (M10-M11) | 16 Nov | 22 Nov |
| HAP-66 | Wira | PR personal untuk HAP-43 sampai HAP-48 (M13-M14) | 16 Nov | 22 Nov |
| HAP-71 | Yoga (PM) | Integrasi/penutupan batch 1: Yoga, Rofii, Bitari | 16 Nov | 22 Nov |
| HAP-72 | Yoga (PM) | Integrasi/penutupan batch 2: Arif, Wira, setelah HAP-71 | 16 Nov | 22 Nov |

Urutan Jira memakai hubungan **blocks**: HAP-62/63/64 sebelum HAP-71; HAP-65/66 dan HAP-71 sebelum HAP-72. HAP-72 memblokir HAP-67 agar Sprint 2 baru diintegrasikan setelah hasil Sprint 1 tersedia. HAP-70 sudah dihapus karena isinya duplikat checklist di HAP-71/HAP-72.

### Apa arti kesiapan integrasi

Kesiapan integrasi adalah pemeriksaan teknis singkat sebelum PM menggabungkan PR, bukan task produk atau serah-terima berulang. Pastikan branch anggota terbaru terhadap `main`, semua konflik sudah diselesaikan pemiliknya, PR mencakup issue yang benar, CI pada head PR lulus, dan migration tidak bertabrakan. Bandingkan PK/FK dengan migration domain yang sudah ada, cek Policy/query ownership, dan cocokkan UI ke frame Stitch serta tema Filament reusable. Jika ada dependensi lintas modul yang belum tersedia, uji story dengan fixture yang sesuai dan tandai batas itu. Jangan menyatakan alur end-to-end lintas modul lolos sampai integrasi penuh Sprint 2 dan UAT.

## Sprint 2 - minggu 11-15

Sprint 2 tetap berlangsung 23 Nov sampai 27 Des 2026 sesuai kalender kuliah. Setelah lima modul utuh dimajukan, Sprint 2 berisi 15 story, tiga per orang. Setiap anggota menyelesaikan dua story pada minggu 11 dan satu story pada minggu 12. Minggu 13 untuk integrasi PR, minggu 14 untuk UAT, pemeriksaan deploy dan dokumentasi rilis, lalu minggu 15 sebagai ruang perbaikan/feedback dosen, laporan akhir, dan persiapan presentasi.

| Pekan | Rentang kerja | Issue yang dijadwalkan |
| --- | --- | --- |
| 11 | 23-29 Nov 2026 | Yoga: HAP-13/14; Rofii: HAP-22/23; Bitari: HAP-31/32; Arif: HAP-40/41; Wira: HAP-49/50. Dua story per orang. |
| 12 | 30 Nov-6 Des 2026 | Yoga: HAP-15; Rofii: HAP-24; Bitari: HAP-33; Arif: HAP-42; Wira: HAP-51. Satu story per orang; semua story Sprint 2 ditargetkan selesai. |
| 13 | 7-13 Des 2026 | HAP-67: integrasi PR batch 1 (Yoga, Rofii, Bitari). Merge sesuai dependensi M03 lalu M06, lalu M09. HAP-68: batch 2 (Arif, Wira) setelah HAP-67 dan CI `main` lulus. Anggota tetap membuka PR terpisah dari branch personal; pemilik menyelesaikan konflik dan PM merge. |
| 14 | 14-20 Des 2026 | HAP-69: UAT lintas role, regression pada modul yang dimajukan, pemeriksaan migration/seeder dan relasi PK/FK, cek API bila memang ada di acceptance, smoke test deploy, verifikasi UI Stitch dan responsive, serta dokumentasi rilis. |
| 15 | 21-27 Des 2026 | HAP-73: tindak lanjut temuan UAT/feedback dosen, tes regresi, laporan akhir Tugas Besar, dokumentasi akhir, dan persiapan presentasi. Sprint formal berakhir pada 27 Desember. |
| **Total** |  | **15 story, 3 per anggota; 4 task integrasi/UAT/penutupan** |

Urutan Jira memakai hubungan **blocks**: HAP-67 sebelum HAP-68, HAP-68 sebelum HAP-69, dan HAP-69 sebelum HAP-73. HAP-67/HAP-68 hanya menyelesaikan integrasi teknis, belum menutup seluruh Sprint 2. HAP-69 memverifikasi aplikasi yang sudah terintegrasi dan memeriksa kesiapan rilis. HAP-73 menuntaskan temuan, dokumen, laporan, dan presentasi. Empat task koordinasi ini punya hasil berbeda dan tidak menggantikan 15 story fitur.

## Sprint di Jira

- Sprint 0: ditutup, 12-18 Okt 2026.
- Sprint 1: aktif, kalender 19 Okt-22 Nov 2026. Minggu 6 dipakai untuk backlog, coding mulai 26 Okt. Total 30 story, enam per anggota dari dua modul.
- Sprint 2: future, kalender 23 Nov-27 Des 2026. Total 15 story; tiap anggota mengerjakan dua di minggu 11 dan satu di minggu 12. Minggu 13 integrasi PR; minggu 14 UAT/deploy/dokumen rilis; minggu 15 temuan, laporan, dan persiapan presentasi.
- Tanggal resmi sprint tidak digeser karena mengikuti kalender kuliah. Tanggal per story menjadi target mingguan, bukan work log aktual.
- Epic HAP-2 sampai HAP-5 mencakup rentang story lintas Sprint 1 dan Sprint 2; tanggal epic tidak menggantikan Start date dan Due date story.

## Replan HAP-8 dan HAP-9 pada 10 November

Pada audit 10 November, target sebelumnya untuk HAP-8 (1 November) dan HAP-9 (8 November) sudah lewat. Keduanya dijadwalkan ulang ke minggu 9, 9-15 November 2026. Perubahan ini tercatat di riwayat Jira dan komentar kedua issue; tanggal tersebut adalah rencana baru, bukan catatan waktu kerja.

| Issue | Target sebelumnya | Target baru | Status setelah audit |
| --- | --- | --- | --- |
| HAP-8 / HJK-002 | 26 Okt-1 Nov | 9-15 Nov | Done pada 10 Nov setelah PR #17 merged, CI lulus, dan PM menerima hasil. |
| HAP-9 / HJK-003 | 2-8 Nov | 9-15 Nov | Done pada 10 Nov setelah PR #17 merged, CI lulus, dan PM menerima hasil. |

HAP-8 dan HAP-9 adalah dua story M01 yang dijadwalkan ulang ke minggu 9. Keduanya selesai pada 10 November setelah PR #17 merged dan CI lulus. HAP-10 dari M02 juga ditargetkan 15 November setelah modul itu dimajukan, sehingga Yoga memiliki tiga story dengan due date minggu ini, tetapi dua di antaranya sudah Done. Anggota lain masing-masing memiliki dua story yang ditargetkan minggu ini. Target tanggal bukan work log; tidak ada work log yang dibuat untuk PR #17 dan sisa estimasi HAP-8/HAP-9 menjadi 0 jam.
