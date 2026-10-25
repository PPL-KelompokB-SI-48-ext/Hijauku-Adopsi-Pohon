# Diagram proposal

Gambar ini dipakai pada proposal Google Docs revisi 25 Oktober 2026. PNG untuk penyisipan, SVG untuk pengeditan dan pembesaran. File DOT tersedia untuk delapan diagram berbasis Graphviz. `01-process.svg` adalah diagram BPMN ringkas; SVG-nya merupakan sumber yang dipakai. `10-gantt.svg` mengikuti kalender di `docs/task-schedule.md`.

| Berkas | Isi |
| --- | --- |
| 01-process | Ringkasan proses adopsi, kegiatan/perawatan, dan penanganan masalah |
| 02-use-cases | Aktor dan kelompok fitur aktif |
| 03-architecture | Publik, tiga panel, domain, database, Storage, dan CI |
| 04-erd-inventory | Program, lokasi, petak, jenis, kelompok, dan pohon |
| 05-erd-adoption | Permohonan, kesepakatan, alokasi, dan masalah |
| 06-erd-operations | Kegiatan, penugasan, rencana, realisasi, dan pengamatan |
| 07-classes-inventory | Model data utama serta Action pembuatan program |
| 08-classes-activities | Model kegiatan dan workflow yang masih direncanakan |
| 09-classes-care | Model perawatan/pengamatan dan workflow yang direncanakan |
| 10-gantt | Minggu 5-15, dua sprint formal dan persiapan |

Diagram merangkum relasi, bukan daftar seluruh kolom/metode. Migration, model, Policy, dan acceptance Jira tetap menjadi acuan implementasi. Perubahan skema atau jadwal perlu diikuti pada SVG/PNG, proposal, serta dokumentasi terkait.
