# Audit sumber dan kondisi proyek

Audit ini mencatat kondisi repo, GitHub, Jira, Stitch, proposal, dan bahan kuliah setelah pembaruan Sprint 0 pada 22 Oktober 2026. Aturan kuliah berasal dari PDF; backlog berasal dari Jira; keputusan roster berasal dari PM. Ketiganya tidak boleh dicampur.

## Ringkasan

| Area | Kondisi sekarang |
| --- | --- |
| Roster | Lima anggota: Yoga, Bitari, Arif, Wira, Rofii. Dinda tidak termasuk. |
| Aturan kelas | PDF menyebut tim 6-7 orang. Tim lima orang perlu konfirmasi dosen. |
| Hitungan PBI | Jira menata 3 modul/orang × 3 story/modul = 9 story/orang, 45 total. Ini pembagian proyek, bukan bunyi aturan kuliah. |
| Scope aktif | HAP-1 sampai HAP-5: 5 epic; HJK-001 sampai HJK-045: 45 story untuk M01 sampai M15. |
| Scope di luar backlog | HAP-6 sudah dihapus permanen. M16 sampai M18 tidak memiliki story aktif; model/tabel scaffold masih ada di repo. |
| Jira setup | HAP-61 task Sprint 0, In Progress, assigned Yoga; acceptance criteria menyebut lima orang, 45 story, role panel, CI dan alur PR. |
| Sprints | Sprint 0, 1, 2 ada dan status Jira masih future. Tanggal Sprint 0 berakhir 18 Okt; Sprint 1 dijadwalkan 19 Okt-22 Nov; Sprint 2 diselaraskan ke pekan 11-15 dan berakhir 27 Des. PM masih perlu memutuskan state Sprint 0/1 sebelum board dianggap berjalan/selesai. |
| Database | Baseline development MySQL 8+, berdasar konfigurasi lokal yang sudah berjalan. CI memeriksa MySQL dan SQLite. File .env lokal pemilik tidak diubah dan tidak dijalankan migrasi. |
| UI | Stitch berisi 25 frame. Frame 21 (Beranda Publik) dan frame 24 (Kesepakatan Saya/Warga, M11) teridentifikasi; beranda starter mengikuti headline, ilustrasi, CTA, dan tiga langkah. Katalog dan layar modul belum terhubung. |
| Starter | Laravel 13, Filament 5, tiga panel gate, Policy dasar, schema/seeder, setup lokal Windows, panduan tim, tes autentikasi/session, dan CI. Belum ada Resource/domain workflow lengkap. |
| Estimasi Jira | 45 story memiliki Original estimate proposal awal PM dengan total 211 jam; HAP-61 memiliki Original estimate 18 jam dan remaining estimate 4 jam. Belum ada work log aktual. Rincian ada di [baseline estimasi](effort-estimates.md). |

## Perhitungan backlog Jira

| Anggota | Modul | Sprint 1 | Sprint 2 | Total |
| --- | --- | ---: | ---: | ---: |
| Yoga | M01-M03 | 5 | 4 | 9 |
| Rofii | M04-M06 | 5 | 4 | 9 |
| Bitari | M07-M09 | 4 | 5 | 9 |
| Arif | M10-M12 | 4 | 5 | 9 |
| Wira | M13-M15 | 4 | 5 | 9 |
| **Total** | **M01-M15** | **22** | **23** | **45** |

Story memiliki ID HJK-001 sampai HJK-045 berurutan dan issue Jira HAP-7 sampai HAP-51. Key yang sudah dipakai tidak dinomori ulang. Sprint 0 bukan sprint formal. Materi kuliah yang diaudit mengarah pada minimal 3 PBI/orang/sprint bila setiap orang mengambil satu fitur sedang/tinggi per sprint; bukan 3 PBI/orang untuk seluruh proyek.

## Jira yang sudah diselaraskan

- Project HAP menampilkan 5 epic aktif dan 45 story aktif.
- Role Jira menampilkan Yoga sebagai Administrator dan empat anggota lain sebagai Member; Dinda tidak tercantum pada role proyek.
- HAP-6 dihapus permanen melalui Jira setelah dipulihkan dari arsip dan diperiksa tidak memiliki child issue atau issue link. Pengecekan setelah penghapusan mengembalikan 404.
- HAP-61 acceptance criteria sudah diubah dari 6 orang/54 story ke 5 orang/45 story, memasukkan pengecualian M16-M18, baseline RBAC, dokumen, CI, dan aturan PM merge.
- HAP-1 masih To Do. Acceptance sudah diperjelas untuk tiga panel role, Policy/ownership, CI MySQL/SQLite, dokumentasi, dan scope lima orang/45 story; issue tetap To Do sampai tim menerima hasil.
- Semua story aktif mempunyai acceptance criteria dan dialokasikan per modul/owner; Parent story HAP-7 sampai HAP-51 tertaut ke HAP-2 sampai HAP-5 sesuai modul. HAP-61 tertaut ke HAP-1.
- Deskripsi 45 story sekarang memuat acceptance criteria asli, scope, owner, sprint, parent epic, arahan implementasi/tes untuk anggota dan AI agent, Definition of Done, Original estimate, dan aturan work log aktual. Field waktu adalah proposal awal dan masih perlu refinement pemilik.
- Penempatan Sprint 1/2 diperbarui pada 17 story untuk merapikan urutan prasyarat dan beban awal per orang. Baseline hasil audit adalah 22 story/105h di Sprint 1 dan 23 story/106h di Sprint 2. Rincian per issue ada di [baseline estimasi](effort-estimates.md); semua angka tetap proposal PM sampai refinement anggota.
- Panel Jira Development pada HAP-61 menampilkan commit `ca55ec6`, draft PR #1, dan build GitHub Actions. Integrasi commit/PR terdeteksi setelah push.
- Jangan mulai atau menyelesaikan sprint hanya untuk menyamakan status; tindakan itu dapat mengubah status issue dan memindahkan pekerjaan. PM menentukan tanggal/state setelah melihat kondisi tim.

## GitHub

Repo: https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon

Roster yang terverifikasi: PM Yoga (@mygads), Bitari, Arif, Wira, dan Rofii. Dinda bukan kolaborator. Empat anggota memiliki write; Yoga satu-satunya admin. Branch main mewajibkan CI install-and-build, satu approval, penyelesaian percakapan, dan membatasi push/merge ke Yoga. CODEOWNERS mencatat PM; anggota tetap meminta @mygads secara eksplisit. Draft PR #1 belum digabung.

## Database dan keamanan

PM menyatakan MySQL lokal sudah berjalan. Audit ini tidak membaca kredensial .env, tidak mengubah .env, tidak tersambung ke database, dan tidak menjalankan migration. Setup anggota memakai database hijauku_dev yang terpisah. M16 sampai M18 masih ada sebagai model/tabel scaffold karena berasal dari rancangan awal, tetapi tidak memiliki route/Resource aktif.

Jangan simpan API token pada repo, dokumen, atau CI. Gunakan secret manager GitHub untuk secret CI; kredensial lokal tetap di .env.

## Pemeriksaan lokal

Validasi yang dijalankan pada template di branch kerja: Composer strict validation, tes Unit dan Feature pada SQLite in-memory, Laravel Pint untuk file PHP yang disentuh, dan build aset Vite. Dusk homepage sebelumnya lulus pada CI; tes browser tidak dijalankan ulang untuk perubahan dokumen dan teks judul. Script setup diperiksa sintaks PowerShell tanpa menjalankan migrasi pada database lokal pemilik.

## Stitch

Proyek Stitch Hijauku Admin UI Batch 01 berisi 25 frame. Frame 21 adalah Beranda Publik; frame 24 menunjukkan Kesepakatan Saya untuk Warga pada M11. Beranda kode memiliki headline utama, ilustrasi pohon, CTA, dan alur tiga langkah yang terlihat pada frame publik. Bagian katalog dan angka pada desain belum memakai sumber data; jangan tampilkan sebagai fakta produksi. Arahan kanvas mengecualikan M16-M18 dan meminta keadaan overlay tertutup serta kamus data/seed konsisten. Pemeriksaan pixel-per-pixel untuk seluruh frame belum dilakukan. Rincian implementasi ada di [panduan desain UI](ui-design.md).

## Sumber yang diperiksa

- Rancangan_Arsitektur_Hijauku_PPL.docx - rancangan awal 18 modul dan PostgreSQL 18.
- Ketentuan Proyek PPL.pdf dan Modul 1 2026 - aturan kelompok/sprint/PBI.
- Proposal Tugas Besar PPL.pdf - daftar nama sampul dan uraian lama yang masih menyebut enam orang/54 PBI.
- Materi requirement, Scrum, Agile dan sprint - proses backlog dan sprint.
- Jira HAP - epic, story, assignee, label modul, sprint, dan HAP-1/HAP-61.
- Repo GitHub dan daftar kolaborator - kode, workflow, branch, dan akses repo.
- Stitch Hijauku Admin UI Batch 01 - frame UI dan arahan implementasi.
- desain.md - file pengguna yang dipertahankan.
- File .env lokal tidak dibaca atau diubah pada audit ini.

## Hal yang tetap perlu keputusan manusia

1. Dosen mengonfirmasi apakah tim lima orang memenuhi aturan kelompok 6-7 orang.
2. PM menyelaraskan state Sprint 0 dan Sprint 1 yang belum cocok dengan tanggal board. Sprint 2 kini berakhir 27 Desember 2026 sesuai pekan 11-15 pada materi.
3. Pemilik modul memetakan semua frame Stitch yang relevan ke story aktif dan melakukan review visual sebelum mengklaim UI modul final.
4. PM dan pemilik modul melengkapi Resource, query scope, workflow transaksi, dan tes untuk story aktif.
