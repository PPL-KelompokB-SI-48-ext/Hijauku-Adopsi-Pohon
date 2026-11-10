# Konteks proyek Hijauku untuk AI agent

Pakai dokumen ini sebagai ringkasan kerja. Jira tetap sumber status dan tanggal, GitHub sumber kode/branch/PR/CI, sedangkan proposal dan Stitch menjadi sumber rancangan. Status di bawah diperiksa pada 11 November 2026. Periksa lagi sumber live sebelum mengubah status, branch, atau jadwal.

## Identitas dan scope

- Produk: Hijauku - Adopsi Pohon.
- Repository: `PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon`.
- PM: Yoga (`@mygads`).
- Roster: Yoga, Bitari, Arif, Wira, dan Rofii. Dinda bukan anggota.
- Materi kuliah menyebut jumlah kelompok yang berbeda: PDF Ketentuan Proyek menyebut 6-7 orang, teks LMS Sprint 00 menyebut 5-6 orang. Konfirmasi dosen untuk tim lima orang masih terbuka.
- Scope aktif: 5 epic dan 45 story HJK-001 sampai HJK-045 untuk M01 sampai M15. Setiap anggota punya 3 modul × 3 story = 9 story. HAP-6 dan M16-M18 di luar scope; tabel/model terkait yang masih ada hanya scaffold.
- Stack: PHP 8.3+, Laravel 13, Filament 5, Blade, Vite, Eloquent, MySQL 8+. Panel: manager `/app`, field_staff `/petugas`, citizen `/warga`.

## Kondisi Jira terakhir

Pada pemeriksaan Jira 11 November 2026, status 45 story adalah 7 Done, 10 In Progress, dan 28 To Do. Story Done: HAP-7/8/9, HAP-25, dan HAP-43/44/45. Story In Progress: HAP-10/11/12, HAP-16/17/18, HAP-26/27, dan HAP-34/35. Story lain masih To Do. HAP-1 Done; epic HAP-2 sampai HAP-5 In Progress. Task integrasi baru tidak menambah jumlah 45 story.

HAP-16/17, HAP-26/27, dan HAP-34/35 masih In Progress sampai acceptance, tes, CI, review, dan penerimaan PM bisa diverifikasi. Jangan mengandalkan checklist PR atau CI lama sebagai bukti.

| Pemilik | Modul / status story Sprint 1 | PR dan tindak lanjut |
| --- | --- | --- |
| Yoga | M01, HAP-7/8/9 Done. M02, HAP-10/11/12 In Progress di Sprint 1; M03, HAP-13/14/15 To Do di Sprint 2. | HJK-001/002/003 dan shell Filament sudah di `main`. HJK-004/005/006 sudah dikerjakan di branch personal `yoga` dan tercakup dalam PR #20 yang siap ditinjau. |
| Rofii | M04, HAP-16/17/18 In Progress. M05, HAP-19/20/21 To Do di Sprint 1; M06, HAP-22/23/24 To Do di Sprint 2. | PR #12 ditutup tanpa merge. Branch `Rofii` 5 commit di depan/16 di belakang `main`; review lama meminta perbaikan policy dan tes. |
| Bitari | M07, HAP-25 Done; HAP-26/27 In Progress. M08, HAP-28/29/30 To Do di Sprint 1; M09, HAP-31/32/33 To Do di Sprint 2. | PR #15 ditutup tanpa merge. Branch `bitaridamar` 2 commit di depan/22 di belakang `main`; PR lama belum mendapat review. |
| Arif | M10, HAP-34/35 In Progress; HAP-36 To Do. M11, HAP-37/38/39 To Do di Sprint 1; M12, HAP-40/41/42 To Do di Sprint 2. | PR #14 ditutup tanpa merge. Branch `arif` 6 commit di depan/16 di belakang `main`; review lama meminta tes, scope query, dan filter pohon tersedia. |
| Wira | M13, HAP-43/44/45 Done. M14, HAP-46/47/48 To Do di Sprint 1; M15, HAP-49/50/51 To Do di Sprint 2. | PR #13/#16 merged. Pertahankan branch `ayuwira`; periksa commit yang belum masuk `main` sebelum penutupan sprint. |

Tanggal yang sudah lewat dipertahankan sebagai riwayat: HAP-16/HAP-34 jatuh tempo 1 November; HAP-17/HAP-26/HAP-35 jatuh tempo 8 November. Pemilik dan PM menyepakati replan serta memperbarui remaining estimate berdasarkan sisa kerja nyata. Jangan membuat work log untuk mengejar jadwal atau estimasi.

HAP-25 dan HAP-43 sudah merged/Done, tetapi Remaining estimate masing-masing masih 4 jam dan 5 jam. Tidak ada work log. Pemilik perlu memastikan remaining estimate karena pekerjaan yang diterima sudah selesai; angka itu bukan bukti jam aktual.

## Sprint dan branch

- Sprint 0 adalah persiapan minggu 5, 12-18 Oktober, dan sudah ditutup.
- Sprint 1 mengikuti kalender kuliah minggu 6-10, 19 Oktober-22 November. Minggu 6 direncanakan untuk backlog/refinement tanpa coding. Story coding dijadwalkan mulai minggu 7.
- Sprint 1 tetap minggu 6-10, 19 Oktober-22 November 2026. Totalnya 30 story, enam per anggota dari dua modul utuh. Lima belas story dari satu modul per anggota dimajukan dari Sprint 2, dengan satu story tiap anggota due 15 November dan dua lainnya due 22 November.
- Sprint 2 tetap minggu 11-15, 23 November-27 Desember 2026. Lima belas story dijadwalkan selesai pada minggu 12: dua story per anggota di minggu 11 dan satu di minggu 12. Minggu 13 untuk integrasi PR; minggu 14 untuk UAT, pemeriksaan deploy, dan dokumentasi rilis; minggu 15 untuk temuan, laporan akhir, dan persiapan presentasi.
- Tiga story satu modul tetap berada dalam sprint yang sama. Pemindahan modul dilakukan utuh; beberapa alur lintas modul menunggu integrasi dependensinya di Sprint 2. Gunakan schema contract/fixture untuk tes story terisolasi dan lakukan UAT lintas modul setelah seluruh PR tergabung. Tiga PBI per modul bukan sembilan PBI per fitur; sembilan adalah total story per anggota.
- Satu branch personal per anggota. Nama yang diterima PM: Yoga `yoga`, Bitari `bitaridamar`, Arif `arif`, Wira `ayuwira`, Rofii `Rofii`. Jangan rename atau menghapus branch yang masih menyimpan commit unik.
- Setiap commit dan PR wajib mencantumkan key HAP huruf kapital. Acceptance, tes, CI, review, dan penerimaan PM harus lengkap sebelum story Done. Pada akhir sprint, setiap anggota menyiapkan satu PR berisi pekerjaan sprintnya. Pemilik menyinkronkan `main`, menyelesaikan konflik, menjalankan tes lagi, dan meminta Yoga review. Hanya PM yang merge. Task integrasi ditutup setelah PR terkait merged dan CI lulus. Jika pemeriksaan integrasi menemukan acceptance gagal, buka kembali story.
- Sprint 1 memiliki satu task persiapan PR per anggota, HAP-62 sampai HAP-66, due 22 November, lalu HAP-71/HAP-72 untuk integrasi dan penutupan sprint oleh PM. HAP-70 dihapus karena menjadi duplikasi; pemeriksaan kesiapan, sinkronisasi `main`, konflik, schema, UI, test, dan CI sudah dimasukkan ke instruksi task integrasi.
- Sprint 2 memiliki HAP-67/HAP-68 untuk integrasi PR pada minggu 13, HAP-69 untuk UAT/deploy/dokumentasi rilis pada minggu 14, dan HAP-73 untuk temuan UAT, laporan akhir, serta persiapan presentasi pada minggu 15. Hubungan Jira **blocks**: HAP-62/63/64 sebelum HAP-71; HAP-65/66 dan HAP-71 sebelum HAP-72; HAP-72 sebelum HAP-67; lalu HAP-67 sebelum HAP-68, HAP-68 sebelum HAP-69, dan HAP-69 sebelum HAP-73.

## GitHub dan CI

`main` pada pemeriksaan 11 November setelah PR #19 berada di merge commit `bee4a0d2dc3033553403ba88f3e086b5290050a1`.

- PR #17 berisi HAP-8/HAP-9 dan merged. CI run `42774855543` lulus, termasuk Unit, Feature, build frontend, migration SQLite/MySQL, dan Dusk untuk alur M01.
- PR #16 berisi HAP-44/HAP-45 dan merged. CI run `42826130678` lulus.
- PR #18 memperbarui catatan HAP-8/HAP-9 dan merged.
- PR #19 menyelaraskan shell tiga panel Filament dengan tema Stitch dan sudah merged.
- PR #12, #14, dan #15 sudah ditutup tanpa merge. PR #20 Yoga terbuka dan siap ditinjau dari `yoga` ke `main`; pemeriksaan CI terakhir yang tersedia lulus dan branch tidak memiliki konflik. GitHub masih meminta satu approving review sesuai perlindungan branch. Run CI lama pada PR teman bukan bukti untuk head terbaru mereka.
- Pemilik mempertahankan commit unik, menyinkronkan `main`, menyelesaikan konflik sendiri, memperbaiki temuan review yang masih relevan, menjalankan CI, lalu membuka PR pengganti. Jangan force-push atau menghapus branch teman.
- PR #1/#2/#4/#7/#9/#11/#13/#16/#17/#18 sudah merged. PR #3/#5/#6 ditutup tanpa merge.

## Implementasi aplikasi

- Fondasi Laravel/Filament, tiga panel, autentikasi, Policy dasar, migration awal, seeder demo, dan CI sudah tersedia.
- M01 HJK-001 sampai HJK-003 sudah di `main`: pengelola mencatat program sebagai draf, mengaktifkan program, dan mengarsipkan program selesai. Validasi, Policy, audit log, tes Feature, dan tes Dusk tersedia.
- M02 HJK-004 sampai HJK-006 sudah diimplementasikan di branch `yoga` dan diajukan lewat PR #20. Resource lokasi memakai shell dan tema Filament bersama, `SitePolicy`, validasi koordinat, filter program, pencarian, pagination 20, pembatasan perubahan program bila lokasi memiliki petak, audit log alasan/nilai sebelum-sesudah, serta perlindungan hapus untuk lokasi yang masih dipakai. Schema `sites` sudah ada, jadi tidak ada migration baru. CI PR #20 lulus; story tetap In Progress sampai pemeriksaan/approval, integrasi, dan penerimaan PM selesai.
- M07 HJK-019/HAP-25 sudah Done. HJK-020/HAP-26 dan HJK-021/HAP-27 masih In Progress; PR lama #15 ditutup tanpa merge.
- M13 HJK-037 sampai HJK-039 sudah di `main`, termasuk kolom prioritas dengan migration. M14-M15 belum dikerjakan.
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
