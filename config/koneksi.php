<?php
/**
 * config/koneksi.php
 * Koneksi database + pengaturan zona waktu.
 * v2: ditambah date_default_timezone_set agar jam absensi sesuai WIB.
 */

date_default_timezone_set('Asia/Jakarta');

$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "absen_rfid";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
?>
