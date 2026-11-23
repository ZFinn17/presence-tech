# Sistem Absensi RFID - NodeMCU ESP8266

## Struktur Project

```
absensi_rfid/
├── config/
│   └── koneksi.php          # Koneksi database (edit jika password MySQL beda)
├── database/
│   └── absen_rfid.sql       # Import file ini ke phpMyAdmin
├── firmware/
│   └── absensi_rfid.ino     # Buka dengan Arduino IDE
├── header.php / footer.php  # Layout bersama
├── index.php                # Dashboard
├── datakaryawan.php         # Daftar karyawan
├── tambah.php                # Form registrasi karyawan (auto-read UID)
├── proses_tambah.php
├── edit.php / proses_edit.php
├── hapus.php
├── scan.php                  # Halaman utama untuk proses absensi harian
├── bacakartu.php             # Logika inti pemrosesan absensi (dipanggil AJAX)
├── status.php                 # AJAX label mode aktif
├── nokartu.php                # AJAX ambil UID pending (untuk registrasi)
├── kirimkartu.php             # DIPANGGIL ESP8266 saat tap kartu
├── ubahmode.php                # DIPANGGIL ESP8266 saat tombol ditekan
└── rekap.php                   # Rekap absensi harian
```

## Langkah Instalasi

### 1. Setup Server (XAMPP)
1. Copy seluruh folder `absensi_rfid` ke dalam `htdocs` XAMPP Anda
   (contoh: `C:\xampp\htdocs\absensi_rfid`)
2. Jalankan **Apache** dan **MySQL** dari XAMPP Control Panel
3. Buka `http://localhost/phpmyadmin`
4. Klik tab **Import**, pilih file `database/absen_rfid.sql`, klik **Go**
5. Pastikan database `absen_rfid` dengan 4 tabel (karyawan, tmprfid, status, absensi) sudah muncul

### 2. Cek Koneksi Database
Buka `config/koneksi.php`. Jika password MySQL Anda BUKAN kosong (default XAMPP
biasanya kosong), ubah baris `$pass = "";` sesuai password Anda.

### 3. Cari IP Komputer Anda
NodeMCU perlu tahu alamat IP komputer yang menjalankan XAMPP di jaringan WiFi yang sama.

- **Windows**: buka Command Prompt, ketik `ipconfig`, cari "IPv4 Address"
- **Mac/Linux**: buka Terminal, ketik `ifconfig` atau `ip addr`, cari alamat di adapter WiFi

Catat IP ini (contoh: `192.168.1.100`). **PENTING: NodeMCU dan komputer XAMPP
harus terhubung ke jaringan WiFi yang SAMA.**

### 4. Setup Firmware ESP8266
1. Buka `firmware/absensi_rfid.ino` di Arduino IDE
2. Install library berikut lewat Library Manager (Tools > Manage Libraries):
   - `MFRC522` by GithubCommunity
3. Daftarkan board ESP8266 (WAJIB dilakukan sebelum board ini muncul di Boards Manager):
   a. Buka File > Preferences
   b. Di kolom "Additional Boards Manager URLs", tempelkan:
      `https://arduino.esp8266.com/stable/package_esp8266com_index.json`
      (jika sudah ada URL lain, pisahkan dengan koma)
   c. Klik OK
   d. Buka Tools > Board > Boards Manager, cari "esp8266", klik Install
      (butuh koneksi internet, proses beberapa menit)
4. Pilih Board: Tools > Board > esp8266 > **NodeMCU 1.0 (ESP-12E Module)**
5. Edit 3 baris konfigurasi di bagian atas kode:
   ```cpp
   const char* ssid     = "NAMA_WIFI_ANDA";
   const char* password = "PASSWORD_WIFI_ANDA";
   const char* serverBaseUrl = "http://192.168.1.100/absensi_rfid";
   ```
   Ganti dengan SSID WiFi, password WiFi, dan IP dari langkah 3.
6. Upload ke NodeMCU (pastikan port COM yang benar terpilih)
7. Buka Serial Monitor (baud rate 115200) untuk melihat log

### 5. Uji Coba Sistem
Ikuti prosedur testing lengkap di bagian akhir chat (Test 1-8).

## Alur Penggunaan
1. Buka `http://localhost/absensi_rfid/tambah.php` di browser, tempelkan kartu
   baru, isi nama & alamat, simpan.
2. Buka `http://localhost/absensi_rfid/scan.php` di layar dekat alat.
3. Tekan push button untuk memilih mode (Masuk/Istirahat/Kembali/Pulang).
4. Tempelkan kartu karyawan yang sudah terdaftar untuk mencatat absensi.

---

## Update v2 (audit) — wajib dibaca

Perubahan: perbaikan bug debounce tombol di firmware, self-test saat boot, `ping.php`,
`diag.php` (halaman diagnostik), `logs/iot.log`, zona waktu Asia/Jakarta, polling aman
(`scan.php` berhenti saat tab tersembunyi), klaim UID anti-ganda di `bacakartu.php`.

Cara pakai: timpa seluruh file ke `htdocs/absensi_rfid`, isi ulang 3 baris konfigurasi
(ssid, password, serverBaseUrl) di firmware, upload, buka Serial Monitor 115200 dan
buka `http://<IP_LAPTOP>/absensi_rfid/diag.php` di browser.

Catatan hotspot HP: IP laptop BERUBAH tiap konek ulang -> serverBaseUrl harus diperbarui.
Laptop & NodeMCU harus di hotspot yang sama (subnet sama, mis. 10.209.11.x).
Windows Firewall: izinkan Apache HTTP Server untuk jaringan Private DAN Public.
