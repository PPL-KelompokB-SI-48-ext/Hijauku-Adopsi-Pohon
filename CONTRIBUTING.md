# Panduan kontribusi Hijauku

Panduan ini dipakai semua anggota tim agar issue, branch, perubahan, dan review tetap mudah ditelusuri. PM adalah Yoga (@mygads). Setiap anggota memakai satu branch personal untuk seluruh story miliknya. Penulis menyelesaikan konflik; PM meninjau dan menggabungkan PR ke main.

## 1. Ambil satu PBI

1. Pilih story yang memang ditugaskan kepada Anda di board Jira HAP. Scope aktif menggunakan lima epic dan 45 story untuk M01 sampai M15. HAP-6 telah dihapus; issue key story yang sudah terbit tetap seperti semula.
2. Baca tujuan, acceptance criteria, dependensi, dan modulnya. Bila scope tidak jelas, diskusikan di issue sebelum coding.
3. Pastikan story berada di sprint yang direncanakan dan statusnya sesuai.
4. Jangan mengerjakan M16 sampai M18. Area itu tidak termasuk backlog aktif.

Satu story harus menghasilkan perubahan yang dapat ditinjau. Bila story terlalu besar atau acceptance criteria berubah, minta PM memecah atau memperbarui issue terlebih dahulu.

## 2. Gunakan satu branch personal

Branch personal dibuat satu kali dari `main` dan dipakai untuk semua story anggota tersebut. Jangan membuat branch baru untuk setiap fitur. Nama branch tim adalah `yoga`, `bitari`, `arif`, `wira`, dan `rofii`, ditulis dengan huruf kecil.

Pada clone pertama, buat branch personal dari `main` terbaru:

    git fetch origin
    git switch --create yoga origin/main
    git push --set-upstream origin yoga

Ganti `yoga` dengan nama branch pribadi Anda. Jika branch personal sudah ada, pindah ke branch itu dan ambil commit terbaru:

    git fetch origin
    git switch yoga
    git pull --ff-only origin yoga

Jangan commit atau push langsung ke `main`. Branch personal tidak memakai Jira key karena satu branch memuat beberapa issue. Cantumkan key HAP yang sesuai pada setiap commit dan PR agar Jira dapat menautkan pekerjaan. Jangan mulai dua perubahan yang perlu PR terpisah pada waktu bersamaan di branch yang sama; selesaikan dan gabungkan PR lebih dulu, lalu sinkronkan branch. Dengan begitu isi PR berikutnya tidak ikut membawa pekerjaan yang belum siap.

### Pindahkan pekerjaan dari branch lama

Beberapa PR lama mungkin masih memakai nama `feature/HAP-...`. Jangan memakai pola itu untuk story baru. Jika PR Anda masih terbuka, pindahkan commit yang belum tergabung ke branch personal Anda, lalu buat PR pengganti. Periksa commit yang belum ada di `main` sebelum migrasi. Jangan menghapus atau mengganti nama branch yang masih menjadi head PR sebelum PR pengganti siap. GitHub menutup PR terbuka ketika head branch-nya diganti nama ([dokumentasi GitHub](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-branches-in-your-repository/renaming-a-branch)), jadi ikuti proses migrasi ini bersama PM dan tutup PR lama setelah perubahan serta issue link pada PR baru sudah diperiksa.

Jika branch personal belum ada, contoh pemindahan satu commit yang belum tergabung:

```powershell
git fetch origin
git switch --create rofii origin/main
git cherry-pick SHA_COMMIT_HAP_16
git push --set-upstream origin rofii
```

Ganti `rofii` dengan nama branch personal Anda dan `SHA_COMMIT_HAP_16` dengan hash yang benar setelah membandingkan branch lama dengan `origin/main`. Jangan menyalin placeholder tanpa pemeriksaan. Jika branch personal sudah ada, pindah ke branch itu sebelum cherry-pick. Buat PR baru dengan semua key HAP yang dibawa, lalu minta PM memeriksa commit dan acceptance sebelum menutup PR lama.

## 3. Implementasikan dan periksa

- Baca AGENTS.md dan dokumen modul terkait.
- Gunakan desain di Stitch sebagai rujukan UI untuk story yang ditugaskan. Jangan mengubah scope atau menambah fitur di luar acceptance criteria tanpa diskusi dengan PM.
- Pertahankan batas role dan kepemilikan data di Policy, query, serta service.
- Perubahan database harus lewat migration; jangan mengubah database bersama secara manual.
- Periksa .env sebelum menjalankan migrasi. Jangan menjalankan migrate:fresh terhadap database yang berisi data.
- Jalankan pemeriksaan yang relevan dan catat hasilnya di issue/PR.
- Jangan masukkan secret, data pribadi, atau foto nyata ke commit.

Gunakan Jira key dalam teks commit tanpa tanda kurung agar mudah ditemukan oleh integrasi Jira:

    git status --short
    git add app/Models/User.php docs/architecture.md
    git commit -m "feat: catat rencana kegiatan HAP-25"

Push branch ke GitHub setelah commit. Jira baru dapat menampilkan commit dan pull request pada panel Development jika repository GitHub sudah terhubung ke Jira. Setelah push, buka issue dan periksa panel Development. Jika belum muncul, PM memeriksa koneksi integrasi dan ejaan key; jangan menganggap commit sudah tertaut hanya karena key ada di komputer lokal.

Stage hanya file yang memang menjadi bagian perubahan Anda. Hindari git add . agar file lokal seperti desain.md atau .playwright-mcp tidak ikut ter-commit.

## 4. Sinkronkan branch dan selesaikan konflik

Pemilik branch menyinkronkan branch personal dengan `main` terbaru setelah PR digabung dan sebelum melanjutkan pekerjaan. Pemilik branch bertanggung jawab menyelesaikan konflik; jangan meminta reviewer menyelesaikannya.

    git fetch origin
    git merge origin/main

Jika Git memberi konflik, buka file yang disebut oleh `git status`, pilih hasil gabungan yang benar, hapus penanda konflik, lalu:

    git add path/file-yang-selesai
    git commit
    git push

Jangan memakai git push --force. Bila konflik menyentuh skema, aturan role, atau kontrak bersama, koordinasikan keputusan dengan PM sebelum memilih hasilnya.

Selesaikan konflik pada branch personal, jalankan pemeriksaan yang relevan, lalu push hasilnya. Jangan memakai `git push --force`. Reviewer memberi masukan; ia tidak mengambil alih branch pemilik.

## 5. Buka pull request

Push branch personal dan buat PR ke `main` setelah story atau kelompok story yang dibawa PR memenuhi acceptance criteria. Sertakan semua key HAP yang dibawa PR. Sebelum membuka PR baru, pastikan tidak ada perubahan issue lain yang belum siap di branch personal. Contoh satu story:

    [HAP-25] Catat rencana kegiatan

Di isi PR, sertakan:

- tautan semua issue Jira yang dibawa PR dan ringkasan hasil;
- acceptance criteria yang dipenuhi;
- perubahan skema atau konfigurasi;
- pemeriksaan yang dijalankan dan hasilnya;
- screenshot bila UI berubah;
- risiko, batasan, atau pekerjaan lanjutan.

Anggota meminta review dari @mygads dan tidak merge PR sendiri. PR Yoga tidak memerlukan self-approval; Yoga memakai admin bypass setelah CI hijau. Admin bypass juga melewati enforcement branch protection, jadi aturan tim tetap mewajibkan CI lulus sebelum merge.

## 6. Review dan merge

- CI wajib hijau.
- PR anggota wajib mendapat satu approval dari PM.
- PR yang dibuat PM tidak memerlukan approval; PM memakai admin bypass hanya setelah CI lulus.
- Selesaikan semua komentar dan percakapan review.
- Hanya PM yang menggabungkan PR ke `main`. Riwayat PR repo saat ini memakai merge commit; pertahankan cara itu agar branch personal dapat disinkronkan tanpa force push.
- Jangan hapus branch personal setelah merge. Tarik `main` terbaru ke branch personal sebelum mengambil story berikutnya.
- Hapus remote branch sementara hanya setelah PR terkait merged/closed, tidak ada PR terbuka lain yang memakai branch itu, dan `git log origin/main..branch-lama` tidak menunjukkan commit unik. Pemilik branch mengonfirmasi tidak ada pekerjaan lokal yang belum dipindahkan. Jangan hapus branch personal.

## Estimasi dan catatan waktu

- Original estimate di Jira adalah perkiraan sebelum pekerjaan dimulai. Isi saat story siap masuk sprint, setelah acceptance criteria dan dependensinya jelas.
- Work log adalah waktu yang benar-benar dipakai oleh orang yang mengerjakan. Catat waktu aktual setelah bekerja, jangan menyalin estimasi, durasi sprint, atau perkiraan dari anggota lain.
- Perbarui remaining estimate bila rencana berubah. Bila story membesar, belum jelas, atau tidak muat dalam sprint, beri tahu PM sebelum menambah scope.
- Jangan mengisi waktu aktual untuk pekerjaan yang belum dilakukan. Jangan mengubah work log agar sama dengan estimasi.
- Jika Jira belum mengaktifkan field Time tracking atau Log work, catat hambatan itu di issue dan minta PM mengatur field/izin. Jangan mengganti work log dengan angka di komentar.
- Tanggal sprint menunjukkan jendela perencanaan tim; tanggal itu bukan bukti durasi aktual untuk setiap story. Due date hanya dipakai untuk tenggat yang memang disepakati.

Format lengkap untuk isi issue, Original estimate, work log, dan pengecekan tautan commit dijelaskan di [alur Jira dan pencatatan waktu](docs/jira-workflow.md).

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
