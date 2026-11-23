<?php
/**
 * ubahmode.php
 * Dipanggil ESP8266 saat push button ditekan.
 * Mode naik 1->2->3->4->1, disimpan di tabel status, dibalas sebagai angka.
 */
require 'config/koneksi.php';
require 'config/log.php';

$result = mysqli_query($conn, "SELECT mode FROM status LIMIT 1");
$row = mysqli_fetch_assoc($result);
$mode = (int) $row['mode'];

$mode++;
if ($mode > 4) {
    $mode = 1;
}

$stmt = mysqli_prepare($conn, "UPDATE status SET mode = ?");
mysqli_stmt_bind_param($stmt, "i", $mode);
mysqli_stmt_execute($stmt);

iot_log("ubahmode -> mode sekarang = " . $mode);
echo $mode;
?>
