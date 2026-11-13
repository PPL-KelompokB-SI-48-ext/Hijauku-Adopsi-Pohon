# CI dan sinkronisasi branch

Panduan ini menjelaskan kapan pemeriksaan berjalan dan apa yang harus dilakukan sebelum PR digabung ke `main`.

## Kapan CI berjalan

Workflow ada di `.github/workflows/ci.yml`. Setelah perubahan workflow ini masuk ke `main`, CI berjalan pada tiga tahap:

1. **Push ke branch personal.** Setiap push ke `yoga`, `bitaridamar`, `arif`, `ayuwira`, `Rofii`, atau `rofii` menjalankan seluruh job. Hasil ini memberi kabar lebih awal kepada pemilik branch.
2. **PR menuju `main`.** CI berjalan saat PR dibuka dan setiap kali commit baru di-push. Job menguji hasil gabungan branch PR dengan `main` yang menjadi targetnya. Pemeriksaan `install-and-build` ini wajib lulus agar PR bisa digabung.
3. **Push ke `main`.** Setelah PR digabung, CI berjalan lagi untuk memeriksa hasil integrasi yang sudah ada di branch utama.

Jika branch PR memiliki konflik, GitHub menandai PR tidak dapat digabung dan workflow `pull_request` tidak berjalan. Konflik adalah kondisi Git yang harus diselesaikan pemilik branch. Itu bukan hasil tes yang gagal.

Push ke branch personal tidak mengubah `main`. CI di branch juga tidak menggantikan CI PR, review PM, atau pemeriksaan acceptance. Status story Jira mengikuti kriteria penerimaan dan aturan Done pada issue.

## Isi pemeriksaan

Job `install-and-build` menjalankan:

- validasi `composer.json` dan `composer.lock`;
- pemasangan dependency PHP dan JavaScript;
- migrasi dan seeder pada SQLite serta MySQL uji;
- pemeriksaan route panel `/app`, `/petugas`, dan `/warga`;
- build aset dengan `npm run build`;
- tes Unit, Feature, dan Laravel Dusk pada database SQLite sementara.

Dusk memakai database dan server uji terpisah. Jangan mengarahkan tes atau migration CI ke database lokal bersama. Untuk menjalankan tes di komputer sendiri, ikuti [panduan setup lokal](local-setup.md), terutama bagian database Dusk.

CI hijau berarti pemeriksaan otomatis yang tercantum di atas lulus pada commit yang diuji. CI tidak membuktikan semua kriteria bisnis, kesesuaian seluruh layar dengan Stitch, atau kesiapan UAT. Pemilik dan PM tetap memeriksa acceptance; perubahan tampilan juga perlu dibandingkan dengan frame Stitch yang tersedia.

## Sinkronkan branch dengan `main`

Lakukan ini sebelum membuat PR dan ulangi setiap kali PR anggota lain digabung lebih dulu:

```powershell
git status --short
git fetch origin
git switch NAMA_BRANCH_PERSONAL
git merge origin/main
```

Pastikan perubahan lokal yang belum dicommit sudah diamankan sebelum merge. Bila Git menemukan konflik:

1. Jalankan `git status` untuk melihat file yang perlu diperbaiki.
2. Buka setiap file, pilih isi gabungan yang benar, lalu hapus penanda `<<<<<<<`, `=======`, dan `>>>>>>>`.
3. Bila konflik menyangkut schema, role, atau kontrak lintas modul, minta keputusan PM sebelum menentukan hasilnya.
4. Tandai file yang sudah selesai dan buat commit dengan key HAP yang terkait:

```powershell
git add path/file-yang-sudah-diperbaiki
git commit -m "merge: sinkronkan main HAP-XX"
git push origin NAMA_BRANCH_PERSONAL
```

Jangan memakai `git push --force`. Jika belum yakin dengan hasil konflik, batalkan merge menggunakan `git merge --abort`, lalu minta bantuan PM. Setelah push, buka PR dan pastikan status CI berubah ke commit terbaru.

Branch protection `main` mewajibkan status `install-and-build`, satu approval untuk PR anggota, dan penyelesaian percakapan. Pemeriksaan status diatur agar branch PR harus mengikuti `main` terbaru. Karena itu, setelah PR lain digabung, PR yang belum digabung akan perlu disinkronkan lagi. Perubahan pada branch dapat membatalkan approval sebelumnya, jadi pemilik meminta review ulang setelah CI baru lulus.

## Checklist pemilik branch

Sebelum meminta PM menggabungkan PR, pastikan:

- [ ] Commit ada di branch personal yang benar dan memuat key HAP.
- [ ] Acceptance criteria serta tes fitur yang relevan sudah diperiksa.
- [ ] Branch sudah disinkronkan dengan `origin/main` terbaru dan tidak memiliki konflik.
- [ ] Pemeriksaan `install-and-build` pada PR lulus untuk commit terbaru.
- [ ] PR menyebut issue HAP, acceptance yang dipenuhi, tes yang dijalankan, perubahan schema/konfigurasi, dan screenshot bila UI berubah.
- [ ] Review PM sudah disetujui dan semua percakapan review selesai.

## Jika CI gagal

1. Buka tab **Checks** pada PR atau tab **Actions** di GitHub.
2. Buka job `install-and-build`, lalu cari step merah pertama. Step setelahnya bisa ikut gagal akibat error awal itu.
3. Jika Dusk gagal, unduh artifact `dusk-screenshots` bila tersedia dan periksa log server pada step tes.
4. Perbaiki masalah pada branch personal, jalankan pemeriksaan yang sesuai, lalu push. Push baru akan memulai pemeriksaan ulang.
5. Jangan meminta merge sebelum CI terbaru lulus. Jangan mengubah status Jira menjadi Done hanya karena pemeriksaan lama pernah lulus.

## Catatan nama branch

GitHub saat ini memiliki branch bernama `Rofii` dan `rofii` dengan huruf besar/kecil berbeda. Keduanya sementara mendapat CI push agar tidak ada pekerjaan yang terlewat. Pemilik dan PM menentukan branch personal yang dipakai untuk pekerjaan berikutnya. Jangan rename atau menghapus salah satunya selama masih ada PR atau commit unik.
