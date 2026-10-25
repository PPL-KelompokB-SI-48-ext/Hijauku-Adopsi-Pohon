# Audit sumber dan kondisi proyek

Audit ini memisahkan fakta dari bahan kuliah, keputusan tim, dan kondisi Jira/GitHub. Kondisi proyek diperiksa kembali pada 25 Oktober 2026. Jira menyimpan status dan rencana issue; GitHub menyimpan kode dan hasil CI; bahan kuliah tetap menjadi rujukan aturan.

## Ringkasan

| Area | Kondisi terverifikasi |
| --- | --- |
| Roster | Lima anggota: Yoga, Bitari, Arif, Wira, dan Rofii. Dinda tidak termasuk. |
| Aturan kelompok | Ketentuan Proyek PPL menyebut 6-7 orang, sedangkan teks LMS Sprint 00 yang dibagikan PM menyebut 5-6 orang. Kesesuaian tim lima orang perlu dikonfirmasi kepada dosen. |
| Backlog | Jira memiliki 5 epic aktif dan 45 story HJK-001 sampai HJK-045 untuk M01 sampai M15. Tiap anggota memegang 3 modul × 3 story = 9 story. |
| Sprint dan PBI | Tiga story pembentuk satu modul sudah ditempatkan utuh dalam satu sprint. Tiap orang mendapat 3 story pada Sprint 1 dan 6 pada Sprint 2, bukan 9 story pada satu sprint. |
| Scope di luar backlog | HAP-6 sudah dihapus permanen. M16-M18 tidak memiliki story aktif; beberapa model dan tabel awal masih berupa scaffold. |
| Jira setup | HAP-61 berstatus Done di Sprint 0; HAP-1 tetap To Do. HAP-7/HJK-001 dikembalikan ke To Do untuk mulai sesuai jadwal minggu 7. |
| Sprint | Sprint 0 closed, Sprint 1 active, Sprint 2 future. Sprint 1 tetap minggu 6-10 dan Sprint 2 minggu 11-15. Minggu 6 dipakai untuk backlog/refinement tanpa coding; story coding mulai Senin, 26 Oktober. |
| Database | MySQL 8+ menjadi baseline pengembangan. CI menjalankan pemeriksaan MySQL dan SQLite. File `.env` pemilik tidak dibaca atau diubah. |
| UI | Stitch berisi 25 frame. Frame 21 (Beranda Publik) dan frame 24 (Kesepakatan Saya/Warga, M11) sudah dipetakan. Layar modul lain belum dianggap implementasi final. |
| Starter | Laravel 13, Filament 5, tiga panel dan Policy dasar, schema/seeder, README, panduan setup dan CI tersedia di hasil merge PR #1/#2. Resource bisnis yang sudah ada pada `main` saat audit adalah alur pencatatan program HJK-001. |
| Estimasi | 45 story memiliki Original estimate proposal berjumlah 211 jam. HAP-61 memiliki Original estimate 18 jam dan remaining estimate 4 jam, tanpa work log aktual. Tidak ada work log yang dibuat dalam pembaruan ini. |
| Pengujian browser | CI sudah memanggil Laravel Dusk dan smoke test beranda pernah lulus. Dusk untuk membuat program baru ditambahkan pada branch lokal `yoga`, tetapi belum berjalan di CI. |

## Pembagian story Jira

| Anggota | Modul Sprint 1 | Story Sprint 1 | Modul Sprint 2 | Story Sprint 2 | Total |
| --- | --- | ---: | --- | ---: | ---: |
| Yoga | M01 | 3 | M02, M03 | 6 | 9 |
| Rofii | M04 | 3 | M05, M06 | 6 | 9 |
| Bitari | M07 | 3 | M08, M09 | 6 | 9 |
| Arif | M10 | 3 | M11, M12 | 6 | 9 |
| Wira | M13 | 3 | M14, M15 | 6 | 9 |
| **Total** | **5 modul** | **15** | **10 modul** | **30** | **45** |

Sprint 1 memuat M01, M04, M07, M10, dan M13. Sprint 2 memuat M02, M03, M05, M06, M08, M09, M11, M12, M14, dan M15. Ketiga PBI satu modul tidak tersebar di dua sprint. Pembagian jumlah PBI ini belum membuktikan kompleksitas sedang/tinggi; pemilik modul perlu memeriksa rubric dan dependensi saat refinement.

Tanggal Jira untuk seluruh story aktif sudah disusun per minggu. Sprint 1: HAP-7, 16, 25, 34, 43 pada minggu 7; HAP-8, 17, 26, 35, 44 pada minggu 8; HAP-9, 18, 27, 36, 45 pada minggu 9. Sprint 2 berlangsung pada minggu 11-15, dengan paling banyak dua story per anggota pada minggu 13. Tanggal target, bukan catatan pekerjaan aktual. Rincian ada di [jadwal mingguan](task-schedule.md).

Beberapa fitur memiliki dependensi lintas anggota. Pemilik perlu menyepakati kontrak data, seed, dan urutan integrasi. Jika dependency membuat acceptance tidak dapat diuji dalam sprint yang ditetapkan, PM harus memperbaiki rencana sebelum coding, tanpa memecah modul ke dua sprint secara diam-diam.

## Jira

- HAP-1 sampai HAP-5 adalah lima epic aktif. HAP-6 sudah dihapus permanen atas keputusan PM.
- HJK-001 sampai HJK-045 tetap berpasangan dengan HAP-7 sampai HAP-51. Issue key tidak dinomori ulang.
- HAP-61 adalah SETUP-001 di Sprint 0 dan berstatus Done. HAP-1 tetap To Do sampai PM menerima keseluruhan hasil.
- HAP-7 sampai HAP-51 saat ini To Do. Issue tersebut sudah dipetakan ke Sprint 1/Sprint 2, diberi label sprint, start date Senin, due date Minggu, dan petunjuk branch personal pemilik. Coding Sprint 1 mulai minggu 7.
- Sprint 1 goal menjelaskan bahwa minggu 6 adalah backlog, coding M01/M04/M07/M10/M13 berjalan minggu 7-9, lalu minggu 10 untuk integrasi. Sprint 2 memuat sepuluh modul utuh dan enam story per anggota.
- Original estimate merupakan rencana. Jangan mencatat actual time dari estimasi atau rentang tanggal; pelaksana menulis work log hanya untuk jam kerja yang benar-benar dilakukan.

## GitHub dan status revert

Repo: <https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon>.

- PR #1 berisi starter Sprint 0 dan PR #2 berisi HJK-001. Keduanya tercatat merged ke `main`.
- PM meminta perubahan kedua PR direvert dari `main`. Revert akan diajukan sebagai PR terpisah untuk ditinjau dan dimerge PM. Sebelum revert PR digabung, `main` masih menyimpan kedua perubahan. Jangan menyebut isi `main` sudah direvert sebelum GitHub menunjukkan merge.
- Branch lokal `yoga` dibuat dari `main` agar Yoga melanjutkan pekerjaan pada satu branch personal. Dokumentasi dan tes Dusk yang sedang disiapkan belum dipush pada saat audit ini ditulis.
- Aturan kontribusi memakai satu branch personal per anggota: `yoga`, `bitari`, `arif`, `wira`, atau `rofii`. Setiap commit dan PR tetap menyebut key HAP kapital. Penulis menyelesaikan konflik; anggota meminta Yoga untuk review; hanya PM yang merge. PR PM tidak memerlukan self-approval, tetapi CI harus lulus.
- GitHub for Atlassian sebelumnya terverifikasi terhubung ke organisasi `PPL-KelompokB-SI-48-ext`, dengan `Hijauku-Adopsi-Pohon` dalam daftar repository terhubung. Setelah push, panel Development pada issue harus diperiksa lagi untuk tautan commit/PR yang nyata.

Kolaborator repo yang diperiksa pada audit terdahulu adalah Yoga sebagai admin dan empat anggota sebagai write. Dinda tidak termasuk roster. Jangan menambahkan atau menghapus kolaborator berdasarkan asumsi; perubahan akses dilakukan dengan memeriksa daftar aktual GitHub.

## Arsitektur, database, dan keamanan

- Rancangan arsitektur Rev 1 memakai satu monolit Laravel 13, Filament 5 untuk tiga panel role, Blade + Vite untuk publik, MySQL 8+ untuk pengembangan, serta Storage privat untuk bukti.
- Panel gate dan Policy dasar tersedia. Setiap Resource baru tetap harus punya Policy, query ownership, validasi, tes akses, dan aksi status yang aman.
- Kapasitas petak, kuota kegiatan, dan alokasi pohon memerlukan constraint, transaksi, dan tes kondisi bersamaan.
- M16-M18 masih tampak sebagai model/tabel scaffold dari rancangan awal, tetapi tidak memiliki workflow aktif. Jangan membuat Resource atau fitur untuk area tersebut tanpa scope baru.
- Pemilik menyatakan MySQL lokal sudah berjalan. Audit tidak membaca `.env`, tidak mengubahnya, tidak tersambung ke database lokal, dan tidak menjalankan migrasi.

## Pengujian

- CI workflow memeriksa Composer, migrasi/seeder SQLite dan MySQL, route tiga panel, tes Unit/Feature, build Vite, dan Laravel Dusk.
- Dusk menggunakan database SQLite sementara yang terpisah dari database pengembangan. CI menjalankan migrasi dan seeder pada file database tersebut sebelum menyalakan server browser.
- `tests/Browser/ProgramCreationTest.php` menambahkan skenario manager login, membuat program melalui Filament, dan melihat status Draf. Hasil belum terverifikasi sampai CI berjalan pada commit terbaru.
- Feature tests HJK-001 tetap memeriksa validasi dan aturan aplikasi di luar browser. Dusk tidak menggantikan tes policy, kepemilikan, dan kondisi penolakan.
- Shell lokal untuk audit ini belum menyediakan PHP, Composer, Chrome, atau ChromeDriver. Karena itu tes lokal Dusk tidak dapat dijalankan; gunakan hasil CI sebagai bukti untuk tes tersebut.

## Stitch dan bahan kuliah

Proyek Stitch Hijauku Admin UI Batch 01 memiliki 25 frame. Frame yang dipetakan adalah Beranda Publik dan Kesepakatan Saya/Warga. Desain katalog atau halaman modul lain belum dijadikan klaim implementasi. Pemeriksaan visual seluruh frame belum dilakukan. Lihat [panduan desain UI](ui-design.md).

Sumber yang diperiksa: rancangan arsitektur, Ketentuan Proyek PPL, Modul 1 Reguler 2026, proposal tugas besar, materi requirement/Scrum/agile/sprint/version control/issues, Jira, repo GitHub, Stitch, dan `desain.md`. Proposal masih boleh direvisi agar mengikuti roster dan rencana terbaru; keputusan backlog resmi tetap dicatat di Jira.

## Hal yang perlu tindak lanjut manusia

1. Dosen mengonfirmasi perbedaan jumlah anggota antara PDF dan LMS untuk roster lima orang.
2. Pemilik modul memeriksa kompleksitas tiap fitur, kesiapan story, dan kontrak data lintas modul saat refinement.
3. Pemilik modul memetakan frame Stitch yang relevan dan meninjau hasil UI sebelum menyatakan desain sesuai.
4. PM meninjau revert PR dan menerima hasilnya. Setelah merge revert, periksa lagi acceptance serta status HAP-61/HAP-7 agar Jira mengikuti kondisi `main`.
5. CI harus selesai pada commit terbaru untuk membuktikan Dusk HJK-001 berjalan.
