<?php
/**
 * proses_tambah.php
 * Apa: Menyimpan data karyawan baru ke database.
 * Menggunakan prepared statement untuk mencegah SQL Injection.
 */
require 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nokartu = trim($_POST['nokartu']);
    $nama    = trim($_POST['nama']);
    $alamat  = trim($_POST['alamat']);

    if ($nokartu === '' || $nama === '' || $alamat === '') {
        die("Semua field wajib diisi. <a href='tambah.php'>Kembali</a>");
    }

    // Cek apakah nokartu sudah terdaftar sebelumnya
    $cek = mysqli_prepare($conn, "SELECT id FROM karyawan WHERE nokartu = ?");
    mysqli_stmt_bind_param($cek, "s", $nokartu);
    mysqli_stmt_execute($cek);
    $hasilCek = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($hasilCek) > 0) {
        die("Kartu ini sudah terdaftar atas nama karyawan lain. <a href='tambah.php'>Kembali</a>");
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO karyawan (nokartu, nama, alamat) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $nokartu, $nama, $alamat);
    mysqli_stmt_execute($stmt);

    // Bersihkan tmprfid supaya kartu yang sama tidak otomatis
    // terisi lagi jika admin membuka form tambah lain kali
    mysqli_query($conn, "DELETE FROM tmprfid");

    header("Location: datakaryawan.php");
    exit;
}
?>
