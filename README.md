# Mind OS

**Hipnoterapi & kesehatan mental berbasis metode 6R** — Recognize, Reveal, Release, Reframe, Reprogram, Reinforce.

Live: [mindos.mooda.id](https://mindos.mooda.id)

Mind OS menggabungkan Web App gratis untuk latihan mandiri, sesi Online Live, sesi Tatap Muka bersama praktisi, dan jalur sertifikasi Practitioner → Master → Trainer dalam satu aplikasi.

## Fitur

| Area | Fitur |
|---|---|
| **Web App (gratis)** | Perjalanan Saya (alur A–P), Modul Foundation (Filosofi Dasar, 5 Lapisan Diri, 7 Pola Pikiran), Cek Kesehatan Pikiran + rekomendasi modalitas, Jurnal Recognize, Audio Guided Healing, 6R Tracker, Action Plan 30 Hari, Evaluasi 30 Hari, Komunitas |
| **Layanan** | Booking Online Live (Reframe, Reprogram Guided, workshop, coaching) dan Tatap Muka (Release, Reprogram Deep, trauma, couple) |
| **Sertifikasi** | Practitioner, Master Practitioner, Trainer & Lisensi, dengan cek prasyarat dan nomor sertifikat otomatis |
| **Practitioner** | Sesi Klien: konfirmasi, link meeting, catatan sesi |
| **Admin** | Kelola booking, sertifikasi, dan konten (modul, pola pikiran, audio, layanan), dashboard statistik, user & role, log aktivitas |
| **Publik** | Landing page full-screen, SEO (meta, JSON-LD, sitemap, robots), security headers |

Role: `Superadmin`, `admin`, `Practitioner`, `Trainer`, `Klien`.

## Teknologi

- Laravel 12, PHP 8.2+
- PostgreSQL
- spatie/laravel-permission, spatie/laravel-activitylog
- Blade + tema admin di `public/assets`. Tidak perlu build Node untuk menjalankan aplikasi.

## Menjalankan di lokal

```bash
git clone https://github.com/rendyirawann/mind-os.git
cd mind-os
composer install
cp .env.example .env
php artisan key:generate
```

Lalu atur `DB_*` di `.env` ke database PostgreSQL lokal dan jalankan:

```bash
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Buka `http://127.0.0.1:8000`.

### Akun hasil seeder

Password awal di lokal adalah nilai `SEED_DEFAULT_PASSWORD` di `.env` (bawaan: `MindOs@2026!`). Di production nilai ini **wajib** diisi sendiri; seeder menolak berjalan tanpanya.

| Role | Email |
|---|---|
| Superadmin | `superadmin@mindos.mooda.id` |
| admin | `admin@mindos.mooda.id` |
| Practitioner | `practitioner@mindos.mooda.id`, `master@mindos.mooda.id` |
| Trainer | `trainer@mindos.mooda.id` |
| Klien | `klien@mindos.mooda.id`, `klien2@mindos.mooda.id` |

## Test

```bash
php artisan test
```

Test memakai SQLite in-memory, jadi tidak menyentuh database lokal.

## Deploy

Panduan lengkap ke server (nginx, PHP-FPM, PostgreSQL, SSL, update rutin) ada di **[DEPLOY.md](DEPLOY.md)**. File konfigurasinya di folder [`deploy/`](deploy/).
