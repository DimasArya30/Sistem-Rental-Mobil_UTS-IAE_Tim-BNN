# Sistem Rental Mobil - DriveNow

Sistem Rental Mobil adalah project yang menerapkan konsep **Service-to-Service Communication**. Sistem ini terdiri dari tiga service yang berjalan secara terpisah, tetapi saling berkomunikasi secara langsung menggunakan HTTP request dan format data JSON.

Project ini berfokus pada bagaimana setiap service dapat berperan sebagai **provider** dan **consumer** secara bersamaan.

---

## Gambaran Sistem

Proses utama sistem ini adalah pembuatan transaksi sewa mobil. Ketika client membuat rental baru melalui RentalService, maka RentalService akan mengambil data customer dari CustomerService dan mengambil data mobil dari CarService.

Jika data customer dan mobil valid serta mobil berstatus `available`, RentalService akan meminta CarService untuk mengubah status mobil menjadi `rented`. Setelah itu, data rental akan disimpan oleh RentalService.

Selain itu, CustomerService juga dapat mengambil histori rental dari RentalService. CarService pun dapat melihat daftar customer yang menjadi penyewa potensial. Jadi setiap service tidak hanya menyediakan data, tetapi juga menggunakan data dari service lain.

---

## Daftar Service

| Service | Teknologi | Port | Fungsi |
|---|---|---:|---|
| CustomerService | Laravel | 8000 | Mengelola data customer/penyewa |
| RentalService | Laravel | 8002 | Mengelola transaksi sewa mobil |
| CarService | CodeIgniter 4 | 8080 | Mengelola data mobil dan status |

---

## Konsep Provider dan Consumer

| Service | Sebagai Provider | Sebagai Consumer |
|---|---|---|
| CustomerService | Menyediakan data customer | Mengambil histori rental dari RentalService |
| RentalService | Menyediakan data rental | Mengambil data customer dari CustomerService dan data mobil dari CarService |
| CarService | Menyediakan data mobil dan update status | Mengambil daftar customer dari CustomerService |

---

## Alur Komunikasi Utama

1. Client mengirim request pembuatan rental ke RentalService.
2. RentalService meminta data customer ke CustomerService.
3. RentalService meminta data mobil ke CarService.
4. RentalService mengecek apakah status mobil `available`.
5. Jika tersedia, RentalService menghitung total hari dan harga.
6. RentalService meminta CarService untuk mengubah status mobil menjadi `rented`.
7. RentalService menyimpan data rental (denormalized).
8. CustomerService dapat mengambil histori rental dari RentalService.
9. Saat menghapus customer, CustomerService mengecek ke RentalService apakah masih ada rental aktif.

---

## Teknologi yang Digunakan

- Laravel (CustomerService & RentalService)
- CodeIgniter 4 (CarService)
- PHP
- Composer
- MySQL
- REST API
- JSON
- Postman

---

## Struktur Project

```text
Sistem-Rental-Mobil_UTS-IAE_Tim-BNN/
├── customer-service/        # Laravel — mengelola data customer
├── rental-service/          # Laravel — mengelola transaksi rental
└── car-service/             # CodeIgniter 4 — mengelola data mobil
```

---

## Setup Database

Buat tiga database terpisah di MySQL:

```sql
CREATE DATABASE db_customer_service;
CREATE DATABASE db_rental_service;
CREATE DATABASE db_car_service;
```

---

## Cara Menjalankan Project

Pastikan **PHP**, **Composer**, dan **MySQL** sudah terinstall di perangkat.

### 1. Menjalankan CustomerService

Masuk ke folder CustomerService:

```bash
cd customer-service
```

Install dependency:

```bash
composer install
```

Salin file environment dan sesuaikan konfigurasi database:

```bash
cp .env.example .env
```

Isi nilai berikut di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_customer_service
DB_USERNAME=root
DB_PASSWORD=

RENTAL_SERVICE_URL=http://localhost:8002
```

Generate app key dan jalankan migrasi:

```bash
php artisan key:generate
php artisan migrate
php artisan db:seed --class=CustomerSeeder
```

Jalankan service di port 8000:

```bash
php artisan serve --port=8000
```

CustomerService akan berjalan di:

```
http://127.0.0.1:8000
```

---

### 2. Menjalankan RentalService

Buka terminal baru, masuk ke folder RentalService:

```bash
cd rental-service
```

Install dependency:

```bash
composer install
```

Salin file environment dan sesuaikan konfigurasi:

```bash
cp .env.example .env
```

Isi nilai berikut di `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_rental_service
DB_USERNAME=root
DB_PASSWORD=

CUSTOMER_SERVICE_URL=http://localhost:8000
CAR_SERVICE_URL=http://localhost:8080
```

Generate app key dan jalankan migrasi:

```bash
php artisan key:generate
php artisan migrate
```

Jalankan service di port 8002:

```bash
php artisan serve --port=8002
```

RentalService akan berjalan di:

```
http://127.0.0.1:8002
```

---

### 3. Menjalankan CarService

Buka terminal baru, masuk ke folder CarService:

```bash
cd car-service
```

Install dependency:

```bash
composer install
```

Salin file environment:

```bash
cp env .env
```

Sesuaikan konfigurasi database di `.env`:

```env
CI_ENVIRONMENT = development

database.default.hostname = localhost
database.default.database = db_car_service
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port     = 3306
```

Jalankan migrasi:

```bash
php spark migrate
```

Jalankan service di port 8080:

```bash
php spark serve --host 127.0.0.1 --port 8080
```

CarService akan berjalan di:

```
http://127.0.0.1:8080
```

---

## Endpoint CustomerService

Base URL: `http://127.0.0.1:8000`

### Get All Customers

```http
GET /api/customers
```

Mengambil semua data customer.

**Response:**
```json
{
  "success": true,
  "message": "Customers retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Andrew Napitupulu",
      "email": "andrew@email.com",
      "phone": "081234567890",
      "ktp_number": "3201012345670001",
      "address": "Jl. Cibubur No. 1, Bekasi"
    }
  ]
}
```

---

### Get Customer by ID

```http
GET /api/customers/{id}
```

Mengambil detail customer berdasarkan ID.

---

### Create Customer

```http
POST /api/customers
```

Membuat data customer baru.

**Request Body:**
```json
{
  "name": "Budi Santoso",
  "email": "budi@email.com",
  "phone": "081234567892",
  "ktp_number": "3201012345670003",
  "address": "Jl. Merdeka No. 5, Jakarta"
}
```

---

### Delete Customer

```http
DELETE /api/customers/{id}
```

Menghapus data customer. Jika customer masih memiliki rental yang berstatus `active`, penghapusan akan ditolak.

> **CONSUMER:** Endpoint ini memanggil RentalService untuk memvalidasi status rental customer.

---

### Get Customer Rental History

```http
GET /api/customers/{id}/rentals
```

Mengambil histori rental milik customer tertentu dari RentalService.

> **CONSUMER:** Endpoint ini memanggil RentalService secara langsung.

**Response:**
```json
{
  "success": true,
  "message": "Customer with rental history retrieved",
  "customer": { "id": 1, "name": "Andrew Napitupulu", "..." : "..." },
  "rental_history": [...]
}
```

---

## Endpoint RentalService

Base URL: `http://127.0.0.1:8002`

### Get All Rentals

```http
GET /api/rentals
```

Mengambil semua data rental.

---

### Get Rental by ID

```http
GET /api/rentals/{id}
```

Mengambil detail rental berdasarkan ID.

---

### Get Active Rentals

```http
GET /api/rentals/active
```

Mengambil semua rental yang berstatus `active`.

---

### Get Rentals by Customer

```http
GET /api/rentals/customer/{customerId}
```

Mengambil semua rental milik customer tertentu.

---

### Create Rental

```http
POST /api/rentals
```

Membuat transaksi rental baru.

> **CONSUMER:** Endpoint ini memanggil CustomerService untuk validasi customer dan CarService untuk validasi serta update status mobil.

**Request Body:**
```json
{
  "customer_id": 1,
  "car_id": 1,
  "rental_date": "2026-05-10",
  "return_date": "2026-05-13"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Rental created successfully",
  "data": {
    "id": 1,
    "customer_id": 1,
    "car_id": 1,
    "customer_name": "Andrew Napitupulu",
    "customer_email": "andrew@email.com",
    "car_brand": "Toyota",
    "car_model": "Avanza",
    "car_plate": "B 1234 ABC",
    "rental_date": "2026-05-10",
    "return_date": "2026-05-13",
    "total_days": 3,
    "total_price": "450000.00",
    "status": "active"
  },
  "service_calls": {
    "customer_service": "success",
    "car_service_get": "success",
    "car_service_update": "success"
  }
}
```

---

### Return Car

```http
PUT /api/rentals/{id}/return
```

Mengembalikan mobil dan mengubah status rental menjadi `returned`.

> **CONSUMER:** Endpoint ini memanggil CarService untuk mengubah status mobil kembali menjadi `available`.

**Response:**
```json
{
  "success": true,
  "message": "Car returned successfully",
  "data": { "...": "..." },
  "car_status_update": "success"
}
```

---

## Endpoint CarService

Base URL: `http://127.0.0.1:8080`

### Get All Cars

```http
GET /api/cars
```

Mengambil semua data mobil.

---

### Get Car by ID

```http
GET /api/cars/{id}
```

Mengambil detail mobil berdasarkan ID.

---

### Get Available Cars

```http
GET /api/cars/available
```

Mengambil daftar mobil yang tersedia.

---

### Create Car

```http
POST /api/cars
```

Menambahkan data mobil baru.

**Request Body:**
```json
{
  "brand": "Toyota",
  "model": "Avanza",
  "year": 2022,
  "plate_number": "B 1234 ABC",
  "price_per_day": 150000
}
```

---

### Update Car Status

```http
PUT /api/cars/{id}/status
```

Mengubah status mobil. Status yang tersedia: `available`, `rented`.

**Request Body:**
```json
{
  "status": "available"
}
```

---

### Delete Car

```http
DELETE /api/cars/{id}
```

Menghapus data mobil. Mobil yang sedang berstatus `rented` tidak dapat dihapus.

---

### Get Car Renters

```http
GET /api/cars/{id}/renters
```

Mengambil daftar customer potensial dari CustomerService.

> **CONSUMER:** Endpoint ini memanggil CustomerService.

---

## Data Seeder

CustomerService sudah dilengkapi dengan data awal:

| ID | Nama | Email | KTP |
|---|---|---|---|
| 1 | Andrew Napitupulu | andrew@email.com | 3201012345670001 |
| 2 | Ifli Najzahya | ifli@email.com | 3201012345670002 |

Jalankan seeder dengan perintah:

```bash
php artisan db:seed --class=CustomerSeeder
```

---

## Dashboard

RentalService dilengkapi dengan dashboard UI berbasis web yang dapat diakses di:

```
http://127.0.0.1:8002/dashboard.html
```

---

## Dokumentasi API

Dokumentasi API dibuat menggunakan Postman Documentation.

Link Postman Documentation:

```
https://documenter.getpostman.com/view/53766841/2sBXqKq15o
```

---

## Video Demo

Link Video Demo:

```
https://drive.google.com/file/d/1iz1g_wE25EgNNZ-ROZ4tfS6aWMIANS-t/view?usp=sharing
```

---

## Kesimpulan

Sistem Rental Mobil DriveNow telah menerapkan service-to-service communication dengan tiga service yang berjalan secara mandiri. Setiap service memiliki peran sebagai provider dan consumer. Komunikasi antar service dilakukan secara langsung menggunakan REST API, HTTP request, dan format data JSON.
