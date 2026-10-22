# Persiapan Sprint 0 dan sprint formal

## Sprint 0: fondasi

HAP-1 adalah epic **Fondasi & Infrastruktur** (To Do). HAP-61 adalah task **SETUP-001** (To Do), parent HAP-1, assignee Yoga, dan masuk Sprint 0 - Persiapan.

### Isi dan penerimaan HAP-61 yang aktual

HAP-61 meminta repo GitHub, pemetaan tugas, dokumen arsitektur rev1, dan Laravel/Filament starter. Acceptance criteria masih menyebut 6 anggota yang punya akses dan 6 epic + 54 story. Pemilik proyek telah mengonfirmasi ada 5 anggota tanpa Dinda. Jira aktual memiliki 6 epic dan 45 story untuk M01–M15. Rekomendasi audit adalah memperbarui kriteria menjadi lima anggota/45 story dan menahan M16–M18 sampai backlog-nya dibuka; issue Jira sendiri belum diubah.

| Kriteria HAP-61 | Kondisi saat audit |
| --- | --- |
| Repo GitHub dan akses anggota | Repo sudah ada dan audit sebelumnya menemukan 5 kolaborator. Cocok dengan roster pemilik; acceptance criterion masih meminta 6 anggota. |
| 6 epic + 54 story + sprint di Jira | Ada 6 epic, 45 story aktif, dan 3 sprint. HAP-6/M16–M18 kosong. Selaraskan acceptance criterion dengan backlog yang dipilih PM. |
| Dokumen arsitektur rev1 final dan dibagikan | Dokumen rev1 tersedia di PR untuk ditinjau tim; statusnya masih draf sampai keputusan database/scope dan persetujuan tim dicatat. |
| Template Laravel + Filament yang bisa dijalankan | Starter, migration, seeder demo, README, workflow CI, PHPUnit, dan Dusk beranda publik tersedia di PR. PHPUnit, Dusk lokal, serta build aset sudah lolos; CI PR perlu dilihat setelah perubahan dipush. |

### Kondisi starter

- [x] Audit bahan kuliah, rancangan arsitektur, proposal, dan `desain.md`.
- [x] Siapkan skeleton Laravel 13 + Filament 5, model, migration, role dasar, dan seeder demo.
- [x] Tambahkan CI instalasi, migrasi/seeder SQLite, pemeriksaan route panel, dan build Vite.
- [x] Tulis README, requirement, rancangan arsitektur, skema database, serta konteks agent.
- [x] Buat dan push draft PR untuk review tim.
- [x] Baca HAP-1, HAP-61, 45 story, 6 epic, dan 3 sprint dalam Jira.
- [x] Baca struktur frame dan instruksi UI pada proyek Stitch.
- [x] Selaraskan isi utama beranda publik dengan frame Stitch 21 dan pasang smoke test Dusk.
- [ ] Selaraskan kriteria HAP-61 (5 orang/45 story atau putuskan tambahan M16–M18).
- [ ] Minta konfirmasi dosen tentang syarat kelompok 6–7 orang.
- [ ] Lengkapi pencocokan visual beranda, aset merek, dan shell tiap role sesuai frame Stitch.
- [ ] Tambah Policy dan pembatasan role/ownership pada resource sebelum membuka data melalui panel.
- [ ] Perbarui status/tanggal sprint yang telah lewat.
- [ ] Putuskan apakah tabel M16–M18 dipertahankan sebagai scope tertunda.
- [ ] Sepakati database tim; jangan menimpa `.env` lokal yang sudah bekerja.
- [ ] Minta review arsitektur dari tim dan tandai final setelah disetujui.
- [ ] Jangan tandai HAP-1/HAP-61 selesai sebelum acceptance criteria tersisa dipenuhi.

HAP-1 secara khusus memuat autentikasi, RBAC tiga peran, panel Filament `/app`, dashboard dasar, GitHub Actions + Dusk, dan seeder demo. Starter punya login/panel dasar, enum tiga role, seeder, workflow CI, tes akses panel, dan smoke test Dusk untuk beranda publik. Gate panel menerima akun aktif dari ketiga role; pembatasan policy per halaman/resource, kepemilikan data, dan dashboard sesuai peran belum lengkap. Karena itu HAP-1 belum dapat dianggap selesai.

## Dua sprint formal

Materi kuliah yang dibaca menyebut dua sprint formal pada minggu 6–10 dan 11–15. Jira memiliki Sprint 1 - Modul inti dan Sprint 2 - Lanjutan. Sprint 1 direncanakan untuk M02, M03, M04, M06, M07, M08, M10, M11, M15; Sprint 2 untuk M01, M05, M09, M12, M13, M14 plus hardening/demo. M16–M18 tidak mempunyai story di Jira.

Jira menempatkan masing-masing lima anggota pada tiga modul dan 9 story. Setiap anggota memiliki 3 atau 6 story per sprint. Pemilik/PM tetap perlu memperbarui sprint state: pada audit 22 Oktober 2026 Sprint 0 sudah melewati tanggal akhir 18 Oktober dan Sprint 1 mulai 19 Oktober, tetapi Jira masih menunjukkan keduanya `future`. Sprint 2 pada goal Jira menyebut minggu 11–14, bukan 11–15.

Rincian rumus, daftar story, dan pembagian anggota ada di [`requirements.md`](requirements.md). Temuan sumber dan acceptance criteria per ticket ada di [`source-audit.md`](source-audit.md).
