# Panduan agent untuk Hijauku

Berikan instruksi ini pada AI agent saat memulai tugas:

> Kamu membantu proyek kuliah Hijauku Adopsi Pohon. Baca `AGENTS.md`, `docs/project-context.md`, `docs/source-audit.md`, dan dokumen modul sebelum mengubah kode. Stack starter Laravel 13 + Filament 5 + Blade. Pemilik proyek mengonfirmasi tim berisi lima orang (Yoga, Bitari, Arif, Wira, Rofii); Dinda tidak termasuk. Bahan kuliah meminta 6–7 orang, jadi jangan mengklaim kepatuhan sebelum dosen memberi arahan. Jira aktual berisi 6 epic dan 45 story untuk M01–M15 (9 per anggota); HAP-6 kosong, HAP-61 masih meminta 6 anggota/54 story. Sprint 0/1 punya status yang tertinggal. Stitch dapat dibaca; jangan membuat UI M16–M18 sebelum scope itu kembali dibuka. Pemilik menyatakan MySQL lokal sudah berjalan: jangan menimpa `.env` atau menjalankan migrasi ke DB itu tanpa izin. Pertahankan `desain.md`. Gunakan branch/PR, jangan commit secret/data nyata, dan sebutkan dengan jelas apa yang scaffold dan apa yang sudah berfungsi.

## Urutan membaca

1. `AGENTS.md` — aturan kerja singkat.
2. `docs/project-context.md` — keputusan dan konteks jangka panjang.
3. `docs/source-audit.md` — apa yang sudah diperiksa dan apa yang belum.
4. `docs/requirements.md` atau `docs/architecture.md` — sesuai modul.
5. Issue Jira aktual dan frame Stitch yang relevan; hasil akses dan selisihnya dicatat di `docs/source-audit.md`.

## Prompt ringkas untuk tugas implementasi

```text
Kerjakan [JIRA KEY] sesuai acceptance criteria yang saya berikan. Baca AGENTS.md dan docs yang relevan. Sebelum mengubah kode, sebutkan singkat alur dan file yang akan disentuh. Jaga akses peran/pemilik data, status, constraint, dan transaksi sesuai domain. Perbarui dokumentasi bila requirement atau keputusan berubah. Jangan menebak detail Jira/Stitch yang belum saya berikan. Laporkan perubahan dan batas yang belum tertangani.
```
