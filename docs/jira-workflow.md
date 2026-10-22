# Alur kerja Jira Hijauku

Panduan ini menjaga isi backlog, estimasi, catatan waktu, kode, dan pull request tetap saling cocok. Jira menyimpan status dan acceptance criteria pekerjaan. Repo menyimpan aturan teknis dan cara menjalankan aplikasi. Jika requirement berubah, perbarui Jira dan dokumen yang terdampak pada perubahan yang sama.

## Isi setiap story

Sebelum story masuk sprint, pastikan issue memiliki bagian berikut. Gunakan bahasa Indonesia yang jelas dan sebut nama role, status, data, serta hasil yang diharapkan secara spesifik.

1. **Tujuan pengguna** - siapa yang membutuhkan kemampuan ini dan untuk apa.
2. **Batas modul** - salah satu modul aktif M01-M15, epic induk, dan dependensi yang benar-benar diperlukan.
3. **Ruang lingkup** - tindakan yang termasuk dan hal yang tidak termasuk, agar implementer tidak menambah fitur sendiri.
4. **Kriteria penerimaan** - kondisi berhasil dan ditolak yang dapat diperiksa; cantumkan role, kepemilikan record, validasi, dan transisi status bila relevan.
5. **Catatan implementasi** - file/domain yang perlu dibaca, halaman Stitch bila ada, Policy dan query scope, migration/service/transaksi yang mungkin terdampak. Ini panduan, bukan pengganti keputusan implementer.
6. **Definition of Done** - pemeriksaan yang dijalankan, dokumentasi yang disesuaikan, PR yang menaut ke issue, CI hijau, review selesai, dan status yang diperbarui setelah penerimaan PM.
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
- UI Stitch: [frame/link atau "belum dipetakan"]
- Jangan mengubah scope di luar issue tanpa persetujuan PM.

Definition of Done
- Acceptance criteria teruji.
- Migration dan seeder aman untuk data yang ada bila schema berubah.
- Tes akses berhasil dan ditolak tersedia.
- Pemeriksaan yang relevan lulus dan hasilnya dicantumkan di PR.
- PR menyebut HAP-x; penulis menyelesaikan konflik; CI hijau; review selesai.
- PM menerima perubahan sebelum issue dinyatakan selesai.

Estimasi awal
- Original estimate: [diisi saat refinement]
- Asumsi/bagian yang belum diketahui: ...
```

Jangan menyalin template dengan placeholder ke issue siap dikerjakan. Isi setiap kriteria berdasarkan perilaku story yang sebenarnya. Jika perilaku belum diketahui, klarifikasi di Jira sebelum memasukkannya ke sprint.

## Estimasi dan waktu aktual

Gunakan field Jira **Original estimate** untuk rencana waktu bila time tracking tersedia. Estimasi dibuat oleh pemilik story dan ditinjau bersama PM saat refinement, berdasarkan acceptance criteria dan pekerjaan implementasi, validasi, tes, serta dokumentasi yang diperlukan. Angka ini merupakan rencana, bukan janji durasi presisi.

Setiap pelaksana mencatat **work log** untuk waktu yang benar-benar ia gunakan pada issue, mengikuti resolusi waktu Jira. Catat setelah pekerjaan dilakukan dan gunakan angka yang jujur. Work log tidak boleh disamakan otomatis dengan Original estimate, lama sprint, ataupun rentang tanggal. Bila pekerjaan belum mulai, jangan masukkan waktu aktual. Jika Jira tidak menampilkan Log work, jangan mengakalinya dengan komentar; minta PM memeriksa konfigurasi dan izin time tracking.

Sebelum sprint, periksa jumlah Original estimate per orang terhadap waktu yang memang tersedia. Sembilan story per anggota adalah pembagian backlog (3 modul × 3 story), bukan bukti semua story punya ukuran atau jam yang sama. Jangan membuat estimasi seragam hanya agar total terlihat rapi. Jira sekarang memakai Start date pada hari Senin dan Due date pada hari Minggu untuk rentang mingguan tiap story. Jadwal tersebut adalah target koordinasi PM, bukan jam aktual atau janji durasi pengerjaan. Lihat [jadwal mingguan backlog](task-schedule.md). Pemilik story meninjau ulang tanggal dan estimate saat refinement; jika berubah, selaraskan field Jira dan snapshot dokumentasi pada PR yang sama.

Pada 22 Oktober 2026, Original estimate untuk 45 story merupakan **proposal awal PM** dan belum dianggap hasil refinement pemilik modul. Angkanya ada pada [baseline estimasi](effort-estimates.md). Story dan Task kini menampilkan field Original estimate serta Time tracking. HAP-61 memiliki estimate awal 18 jam dan remaining estimate 4 jam untuk pemeriksaan akhir. Target HAP-61 di board adalah pekan 5, 12-18 Oktober, sehingga Jira menandainya lewat tenggat saat status masih In Progress. Jika target Sprint 0 diperpanjang, PM mengubah tanggal rencana, bukan work log. Pemilik modul meninjau estimasi Jira dan memperbarui dokumen snapshot bila ada perubahan. Pada tanggal audit belum ada work log aktual untuk story atau HAP-61.

Jika estimasi berubah, tulis alasan di issue dan sesuaikan remaining estimate. Jika scope/acceptance berubah, PM memperbarui deskripsi dan rencana sprint sebelum tim mengerjakan tambahan itu. Work log yang sudah mencatat waktu aktual tidak diubah agar cocok dengan rencana baru.

## Menghubungkan Jira, branch, commit, dan PR

Gunakan satu issue key HAP yang benar pada semua bagian:

```text
Branch: feature/HAP-25-pendaftaran-kegiatan
Commit: feat: catat pendaftaran kegiatan HAP-25
PR title: [HAP-25] Pendaftaran kegiatan warga
PR body: https://myogaadi1234567.atlassian.net/browse/HAP-25
```

Key Jira lama tidak dinomori ulang. Nomor HJK-001 sampai HJK-045 adalah label kebutuhan, bukan pengganti key HAP pada branch dan commit. Sebut key dengan huruf kapital. Buat commit pada branch yang benar, lalu push ke GitHub. Commit lokal yang belum di-push belum dapat terlihat oleh Jira.

Sesudah push, buka issue Jira dan periksa **Development** untuk commit/branch/PR. Koneksi **GitHub for Atlassian** proyek ini sudah mengarah ke organisasi `PPL-KelompokB-SI-48-ext`, memberi akses semua enam repo termasuk `Hijauku-Adopsi-Pohon`, dan status backfill selesai. Keterkaitan otomatis tetap perlu diverifikasi pada issue setelah push; jika panel kosong, periksa format key, izin `View development tools`, sinkronisasi, dan akses repo sebelum mengklaim tautan tampil. Jangan membuat duplikasi issue.

Penulis branch membuka PR ke `main`, mencantumkan issue dan acceptance yang dipenuhi, lalu meminta Yoga (@mygads) sebagai reviewer. Penulis menyelesaikan konflik dan percakapan review. PM memberi keputusan review dan satu-satunya pihak yang merge. Setelah merge, anggota menarik `main` terbaru. Lihat [CONTRIBUTING.md](../CONTRIBUTING.md) untuk perintah lengkap.

## Siapa memperbarui apa

| Perubahan | Jira | Repo/GitHub |
| --- | --- | --- |
| Requirement atau acceptance criteria | Deskripsi story dan sprint bila terdampak | Dokumen requirement/arsitektur bila baseline berubah |
| Perkiraan sebelum sprint | Original estimate | Tidak perlu menggandakan angka di README |
| Waktu aktual | Pelaksana menambah work log untuk issue yang dikerjakan | Commit/PR menjadi bukti perubahan kode, bukan pengganti work log |
| Perubahan kode | Status dan tautan issue/PR | Branch, commit, PR, CI, review, merge |
| Perubahan skema/keputusan arsitektur | Issue terkait dan persetujuan PM | Migration, tes, ADR/dokumen, PR |
| Penerimaan/penutupan | PM memperbarui status setelah review/acceptance | Merge hanya oleh PM |

Jangan menyatakan issue selesai hanya karena commit sudah ada. Status akhir mengikuti acceptance criteria, hasil review, CI, dan penerimaan PM.

## Referensi Jira

- [Atlassian: log time on an issue](https://support.atlassian.com/jira-software-cloud/docs/log-time-on-an-issue/) - memisahkan original estimate dari waktu kerja yang dicatat setelah pekerjaan dilakukan.
- [Atlassian: reference work items in development work](https://support.atlassian.com/jira-software-cloud/docs/reference-issues-in-your-development-work/) - format key pada branch, commit, dan pull request serta kebutuhan push ke repository yang sudah terhubung.
