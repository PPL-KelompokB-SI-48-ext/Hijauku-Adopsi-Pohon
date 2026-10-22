# Panduan kontribusi Hijauku

Panduan ini dipakai semua anggota tim agar issue, branch, perubahan, dan review tetap mudah ditelusuri. PM adalah Yoga (@mygads). Anggota menulis kode dan memperbaiki konflik; PM meninjau dan menggabungkan perubahan ke main.

## 1. Ambil satu PBI

1. Pilih story yang memang ditugaskan kepada Anda di board Jira HAP.
2. Baca tujuan, acceptance criteria, dependensi, dan modulnya. Bila scope tidak jelas, diskusikan di issue sebelum coding.
3. Pastikan story berada di sprint yang direncanakan dan statusnya sesuai.
4. Jangan mengerjakan M16–M18: area itu sedang ditunda.

Satu story harus menghasilkan perubahan yang dapat ditinjau. Bila story terlalu besar atau acceptance criteria berubah, minta PM memecah atau memperbarui issue terlebih dahulu.

## 2. Buat branch dari main terbaru

Jangan commit atau push langsung ke main.

    git switch main
    git pull --ff-only origin main
    git switch -c feature/HAP-25-nama-singkat

Gunakan Jira key pada branch. Awalan yang disarankan: feature untuk fitur, fix untuk perbaikan, docs untuk dokumentasi, dan chore untuk konfigurasi.

## 3. Implementasikan dan periksa

- Baca AGENTS.md dan dokumen modul terkait.
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
