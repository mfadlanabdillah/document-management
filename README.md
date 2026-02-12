# Document Management System

Aplikasi manajemen dokumen berbasis `Laravel 12` + `Vue 3 SPA` dengan fitur:
- autentikasi token `Sanctum`
- RBAC (`admin` / `user`)
- versioning dokumen
- master data (`status`, `category`, `tag`)
- status lifecycle matrix yang bisa dikonfigurasi
- audit log aktivitas dokumen dan manajemen user

## Stack
- Backend: `Laravel 12`, `Sanctum`, `Fortify`
- Frontend: `Vue 3`, `Vite`, `TailwindCSS`
- Test: `Pest`

## Fitur Utama
- Login/register API token
- Dashboard dokumen + filter + pagination
- CRUD dokumen + upload versi + download file
- Trash dokumen + restore
- Detail dokumen + activity logs
- User management (admin only)
- Master data management (admin only):
  - status CRUD
  - category CRUD
  - tag CRUD
  - status transition matrix (from status -> allowed next status)

## RBAC
Role user:
- `admin`: akses penuh, termasuk menu `Users` dan `Master Data`
- `user`: akses dokumen sesuai policy

Gate:
- `manage-users`
- `manage-master-data`

## URL Aplikasi
- SPA: `http://127.0.0.1:8000/app`
- API base: `http://127.0.0.1:8000/api`

## Prasyarat
- PHP `>= 8.2`
- Composer
- Node.js + npm
- PostgreSQL (saat ini konfigurasi aktif project)

## Setup Lokal
1. Install dependency:
```bash
composer install
npm install
```

2. Siapkan `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

3. Pastikan konfigurasi penting:
```env
APP_URL=http://127.0.0.1:8000
VITE_API_BASE_URL=http://127.0.0.1:8000/api
```

4. Migrate + seed:
```bash
php artisan migrate --seed
```

5. Jalankan aplikasi:
```bash
composer dev
```

## Fresh Reset (Seperti Baru)
Untuk reset total database + seed + clear runtime storage:
```bash
php artisan optimize:clear
rm -rf storage/app/documents && mkdir -p storage/app/documents
printf "*\n!.gitignore\n" > storage/app/documents/.gitignore
find storage/framework/cache -type f -not -name '.gitignore' -delete
find storage/framework/sessions -type f -not -name '.gitignore' -delete
find storage/framework/views -type f -not -name '.gitignore' -delete
find storage/logs -type f -name '*.log' -delete
php artisan migrate:fresh --seed --force
```

## Seeder
Seeder utama dijalankan lewat `DatabaseSeeder`:
- `CategorySeeder`
- `TagSeeder`
- `AdminUserSeeder`
- `UserSeeder`
- `DocumentSeeder`
- `UserManagementAuditLogSeeder`

Admin seed default (bisa override via `.env`):
```env
ADMIN_SEED_NAME="System Admin"
ADMIN_SEED_EMAIL=admin@example.com
ADMIN_SEED_PASSWORD=password
```

## Autentikasi API
1. Register:
- `POST /api/auth/register`

2. Login:
- `POST /api/auth/login`

3. Cek profil login:
- `GET /api/auth/me`

Gunakan header:
```http
Authorization: Bearer <token>
```

## Ringkasan Endpoint API
### Dokumen
- `GET /api/documents`
- `GET /api/documents/trash`
- `POST /api/documents`
- `GET /api/documents/{document}`
- `PUT /api/documents/{document}`
- `PATCH /api/documents/{document}`
- `PATCH /api/documents/{document}/status`
- `DELETE /api/documents/{document}`
- `POST /api/documents/{document}/restore`
- `GET /api/documents/{document}/activities`

### Versi Dokumen
- `GET /api/documents/{document}/versions`
- `POST /api/documents/{document}/versions`
- `GET /api/documents/{document}/versions/{version}/download`

### Master Data (read)
- `GET /api/categories`
- `GET /api/tags`
- `GET /api/document-statuses`

### User Management (admin)
- `GET /api/users`
- `POST /api/users`
- `GET /api/users/{user}`
- `PATCH /api/users/{user}`
- `PATCH /api/users/{user}/role`
- `DELETE /api/users/{user}`
- `GET /api/users/audit-logs`

### Master Data Management (admin)
- Status:
  - `POST /api/master/statuses`
  - `PATCH /api/master/statuses/{status}`
  - `DELETE /api/master/statuses/{status}`
- Status transition matrix:
  - `GET /api/master/status-transitions`
  - `PATCH /api/master/statuses/{status}/transitions`
- Category:
  - `GET /api/master/categories`
  - `POST /api/master/categories`
  - `PATCH /api/master/categories/{category}`
  - `DELETE /api/master/categories/{category}`
- Tag:
  - `GET /api/master/tags`
  - `POST /api/master/tags`
  - `PATCH /api/master/tags/{tag}`
  - `DELETE /api/master/tags/{tag}`

## Testing
Jalankan seluruh test:
```bash
php artisan test
```

## Performance Toolkit
Optimasi performa yang sudah ditambahkan:
- index DB untuk query dokumen/relasi utama
- trigram index PostgreSQL untuk pencarian `title`
- caching master data (`categories`, `tags`, `statuses`, `status transitions`) + invalidasi saat CRUD

Benchmark query list dokumen:
```bash
php artisan app:benchmark-documents --iterations=30
```

Contoh benchmark dengan filter:
```bash
php artisan app:benchmark-documents --iterations=30 --status=active --search=policy
```

## Catatan Menu Admin
Setelah login sebagai admin, menu ini muncul di topbar SPA:
- `Users`
- `Master Data`

Jika tidak muncul, cek role user di tabel `users` harus `admin`.
