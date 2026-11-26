# Konteks proyek Hijauku untuk AI agent

Pakai dokumen ini sebagai ringkasan kerja. Jira tetap sumber status dan tanggal, GitHub sumber kode/branch/PR/CI, sedangkan proposal dan Stitch menjadi sumber rancangan. Status proyek dan Sprint 1 diperiksa ulang pada 26 November 2026. Periksa lagi sumber live sebelum mengubah status, branch, atau jadwal.

## Identitas dan scope

- Produk: Hijauku - Adopsi Pohon.
- Repository: `PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon`.
- PM: Yoga (`@mygads`).
- Roster: Yoga, Bitari, Arif, Wira, dan Rofii. Dinda bukan anggota.
- Materi kuliah menyebut jumlah kelompok yang berbeda: PDF Ketentuan Proyek menyebut 6-7 orang, teks LMS Sprint 00 menyebut 5-6 orang. Konfirmasi dosen untuk tim lima orang masih terbuka.
- Scope aktif: 5 epic dan 45 story HJK-001 sampai HJK-045 untuk M01 sampai M15. Setiap anggota punya 3 modul × 3 story = 9 story. HAP-6 dan M16-M18 di luar scope; tabel/model terkait yang masih ada hanya scaffold.
- Stack: PHP 8.3+, Laravel 13, Filament 5, Blade, Vite, Eloquent, MySQL 8+. Panel: manager `/app`, field_staff `/petugas`, citizen `/warga`.

## Kondisi Jira terakhir

Pada pemeriksaan Jira 26 November 2026, Sprint 1 sudah ditutup dan seluruh 37 item sprint berstatus Done: 30 story, 5 persiapan PR anggota, dan 2 task integrasi/penutupan. Semua story Sprint 1 berasal dari satu modul utuh per anggota, tiga story per modul.

| Pemilik | Modul / status story Sprint 1 | PR dan tindak lanjut |
| --- | --- | --- |
| Yoga | M01-M02, HAP-7/8/9/10/11/12 Done. M03, HAP-13/14 Done dan HAP-15 To Do di Sprint 2. | PR Sprint 1 #20 merged. HAP-13/14 ada di `yoga` dan CI lulus, belum masuk `main` karena dijadwalkan untuk PR integrasi Sprint 2. HAP-62, HAP-71, dan HAP-72 Done. |
| Rofii | M04-M05, HAP-16/17/18/19/20/21 Done. M06, HAP-22 In Progress; HAP-23/24 To Do di Sprint 2. | PR #24 merged. Branch personal `Rofii` dipertahankan; PR lama #12 ditutup tanpa merge. |
| Bitari | M07-M08, HAP-25/26/27/28/29/30 Done. M09, HAP-31 In Progress; HAP-32/33 To Do di Sprint 2. | PR #30 merged. Branch personal `bitaridamar` dipertahankan; PR lama #15 ditutup tanpa merge. |
| Arif | M10-M11, HAP-34/35/36/37/38/39 Done. M12, HAP-40/41 Done dan HAP-42 To Do di Sprint 2. | PR #28 merged. PR #32 berisi pekerjaan Sprint 2 dan masih terbuka pada snapshot audit. PR lama #14 ditutup tanpa merge. |
| Wira | M13-M14, HAP-43/44/45/46/47/48 Done. M15, HAP-49/50 In Progress; HAP-51 To Do di Sprint 2. | PR #29 merged. Branch personal `ayuwira` dipertahankan. |

Sprint 2 aktif dan berisi 15 story, 4 story Done, 4 In Progress, dan 7 To Do pada snapshot audit. Empat task koordinasi Sprint 2 (HAP-67/68/69/73) masih To Do. Due date mengikuti rencana: story minggu 11 jatuh tempo 29 November, story minggu 12 pada 6 Desember, integrasi minggu 13 pada 13 Desember, UAT/rilis minggu 14 pada 20 Desember, dan laporan/presentasi minggu 15 pada 27 Desember 2026. Lihat [laporan audit Sprint 1 dan HAP-14](sprint-1-audit-uat-2026-11-26.md) untuk temuan dan bukti tes.

## Sprint dan branch

- Sprint 0 adalah persiapan minggu 5, 12-18 Oktober, dan sudah ditutup.
- Sprint 1 mengikuti kalender kuliah minggu 6-10, 19 Oktober-22 November. Minggu 6 direncanakan untuk backlog/refinement tanpa coding. Story coding dijadwalkan mulai minggu 7.
- Sprint 1 tetap minggu 6-10, 19 Oktober-22 November 2026. Totalnya 30 story, enam per anggota dari dua modul utuh. Lima belas story dari satu modul per anggota dimajukan dari Sprint 2, dengan satu story tiap anggota due 15 November dan dua lainnya due 22 November.
- Sprint 2 tetap minggu 11-15, 23 November-27 Desember 2026. Lima belas story dijadwalkan selesai pada minggu 12: dua story per anggota di minggu 11 dan satu di minggu 12. Minggu 13 untuk integrasi PR; minggu 14 untuk UAT, pemeriksaan deploy, dan dokumentasi rilis; minggu 15 untuk temuan, laporan akhir, dan persiapan presentasi.
- Tiga story satu modul tetap berada dalam sprint yang sama. Pemindahan modul dilakukan utuh; beberapa alur lintas modul menunggu integrasi dependensinya di Sprint 2. Gunakan schema contract/fixture untuk tes story terisolasi dan lakukan UAT lintas modul setelah seluruh PR tergabung. Tiga PBI per modul bukan sembilan PBI per fitur; sembilan adalah total story per anggota.
- Satu branch personal per anggota. Nama yang diterima PM: Yoga `yoga`, Bitari `bitaridamar`, Arif `arif`, Wira `ayuwira`, Rofii `Rofii`. Jangan rename atau menghapus branch yang masih menyimpan commit unik.
- Setiap commit dan PR wajib mencantumkan key HAP huruf kapital. Acceptance, tes, CI, review, dan penerimaan PM harus lengkap sebelum story Done. Pada akhir sprint, setiap anggota menyiapkan satu PR berisi pekerjaan sprintnya. Pemilik menyinkronkan `main`, menyelesaikan konflik, menjalankan tes lagi, dan meminta Yoga review. Setelah approval PM dan semua syarat branch protection lulus, penulis PR menggabungkannya. Task integrasi ditutup setelah PR terkait merged dan CI lulus. Jika pemeriksaan integrasi menemukan acceptance gagal, buka kembali story.
- Sprint 1 memiliki satu task persiapan PR per anggota, HAP-62 sampai HAP-66, due 22 November, lalu HAP-71/HAP-72 untuk integrasi dan penutupan sprint oleh PM. HAP-70 dihapus karena menjadi duplikasi; pemeriksaan kesiapan, sinkronisasi `main`, konflik, schema, UI, test, dan CI sudah dimasukkan ke instruksi task integrasi.
- Sprint 2 memiliki HAP-67/HAP-68 untuk integrasi PR pada minggu 13, HAP-69 untuk UAT/deploy/dokumentasi rilis pada minggu 14, dan HAP-73 untuk temuan UAT, laporan akhir, serta persiapan presentasi pada minggu 15. Hubungan Jira **blocks**: HAP-62/63/64 sebelum HAP-71; HAP-65/66 dan HAP-71 sebelum HAP-72; HAP-72 sebelum HAP-67; lalu HAP-67 sebelum HAP-68, HAP-68 sebelum HAP-69, dan HAP-69 sebelum HAP-73.

## GitHub dan CI

Snapshot GitHub 26 November 2026: `origin/main` berada di `8bc3b1b925d7af87a922f9ada8a1fd9b5bb7c160`; CI run [45127557638](https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon/actions/runs/45127557638) lulus. PR anggota Sprint 1 #20, #24, #30, #28, dan #29 sudah merged dan CI lulus pada head masing-masing. PR #31 adalah pembaruan dokumentasi, bukan PR fitur tambahan.

Workflow CI memeriksa push ke `main` dan branch personal, PR yang menuju `main`, lalu hasil setelah merge ke `main`. Perubahan ini masuk lewat PR #25. Push branch memberi umpan balik lebih awal; pemeriksaan PR tetap menjadi gerbang merge. Branch protection `main` mewajibkan `install-and-build`, satu approval, penyelesaian percakapan, dan branch yang sudah mengikuti `main` terbaru. Setelah PR anggota lain digabung, pemilik PR berikutnya perlu menyinkronkan branch, menunggu CI baru, lalu meminta review lagi. Rincian konflik dan langkah perbaikan ada di [panduan CI](ci-workflow.md).

- PR #17 berisi HAP-8/HAP-9 dan merged. CI run `42774855543` lulus, termasuk Unit, Feature, build frontend, migration SQLite/MySQL, dan Dusk untuk alur M01.
- PR #16 berisi HAP-44/HAP-45 dan merged. CI run `42826130678` lulus.
- PR #18 memperbarui catatan HAP-8/HAP-9 dan merged.
- PR #19 menyelaraskan shell tiga panel Filament dengan tema Stitch dan sudah merged.
- Setelah tiap PR Sprint 1 merged, main CI terakhir lulus pada SHA `8bc3b1b`. PR #24, #28, #29, dan #30 lulus CI pada head PR masing-masing; PR #20 juga lulus CI sebelum merge. Detail PR dan statusnya dirangkum di [laporan audit](sprint-1-audit-uat-2026-11-26.md).
- Pull Request #32 Arif adalah pekerjaan Sprint 2 dan masih terbuka pada snapshot 26 November. Audit Sprint 1 tidak menganggapnya blocker untuk modul Sprint 1 yang telah merged.
- Pertahankan branch personal anggota. Branch `rofii` huruf kecil adalah branch terpisah yang masih memiliki commit unik; jangan hapus atau gabungkan sebelum pemilik memeriksa isinya.
- PR fitur yang sudah merged mencakup #2/#4/#17/#18 untuk M01, #20 untuk M02, #24 untuk M04-M05, #30 untuk M07-M08, #28 untuk M10-M11, dan #29 untuk M13-M14. PR #31 hanya memperbarui README. PR #12/#14/#15 ditutup tanpa merge; branch personal dan commit unik tetap dipertahankan.

## Implementasi aplikasi

- Fondasi Laravel/Filament, tiga panel, autentikasi, Policy dasar, migration awal, seeder demo, dan CI sudah tersedia.
- M01 HJK-001 sampai HJK-003 sudah di `main`: pengelola mencatat program sebagai draf, mengaktifkan program, dan mengarsipkan program selesai. Validasi, Policy, audit log, tes Feature, dan tes Dusk tersedia.
- M02 HJK-004 sampai HJK-006 sudah masuk `main` melalui PR #20. Resource lokasi memakai shell dan tema Filament bersama, `SitePolicy`, validasi koordinat, filter program, pencarian, pagination 20, pembatasan perubahan program bila lokasi memiliki petak, audit log alasan/nilai sebelum-sesudah, serta perlindungan hapus untuk lokasi yang masih dipakai. Schema `sites` sudah ada, jadi tidak ada migration baru. Jira menampilkan HAP-10/11/12 Done. UI mengikuti bagian yang tersedia di frame Stitch M02; form/detail tidak memiliki frame pembanding dan gap filter lanjutan dijelaskan di `docs/ui-design.md`.
- M03 HJK-007/HAP-13 dan HJK-008/HAP-14 sudah selesai pada branch `yoga` dan CI terbaru lulus, tetapi belum masuk `main`; HJK-009/HAP-15 masih To Do. Data lintas modul M03 baru diuji penuh setelah PR Sprint 2 terintegrasi.
- M04-M05 HJK-010 sampai HJK-015, M07-M08 HJK-019 sampai HJK-024, M10-M11 HJK-028 sampai HJK-033, dan M13-M14 HJK-037 sampai HJK-042 sudah masuk `main` lewat PR Sprint 1 #24, #30, #28, dan #29.
- Sprint 2 masih menyisakan modul M06, M09, M12, dan M15. Status per story dan pemiliknya ada pada Jira; jangan menganggap model atau scaffold berarti alur pengguna selesai.
- Implementasi di atas memakai panel Filament dan Livewire. Belum ada REST API terpisah untuk CRUD; jangan membuat API tanpa acceptance Jira.
- PR #19 menyatukan tema dan shell tiga panel dengan acuan Stitch. Untuk frame yang dipetakan, target visual 1:1 berlaku pada elemen yang ditunjukkan. Form/detail tanpa frame memakai pola Filament reusable dan gap dicatat. Isi M01 tetap mengikuti acceptance; lihat `docs/ui-design.md`.
- Gate panel tidak cukup untuk membatasi record. Resource baru tetap memerlukan Policy, query ownership, validasi, tes akses, dan transaksi untuk aturan kuota/kapasitas/alokasi.
- Simpan bukti foto secara privat. Pengirim laporan tidak boleh memverifikasi laporannya sendiri.

## Keamanan kerja lokal

- Jangan membaca, menimpa, atau membagikan `.env` milik pemilik. Jangan menjalankan migration, seeder, `migrate:fresh`, atau Dusk ke MySQL pribadi/bersama.
- Tes memakai SQLite in-memory dari phpunit atau file SQLite terisolasi. Development MySQL hanya pada database khusus proyek.
- Pertahankan `desain.md` dan `.playwright-mcp/`. Jangan commit credential, token, data pribadi, atau foto nyata.
- Untuk proposal, video, atau slide, perlakukan sebagai rancangan awal. Jangan memasukkan riwayat status issue, PR, atau implementasi internal sebagai isi proposal. Baca `docs/proposal-audit.md` hanya jika tugas memang terkait dokumen tersebut.

## Urutan baca agent

1. `AGENTS.md`.
2. `docs/project-context.md` dan `docs/source-audit.md`.
3. `docs/architecture.md`, `docs/database.md`, `docs/requirements.md`, serta acceptance Jira yang ditugaskan.
4. `docs/jira-workflow.md` dan `CONTRIBUTING.md` sebelum mengubah backlog atau Git.
5. `docs/ui-design.md` untuk layar yang terkait.

Kerjakan satu story yang diminta. Lapor file yang berubah, acceptance yang dipenuhi, tes yang benar-benar dijalankan, serta gap yang masih ada. Jangan merge tanpa arahan PM dan jangan mengarang jam aktual.
