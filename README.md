# SanitRiver Backend API

Backend RESTful API untuk **SanitRiver**, platform digital berbasis data spasial sungai dan pelaporan kondisi sanitasi/sungai yang mendukung **SDG 6 (Clean Water and Sanitation)**. Layanan ini dibangun menggunakan **Laravel** dengan arsitektur *decoupled API* berbasis JSON, autentikasi token **Laravel Sanctum**, kontrol akses berbasis peran dan izin (**Spatie Laravel Permission**), database **MariaDB** (terkelola di Layerbase), serta deployment di **Wasmer**.

---

## Identitas Kelompok

### Anggota Kelompok 6 — SDG 6: Clean Water and Sanitation (4 Anggota)

| No | Nama Lengkap | NIM / ID | Peran dalam Tim |
| :- | :--- | :--- | :--- |
| 1 | *Angelis Nasta Nadia* | *254311010* | *Frontend / Implementasi UI* |
| 2 | *Mohamad Malik fajar Baihaqi* | *254311011* | *Backend & Keamanan / Pengaturan endpoint / Auth* |
| 3 | *Raditya Daffa Pratama* | *254311018* | *Frontend / Integrasi API* |
| 4 | *ZAHRAH* | *244311060* | *Backend / REST API* |

### Anggota Pengembang Backend (2 Anggota)

| No | Nama Lengkap | NIM / ID | Tanggung Jawab Utama Backend |
| :- | :--- | :--- | :--- |
| 1 | *Mohamad Malik fajar Baihaqi* | *254311011* | Routing API, autentikasi Sanctum, pengujian API (Bruno), & deployment Wasmer |
| 2 | *ZAHRAH* | *244311060* | Skema database & seeder, Implementasi JSON Response, multi-guard RBAC/Permission Spatie |

---

## Arsitektur & Ringkasan Sistem

- **Decoupled Backend API:** Seluruh endpoint mengembalikan respons JSON terstandar (`status`, `message`, `data`) beserta kode status HTTP yang konsisten (`200`, `201`, `401`, `403`, `404`, `422`, `500`) untuk memudahkan konsumsi oleh aplikasi frontend terpisah.
- **Dual Authenticatable Model (`User` & `Admin`):** Memisahkan entitas pengguna umum (`User`, primary key `user_id`, guard `user`) dan administrator (`Admin`, primary key `admin_id`, guard `admin`) dengan konfigurasi guard/provider eksplisit di `config/auth.php`.
- **Pertahanan Berlapis (*Defense in Depth*):** Mengombinasikan middleware rute (`auth:sanctum`, `role:...`, `permission:...`) dengan validasi model (`instanceof`) dan pengecekan izin berbasis guard di dalam controller.
- **Strategi Routing Terstruktur:** Rute statis/khusus (seperti `/laporan-sungais/latest`, `/laporan-sungais/search`, `/sungai-bbox`, `/dashboard-user`) ditempatkan sebelum rute berparameter dinamis (seperti `/{laporan_sungai}`) agar tidak terjadi bentrok rute atau `404`.

---

## Pihak Ketiga & Alasan Penggunaan

| Pihak Ketiga / Teknologi | Fungsi dalam Proyek | Alasan Penggunaan |
| :--- | :--- | :--- |
| **Laravel Framework** | Kerangka kerja utama REST API, routing, Eloquent ORM, validasi, & seeder | Ekosistem matang, struktur kode rapi, dukungan migrasi/seeder yang jelas, serta mempercepat pengembangan API terstandar. |
| **Laravel Sanctum** | Autentikasi API berbasis token (`Bearer Token`) | Ringan dan sesuai untuk arsitektur API *decoupled* tanpa ketergantungan sesi browser tradisional, sekaligus mendukung asosiasi token ke model `User` maupun `Admin`. |
| **Spatie Laravel Permission** | Manajemen *Role* dan *Permission* (`role:admin`, `permission:...`) | Menyediakan kontrol akses granular berbasis database dan cache, serta mendukung pemisahan *guard* (`user` dan `admin`) secara terstruktur. |
| **MariaDB (Layerbase)** | Sistem manajemen basis data relasional (RDBMS) | Andal untuk menyimpan data relasional pengguna, admin, sungai, dan laporan sungai; hosting Layerbase memudahkan verifikasi koneksi database cloud sejak awal. |
| **Wasmer** | Platform *deployment* aplikasi backend | Memungkinkan proses *build* dan *deploy* lingkungan produksi secara praktis serta memvalidasi kesiapan runtime lebih awal dalam siklus pengembangan. |
| **Bruno** | Klien pengujian API (*API Client*) | Memudahkan pengujian endpoint publik, autentikasi, dan kasus *authorized* (`200`/`201`) maupun *unauthorized* (`401`/`403`) secara terstruktur dan ringan. |

---

## Struktur Utama Proyek

```text
SanitRiver-Backend/
├── app/
│   ├── Http/Controllers/Api/   # AuthController, AdminController, SungaiController, LaporanSungaiController, dll.
│   └── Models/                 # User, Admin, Sungai, LaporanSungai
├── bootstrap/
│   └── app.php                 # Registrasi middleware & konfigurasi aplikasi
├── config/
│   └── auth.php                # Konfigurasi guards & providers (users / admins)
├── database/
│   ├── migrations/             # Struktur tabel aplikasi, Sanctum, dan Spatie Permission
│   └── seeders/                # RolePermissionSeeder, AdminSeeder, UserSeeder, DatabaseSeeder
└── routes/
    └── api.php                 # Definisi endpoint publik, autentikasi, permission, dan admin
```

---

## Cara Clone dan Menjalankan (Local Development)

### Prasyarat

- **PHP** (sesuai versi yang didukung proyek Laravel yang digunakan) & **Composer**
- **MariaDB** / **MySQL** (lokal, Docker, atau instance Layerbase)
- **Git**

### 1. Clone Repositori

```bash
git clone [https://github.com/](https://github.com/)<username-atau-organisasi>/SanitRiver-Backend.git
cd SanitRiver-Backend
```

### 2. Instal Dependensi PHP

```bash
composer install
```

### 3. Konfigurasi Environment (`.env`)

Salin berkas `.env.example` menjadi `.env`, lalu generate *application key*:

```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan konfigurasi database di `.env` (arah ke MariaDB lokal atau kredensial Layerbase):

```env
APP_NAME=SanitRiver
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sanitriver_db
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migrasi dan Seeding Database

Jalankan migrasi beserta seeder sesuai urutan (`RolePermissionSeeder` terlebih dahulu, diikuti `AdminSeeder` dan seeder data/pengguna):

```bash
php artisan migrate:fresh --seed
```

Jika ingin menjalankan seeder secara terpisah:

```bash
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=AdminSeeder
```

### 5. Jalankan Server Lokal

```bash
php artisan serve
```

API akan berjalan di `http://127.0.0.1:8000` (dengan prefix rute API sesuai konfigurasi Laravel, umumnya `/api/...`).

---

## Ringkasan Endpoint Utama

- **Autentikasi (`User` & `Admin`):**
  - `POST /api/auth/register-user`, `POST /api/auth/login-user`, `POST /api/auth/logout-user`
  - `POST /api/auth/register-admin`, `POST /api/auth/login-admin`, `POST /api/auth/logout-admin`
- **Data Spasial & Publik:**
  - `GET /api/sungai-bbox`
  - `GET /api/laporan-sungais`, `GET /api/laporan-sungais/{laporan_sungai}`
  - `GET /api/laporan-sungais/latest`, `GET /api/laporan-sungais/search`
  - `GET /api/sungais`, `GET /api/sungais/{sungai}`
  - `GET /api/dashboard-user`
- **Terproteksi Sanctum, Role & Permission:**
  - `GET /api/user`
  - `POST / PATCH / DELETE /api/laporan-sungais`
  - `POST / PATCH / DELETE /api/sungais`
  - Prefix `/api/admin/*` (`GET /api/admin/dashboard-admin`, `apiResource` `users` & `admins` dengan peran `admin`)

---

## Pengujian & Deployment

- **Pengujian API:** Seluruh alur registrasi, login, akses data publik, otorisasi berbasis peran/izin (`role` & `permission`), serta penolakan akses tidak sah (`401`/`403`) telah diuji.
- **Deployment:** Kesiapan lingkungan produksi telah diverifikasi menggunakan **Wasmer** dan basis data **MariaDB** pada **Layerbase**. Pastikan variabel lingkungan produksi (`APP_ENV=production`, `APP_DEBUG=false`, kredensial DB, dan konfigurasi CORS/URL) diatur aman pada platform deployment sebelum digunakan secara publik.
