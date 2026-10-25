# Paket rancangan sistem Hijauku

Bab Rancangan Sistem pada proposal menyatukan use case, arsitektur, ERD relasi, skema tabel, dan class diagram. Berkas sumber serta hasil gambar di folder ini dipakai untuk memeriksa dan memperbarui bab yang sama.

## Isi dan cara membaca

- Use case: empat kelompok use case mencakup M01-M15, lalu satu diagram kecil menjelaskan relasi UML include dan extend yang dipakai bersama. Diagram menunjukkan kebutuhan rancangan, bukan bukti bahwa semua fitur sudah selesai.
- Arsitektur: gambar vektor memperlihatkan satu aplikasi Laravel modular, tiga panel, penyimpanan, dan CI. Logo teknologi membantu tim mengenali komponen yang digunakan.
- ERD relasi: diagram dibagi per alur. Setiap garis dimulai tepat di kolom PK/UK yang dirujuk dan berakhir di kolom FK, misalnya trees.id ke adoption_requests.tree_id. Diagram hanya menampilkan kunci yang dibutuhkan agar garisnya tidak menutupi kolom lain.
- Skema tabel: gambar terpisah menampilkan nama tabel, semua kolom, tipe MySQL, dan penanda PK/FK/UK. Garis biru menempel pada baris FK dan kolom PK/UK yang dirujuk. Gambar tidak berisi baris data, akun demo, atau seed.
- Class diagram: satu gambar untuk tiap modul aktif M01-M15. Diagram menunjukkan model dan relasi yang sesuai dengan source code saat ini. Action yang belum ada tidak digambarkan seolah sudah dibuat.

## Kenapa ada SQL dan gambar terpisah

DDL schema-only diimpor ke diagrams.net untuk memeriksa tabel serta constraint tanpa menyalin data database lokal. Impor SQL membuat bentuk tabel secara otomatis, tetapi susunan awalnya masih perlu dirapikan. Gambar skema di proposal dibuat dari DDL yang sama lewat generator agar semua kolom tetap tampil dan garis FK tepat pada baris PK/UK yang dirujuk.

## Berkas utama

| Berkas | Kegunaan |
| --- | --- |
| erd-schema.mysql.sql | DDL MySQL schema-only dari migration, tanpa INSERT atau data akun. Termasuk tabel users dan 20 tabel domain. |
| tools/generate-diagrams.cjs | Membaca DDL dan menghasilkan sumber Mermaid ERD field-level serta gambar skema tabel dalam format SVG. |
| erd-relations-*.mmd | Sumber diagram hubungan FK ke kolom PK/UK yang tepat. |
| exports/schema-rel-*.svg dan PNG | Gambar skema tabel dengan semua kolom, tipe persis dari DDL, serta garis FK ke PK/UK. SVG menjadi sumber vektor, PNG dipakai dalam proposal. |
| use-cases-*.mmd | Sumber empat diagram use case berdasarkan kelompok modul. |
| use-case-relations.mmd | Rincian contoh relasi include dan extend. |
| architecture.svg | Gambar arsitektur vektor dengan logo teknologi. |
| classes/M01-*.mmd sampai classes/M15-*.mmd | Class diagram per modul aktif. |
| exports/ | Gambar PNG dan SVG yang dipakai pada proposal. |

## Cakupan database

DDL mencakup 20 tabel domain dan users. Tabel internal Laravel seperti sessions, cache, queue, dan failed jobs tidak dimasukkan. Tiga tabel environment_articles, gallery_entries, dan green_action_proposals tetap digambar sebagai scaffold M16-M18, dengan warna berbeda. Ketiganya bukan scope fitur aktif. activity_logs.subject_type dan subject_id bersifat polimorfik, jadi bukan foreign key dan tidak diberi garis FK.

Dalam diagram relasi, satu garis berarti PK/UK parent mengacu ke FK child. Relasi gabungan trees ke planting_batches ditampilkan pada kedua pasangan kolom, batch_id dan plot_id. Angka di garis menunjukkan jumlah record child yang diizinkan oleh kunci unik pada schema.

Skema tabel memuat seluruh nama kolom dan tipe lengkap, misalnya VARCHAR(255) dan BIGINT UNSIGNED. File SQL tetap menjadi acuan untuk nullability, default, panjang tipe, indeks, dan constraint. Gambar tidak menampilkan data atau isi seeder.

## Cara memperbarui

1. Perbarui migration Laravel lebih dulu.
2. Cocokkan perubahan migration dengan erd-schema.mysql.sql. Jangan menambahkan INSERT atau mengambil data dari database lokal.
3. Dari root repo, jalankan node docs/diagrams/system-design/tools/generate-diagrams.cjs .
4. Sumber Mermaid ERD dan SVG skema tabel akan dibuat ulang di folder ini. Render berkas Mermaid ke PNG/SVG dengan Mermaid CLI atau Mermaid Live Editor. Untuk Google Docs, export SVG skema tabel ke PNG memakai editor diagram atau konverter SVG, lalu ganti gambar yang bersangkutan.
5. Perbarui class diagram modul yang terpengaruh. Jangan menandai workflow sebagai sudah berjalan hanya karena model atau migration sudah ada.
6. Periksa lagi arah/cardinality foreign key pada SQL, gambar proposal, dan dokumentasi database.md sebelum commit.

Untuk melihat struktur dari SQL di web, buka diagrams.net, pilih Arrange > Insert > Advanced > SQL, lalu tempel isi DDL schema-only. Impor awal menempatkan tabel secara otomatis; rapikan susunannya dan cek setiap garis FK agar ujungnya tepat di kolom PK/UK sebelum mengekspor. Gambar proposal saat ini dirender dari DDL yang sama oleh generator di atas. Untuk sumber Mermaid, gunakan https://mermaid.live atau Arrange > Insert > Advanced > Mermaid.

## Status implementasi

Rancangan data mengacu ke migration dan model, bukan ke isi database lokal. HJK-001 membuat program lewat action CreateDraftProgram; modul lain tetap perlu mengikuti status PBI dan acceptance Jira masing-masing. Diagram rancangan tidak menggantikan tes, Policy, query ownership, validasi, atau penerimaan PM.
