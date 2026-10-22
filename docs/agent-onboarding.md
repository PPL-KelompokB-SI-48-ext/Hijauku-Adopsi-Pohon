# Panduan agent untuk Hijauku

Berikan instruksi ini pada AI agent saat memulai tugas:

> Kamu membantu tim proyek kuliah Hijauku Adopsi Pohon. Sebelum mengubah kode, baca `AGENTS.md`, `docs/project-context.md`, dan dokumen yang relevan di `docs/`. Repo memakai Laravel 13, Filament 5, Blade, dan migrasi awal 18 modul. Perlakukan `docs/source-audit.md` sebagai batas fakta: Jira dan Stitch belum terbaca, jumlah anggota 5 pada roster/GitHub bertentangan dengan ketentuan 6–7 orang serta rancangan 6 anggota, dan pilihan database belum disahkan. Jangan mengarang isi Jira, desain Stitch, requirement, identitas anggota, atau persetujuan dosen. Pertahankan `desain.md` karena itu catatan proyek pengguna. Sebelum mengubah skema, role, status bisnis, atau struktur folder, jelaskan implikasi dan perbarui dokumentasi terkait. Gunakan branch/PR, buat perubahan sekecil mungkin, dan jangan commit `.env`, file pribadi, atau data nyata. Ikuti issue Jira dan acceptance criteria yang disediakan pengguna sebagai sumber scope.

## Urutan membaca

1. `AGENTS.md` — aturan kerja singkat.
2. `docs/project-context.md` — keputusan dan konteks jangka panjang.
3. `docs/source-audit.md` — apa yang sudah diperiksa dan apa yang belum.
4. `docs/requirements.md` atau `docs/architecture.md` — sesuai modul.
5. Issue Jira aktual dan desain Stitch setelah pengguna membuka akses.

## Prompt ringkas untuk tugas implementasi

```text
Kerjakan [JIRA KEY] sesuai acceptance criteria yang saya berikan. Baca AGENTS.md dan docs yang relevan. Sebelum mengubah kode, sebutkan singkat alur dan file yang akan disentuh. Jaga akses peran/pemilik data, status, constraint, dan transaksi sesuai domain. Perbarui dokumentasi bila requirement atau keputusan berubah. Jangan menebak detail Jira/Stitch yang belum saya berikan. Laporkan perubahan dan batas yang belum tertangani.
```
