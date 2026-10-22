# Konteks proyek Hijauku untuk AI agent

## Identitas

- Nama: Hijauku — Adopsi Pohon.
- Repo: `PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon`.
- Tujuan: mendukung program penghijauan dari inventaris pohon, adopsi, kegiatan lapangan, perawatan, hingga informasi yang dibuka ke publik.
- Koordinator/PM yang meminta starter: Muhammad Yoga Adi Saputra.
- Bahasa dokumen dan UI: Bahasa Indonesia; waktu operasional: `Asia/Jakarta`.

## Stack starter

- PHP 8.3+, Laravel 13, Filament 5, Blade, Vite, Eloquent.
- Panel Filament di `/app`; halaman publik Blade.
- Migrasi awal mencakup 18 modul bisnis dan dua tabel teknis.
- Pemilik menyatakan DB MySQL lokal sudah berjalan; `.env` lokal memakai host localhost. Jangan mengubah atau menimpa `.env`. Engine tim belum diputuskan karena rancangan awal mengusulkan PostgreSQL 18. CI memakai SQLite.
- Role awal: `manager`, `field_staff`, `citizen`. Ketiga role aktif dapat masuk ke panel dasar; Policies dan pembatasan tiap Resource/ownership masih perlu dibuat.

## Fakta dan konflik yang harus diingat

- Pemilik proyek mengonfirmasi lima anggota: Yoga, Bitari, Arif, Wira, dan Rofii. Dinda tidak masuk tim. Lima nama juga tampak di sampul proposal dan audit GitHub sebelumnya melihat lima kolaborator.
- Ketentuan kelas menyebut kelompok 6–7 orang; penerimaan dosen atas tim lima orang masih perlu dikonfirmasi.
- Ketentuan kelas: minimal satu fitur kompleksitas sedang/tinggi tiap orang pada masing-masing dua sprint; setiap fitur perlu sedikitnya tiga PBI. Ini berarti minimal 3 PBI/orang/sprint atau 6 PBI/orang untuk dua sprint bila tiap orang mengambil satu fitur minimal per sprint. Rumus 3 CRUD per orang tidak tertulis pada PDF aturan yang diberikan.
- Jira aktual: 6 epic dan 45 story HJK-001–045 untuk M01–M15. Lima orang masing-masing memiliki 9 story pada tiga modul. HAP-6 (M16–M18) belum memiliki story; HAP-61 masih meminta enam anggota dan 54 story.
- HAP-1 adalah epic To Do; HAP-61 adalah task To Do di Sprint 0, parent HAP-1 dan assignee Yoga. HAP-1 mencantumkan Dusk; workflow CI kini menjalankan smoke test Dusk beranda publik serta tes PHPUnit.
- Jira memiliki tiga sprint berstatus `future`; Sprint 0 berakhir 18 Okt dan Sprint 1 mulai 19 Okt walau audit dilakukan 22 Okt. Sprint 2 ditulis pekan 11–14, sementara materi kuliah menyebut pekan 11–15.
- Stitch dapat dibaca. Desain memuat landing/detail/katalog publik dan panel role Pengelola, Warga, Petugas. Panduan batch terakhir mengecualikan domain M16–M18. Isi dan alur utama beranda kini mengikuti frame Stitch; pencocokan visual penuh, aset merek, dan halaman lain belum selesai.
- Dokumen arsitektur lama menyebut 18 modul/54 PBI. Rekomendasi scope aktif dari Jira adalah 15 modul/45 story; M16–M18 ditandai tertunda sampai ada backlog.
- `desain.md` adalah file proyek pengguna dan berisi salinan detail tugas awal; jangan menghapus atau menimpanya.

## Aturan domain utama

- Program memiliki lokasi; lokasi memiliki petak; petak menampung batch penanaman dan pohon.
- Pohon memiliki jenis; batch pohon harus cocok dengan petak pohon.
- Kuota kegiatan dan kapasitas petak perlu dilindungi dengan validasi serta transaksi.
- Adopsi harus mencegah dua persetujuan bersamaan untuk satu pohon. Tabel alokasi sekarang mengunci satu allocation per pohon sepanjang hidup; adopsi ulang belum diselesaikan.
- Petugas hanya mengisi data pada penugasan sendiri.
- Pengirim perawatan/pengamatan tidak boleh memverifikasi laporan sendiri.
- Data publik hanya boleh dibaca setelah diterbitkan atau diverifikasi sesuai aturan modul.
- Foto/bukti disimpan di storage privat dan diakses melalui otorisasi.

## Cara bekerja

- Gunakan issue Jira aktual sebagai sumber scope. Jangan mengarang data, dan catat selisih HAP-61 dengan isi Jira sebelum perubahan.
- Jangan mengubah batas tugas karena asumsi jumlah anggota.
- Perbarui `docs/architecture.md`, `docs/requirements.md`, dan `docs/source-audit.md` ketika keputusan berubah.
- Jangan menaruh kredensial/data nyata di repo; jangan arahkan migrasi ke database lokal pengguna tanpa persetujuan jelas dan verifikasi `.env`.
- Perubahan migration harus menjaga instalasi baru dan SQLite CI, kecuali tim sudah memilih engine lain.
- File keputusan penting harus singkat, bisa ditelusuri, dan ditulis untuk manusia.
