# RFI Management System

RFI Management System adalah aplikasi berbasis web untuk mengelola proses **Request for Information (RFI)** dalam lingkungan proyek konstruksi.

Sistem ini menghubungkan proses antara **DOCONT (Production)**, **INSPECTOR (Production)**, dan **MATERIAL MAN (Warehouse)** dalam proses pengajuan, pemeriksaan, validasi, revisi, approval, dan pengelolaan ketersediaan material.

## Overview

Sistem memiliki tiga role utama:

### DOCONT
- Membuat RFI
- Memilih project
- Memilih Inspector
- Menentukan material dan quantity yang dibutuhkan
- Mengirim RFI untuk dilakukan review
- Melakukan revisi apabila RFI membutuhkan revisi

### INSPECTOR
- Melakukan review dan validasi RFI
- Memeriksa setiap material
- Melihat quantity material yang tersedia pada master material
- Menentukan material `READY` atau `NOT READY`
- Memberikan remark apabila material `NOT READY`
- Melakukan approval terhadap RFI
- Quantity material yang disetujui akan mengurangi stock master material

### MATERIAL MAN
- Mengelola master material
- Menambah material
- Mengubah data material
- Mengelola quantity material
- Melihat transaksi material

## Business Flow

```text
MATERIAL MAN
     |
     v
MASTER MATERIAL
     |
     v
DOCONT
     |
     | Create RFI
     v
SELECT PROJECT
     |
     v
SELECT INSPECTOR
     |
     v
SELECT MATERIAL + QUANTITY
     |
     v
SUBMIT RFI
     |
     v
INSPECTOR
     |
     +----------------------+
     |                      |
     v                      v
   READY                NOT READY
     |                      |
     |                      v
     |                 REVISION REQUIRED
     |                      |
     |                      v
     |                    DOCONT
     |                      |
     |                      v
     |                  REVISION +1
     |                      |
     |                      v
     |                   SUBMIT
     |                      |
     +-----------> INSPECTOR
     |
     v
APPROVED
     |
     v
STOCK MATERIAL BERKURANG
```

## RFI Revision Flow

RFI menggunakan sistem revision tanpa menghapus atau membuat ulang nomor RFI.

Contoh:

```text
RFI-2026-0003 Rev 0
        |
        | Inspector meminta revisi
        v
RFI-2026-0003 Rev 1
        |
        | Inspector melakukan review
        v
APPROVED
```

Nomor RFI tetap sama, sedangkan nilai `revision` bertambah.

History revision disimpan pada:

- `rfi_revisions`
- `rfi_revision_items`

## Material Quantity Calculation

Master material menjadi sumber utama quantity yang tersedia.

Inspector dapat melihat:

| Quantity | Keterangan |
|---|---|
| Requested | Quantity yang diminta DOCONT |
| Available | Quantity saat ini pada master material |
| Approved | Quantity yang dapat disetujui |
| Shortage | Kekurangan quantity |

Perhitungan:

```text
Approved Quantity = MIN(Requested Quantity, Available Quantity)

Shortage Quantity = MAX(Requested Quantity - Available Quantity, 0)
```

Contoh:

```text
Requested : 100
Available : 70

Approved  : 70
Shortage  : 30
```

Inspector tidak perlu memasukkan Approved Quantity secara manual.

## Partial Approval

Sistem mendukung partial approval.

Contoh:

```text
Requested = 100 PCS
Available = 70 PCS
Approved  = 70 PCS
Shortage  = 30 PCS
```

Inspector tetap dapat memberikan status `READY` apabila quantity yang tersedia dapat digunakan.

`NOT READY` digunakan apabila Inspector memutuskan material tidak dapat atau tidak boleh diproses dan wajib memberikan remark.

## Stock Transaction

Stock material hanya dikurangi ketika RFI berhasil mendapatkan approval akhir.

Proses approval dilakukan dalam database transaction:

```text
BEGIN TRANSACTION
        |
        v
Lock Material Stock
        |
        v
Check Current Stock
        |
        v
Calculate Approved Quantity
        |
        v
Update RFI Item
        |
        v
Reduce Material Stock
        |
        v
Create Material Transaction
        |
        v
Update RFI = APPROVED
        |
        v
Create Approval History
        |
        v
COMMIT
```

Apabila terjadi error, proses akan di-rollback.

## RFI Status

| Status | Keterangan |
|---|---|
| `DRAFT` | RFI masih dalam proses pembuatan |
| `SUBMITTED` | RFI telah dikirim dan menunggu Inspector |
| `REVISION_REQUIRED` | Inspector meminta DOCONT melakukan revisi |
| `APPROVED` | RFI telah disetujui |

## RFI Item Status

| Status | Keterangan |
|---|---|
| `PENDING` | Belum diperiksa Inspector |
| `READY` | Material dinyatakan siap |
| `NOT_READY` | Material tidak dapat diproses dan membutuhkan remark |

## Main Features

### Authentication
- Login
- Logout
- Session-based authentication
- Role-based authorization
- Department dan role management
- Password menggunakan PHP password hashing

### DOCONT
- Dashboard RFI
- Create RFI
- Select project
- Select Inspector
- Select material
- Input requested quantity
- Submit RFI
- View RFI detail
- Revision RFI
- View revision history
- View approval history

### INSPECTOR
- Melihat RFI yang ditujukan kepada Inspector
- Melihat detail RFI
- Melihat requested quantity
- Melihat available material quantity
- Melihat calculated approved quantity
- Melihat shortage quantity
- Check material
- Set `READY`
- Set `NOT READY`
- Memberikan remark
- Approval RFI
- View approval history

### MATERIAL MAN
- Master material
- Create material
- Edit material
- Manage quantity
- View material transaction

## Technology Stack

### Backend
- PHP
- CodeIgniter 3
- PostgreSQL

### Frontend
- HTML5
- CSS3
- Bootstrap 5
- JavaScript
- jQuery
- DataTables
- Bootstrap Icons

### Authentication & Security
- CodeIgniter Session
- CodeIgniter CSRF Protection
- PHP `password_hash()`
- PHP `password_verify()`
- Role-based authorization

## Architecture

Sistem menggunakan arsitektur MVC CodeIgniter 3.

```text
View
  |
  v
Controller
  |
  v
Model
  |
  v
PostgreSQL
```

Sistem menggunakan server-side rendering dan tidak menggunakan SPA atau API sebagai arsitektur utama.

## Database

Database menggunakan PostgreSQL.

### Main Tables

```text
departments
roles
users
projects
materials
rfis
rfi_items
rfi_revisions
rfi_revision_items
rfi_approvals
material_transactions
```

### Relationship Overview

```text
departments
    |
    v
  roles
    |
    v
  users
    |
    +------------------+
    |                  |
    v                  v
  rfis              projects
    |
    v
rfi_items
    |
    v
materials
```

Revision:

```text
rfis
 |
 v
rfi_revisions
 |
 v
rfi_revision_items
```

Approval:

```text
rfis
 |
 v
rfi_approvals
```

Stock:

```text
materials
 |
 v
material_transactions
```

## Project Structure

```text
application/
├── config/
│   ├── autoload.php
│   ├── config.php
│   └── routes.php
├── controllers/
│   ├── Auth.php
│   ├── Rfi.php
│   ├── Inspector.php
│   └── Warehouse.php
├── models/
│   ├── User_model.php
│   ├── Rfi_model.php
│   └── Material_model.php
├── helpers/
│   ├── auth_helper.php
│   └── csrf_helper.php
└── views/
    ├── auth/
    │   └── login.php
    ├── rfi/
    │   ├── index.php
    │   ├── create.php
    │   ├── detail.php
    │   └── revision.php
    ├── inspector/
    │   ├── index.php
    │   └── detail.php
    ├── warehouse/
    │   └── index.php
    └── layout/
        ├── header.php
        ├── footer.php
        ├── navbar.php
        └── sidebar.php
```

## Installation

### 1. Clone Repository

```bash
git clone https://github.com/USERNAME/REPOSITORY.git
cd REPOSITORY
```

### 2. Web Server

Project dapat dijalankan menggunakan:
- XAMPP
- Laragon
- Apache
- PHP built-in server

Contoh menggunakan XAMPP:

```text
htdocs/
└── minipcms/
```

### 3. Database

Buat database PostgreSQL:

```sql
CREATE DATABASE minipcms;
```

Kemudian jalankan SQL schema yang tersedia pada project.

Pastikan table berikut berhasil dibuat:

```text
departments
roles
users
projects
materials
rfis
rfi_items
rfi_revisions
rfi_revision_items
rfi_approvals
material_transactions
```

### 4. Configure Database

Edit:

```text
application/config/database.php
```

Sesuaikan konfigurasi PostgreSQL:

```php
$db['default'] = array(
    'dsn'      => '',
    'hostname' => 'localhost',
    'username' => 'postgres',
    'password' => 'YOUR_PASSWORD',
    'database' => 'minipcms',
    'dbdriver' => 'postgre',
    'port'     => 5432
);
```

### 5. Configure Base URL

Edit:

```text
application/config/config.php
```

Contoh:

```php
$config['base_url'] = 'http://localhost/minipcms/';
$config['index_page'] = 'index.php';
```

### 6. Session Directory

Pastikan directory berikut tersedia:

```text
application/cache/sessions/
```

### 7. Run Application

Jalankan Apache dan PostgreSQL, kemudian akses:

```text
http://localhost/minipcms/
```

## Demo Account

### DOCONT

```text
Username : docont1
Password : password
Role     : DOCONT
```

### INSPECTOR

```text
Username : inspector1
Password : password
Role     : INSPECTOR
```

### MATERIAL MAN

```text
Username : materialman1
Password : password
Role     : MATERIAL_MAN
```

## Example Workflow

### Material Man

```text
Warehouse
   |
   v
Master Material
   |
   +-- Create Material
   +-- Edit Material
   +-- Update Quantity
   +-- View Transaction
```

### DOCONT

```text
RFI
 |
 +-- Create RFI
 +-- Select Project
 +-- Select Inspector
 +-- Select Material
 +-- Input Quantity
 +-- Submit
```

Status RFI menjadi `SUBMITTED`.

### Inspector

Inspector melihat RFI yang ditujukan kepadanya dan melakukan pemeriksaan berdasarkan:

```text
Requested
Available
Approved
Shortage
```

Kemudian Inspector menentukan:

```text
READY
```

atau:

```text
NOT READY
```

Jika `NOT READY`, remark wajib diisi.

### Approval

Jika seluruh material `READY`:

```text
RFI
 |
 v
APPROVED
 |
 v
Material Stock Reduced
 |
 v
Material Transaction Created
```

### Revision

Jika terdapat material `NOT READY`:

```text
SUBMITTED
    |
    v
REVISION_REQUIRED
    |
    v
DOCONT
    |
    v
Revision +1
    |
    v
Submit Again
```

Nomor RFI tetap sama.

## Security

Sistem menggunakan:

- Session authentication
- Role-based authorization
- CSRF protection
- Password hashing
- Database transaction untuk proses approval
- Material stock locking saat final approval
- Validasi status RFI
- Validasi hak akses Inspector terhadap RFI

## Important Business Rules

1. RFI Number tidak berubah ketika terjadi revision.
2. Revision number bertambah ketika RFI membutuhkan revisi.
3. RFI yang `NOT READY` tidak dihapus.
4. RFI menggunakan status `REVISION_REQUIRED`.
5. Inspector adalah pihak yang melakukan review dan validasi RFI.
6. Master material merupakan sumber quantity material.
7. Approved Quantity dihitung oleh sistem.
8. Inspector tidak menginput Approved Quantity secara manual.
9. Stock hanya berkurang ketika RFI berhasil di-approve.
10. Setiap perubahan stock dicatat pada `material_transactions`.
11. Proses pengurangan stock dilakukan menggunakan database transaction.
12. `NOT READY` wajib memiliki remark.
13. Partial approval dapat terjadi apabila stock tersedia lebih sedikit daripada requested quantity.

## Sample Material Calculation

```text
Material       : MAT-001
Material Name  : Cement
Requested      : 100 KG
Available      : 70 KG
```

Sistem menghitung:

```text
Approved = MIN(100, 70)
         = 70 KG

Shortage = MAX(100 - 70, 0)
         = 30 KG
```

Setelah approval:

```text
Stock Before = 70 KG
Approved     = 70 KG
Stock After  = 0 KG
```

Transaction:

```text
Transaction Type : OUT
Quantity         : -70
Before           : 70
After            : 0
```

## Current Scope

Sistem saat ini berfokus pada:

- Authentication
- Role management
- Project selection
- Master material
- RFI creation
- RFI submission
- RFI inspection
- Material availability calculation
- Partial approval
- RFI revision
- Approval history
- Material transaction history
- Stock deduction

## Future Development

- Dashboard dengan statistik RFI
- Notification untuk Inspector
- Notification untuk DOCONT ketika RFI membutuhkan revision
- Export RFI ke PDF
- Export report ke Excel
- Audit log yang lebih detail
- Advanced material search/autocomplete
- Filtering RFI berdasarkan project, status, dan tanggal
- User management
- Project management
- Pagination dan server-side DataTables
- Email notification

## Author

Developed as a technical test / project implementation for RFI and material management.

## License

This project is intended for educational, technical test, and development purposes.
