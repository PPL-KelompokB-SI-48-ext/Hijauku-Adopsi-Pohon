# Naskah Video Hijauku

27 slide | 5 anggota | 15 modul | 45 PBI | Revisi 25 Oktober 2026

## Cara memakai naskah

Gunakan Canva yang sudah direvisi sebagai visual. Naskah ini membahas ide dan rancangan, bukan menyatakan semua fitur selesai. Baca dengan bahasa sendiri, beri jeda saat berpindah modul, dan tunjuk bagian UI yang sedang dijelaskan. HJK adalah ID kebutuhan; HAP adalah issue Jira yang menautkan pekerjaan dan commit.

Target latihan sekitar 15-18 menit, bukan ketentuan durasi dari dosen. Sesuaikan jika ada batas video resmi. Yoga membuka slide 1-6 dan menutup slide 27; tiap anggota menjelaskan satu slide tanggung jawab dan tiga slide modulnya. Pada setiap modul, sebut tiga PBI dan tunjuk contoh alur di mockup.

## Slide 1 - Yoga | Pembuka

Halo, kami dari tim Hijauku. Saya Yoga sebagai koordinator proyek. Bersama Rofii, Bitari, Arif, dan Wira, kami merancang aplikasi web untuk membantu pengelolaan adopsi dan pemeliharaan pohon. Di video ini, kami menjelaskan kebutuhan yang ingin dijawab, pembagian PBI, dan rancangan antarmukanya.

## Slide 2 - Yoga | Latar belakang

Penghijauan berlanjut setelah pohon ditanam. Pengelola perlu mengetahui lokasi pohon, siapa yang bertanggung jawab, tindakan perawatan, dan perubahan kondisinya. Itu menjadi kebutuhan awal yang ingin kami validasi bersama calon pengguna. Hijauku berkaitan dengan SDG 15 melalui pencatatan dan pemantauan; aplikasi ini tidak menghitung capaian indikator SDG nasional.

## Slide 3 - Yoga | Identitas visual

Identitas Hijauku memakai hijau utama #15803D, aksen #22C55E, dan hijau muda #A7F3D0. Teks memakai #1F2937 di atas latar terang #F8FAFC. Palet ini mengacu pada aset merek di Stitch. Status tetap disertai label agar makna tidak bergantung pada warna. Untuk implementasi panel, kami mengutamakan komponen standar Filament agar pengerjaannya cepat dan konsisten.

## Slide 4 - Yoga | Scope dan teknologi

Scope aktif kami adalah 15 modul dengan 45 PBI. Lima anggota masing-masing memiliki tiga modul, atau sembilan PBI. Autentikasi, RBAC, repo, seeder, dan CI adalah fondasi persiapan. Pengembangan menggunakan satu aplikasi Laravel 13, Filament 5, Blade dan Vite, Eloquent, serta MySQL 8 atau lebih baru. Ada dua sprint development formal; Sprint 0 adalah masa persiapan.

## Slide 5 - Yoga | Peran pengguna

Pengelola mengatur program, inventaris, kegiatan, adopsi, dan pemeriksaan hasil. Petugas menangani pohon yang ditugaskan kepadanya. Warga mengakses pendaftaran dan adopsinya sendiri. Pengunjung dapat membaca beranda dan katalog publik. Hak akses diperiksa pada Policy dan query kepemilikan data, sehingga keamanan tidak bergantung pada tombol yang terlihat di layar.

## Slide 6 - Yoga | Alur utama

Alurnya dimulai dari data program, lokasi, petak, jenis, dan inventaris pohon. Setelah itu ada kegiatan dan penugasan. Warga dapat mengajukan adopsi, lalu pengelola menyusun kesepakatan untuk diterima warga sasaran. Perawatan dan pengamatan tetap bisa dilakukan tanpa adopsi. Hasil terverifikasi menjadi dasar ringkasan, sedangkan masalah pohon ditangani melalui laporan dan tindak lanjut.

## Slide 7 - Yoga | Tanggung jawab

Saya Yoga, bertanggung jawab pada program, lokasi, dan kapasitas petak. Bagian saya berisi sembilan PBI. Sprint 1 mencakup M01 dengan tiga PBI, lalu Sprint 2 mencakup M02 dan M03 dengan enam PBI. Saya mengerjakan UI, validasi, otorisasi, data, dan pengujian yang terkait dengan modul tersebut.

## Slide 8 - Yoga | M01 Program penghijauan

Pada modul program penghijauan, kami membagi alurnya menjadi tiga PBI berikut.

### HJK-001 / HAP-7: Catat tujuan dan periode program

Nama, tujuan, dan periode wajib. Akhir setelah mulai. Program disimpan sebagai draf dengan pembuat dari sesi.

### HJK-002 / HAP-8: Aktifkan program terencana

Hanya draf dengan data lengkap yang dapat diaktifkan. Aktor dan transisi dicatat.

### HJK-003 / HAP-9: Arsipkan program selesai

Program selesai dapat diarsipkan dengan alasan. Data yang sudah dirujuk tetap dilindungi.

Petunjuk visual: Tunjuk tombol Tambah Program serta kolom tujuan, periode, dan status. Form belum digambar pada frame daftar; implementasinya memakai form standar Filament.

## Slide 9 - Yoga | M02 Lokasi tanam

Pada modul lokasi tanam, kami membagi alurnya menjadi tiga PBI berikut.

### HJK-004 / HAP-10: Catat lokasi tanam terverifikasi

Pilih program induk, isi nama, alamat, dan koordinat yang valid.

### HJK-005 / HAP-11: Telusuri lokasi per program

Telusuri lokasi per program, gunakan pencarian dan pagination 20 record.

### HJK-006 / HAP-12: Kendalikan perubahan lokasi

Perubahan induk dilindungi bila ada petak. Alasan dan nilai sebelum/sesudah masuk audit.

Petunjuk visual: Tunjuk pilihan program, nama lokasi, koordinat, filter, dan daftar lokasi.

## Slide 10 - Yoga | M03 Petak tanam

Pada modul petak tanam, kami membagi alurnya menjadi tiga PBI berikut.

### HJK-007 / HAP-13: Catat petak dan kapasitas

Kode unik per lokasi, lokasi wajib, kapasitas positif.

### HJK-008 / HAP-14: Pantau daya tampung petak

Tampilkan keterisian dan peringatan 90%. Tolak penambahan melebihi kapasitas.

### HJK-009 / HAP-15: Kendalikan penutupan petak

Petak dengan pohon aktif tidak dihapus. Penutupan beralasan mencegah pilihan baru.

Petunjuk visual: Tunjuk kode petak, lokasi, kapasitas, keterisian, dan status petak.

## Slide 11 - Rofii | Tanggung jawab

Saya Rofii, bertanggung jawab pada acuan jenis, kelompok penanaman, dan inventaris pohon. Bagian saya berisi sembilan PBI. Sprint 1 mencakup M04 dengan tiga PBI, lalu Sprint 2 mencakup M05 dan M06 dengan enam PBI. Saya mengerjakan UI, validasi, otorisasi, data, dan pengujian yang terkait dengan modul tersebut.

## Slide 12 - Rofii | M04 Jenis pohon

Pada modul jenis pohon, kami membagi alurnya menjadi tiga PBI berikut.

### HJK-010 / HAP-16: Catat acuan jenis dan kebutuhan perawatan

Nama jenis unik, catatan perawatan opsional, dan status aktif.

### HJK-011 / HAP-17: Pilih jenis untuk inventaris

Pohon baru hanya memilih jenis aktif. Referensi lama tetap terbaca.

### HJK-012 / HAP-18: Kendalikan perubahan acuan jenis

Jenis yang digunakan tidak dihapus. Perubahan dicatat; nonaktifkan bila diperlukan.

Petunjuk visual: Tunjuk nama jenis, catatan perawatan, dan penanda aktif/nonaktif.

## Slide 13 - Rofii | M05 Kelompok penanaman

Pada modul kelompok penanaman, kami membagi alurnya menjadi tiga PBI berikut.

### HJK-013 / HAP-19: Catat pelaksanaan penanaman

Pilih petak, isi kode unik per petak, tanggal tanam tidak di masa depan.

### HJK-014 / HAP-20: Kelompokkan pohon per pelaksanaan

Kelompok berisi pohon dari petak yang sama. Setiap pohon maksimal satu kelompok.

### HJK-015 / HAP-21: Koreksi kelompok secara aman

Koreksi memerlukan alasan. Lindungi anggota; kelompok hanya dihapus bila kosong.

Petunjuk visual: Tunjuk kode kelompok, petak, tanggal tanam, dan daftar anggotanya.

## Slide 14 - Rofii | M06 Inventaris pohon

Pada modul inventaris pohon, kami membagi alurnya menjadi tiga PBI berikut.

### HJK-016 / HAP-22: Catat identitas dan kondisi awal pohon

Kode pohon unik. Petak, tanggal, dan kondisi awal divalidasi bersama kapasitas.

### HJK-017 / HAP-23: Telusuri pohon untuk adopsi

Katalog menawarkan pohon tersedia. Alokasi menunggu atau aktif tidak ditawarkan lagi.

### HJK-018 / HAP-24: Koreksi inventaris dengan jejak

Kode setelah transaksi dilindungi. Koreksi beralasan memiliki riwayat audit.

Petunjuk visual: Tunjuk kode pohon, jenis, lokasi/petak, kondisi awal, serta status ketersediaan.

## Slide 15 - Bitari | Tanggung jawab

Saya Bitari, bertanggung jawab pada kegiatan, pendaftaran, dan penugasan lapangan. Bagian saya berisi sembilan PBI. Sprint 1 mencakup M07 dengan tiga PBI, lalu Sprint 2 mencakup M08 dan M09 dengan enam PBI. Saya mengerjakan UI, validasi, otorisasi, data, dan pengujian yang terkait dengan modul tersebut.

## Slide 16 - Bitari | M07 Kegiatan lapangan

Pada modul kegiatan lapangan, kami membagi alurnya menjadi tiga PBI berikut.

### HJK-019 / HAP-25: Catat rencana kegiatan

Isi kegiatan dan lokasi; akhir setelah mulai, kuota harus positif.

### HJK-020 / HAP-26: Buka kegiatan dengan kuota

Hanya draf dibuka. Perubahan kuota setelah ada peserta memerlukan konfirmasi.

### HJK-021 / HAP-27: Tutup/batalkan kegiatan

Batalkan sebelum selesai, sertakan alasan, dan perbarui status peserta secara konsisten.

Petunjuk visual: Tunjuk judul kegiatan, periode, lokasi, kuota, dan status kegiatan.

## Slide 17 - Bitari | M08 Pendaftaran kegiatan

Pada modul pendaftaran kegiatan, kami membagi alurnya menjadi tiga PBI berikut.

### HJK-022 / HAP-28: Daftar pada kegiatan yang tersedia

Pendaftaran unik per akun/kegiatan. Transaksi dan penguncian baris menjaga kuota.

### HJK-023 / HAP-29: Catat kehadiran peserta

Kehadiran hanya untuk peserta terdaftar, dengan waktu dan koreksi tercatat.

### HJK-024 / HAP-30: Tarik pendaftaran secara aman

Peserta menarik pendaftaran sendiri sebelum mulai; kuota kembali tersedia.

Petunjuk visual: Tunjuk kegiatan, peserta, status pendaftaran, dan kehadiran.

## Slide 18 - Bitari | M09 Penugasan lapangan

Pada modul penugasan lapangan, kami membagi alurnya menjadi tiga PBI berikut.

### HJK-025 / HAP-31: Tetapkan petugas dan pohon

Pengelola memilih petugas dan pohon yang sesuai lokasi. Tenggat tidak di masa lalu.

### HJK-026 / HAP-32: Konfirmasi tanggung jawab petugas

Petugas mengonfirmasi tugas sendiri. Penolakan dikembalikan ke pengelola.

### HJK-027 / HAP-33: Ubah penugasan dengan jejak

Perubahan menyimpan alasan, aktor, dan riwayat; petugas lama mendapat pemberitahuan.

Petunjuk visual: Gunakan frame Tugas Saya di panel petugas. Tunjuk pohon, tenggat, dan aksi konfirmasi tugas.

## Slide 19 - Arif | Tanggung jawab

Saya Arif, bertanggung jawab pada permohonan adopsi, kesepakatan, dan laporan masalah. Bagian saya berisi sembilan PBI. Sprint 1 mencakup M10 dengan tiga PBI, lalu Sprint 2 mencakup M11 dan M12 dengan enam PBI. Saya mengerjakan UI, validasi, otorisasi, data, dan pengujian yang terkait dengan modul tersebut.

## Slide 20 - Arif | M10 Permohonan adopsi

Pada modul permohonan adopsi, kami membagi alurnya menjadi tiga PBI berikut.

### HJK-028 / HAP-34: Susun dan kirim permohonan adopsi

Warga memilih pohon tersedia, mengisi alasan, dan menghindari permohonan aktif ganda.

### HJK-029 / HAP-35: Putuskan kelayakan permohonan

Pengelola memutuskan permohonan yang sudah diajukan. Penolakan wajib beralasan.

### HJK-030 / HAP-36: Tarik permohonan yang belum final

Warga menarik miliknya sebelum keputusan. Alokasi pihak lain tetap dilindungi.

Petunjuk visual: Gunakan frame Adopsi Saya di panel warga. Tunjuk pohon, status permohonan, dan aksi pengajuan.

## Slide 21 - Arif | M11 Kesepakatan adopsi

Pada modul kesepakatan adopsi, kami membagi alurnya menjadi tiga PBI berikut.

### HJK-031 / HAP-37: Susun komitmen dan periode adopsi

Susun kesepakatan dari permohonan yang disetujui, dengan komitmen dan periode jelas.

### HJK-032 / HAP-38: Terima dan aktifkan adopsi tunggal

Hanya warga sasaran dapat menerima. Constraint alokasi unik dan penguncian baris mencegah adopsi ganda.

### HJK-033 / HAP-39: Akhiri kesepakatan secara tercatat

Akhir periode atau pengakhiran tercatat melepaskan alokasi yang berlaku.

Petunjuk visual: Gunakan frame Kesepakatan Saya di panel warga. Tunjuk komitmen, periode, status, dan aksi penerimaan.

## Slide 22 - Arif | M12 Laporan masalah pohon

Pada modul laporan masalah pohon, kami membagi alurnya menjadi tiga PBI berikut.

### HJK-034 / HAP-40: Laporkan masalah pohon dengan bukti

Lapor untuk pohon yang menjadi haknya. Severity wajib; bukti JPG/PNG/WebP maksimal 5 MB.

### HJK-035 / HAP-41: Tentukan tindak lanjut masalah

Pengelola menilai laporan dan menetapkan tindak lanjut. Penolakan menyertakan alasan.

### HJK-036 / HAP-42: Catat penyelesaian masalah

Penyelesaian memerlukan catatan. Hasil final terlindungi dan dapat dilihat pelapor.

Petunjuk visual: Tunjuk pohon yang dilaporkan, tingkat masalah, status, dan catatan tindak lanjut.

## Slide 23 - Wira | Tanggung jawab

Saya Wira, bertanggung jawab pada rencana perawatan, realisasi, dan pengamatan pertumbuhan. Bagian saya berisi sembilan PBI. Sprint 1 mencakup M13 dengan tiga PBI, lalu Sprint 2 mencakup M14 dan M15 dengan enam PBI. Saya mengerjakan UI, validasi, otorisasi, data, dan pengujian yang terkait dengan modul tersebut.

## Slide 24 - Wira | M13 Rencana perawatan

Pada modul rencana perawatan, kami membagi alurnya menjadi tiga PBI berikut.

### HJK-037 / HAP-43: Susun rencana perawatan

Isi pohon, tanggal, dan tindakan. Rujukan penugasan bersifat opsional.

### HJK-038 / HAP-44: Terbitkan prioritas perawatan

Tampilkan rencana terlambat dan prioritas. HAP-44 menambahkan kolom prioritas lewat migration.

### HJK-039 / HAP-45: Koreksi atau batalkan rencana

Rencana berjalan dibatalkan dengan alasan, bukan dihapus. Riwayat tetap tersimpan.

Petunjuk visual: Tunjuk pohon, tanggal rencana, tindakan, prioritas, dan status. Prioritas masih membutuhkan migration pada HAP-44.

## Slide 25 - Wira | M14 Realisasi perawatan

Pada modul realisasi perawatan, kami membagi alurnya menjadi tiga PBI berikut.

### HJK-040 / HAP-46: Catat realisasi dan bukti perawatan

Petugas mencatat perawatan pohon yang ditugaskan, dengan bukti privat maksimal 5 MB.

### HJK-041 / HAP-47: Verifikasi hasil perawatan

Verifikator berbeda dari pengirim. Terima atau kembalikan dengan alasan yang jelas.

### HJK-042 / HAP-48: Kembalikan atau koreksi realisasi

Koreksi data terverifikasi melalui keputusan tercatat. Petugas melihat status perbaikan.

Petunjuk visual: Tunjuk tanggal realisasi, pelaksana, bukti, dan status pemeriksaan.

## Slide 26 - Wira | M15 Pengamatan pertumbuhan

Pada modul pengamatan pertumbuhan, kami membagi alurnya menjadi tiga PBI berikut.

### HJK-043 / HAP-49: Catat pengamatan terukur

Tinggi/diameter positif, satu pengamatan per pohon/tanggal, foto opsional maksimal 5 MB.

### HJK-044 / HAP-50: Verifikasi dan tampilkan perkembangan

Ringkasan memakai data terverifikasi; tandai pohon tanpa pengamatan lebih dari 30 hari.

### HJK-045 / HAP-51: Koreksi pengamatan tercatat

Koreksi menjaga riwayat dan validasi ukuran. Data bukan bukti langsung capaian SDG.

Petunjuk visual: Tunjuk tanggal, tinggi, diameter, kondisi, dan status verifikasi. Angka pada UI hanya contoh.

## Slide 27 - Yoga | Jadwal dan penutup

Persiapan berada di minggu 5, tanggal 12-18 Oktober 2026. Sprint 1 mengikuti minggu 6-10, yaitu 19 Oktober-22 November. Minggu 6 untuk backlog dan refinement; coding dimulai minggu 7, lalu minggu 10 dipakai untuk integrasi, pengujian, review, dan retrospektif. Sprint 2 berlangsung 23 November-27 Desember pada minggu 11-15. Setiap PBI memiliki tanggal mulai Senin dan target Minggu di Jira, dan dua PBI boleh berjalan paralel sesuai rencana.

Setiap anggota menggunakan satu branch personal. Commit dan PR mencantumkan key HAP agar dapat ditautkan ke Jira. Penulis menyelesaikan konflik; anggota meminta review saya, dan hanya PM yang melakukan merge setelah CI dan acceptance terpenuhi. Tanggal tersebut adalah rencana, sedangkan waktu aktual dicatat sesuai pekerjaan yang benar-benar dilakukan. Terima kasih.

## Pemeriksaan sebelum rekaman

Pastikan urutan 27 slide sesuai naskah, nama pembicara benar, dan 45 PBI disebut pada modul pemiliknya. Sebut tampilan sebagai mockup dengan data contoh. Jangan mengklaim komunitas sudah diwawancarai, semua fitur sudah selesai, atau aplikasi sudah mengukur keberhasilan SDG. Konfirmasi ketentuan tim lima orang dan kompleksitas fitur kepada dosen; jumlah sembilan PBI per anggota tidak otomatis membuktikan kedua hal itu.

Sumber kerja: proposal Google Docs, desain Canva Hijauku, Jira HAP, dan proyek Stitch. Jadwal dan acceptance Jira adalah baseline 25 Oktober 2026; perubahan berikutnya perlu diikuti pada proposal, slide, dan panduan tim.
