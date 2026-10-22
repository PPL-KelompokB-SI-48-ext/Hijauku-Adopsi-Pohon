# Kebutuhan dan katalog modul

Dokumen ini memisahkan aturan kuliah, roster yang dikonfirmasi pemilik proyek, scope arsitektur, dan backlog yang benar-benar ada di Jira.

## Aturan kuliah dan roster proyek

- Ketentuan Proyek PPL menyebut kelompok berisi 6–7 orang dan semua anggota berperan sebagai developer.
- Pemilik proyek mengonfirmasi tim Hijauku terdiri dari lima orang: Yoga, Bitari, Arif, Wira, dan Rofii. Dinda tidak termasuk.
- Setiap orang perlu mengerjakan sedikitnya satu fitur kompleksitas sedang/tinggi pada setiap sprint formal. Setiap fitur dipecah menjadi minimal tiga PBI/user story.
- Dengan asumsi satu fitur minimum per orang per sprint dan dua sprint formal, hasil hitung dasarnya **6 PBI per orang** atau **30 PBI untuk tim lima orang**. Angka ini turunan dari aturan, bukan aturan 3 modul per orang.
- Karena bahan kuliah masih menyebut 6–7 anggota, kesesuaian tim lima orang perlu dikonfirmasi kepada dosen.
- Sprint 0 adalah persiapan dan bukan salah satu dari dua sprint formal.

## Cara membaca hitungan Jira

Satu modul adalah kelompok kerja fitur; tiga story/PBI menjabarkan pekerjaan modul tersebut. Pembagian Jira saat audit:

| Anggota | Modul yang ditugaskan | Story/PBI |
| --- | --- | ---: |
| Yoga | M01–M03 | 9 |
| Rofii | M04–M06 | 9 |
| Bitari | M07–M09 | 9 |
| Arif | M10–M12 | 9 |
| Wira | M13–M15 | 9 |
| **Total aktif di Jira** | **15 modul × 3 story** | **45** |

Jadi susunan saat ini adalah **3 modul per orang × 3 story per modul = 9 story per orang**. Bukan 1 PBI yang terdiri dari 3 modul. Target 45 lebih besar daripada batas dasar 6 PBI/orang yang diturunkan dari aturan dua sprint.

Jira aktual memuat 6 epic dan 45 story: HJK-001–045 untuk M01–M15. HAP-6 (M16–M18) belum memiliki story. HAP-61 masih meminta 6 anggota dan 54 story, berbeda dari roster lima orang yang dikonfirmasi pemilik dan 45 story yang ada. Rekomendasi audit: PM memperbarui target HAP-61 menjadi lima orang/45 story dan mempertahankan M16–M18 sebagai tertunda sampai ada keputusan scope serta story baru. Jangan menambahkan Dinda.

Semua 45 story yang terbaca mempunyai deskripsi dan kriteria penerimaan. Setiap anggota memiliki 9 story dan memperoleh paling sedikit satu kelompok tiga story pada masing-masing Sprint 1 dan Sprint 2. Ini memenuhi hitungan minimum 3 PBI per orang per sprint dengan asumsi satu kelompok story merupakan satu fitur. Target 54 hanya muncul di kriteria HAP-61 dan rancangan lama 18 modul; bahan kuliah yang diberikan tidak menyatakan rumus itu.

## Katalog 18 modul arsitektur (15 aktif, 3 tertunda)

Tabel berikut merekam domain dari rancangan awal. Jira saat ini hanya membagi pekerjaan aktif untuk M01–M15; M16–M18 belum memiliki PBI dan belum boleh dianggap selesai hanya karena tabelnya ada di migrasi starter.

| Modul | Area | PBI 1 | PBI 2 | PBI 3 | Status Jira |
| --- | --- | --- | --- | --- | --- |
| M01 | Program penghijauan | Catat tujuan dan periode | Aktifkan program terencana | Arsipkan program selesai | Aktif |
| M02 | Lokasi tanam | Catat lokasi terverifikasi | Telusuri lokasi per program | Kendalikan perubahan lokasi | Aktif |
| M03 | Petak tanam | Catat petak dan kapasitas | Pantau daya tampung petak | Kendalikan penutupan petak | Aktif |
| M04 | Jenis pohon | Catat acuan jenis/perawatan | Pilih jenis untuk inventaris | Kendalikan perubahan acuan | Aktif |
| M05 | Kelompok penanaman | Catat pelaksanaan penanaman | Kelompokkan pohon per pelaksanaan | Koreksi kelompok secara aman | Aktif |
| M06 | Inventaris pohon | Catat identitas dan kondisi awal | Telusuri pohon untuk adopsi | Koreksi inventaris dengan jejak | Aktif |
| M07 | Kegiatan lapangan | Catat rencana kegiatan | Buka kegiatan dengan kuota | Tutup/batalkan kegiatan | Aktif |
| M08 | Pendaftaran kegiatan | Daftar pada kegiatan tersedia | Catat kehadiran peserta | Tarik pendaftaran secara aman | Aktif |
| M09 | Penugasan lapangan | Tetapkan petugas dan pohon | Konfirmasi tanggung jawab petugas | Ubah penugasan dengan jejak | Aktif |
| M10 | Permohonan adopsi | Susun/kirim permohonan | Putuskan kelayakan permohonan | Tarik permohonan belum final | Aktif |
| M11 | Kesepakatan adopsi | Susun komitmen dan periode | Terima/aktifkan adopsi tunggal | Akhiri kesepakatan tercatat | Aktif |
| M12 | Laporan masalah pohon | Laporkan masalah dengan bukti | Tentukan tindak lanjut masalah | Catat penyelesaian masalah | Aktif |
| M13 | Rencana perawatan | Susun rencana berbasis penugasan | Terbitkan prioritas perawatan | Koreksi/batalkan rencana | Aktif |
| M14 | Realisasi perawatan | Catat realisasi dan bukti | Verifikasi hasil perawatan | Kembalikan/koreksi realisasi | Aktif |
| M15 | Pengamatan pertumbuhan | Catat pengamatan terukur | Verifikasi/tampilkan perkembangan | Koreksi pengamatan tercatat | Aktif |
| M16 | Artikel lingkungan | Susun materi edukasi | Terbitkan materi untuk publik | Tarik/arsipkan materi | Tertunda |
| M17 | Galeri kegiatan | Susun foto dan keterangannya | Terbitkan dokumentasi kegiatan | Tarik dokumentasi publik | Tertunda |
| M18 | Usulan aksi penghijauan | Susun/kirim usulan aksi | Nilai kelayakan usulan | Tarik usulan belum diputuskan | Tertunda |

ID HJK-001–054 adalah ID rancangan, bukan Jira issue key. ID HJK-001–045 sudah muncul di Jira; HJK-046–054 belum ada. Jika ruang lingkup 15 modul dipertahankan, katalog tiga modul tertunda tidak otomatis dihitung sebagai pekerjaan sprint.

## Sprint dan penugasan

Jira mempunyai tiga sprint: Sprint 0 - Persiapan, Sprint 1 - Modul inti, dan Sprint 2 - Lanjutan. Seluruhnya masih berstatus `future` pada saat audit 22 Oktober 2026, meskipun tanggal Sprint 0 berakhir 18 Oktober dan Sprint 1 dimulai 19 Oktober. Sprint 2 pada Jira direncanakan pekan 11–14, sementara bahan kuliah yang dibaca menyebut pekan 11–15.

| Anggota | Story Sprint 1 | Story Sprint 2 | Catatan |
| --- | ---: | ---: | --- |
| Yoga | 6 | 3 | Satu modul penuh pada tiap sprint |
| Rofii | 6 | 3 | Satu modul penuh pada tiap sprint |
| Bitari | 6 | 3 | Satu modul penuh pada tiap sprint |
| Arif | 6 | 3 | Satu modul penuh pada tiap sprint |
| Wira | 3 | 6 | Satu modul penuh pada tiap sprint |

Rincian modul dan dependensi sebaiknya tetap di Jira setelah PM memperbaiki tanggal/status sprint yang sudah lewat.

## Kriteria lintas modul

- Hak akses mengikuti peran dan kepemilikan data; halaman publik hanya menampilkan konten yang memang diterbitkan atau diverifikasi.
- Data yang sudah dirujuk transaksi tidak dihapus sembarang; gunakan arsip/nonaktif bila dibutuhkan.
- Kuota dan kapasitas diperiksa di operasi transaksional, bukan hanya disembunyikan di UI.
- Status memiliki transisi yang sah dan dicatat; data terkirim yang ditolak dapat diperbaiki dengan alasan.
- Bukti foto berada pada storage privat hingga alur publikasi memberi akses.
- Dashboard membedakan jumlah inventaris dari pohon hidup dan hanya memakai laporan yang terverifikasi.

Kriteria ini perlu diselaraskan dengan desain Stitch dan diterapkan sebagai acceptance criteria yang spesifik per story.
