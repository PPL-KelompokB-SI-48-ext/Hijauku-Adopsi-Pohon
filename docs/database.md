# Skema data awal

Migrasi `2026_10_22_000003_create_hijauku_domain_tables.php` membuat 18 tabel domain dan 2 tabel teknis. Jira saat ini hanya memiliki backlog M01–M15; tabel M16–M18 (artikel, galeri, usulan aksi) masih scaffold dari rancangan lama dan belum memiliki story. Perlakukan tiga tabel tersebut sebagai tertunda sampai diputuskan apakah backlog akan ditambah atau skema starter dipangkas. Migrasi hanya kerangka relasi dan beberapa constraint; aturan proses bisnis belum seluruhnya dilaksanakan.

## Tabel domain

| Tabel | Isi dan relasi kunci |
| --- | --- |
| `programs` | Tujuan, periode, status; induk `sites`. |
| `sites` | Lokasi dalam program, alamat, koordinat; induk `plots` dan `activities`. |
| `plots` | Petak dan kapasitas pada site; kode unik per site. |
| `tree_species` | Acuan nama dan catatan perawatan. |
| `planting_batches` | Satu pelaksanaan tanam pada petak; kode unik per petak. |
| `trees` | Kode, jenis, petak, batch opsional, tanggal tanam, kondisi awal. Constraint gabungan memastikan batch bila diisi berada pada petak yang sama. |
| `activities` | Agenda lapangan, waktu, lokasi, kuota, status. |
| `activity_registrations` | Pendaftaran/kehadiran; unik per kombinasi activity dan user. |
| `field_assignments` | Hubungan aktivitas, pohon dan petugas; status serta tenggat. |
| `adoption_requests` | Pohon, pemohon, alasan, status, peninjau dan alasan keputusan. |
| `adoption_agreements` | Syarat/periode untuk satu request; satu kesepakatan maksimal per permohonan. |
| `tree_adoption_allocations` | Kunci alokasi pohon, request, kesepakatan dan status tunggu/aktif. Unik per pohon pada versi starter. |
| `tree_issues` | Laporan masalah, bukti, tingkat prioritas, keputusan dan penyelesaian. |
| `care_plans` | Rencana tindakan pohon; penugasan opsional. |
| `care_records` | Realisasi perawatan, bukti, pengirim, status dan verifikator. |
| `growth_observations` | Tinggi, diameter, kondisi, foto dan verifikasi; unik per pohon/tanggal. |
| `environment_articles` | Artikel edukasi, penulis, status dan waktu terbit. |
| `gallery_entries` | Foto kegiatan opsional, pengunggah dan status. |
| `green_action_proposals` | Usulan warga, perkiraan peserta/lokasi/waktu, status dan keputusan. |

## Tabel teknis

- `activity_logs`: actor, aksi, perubahan status, metadata, dan subject polimorfik. Gunakan untuk jejak perubahan yang memang perlu ditelusuri, bukan log semua request.
- `users`: tabel Laravel diperluas dengan `role` dan `is_active`. Password tetap disimpan melalui cast hash Laravel.
- Tabel bawaan Laravel untuk cache, job queue, dan session disiapkan oleh skeleton.

## Relasi inti

```text
programs -> sites -> plots -> trees <- tree_species
                       |       ^
                       v       |
                planting_batches

sites -> activities -> activity_registrations -> users
                    -> field_assignments -> trees / users

trees -> adoption_requests -> adoption_agreements
      -> tree_adoption_allocations (maksimal satu alokasi tertahan per pohon)
      -> tree_issues, care_plans -> care_records, growth_observations

users -> environment_articles, gallery_entries, green_action_proposals
```

## Constraint yang sudah ada

- ID utama memakai bigint; foreign key mencegah orphan dan penghapusan tidak sah.
- `programs.status`, `activities.status`, dan status dokumen bertipe string agar nilai alur dapat berkembang. Buat enum/constant aplikasi dan aturan transisi sebelum implementasi workflow.
- Kode pohon unik global; kode petak dan batch unik di lingkup induknya.
- Satu akun hanya dapat tercatat sekali pada kegiatan yang sama.
- Satu request maksimal mempunyai satu agreement; satu allocation per pohon menahan persetujuan bersamaan.
- Pengamatan pohon unik per tanggal.
- Foto menyimpan path. Pastikan service memakai private disk dan otorisasi saat menyajikan unduhan.

## Batasan yang harus dikerjakan di aplikasi

Migration tidak dapat sendiri menegakkan kapasitas dinamis, kuota pendaftaran, validasi rentang waktu, transisi status, kepemilikan, nilai tinggi/diameter positif, larangan self-review, dan konsistensi tanggal antar entitas. Letakkan aturan itu pada Request validation, Policy, dan service transaksi. Acceptance criteria per PBI harus menyebut kasus berhasil, penolakan, dan perubahan status.

## Keputusan alokasi dan adopsi ulang

Tabel awal menggunakan `tree_id` unik di `tree_adoption_allocations`. Baris ini adalah lock untuk permohonan menunggu atau adopsi aktif. Saat agreement berakhir/dihentikan, service harus menghapus baris allocation dalam transaksi sambil mempertahankan status/riwayat pada agreement dan activity log; pohon kemudian dapat dialokasikan lagi. Jika tim membutuhkan riwayat lock yang lebih lengkap, tambah tabel riwayat terpisah. Jangan menghapus constraint unik tanpa mengganti perlindungan konkurensi.

## Engine database

Dokumen arsitektur awal mengusulkan PostgreSQL 18. Pemilik menyatakan MySQL lokalnya sudah disiapkan dan berjalan; `.env` lokal memakai MySQL dan tidak boleh ditimpa. CI memakai SQLite sementara. Ini belum menjadi keputusan engine seluruh tim. Setelah diputuskan, ubah `.env.example`, workflow CI, README, dan migrasi bila perlu; jangan arahkan migrasi ke database lokal pengguna tanpa izin.
