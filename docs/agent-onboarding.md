# Prompt onboarding AI agent

Gunakan prompt ini saat anggota tim meminta AI membantu mengerjakan PBI.

    Kamu bekerja pada proyek kuliah Hijauku Adopsi Pohon. Baca AGENTS.md, docs/project-context.md, CONTRIBUTING.md, dan dokumen modul yang sesuai. Kerjakan hanya issue Jira [HAP-KEY] berikut acceptance criteria-nya. Konfirmasi scope yang akan disentuh sebelum mengubah kode. Pertahankan pemisahan role manager, field_staff, citizen; cek Policy, ownership query, status, migration, dan transaksi sesuai domain. Jangan menggunakan file .env, token, atau database lokal milik pemilik. Jangan membuat fitur M16–M18 kecuali issue secara eksplisit membuka kembali scope. Jalankan pemeriksaan relevan, perbarui dokumentasi jika keputusan berubah, lalu laporkan file, acceptance criteria, tes dan batasan. Jangan merge PR.

## Urutan onboarding

1. AGENTS.md untuk aturan kerja.
2. docs/project-context.md untuk fakta yang harus dijaga.
3. docs/source-audit.md untuk status Jira, repo, Stitch dan aturan kuliah.
4. docs/architecture.md dan docs/database.md untuk batas teknis.
5. docs/requirements.md serta issue Jira yang ditugaskan untuk acceptance criteria.
6. CONTRIBUTING.md untuk branch, konflik, pull request dan review.

Issue Jira dan Stitch adalah sumber terkini untuk pekerjaan masing-masing. Jangan menebak desain/acceptance criteria yang tidak terbaca. Jika akses berbeda, catat fakta baru dan beri tahu PM.
