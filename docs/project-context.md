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
- Aturan kelas menyebut kelompok 6-7 orang; status tim lima orang masih menunggu konfirmasi dosen.
- Scope Jira aktif: 5 epic dan 45 story HJK-001 sampai HJK-045 untuk M01 sampai M15. Setiap anggota memegang tiga modul × tiga story = sembilan story.
- HAP-6 untuk M16 sampai M18 sudah dihapus permanen atas keputusan PM. Model/tabelnya masih scaffold awal; jangan menambahkan UI atau workflow sampai PM membuka scope baru.
- HAP-61 adalah task SETUP-001 Sprint 0, status Jira In Progress dan parent HAP-1. HAP-1 masih To Do. Acceptance criteria telah diselaraskan ke roster dan backlog aktual; hanya PM yang mengubah status setelah penerimaan.
- Issue keys HAP dan ID HJK yang sudah diterbitkan tidak dinomori ulang.
- Sprint 0, 1, dan 2 ada di Jira; state masih future walaupun tanggal Sprint 0 lewat dan Sprint 1 mulai 19 Oktober 2026. Jangan mulai/menutup sprint tanpa keputusan PM.
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

1. AGENTS.md.
2. docs/project-context.md dan docs/source-audit.md.
3. docs/architecture.md serta docs/database.md untuk pekerjaan backend.
4. docs/requirements.md dan Jira issue untuk acceptance criteria.
5. docs/jira-workflow.md untuk isi story, estimasi, work log, serta tautan Jira-GitHub.
6. docs/agent-onboarding.md dan CONTRIBUTING.md untuk alur kerja.

## Cara menjalankan tugas AI

Kerjakan satu Jira story yang disebut oleh pengguna. Baca acceptance criteria aktual. Sebelum edit, tulis singkat rencana dan file yang akan berubah. Setelah edit, jalankan pemeriksaan relevan. Perbarui dokumentasi jika keputusan berubah. Dalam laporan akhir, pisahkan perilaku yang sudah bekerja dari scaffold dan pekerjaan lanjutan. Jangan merge PR; merge hanya oleh PM.
