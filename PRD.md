# PRD — Sistem Aspirasi & Pengaduan Desa Sirnaputra (SAPdesa)

## 1. Ringkasan (TL;DR)

Aplikasi web untuk menampung **Aspirasi, Pengaduan, dan Kritik/Saran** warga Desa Sirnaputra secara *per dusun*. Warga tidak login per individu; aspirasi dikirim **atas nama dusun** (4 dusun) agar saluran tidak ramai dan mudah ditindaklanjuti oleh kepala dusun masing-masing.

## 2. Tujuan & Masalah

| Masalah | Solusi yang diharapkan |
|---|---|
| Aspirasi warga tersebar di WA/Rapat/kesan lisan, sulit dicatat | Semua tertampung di satu sistem terpusat |
| Tidak ada tracking tindak lanjut | Setiap laporan punya status & timeline yang bisa dipantau |
| Banjir laporan ICU (1 laporan = 1 warga) | Laporan dikelompokkan **per dusun** |
| Data hilang / tidak ada laporan resmi | Arsip digital, bisa diexport (Excel/PDF) |

### Sukses / Metrik
- Adopsi: 4/4 dusun aktif melaporkan tiap bulan.
- Semua laporan berstatus selain "Baru" dalam ≤ 7 hari.
- 0 data laporan hilang (backup DB rutin).

## 3. User & Role (3 Role)

| Role | Keterangan | Hak akses utama |
|---|---|---|
| **Superadmin** (developer) | Untuk pengembang/operator teknis | Kelola role & user, lihat semua data, konfigurasi sistem, maintenance |
| **Admin** (pihak desa) | Perangkat Desa Sirnaputra | Lihat SEMUA laporan semua dusun, terbitkan umpan balik resmi, kelola master dusun/kategori, export data |
| **Kepala Dusun** (4 orang) | Pimpinan tiap dusun | Kelola **hanya dusunnya sendiri**: update status, catat tindak lanjut, terima/validasi laporan masuk |

### Aturan akses (singkat)
- Kepala Dusun HANYA melihat datanya sendiri (data scope di filter user_id → dusun_id di tabel). Tidak boleh melihat dusun lain.
- Admin & Superadmin melihat semua dusun.
- Kepala Dusun tidak bisa menghapus laporan (hanya admin).

## 4. Entity & Data Model (MySQL)

### Tabel inti

**`users`** (base Breeze, + kolom)
- `name`, `email`, `password`
- `role` enum: `superadmin`, `admin`, `kepala_dusun`
- `dusun_id` FK nullable (wajib diisi utk kepala_dusun)

**`dusun`**
- `id`, `nama` (unik) → seed 4 data: Dusun 1, Dusun 2, Dusun 3, Dusun 4
- (opsional) `jumlah_penduduk`, `nama_kepala`

**`kategori`**
- `id`, `nama`, `slug` → seed: **Aspirasi**, **Pengaduan**, **Kritik/Saran**

**`laporan`** (tabel utama)
- `id`, `judul`, `isi` (text), `kategori_id` FK
- `dusun_id` FK (asal laporan)
- `user_id` FK (pelapor = kepala dusun yang input)
- `tanggal` (default now)
- `lampiran` nullable (path file foto/dokumen)
- `status` enum: `baru`, `diproses`, `selesai`, `ditolak`
- `respon_admin` nullable text (umpan balik resmi dari pihak desa)
- `created_at`, `updated_at`
- `softDeletes`

**`tindak_lanjut`**
- `id`, `laporan_id` FK, `keterangan`, `user_id`, `created_at`
- Timeline jejak aktivitas per laporan (dibuat saat status berubah)

### Hubungan (relationship)
```
dusun  1 ── *  users
dusun  1 ── *  laporan
kategori 1 ── *  laporan
laporan 1 ── *  tindak_lanjut
users   1 ── *  laporan   (pelapor)
users   1 ── *  tindak_lanjut
```

## 5. Fitur (User Stories)

### A. Autentikasi (Breeze)
- [x] Register / Login / Logout / Forgot Password (default Breeze)
- [x] Penentuan role saat register awal → default `kepala_dusun`; Superadmin/Admin dibuat via user management
- [ ] Middleware role: superadmin/admin/kepala_dusun
- [ ] Halaman "Profil" ubah nama/email/password

### B. Dashboard (Role-aware)
- Superadmin: semua statistik, user list, dsb.
- Admin: statistik seluruh dusun + export.
- Kepala Dusun: statistik dusun miliknya saja.
- Widget: total laporan, per status (baru/diproses/selesai/ditolak), per kategori, laporan terbaru.

### C. Manajemen Laporan (CRUD)
- **Kepala Dusun**: create laporan atas nama dusunnya (isi judul, kategori, isi, lampiran). Edit & hapus hanya jika status masih `baru`.
- **Admin**: lihat semua, beri `respon_admin`, ubah status final.
- **Superadmin**: akses penuh.
- Alur status: `baru` → `diproses` → `selesai` | `ditolak`. Setiap perpindahan status otomatis membuat catatan di `tindak_lanjut`.
- Filter: by dusun, by kategori, by status, pencarian judul.

### D. Manajemen Master
- Kelola dusun (CRUD) — Admin/Superadmin.
- Kelola kategori (CRUD) — Admin/Superadmin.
- Kelola user (CRUD) — Superadmin (agar bisa mendaftarkan 4 kepala dusun).

### E. Export & Arsip
- Export data laporan ke **Excel (CSV)** & **PDF** — Admin/Superadmin.

### F. Statistik & Reporting
- Grafik jumlah laporan per dusun / per kategori / per bulan (Chart.js).

## 6. Alur Utama (Flow)

```
1. Kepala Dusun login → Dashboard (hanya dusunnya)
2. Klik "Buat Laporan" → pilih kategori + tulis isi → submit (status: baru)
3. Admin desa login → lihat daftar semua laporan baru
4. Admin/Admin desa beri respon / ubah status (diproses → selesai/ditolak)
   →  otomatis muncul di tindak lanjut
5. Kepala Dusun pantau status laporannya sampai selesai
6. (Opsional) Export laporan bulanan ke Excel/PDF
```

## 7. Stack Teknologi

| Layer | Pilihan | Catatan |
|---|---|---|
| Backend | **Laravel** (versi stabil terbaru, gunakan yang disupport utk Breeze+Inertia) | Request: Laravel 13 — gunakan versi aktif resmi |
| Scaffold | **Laravel Breeze** (`--vue`) | Auth + Inertia Vue 3 out of the box |
| Frontend | **Vue 3 + Inertia.js** | SPA-style, tetap SEO-friendly |
| UI | **Tailwind CSS** | Modern, utility-first |
| Chart | Chart.js (via vue-chartjs) | Statistik dashboard |
| Database | **MySQL** | DB native, backup rutin |
| Export | Laravel Excel (maatwebsite) atau CSV native | CSV native dulu, Excel library kalau perlu |

## 8. Desain UI (bukan AI slop, tidak jadul)

- **Pola**: sidebar kiri (navigasi) + topbar (user info) + konten kartu.
- **Warna**: netral & earthy (putih + abu lembut + aksen hijau/teal milik desa), BUKAN gradien ungu/pink.
- **Font**: system font stack / sans-serif lokal (Inter via bun/font). Tanpa font "script" dekoratif.
- **Komponen**: kartu laporan dengan badge status berwarna (Baru=amber, Diproses=blue, Selesai=green, Ditolak=red), tabel rapi, modal konfirmasi.
- **Responsif**: mobile-first (dipakai di HP warga/desa).
- **Konsisten**: satu spacing scale, satu radius, satu palette (definisi di `tailwind.config`).

## 9. Tahapan Pengerjaan (Roadmap)

> Urutan ini memastikan jalan tanpa banyak revisi. Centang saat selesai.

### Fase 0 — Persiapan (1 hari)
- [x] Install Laravel baru + Breeze (`laravel new` + `composer require laravel/breeze` + `breeze:install vue`)
- [x] Konfigurasi `.env`: DB **MySQL** (buat database `sapdesa`)
- [x] Setup Tailwind + font lokal
- [x] Commit awal + branch `dev`

### Fase 1 — Data Model (1–2 hari)
- [x] Migration: `dusun`, `kategori`, `laporan`, `tindak_lanjut` + kolom role & dusun_id di `users`
- [x] Model + relationship di Laravel (Eloquent)
- [x] Seeder: 4 dusun, 3 kategori
- [x] Migration test: `php artisan migrate:fresh --seed`

### Fase 2 — Auth & Role (1–2 hari)
- [x] Role enum + helper `auth()->user()->role`
- [x] Middleware `role:superadmin,admin,kepala_dusun`
- [x] Seeder user: 1 superadmin, 1 admin, 4 kepala dusun (satu per dusun)

### Fase 3 — CRUD Master (1–2 hari)
- [ ] Kelola dusun (Admin/Superadmin) — CRUD page
- [ ] Kelola kategori (Admin/Superadmin) — CRUD page

### Fase 4 — Kelola Laporan (3–5 hari) ← inti
- [ ] Halaman daftar laporan (+ filter dusun/kategori/status, search)
- [ ] Create/edit/hapus laporan (kepala dusun, scope dusun sendiri)
- [ ] Detail laporan + lampiran (upload file)
- [ ] Ubah status + respon admin → catat `tindak_lanjut`
- [ ] Dashboard statistik + Chart.js per role

### Fase 5 — Pengelolaan User (1 hari)
- [ ] CRUD user (Superadmin)
- [ ] Assign dusun & role ke kepala dusun

### Fase 6 — Export & Hits (1–2 hari)
- [ ] Export CSV/Excel
- [ ] Export PDF (kalau butuh)
- [ ] Validasi input (Laravel Form Request) + pagination

### Fase 7 — Polish & Launch (2–3 hari)
- [ ] Responsif mobile final
- [ ] Error handling & flash message (toast)
- [ ] Redirect role-aware pasca login
- [ ] Test manual alur lengkap (create → proses → selesai)
- [ ] Deploy (VPS/shared hosting) + backup DB

## 10. Non-Goals (sengaja TIDAK dibuat dulu)
- Tidak ada login per warga individu (sesuai permintaan: per dusun).
- Tidak ada chat 2 arah realtime (cukup tindak lanjut tercatat) — upgrade: tambahkan komentar kalau diminta.
- Tidak ada notifikasi email/SMS otomatis — upgrade: notifikasi WA/email saat status berubah.
- Tidak ada mobile app native (cukup web responsif).

## 11. Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| Kepala dusun sulit pakai | UI sederhana + panduan singkat |
| Data scope bocor antar dusun | Enforce di query (scope) + test akses manual |
| File lampiran besar/server kecil | Batasi ukuran & tipe upload |
| Lupa backup | Backup otomatis/terjadwal di DB MySQL |
| Status tidak update | Tampilkan timeline & remind di dashboard admin |

## 12. Out of Scope / Notes
- Nama desa & 4 dusun pastikan mengikuti data riil Sirnaputra (isi nama dusun yang benar di seeder sebelum go-live).
- Gunakan bahasa Indonesia di semua UI (Breeze disesuaikan).

---

**Definisi Selesai (DoD):** Login 3 role jalan → kepala dusun bisa buat 1 laporan → admin lihat & ubah status → status muncul di tracking → seluruhnya responsive & tampil di HP.