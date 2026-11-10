# Konteks proyek Hijauku untuk AI agent

Pakai dokumen ini sebagai ringkasan kerja. Jira tetap sumber status dan tanggal, GitHub sumber kode/branch/PR/CI, sedangkan proposal dan Stitch menjadi sumber rancangan. Status di bawah diperiksa pada 10 November 2026. Periksa lagi sumber live sebelum mengubah status, branch, atau jadwal.

## Identitas dan scope

- Produk: Hijauku - Adopsi Pohon.
- Repository: `PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon`.
- PM: Yoga (`@mygads`).
- Roster: Yoga, Bitari, Arif, Wira, dan Rofii. Dinda bukan anggota.
- Materi kuliah menyebut jumlah kelompok yang berbeda: PDF Ketentuan Proyek menyebut 6-7 orang, teks LMS Sprint 00 menyebut 5-6 orang. Konfirmasi dosen untuk tim lima orang masih terbuka.
- Scope aktif: 5 epic dan 45 story HJK-001 sampai HJK-045 untuk M01 sampai M15. Setiap anggota punya 3 modul × 3 story = 9 story. HAP-6 dan M16-M18 di luar scope; tabel/model terkait yang masih ada hanya scaffold.
- Stack: PHP 8.3+, Laravel 13, Filament 5, Blade, Vite, Eloquent, MySQL 8+. Panel: manager `/app`, field_staff `/petugas`, citizen `/warga`.

## Kondisi Jira terakhir

Pada 10 November, status 45 story adalah 7 Done, 7 In Progress, dan 31 To Do. Story Done: HAP-7, HAP-8, HAP-9, HAP-25, HAP-43, HAP-44, dan HAP-45. Story In Progress: HAP-16, HAP-17, HAP-18, HAP-26, HAP-27, HAP-34, dan HAP-35. Story lain masih To Do. HAP-1 Done; epic HAP-2 sampai HAP-5 In Progress.

HAP-16/17, HAP-26/27, dan HAP-34/35 pernah terbaca Done saat PR terkait masih terbuka. Audit mengoreksi status ke In Progress karena belum dapat memverifikasi scope, acceptance, dan tes pada head branch terbaru. Dalam alur tim, story boleh Done sebelum PR akhir sprint digabung ke `main` jika acceptance dan tes relevan sudah terpenuhi di branch personal yang sudah di-push. Jangan mengandalkan checklist PR atau CI lama sebagai bukti.

| Pemilik | Modul / status story Sprint 1 | PR dan tindak lanjut |
| --- | --- | --- |
| Yoga | M01, HAP-7/8/9 Done. M02 dan M03, HAP-10 sampai HAP-15 To Do. | PR #17 merged; alur M01 ada di `main`. |
| Rofii | M04, HAP-16/17/18 In Progress. | PR #12 terbuka pada branch `Rofii`; kode mencakup jenis pohon dan pemilihan jenis, tetapi judul/body perlu menyebut semua story yang benar-benar dibawa. Branch 7 commit di belakang dan 5 di depan `main`. HAP-18 juga perlu jelas apakah termasuk PR atau belum. |
| Bitari | M07, HAP-25 Done; HAP-26/27 In Progress. | PR #15 terbuka pada branch `bitaridamar`; body hanya menyebut HAP-26, sementara perubahan juga memuat alur tutup/batal HAP-27. Branch 13 commit di belakang dan 2 di depan `main`. |
| Arif | M10, HAP-34/35 In Progress; HAP-36 To Do. | PR #14 terbuka pada branch `arif`; body menyebut HAP-34, sedangkan diff juga memuat keputusan pengelola untuk HAP-35. Branch 7 commit di belakang dan 6 di depan `main`. |
| Wira | M13, HAP-43/44/45 Done. M14 dan M15 masih To Do. | PR #16 merged; HAP-44/45 ada di `main`. Branch personal `ayuwira` perlu mengambil `main` setelah merge. |

Tanggal rencana yang telah lewat tidak diubah saat audit: HAP-16/HAP-34 jatuh tempo 1 November; HAP-17/HAP-26/HAP-35 jatuh tempo 8 November. Pemiliknya perlu mengusulkan tanggal baru yang masuk akal dan memperbarui remaining estimate berdasarkan sisa pekerjaan nyata. Jangan membuat work log untuk mengejar jadwal atau estimasi.

HAP-25 dan HAP-43 sudah merged/Done, tetapi Remaining estimate masing-masing masih 4 jam dan 5 jam. Tidak ada work log. Pemilik perlu memastikan remaining estimate karena pekerjaan yang diterima sudah selesai; angka itu bukan bukti jam aktual.

## Sprint dan branch

- Sprint 0 adalah persiapan minggu 5, 12-18 Oktober, dan sudah ditutup.
- Sprint 1 mengikuti kalender kuliah minggu 6-10, 19 Oktober-22 November. Minggu 6 direncanakan untuk backlog/refinement tanpa coding. Story coding dijadwalkan mulai minggu 7.
- Sprint 2 minggu 11-15, 23 November-27 Desember 2026.
- Tiga story satu modul tetap berada dalam sprint yang sama. Tiga PBI per modul bukan sembilan PBI per fitur; sembilan adalah total story per anggota.
- Satu branch personal per anggota. Nama yang saat ini ada di GitHub: Yoga `yoga`, Bitari `bitaridamar`, Arif `arif`, Wira `ayuwira`, Rofii `Rofii`. PM menerima variasi ejaan/huruf besar; jangan rename branch yang masih dipakai.
- Setiap commit dan PR wajib mencantumkan key HAP huruf kapital. Setelah acceptance dan tes relevan lulus di branch personal yang di-push, pemilik dapat menandai story Done dan memeriksa panel Development Jira. Pada akhir sprint, setiap anggota menyiapkan satu PR yang berisi pekerjaan sprintnya. Pemilik sinkronkan `origin/main`, selesaikan konflik, jalankan tes lagi, dan minta Yoga review. Hanya PM yang merge; urutan PR ditentukan PM. Pertahankan branch personal setelah merge. Jika pemeriksaan integrasi menemukan acceptance gagal, buka kembali story.

## GitHub dan CI

`main` pada audit ini berada di merge commit `8930f589a1cd31b8bbe8621b74fa95e4af0f0e36` dari PR #16.

- PR #17 berisi HAP-8/HAP-9 dan merged. CI run `42774855543` lulus, termasuk Unit, Feature, build frontend, migration SQLite/MySQL, dan Dusk untuk alur M01.
- PR #16 berisi HAP-44/HAP-45 dan merged. CI run `42826130678` lulus.
- PR #18 memperbarui catatan HAP-8/HAP-9 dan merged.
- PR #12 (Rofii), #14 (Arif), dan #15 (Bitari) masih terbuka. Run CI terakhir mereka `41434932370`, `41592514567`, dan `42823882248` lulus pada head lama, sebelum perubahan `main` terbaru. Jangan sebut pemeriksaan itu sebagai CI terkini.
- Sebelum integrasi akhir sprint, pemilik PR #12/#14/#15 harus menyinkronkan `origin/main` ke branch personal, menyelesaikan konflik, meninjau cakupan PR dan acceptance, lalu meminta CI dan review baru. Jangan force-push. Jangan menghapus branch teman. Status story ditentukan dari hasil story pada branch, bukan dari PR sudah merged atau belum.
- PR #1/#2/#4/#7/#9/#11/#13/#16/#17/#18 sudah merged. PR #3/#5/#6 ditutup tanpa merge.

## Implementasi aplikasi

- Fondasi Laravel/Filament, tiga panel, autentikasi, Policy dasar, migration awal, seeder demo, dan CI sudah tersedia.
- M01 HJK-001 sampai HJK-003 sudah di `main`: pengelola mencatat program sebagai draf, mengaktifkan program, dan mengarsipkan program selesai. Validasi, Policy, audit log, tes Feature, dan tes Dusk tersedia.
- M07 HJK-019 pencatatan rencana kegiatan sudah di `main`. HJK-020 dan HJK-021 masih dalam PR terbuka, belum diterima.
- M13 HJK-037 sampai HJK-039 sudah di `main`, termasuk kolom prioritas dengan migration. M14-M15 belum dikerjakan.
- Implementasi di atas memakai panel Filament dan Livewire. Belum ada REST API terpisah untuk CRUD; jangan membuat API tanpa acceptance Jira.
- Shell tiga panel Filament memakai tema bersama yang mengikuti warna, font, sidebar, topbar, dan area konten pada Stitch. Isi M01 belum 1:1 karena filter status, halaman detail, aksi lihat/edit/hapus, data contoh, dan menu modul yang belum diimplementasikan tidak disalin dari mockup. Lihat `docs/ui-design.md`.
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
