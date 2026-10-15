# Deploy Mind OS ke `mindos.mooda.id`

Panduan memasang Mind OS (Laravel 12 + PostgreSQL) di server Ubuntu/Debian dengan **nginx + PHP-FPM**, tanpa mengganggu project lain yang sudah berjalan di server yang sama.

- **Repository:** [github.com/rendyirawann/mind-os](https://github.com/rendyirawann/mind-os) (publik, branch `main`)
- **Domain:** `https://mindos.mooda.id`
- **Folder di server:** `/var/www/mindos.mooda.id`

Alur kerjanya:

1. Ubah kode di laptop.
2. `git push` ke GitHub.
3. Di server, jalankan `deploy/deploy.sh`. Skrip ini menarik commit terbaru lalu memasangnya.

Semua file konfigurasi siap pakai ada di folder [`deploy/`](deploy/):

| File | Dipasang ke |
|---|---|
| [`deploy/env.production.example`](deploy/env.production.example) | `/var/www/mindos.mooda.id/.env` |
| [`deploy/nginx/mindos.mooda.id.conf`](deploy/nginx/mindos.mooda.id.conf) | `/etc/nginx/sites-available/mindos.mooda.id` |
| [`deploy/php-fpm/mindos.conf`](deploy/php-fpm/mindos.conf) | `/etc/php/8.3/fpm/pool.d/mindos.conf` |
| [`deploy/supervisor/mindos-worker.conf`](deploy/supervisor/mindos-worker.conf) | `/etc/supervisor/conf.d/` (opsional) |
| [`deploy/deploy.sh`](deploy/deploy.sh) | dijalankan untuk setiap update |

---

## Prinsip: tidak mengganggu project lain

| Bagian | Milik Mind OS sendiri | Yang **tidak** disentuh |
|---|---|---|
| Folder | `/var/www/mindos.mooda.id` | folder project lain |
| User Linux | `mindos` | user/owner project lain |
| PHP-FPM | pool `[mindos]`, socket `/run/php/php8.3-fpm-mindos.sock` | pool `www` & pool lain |
| nginx | 1 file `sites-available/mindos.mooda.id`, log sendiri | `nginx.conf`, site lain |
| PostgreSQL | role `mindos`, database `mindos` | database & `pg_hba.conf` lain |
| Session cookie | `mindos_session`, host-only (tidak dibagi ke `*.mooda.id`) | cookie app lain |
| Cache/queue/session | disimpan di database `mindos` sendiri | Redis/cache app lain |
| Realtime | dimatikan (`BROADCAST_CONNECTION=log`), tidak membuka port | port 8080 dsb. |

Aturan saat bekerja di server:

- Selalu jalankan `nginx -t` dan `php-fpm8.3 -t` **sebelum** reload.
- Pakai `systemctl reload` (graceful), **jangan** `restart`.
- Jangan ubah `/etc/nginx/nginx.conf`, pool `www.conf`, atau `pg_hba.conf`.

---

## 0. Cek kondisi server (hanya membaca, aman)

```bash
lsb_release -a                       # versi Ubuntu/Debian
nginx -v
ls /etc/php/                         # versi PHP yang terpasang
php -v                               # PHP CLI default (dipakai project lain)
psql --version
sudo -u postgres psql -c "SHOW port;"          # port PostgreSQL (biasanya 5432)
sudo -u postgres psql -c "\l" | grep -i mindos  # pastikan nama DB 'mindos' belum dipakai
ls /etc/nginx/sites-enabled/                    # site lain yang sudah aktif
grep -rl "mindos.mooda.id" /etc/nginx/ || echo "belum ada config untuk domain ini"
dig +short mindos.mooda.id                      # harus mengarah ke IP server ini
```

Jika versi PHP di server bukan 8.3, ganti semua `8.3` di panduan dan di file `deploy/` dengan versi yang tersedia (minimal **8.2**).

## 1. DNS

Buat record **A** `mindos` di zona `mooda.id` yang mengarah ke IP publik server, lalu tunggu sampai `dig +short mindos.mooda.id` menampilkan IP tersebut.

Jika domain lewat **Cloudflare** (awan oranye): gunakan SSL mode **Full (strict)**, bukan *Flexible*, agar tidak terjadi redirect loop.

## 2. Ekstensi PHP

Pasang hanya ekstensi yang belum ada. Menambah ekstensi tidak memengaruhi project lain.

```bash
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-pgsql php8.3-mbstring php8.3-xml \
    php8.3-curl php8.3-zip php8.3-gd php8.3-intl php8.3-bcmath unzip
```

> **Penting:** kalau PHP 8.3 baru dipasang di server yang sebelumnya memakai versi lain, perintah `php` default bisa ikut berpindah ke 8.3 dan memengaruhi cron/artisan project lain. Cek lalu kembalikan bila berubah:
>
> ```bash
> update-alternatives --display php
> sudo update-alternatives --set php /usr/bin/php8.1   # contoh: kembalikan ke versi semula
> ```
>
> Untuk Mind OS, panggil PHP secara eksplisit dengan `/usr/bin/php8.3`.

Composer (jika belum ada):

```bash
composer --version || (curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer)
```

## 3. PostgreSQL: database & user khusus

```bash
sudo -u postgres psql
```

```sql
CREATE ROLE mindos WITH LOGIN PASSWORD 'GANTI_PASSWORD_DATABASE';
CREATE DATABASE mindos OWNER mindos ENCODING 'UTF8';
REVOKE ALL ON DATABASE mindos FROM PUBLIC;   -- role lain tidak bisa connect
\q
```

Uji koneksi (default Ubuntu sudah mengizinkan login password dari 127.0.0.1, jadi `pg_hba.conf` tidak perlu diubah):

```bash
psql "postgresql://mindos:GANTI_PASSWORD_DATABASE@127.0.0.1:5432/mindos" -c "select 1;"
```

Jika PostgreSQL belum terpasang sama sekali: `sudo apt install -y postgresql`, lalu ulangi langkah di atas.

## 4. User Linux & folder aplikasi

```bash
sudo adduser --system --group --shell /bin/bash --home /home/mindos mindos
sudo mkdir -p /var/www/mindos.mooda.id
sudo chown mindos:mindos /var/www/mindos.mooda.id
```

## 5. Ambil kode dari GitHub

Repo bersifat publik, jadi server tidak perlu token atau SSH key. Folder dari langkah 4 masih kosong, sehingga bisa langsung di-clone ke sana:

```bash
sudo apt install -y git
sudo -u mindos git clone https://github.com/rendyirawann/mind-os.git /var/www/mindos.mooda.id
cd /var/www/mindos.mooda.id && sudo -u mindos git log -1 --oneline   # commit yang terpasang
```

Jalankan `git` sebagai user `mindos`, bukan `root`. Kalau dijalankan sebagai root, git akan menolak dengan pesan *dubious ownership*, dan file baru jadi milik root.

> `.env`, `vendor/`, `node_modules/`, log, dan file upload user **tidak ada di repo** (sudah di-`.gitignore`). Server memakai `.env` sendiri di langkah 7.

<details>
<summary>Alternatif tanpa git: upload arsip dari laptop Windows (Git Bash)</summary>

```bash
cd /c/xampp/htdocs/myProject/mind-os
tar --exclude=./.git --exclude=./vendor --exclude=./node_modules --exclude=./.env --exclude=./.env.backup \
    --exclude=./public/storage --exclude='./storage/logs/*' \
    --exclude='./storage/framework/cache/data/*' --exclude='./storage/framework/sessions/*' \
    --exclude='./storage/framework/views/*' --exclude='./bootstrap/cache/*.php' \
    -czf ../mindos.tar.gz .
scp ../mindos.tar.gz USER@IP_SERVER:/tmp/
```

Di server:

```bash
sudo tar -xzf /tmp/mindos.tar.gz -C /var/www/mindos.mooda.id
sudo chown -R mindos:mindos /var/www/mindos.mooda.id
rm /tmp/mindos.tar.gz
```

Dengan cara ini, `deploy.sh` tidak bisa menarik update dari GitHub. Setiap update harus di-upload ulang dengan cara yang sama.

</details>

## 6. Dependensi Composer

```bash
cd /var/www/mindos.mooda.id
sudo -u mindos -H mkdir -p storage/framework/{cache/data,sessions,views} storage/logs bootstrap/cache
sudo -u mindos -H /usr/bin/php8.3 "$(command -v composer)" install --no-dev --prefer-dist --optimize-autoloader --no-interaction
```

Aset CSS/JS sudah ada di `public/assets`, jadi **tidak perlu** `npm`/Node di server.

## 7. File `.env` production

```bash
cd /var/www/mindos.mooda.id
sudo -u mindos cp deploy/env.production.example .env
sudo -u mindos nano .env
```

Isi semua nilai `GANTI_*`:

| Variabel | Isi |
|---|---|
| `DB_PASSWORD` | password role `mindos` dari langkah 3 |
| `DB_PORT` | hasil `SHOW port;` (biasanya `5432`) |
| `MAIL_*` | akun SMTP untuk e-mail reset password |
| `SEED_DEFAULT_PASSWORD` | password awal akun hasil seeder (rahasia, minimal 12 karakter). **Wajib** diisi: di production seeder menolak berjalan tanpanya, karena password cadangan di kode sudah terbaca publik di GitHub |

Variabel yang sudah diset untuk domain ini: `APP_URL=https://mindos.mooda.id`, `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, `SESSION_COOKIE=mindos_session`, `SESSION_DOMAIN=null`, `CACHE_PREFIX=mindos_cache_`, dan `CORS_ALLOWED_ORIGINS=https://mindos.mooda.id`.

Kunci aplikasi dibuat **sekali saja**, lalu kunci izin file:

```bash
sudo -u mindos /usr/bin/php8.3 artisan key:generate --force
sudo chmod 640 .env
```

## 8. Migrate & seeder

```bash
cd /var/www/mindos.mooda.id
sudo -u mindos /usr/bin/php8.3 artisan migrate --force
sudo -u mindos /usr/bin/php8.3 artisan db:seed --force
```

`db:seed` menjalankan `RolePermissionSeeder` (role & permission), `AccountSeeder` (akun, lihat [daftar akun](#daftar-akun)), dan `MindOsSeeder` (7 Pola Pikiran, pertanyaan, modul Foundation, audio, layanan, program sertifikasi). Semua seeder **idempotent**: aman dijalankan ulang, dan akun yang sudah ada tidak ditimpa.

## 9. Storage link, izin folder, cache production

```bash
cd /var/www/mindos.mooda.id
sudo -u mindos /usr/bin/php8.3 artisan storage:link
sudo chown -R mindos:mindos storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod 775 {} \;
sudo -u mindos /usr/bin/php8.3 artisan optimize      # config, route, view, event cache
```

Setiap kali `.env` diubah, jalankan ulang `artisan optimize` agar nilainya terbaca.

## 10. Pool PHP-FPM khusus

```bash
sudo cp /var/www/mindos.mooda.id/deploy/php-fpm/mindos.conf /etc/php/8.3/fpm/pool.d/mindos.conf
sudo php-fpm8.3 -t && sudo systemctl reload php8.3-fpm
ls -l /run/php/php8.3-fpm-mindos.sock     # socket harus muncul
```

## 11. nginx + SSL (Let's Encrypt)

Sertifikat belum ada, jadi pasang dulu blok HTTP sementara khusus untuk verifikasi:

```bash
sudo tee /etc/nginx/sites-available/mindos.mooda.id >/dev/null <<'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name mindos.mooda.id;
    location ^~ /.well-known/acme-challenge/ { root /var/www/mindos.mooda.id/public; }
    location / { return 404; }
}
EOF
sudo ln -s /etc/nginx/sites-available/mindos.mooda.id /etc/nginx/sites-enabled/mindos.mooda.id
sudo nginx -t && sudo systemctl reload nginx
```

Ambil sertifikat dengan mode `webroot`. Mode ini tidak mengubah file config nginx mana pun:

```bash
sudo apt install -y certbot
sudo certbot certonly --webroot -w /var/www/mindos.mooda.id/public -d mindos.mooda.id \
    --agree-tos -m admin@mooda.id --no-eff-email
```

Pasang config lengkap:

```bash
sudo cp /var/www/mindos.mooda.id/deploy/nginx/mindos.mooda.id.conf /etc/nginx/sites-available/mindos.mooda.id
sudo nginx -t && sudo systemctl reload nginx
```

Perpanjangan sertifikat otomatis lewat timer certbot. Setelah perpanjangan, nginx perlu di-reload; hook ini juga graceful:

```bash
echo 'systemctl reload nginx' | sudo tee /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh
sudo chmod +x /etc/letsencrypt/renewal-hooks/deploy/reload-nginx.sh
sudo certbot renew --dry-run
```

## 12. Verifikasi

```bash
curl -sI https://mindos.mooda.id | head -20     # 200 + header keamanan
curl -s  https://mindos.mooda.id/robots.txt
curl -s  https://mindos.mooda.id/sitemap.xml | head -5
curl -sI http://mindos.mooda.id | head -3       # 301 ke https
```

Lalu buka di browser:

- `https://mindos.mooda.id`: landing page tampil.
- `https://mindos.mooda.id/admin/login`: login memakai akun di bawah.
- [securityheaders.com](https://securityheaders.com/?q=mindos.mooda.id): cek grade header keamanan.
- Cek juga satu-dua situs lain di server: harus tetap normal.

Daftarkan `https://mindos.mooda.id/sitemap.xml` di Google Search Console.

---

## Daftar akun

Dibuat oleh `AccountSeeder` ([database/seeders/AccountSeeder.php](database/seeders/AccountSeeder.php)). Password awal semua akun adalah nilai `SEED_DEFAULT_PASSWORD` di `.env` server. Di lokal nilainya `MindOs@2026!`, tapi password ini **tidak boleh** dipakai di server karena repo-nya publik.

| Role | Nama | Email | Username | Setelah login ke |
|---|---|---|---|---|
| Superadmin | Super Admin Mind OS | `superadmin@mindos.mooda.id` | `mindos.superadmin` | Dashboard admin (akses penuh) |
| admin | Admin Operasional | `admin@mindos.mooda.id` | `mindos.admin` | Dashboard admin (kelola booking, sertifikasi, konten) |
| Practitioner | Practitioner Mind OS | `practitioner@mindos.mooda.id` | `mindos.practitioner` | Perjalanan Saya + menu **Sesi Klien** |
| Practitioner | Master Practitioner Mind OS | `master@mindos.mooda.id` | `mindos.master` | Perjalanan Saya + menu **Sesi Klien** |
| Trainer | Trainer Mind OS | `trainer@mindos.mooda.id` | `mindos.trainer` | Perjalanan Saya + menu **Sesi Klien** |
| Klien | Klien Demo | `klien@mindos.mooda.id` | `mindos.klien` | Perjalanan Saya (fitur Web App) |
| Klien | Klien Demo Dua | `klien2@mindos.mooda.id` | `mindos.klien2` | Perjalanan Saya (fitur Web App) |

Wajib setelah go-live:

1. Login sebagai setiap akun staf, lalu ganti password di **My Account → Security**.
2. Akun `klien*` hanya untuk demo. Nonaktifkan atau hapus lewat **Users** bila tidak dipakai.
3. Hapus `SEED_DEFAULT_PASSWORD` dari `.env` setelah seeding, lalu jalankan `artisan optimize`.

Untuk menambah akun baru, edit `AccountSeeder::accounts()` lalu jalankan:

```bash
sudo -u mindos /usr/bin/php8.3 artisan db:seed --class=AccountSeeder --force
```

---

## Update berikutnya

**Di laptop**, kirim perubahan ke GitHub:

```bash
cd /c/xampp/htdocs/myProject/mind-os
php artisan test                 # pastikan semua test lulus
git add -A
git commit -m "Deskripsi perubahan"
git push
```

**Di server**, tarik dan pasang:

```bash
sudo -u mindos -H env PHP=/usr/bin/php8.3 bash /var/www/mindos.mooda.id/deploy/deploy.sh
```

Skrip ini menjalankan beberapa langkah berurutan:

1. `git pull --ff-only` dari GitHub. Skrip berhenti jika ada file yang diubah langsung di server, jadi tidak ada yang tertimpa diam-diam.
2. Maintenance mode untuk app ini saja.
3. `composer install`.
4. `migrate --force`.
5. Seeder konten dan role (bukan akun).
6. Rebuild cache.
7. Online kembali.

nginx dan PHP-FPM tidak di-reload. `.env` di server tidak pernah disentuh, karena tidak ada di repo.

**Rollback ke versi sebelumnya:**

```bash
cd /var/www/mindos.mooda.id
sudo -u mindos git log --oneline -5                 # pilih commit tujuan
sudo -u mindos git checkout <hash-commit>
sudo -u mindos -H env PHP=/usr/bin/php8.3 bash deploy/deploy.sh
```

Saat berada di commit lama, `deploy.sh` otomatis melewati `git pull` dan langsung memasang commit tersebut.

Migrasi database yang sudah berjalan tidak ikut dibatalkan. Jika memang perlu, jalankan `sudo -u mindos /usr/bin/php8.3 artisan migrate:rollback --step=1 --force`.

Setelah perbaikan di-push, kembali ke jalur normal:

```bash
sudo -u mindos git checkout main
sudo -u mindos -H env PHP=/usr/bin/php8.3 bash deploy/deploy.sh
```

Jika `opcache.validate_timestamps=0` di server, kode baru baru terbaca setelah `sudo systemctl reload php8.3-fpm`. Reload ini graceful dan tidak memutus situs lain.

## Opsional

**Worker antrean.** Saat ini belum ada job antrean, jadi worker belum dibutuhkan. Jika nanti ada:

```bash
sudo cp /var/www/mindos.mooda.id/deploy/supervisor/mindos-worker.conf /etc/supervisor/conf.d/
sudo supervisorctl reread && sudo supervisorctl update   # hanya menambah program baru
```

**Scheduler.** Belum ada jadwal, tapi aman dipasang. Ini crontab milik user `mindos` saja:

```bash
sudo crontab -u mindos -e
* * * * * cd /var/www/mindos.mooda.id && /usr/bin/php8.3 artisan schedule:run >> /dev/null 2>&1
```

**Backup database harian**, disimpan 14 hari:

```bash
sudo mkdir -p /var/backups/mindos && sudo chown postgres:postgres /var/backups/mindos
sudo crontab -u postgres -e
30 2 * * * pg_dump -Fc mindos > /var/backups/mindos/mindos-$(date +\%F).dump && find /var/backups/mindos -name '*.dump' -mtime +14 -delete
```

Restore: `sudo -u postgres pg_restore --clean -d mindos /var/backups/mindos/mindos-YYYY-MM-DD.dump`

---

## Troubleshooting

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| **502 Bad Gateway** | socket PHP-FPM tidak ada / versi beda | `ls /run/php/`, samakan path socket di config nginx & pool |
| **500 Server Error** | izin folder `storage` / `.env` salah | `tail -50 storage/logs/laravel-*.log`, ulangi langkah 9 |
| **419 Page Expired** saat login | akses lewat `http://` atau `APP_URL` salah | pastikan buka via `https://`, `APP_URL` benar, lalu `artisan optimize` |
| Redirect loop | Cloudflare SSL mode *Flexible* | ubah ke **Full (strict)** |
| `SQLSTATE ... Connection refused` | port/password DB salah | cek `DB_PORT`, uji `psql` di langkah 3 |
| Perubahan `.env` tidak terbaca | config ter-cache | `sudo -u mindos /usr/bin/php8.3 artisan optimize` |
| CSS/gambar 404 | `root` nginx bukan folder `public` | `root /var/www/mindos.mooda.id/public;` |

Log yang berguna:

- `storage/logs/laravel-*.log`
- `/var/log/nginx/mindos.mooda.id.error.log`
- `journalctl -u php8.3-fpm`

## Melepas Mind OS dari server (bila perlu)

Langkah ini hanya menghapus milik Mind OS:

```bash
sudo rm /etc/nginx/sites-enabled/mindos.mooda.id /etc/nginx/sites-available/mindos.mooda.id
sudo nginx -t && sudo systemctl reload nginx
sudo rm /etc/php/8.3/fpm/pool.d/mindos.conf && sudo systemctl reload php8.3-fpm
sudo -u postgres psql -c "DROP DATABASE mindos;" -c "DROP ROLE mindos;"
sudo certbot delete --cert-name mindos.mooda.id
sudo rm -rf /var/www/mindos.mooda.id && sudo deluser mindos
```
