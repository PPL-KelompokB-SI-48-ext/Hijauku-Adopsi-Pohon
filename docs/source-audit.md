# Audit sumber dan status keputusan

Audit ini memisahkan isi dokumen, isi Jira yang terbaca, koreksi dari pemilik proyek, dan rekomendasi. Dokumen kuliah tetap menjadi sumber untuk aturan tugas; Jira menjadi sumber status kerja; Stitch menjadi sumber desain UI. Pemeriksaan Jira dilakukan secara read-only pada 22 Oktober 2026.

## Ringkasan hasil

| Area | Temuan | Status |
| --- | --- | --- |
| Roster | Pemilik proyek mengonfirmasi ada 5 anggota: Yoga, Bitari, Arif, Wira, dan Rofii. Dinda tidak masuk. Sampul proposal dan daftar kolaborator repo juga berisi 5 orang. | Roster proyek terkonfirmasi; pengecualian terhadap aturan kelas belum disahkan dosen |
| Aturan anggota | Ketentuan Proyek PPL meminta kelompok 6–7 orang. | Konflik kebijakan kelas, perlu arahan dosen |
| Dasar jumlah PBI | Satu fitur kompleksitas sedang/tinggi per orang per sprint; tiap fitur minimal 3 PBI. Dua sprint menghasilkan turunan 6 PBI/orang atau 30 untuk lima orang bila tiap orang mengambil satu fitur minimum per sprint. | Aturan tertulis dan hitungan turunannya |
| Hitungan rancangan lama | Rancangan lama menghitung 18 modul × 3 story = 54 PBI dengan asumsi enam anggota dan tiga modul per orang. Rumus tiga modul/orang tidak ada pada bahan kuliah. | Perlu diselaraskan dengan roster baru |
| Jira aktual | 6 epic dan 45 story, HJK-001–045 untuk M01–M15. Lima orang masing-masing mendapat 9 story pada 3 modul; HAP-6 M16–M18 kosong. | Terbaca langsung; tidak sama dengan kriteria HAP-61 |
| HAP-1 dan HAP-61 | HAP-1 adalah epic To Do. HAP-61 adalah task To Do, parent HAP-1, assigned kepada Yoga dan masuk Sprint 0. HAP-61 masih meminta 6 anggota serta 54 story. | Terbaca langsung; acceptance criteria jumlah anggota/story stale terhadap kondisi sekarang |
| Sprint | Sprint 0, Sprint 1, dan Sprint 2 ada; ketiganya `future`. Sprint 0 berakhir 18 Okt 2026 dan Sprint 1 mulai 19 Okt, tetapi status belum berubah per 22 Okt. Sprint 2 menyebut minggu 11–14, sementara materi kuliah menyebut minggu 11–15. | Perlu PM memperbarui status/tanggal dan mengonfirmasi kalender |
| Acceptance criteria story | Semua 45 story yang terbaca punya deskripsi user story dan kriteria penerimaan. Setiap anggota mendapat 3 atau 6 story per sprint; pembagian tersebut memberi satu kelompok tiga story pada tiap sprint. | Struktur cukup rapi; kualitas bisnis tetap perlu review tim per modul |
| Stitch | Proyek `Hijauku Admin UI Batch 01` dapat dibaca. Snapshot menampilkan 25 frame dengan shell publik, Pengelola, Warga, dan Petugas. Instruksi batch terbaru mengecualikan artikel, galeri, dan usulan aksi. | Isi/alur beranda sudah diselaraskan; pemeriksaan visual penuh dan frame lain masih terbuka |
| Repo | Repo publik GitHub sudah ada; audit sebelumnya melihat 5 kolaborator dengan akses. | Sudah ada; cocok dengan roster pemilik proyek |
| Database lokal | `.env` yang ada memakai MySQL di localhost dan database `hijauku`; pemilik menyatakan setup lokal sudah berjalan. `.env` dibiarkan utuh. | Dikonfirmasi pemilik; koneksi tidak diuji/diubah dalam audit ini |
| Engine bersama | Dokumen awal meminta PostgreSQL 18, sementara setup lokal menggunakan MySQL dan CI memakai SQLite. | Belum diputuskan untuk seluruh tim |

## Hitungan yang sedang dipakai

Jira membagi pekerjaan aktif ke lima anggota dengan pola:

| Anggota | Modul | PBI Jira | Sprint 1 | Sprint 2 |
| --- | --- | ---: | ---: | ---: |
| Yoga | M01–M03 | 9 | 6 | 3 |
| Rofii | M04–M06 | 9 | 6 | 3 |
| Bitari | M07–M09 | 9 | 6 | 3 |
| Arif | M10–M12 | 9 | 6 | 3 |
| Wira | M13–M15 | 9 | 3 | 6 |
| **Total** | **M01–M15** | **45** | **27** | **18** |

Jadi hitungan backlog saat ini adalah **5 orang × 3 modul/orang × 3 story/modul = 45 story**. Ini lebih besar daripada batas dasar 30 PBI yang diturunkan dari dua fitur minimal per orang selama dua sprint. Tidak tepat mengatakan satu PBI memiliki tiga modul; modul memuat tiga story.

HAP-61 mencantumkan 6 anggota dan 54 story, tetapi pemilik mengonfirmasi roster lima orang dan Jira aktual menunjukkan 45 story. Ada sembilan story rancangan yang belum dibuat: HJK-046–054 (M16–M18). Rekomendasi audit adalah memperbarui kriteria HAP-61 menjadi lima orang/45 story dan menahan M16–M18 sampai scope itu dibuka lewat keputusan dan backlog. Jangan menambahkan Dinda.

## Isi issue dan rencana Jira

- **HAP-1 — Fondasi & Infrastruktur:** Epic, status To Do, assignee Yoga. Kriteria: autentikasi, RBAC tiga peran, panel Filament di `/app`, dashboard dasar, GitHub Actions + Dusk, dan seeder akun demo. Starter menyediakan panel/login dasar, gate bagi akun aktif pada ketiga role, seeder, CI, tes PHPUnit, dan smoke test Dusk untuk beranda publik. Pembatasan Policy/ownership per resource dan dashboard per peran belum penuh; HAP-1 belum dapat dianggap selesai.
- **HAP-61 — SETUP-001:** Task To Do, parent HAP-1, assignee Yoga, Sprint 0 - Persiapan. Repo starter, dokumentasi dan CI ada di draft PR. Kriteria akses GitHub masih menyebut 6 anggota padahal roster terkonfirmasi 5; audit repo sebelumnya menemukan 5 kolaborator. Target Jira 54 story padahal ada 45. Arsitektur belum disetujui final; Dusk kini berjalan di CI tetapi hanya menguji beranda. Tiket belum selesai.
- **Epic HAP-2–HAP-5:** mengelompokkan M01–M15: Data Dasar & Inventaris (M01–M06), Kegiatan & Penugasan (M07–M09), Adopsi (M10–M12), Perawatan & Pengamatan (M13–M15).
- **Epic HAP-6:** Konten & Publikasi (M16–M18), belum memiliki story. Stitch batch terbaru secara eksplisit menghindari artikel, galeri, dan usulan aksi.
- **Sprint 0:** HAP-61 masuk sprint ini.
- **Sprint 1:** M02, M03, M04, M06, M07, M08, M10, M11, M15. Lima anggota menerima total 27 story.
- **Sprint 2:** M01, M05, M09, M12, M13, M14 + hardening/demo. Lima anggota menerima total 18 story.

## UI Stitch yang dibaca

Proyek Stitch bernama `Hijauku Admin UI Batch 01` dan menampilkan 25 frame. Frame halaman yang tampak mencakup:

- halaman publik: Beranda Publik, Detail Pohon Publik, dan Katalog Pohon;
- panel Warga: Adopsi Saya dan Kesepakatan Saya;
- panel Petugas: Tugas Saya, Realisasi Perawatan, dan Pengamatan Pertumbuhan;
- panel Pengelola: shell navigasi dan dashboard/data M01–M15.

Instruksi batch terakhir menetapkan lima layar tambahan (frame 21–25), memakai identitas merek dan shell per role, tanpa membuka modal/form. Beranda publik memiliki navigasi Beranda/Katalog Pohon/Kegiatan/Masuk, penjelasan bahwa adopsi tidak berbayar, CTA Jelajahi Pohon dan Lihat Kegiatan, serta tiga langkah Pilih Pohon → Ajukan Adopsi → Pantau Perawatan. Panel Warga hanya menampilkan data milik sendiri. Tugas Petugas hanya menampilkan penugasan miliknya. Aksi ditampilkan dalam keadaan tertutup.

Instruksi itu mengecualikan **modul domain** M16 Artikel, M17 Galeri, dan M18 Usulan Aksi. Stitch juga memakai nomor frame M16–M20 untuk Login, Dashboard, dan Katalog; nomor frame ini bukan nomor modul domain yang sama. `resources/views/home.blade.php` kini mengikuti isi dan alur utama frame beranda: nav Beranda/Katalog Pohon/Kegiatan/Masuk, adopsi tanpa biaya, CTA Jelajahi Pohon/Lihat Kegiatan, dan langkah pilih-ajukan-pantau. Pencocokan visual belum penuh; logo/panduan merek tersedia di proyek Stitch dan belum disalin ke repo.

## Dokumen yang ditelaah

- `Rancangan_Arsitektur_Hijauku_PPL.docx` — rancangan lama, 18 modul dan 54 PBI, relasi domain, role, sprint. M02 menyebut kontak pengelola, sementara daftar kolom sites pada rancangan belum jelas soal field itu.
- `Ketentuan Proyek PPL.pdf` — syarat jumlah anggota, sprint formal, kompleksitas fitur, dan minimal PBI per fitur.
- `[SI-48-EXT A-B] Proposal Tugas Besar PPL.pdf` — sampul berisi lima anggota; uraian pembagian tugas masih memuat hitungan enam anggota/54 PBI.
- `MODUL 1 Reguler Praktikum Proyek Perangkat Lunak 2026.pdf` dan lima bahan requirement/agile — materi Jira, requirement, Scrum, dan sprint.
- `desain.md` — salinan deskripsi ticket SETUP-001. File user ini tetap dipertahankan.
- Jira project HAP: daftar issue, HAP-1, HAP-61, seluruh story, dan sprint; diperiksa read-only melalui sesi pengguna.
- Stitch project `Hijauku Admin UI Batch 01`; snapshot frame/narasi dibaca, file desain tidak diekspor.

## Temuan arsitektur dan pekerjaan berikutnya

- Aplikasi satu Laravel monolith dengan Blade publik dan Filament `/app` tetap sesuai arah umum rancangan.
- Starter saat ini membuat tabel/model untuk 18 modul, meski Jira hanya punya story M01–M15. M16–M18 perlu ditandai tertunda atau diberi backlog sebelum dibuka ke pengguna.
- Constraint dasar sudah disiapkan, tetapi kapasitas petak, kuota, kepemilikan, transisi status, verifikasi, dan publikasi tetap memerlukan Policy/service/transaksi.
- Kriteria HAP-1 menyebut Dusk; paket dan job smoke test Dusk kini dipasang di workflow. Cakupan browser masih satu tes beranda publik.
- README, migrasi, seeder, branch, dan workflow CI ada pada draft PR. Pull request belum merged dan status HAP tetap To Do.
- Jangan jalankan migrasi ke MySQL lokal pemilik tanpa izin eksplisit. Pemilik menyatakan DB lokal sudah jalan; `.env` harus tetap dipertahankan.

## Keputusan yang dibutuhkan sebelum menyebut Sprint 0 selesai

1. Perbarui kriteria HAP-61: 5 orang dan 45 story, atau putuskan menambah 9 story untuk M16–M18. Jangan memasukkan Dinda.
2. Minta dosen menyetujui apakah tim lima orang memenuhi ketentuan kelas 6–7 orang.
3. Koreksi status/tanggal sprint: Sprint 0 sudah berakhir dan Sprint 1 telah dimulai menurut tanggal Jira, tetapi keduanya masih `future`; cocokkan durasi Sprint 2 dengan minggu kuliah 11–15.
4. Putuskan apakah M16–M18 tetap deferred dan perbarui starter schema/doc sesuai keputusan tim.
5. Implementasikan halaman publik dan shell per role dari Stitch; gunakan hanya role/data/aksi pada desain, jangan mengarang fitur pembayaran atau publikasi.
6. Perluas cakupan Dusk ke alur login/panel hanya setelah role policies dan halaman terkait tersedia.
7. Pilih engine database bersama. MySQL lokal milik pemilik sudah dikonfigurasi; PostgreSQL 18 tetap tercantum pada rancangan lama.
8. Minta tim meninjau dan menyetujui rev1 sebelum mengganti status dokumen menjadi final.
