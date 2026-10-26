# Skema database Hijauku

Skema berada di migration Laravel dan ditujukan untuk MySQL 8+. File migration tidak mengubah database saat dibuat. Jalankan hanya pada database pengembangan yang sudah diperiksa di .env.

Migrasi domain membuat 20 tabel: 16 tabel untuk modul aktif M01-M15 (M11 menggunakan tabel agreement dan allocation), tiga tabel scaffold tertunda M16-M18, dan satu tabel activity_logs. Tabel akun dan infrastruktur Laravel dibuat pada migration terpisah.

## Tabel aktif

| Tabel | Isi dan relasi utama | Modul |
| --- | --- | --- |
| programs | Tujuan, periode, status, dan pembuat; satu program memiliki lokasi | M01 |
| sites | Alamat/koordinat dalam program; memiliki petak dan kegiatan | M02 |
| plots | Petak dan kapasitas di lokasi | M03 |
| tree_species | Nama jenis dan catatan perawatan | M04 |
| planting_batches | Pelaksanaan penanaman pada petak | M05 |
| trees | Kode, jenis, petak, batch, tanggal tanam, kondisi | M06 |
| activities | Jadwal, lokasi, kuota, dan status | M07 |
| activity_registrations | Pendaftaran/kehadiran; unik per warga dan kegiatan | M08 |
| field_assignments | Kegiatan, pohon, petugas, tenggat, status | M09 |
| adoption_requests | Pohon, pemohon, alasan, keputusan dan reviewer | M10 |
| adoption_agreements | Syarat/periode satu permohonan yang disetujui | M11 |
| tree_adoption_allocations | Lock satu pohon untuk mencegah persetujuan bersamaan | M11 |
| tree_issues | Laporan masalah, bukti privat, severity, resolusi | M12 |
| care_plans | Rencana tindakan untuk pohon dan `assignment_id` opsional | M13 |
| care_records | Realisasi, pelaksana, bukti, status dan verifikator | M14 |
| growth_observations | Ukuran/kondisi pohon, bukti dan verifikasi | M15 |

## Tabel yang masih disiapkan untuk scope tertunda

| Tabel | Isi | Status |
| --- | --- | --- |
| environment_articles | Artikel, penulis dan waktu publikasi | M16, di luar scope aktif |
| gallery_entries | Foto kegiatan dan pengunggah | M17, di luar scope aktif |
| green_action_proposals | Usulan warga dan keputusan | M18, di luar scope aktif |

Tabel tersebut tersisa dari rancangan 18 modul. HAP-6 sudah dihapus permanen dari Jira dan M16 sampai M18 tidak mempunyai story aktif. Jangan membuat Resource, route, atau workflow untuk tabel ini sampai PM menetapkan scope baru beserta acceptance criteria.

## ERD relasi antartabel

ERD dibagi per alur supaya garis relasi mudah diikuti. Setiap garis menghubungkan kolom PK/UK yang dirujuk langsung ke kolom FK, bukan ke kotak tabel secara umum. Untuk FK gabungan pada pohon dan batch, kedua pasangan kolom ditampilkan. Diagram relasi menunjukkan kunci, sedangkan daftar kolom lengkap ada pada skema tabel.

Lihat diagram ERD per kelompok di diagrams/system-design/exports/erd-relations-*.png. Sumber Mermaid-nya ada di diagrams/system-design/erd-relations-*.mmd. activity_logs.subject_type dan subject_id merupakan pasangan polimorfik, bukan foreign key.

## Skema tabel tanpa data

File diagrams/system-design/erd-schema.mysql.sql berisi perintah CREATE TABLE saja, tanpa INSERT, data akun, atau data seeder. Skema mencakup users dan 20 tabel domain yang dibuat migration. Kelompok SVG/PNG di diagrams/system-design/exports/schema-rel-*.png menampilkan seluruh kolom dan tipe MySQL, dengan penanda PK/FK/UK serta garis yang menghubungkan kolom FK ke PK/UK yang dirujuk. File SQL menjadi acuan untuk panjang tipe, nullability, default, indeks, dan constraint.


## Tabel teknis

- users menyimpan akun, enum role, dan status aktif. Password memakai cast hash Laravel.
- activity_logs menyimpan actor, aksi, transisi status, metadata, dan subject polimorfik.
- Migration Laravel menyiapkan tabel session, cache, queue, dan failed job sesuai konfigurasi proyek.

## Constraint yang sudah ada

- Primary/foreign key mencegah relasi orphan; penghapusan master yang masih dipakai dibatasi.
- `programs.created_by` menyimpan akun pengelola yang membuat program. Kolom nullable menjaga kompatibilitas data lama; alur pembuatan baru wajib mengisinya dan foreign key mencegah akun pembuat dihapus selama masih menjadi pencatat.
- Pembuatan program menyimpan status awal `draft` dan event `program.created` dalam satu transaksi. Form mewajibkan nama, tujuan, tanggal mulai, dan tanggal akhir; tanggal akhir harus lebih besar dari tanggal mulai.
- Kode pohon unik global; kode petak unik di lokasi; kode batch unik di petak.
- Foreign key gabungan memastikan pohon dan batch berada pada petak yang sama.
- Pendaftaran unik pada kombinasi kegiatan dan warga.
- Satu agreement per permohonan dan satu allocation per pohon.
- Pengamatan unik untuk pohon pada tanggal yang sama.
- Indeks status/tanggal/owner disiapkan untuk query umum.

## Aturan yang harus ditegakkan oleh aplikasi

### Batas scaffold pada audit 25 Oktober 2026

- `care_plans` belum memiliki kolom prioritas. HAP-44 harus menambahkannya melalui migration yang aman, disertai validasi dan audit; kolom pada mockup bukan bukti skema sudah tersedia.
- Riwayat perubahan memakai `activity_logs` dengan `actor_id`, subject, aksi, dan metadata. Jangan menganggap semua tabel memiliki `updated_by` atau kolom alasan tersendiri.
- Alokasi yang menunggu penerimaan maupun aktif membatasi penawaran pohon. HAP-23, HAP-36, dan HAP-38 menggunakan alokasi yang masih berlaku, bukan hanya memeriksa label status pada katalog. Gunakan transaksi/row lock dan constraint MySQL, bukan partial unique index PostgreSQL.
- `care_plans.assignment_id` dan `care_records.plan_id` bersifat opsional pada skema. Policy dan validasi story tetap memeriksa pohon, aktor, dan hubungan record yang dipilih.

Diagram yang dipakai pada proposal tersedia di [diagrams/](diagrams/). Diagram adalah ringkasan; migration dan kode merupakan sumber nama kolom yang sebenarnya.

Constraint statis tidak cukup untuk memeriksa kuota, kapasitas petak, availability pohon, status yang sah, tanggal lintas record, ownership, ukuran positif, dan self-review. Gunakan:

1. validasi request untuk bentuk/nilai input;
2. Policy untuk role dan record;
3. query scope untuk membatasi daftar;
4. service + database transaction untuk operasi lintas record atau rawan race condition;
5. tes untuk kondisi diterima dan ditolak.

Setiap Resource Filament harus menyembunyikan field sensitif dan membatasi query, walaupun Policy viewAny mengizinkan role membuka daftar. Untuk upload bukti, gunakan disk privat, validasi tipe/ukuran, dan route download yang memanggil otorisasi.

## Database lokal dan CI

MySQL 8+ adalah baseline development tim. Lihat SQL setup lokal di database-setup.mysql.sql dan pastikan akun memiliki akses hanya ke database pengembangan. CI memeriksa migration/seeder pada MySQL dan SQLite; tes cepat memakai SQLite in-memory.

Jangan menimpa .env lokal yang sudah bekerja. Sebelum migrate, verifikasi DB_HOST, DB_DATABASE, DB_USERNAME, dan hak akses. Hindari migrate:fresh pada database yang mungkin berisi data yang ingin disimpan.
