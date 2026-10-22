# Audit sumber dan status keputusan

Audit ini memisahkan ketentuan yang tertulis dari asumsi teknis dan hal yang belum dapat diverifikasi. Dokumen sumber dipertahankan di folder kuliah pemilik proyek; ringkasan ini tidak menggantikannya.

## Ringkasan hasil

| Area | Temuan | Status |
| --- | --- | --- |
| Jumlah anggota | Sampul proposal dan daftar kolaborator GitHub menunjukkan 5 orang. Ketentuan PPL menyebut kelompok 6–7 orang. Rancangan arsitektur dan issue SETUP-001 menghitung 6 orang dan mencantumkan Dinda sebagai anggota keenam. | Konflik perlu diselesaikan dengan dosen/tim |
| Beban fitur | Ketentuan kuliah menyebut tiap anggota mengerjakan minimal satu fitur dengan kompleksitas sedang/tinggi per sprint; satu fitur harus dipecah menjadi sedikitnya 3 PBI. | Tertulis di bahan kuliah |
| Hitungan 18/54 | Rancangan mengasumsikan rumus CRUD minimal = 3 × 6 anggota, sehingga 18 modul. Masing-masing dibuat 3 PBI: 54 story. Dengan 6 orang, rata-rata 9 story/orang. Ini tidak berarti setiap orang hanya wajib membuat 3 PBI, dan rumus 3 × anggota tidak ditemukan di PDF aturan yang diberikan. | Rumus ada pada rancangan; asal aturan dosen belum terkonfirmasi |
| Roster | Lima nama di proposal adalah Muhammad Yoga Adi Saputra, I Gusti Agung Ayu Bitari Damar Ratih, Muhammad Arif Sobriyanto, Ni Made Ayu Wira Salindri, dan Rofii Sultan. “Dinda Putri Maulida” muncul di rancangan dan isi proposal, tetapi tidak pada sampul/daftar lima nama. | Perlu cocokkan roster resmi |
| Jira | Jira mengharuskan login pada saat audit. Isi HAP, HAP-1, HAP-61, sprint, epic, dan story tidak terbaca, jadi daftar pekerjaan Jira belum dapat dibandingkan satu per satu dengan 54 PBI rancangan. | Belum diverifikasi |
| Stitch | Proyek Stitch meminta login Google. Mockup dan komponen tidak terbaca. Halaman depan repo adalah placeholder, belum diverifikasi terhadap desain Stitch. | Belum diverifikasi |
| Repo | Repo publik sudah ada di organisasi kelas, dengan 5 kolaborator yang dapat menulis. Repositori awal berisi README saja. | Terverifikasi saat audit |
| Basis data | Rancangan menyebut PostgreSQL 18. Mesin pengembangan yang diperiksa memiliki PHP/Laragon, ekstensi PDO MySQL, dan MySQL 8.4. Driver PostgreSQL PHP belum aktif. Tidak ada server database aktif yang terdeteksi pada saat pemeriksaan. | Belum ada keputusan bersama; jangan anggap database lokal sedang berjalan |

## Ketentuan dari bahan kuliah

Ketentuan Proyek PPL menyebut tim terdiri dari 6–7 orang dan setiap anggota berperan sebagai pengembang. Bagian pembagian sprint menyatakan setiap anggota perlu menyelesaikan minimal satu fitur/fungsi dengan kompleksitas sedang hingga tinggi dalam setiap sprint. Fitur perlu terdiri dari sedikitnya tiga PBI dan tiap PBI ditulis sebagai user story. Contoh materi menunjukkan CRUD dapat dipecah menjadi empat PBI. Materi mengarahkan tim membangun, menguji, dan meninjau hasil pada tiap sprint.

Dengan demikian, hitungan yang dapat disimpulkan dari rancangan adalah:

| Asumsi jumlah anggota | Modul CRUD jika aturan 3×anggota berlaku | PBI bila 3 per modul | PBI rata-rata per orang |
| ---: | ---: | ---: | ---: |
| 6 | 18 | 54 | 9 |
| 5 | 15 | 45 | 9 |

Baris 5 orang hanyalah hitungan aritmetika, bukan konfirmasi bahwa tim beranggotakan lima orang memenuhi ketentuan kelas. Dosen perlu mengesahkan perubahan jumlah anggota atau memberi arahan lain. Jangan menghapus 3 modul hanya untuk membuat rancangan selaras sebelum keputusan itu ada.

Enam epic dan 54 story adalah struktur kerja rancangan/Jira, bukan angka yang secara eksplisit diwajibkan bahan kuliah. HJK-001 hingga HJK-054 adalah ID logis rancangan, bukan Jira issue key. Key proyek yang tampak pada URL adalah HAP.

## Dokumen yang ditelaah

- `Rancangan_Arsitektur_Hijauku_PPL.docx` — arsitektur, 18 modul, 54 PBI, tabel domain, sprint, peran, dan dua diagram. Catatan: uraian M02 menyebut kontak pengelola, sedangkan ringkasan kolom `sites` tidak mencantumkan kolom kontak; perlu diklarifikasi sebelum migration dianggap final.
- `Ketentuan Proyek PPL.pdf` — ketentuan tim pada halaman 2 dan aturan fitur/PBI/sprint pada halaman 7.
- `[SI-48-EXT A-B] Proposal Tugas Besar PPL.pdf` — sampul berisi lima anggota; uraian pembagian pada halaman 36 menyebut enam anggota/54 PBI; daftar modul dan tugas tersebar pada halaman 37–41.
- `MODUL 1 Reguler Praktikum Proyek Perangkat Lunak 2026.pdf` — issue management, Jira, version control, kontribusi, testing, peer review.
- `Slide Requirement Engineering-SRL.pdf`, `Slide Requirement Management-SRL.pdf`, `Slide Scrum Framework.pptx-SRL.pdf`, `Slide Sprinting.pptx-SRL.pdf`, dan `01 Agile Principles_v02.pptx-SRL.pdf` — materi pendukung tentang PBI, requirement, sprint, Scrum, dan prinsip agile.
- `desain.md` — catatan lokal berisi salinan deskripsi setup awal; file dipertahankan.

## Audit arsitektur dan dokumen

Rancangan menggambarkan satu aplikasi Laravel dengan panel internal Filament, halaman publik Blade, tiga peran, dan relasi domain yang cukup koheren untuk menjadi monolit modular. Pembagian M01–M18 dan aturan status memberi dasar yang baik untuk memecah pekerjaan menjadi stories.

Perbaikan yang masuk starter:

- Menyediakan tabel awal 18 modul dan dua tabel teknis untuk alokasi adopsi serta catatan aktivitas.
- Menambahkan kunci unik pohon di tabel alokasi agar aturan “satu pohon hanya punya satu alokasi aktif/terpesan” dapat dijaga lintas MySQL dan SQLite. Alur persetujuan tetap perlu memakai transaksi dan penguncian baris.
- Menambahkan kolom peran dan status aktif pada pengguna.
- Menandai basis data, roster, dan keterhubungan Stitch sebagai keputusan yang belum selesai.
- Mengganti gambar ERD ringkas di dokumen ini dengan daftar hubungan skema lengkap; gambar ERD pada dokumen arsitektur awal tidak menampilkan seluruh tabel bisnis.

Rancangan belum cukup menjadi spesifikasi implementasi final. Beberapa batasan bisnis harus diwujudkan pada validasi, policy, service, dan acceptance criteria: kapasitas petak, kuota kegiatan, pembatasan petugas ke penugasan sendiri, aturan transisi status, verifikasi pengamatan/perawatan, publikasi konten, dan audit koreksi. Rancangan skema starter belum mengimplementasikan semua alur tersebut.

## Yang perlu dicocokkan saat akses tersedia

1. Buka HAP-1 dan HAP-61; salin kriteria, subtasks, dependensi, status, dan assignee yang aktual ke catatan audit.
2. Ekspor/list semua epic, stories, dan sprint. Cocokkan tiap story ke modul M01–M18 dan ID HJK-001–054; tandai duplikat, story yang hilang, atau story tanpa kriteria penerimaan.
3. Pastikan total anggota resmi ke-6 dan peran tiap orang. Minta dosen mengonfirmasi bila lima nama memang jumlah tim yang sah.
4. Buka proyek Stitch dan petakan halaman, state, dan komponen UI ke rute serta peran. Ganti placeholder publik setelah rancangan dibaca.
5. Putuskan satu engine database untuk tim dan CI sebelum mengunci konfigurasi `.env.example` serta dokumentasi deployment.
