# Panduan menyelesaikan PR M04 Rofii

Catatan ini menjelaskan perpindahan pekerjaan HAP-16 dari PR lama ke branch personal. Gunakan Jira untuk acceptance dan status terbaru.

## Posisi sekarang

- PR #3 memakai branch `feature/HAP-16-jenis-pohon`, pola lama satu branch per fitur. PR #3 sudah ditutup dengan perubahan yang diminta.
- PR #6 sudah dibuka dari branch personal `rofii`, dengan commit `22737f8`. CI run `40184824535` lulus pada commit tersebut.
- PR #6 belum mendapat approval dan berstatus changes requested. Branch-nya masih berbasis `d683407`, sedangkan `main` sekarang memuat merge PR #4 pada `892ec72`.
- PR #6 sudah memiliki tes Feature, tetapi belum memiliki tes Dusk khusus untuk alur M04. Karena itu CI yang hijau belum cukup untuk menyatakan HAP-16 selesai.
- Branch lama tetap disimpan sementara agar tidak ada pekerjaan yang hilang. HAP-16 dan parent HAP-2 saat ini In Progress. HAP-16 belum Done sampai acceptance, tes, review, dan merge beres.

## Langkah Rofii

Branch `rofii` sudah ada di GitHub. Jangan membuat branch baru lagi. Sebelum mulai, pastikan perubahan lokal aman dan tidak ada file lain yang belum di-commit.

```powershell
git fetch origin
git switch rofii
git status --short
git merge origin/main
```

Jika `git status` menunjukkan perubahan lokal, simpan atau commit dulu sesuai pekerjaan yang sedang dilakukan. Jika merge menemukan konflik, buka file yang disebut Git, pilih isi yang benar, lalu tandai file selesai dan buat merge commit:

```powershell
git add path/file-yang-sudah-diperbaiki
git commit
git push origin rofii
```

Jangan memakai `git push --force`. Bila konflik menyentuh schema, akses, atau acceptance, minta keputusan Yoga sebelum memilih hasilnya.

Setelah branch memakai `main` terbaru, tambahkan Laravel Dusk untuk alur utama HAP-16: login sebagai manager, membuat jenis pohon dari panel, lalu memastikan jenis dan kebutuhan perawatannya muncul pada daftar. Tes Feature yang sudah ada tetap dipakai untuk validasi dan kondisi yang ditolak. Jalankan Dusk dengan database uji terisolasi seperti yang dipakai workflow CI; jangan arahkan tes ke database pribadi.

Pastikan judul dan isi PR tetap menyebut HAP-16. Tunggu CI hijau pada commit terbaru, lalu minta Yoga (@mygads) meninjau. Penulis memperbaiki komentar dan konflik. PM yang merge setelah seluruh acceptance terpenuhi.

## Branch lama

Jangan hapus `feature/HAP-16-jenis-pohon` sekarang. Pemilik branch memeriksa ulang semua commit dan perubahan file setelah PR #6 disinkronkan. Hapus branch lama hanya setelah PR #6 lengkap, tidak ada PR lain yang menggunakannya, dan tidak ada perubahan unik yang tertinggal. Branch personal `rofii` dipertahankan setelah merge.

## Alur umum anggota lain

Setiap anggota menggunakan satu branch personal: `yoga`, `bitari`, `arif`, `wira`, atau `rofii`. Untuk repo yang sudah di-clone, ambil `origin/main` terbaru sebelum melanjutkan pekerjaan. Cantumkan key HAP di setiap commit dan PR, buat PR ke `main`, lalu minta Yoga sebagai reviewer. Pemilik branch menyelesaikan konflik dan hanya PM yang merge.

Panduan clone, setup, test, commit, dan PR ada di [README](../README.md), [CONTRIBUTING](../CONTRIBUTING.md), serta [setup lokal](local-setup.md).
