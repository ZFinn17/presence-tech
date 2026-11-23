-- ============================================================
-- Database: absen_rfid
-- Sistem Absensi Karyawan Berbasis RFID (RC522 + NodeMCU ESP8266)
-- ============================================================

CREATE DATABASE IF NOT EXISTS absen_rfid;
USE absen_rfid;

-- ------------------------------------------------------------
-- Tabel: karyawan
-- Menyimpan data karyawan yang terdaftar beserta nomor kartu RFID
-- Catatan: kolom nokartu dibuat UNIQUE (perubahan dari referensi asli)
-- Alasan: mencegah 1 kartu fisik terdaftar dobel ke 2 nama karyawan
-- berbeda, yang bisa menyebabkan data absensi tertukar/ambigu.
-- ------------------------------------------------------------
CREATE TABLE karyawan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nokartu VARCHAR(20) NOT NULL UNIQUE,
  nama VARCHAR(50) NOT NULL,
  alamat VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Tabel: tmprfid
-- Penampung sementara UID kartu yang baru saja di-tap.
-- Hanya berisi 0 atau 1 baris pada satu waktu (selalu dikosongkan
-- setelah UID diproses, baik untuk registrasi maupun absensi).
-- ------------------------------------------------------------
CREATE TABLE tmprfid (
  nokartu VARCHAR(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Tabel: status
-- Menyimpan mode absensi yang sedang aktif (hanya 1 baris).
-- 1 = Masuk, 2 = Istirahat, 3 = Kembali, 4 = Pulang
-- ------------------------------------------------------------
CREATE TABLE status (
  mode INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO status (mode) VALUES (1);

-- ------------------------------------------------------------
-- Tabel: absensi
-- Log absensi harian per karyawan.
-- Satu baris = satu karyawan untuk satu tanggal.
-- ------------------------------------------------------------
CREATE TABLE absensi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nokartu VARCHAR(20) NOT NULL,
  tgl DATE NOT NULL,
  jam_masuk TIME NULL,
  jam_istirahat TIME NULL,
  jam_kembali TIME NULL,
  jam_pulang TIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
