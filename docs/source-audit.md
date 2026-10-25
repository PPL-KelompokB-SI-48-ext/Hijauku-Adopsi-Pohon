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
| Jira setup | Pembacaan akhir 25 Oktober 2026: HAP-61 Done, HAP-7 In Progress, HAP-1 To Do. Tanggal target story tetap mengikuti jadwal minggu 7. |
| Sprint | Sprint 0 closed, Sprint 1 active, Sprint 2 future. Sprint 1 tetap minggu 6-10 dan Sprint 2 minggu 11-15. Minggu 6 dipakai untuk backlog/refinement tanpa coding; story coding mulai Senin, 26 Oktober. |
| Database | MySQL 8+ menjadi baseline pengembangan. CI menjalankan pemeriksaan MySQL dan SQLite. File `.env` pemilik tidak dibaca atau diubah. |
| UI | Seluruh 25 frame Stitch diperiksa sebagai rancangan. Lima belas modul dipetakan ke PBI dan pembicara video; penerimaan implementasi UI tetap mengikuti story. |
| Starter | Laravel 13, Filament 5, tiga panel dan Policy dasar, schema/seeder, README, panduan setup dan CI tersedia di hasil merge PR #1/#2. Resource bisnis yang sudah ada pada `main` saat audit adalah alur pencatatan program HJK-001. |
| Estimasi | 45 story memiliki Original estimate proposal berjumlah 211 jam. HAP-61 memiliki Original estimate 18 jam dan remaining estimate 4 jam, tanpa work log aktual. Tidak ada work log yang dibuat dalam pembaruan ini. |
| Pengujian browser | CI menjalankan Laravel Dusk dan smoke test beranda. Tes manager membuat program lewat panel Filament dan memeriksa nama, tujuan, periode, serta status Draf. Run CI `40170424971` pada commit `7e59f30` lulus. |

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
- HAP-61 adalah SETUP-001 di Sprint 0 dan berstatus Done pada pembacaan akhir. HAP-1 tetap To Do. Remaining estimate HAP-61 masih 4 jam, Original estimate 18 jam, dan work log kosong. Pelaksana/PM perlu memeriksa nilai remaining terhadap sisa kerja nyata; audit tidak mengubah status atau membuat work log.
- Pembacaan ulang 25 Oktober 2026 menunjukkan HAP-7 In Progress; HAP-8 sampai HAP-51 To Do. Status itu dipertahankan sesuai Jira. Seluruh story sudah dipetakan ke Sprint 1/Sprint 2, start date Senin dan due date Minggu; coding direncanakan mulai minggu 7. Status aktif tidak mengubah tanggal target menjadi work log aktual.
- Sprint 1 goal menjelaskan bahwa minggu 6 adalah backlog, coding M01/M04/M07/M10/M13 berjalan minggu 7-9, lalu minggu 10 untuk integrasi. Sprint 2 memuat sepuluh modul utuh dan enam story per anggota.
- Original estimate merupakan rencana. Jangan mencatat actual time dari estimasi atau rentang tanggal; pelaksana menulis work log hanya untuk jam kerja yang benar-benar dilakukan.

## GitHub dan status revert

Repo: <https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon>.

- `main` protected pada commit `d683407`.
- PR #1 berisi starter Sprint 0 dan PR #2 berisi HJK-001. Keduanya tercatat merged ke `main`.
- PM meminta perubahan kedua PR direvert dari `main`. PR draft #5 sudah dibuat untuk ditinjau. Isinya membalik PR #2 dan #1, termasuk menghapus starter Laravel dan workflow CI. Revert belum digabung; `main` masih menyimpan kedua PR. PR #5 tidak memiliki CI check karena workflow dihapus pada hasil revert. Jangan gabungkan sebelum rencana pemulihan kode/CI diputuskan.
- Branch personal `yoga` dibuat dari `main`, dipush pada 25 Oktober 2026, dan PR draft #4 terbuka untuk Dusk serta penyelarasan dokumentasi. Commit `45e3666` lulus CI `40170795177`. Branch ini mempertahankan basis kode PR #1/#2 untuk pekerjaan Yoga.
- Branch `feature/HAP-16-jenis-pohon` menjadi head PR #3 yang masih terbuka, dibuat oleh Rofii29. Satu-satunya commit yang belum ada di `main` adalah `ed658a3` untuk HAP-16. Pemilik perlu memindahkannya ke branch personal `rofii` dan membuka PR pengganti; jangan hapus atau rename head branch sebelum penggantinya siap. Rename head branch dari GitHub akan menutup PR terbuka.
- Branch remote lama `codex/sprint-0-starter-pack` dan `feature/HAP-7-program-penghijauan` masing-masing terkait PR #1 dan #2 yang sudah merged. Perbandingan terhadap `main` menunjukkan nol commit unik di kedua branch dan tidak ada PR terbuka. Remote branch keduanya dihapus pada 25 Oktober 2026 atas permintaan PM. Checkout lokal tidak dihapus.
- Branch `revert/HAP-61-HAP-7` adalah branch administrasi untuk PR draft #5, bukan branch personal anggota. Pertahankan selama PR itu terbuka.
- Aturan kontribusi memakai satu branch personal per anggota: `yoga`, `bitari`, `arif`, `wira`, atau `rofii`. Setiap commit dan PR tetap menyebut key HAP kapital. Penulis menyelesaikan konflik; anggota meminta Yoga untuk review; hanya PM yang merge. PR PM tidak memerlukan self-approval, tetapi CI harus lulus.
- GitHub for Atlassian terhubung ke organisasi `PPL-KelompokB-SI-48-ext`. Setelah push terakhir, Jira Development menampilkan satu build sukses dan nol build gagal pada HAP-7 dan HAP-61. Tautan yang tampil harus tetap diperiksa pada issue setelah push.

Pembacaan ulang kolaborator pada 25 Oktober 2026 menunjukkan `mygads` sebagai admin; `ayuwirasalindri`, `Rofii29`, `bitaridmr`, dan `ArifSobriyanto` memiliki write. Jumlahnya lima dan Dinda tidak termasuk. Jangan menambahkan atau menghapus kolaborator berdasarkan asumsi; perubahan akses memerlukan pemeriksaan daftar aktual GitHub.

## Arsitektur, database, dan keamanan

- Rancangan arsitektur Rev 1 memakai satu monolit Laravel 13, Filament 5 untuk tiga panel role, Blade + Vite untuk publik, MySQL 8+ untuk pengembangan, serta Storage privat untuk bukti.
- Panel gate dan Policy dasar tersedia. Setiap Resource baru tetap harus punya Policy, query ownership, validasi, tes akses, dan aksi status yang aman.
- Kapasitas petak, kuota kegiatan, dan alokasi pohon memerlukan constraint, transaksi, dan tes kondisi bersamaan.
- M16-M18 masih tampak sebagai model/tabel scaffold dari rancangan awal, tetapi tidak memiliki workflow aktif. Jangan membuat Resource atau fitur untuk area tersebut tanpa scope baru.
- Pemilik menyatakan MySQL lokal sudah berjalan. Audit tidak membaca `.env`, tidak mengubahnya, tidak tersambung ke database lokal, dan tidak menjalankan migrasi.

## Pengujian

- CI workflow memeriksa Composer, migrasi/seeder SQLite dan MySQL, route tiga panel, tes Unit/Feature, build Vite, dan Laravel Dusk.
- Dusk menggunakan database SQLite sementara yang terpisah dari database pengembangan. CI menjalankan migrasi dan seeder pada file database tersebut sebelum menyalakan server browser.
- `tests/Browser/ProgramCreationTest.php` menjalankan login manager, mengisi nama dan tujuan, memilih tanggal dari kalender Filament, menyimpan program, lalu memeriksa nama, tujuan, periode, dan status Draf. Run CI [40170424971](https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon/actions/runs/40170424971) lulus pada commit `7e59f30`. CI juga lulus untuk tes unit, feature, migration SQLite/MySQL, route panel, dan build aset.
- Feature tests HJK-001 tetap memeriksa validasi dan aturan aplikasi di luar browser. Dusk tidak menggantikan tes policy, kepemilikan, dan kondisi penolakan.
- PHP dan Composer belum tersedia pada PATH shell audit. Chrome ditemukan di `C:/Program Files/Google/Chrome/Application/chrome.exe` dan dipakai untuk merender mockup melalui Playwright. Tes aplikasi/Dusk lokal tidak dijalankan; hasil CI menjadi bukti pengujian aplikasi yang disebut di atas.

## Stitch dan bahan kuliah

Proyek Stitch Hijauku Admin UI Batch 01 memiliki 25 frame. Seluruh frame diperiksa dan dirender sebagai mockup pada 25 Oktober 2026; pemetaan 15 modul, aktor, dan PBI dicatat pada [panduan desain UI](ui-design.md). Ini pemeriksaan rancangan, bukan bukti implementasi, tes interaksi, atau penerimaan seluruh layar aplikasi.

Proposal Google Docs direvisi langsung dan presentasi Canva 27 slide disimpan setelah persetujuan PM pada audit ini. Naskah terpisah memuat pembicara, 45 PBI, dan petunjuk visual. Lihat [catatan audit proposal](proposal-audit.md) untuk tautan, koreksi ilmiah, perubahan Jira, serta batas verifikasi. Sprint 2 memiliki start/end date resmi 23 November-27 Desember 2026.

Sumber yang diperiksa: rancangan arsitektur, Ketentuan Proyek PPL, Modul 1 Reguler 2026, proposal tugas besar, materi requirement/Scrum/agile/sprint/version control/issues, Jira, repo GitHub, Stitch, dan `desain.md`. Proposal masih boleh direvisi agar mengikuti roster dan rencana terbaru; keputusan backlog resmi tetap dicatat di Jira.

## Hal yang perlu tindak lanjut manusia

1. Dosen mengonfirmasi perbedaan jumlah anggota antara PDF dan LMS untuk roster lima orang.
2. Pemilik modul memeriksa kompleksitas tiap fitur, kesiapan story, dan kontrak data lintas modul saat refinement.
3. Pemilik modul meninjau implementasi terhadap frame yang sudah dipetakan; mockup tidak membuktikan validasi, ownership, atau workflow aplikasi.
4. PM meninjau PR draft revert #5. HAP-61 Done dan HAP-7 In Progress pada pembacaan akhir; status tersebut tidak otomatis menerima PR draft #4/#5. Pelaksana/PM merekonsiliasi remaining estimate HAP-61 yang masih 4 jam tanpa mengarang waktu aktual.
5. Dusk HJK-001 sudah lulus pada run CI yang ditautkan di atas. Audit ini tidak menjalankan ulang tes aplikasi lokal; setiap commit berikutnya mengikuti hasil CI terbaru pada PR.
