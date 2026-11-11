# Panduan desain antarmuka Hijauku

## Sumber desain

Referensi utama adalah [Hijauku Admin UI Batch 01 di Stitch](https://stitch.google.com/projects/162416937856577753). Proyek yang diperiksa berisi 25 frame untuk beranda publik dan alur tiga peran. Frame 21 berjudul **Beranda Publik**; frame 24 memperlihatkan **Kesepakatan Saya (Warga, M11)**.

Arahan yang terlihat di kanvas Stitch menetapkan batas berikut:

- Antarmuka aktif hanya untuk M01-M15. Jangan menambahkan Artikel, Galeri, atau Usulan Aksi dari M16-M18 ke navigasi, sidebar, atau footer.
- Gunakan tiga area akses: Pengelola, Petugas, dan Warga. Panel role bukan pengganti pemeriksaan Policy dan kepemilikan data.
- Kondisi awal drawer, modal, accordion, dan split view harus tertutup. Aksi cepat tidak boleh menyamarkan status tugas atau perawatan.
- Label, status, dan nilai seed harus berasal dari kamus data yang disepakati, bukan teks sementara yang tampak seperti data produksi.

## Keadaan implementasi yang sudah diverifikasi

| Area | Implementasi sekarang | Batas saat ini |
| --- | --- | --- |
| Beranda publik | `resources/views/home.blade.php` memakai identitas hijau, headline Frame 21, ilustrasi pohon, CTA, dan alur tiga langkah | Belum tersambung ke katalog, hitungan, atau data pohon. Angka dan daftar pada frame Stitch tidak boleh disajikan sebagai fakta produksi sebelum sumber datanya tersedia. |
| M01, Pengelola | `main` menyediakan daftar, pembuatan draf, aktivasi, dan pengarsipan program untuk HJK-001-HJK-003. PR #20 merapikan daftar dengan filter status. | Aktivasi hanya muncul pada draf dan meminta alasan. Pengarsipan hanya muncul pada program selesai dan tidak menghapus lokasi/pohon. Edit dan hapus tidak termasuk acceptance saat ini. |
| M02, Pengelola | HAP-10-HAP-12 dikembangkan di branch personal `yoga` dan diajukan lewat PR #20. Toolbar pencarian/filter dan tabel mengikuti bagian utama frame **M02 - Lokasi Tanam**. | Frame menampilkan kontrol filter lanjutan yang belum punya perilaku pada acceptance, jadi belum dibuat. Kode lokasi contoh tidak tersedia di skema; halaman memakai ID database sebenarnya. |
| M07, Pengelola | Pencatatan rencana kegiatan HJK-019 tersedia di `main` | HJK-020/021 masih perlu implementasi dan penerimaan. PR #15 ditutup tanpa merge, jadi diff PR lama bukan fitur yang tersedia bagi pengguna `main`. |
| M13, Pengelola | Rencana perawatan, prioritas, koreksi, dan pembatalan HJK-037-HJK-039 tersedia di `main` | Realisasi HJK-040-HJK-042 dan pengamatan HJK-043-HJK-045 masih belum dikerjakan. |
| Petugas dan Warga | Panel Filament `/petugas` dan `/warga` sudah memiliki login/dashboard dasar | Resource kerja dan alur di panel ini harus dibuat per story. PR #14 untuk M10 ditutup tanpa merge; pekerjaan yang belum masuk `main` harus diverifikasi melalui story dan PR pengganti. |

## Kesesuaian visual M01 dengan Stitch

Panel Filament sekarang memakai satu tema bersama yang mengikuti shell pada Stitch. Tema ini dipakai di `/app`, `/petugas`, dan `/warga`, supaya warna, ukuran navigasi, dan tipografi tidak berkembang berbeda antar panel.

| Bagian | Acuan bersama |
| --- | --- |
| Font | Inter untuk panel dan halaman publik |
| Warna utama | `#15803D`, hover `#14532D` |
| Latar isi | `#F8FAFC`; kartu dan sidebar `#FFFFFF` |
| Teks | `#131B2E`; teks sekunder `#64748B`; garis `#E2E8F0` |
| Sidebar | Lebar 256 px, putih, menu berjarak tetap, aktif hijau |
| Topbar | Tinggi 64 px, putih transparan, breadcrumb dan konteks akun |
| Konten | Lebar penuh, padding 24 px; layar kecil memakai padding 16 px |
| Komponen | Radius kartu 12 px, field 8 px, tombol 40 px, ikon sekitar 18 px |

Tema ada di `resources/css/filament/theme.css`; konfigurasi tiga panel dipakai ulang lewat `app/Providers/Filament/Concerns/ConfiguresHijaukuPanel.php`. Identitas logo berasal dari aset Stitch `public/images/hijauku-brand-sheet.png`. Render hook Filament menambahkan label navigasi, breadcrumb topbar, serta nama dan peran akun tanpa mengganti perilaku autentikasi Filament. Untuk halaman baru, gunakan pola form, tabel, aksi, notifikasi, dan layout Filament yang reusable; hindari salin-tempel shell berbeda di tiap modul.

Shell mengikuti ukuran, warna, font, dan identitas utama pada frame, tetapi belum identik sepenuhnya. Sidebar hanya menampilkan halaman yang sudah punya Resource, dan topbar belum memakai tombol notifikasi/bantuan dekoratif dari beberapa frame. Menu yang belum punya halaman tidak dibuat sebagai tautan mati.

Pada M01, toolbar kini menyediakan pencarian dan filter status. Tabel menampilkan kolom yang dipakai requirement. Aksi tetap dibatasi pada transisi program yang diterima di Jira, jadi tombol lihat/edit pada contoh frame tidak ditambahkan. Data contoh di frame juga tidak menggantikan data database aplikasi.

Pada M02, tabel memakai pencarian, filter program, pagination 20, dan aksi yang dibutuhkan story. Ikon lokasi, lencana program, serta chip koordinat sudah diberi treatment yang mendekati frame. Kontrol **Filter Lanjutan** belum dibuat karena belum ada kriteria Jira yang menjelaskan filter tambahan tersebut. Daftar pada ponsel dapat digulir mendatar agar kolom tidak saling menimpa. Form dan modal detail/edit belum punya frame Stitch sendiri, jadi tidak diklaim 1:1.

Untuk setiap story baru, periksa layar pada ukuran desktop dan ponsel, termasuk drawer sidebar, tabel, form, status kosong, dan pesan validasi. Tes satu layar tidak membuktikan seluruh aplikasi sudah responsif atau identik. Catat bukti visual per frame pada PR saat layar tersebut dikerjakan.

Pada 25 Oktober 2026, seluruh 25 frame diperiksa sebagai rancangan dan dirender untuk proposal/presentasi. Pemeriksaan mencakup identitas layar, aktor, scope, serta pemetaan PBI; ini belum merupakan tes responsif, aksesibilitas, interaksi, atau penerimaan UI aplikasi yang sudah diimplementasikan.

## Palet dan batas penerapan

| Warna | Penggunaan rancangan |
| --- | --- |
| `#15803D` | Hijau utama, tombol utama, dan menu aktif |
| `#14532D` | Hijau gelap untuk hover |
| `#B64C62` | Aksen sekunder pada frame tertentu |
| `#131B2E` | Teks utama |
| `#64748B` | Teks sekunder |
| `#F8FAFC` | Latar halaman |
| `#FFFFFF` | Sidebar, topbar, dan kartu |
| `#E2E8F0` | Garis pemisah dan batas field |

Nilai ini disalin dari kode frame Stitch yang dibagikan. Palet status tetap memakai warna sukses, informasi, peringatan, dan bahaya dengan label teks agar status tidak bergantung pada warna saja.

Gunakan komponen Filament reusable untuk Resource, form, tabel, modal, notifikasi, dan validasi. Untuk frame Stitch yang dipetakan ke story, samakan tampilan 1:1 pada bagian yang tercakup acceptance: struktur, jarak, ukuran, warna, font, ikon, dan hierarki. Catat perbedaan yang disebabkan batas acceptance atau ketiadaan frame. Jangan menambah perilaku yang belum tercantum di Jira hanya demi menyalin seluruh kontrol mockup. Halaman tanpa frame mengikuti tema dan komponen bersama; jangan menyatakan 1:1 tanpa frame pembanding. Status harus memiliki label, bukan mengandalkan warna saja.

M08 mengikuti HAP-28/HAP-30: warga mendaftar dan menarik pendaftaran sendiri; pengelola mencatat kehadiran pada HAP-29. Petugas menangani penugasan M09, bukan menjadi pendaftar M08. Dua teks lama pada presentasi dan satu uraian proposal dikoreksi agar tidak memperluas aktor tanpa requirement.

## Pemetaan seluruh modul untuk proposal dan video

| Modul | Pemilik | PBI / Jira | Frame yang ditunjukkan |
| --- | --- | --- | --- |
| M01 | Yoga | HJK-001-003 / HAP-7-9 | M01 Program Penghijauan |
| M02 | Yoga | HJK-004-006 / HAP-10-12 | M02 Lokasi Tanam |
| M03 | Yoga | HJK-007-009 / HAP-13-15 | M03 Petak Tanam |
| M04 | Rofii | HJK-010-012 / HAP-16-18 | M04 Jenis Pohon |
| M05 | Rofii | HJK-013-015 / HAP-19-21 | M05 Kelompok Penanaman |
| M06 | Rofii | HJK-016-018 / HAP-22-24 | M06 Inventaris Pohon |
| M07 | Bitari | HJK-019-021 / HAP-25-27 | M07 Kegiatan Lapangan |
| M08 | Bitari | HJK-022-024 / HAP-28-30 | M08 Pendaftaran Kegiatan |
| M09 | Bitari | HJK-025-027 / HAP-31-33 | Frame 25 Tugas Saya, petugas |
| M10 | Arif | HJK-028-030 / HAP-34-36 | Frame 23 Adopsi Saya, warga |
| M11 | Arif | HJK-031-033 / HAP-37-39 | Frame 24 Kesepakatan Saya, warga |
| M12 | Arif | HJK-034-036 / HAP-40-42 | M12 Laporan Masalah Pohon |
| M13 | Wira | HJK-037-039 / HAP-43-45 | M13 Rencana Perawatan |
| M14 | Wira | HJK-040-042 / HAP-46-48 | M14 Realisasi Perawatan |
| M15 | Wira | HJK-043-045 / HAP-49-51 | M15 Pengamatan Pertumbuhan |

Frame pengelola M09-M11 juga ada pada proposal. Frame 16 adalah login; 17-19 dashboard pengelola/petugas/warga; 20 katalog publik; 21 beranda; 22 detail pohon. Angka 16-25 adalah nomor layar, bukan modul bisnis baru M16-M25.

Render yang dipakai pada presentasi tersedia di `docs/assets/mockups/`, dengan label data contoh. Diagram proposal ada di `docs/diagrams/`. Kolom/aksi dalam mockup adalah sasaran desain, bukan bukti migration atau workflow sudah tersedia. Misalnya prioritas M13 memerlukan migration HAP-44.

## Cara membawa frame ke kode

1. Catat nomor/judul frame Stitch dan tautkan ke HJK story serta modul M01-M15 di Jira.
2. Pastikan aktor, route/panel, aksi, status awal, kondisi kosong, validasi, dan hasil sukses/gagal cocok dengan acceptance criteria story.
3. Implementasikan tampilan di panel role yang tepat. Taruh Resource, Page, dan Widget dalam namespace panel masing-masing; jangan mengandalkan menu untuk mengamankan data.
4. Terapkan Policy, scope query, validasi field, dan service transaksi sebelum menghubungkan data nyata. `viewAny` saja tidak membatasi baris yang dikirim ke browser.
5. Pakai komponen, warna, jarak, dan hierarki yang konsisten dengan frame. Simpan aset proyek pada `public/images/` atau `resources/images/` dengan nama yang menjelaskan isinya.
6. Sertakan keadaan loading, kosong, error, berhasil, dan layar kecil. Jangan membuka modal/drawer otomatis tanpa tindakan pengguna.
7. Minta pemilik modul membandingkan hasil dengan frame Stitch pada PR; lampirkan tangkapan layar hanya jika diperlukan untuk menilai perbedaan visual.

## Pemetaan M01

- HJK-001 sampai HJK-003 memakai frame **M01 - Program Penghijauan** untuk halaman daftar di panel Pengelola.
- Frame menampilkan nama program, ringkasan tujuan, periode, status, dan area aksi. Tombol **Tambah Program** membuka alur pembuatan.
- Stitch tidak menyediakan frame formulir pembuatan program. HJK-001 memakai halaman CreateRecord Filament 5 dengan validasi di server. Formulir menggunakan tema bersama, tetapi susunan form tetap mengikuti komponen Filament dan belum memiliki frame pembanding khusus.
- HJK-002 dan HJK-003 juga memakai komponen standar Filament 5 pada daftar yang sama, karena tidak ada frame khusus untuk aktivasi atau pengarsipan. Modal mencatat alasan, sedangkan label status tetap terbaca tanpa mengandalkan warna. Kesesuaian visual akhir tetap diperiksa pada PR.

## Pemetaan M02

- HJK-004 sampai HJK-006 memakai frame **M02 - Lokasi Tanam** sebagai acuan daftar di panel Pengelola. Implementasi branch personal `yoga` diajukan melalui PR #20; gunakan riwayat PR untuk melihat perubahan yang masuk ke `main`.
- Halaman mengikuti urutan utama frame: judul dan keterangan, tombol tambah lokasi, pencarian, filter program, kolom nama lokasi/program/alamat/koordinat, aksi, dan pagination 20 baris. Pada lebar 390 px, panel tidak melebar keluar viewport; tabel digulir mendatar agar kolom tidak bertumpuk.
- Frame menampilkan kode seperti `SIT-BDG-001`, tetapi tabel `sites` tidak mempunyai kolom kode. Implementasi menampilkan ID database yang nyata dan tidak membuat kode contoh seolah-olah data resmi.
- Stitch hanya memberi frame daftar. Form tambah memakai halaman Filament; ubah dan detail memakai modal Filament dengan form yang sama. Karena tidak ada frame pembanding untuk ketiga tampilan itu, keduanya mengikuti komponen bersama tanpa klaim 1:1.
- Perubahan lokasi meminta alasan dan menyimpan aktor serta nilai sebelum/sesudah pada `activity_logs`. Penghapusan ditolak bila lokasi masih dipakai petak atau kegiatan. Tidak ada REST API baru karena acceptance HJK-004-HJK-006 memakai alur panel Filament/Livewire.

## Batas scope dan penerimaan

- Beranda publik adalah template; ia tidak boleh mengklaim jumlah pohon/lokasi dari contoh visual sebagai data aktual.
- Pekerjaan layar setelah starter harus menunjuk ke HJK story aktif. Detail interaksi M16-M18 menunggu keputusan scope dan tidak masuk implementasi sekarang.
- Satu layar diterima ketika tampilan, akses role, query kepemilikan, validasi, status kosong/error, dan acceptance criteria story selaras.
- Perubahan besar pada alur Stitch atau desain memerlukan pembaruan story/acceptance criteria dan catatan PR; perubahan arsitektur lintas modul dicatat sebagai ADR.
