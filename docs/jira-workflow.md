# Alur kerja Jira Hijauku

Panduan ini menjaga isi backlog, estimasi, catatan waktu, kode, dan pull request tetap saling cocok. Jira menyimpan status dan acceptance criteria pekerjaan. Repo menyimpan aturan teknis dan cara menjalankan aplikasi. Jika requirement berubah, perbarui Jira dan dokumen yang terdampak pada perubahan yang sama.

## Isi setiap story

Sebelum story masuk sprint, pastikan issue memiliki bagian berikut. Gunakan bahasa Indonesia yang jelas dan sebut nama role, status, data, serta hasil yang diharapkan secara spesifik.

1. **Tujuan pengguna** - siapa yang membutuhkan kemampuan ini dan untuk apa.
2. **Batas modul** - salah satu modul aktif M01-M15, epic induk, dan dependensi yang benar-benar diperlukan.
3. **Ruang lingkup** - tindakan yang termasuk dan hal yang tidak termasuk, agar implementer tidak menambah fitur sendiri.
4. **Kriteria penerimaan** - kondisi berhasil dan ditolak yang dapat diperiksa; cantumkan role, kepemilikan record, validasi, dan transisi status bila relevan.
5. **Catatan implementasi** - file/domain yang perlu dibaca, halaman Stitch bila ada, Policy dan query scope, migration/service/transaksi yang mungkin terdampak. Ini panduan, bukan pengganti keputusan implementer.
6. **Definition of Done** - acceptance criteria dan tes relevan terpenuhi di branch personal yang sudah di-push; CI terbaru lulus; review dan penerimaan PM selesai; status Jira diperbarui. Work log hanya mencatat waktu aktual. Task integrasi sprint baru Done setelah PR terkait di-merge dan CI akhir lulus.
7. **Estimasi** - Original estimate yang disepakati tim sebelum mulai; jangan menaruh estimasi seolah-olah itu waktu aktual.

Contoh kerangka deskripsi:

```text
Tujuan
Sebagai [role], saya ingin [kemampuan], agar [manfaat].

Modul dan dependensi
- Modul: Mxx - [nama]
- Epic: HAP-x
- Dependensi: [issue/keputusan atau "tidak ada"]

Ruang lingkup
- Termasuk: ...
- Tidak termasuk: ...

Kriteria penerimaan
1. Given ..., when ..., then ...
2. Pengguna dengan role ... dapat/tidak dapat ...
3. Record milik pengguna lain ...
4. Input atau status tidak valid ...

Catatan implementasi
- Baca docs/architecture.md, docs/database.md, dan ...
- Panel/route: ...
- Terapkan Policy dan query ownership; gunakan transaksi bila mengubah beberapa record.
- UI Stitch: [nomor/judul frame dan tautan, atau "belum dipetakan"]. Untuk frame yang dipetakan, samakan susunan, warna, tipografi, jarak, dan komponen yang terlihat dengan frame. Gunakan tema serta komponen Filament reusable. Halaman tanpa frame memakai pola Filament bersama dan tidak boleh diklaim 1:1 tanpa pembanding.
- Database: baca `docs/database.md`; ubah schema lewat migration aman sesuai acceptance. Jangan mengubah `.env` atau database lokal pemilik.
- API: gunakan Filament/Livewire sebagai jalur standar. Buat API terpisah hanya jika acceptance criteria memintanya.
- Tes: tambahkan tes Feature untuk perilaku dan akses yang berubah; gunakan Laravel Dusk untuk alur browser penting. CI harus berjalan pada head PR terbaru.
- Jangan mengubah scope di luar issue tanpa persetujuan PM.

Definition of Done
- Acceptance criteria teruji.
- Migration dan seeder aman untuk data yang ada bila schema berubah.
- Tes akses berhasil dan ditolak tersedia.
- Pemeriksaan relevan untuk story lulus di branch personal yang sudah di-push.
- Commit memakai key HAP dan muncul pada panel Development Jira.
- Pelaksana memperbarui status dan remaining estimate berdasarkan kondisi sebenarnya.
- PR berisi pekerjaan satu sprint per anggota. CI, review PM, dan merge ke `main` diselesaikan pada akhir sprint.
- Jika review integrasi menemukan acceptance yang gagal, buka kembali issue terkait dan perbaiki.

Estimasi awal
- Original estimate: [diisi saat refinement]
- Asumsi/bagian yang belum diketahui: ...
```

Jangan menyalin template dengan placeholder ke issue siap dikerjakan. Isi setiap kriteria berdasarkan perilaku story yang sebenarnya. Jika perilaku belum diketahui, klarifikasi di Jira sebelum memasukkannya ke sprint.

## Estimasi dan waktu aktual

Gunakan field Jira **Original estimate** untuk rencana waktu bila time tracking tersedia. Estimasi dibuat oleh pemilik story dan ditinjau bersama PM saat refinement, berdasarkan acceptance criteria dan pekerjaan implementasi, validasi, tes, serta dokumentasi yang diperlukan. Angka ini merupakan rencana, bukan janji durasi presisi.

Setiap pelaksana mencatat **work log** untuk waktu yang benar-benar ia gunakan pada issue, mengikuti resolusi waktu Jira. Catat setelah pekerjaan dilakukan dan gunakan angka yang jujur. Work log tidak boleh disamakan otomatis dengan Original estimate, lama sprint, ataupun rentang tanggal. Bila pekerjaan belum mulai, jangan masukkan waktu aktual. Jika Jira tidak menampilkan Log work, jangan mengakalinya dengan komentar; minta PM memeriksa konfigurasi dan izin time tracking.

Sebelum sprint, periksa jumlah Original estimate per orang terhadap waktu yang memang tersedia. Sembilan story per anggota dibagi menjadi enam di Sprint 1 dan tiga di Sprint 2. Setiap modul tetap utuh, masing-masing tiga story. Lima belas story dari satu modul per anggota dimajukan dari Sprint 2: satu story per orang ditargetkan 15 November, dua sisanya 22 November. Sprint 2 menjadi 15 story total, dengan dua story per orang pada minggu 11 dan satu pada minggu 12. Angka story bukan ukuran beban yang sama, jadi pemilik tetap perlu meninjau estimate dan kapasitas. Rencana awal memakai minggu 6 untuk backlog/refinement dan menjadwalkan coding mulai minggu 7. Alur HJK-001 masuk ke `main` lewat PR #2 pada 22 Oktober; tes Dusk dan penerimaan PM dilakukan lewat PR #4 pada 25 Oktober. HAP-16 dan HAP-25 juga tercatat In Progress pada akhir minggu 6, sebelum tanggal mulai rencana minggu 7. Pemilik perlu mencocokkan tanggal mulai dengan pekerjaan sebenarnya. Start date Jira adalah hari Senin dan Due date hari Minggu sebagai target koordinasi, bukan actual time atau janji durasi. Sprint 1 memakai HAP-62 sampai HAP-66 untuk satu task persiapan PR per anggota, lalu HAP-71/HAP-72 untuk integrasi PM dalam dua batch. Task HAP-70 dihapus karena pemeriksaan kesiapan sudah menjadi bagian dari HAP-71/HAP-72. Di Sprint 2, HAP-67/HAP-68 untuk integrasi minggu 13, HAP-69 untuk UAT/pemeriksaan deploy dan dokumentasi rilis minggu 14, dan HAP-73 untuk temuan, laporan, serta presentasi minggu 15. Jira memakai hubungan **blocks** agar urutan tersebut terlihat: task PR anggota terkait harus selesai sebelum batch integrasi; Sprint 1 ditutup sebelum integrasi Sprint 2; batch 1 selesai sebelum batch 2; batch 2 sebelum UAT; UAT sebelum penutupan dokumentasi. Lihat [jadwal mingguan backlog](task-schedule.md). Pemilik meninjau ulang tanggal dan estimate saat refinement.

Pemetaan modul yang dimajukan adalah M02 Yoga, M05 Rofii, M08 Bitari, M11 Arif, dan M14 Wira. Beberapa modul memakai data dari modul lain yang masih direncanakan untuk Sprint 2. Gunakan model/tabel awal, kontrak field dan relasi yang disepakati, serta fixture untuk menguji story secara terisolasi. Jangan membuat schema duplikat atau mengklaim alur lintas modul sudah terintegrasi sebelum modul dependensinya masuk ke `main`. Integrasi penuh dan pengujian lintas modul dijadwalkan setelah semua PR Sprint 2 digabung pada minggu 13, lalu diverifikasi kembali pada UAT minggu 14.

Pada 10 November 2026, Original estimate 45 story masih **proposal awal PM** dan perlu ditinjau pemilik. Angkanya ada pada [baseline estimasi](effort-estimates.md). HAP-61, HAP-1, HAP-7/8/9, HAP-25, dan HAP-43/44/45 tercatat Done; HAP-16/17/18, HAP-26/27, dan HAP-34/35 masih In Progress. HAP-25 dan HAP-43 masih menunjukkan sisa estimasi meskipun Done, sehingga pemilik perlu merekonsiliasikannya tanpa mengarang work log. Pembaruan dokumentasi tidak membuat work log aktual.

Jika estimasi berubah, tulis alasan di issue dan sesuaikan remaining estimate. Jika scope/acceptance berubah, PM memperbarui deskripsi dan rencana sprint sebelum tim mengerjakan tambahan itu. Work log yang sudah mencatat waktu aktual tidak diubah agar cocok dengan rencana baru.

## Menghubungkan Jira, branch, commit, dan PR

Gunakan branch personal yang sudah dimiliki anggota. Nama yang sekarang terlihat di GitHub adalah Yoga `yoga`, Bitari `bitaridamar`, Arif `arif`, Wira `ayuwira`, dan Rofii `Rofii`. Variasi ejaan atau huruf besar sudah diterima PM, jadi jangan mengganti nama branch aktif. Sertakan issue key HAP pada setiap commit dan PR:

```text
Branch: bitaridamar
Commit: feat: catat rencana kegiatan HAP-25
PR title: [HAP-25] Catat rencana kegiatan
PR body: https://myogaadi1234567.atlassian.net/browse/HAP-25
```

Satu anggota hanya memakai satu branch personal, bukan satu branch per fitur. Branch tidak memuat issue key karena dipakai lintas story; setiap commit dan PR tetap harus menyebut key HAP huruf kapital. Key lama tidak dinomori ulang. Nomor HJK adalah label kebutuhan, bukan pengganti HAP. Commit lokal yang belum di-push belum terlihat Jira. Branch lama `feature/HAP-...` tidak dipakai untuk story baru; pemilik memindahkan commit yang belum tergabung ke branch personal sebelum membuka PR pengganti.

Sesudah push, buka issue Jira dan periksa **Development** untuk commit/branch/PR. Koneksi **GitHub for Atlassian** proyek ini sudah mengarah ke organisasi `PPL-KelompokB-SI-48-ext`, memberi akses semua enam repo termasuk `Hijauku-Adopsi-Pohon`, dan status backfill selesai. Keterkaitan otomatis tetap perlu diverifikasi pada issue setelah push; jika panel kosong, periksa format key, izin `View development tools`, sinkronisasi, dan akses repo sebelum mengklaim tautan tampil. Jangan membuat duplikasi issue.

Story dikerjakan di branch personal. Tandai Done setelah acceptance dan tes relevan terpenuhi pada branch yang sudah di-push, CI terbaru lulus, serta review dan penerimaan PM selesai. Periksa tautan commit pada panel Development Jira. Jika salah satu syarat belum terpenuhi, statusnya tetap In Progress. PR sprint dapat digabung pada jendela integrasi, tetapi task penutupan sprint tetap terbuka sampai PR yang diperlukan merged dan CI akhir lulus.

Pada akhir sprint, tiap anggota membuka satu PR dari branch personal ke `main` untuk story sprint yang sudah selesai. PM menentukan urutan integrasi. Pemilik branch menyinkronkan `main`, menyelesaikan konflik, menjalankan pemeriksaan, dan mencantumkan semua issue serta acceptance dalam body PR. Anggota meminta Yoga (@mygads) sebagai reviewer; hanya PM yang merge. PR anggota perlu satu approval PM; PR Yoga tidak perlu self-approval, tetapi CI wajib hijau. Jika CI atau review integrasi menemukan acceptance gagal, story terkait dikembalikan ke In Progress. Repo memakai merge commit; setelah merge, pemilik branch menarik `main` terbaru dan mempertahankan branch personal. Lihat [CONTRIBUTING.md](../CONTRIBUTING.md) untuk perintah lengkap.

## Siapa memperbarui apa

| Perubahan | Jira | Repo/GitHub |
| --- | --- | --- |
| Requirement atau acceptance criteria | Deskripsi story dan sprint bila terdampak | Dokumen requirement/arsitektur bila baseline berubah |
| Perkiraan sebelum sprint | Original estimate | Tidak perlu menggandakan angka di README |
| Waktu aktual | Pelaksana menambah work log untuk issue yang dikerjakan | Commit/PR menjadi bukti perubahan kode, bukan pengganti work log |
| Perubahan kode | Status story setelah acceptance, tes, CI, review, dan PM acceptance; tautan commit | Branch dan commit; satu PR per anggota saat penutupan sprint; CI, review, merge |
| Perubahan skema/keputusan arsitektur | Issue terkait dan persetujuan PM | Migration, tes, ADR/dokumen, PR |
| Penerimaan/penutupan | Story Done setelah acceptance, tes, CI, review, dan penerimaan PM; task integrasi setelah PR merged dan CI akhir lulus | PM me-review dan me-merge PR sprint; bila integrasi menemukan masalah, buka kembali story |

Commit yang ada saja tidak cukup untuk menandai Done. Pastikan acceptance dan tes relevan terpenuhi di branch personal yang di-push, CI terbaru lulus, dan review serta penerimaan PM tercatat. Task integrasi sprint menjadi Done setelah seluruh PR dalam scope-nya merged dan CI akhir lulus.

## Pemeriksaan Jira dan GitHub 11 November 2026

Jira menampilkan 45 story: 7 Done, 10 In Progress, dan 28 To Do. Status Done: HAP-7/8/9, HAP-25, HAP-43/44/45. Status In Progress: HAP-10/11/12, HAP-16/17/18, HAP-26/27, HAP-34/35. Epic HAP-1 Done; HAP-2 sampai HAP-5 In Progress.

HAP-10/11/12 berstatus In Progress selama implementasi M02 di branch `yoga`. Ketiganya tetap belum Done sampai acceptance, tes, CI terbaru, review integrasi, dan penerimaan PM lengkap.

Status HAP-16/17, HAP-26/27, dan HAP-34/35 dikoreksi ke In Progress saat audit karena belum ada bukti terbaru bahwa scope, acceptance, tes, CI, review, dan penerimaan PM sudah diverifikasi. Status ini bukan semata-mata karena PR belum merged. Pemilik perlu memeriksa ulang semua syarat pada branch yang di-push, menyinkronkan branch untuk integrasi, lalu memperbarui status hanya setelah bukti lengkap. Komentar audit Jira juga meminta replanning due date yang lewat.

PR #12, #14, dan #15 sudah ditutup tanpa merge. Pada pemeriksaan GitHub 11 November, branch personal terkait masih menyimpan commit unik dan tertinggal dari `main`: Rofii 5 ahead/16 behind, Arif 6 ahead/16 behind, dan Bitari 2 ahead/22 behind. Tidak ada PR terbuka. Pemilik harus mempertahankan commit unik, menyinkronkan `origin/main`, menyelesaikan konflik, meninjau temuan PR lama yang masih relevan, lalu membuka PR pengganti dengan tes dan CI baru. Jangan menghapus branch atau menganggap hasil CI lama masih berlaku.

HAP-25 dan HAP-43 berstatus Done setelah PR merged, tetapi Remaining estimate masing-masing masih 4 jam dan 5 jam. Work log keduanya kosong. Pemilik perlu mengonfirmasi sisa estimasi dan mencatat work log hanya untuk waktu yang benar-benar dikerjakan. Estimasi dan remaining estimate bukan actual time.

## Referensi Jira

- [Atlassian: log time on an issue](https://support.atlassian.com/jira-software-cloud/docs/log-time-on-an-issue/) - memisahkan original estimate dari waktu kerja yang dicatat setelah pekerjaan dilakukan.
- [Atlassian: reference work items in development work](https://support.atlassian.com/jira-software-cloud/docs/reference-issues-in-your-development-work/) - format key pada branch, commit, dan pull request serta kebutuhan push ke repository yang sudah terhubung.
