<?php
require 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id      = (int) $_POST['id'];
    $nama    = trim($_POST['nama']);
    $alamat  = trim($_POST['alamat']);

    $stmt = mysqli_prepare($conn, "UPDATE karyawan SET nama = ?, alamat = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "ssi", $nama, $alamat, $id);
    mysqli_stmt_execute($stmt);

    header("Location: datakaryawan.php");
    exit;
}
?>
