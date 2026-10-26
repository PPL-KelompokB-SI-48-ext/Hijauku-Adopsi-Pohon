# Diagram Hijauku

Gambar proses lama dan revisi sebelumnya tetap berada di folder ini sebagai riwayat. Paket yang dipakai untuk revisi proposal saat ini ada di system-design. Bab proposal menempatkan use case, arsitektur, ERD, skema tabel, dan 15 class diagram dalam satu bagian Rancangan Sistem.

Baca system-design/README.md untuk cara melihat SQL, memperbarui gambar, batas scope, dan arti garis relasi.

| Kelompok | Sumber |
| --- | --- |
| Use case M01-M15 | system-design/use-cases-*.mmd dan system-design/use-case-relations.mmd |
| Arsitektur dengan logo teknologi | system-design/architecture.svg |
| ERD relasi antar kolom | system-design/erd-relations-*.mmd |
| Skema tabel tanpa data | system-design/erd-schema.mysql.sql dan system-design/exports/schema-rel-*.svg |
| Class diagram per modul | system-design/classes/M01-*.mmd sampai M15 |
| Kalender sprint | 10-gantt pada folder ini dan docs/task-schedule.md |

Diagram relasi menghubungkan kolom FK langsung ke kolom PK/UK yang dirujuk. Daftar kolom lengkap dan tipe MySQL ada di skema tabel. DDL hanya memuat struktur tabel, tanpa baris data. Migration tetap menjadi sumber implementasi.
