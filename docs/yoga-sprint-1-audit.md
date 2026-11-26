# Audit Sprint 1 Yoga: M01 dan M02

**Tanggal:** 26 November 2026, WIB

**Cakupan:** HAP-7 sampai HAP-12, UI dan alur manager di M01 dan M02.

**Acuan:** acceptance Jira dan [panduan UI](ui-design.md).

## Kesimpulan

Acceptance M01 dan M02 yang diperiksa sudah didukung oleh validasi, aturan akses, pencatatan perubahan, dan tes. Pemeriksaan browser menemukan satu masalah tampilan pada dialog aksi: di lebar 1024 px, modal Edit lokasi berada di bawah sidebar dan menu di belakangnya masih bisa menerima klik. Lapisan modal dinaikkan dan tes Dusk baru memastikan modal program dan lokasi tetap berada di atas sidebar.

Tidak ada gap acceptance lain yang ditemukan pada scope ini. Layar daftar mengikuti frame Stitch untuk elemen yang sesuai requirement. Halaman tambah, detail, dan modal edit tidak punya frame pembanding sendiri, jadi memakai pola Filament yang sama. Ini bukan klaim semua halaman identik 1:1.

## Pemeriksaan per story

| Jira dan PBI | Yang diverifikasi | Bukti tes |
| --- | --- | --- |
| HAP-7, HJK-001 | Nama, tujuan, dan periode wajib diisi; tanggal akhir setelah tanggal mulai; status awal Draf dan pembuat berasal dari sesi | `ProgramCreationTest`, `ProgramCreationTest` Dusk |
| HAP-8, HJK-002 | Hanya Draf yang bisa diaktifkan; alasan wajib; tanggal dan status diperiksa ulang; aktor dan transisi dicatat | `ProgramStatusWorkflowTest`, `ProgramStatusWorkflowTest` Dusk |
| HAP-9, HJK-003 | Hanya program Selesai yang bisa diarsipkan; alasan dan aktor dicatat; lokasi tetap tersimpan | `ProgramStatusWorkflowTest` |
| HAP-10, HJK-004 | Lokasi terhubung ke program; nama/alamat wajib; koordinat dibatasi rentangnya; akses hanya untuk pengelola | `SiteManagementTest`, `SiteCreationTest` Dusk |
| HAP-11, HJK-005 | Filter program, pencarian nama, dan pagination 20 baris bekerja | `SiteManagementTest` |
| HAP-12, HJK-006 | Perubahan program induk ditolak bila petak sudah memakai lokasi; perubahan menyimpan aktor, alasan, nilai sebelum/sesudah; hapus ditolak jika dipakai petak atau kegiatan | `SiteManagementTest` |

## Perbaikan yang dibuat

Sebelum perbaikan, modal Filament memakai `z-index: 40`, sementara sidebar desktop memakai `z-index: 50`. Pada viewport 1024 px, klik di sisi kiri modal mengenai menu sidebar. `resources/css/filament/theme.css` sekarang menetapkan lapisan modal ke `60` agar dialog dan backdrop menutup panel di belakangnya.

Tes Dusk memeriksa urutan lapisan pada modal aktivasi program dan modal edit lokasi di lebar 1024 px. Tes edit juga membuka dan menutup dialog tanpa mengubah data. Perilaku ubah, audit, dan batas hapus tetap diverifikasi oleh tes Feature.

## Hasil UAT dan tampilan

UAT memakai akun demo pengelola dan database SQLite tersendiri di worktree sementara. Database MySQL dan `.env` lokal pemilik tidak dipakai.

- M01: daftar program, formulir buat Draf, aktivasi, pengarsipan, alasan transisi, dan status akhir berhasil diperiksa lewat tes Feature/Dusk.
- M02: daftar lokasi, buat lokasi dengan koordinat, filter/pencarian/pagination, dialog Edit, batas perubahan dan penghapusan diperiksa lewat tes Feature/Dusk.
- Pada lebar 390 px, M01 dan M02 tidak melebar keluar viewport. Tabel yang lebih lebar bergulir di wadah tabelnya.
- Pada lebar 1024 px, dialog Edit lokasi dan dialog transisi program berada di atas sidebar.
- Pada lebar 1440 px, sidebar 256 px, topbar 64 px, konten, tabel, dan breadcrumb tampil tanpa overflow horizontal.
- Console browser tidak mencatat error atau warning, dan tidak ada respons HTTP 4xx/5xx pada halaman yang diperiksa. Dua request tercatat `ERR_ABORTED` saat navigasi mengganti halaman.
- Tidak ada endpoint REST terpisah pada acceptance M01/M02. Request fitur berjalan melalui Filament/Livewire, sehingga tes browser dan Feature mencakup batas UI dan servernya.

### Catatan kesesuaian Stitch

- M01 dan M02 memakai urutan konten, palet, font, tabel, pencarian, status, serta aksi yang sesuai dengan frame dan acceptance yang tersedia.
- Frame menampilkan data contoh. Aplikasi menampilkan ID database sebenarnya karena skema tidak memiliki kolom kode program/lokasi tersebut.
- Tombol Filter Lanjutan pada frame M02 belum dibuat karena acceptance belum menjelaskan perilakunya.
- M01 tidak menambah edit/hapus program yang tidak termasuk acceptance saat ini.
- Topbar tidak menambah tombol bantuan/notifikasi yang tidak punya perilaku. Perbedaan shell dan pola Filament yang belum punya frame dicatat di `docs/ui-design.md`.

## Pengujian

Semua tes berikut dijalankan terhadap branch `yoga` pada worktree sementara dengan PHP 8.4.27 dan ekstensi `intl` aktif. Tes Dusk menggunakan file SQLite khusus.

| Pemeriksaan | Hasil |
| --- | --- |
| Unit | 11 tes, 11 assertion, lulus |
| Feature HAP-7 sampai HAP-12 | 27 tes, 176 assertion, lulus |
| Dusk M01/M02 | 4 tes, 23 assertion, lulus |
| `npm run build` | Lulus |
| `git diff --check` | Lulus |

Build Vite memberi catatan opsional bahwa paket `fontaine` belum terpasang untuk optimasi fallback font. Build tetap berhasil dan catatan ini tidak memengaruhi tampilan saat uji browser.

CI GitHub untuk commit baru belum dihitung sebagai lulus sampai run pada head PR terbaru selesai. Merge baru aman setelah check wajib, review yang diwajibkan, thread selesai, dan branch tetap mengikuti `main`.
