# Persiapan Sprint 0 dan sprint formal

## Sprint 0

Sprint 0 menyiapkan kerja tim sebelum implementasi fitur: repo, konteks kebutuhan, arsitektur yang disetujui, skema awal, template Laravel/Filament, cara menjalankan aplikasi, CI, pembagian tugas dan struktur Jira. HAP-1 dan HAP-61 tercantum sebagai pekerjaan awal oleh pemilik proyek, tetapi isi HAP-61 belum dapat diperiksa karena Jira meminta login.

Issue SETUP-001 yang diberikan pengguna memuat target repo GitHub dan akses anggota, 6 epic + 54 story + sprint Jira, dokumen arsitektur rev1, dan starter pack. Repo GitHub yang dimaksud ternyata sudah ada dan saat audit memiliki lima kolaborator. Paket lokal yang disiapkan di branch `codex/sprint-0-starter-pack` menyediakan basis kode dan dokumentasi, tetapi target Jira serta persetujuan arsitektur belum bisa dinyatakan selesai.

### Checklist Sprint 0

- [x] Audit sumber kuliah, rancangan arsitektur, proposal, dan dokumen `desain.md`.
- [x] Siapkan skeleton Laravel 13 + Filament 5 dan konfigurasi peran dasar.
- [x] Buat migrasi awal 18 modul dan data demo yang diberi label contoh.
- [x] Tulis README, catatan requirement, arsitektur, dan konteks agent.
- [x] Tambahkan workflow CI untuk instalasi, migrasi SQLite, dan build.
- [ ] Cocokkan HAP-1 dan HAP-61, semua epic/story, assignee, dan sprint dari Jira.
- [ ] Konfirmasi jumlah anggota resmi dan siapa anggota keenam.
- [ ] Tinjau tampilan Stitch setelah login tersedia.
- [ ] Sahkan engine database bersama (PostgreSQL atau MySQL).
- [ ] Bagikan dan setujui arsitektur rev1 oleh tim.
- [ ] Beri akses write kepada semua anggota resmi pada GitHub.

## Dua sprint formal

Ketentuan kelas yang disediakan membagi pekerjaan menjadi dua sprint formal (minggu 6–10 dan 11–15). Setiap anggota harus punya sedikitnya satu fitur sedang/tinggi pada tiap sprint dan story dikerjakan dari desain sampai verifikasi. Rancangan awal membagi 18 modul ke anggota dan dua sprint, tetapi penugasan orang keenam tidak sesuai roster sampul proposal. Gunakan Jira sebagai rencana berjalan setelah roster disahkan; tabel modul berada di `docs/requirements.md`.

Saran irisan teknis untuk awal proyek:

1. Mulai dari data inti (program, lokasi, petak, jenis, batch, pohon) dan role/policy.
2. Lanjut ke kegiatan, pendaftaran, penugasan dan alur adopsi. Operasi yang berebut kuota/alokasi perlu transaksi.
3. Tambahkan perawatan, pengamatan, laporan masalah, verifikasi, dan ringkasan dashboard.
4. Kembangkan publikasi artikel, galeri, dan usulan aksi sebagai fitur tersendiri; jangan jadikan ketiganya prasyarat alur inti.

Urutan ini adalah usulan ketergantungan teknis, bukan perubahan prioritas backlog Jira.
