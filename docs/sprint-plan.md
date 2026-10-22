# Rencana Sprint 0

## Jira

- Epic fondasi: HAP-1 - Fondasi & Infrastruktur.
- Task Sprint 0: HAP-61 - SETUP-001.
- Assignee HAP-61: Yoga.
- Status Jira saat ini: HAP-1 To Do; HAP-61 In Progress. PM memperbarui status setelah review dan acceptance.
- Rentang Sprint 0 pada Jira: 12-18 Oktober 2026. Start date dan Due date HAP-1/HAP-61 mengikuti pekan tersebut; karena HAP-61 masih In Progress pada 22 Oktober, Jira menampilkannya lewat tenggat.
- Kriteria HAP-61 sudah diperbarui: 5 anggota, 5 epic, 45 story, scope M01-M15, HAP-6 dihapus dan M16-M18 di luar scope, dokumen arsitektur, starter Laravel/Filament, role gate/Policy, dan alur PR.

## Acceptance HAP-61

1. Repo memiliki lima anggota aktif: Yoga, Bitari, Arif, Wira, dan Rofii. Dinda dikecualikan.
2. Backlog aktif Jira memiliki 5 epic dan 45 story HJK-001 sampai HJK-045; pembagian per anggota adalah 3 modul × 3 story. HAP-6 sudah dihapus permanen. M16 sampai M18 tidak termasuk scope aktif.
3. Sprint 0, Sprint 1, dan Sprint 2 tersedia; Sprint 2 telah diselaraskan ke pekan 11-15 dan tanggal akhir 27 Desember. PM masih menyelaraskan state Sprint 0/1 sebelum menjalankan atau menutup sprint.
4. Dokumen arsitektur Rev 1, kebutuhan, database, keputusan, audit sumber, dan kontribusi tersedia serta dibagikan lewat repo.
5. Starter Laravel 13 + Filament 5 bisa di-clone, dikonfigurasi memakai database development khusus, dimigrasi dan di-seed tanpa secret.
6. Panel manager, field_staff, dan citizen dipisahkan per path serta diuji; Policy dasar role/ownership tersedia.
7. CI memvalidasi dependency, migrasi/seeder MySQL/SQLite, route, tes, dan build.
8. Anggota membuka PR ke main dan meminta Yoga sebagai reviewer; CI dan satu approval wajib; hanya PM yang merge; penulis PR menyelesaikan konflik.

## Checklist starter

- [x] Audit sumber, roster, Jira, GitHub dan Stitch.
- [x] Selaraskan HAP-61 ke roster lima orang dan 45 story.
- [x] Hapus HAP-6 secara permanen dari Jira atas permintaan PM.
- [x] Verifikasi 5 epic aktif, 45 story dan HJK-001 sampai HJK-045 berurutan.
- [x] Pertahankan konfigurasi database lokal pengguna; CI memakai DB terisolasi.
- [x] Siapkan Laravel 13 + Filament 5 dan tiga panel role.
- [x] Siapkan Policy dasar dan tes izin/ownership.
- [x] Siapkan migration, seeder demo, .env.example aman, README dan panduan kontribusi.
- [x] Siapkan script setup Windows dengan konfirmasi database, panduan lokal, dan alur login/logout panel.
- [x] Siapkan CI SQLite + MySQL, tes PHP, Dusk beranda, dan build aset.
- [x] Lindungi main: satu approval, CI wajib, percakapan selesai, anggota write, dan push/merge hanya PM.
- [x] Isi Start date dan Due date per story untuk 45 item mengikuti pekan pada timeline; rinciannya di [jadwal mingguan backlog](task-schedule.md).
- [ ] Minta review dan approval PM/tim pada pull request Sprint 0.
- [ ] Dosen mengonfirmasi komposisi tim lima orang.
- [ ] PM menyelaraskan state Sprint 0/1 yang terlambat.
- [ ] Pemilik modul meninjau arsitektur, acceptance criteria dan query ownership sebelum membangun Resource.
- [ ] Lengkapi pemeriksaan visual Stitch; beranda belum dicocokkan pixel-per-pixel dan layar lain belum diimplementasikan.

## Catatan status

CI GitHub memeriksa template setelah perubahan dipush. HAP-61 masih In Progress dan PR Sprint 0 belum digabung; HAP-1 tetap To Do. PM memperbarui status setelah review dan acceptance. Sprint 0 tidak otomatis ditutup karena masih ada keputusan dosen dan status board yang perlu dikelola PM.
