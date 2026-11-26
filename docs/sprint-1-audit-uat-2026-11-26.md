# Audit Sprint 1 dan HAP-14

**Tanggal pemeriksaan:** 26 November 2026, WIB
**Cakupan:** hasil Sprint 1 di Jira dan `origin/main`, keseragaman UI panel, bukti CI, serta HAP-14 pada branch personal Yoga.
**Cara membaca:** status Jira menunjukkan penerimaan story. Status GitHub menunjukkan kode yang sudah masuk `main`. Keduanya tidak selalu berarti hal yang sama.

## Ringkasan

- Sprint 1 ditutup dengan 37 dari 37 item berstatus Done: 30 story, 5 task persiapan PR anggota, dan 2 task integrasi/penutupan.
- Perubahan Sprint 1 seluruh anggota sudah masuk `main` melalui PR #20, #24, #30, #28, dan #29. CI pada PR tersebut lulus. CI `main` pada commit `8bc3b1b` juga lulus.
- HAP-14/HJK-008 sudah memenuhi acceptance yang diperiksa, tes lokal dan CI lulus, serta UAT kapasitas dilakukan pada manager dan petugas. Implementasinya ada di branch `yoga`, belum di `main`, sesuai jadwal integrasi Sprint 2.
- Tidak ditemukan blocker fungsional pada acceptance Sprint 1 yang diuji. Namun, audit ini bukan klaim bahwa setiap tombol di semua layar sudah diklik manual. Beberapa modul belum punya tes Dusk khusus dan harus masuk checklist UAT lintas modul Sprint 2.
- UI memakai shell dan tema bersama. Klaim 1:1 hanya berlaku untuk elemen pada frame Stitch yang tersedia. Form dan detail tanpa frame mengikuti komponen Filament bersama.

## Sumber dan snapshot

| Area | Snapshot yang diperiksa |
| --- | --- |
| Jira | Sprint 1 sudah ditutup; 37 item di sprint semuanya Done. Sprint 2 aktif. |
| GitHub `main` | `8bc3b1b925d7af87a922f9ada8a1fd9b5bb7c160`; CI run [45127557638](https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon/actions/runs/45127557638) sukses. |
| Branch Yoga | `13ad2a885f454067cc6108da63ceae526d4bb24e`; sudah mencakup `origin/main` dan remote Yoga sama dengan branch lokal. CI run [45808233081](https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon/actions/runs/45808233081) sukses. |
| Stitch | Acuan frame dan batas kesesuaian antarmuka ada di [panduan UI](ui-design.md). |
| Pengujian lokal | Worktree audit dan file SQLite khusus. `.env` pemilik dan MySQL lokal tidak dibaca atau diubah. |

## Pemeriksaan Jira Sprint 1

Sprint 1 memiliki enam story per anggota, dari dua modul utuh. Tidak ada story satu modul yang dipecah ke sprint berbeda.

| Anggota | Modul dan story | PR Sprint 1 di `main` | Hasil |
| --- | --- | --- | --- |
| Yoga | M01 HAP-7/8/9 dan M02 HAP-10/11/12 | #20 untuk HAP-10/11/12. M01 sudah masuk melalui PR #2/#4 dan #17/#18 sebelum penutupan Sprint 1. | Semua 6 story Done dan kodenya ada di `main`. HAP-62 Done. |
| Rofii | M04 HAP-16/17/18 dan M05 HAP-19/20/21 | #24 | Semua 6 story Done dan merged. HAP-63 Done. |
| Bitari | M07 HAP-25/26/27 dan M08 HAP-28/29/30 | #30 | Semua 6 story Done dan merged. HAP-64 Done. |
| Arif | M10 HAP-34/35/36 dan M11 HAP-37/38/39 | #28 | Semua 6 story Done dan merged. HAP-65 Done. |
| Wira | M13 HAP-43/44/45 dan M14 HAP-46/47/48 | #29 | Semua 6 story Done dan merged. HAP-66 Done. |

HAP-71 dan HAP-72 juga Done setelah batch PR Sprint 1 masuk dan pemeriksaan integrasi/CI selesai. Jadi hitungan 37 adalah 30 story + 5 task persiapan PR + 2 task integrasi. Task koordinasi tidak menambah jumlah 45 story aktif.

## Pemeriksaan GitHub dan CI

Diagnostik GitHub memeriksa head PR beserta status pemeriksaan wajib. Ringkasan commit yang diperiksa:

| PR | Anggota | Head PR yang diverifikasi | Status pemeriksaan |
| --- | --- | --- | --- |
| #20 | Yoga | `99176cd` | Lulus |
| #24 | Rofii | `263a151` | Lulus |
| #30 | Bitari | `4844b91` | Lulus |
| #28 | Arif | `a912467` | Lulus |
| #29 | Wira | `417b24e` | Lulus |

`origin/main` setelah integrasi Sprint 1 tidak ketinggalan dari branch yang dipakai audit. CI main run [45127557638](https://github.com/PPL-KelompokB-SI-48-ext/Hijauku-Adopsi-Pohon/actions/runs/45127557638) lulus pada SHA yang tercatat di atas. PR #31 juga merged dan CI lulus, tetapi isinya pembaruan README, bukan PR fitur tambahan. PR #32 Arif adalah pekerjaan Sprint 2 dan berada di luar hasil Sprint 1.

CI menjalankan Composer, migrasi dan seeder SQLite/MySQL, pemeriksaan route panel, build Vite, tes Unit, Feature, dan Dusk. Dusk lengkap pada `main` lulus di CI. Untuk HAP-14, CI branch Yoga mencatat 11 tes Unit/11 assertion, 308 tes Feature/1.320 assertion, dan 17 tes Dusk/116 assertion.

## Hasil pengujian lokal

Di worktree `origin/main`, hasil tes adalah:

- Unit: 11 tes, 11 assertion, lulus.
- Feature: 295 tes, 1.269 assertion, lulus.
- Dusk main pada CI: lulus. Pada pengulangan lokal, 12 dari 14 tes lulus; dua tes di bawah gagal karena keterbatasan waktu server lokal dan pemilihan tanggal lintas bulan.
- Build aset pada pemeriksaan main dan branch Yoga: `npm run build` lulus.
- Pemeriksaan format file HAP-14: Pint lulus.

PHP lokal awalnya tidak memuat ekstensi `intl`, sehingga daftar Filament menghasilkan HTTP 500 saat merender pagination. Setelah ekstensi diaktifkan hanya untuk proses tes melalui INI sementara, seluruh tes Feature dan Dusk terarah HAP-14 lulus. Worktree tes memakai SQLite tersendiri.

Dua kegagalan pada pengulangan lengkap Dusk `main` perlu dibaca terpisah dari hasil CI: halaman kesepakatan warga terkena batas waktu eksekusi PHP lokal 30 detik, sedangkan tes pembuatan kesepakatan mencari tanggal 2 Desember ketika kalender yang terbuka masih November. Tes kedua perlu mengubah bulan kalender atau memakai tanggal dalam bulan yang sama agar tidak rapuh saat pergantian bulan. CI pada `main` tetap hijau; hasil lokal tidak disamarkan sebagai lulus penuh.

HAP-14 juga diuji terarah pada branch Yoga: 10 tes Feature dengan 49 assertion dan 2 tes Dusk dengan 6 assertion lulus.

## UAT HAP-14

UAT browser dijalankan pada aplikasi branch `yoga` dengan database SQLite khusus berisi data uji, bukan MySQL pemilik.

| Langkah | Hasil |
| --- | --- |
| Buka `/app/plots` sebagai pengelola untuk petak dengan kapasitas 10 dan 9 pohon | Daftar menampilkan 9/10, 90%, dan label **Hampir penuh**. |
| Buka petak berkapasitas 1 yang sudah terisi | Daftar menampilkan 1/1, 100%, dan status **Penuh**. |
| Buka `/petugas/trees/create` sebagai petugas | Petak penuh tidak muncul pada pilihan. Petak yang masih memiliki kapasitas tersedia. |
| Tambah pohon terakhir sehingga petak berubah dari 9/10 menjadi 10/10 | Simpan berhasil. Daftar pengelola berubah ke 100% dan **Penuh**; petak tidak lagi muncul di pilihan petugas. |
| Lihat layar pada lebar 390 px | Tidak ada overflow pada lebar dokumen; tabel tetap berada di area gulirnya. |

Tes Feature memeriksa ambang tepat 90%, kondisi sebelum ambang, penambahan slot terakhir, penolakan saat kapasitas penuh, peran yang boleh membuat pohon, dan larangan memakai petak tertutup. Aturan kapasitas diperiksa kembali di server dalam transaksi, bukan hanya dengan menyembunyikan opsi di UI.

## Audit UI, akses, dan alur

Panel `/app`, `/petugas`, dan `/warga` memakai konfigurasi shell Filament yang sama untuk tema, font Inter, sidebar, breadcrumb/topbar, identitas akun, dan aksi keluar. Pemeriksaan manual sebelumnya mencakup rute menu per peran, login/logout bersama, akses role yang sesuai, serta penolakan akses lintas role. Tes `PanelShellTest`, `PanelAuthenticationTest`, dan `RolePolicyTest` memberi pemeriksaan otomatis tambahan.

Untuk modul Sprint 1, tes Feature mencakup alur dan batas role/ownership pada program/lokasi, jenis pohon/kelompok penanaman, kegiatan/pendaftaran, adopsi/kesepakatan, serta rencana dan realisasi perawatan. Tes Dusk di `main` mencakup beranda, shell panel, alur M01, lokasi M02, jenis pohon M04, dan alur adopsi M10/M11.

UI yang sudah diperiksa memakai pola bersama, tetapi tingkat pembanding Stitch berbeda per layar:

- Daftar M01, M02, dan M03 punya frame Stitch; verifikasi visual dibatasi pada isi yang tampak pada frame.
- Frame M03 hanya menunjukkan daftar petak. Form tambah pohon dan detail tidak punya frame sendiri.
- Frame atau acceptance yang belum memberi perilaku tidak menjadi alasan menambah tombol/fitur baru.
- Tabel pada viewport kecil boleh bergulir di wadah tabel. Seluruh halaman tidak boleh melebar horizontal.
- Tidak ada endpoint REST baru dalam acceptance Sprint 1. Alur fitur menggunakan Filament/Livewire.

## Temuan dan tindak lanjut

Tidak ditemukan cacat penerimaan Sprint 1 pada hasil merge dan CI, dan HAP-14 lulus pada branch Yoga. Ada satu perbaikan keandalan tes Dusk lintas bulan yang perlu dibawa ke backlog/PR berikutnya. Hal berikut tetap perlu dicatat agar rencana tes tidak berlebihan:

1. Dusk main belum memiliki tes browser khusus untuk M05, M07/M08, M13, dan M14. Tes Feature tersedia, tetapi uji klik browser untuk alur tersebut perlu dimasukkan ke HAP-69 saat UAT lintas modul Sprint 2.
2. Tes tanggal kesepakatan Dusk memilih hari yang berada di bulan berikutnya tanpa menavigasi kalender. Perbaiki helper tes supaya memilih bulan/tahun yang benar, lalu jalankan ulang tes Dusk pada PR berikutnya. Jangan mengubah acceptance bisnis hanya untuk melewati tes.
3. Audit manual ini tidak mencoba setiap kemungkinan tombol pada seluruh data dan variasi status. Setiap acceptance Jira tetap menjadi checklist UAT pemilik modul; laporkan bug dengan langkah reproduksi, peran, data uji, hasil yang diharapkan, dan hasil aktual.
4. Kesetaraan UI 1:1 tidak dapat dinilai pada layar tanpa frame Stitch. Gunakan pola Filament reusable dan catat keterbatasan, jangan membuat klaim di luar frame yang tersedia.
5. HAP-14 sudah ada di branch `yoga` dan CI lulus, tetapi belum ada di `main`. Jangan menutup integrasi HAP-67/HAP-68 sebelum PR Yoga masuk, konflik selesai, CI setelah merge lulus, dan tes lintas modul selesai.
6. HAP-14 memiliki original estimate 6 jam dan remaining 0, tetapi belum ada work log. Angka aktual tetap tidak dicatat; jangan membuat work log retrospektif.

## Jadwal Sprint 2 yang relevan

Pada 26 November, Sprint 2 berlangsung pada minggu 11. HAP-13/HAP-14 dan HAP-40/HAP-41 sudah Done. HAP-22, HAP-31, HAP-49, dan HAP-50 In Progress; story ini masih dalam target due 29 November. Story ketiga per anggota jatuh tempo 6 Desember. Task integrasi HAP-67/HAP-68 dijadwalkan minggu 13, UAT/deploy/rilis HAP-69 minggu 14, dan laporan/presentasi HAP-73 minggu 15. Status due dan backlog harus dibaca lagi dari Jira sebelum memperbarui rencana.

Audit ini tidak mengubah status Jira, estimasi, work log, branch, atau data lokal. HAP-14 sudah committed dan pushed pada branch `yoga`; tidak dibuat commit ulang untuk fitur yang sama.
