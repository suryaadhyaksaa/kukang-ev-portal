# KUKANG EV ITERA — Attendance & Contribution Tracker

Sistem manajemen kehadiran kegiatan berbasis **QR Code dinamis**, pelacakan kontribusi kerja anggota (**Contribution Tracker**), arsip dokumentasi terproteksi, analitik dashboard real-time (**Laravel Reverb + Chart.js**), serta ekspor/sinkronisasi laporan **Excel (.xlsx)** dan **Microsoft Graph API** untuk **Tim Mobil Listrik KUKANG EV — Institut Teknologi Sumatera (ITERA)**.

## Arsitektur & Teknologi
- **Backend:** PHP 8.5, Laravel 13, Livewire 4, Laravel Fortify, Spatie Laravel Permission (ULID Primary Keys)
- **Database:** MySQL 8 (`utf8mb4_unicode_ci`)
- **Frontend:** Tailwind CSS v4, Alpine.js, Chart.js, `html5-qrcode`
- **QR & Reporting:** `chillerlan/php-qrcode` v6, `maatwebsite/excel` v4, Microsoft Graph REST API v1.0
- **Real-Time & Queue:** Laravel Reverb WebSocket Server & Database Queue Worker

## Instalasi Lokal (Laragon)
1. Clone atau tempatkan proyek pada `C:\laragon\www\kukang-ev-attendance`.
2. Salin `.env.example` menjadi `.env` dan sesuaikan koneksi MySQL (`DB_DATABASE=kukang_ev_attendance`).
3. Instal dependensi & build aset frontend:
   ```bash
   composer install
   npm install && npm run build
   php artisan key:generate
   php artisan storage:link
   php artisan migrate:fresh --seed
