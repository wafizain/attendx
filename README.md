
## 1. Alatnya
Download dan install:
- Laragon → https://drive.google.com/file/d/1Nk0i7NOxZuFltBivdAVpsGoJJWxl-CjO/view?usp=sharing  
- Git → https://drive.google.com/file/d/1P6P-oC0wnObSqzLwvjutMqfGJPtbldVK/view?usp=sharing  

## 2. Clone Project dari GitHub

> **Semua perintah jalankan lewat Terminal Laragon**, bukan CMD.  
> Cara buka: Laragon → Menu → *Terminal*

### Masuk ke folder Laragon:
```bash
cd C:\laragon\www
```
### Clone repository ini:
git clone https://github.com/wafizain/attendx.git

### Masuk ke folder project:
cd attendx

### 3.Jalanin Laragon
Buka aplikasi Laragon → klik Start All

### 4. Bikin Database Baru
Di Laragon klik Menu → MySQL → phpMyAdmin
Login:
User: root
Password: (kosong)
Klik tab Databases
Bikin database baru (di sidebar kiri ada button new):
Nama database nya: db_absen, klik create

### 5. Import Database
Download file SQL-nya di sini:
https://drive.google.com/file/d/1jrwA3dKNZXIYIEzuHhjWd03h9nRZgRoe/view?usp=sharing
Terus:
Masuk ke database db_absen (yang tadi udh di buat)
Klik tab Import
Upload file SQL yang barusan di download

6. Bikin File .env
Masih di Terminal Laragon, jalankan:
```bash
copy.env.example .env
```
7. Atur Isi File .env
Buka file .env, terus atur bagian database biar kayak gini:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_absen
DB_USERNAME=root
DB_PASSWORD=
```

8. Install Composer
Masih di Terminal Laragon, pastikan kamu lagi di folder project.
Jalankan:
```bash
composer install
```

9. Generate Key Laravel
Jalankan:
```bash
php artisan key:generate
```

10. Jalankan Aplikasi 
```bash
php artisan serve
```