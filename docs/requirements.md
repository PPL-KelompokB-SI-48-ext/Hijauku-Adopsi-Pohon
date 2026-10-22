# Kebutuhan dan backlog Hijauku

Dokumen ini memisahkan aturan kuliah, roster yang dikonfirmasi PM, dan backlog aktif Jira. Jira adalah sumber status story; dokumen ini menjelaskan cara menghitungnya.

## Tim

| Anggota | Modul | Story aktif |
| --- | --- | ---: |
| Yoga (PM) | M01-M03 | 9 |
| Rofii | M04-M06 | 9 |
| Bitari | M07-M09 | 9 |
| Arif | M10-M12 | 9 |
| Wira | M13-M15 | 9 |
| **Total** | **15 modul** | **45** |

Roster aktif terdiri dari lima orang. Dinda tidak termasuk dan tidak boleh ditugaskan. Repo saat ini memberi anggota akses write dan PM akses admin.

## Rumus yang benar

Jira berisi **tiga modul per orang**. Tiap modul dipecah menjadi **tiga story/PBI**. Jadi setiap orang memegang **3 × 3 = 9 story** dan tim memiliki **5 × 9 = 45 story**.

Satu PBI bukan terdiri dari tiga modul. Modul adalah kelompok fitur, sedangkan story adalah unit pekerjaan di Jira. ID kebutuhan HJK-001 sampai HJK-045 berurutan; key issue Jira HAP-7 sampai HAP-51 juga sudah terbentuk. Jangan mengganti key yang telah dipakai dalam PR, commit, atau diskusi; perubahan judul/assignment dilakukan pada issue yang sama.

## Aturan perkuliahan dan catatan

Bahan kuliah yang diaudit menyebut kelompok berisi 6-7 orang. PM mengonfirmasi tim Hijauku berisi lima orang, sehingga konfirmasi dosen masih diperlukan.

Bahan juga meminta setiap orang mengerjakan setidaknya satu fitur dengan kompleksitas sedang/tinggi di setiap sprint formal, dan setiap fitur memiliki minimal tiga PBI. Dengan asumsi satu fitur minimum per orang per sprint dan dua sprint formal, hitungan dasarnya adalah 3 PBI/orang/sprint, 6 PBI/orang untuk dua sprint, atau 30 untuk lima orang. Itu bukan pengganti keputusan dosen atau bukti kompleksitas pada story.

Backlog Jira sekarang berisi 9 story per anggota. Rencana sprint yang ditinjau ulang memberi tiap orang 4-5 story per sprint; story dibagi menurut urutan prasyarat dan beban estimasi, sehingga tiga story dari satu modul tidak selalu berada di sprint yang sama. Rincian issue dan jam usulan ada di [baseline estimasi](effort-estimates.md). Pastikan dosen menerima pemetaan ini bila ketentuan tugas meminta tiga PBI satu fitur dikerjakan dalam setiap sprint formal.

## Pembagian sprint

| Anggota | Sprint 1 | Sprint 2 | Total |
| --- | ---: | ---: | ---: |
| Yoga | 5 | 4 | 9 |
| Rofii | 5 | 4 | 9 |
| Bitari | 4 | 5 | 9 |
| Arif | 4 | 5 | 9 |
| Wira | 4 | 5 | 9 |
| **Total** | **22** | **23** | **45** |

Sprint 0 adalah persiapan dan bukan sprint formal. Sprint 0, Sprint 1, dan Sprint 2 sudah dibuat di board. Per tanggal audit 22 Oktober 2026, tanggal Sprint 0 telah lewat dan tanggal mulai Sprint 1 juga telah tiba, tetapi status Jira ketiganya masih future. Sprint 2 goal dan tanggal akhir diperbaiki menjadi pekan 11-15 dan 27 Desember 2026. PM tetap perlu menyelaraskan state Sprint 0/1 sebelum menganggap sprint berjalan atau selesai. Penempatan per story dan jumlah estimasi usulan per orang ada di [baseline estimasi](effort-estimates.md); semua angka tetap perlu refinement oleh pemilik modul.

Jadwal Start date dan Due date per story mengikuti minggu Senin-Minggu dalam [jadwal mingguan backlog](task-schedule.md). Setiap anggota mendapat maksimal dua story dalam minggu yang sama; pasangan paralel dipilih setelah prasyaratnya tersedia. Minggu akhir tiap sprint disediakan untuk integrasi, demo, dan review.

## Epic aktif dan scope

| Jira | Isi | Modul |
| --- | --- | --- |
| HAP-1 | Fondasi dan infrastruktur | Sprint 0 |
| HAP-2 | Data dasar dan inventaris | M01-M06 |
| HAP-3 | Kegiatan dan penugasan | M07-M09 |
| HAP-4 | Adopsi dan masalah pohon | M10-M12 |
| HAP-5 | Perawatan dan pengamatan | M13-M15 |

HAP-6, yang dahulu menampung konten dan publikasi M16 sampai M18, sudah dihapus permanen dari Jira atas keputusan PM. Karena issue key Jira tidak dapat dipakai ulang atau dinomori kembali, daftar story tetap memakai key yang telah terbentuk. Tidak ada story aktif M16 sampai M18. Tabel/model yang masih ada di starter adalah scaffold dari rancangan awal, bukan bagian backlog aktif.

## Katalog modul

| Modul | Area | Pemilik | Jumlah story |
| --- | --- | --- | ---: |
| M01 | Program penghijauan | Yoga | 3 |
| M02 | Lokasi tanam | Yoga | 3 |
| M03 | Petak tanam | Yoga | 3 |
| M04 | Jenis pohon | Rofii | 3 |
| M05 | Kelompok penanaman | Rofii | 3 |
| M06 | Inventaris pohon | Rofii | 3 |
| M07 | Kegiatan lapangan | Bitari | 3 |
| M08 | Pendaftaran kegiatan | Bitari | 3 |
| M09 | Penugasan lapangan | Bitari | 3 |
| M10 | Permohonan adopsi | Arif | 3 |
| M11 | Kesepakatan adopsi | Arif | 3 |
| M12 | Laporan masalah pohon | Arif | 3 |
| M13 | Rencana perawatan | Wira | 3 |
| M14 | Realisasi perawatan | Wira | 3 |
| M15 | Pengamatan pertumbuhan | Wira | 3 |
| M16 | Artikel lingkungan | - | Di luar scope aktif |
| M17 | Galeri kegiatan | - | Di luar scope aktif |
| M18 | Usulan aksi penghijauan | - | Di luar scope aktif |

Judul, deskripsi, acceptance criteria, assignee, dan status per story berada pada Jira. Perubahan requirement harus memperbarui issue dan dokumen ini bila mengubah scope, owner, atau hitungan.

## Cara menjaga backlog sehat

- Story menjelaskan siapa membutuhkan apa dan untuk tujuan apa; acceptance criteria dapat diuji.
- Cantumkan kasus berhasil, kasus ditolak, aturan role/ownership, serta efek status bila relevan.
- Pecah story yang tidak dapat selesai dan ditinjau dalam satu sprint.
- Hubungkan PR/commit dengan Jira key, tetapi jangan mengubah key issue yang sudah digunakan.
- Jangan memasukkan M16 sampai M18 ke sprint aktif sebelum PM menetapkan scope baru dan membuat issue yang sesuai.
