# Audit sumber dan kondisi proyek

Audit ini mencatat kondisi repo, GitHub, Jira, Stitch, proposal, dan bahan kuliah setelah pembaruan Sprint 0 pada 22 Oktober 2026. Aturan kuliah berasal dari PDF; backlog berasal dari Jira; keputusan roster berasal dari PM. Ketiganya tidak boleh dicampur.

## Ringkasan

| Area | Kondisi sekarang |
| --- | --- |
| Roster | Lima anggota: Yoga, Bitari, Arif, Wira, Rofii. Dinda tidak termasuk. |
| Aturan kelas | PDF menyebut tim 6–7 orang. Tim lima orang perlu konfirmasi dosen. |
| Hitungan PBI | Jira menata 3 modul/orang × 3 story/modul = 9 story/orang, 45 total. Ini pembagian proyek, bukan bunyi aturan kuliah. |
| Scope aktif | HAP-1 sampai HAP-5: 5 epic; HJK-001–045: 45 story untuk M01–M15. |
| Scope tertunda | HAP-6 M16–M18 sudah diarsipkan tanpa dihapus. Issue masih dapat dipulihkan; tidak ada story aktif untuk scope ini. |
| Jira setup | HAP-61 tetap task Sprint 0, To Do, assigned Yoga; acceptance criteria kini menyebut lima orang, 45 story, role panel, CI dan alur PR. |
| Sprints | Sprint 0, 1, 2 ada dan masih state future. Sprint 0 berakhir 18 Okt; Sprint 1 berjalan 19 Okt–22 Nov; Sprint 2 diselaraskan ke pekan 11–15 dan berakhir 27 Des. PM masih perlu memutuskan state Sprint 0/1 sebelum board dianggap berjalan/selesai. |
| Database | Baseline development MySQL 8+, berdasar konfigurasi lokal yang sudah berjalan. CI memeriksa MySQL dan SQLite. File .env lokal pemilik tidak diubah dan tidak dijalankan migrasi. |
| UI | Stitch memuat frame publik dan panel Pengelola, Warga, Petugas. Beranda mengikuti copy/alur utama; desain semua layar belum diimplementasikan. |
| Starter | Laravel 13, Filament 5, tiga panel gate, Policy dasar, schema/seeder, dokumentasi dan CI. Belum ada Resource/domain workflow lengkap. |

## Perhitungan backlog Jira

| Anggota | Modul | Sprint 1 | Sprint 2 | Total |
| --- | --- | ---: | ---: | ---: |
| Yoga | M01–M03 | 6 | 3 | 9 |
| Rofii | M04–M06 | 6 | 3 | 9 |
| Bitari | M07–M09 | 6 | 3 | 9 |
| Arif | M10–M12 | 6 | 3 | 9 |
| Wira | M13–M15 | 3 | 6 | 9 |
| **Total** | **M01–M15** | **27** | **18** | **45** |

Story memiliki ID HJK-001–045 berurutan dan issue Jira HAP-7–HAP-51. Key yang sudah dipakai tidak dinomori ulang. Sprint 0 bukan sprint formal. Materi kuliah yang diaudit mengarah pada minimal 3 PBI/orang/sprint bila setiap orang mengambil satu fitur sedang/tinggi per sprint; bukan 3 PBI/orang untuk seluruh proyek.

## Jira yang sudah diselaraskan

- Project HAP menampilkan 5 epic aktif dan 45 story aktif.
- Role Jira menampilkan Yoga sebagai Administrator dan empat anggota lain sebagai Member; Dinda tidak tercantum pada role proyek.
- HAP-6 diberi nama [DEFERRED], deskripsi tanpa owner Dinda, label deferred/scope-tertunda, lalu diarsipkan. Issue tidak dihapus dan bisa dipulihkan.
- HAP-61 acceptance criteria sudah diubah dari 6 orang/54 story ke 5 orang/45 story, memasukkan scope deferred, baseline RBAC, dokumen, CI, dan aturan PM merge.
- HAP-1 masih To Do. Acceptance sudah diperjelas untuk tiga panel role, Policy/ownership, CI MySQL/SQLite, dokumentasi, dan scope lima orang/45 story; issue tetap To Do sampai tim menerima hasil.
- Semua story aktif mempunyai acceptance criteria dan dialokasikan per modul/owner; review kualitas bisnis tetap tanggung jawab pemilik modul.
- Jangan mulai atau menyelesaikan sprint hanya untuk menyamakan status; tindakan itu dapat mengubah status issue dan memindahkan pekerjaan. PM menentukan tanggal/state setelah melihat kondisi tim.

## GitHub

Repo: https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon

Roster yang terverifikasi: PM Yoga (@mygads), Bitari, Arif, Wira, dan Rofii. Dinda bukan kolaborator. Empat anggota memiliki write; Yoga satu-satunya admin. Branch main mewajibkan CI install-and-build, satu approval, penyelesaian percakapan, dan membatasi push/merge ke Yoga. CODEOWNERS mencatat PM; anggota tetap meminta @mygads secara eksplisit. Draft PR #1 belum digabung.

## Database dan keamanan

PM menyatakan MySQL lokal sudah berjalan. Audit ini tidak membaca kredensial .env, tidak mengubah .env, tidak tersambung ke database, dan tidak menjalankan migration. Setup anggota memakai database hijauku_dev yang terpisah. M16–M18 masih ada sebagai model/tabel scaffold karena berasal dari rancangan awal, tetapi tidak memiliki route/Resource aktif.

Jangan simpan API token pada repo, dokumen, atau CI. Gunakan secret manager GitHub untuk secret CI; kredensial lokal tetap di .env.

## Stitch

Proyek Stitch Hijauku Admin UI Batch 01 berisi frame publik serta panel role. Arahan terakhir tidak memasukkan artikel, galeri, atau usulan aksi sebagai domain aktif. Halaman beranda mengikuti isi dan alur utama frame; logo/aset merek dan pemeriksaan visual seluruh frame masih belum selesai.

## Sumber yang diperiksa

- Rancangan_Arsitektur_Hijauku_PPL.docx — rancangan awal 18 modul dan PostgreSQL 18.
- Ketentuan Proyek PPL.pdf dan Modul 1 2026 — aturan kelompok/sprint/PBI.
- Proposal Tugas Besar PPL.pdf — daftar nama sampul dan uraian lama yang masih menyebut enam orang/54 PBI.
- Materi requirement, Scrum, Agile dan sprint — proses backlog dan sprint.
- Jira HAP — epic, story, assignee, label modul, sprint, dan HAP-1/HAP-61.
- Repo GitHub dan daftar kolaborator — kode, workflow, branch, dan akses repo.
- Stitch Hijauku Admin UI Batch 01 — frame UI dan arahan implementasi.
- desain.md — file pengguna yang dipertahankan.
- File .env lokal tidak dibaca atau diubah pada audit ini.

## Hal yang tetap perlu keputusan manusia

1. Dosen mengonfirmasi apakah tim lima orang memenuhi aturan kelompok 6–7 orang.
2. PM menyelaraskan state Sprint 0 dan Sprint 1 yang belum cocok dengan tanggal board. Sprint 2 kini berakhir 27 Desember 2026 sesuai pekan 11–15 pada materi.
3. Tim melakukan review visual terhadap semua frame Stitch sebelum mengklaim UI final.
4. PM dan pemilik modul melengkapi Resource, query scope, workflow transaksi, dan tes untuk story aktif.
