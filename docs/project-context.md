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
- `.env.example` memakai MySQL untuk awal lokal; keputusan engine proyek belum dikonfirmasi. CI memakai SQLite.
- Role awal: `manager`, `field_staff`, `citizen`. Saat ini hanya scaffolding; policies dan pembatasan tiap Resource masih perlu dibuat.
- Role admin dapat masuk panel selama aktif; aturan pembatasan final wajib dikerjakan sebelum panel dibuka.

## Fakta dan konflik yang harus diingat

- Lima nama pada sampul proposal dan lima kolaborator repo: Yoga, Bitari, Arif, Wira, Rofii.
- Ketentuan kelas menyebut kelompok 6–7 orang. Rancangan arsitektur dan issue SETUP-001 menyebut 6 orang dan memasukkan Dinda yang tidak tampak pada sampul/daftar 5 nama.
- Ketentuan kelas: minimal satu fitur kompleksitas sedang/tinggi tiap orang pada masing-masing dua sprint; setiap fitur perlu sedikitnya tiga PBI. Ini berarti minimal 3 PBI/orang/sprint atau 6 PBI/orang untuk dua sprint bila tiap orang mengambil satu fitur minimal per sprint. Rumus 3 CRUD per orang tidak tertulis pada PDF aturan yang diberikan.
- Target rancangan 18 modul × 3 PBI = 54 story mengandaikan 6 orang. HJK-001–054 ialah ID logis, bukan Jira key.
- Jira HAP dan Stitch belum dapat dibuka karena meminta login. HAP-1 dan HAP-61 belum diaudit langsung.
- Dokumen arsitektur menyebut PostgreSQL 18. `.env.example` MySQL hanyalah keputusan setup sementara dan perlu diselaraskan setelah tim memilih DB.
- Halaman beranda starter adalah placeholder, tidak diklaim cocok dengan Stitch.
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

- Gunakan issue Jira aktual sebagai sumber scope. Kalau tidak tersedia, nyatakan apa yang belum bisa diverifikasi.
- Jangan mengubah batas tugas karena asumsi jumlah anggota.
- Perbarui `docs/architecture.md`, `docs/requirements.md`, dan `docs/source-audit.md` ketika keputusan berubah.
- Jangan menaruh kredensial/data nyata di repo; jangan arahkan migrasi ke database lokal pengguna tanpa persetujuan jelas dan verifikasi `.env`.
- Perubahan migration harus menjaga instalasi baru dan SQLite CI, kecuali tim sudah memilih engine lain.
- File keputusan penting harus singkat, bisa ditelusuri, dan ditulis untuk manusia.
