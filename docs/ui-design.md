# Panduan desain antarmuka Hijauku

## Sumber desain

Referensi utama adalah [Hijauku Admin UI Batch 01 di Stitch](https://stitch.google.com/projects/162416937856577753). Proyek yang diperiksa berisi 25 frame untuk beranda publik dan alur tiga peran. Frame 21 berjudul **Beranda Publik**; frame 24 memperlihatkan **Kesepakatan Saya (Warga, M11)**.

Arahan yang terlihat di kanvas Stitch menetapkan batas berikut:

- Antarmuka aktif hanya untuk M01-M15. Jangan menambahkan Artikel, Galeri, atau Usulan Aksi dari M16-M18 ke navigasi, sidebar, atau footer.
- Gunakan tiga area akses: Pengelola, Petugas, dan Warga. Panel role bukan pengganti pemeriksaan Policy dan kepemilikan data.
- Kondisi awal drawer, modal, accordion, dan split view harus tertutup. Aksi cepat tidak boleh menyamarkan status tugas atau perawatan.
- Label, status, dan nilai seed harus berasal dari kamus data yang disepakati, bukan teks sementara yang tampak seperti data produksi.

## Yang sudah ada pada starter

| Area | Implementasi sekarang | Batas starter |
| --- | --- | --- |
| Beranda publik | `resources/views/home.blade.php` memakai identitas hijau, headline Frame 21, ilustrasi pohon, CTA, dan alur tiga langkah | Belum tersambung ke katalog, hitungan, atau data pohon. Angka dan daftar pada frame Stitch tidak boleh disajikan sebagai fakta produksi sebelum sumber datanya tersedia. |
| Pengelola | Panel Filament `/app`; daftar program dan pembuatan draf M01 tersedia untuk HJK-001 | Resource M02-M15 dan alur bisnis lain tetap dikerjakan lewat story aktif. |
| Petugas | Panel Filament `/petugas` | Kerangka login dan dashboard; assignment dan laporan dikerjakan melalui story aktif. |
| Warga | Panel Filament `/warga` | Kerangka login dan dashboard; pendaftaran dan adopsi dikerjakan melalui story aktif. |

Pada 25 Oktober 2026, seluruh 25 frame diperiksa sebagai rancangan dan dirender untuk proposal/presentasi. Pemeriksaan mencakup identitas layar, aktor, scope, serta pemetaan PBI; ini belum merupakan tes responsif, aksesibilitas, interaksi, atau penerimaan UI aplikasi yang sudah diimplementasikan.

## Palet dan batas penerapan

| Warna | Penggunaan rancangan |
| --- | --- |
| `#15803D` | Hijau utama dari aset merek Stitch, tombol utama dan navigasi aktif |
| `#22C55E` | Aksen hijau |
| `#A7F3D0` | Latar sorotan dan badge |
| `#1F2937` | Teks utama |
| `#F8FAFC` | Latar terang proposal visual/presentasi |

Empat warna pertama tercantum pada aset identitas di Stitch. Latar terang mengikuti presentasi. Beranda starter masih memiliki variasi warna hijau yang tercatat di Blade; audit dokumen ini tidak mengubah CSS aplikasi atau menyatakan seluruh layar sudah seragam. Perubahan visual kode tetap dibahas dan diperiksa pada PR terkait.

Utamakan komponen standar Filament untuk Resource, form, tabel, modal, notifikasi, dan validasi. Gunakan Stitch untuk arah warna, hierarki informasi, dan alur; jangan membangun ulang komponen hanya agar identik dengan gambar. Status harus memiliki label, bukan mengandalkan warna saja.

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

- HJK-001 memakai frame **M01 - Program Penghijauan** untuk halaman daftar di panel Pengelola.
- Frame menampilkan nama program, ringkasan tujuan, periode, status, dan area aksi. Tombol **Tambah Program** membuka alur pembuatan.
- Stitch tidak menyediakan frame formulir pembuatan program. HJK-001 memakai halaman CreateRecord standar Filament 5 dengan validasi di server. Formulir mengikuti alur panel yang sama, tetapi belum dapat diklaim cocok pixel dengan Stitch; minta pemilik modul/PM memeriksanya pada PR.

## Batas scope dan penerimaan

- Beranda publik adalah template; ia tidak boleh mengklaim jumlah pohon/lokasi dari contoh visual sebagai data aktual.
- Pekerjaan layar setelah starter harus menunjuk ke HJK story aktif. Detail interaksi M16-M18 menunggu keputusan scope dan tidak masuk implementasi sekarang.
- Satu layar diterima ketika tampilan, akses role, query kepemilikan, validasi, status kosong/error, dan acceptance criteria story selaras.
- Perubahan besar pada alur Stitch atau desain memerlukan pembaruan story/acceptance criteria dan catatan PR; perubahan arsitektur lintas modul dicatat sebagai ADR.
