# Konteks proyek Hijauku untuk AI agent

## Identitas

- Produk: Hijauku - Adopsi Pohon.
- Repo: PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon.
- Tujuan: inventaris program/pohon, kegiatan lapangan, adopsi, perawatan dan pengamatan.
- PM: Yoga (@mygads).
- Bahasa UI/dokumen: Bahasa Indonesia.
- Zona waktu operasional: Asia/Jakarta.

## Fakta proyek yang harus dijaga

- Roster lima orang: Yoga, Bitari, Arif, Wira, dan Rofii. Dinda bukan anggota.
- Ketentuan Proyek PPL menyebut kelompok 6-7 orang, sedangkan teks LMS Sprint 00 menyebut 5-6 orang. Konfirmasi dosen untuk tim lima orang masih terbuka.
- Scope Jira aktif: 5 epic dan 45 story HJK-001 sampai HJK-045 untuk M01 sampai M15. Setiap anggota memegang tiga modul × tiga story = sembilan story.
- HAP-6 untuk M16 sampai M18 sudah dihapus permanen atas keputusan PM. Model/tabelnya masih scaffold awal; jangan menambahkan UI atau workflow sampai PM membuka scope baru.
- HAP-61 SETUP-001 dan parent HAP-1 diterima PM dan berstatus Done pada 25 Oktober 2026. HAP-61 mempertahankan Original estimate 18 jam dengan remaining 0 jam. Alur HJK-001/HAP-7 masuk ke `main` lewat PR #2 pada 22 Oktober. PR #4 menambahkan tes Dusk; PM menerima HAP-7 dan menetapkannya Done pada 25 Oktober. Original estimate tetap 4 jam dan remaining 0 jam. Tidak ada work log yang dibuat atau diubah.
- HAP-2 dan HAP-3 berstatus In Progress karena HAP-16 dan HAP-25 sedang aktif. HAP-16 milik Rofii: PR #6 perlu sinkronisasi dengan `main` terbaru serta tes Dusk M04. Original estimate HAP-16 tetap 3 jam dan remaining 3 jam. HAP-25 milik Bitari, dengan Original estimate 4 jam dan remaining 4 jam. Pelaksana mengisi work log sendiri hanya untuk waktu nyata.
- PR #1, #2, #4, dan #7 sudah berada di `main`. PR #4 menambah Dusk, perbaikan validasi, dan dokumentasi. PR #7 menyelaraskan proposal serta catatan proyek. PR #5 ditutup tanpa merge agar aplikasi dan CI tetap ada. PR #3 ditutup dengan perubahan yang diminta; PR pengganti Rofii adalah #6 pada branch personal `rofii`. Jangan menghapus branch lama sebelum pemilik memastikan semua perubahan sudah aman pada branch pengganti.
- Issue keys HAP dan ID HJK yang sudah diterbitkan tidak dinomori ulang.
- Sprint 0 ditutup, Sprint 1 aktif, dan Sprint 2 future. Sprint 1 tetap minggu 6-10, Sprint 2 tetap minggu 11-15. Rencana awal memakai minggu 6 (19-25 Okt) untuk backlog/refinement dan mulai coding pada minggu 7 (26 Okt). Riwayat GitHub mencatat HJK-001 sudah masuk `main` pada 22 Oktober; HAP-7 diterima PM setelah tes Dusk di PR #4 pada 25 Oktober. HAP-16 dan HAP-25 juga In Progress pada akhir minggu 6. Tanggal sprint tetap; cocokkan tanggal mulai story dengan pekerjaan yang benar-benar dilakukan.
- 45 story memiliki Start date Senin dan Due date Minggu sesuai docs/task-schedule.md. Tiga story satu modul ditempatkan utuh dalam satu sprint: setiap anggota mendapat 3 story di Sprint 1 dan 6 di Sprint 2. Tanggal adalah rencana, bukan actual time; jangan membuat work log dari rentang tanggal.
- Git memakai satu branch personal per anggota (`yoga`, `bitari`, `arif`, `wira`, `rofii`). Sertakan HAP key di commit dan PR; jangan membuat branch baru per fitur.
- CI workflow menjalankan Dusk smoke test beranda dan browser test pembuatan program pada panel manager. Tes memakai file SQLite terisolasi yang dimigrasi dan di-seed sebelum server browser dimulai. Run CI `40181326721` pada head PR #4 `7a5efae` lulus, termasuk login manager, penggunaan kalender Filament, penyimpanan program Draf, dan pemeriksaan hasil pada daftar. CI dokumentasi PR #7 juga lulus pada run `40190337974`; tidak ada perubahan aplikasi pada PR tersebut.
- GitHub for Atlassian sudah terhubung ke organisasi PPL-KelompokB-SI-48-ext dengan akses semua enam repositori dan backfill selesai. Repo Hijauku-Adopsi-Pohon tercantum sebagai connected dengan backfill Finished. Setelah push, cek Development pada issue untuk memastikan commit, PR, dan build benar-benar tertaut.
- Stack: PHP 8.3+, Laravel 13, Filament 5, Blade, Vite, Eloquent, MySQL 8+ untuk development, SQLite cepat dan MySQL di CI.
- Tiga panel login terpisah: manager /app, field_staff /petugas, citizen /warga.
- Role gate dan Policy dasar tersedia. Resource, query ownership, workflow, transaksi dan dashboard bisnis masih perlu dibuat per story.
- Pengguna menyatakan MySQL lokal sudah berjalan. Jangan membaca, mengganti atau menimpa .env; jangan menjalankan migrasi pada database tersebut.
- Pertahankan file user desain.md. Jangan commit token, secret, data pribadi, foto nyata, atau folder konfigurasi lokal.

## Aturan domain

- Program memiliki sites; site memiliki plots; plots menampung planting batches dan trees.
- Kapasitas plot harus dilindungi transaksi; kuota activity tidak boleh dilampaui.
- Approval adoption mengunci alokasi pohon dalam transaksi agar tidak ada persetujuan ganda.
- Petugas hanya melihat tugas dan laporan yang berhubungan dengan assignment miliknya.
- Pemohon/pengirim laporan tidak memverifikasi kirimannya sendiri.
- Informasi/bukti tetap privat kecuali workflow publikasi dan otorisasi mengizinkan.

## Urutan baca

Proposal Google Docs, Canva 27 slide, dan naskah video sudah diselaraskan dengan baseline Jira pada 25 Oktober 2026. Lihat docs/proposal-audit.md untuk tautan, koreksi skema/acceptance, serta batas verifikasi. Presentasi membahas rancangan dengan data contoh, bukan menyatakan 45 fitur telah selesai. Naskah lokal ada di docs/video-script.md.

1. AGENTS.md.
2. docs/project-context.md dan docs/source-audit.md.
3. docs/architecture.md serta docs/database.md untuk pekerjaan backend.
4. docs/requirements.md dan Jira issue untuk acceptance criteria.
5. docs/jira-workflow.md untuk isi story, estimasi, work log, serta tautan Jira-GitHub.
6. docs/agent-onboarding.md dan CONTRIBUTING.md untuk alur kerja.

## Cara menjalankan tugas AI

Kerjakan satu Jira story yang disebut oleh pengguna. Baca acceptance criteria aktual. Sebelum edit, tulis singkat rencana dan file yang akan berubah. Setelah edit, jalankan pemeriksaan relevan. Perbarui dokumentasi jika keputusan berubah. Dalam laporan akhir, pisahkan perilaku yang sudah bekerja dari scaffold dan pekerjaan lanjutan. Jangan merge PR; merge hanya oleh PM.
