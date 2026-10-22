# Panduan kontribusi Hijauku

Panduan ini dipakai semua anggota tim agar issue, branch, perubahan, dan review tetap mudah ditelusuri. PM adalah Yoga (@mygads). Anggota menulis kode dan memperbaiki konflik; PM meninjau dan menggabungkan perubahan ke main.

## 1. Ambil satu PBI

1. Pilih story yang memang ditugaskan kepada Anda di board Jira HAP. Scope aktif menggunakan lima epic dan 45 story untuk M01 sampai M15. HAP-6 telah dihapus; issue key story yang sudah terbit tetap seperti semula.
2. Baca tujuan, acceptance criteria, dependensi, dan modulnya. Bila scope tidak jelas, diskusikan di issue sebelum coding.
3. Pastikan story berada di sprint yang direncanakan dan statusnya sesuai.
4. Jangan mengerjakan M16 sampai M18. Area itu tidak termasuk backlog aktif.

Satu story harus menghasilkan perubahan yang dapat ditinjau. Bila story terlalu besar atau acceptance criteria berubah, minta PM memecah atau memperbarui issue terlebih dahulu.

## 2. Buat branch dari main terbaru

Jangan commit atau push langsung ke main.

    git switch main
    git pull --ff-only origin main
    git switch -c feature/HAP-25-nama-singkat

Gunakan Jira key pada branch. Awalan yang disarankan: `feature/` untuk fitur, `fix/` untuk perbaikan, `docs/` untuk dokumentasi, dan `chore/` untuk konfigurasi.

## 3. Implementasikan dan periksa

- Baca AGENTS.md dan dokumen modul terkait.
- Gunakan desain di Stitch sebagai rujukan UI untuk story yang ditugaskan. Jangan mengubah scope atau menambah fitur di luar acceptance criteria tanpa diskusi dengan PM.
- Pertahankan batas role dan kepemilikan data di Policy, query, serta service.
- Perubahan database harus lewat migration; jangan mengubah database bersama secara manual.
- Periksa .env sebelum menjalankan migrasi. Jangan menjalankan migrate:fresh terhadap database yang berisi data.
- Jalankan pemeriksaan yang relevan dan catat hasilnya di issue/PR.
- Jangan masukkan secret, data pribadi, atau foto nyata ke commit.

Pola commit yang disarankan:

    git status --short
    git add app/Models/User.php docs/architecture.md
    git commit -m "feat: catat pendaftaran kegiatan [HAP-25]"

Stage hanya file yang memang menjadi bagian perubahan Anda. Hindari git add . agar file lokal seperti desain.md atau .playwright-mcp tidak ikut ter-commit.

## 4. Sinkronkan branch dan selesaikan konflik

Penulis branch bertanggung jawab menyelesaikan konflik sebelum meminta review, atau setelah PM meminta perubahan. Jangan meminta reviewer menyelesaikan konflik untuk Anda.

    git fetch origin
    git merge origin/main

Jika Git memberi konflik, buka file yang disebut oleh git status, pilih hasil gabungan yang benar, hapus penanda konflik, lalu:

    git add path/file-yang-selesai
    git commit
    git push

Jangan memakai git push --force. Bila konflik menyentuh skema, aturan role, atau kontrak bersama, koordinasikan keputusan dengan PM sebelum memilih hasilnya.

Penulis branch menyelesaikan konflik sebelum meminta review dan memperbarui PR setelah push. Reviewer memberi masukan; ia tidak mengambil alih branch penulis.

## 5. Buka pull request

Push branch dan buat PR ke main. Pakai judul seperti:

    [HAP-25] Pendaftaran kegiatan oleh warga

Di isi PR, sertakan:

- tautan issue Jira dan ringkasan hasil;
- acceptance criteria yang dipenuhi;
- perubahan skema atau konfigurasi;
- pemeriksaan yang dijalankan dan hasilnya;
- screenshot bila UI berubah;
- risiko, batasan, atau pekerjaan lanjutan.

Minta review dari @mygads. Jangan merge PR sendiri. Perubahan oleh PM juga mengikuti aturan satu persetujuan; PM meminta satu anggota lain meninjau PR tersebut.

## 6. Review dan merge

- CI wajib hijau.
- PR wajib mendapat satu approval.
- Selesaikan semua komentar dan percakapan review.
- Hanya PM yang menggabungkan PR ke main. Cara gabung yang dipakai repo adalah squash merge.
- Setelah merge, hapus branch di GitHub dan tarik main terbaru.

## Konvensi proyek

- Tulis UI dan dokumentasi untuk pengguna dalam Bahasa Indonesia.
- Nama class/model mengikuti konvensi Laravel; nama tabel dan kolom mengikuti snake_case.
- Setiap model baru harus mempunyai Policy dan tes akses sebelum ditampilkan pada panel.
- Setiap list non-pengelola harus membatasi query ke data yang boleh dilihat. Izin viewAny saja bukan filter kepemilikan.
- Aturan kuota, kapasitas, alokasi pohon, transisi status, dan verifikasi perlu service/transaksi serta tes.
- Beri nama migration sesuai perubahan dan pertahankan migration yang sudah dipakai oleh tim.
- Perbarui docs dan issue bila keputusan atau acceptance criteria berubah.

## Membuat fitur Filament

Tentukan panel berdasarkan role: pengelola memakai `app`, petugas memakai `petugas`, dan warga memakai `warga`. Folder setiap panel sudah dikonfigurasi pada provider masing-masing. Perintah generator memilih panel dan direktori Resource yang sesuai:

```powershell
php artisan make:filament-resource Tree --panel=app
php artisan make:filament-resource FieldAssignment --panel=petugas
php artisan make:filament-resource AdoptionRequest --panel=warga
php artisan make:filament-page RingkasanPerawatan --panel=petugas
```

Gunakan nama model yang benar-benar ada atau yang dibuat lewat migration pada PBI. Periksa lokasi dan namespace file hasil generator; jangan letakkan Resource di panel role yang salah. Untuk satu alur bisnis, Resource biasanya menyediakan daftar, buat, dan ubah record. Buat Page bila kebutuhan berupa tampilan atau aksi khusus yang bukan operasi CRUD biasa.

Sebelum Resource dapat di-review:

1. Pastikan acceptance criteria Jira mencakup peran, kepemilikan record, status, dan hasil untuk kondisi ditolak.
2. Tambah atau ubah migration untuk schema. Buat relasi, constraint, index, dan aturan hapus yang sesuai. Jangan mengubah database bersama dengan SQL manual.
3. Daftarkan Policy atau perbarui Policy domain. Role gate panel tidak cukup untuk mengotorisasi aksi record.
4. Batasi query tabel dengan scope ownership. Izin `viewAny` hanya memberi akses ke daftar, bukan memfilter record.
5. Validasi input dan gunakan service + transaksi untuk kuota, kapasitas, alokasi, review, accept, verify, atau perubahan status lintas record.
6. Untuk file bukti, pakai disk privat, validasi jenis dan ukuran, lalu otorisasi setiap unduhan.
7. Tambahkan tes untuk aksi yang diizinkan dan ditolak, role lain, record milik orang lain, status tidak sah, dan konkurensi yang relevan.
8. Periksa route, tes Unit/Feature yang relevan, dan `npm run build` bila UI atau aset berubah. Perbarui dokumen modul dan screenshot PR bila tampilannya berubah.

Generator mempercepat kerangka class. Ia tidak membuat aturan akses dan alur bisnis proyek secara otomatis.

## Bahasa dan gaya dokumentasi

Tulis penjelasan dengan Bahasa Indonesia yang wajar, spesifik, dan mudah dipindai. Gunakan nama perintah, file, dan issue yang tepat. Hindari kalimat promosi, pengulangan, dan frasa generik yang tidak membantu pembaca. Gunakan tanda hubung biasa `-`, bukan em dash atau en dash. Jelaskan status dengan jujur: sebut kerangka sebagai scaffold, jangan menyatakan fitur selesai sebelum acceptance criteria dan pemeriksaannya terpenuhi.
