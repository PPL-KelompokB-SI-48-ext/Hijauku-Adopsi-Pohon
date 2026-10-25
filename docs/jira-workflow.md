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

Sebelum sprint, periksa jumlah Original estimate per orang terhadap waktu yang memang tersedia. Sembilan story per anggota dibagi menjadi tiga story di Sprint 1 dan enam di Sprint 2; tiga story pembentuk satu modul ditempatkan utuh pada sprint yang sama. Angka story bukan ukuran beban yang sama. Minggu 6 digunakan untuk backlog/refinement tanpa coding; pengerjaan Sprint 1 dimulai minggu 7. Start date Jira adalah hari Senin dan Due date hari Minggu, sebagai target koordinasi, bukan actual time atau janji durasi. Lihat [jadwal mingguan backlog](task-schedule.md). Pemilik meninjau ulang tanggal dan estimate saat refinement.

Pada 25 Oktober 2026, Original estimate 45 story masih **proposal awal PM** dan perlu ditinjau pemilik. Angkanya ada pada [baseline estimasi](effort-estimates.md). HAP-61 berstatus In Progress dengan Original estimate 18 jam, remaining estimate 4 jam, dan tanpa work log; PM perlu memeriksa remaining estimate tanpa mengarang actual time. PR #1 dan #2 masih merged ke `main`; revert keduanya ada dalam PR draft #5. Setelah keputusan revert, PM menilai kembali HAP-61/HAP-7 berdasarkan kondisi kode dan acceptance.

Jika estimasi berubah, tulis alasan di issue dan sesuaikan remaining estimate. Jika scope/acceptance berubah, PM memperbarui deskripsi dan rencana sprint sebelum tim mengerjakan tambahan itu. Work log yang sudah mencatat waktu aktual tidak diubah agar cocok dengan rencana baru.

## Menghubungkan Jira, branch, commit, dan PR

Gunakan branch personal sesuai nama anggota. Sertakan issue key HAP pada setiap commit dan PR:

```text
Branch: bitari
Commit: feat: catat pendaftaran kegiatan HAP-25
PR title: [HAP-25] Pendaftaran kegiatan warga
PR body: https://myogaadi1234567.atlassian.net/browse/HAP-25
```

Satu anggota hanya memakai satu branch personal (`yoga`, `bitari`, `arif`, `wira`, atau `rofii`), bukan satu branch per fitur. Branch tidak memuat issue key karena dipakai lintas story; setiap commit dan PR tetap harus menyebut key HAP huruf kapital. Key lama tidak dinomori ulang. Nomor HJK adalah label kebutuhan, bukan pengganti HAP. Commit lokal yang belum di-push belum terlihat Jira.

Sesudah push, buka issue Jira dan periksa **Development** untuk commit/branch/PR. Koneksi **GitHub for Atlassian** proyek ini sudah mengarah ke organisasi `PPL-KelompokB-SI-48-ext`, memberi akses semua enam repo termasuk `Hijauku-Adopsi-Pohon`, dan status backfill selesai. Keterkaitan otomatis tetap perlu diverifikasi pada issue setelah push; jika panel kosong, periksa format key, izin `View development tools`, sinkronisasi, dan akses repo sebelum mengklaim tautan tampil. Jangan membuat duplikasi issue.

Pemilik branch membuka PR ke `main` saat story/kelompok story yang dicantumkan sudah memenuhi acceptance criteria. Body PR mencantumkan semua issue dan acceptance yang dibawa; anggota meminta Yoga (@mygads) sebagai reviewer. Penulis menyelesaikan konflik dan percakapan review. Hanya PM yang merge. PR anggota perlu satu approval PM; PR Yoga tidak perlu self-approval, tetapi CI tetap wajib hijau. Repo memakai merge commit; setelah merge, pemilik branch menarik `main` terbaru ke branch personal dan tidak menghapusnya. Lihat [CONTRIBUTING.md](../CONTRIBUTING.md) untuk perintah lengkap.

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
