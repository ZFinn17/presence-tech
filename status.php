<?php
/**
 * status.php
 * Apa: Endpoint AJAX (dipanggil dari browser, bukan dari ESP8266)
 *      untuk menampilkan label mode absensi yang sedang aktif.
 */
require 'config/koneksi.php';

$result = mysqli_query($conn, "SELECT mode FROM status LIMIT 1");
$row = mysqli_fetch_assoc($result);
$mode = (int) $row['mode'];

$label = [
    1 => "Masuk",
    2 => "Istirahat",
    3 => "Kembali",
    4 => "Pulang"
];

echo $label[$mode] ?? "Tidak diketahui";
?>
