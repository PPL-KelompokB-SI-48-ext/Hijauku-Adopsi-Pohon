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
| Pengelola | Panel Filament `/app` | Kerangka login dan dashboard; belum ada Resource/workflow domain. |
| Petugas | Panel Filament `/petugas` | Kerangka login dan dashboard; assignment dan laporan dikerjakan melalui story aktif. |
| Warga | Panel Filament `/warga` | Kerangka login dan dashboard; pendaftaran dan adopsi dikerjakan melalui story aktif. |

Pemeriksaan ini mengonfirmasi arah visual dan cakupan, bukan audit pixel-perfect untuk seluruh 25 frame. Semua frame selain sampel yang disebut di atas masih perlu dicocokkan ke story dan diperiksa sebelum UI modul dianggap selesai.

## Cara membawa frame ke kode

1. Catat nomor/judul frame Stitch dan tautkan ke HJK story serta modul M01-M15 di Jira.
2. Pastikan aktor, route/panel, aksi, status awal, kondisi kosong, validasi, dan hasil sukses/gagal cocok dengan acceptance criteria story.
3. Implementasikan tampilan di panel role yang tepat. Taruh Resource, Page, dan Widget dalam namespace panel masing-masing; jangan mengandalkan menu untuk mengamankan data.
4. Terapkan Policy, scope query, validasi field, dan service transaksi sebelum menghubungkan data nyata. `viewAny` saja tidak membatasi baris yang dikirim ke browser.
5. Pakai komponen, warna, jarak, dan hierarki yang konsisten dengan frame. Simpan aset proyek pada `public/images/` atau `resources/images/` dengan nama yang menjelaskan isinya.
6. Sertakan keadaan loading, kosong, error, berhasil, dan layar kecil. Jangan membuka modal/drawer otomatis tanpa tindakan pengguna.
7. Minta pemilik modul membandingkan hasil dengan frame Stitch pada PR; lampirkan tangkapan layar hanya jika diperlukan untuk menilai perbedaan visual.

## Batas scope dan penerimaan

- Beranda publik adalah template; ia tidak boleh mengklaim jumlah pohon/lokasi dari contoh visual sebagai data aktual.
- Pekerjaan layar setelah starter harus menunjuk ke HJK story aktif. Detail interaksi M16-M18 menunggu keputusan scope dan tidak masuk implementasi sekarang.
- Satu layar diterima ketika tampilan, akses role, query kepemilikan, validasi, status kosong/error, dan acceptance criteria story selaras.
- Perubahan besar pada alur Stitch atau desain memerlukan pembaruan story/acceptance criteria dan catatan PR; perubahan arsitektur lintas modul dicatat sebagai ADR.
