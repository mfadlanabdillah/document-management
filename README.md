# Document Management API

Backend API untuk manajemen dokumen (kategori, tag, versi file, status, dan audit log) menggunakan Laravel 12 + Sanctum. Project ini juga membawa stack Inertia/Vue untuk frontend, namun API utama ada di `routes/api.php`.

## Fitur Utama
- Dokumen dengan status: `draft`, `active`, `archived`
- Versi dokumen (upload file, download versi)
- Kategori dan tag
- Audit log aktivitas dokumen
- Otorisasi berbasis policy
- Autentikasi token via Sanctum

## Prasyarat
- PHP 8.2+
- Composer
- Node.js & npm
- SQLite (default) atau database lain sesuai konfigurasi `.env`

## Setup Cepat
```bash
composer setup
```
Perintah ini akan:
- install dependency
- membuat `.env`
- generate `APP_KEY`
- menjalankan migrasi
- install dependency frontend dan build asset

Untuk menjalankan aplikasi:
```bash
composer dev
```

## Konfigurasi
Konfigurasi default menggunakan SQLite di `.env.example`:
```
DB_CONNECTION=sqlite
```
Jika memakai SQLite, pastikan file database ada (default Laravel: `database/database.sqlite`).

## Autentikasi API
Login untuk mendapatkan token:
```
POST /api/auth/login
```
Body:
```json
{
  "email": "user@example.com",
  "password": "password"
}
```
Response berisi `token`. Gunakan header:
```
Authorization: Bearer <token>
```

## Endpoint API
Base: `/api`

Dokumen:
1. `GET /documents`  
   Query: `category_id`, `status`, `search`
2. `POST /documents`  
   Form-data: `title`, `category_id`, `tag_ids[]`, `file` (opsional), `notes` (opsional)
3. `GET /documents/{document}`
4. `PUT /documents/{document}`  
   Body: `title`, `category_id`, `tag_ids[]`
5. `PATCH /documents/{document}`  
   Body: `status`
6. `DELETE /documents/{document}`
7. `POST /documents/{document}/restore`

Versi Dokumen:
1. `GET /documents/{document}/versions`
2. `POST /documents/{document}/versions`  
   Form-data: `file`, `notes` (opsional)
3. `GET /documents/{document}/versions/{version}/download`

## Status Dokumen
Transisi status yang valid:
- `draft -> active`
- `active -> archived`
- `archived -> active`

## Penyimpanan File
File versi disimpan di:
```
storage/app/documents/{document_id}/v{N}.{ext}
```

## Testing
Project memakai Pest.
```bash
composer test
```

## Struktur Domain (Singkat)
- `Document` (soft delete)
- `DocumentVersion`
- `DocumentActivityLog`
- `Category`
- `Tag`

Policy utama: `app/Policies/DocumentPolicy.php`.

---
Jika kamu ingin README ini ditambahkan contoh curl lengkap atau diagram arsitektur, beri tahu.
