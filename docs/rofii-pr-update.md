# Panduan melanjutkan pekerjaan Rofii di M04

Catatan ini diperbarui setelah pemeriksaan GitHub dan Jira pada 10 November 2026. PR aktif M04 adalah PR #12 dari branch `Rofii` ke `main`. PM menerima nama branch tersebut; jangan menggantinya hanya karena huruf besar.

## Kondisi saat audit

- HAP-16/HJK-010, HAP-17/HJK-011, dan HAP-18/HJK-012 berstatus In Progress.
- PR #12 terbuka dan belum merged. Branch memiliki 5 commit yang belum ada di `main` dan tertinggal 7 commit dari `main` (`8930f589`).
- PR #12 dan diff saat ini menunjukkan pekerjaan HAP-16 serta pemilihan jenis pohon untuk HAP-17. Judul/body masih hanya menyebut HAP-16. HAP-18 tidak tampak di dalam PR.
- CI terakhir untuk branch ini, run `41434932370`, lulus pada head lama sebelum merge PR #16. Hasil itu belum memeriksa integrasi dengan `main` sekarang.
- Jira masih punya due date HAP-16 tanggal 1 November dan HAP-17 tanggal 8 November. Karena tanggal sudah lewat, pemilik perlu memberi rencana baru yang realistis di Jira.

## Langkah yang perlu dilakukan

1. Pastikan semua perubahan lokal sudah commit atau disimpan. Jangan menghapus atau mengganti nama branch.
2. Sinkronkan branch personal dengan `main`:

   ```powershell
   git fetch origin
   git switch Rofii
   git status --short
   git merge origin/main
   ```

3. Selesaikan konflik pada branch `Rofii`. Periksa perubahan dari kedua sisi, khususnya Policy, `AppServiceProvider`, migration, dan Resource. Jangan gunakan `git push --force`.
4. Tentukan bersama PM apakah PR #12 memang membawa HAP-16 dan HAP-17. Jika iya, perbarui judul/body dan checklist acceptance untuk dua issue itu. Jangan memasukkan HAP-18 ke PR sebelum implementasi dan tesnya siap. Jika HAP-17 belum siap, pisahkan perubahan HAP-17 dari PR sesuai kesepakatan dengan PM.
5. Pastikan pengujian mencakup izin manager saat membuat jenis pohon, validasi nama, audit log, serta pemilihan jenis yang sesuai oleh petugas. Tambahkan tes Dusk untuk alur M04 yang melalui browser. Jalankan tes dengan database SQLite yang terisolasi, bukan database pribadi atau MySQL bersama.
6. Jalankan pemeriksaan, lalu push ke branch personal:

   ```powershell
   php artisan test --testsuite=Unit
   php artisan test --testsuite=Feature
   php artisan dusk
   npm run build
   git push origin Rofii
   ```

7. Pastikan setiap story yang akan diberi status Done sudah memenuhi acceptance dan tes relevan di branch `Rofii` yang di-push. Periksa panel Development Jira dan perbarui status hanya untuk story yang memang selesai. PR akhir sprint dapat menunggu jadwal integrasi.
8. Perbarui due date story yang terlambat bersama PM. Original estimate adalah rencana; catat work log hanya untuk waktu yang benar-benar dipakai. Jangan mengarang actual time.

Menjelang akhir sprint, siapkan satu PR dari branch personal yang memuat semua story M04 sprint itu. Sinkronkan `origin/main`, selesaikan konflik, jalankan tes dan CI terbaru, lalu minta PM review. Pertahankan branch `Rofii` setelah merge dan sinkronkan kembali sebelum pekerjaan berikutnya. PR #12 sudah terbuka lebih awal, jadi tidak perlu membuat PR duplikat; rapikan PR yang ada sesuai arahan PM. Panduan umum ada di [CONTRIBUTING.md](../CONTRIBUTING.md) dan [alur Jira](jira-workflow.md).
