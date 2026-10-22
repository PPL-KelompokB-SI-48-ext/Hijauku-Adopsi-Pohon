# Kebutuhan dan katalog modul

Dokumen ini menerjemahkan aturan tugas kuliah dan rancangan awal menjadi peta backlog. Ia belum menyatakan Jira telah cocok karena akun Jira perlu login untuk melihat isinya.

## Aturan tugas yang terverifikasi

- Bahan Ketentuan Proyek PPL meminta kelompok berisi 6–7 orang dan menganggap semua anggota sebagai pengembang.
- Setiap anggota perlu mengerjakan minimal satu fitur berkompleksitas sedang/tinggi pada setiap sprint formal.
- Satu fitur harus dapat dipecah menjadi paling sedikit tiga PBI; PBI dinyatakan sebagai user story.
- Materi membahas dua sprint formal. Sprint 0 adalah persiapan proyek yang disepakati tim, bukan salah satu dari dua sprint formal pada ketentuan.
- Story HJK di bawah adalah daftar kebutuhan awal. Setiap story perlu punya aktor, manfaat, acceptance criteria, dependensi, dan ukuran pekerjaan di Jira.

## Mengurai hitungan tim

Rancangan arsitektur menggunakan 6 anggota untuk menghitung `3 CRUD × 6 anggota = 18 modul`. Tiga tujuan per modul menghasilkan 54 PBI; dibagi 6 menghasilkan rata-rata 9 PBI per orang. Bahan kuliah yang tersedia tidak menyebut rumus 3 CRUD per anggota.

Aturan sprint kuliah meminta minimal satu fitur sedang/tinggi per orang pada setiap sprint, dan setiap fitur perlu dipecah menjadi sekurangnya 3 PBI. Jika tiap orang mengerjakan tepat satu fitur minimal per sprint, hasil turunannya adalah sekurangnya 3 PBI/orang/sprint, atau 6 PBI/orang untuk dua sprint. Untuk 6 orang, batas dasar dari aturan itu adalah 36 PBI. Target 54 pada arsitektur lebih besar; ia berasal dari 18 modul × 3 PBI, bukan dari aturan satu fitur per sprint saja.

Rumusnya adalah **1 fitur → minimal 3 PBI**. Bukan “1 PBI terdiri dari 3 modul”.

Lima nama ada di sampul proposal dan lima orang memiliki akses ke repo. Sebaliknya, instruksi kelas menyebut 6–7 orang, sedangkan arsitektur dan HAP-1 menganggap 6. Karena itu 18 modul/54 PBI layak dipertahankan sebagai target sementara, sampai roster resmi disahkan.

## Katalog 18 modul / 54 PBI awal

| Modul | Area | PBI 1 | PBI 2 | PBI 3 |
| --- | --- | --- | --- | --- |
| M01 | Program penghijauan | Catat tujuan dan periode | Aktifkan program terencana | Arsipkan program selesai |
| M02 | Lokasi tanam | Catat lokasi terverifikasi | Telusuri lokasi per program | Kendalikan perubahan lokasi |
| M03 | Petak tanam | Catat petak dan kapasitas | Pantau daya tampung petak | Kendalikan penutupan petak |
| M04 | Jenis pohon | Catat acuan jenis/perawatan | Pilih jenis untuk inventaris | Kendalikan perubahan acuan |
| M05 | Kelompok penanaman | Catat pelaksanaan penanaman | Kelompokkan pohon per pelaksanaan | Koreksi kelompok secara aman |
| M06 | Inventaris pohon | Catat identitas dan kondisi awal | Telusuri pohon untuk adopsi | Koreksi inventaris dengan jejak |
| M07 | Kegiatan lapangan | Catat rencana kegiatan | Buka kegiatan dengan kuota | Tutup/batalkan kegiatan |
| M08 | Pendaftaran kegiatan | Daftar pada kegiatan tersedia | Catat kehadiran peserta | Tarik pendaftaran secara aman |
| M09 | Penugasan lapangan | Tetapkan petugas dan pohon | Konfirmasi tanggung jawab petugas | Ubah penugasan dengan jejak |
| M10 | Permohonan adopsi | Susun/kirim permohonan | Putuskan kelayakan permohonan | Tarik permohonan belum final |
| M11 | Kesepakatan adopsi | Susun komitmen dan periode | Terima/aktifkan adopsi tunggal | Akhiri kesepakatan tercatat |
| M12 | Laporan masalah pohon | Laporkan masalah dengan bukti | Tentukan tindak lanjut masalah | Catat penyelesaian masalah |
| M13 | Rencana perawatan | Susun rencana berbasis penugasan | Terbitkan prioritas perawatan | Koreksi/batalkan rencana |
| M14 | Realisasi perawatan | Catat realisasi dan bukti | Verifikasi hasil perawatan | Kembalikan/koreksi realisasi |
| M15 | Pengamatan pertumbuhan | Catat pengamatan terukur | Verifikasi/tampilkan perkembangan | Koreksi pengamatan tercatat |
| M16 | Artikel lingkungan | Susun materi edukasi | Terbitkan materi untuk publik | Tarik/arsipkan materi |
| M17 | Galeri kegiatan | Susun foto dan keterangannya | Terbitkan dokumentasi kegiatan | Tarik dokumentasi publik |
| M18 | Usulan aksi penghijauan | Susun/kirim usulan aksi | Nilai kelayakan usulan | Tarik usulan belum diputuskan |

ID rancangan berurutan HJK-001–054, tiga per PBI. Ini adalah ID logis, bukan key Jira. Saat memasukkan ke Jira, gunakan summary user story yang menjelaskan aktor dan manfaat, lalu pertahankan ID sebagai label/referensi bila sesuai.

## Catatan penerimaan lintas modul

- Hak akses mengikuti peran dan kepemilikan data; halaman publik hanya menampilkan konten yang terbit.
- Data yang sudah dirujuk transaksi tidak dihapus sembarang; gunakan arsip/nonaktif bila dibutuhkan.
- Kuota dan kapasitas diperiksa dalam operasi transaksional, bukan hanya disembunyikan di UI.
- Status memiliki transisi yang sah dan dicatat; data terkirim yang ditolak dapat diperbaiki dengan alasan.
- Bukti foto berada pada storage privat hingga alur publikasi memberi akses.
- Dashboard membedakan jumlah inventaris dari pohon hidup dan hanya memakai laporan yang terverifikasi.

Kriteria ini masih perlu diturunkan menjadi acceptance criteria spesifik per story dan diselaraskan dengan proses yang disepakati tim.
