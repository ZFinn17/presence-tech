<?php
/**
 * kirimkartu.php
 * Dipanggil ESP8266 (HTTP GET) setiap kali kartu RFID terbaca.
 *   http://<IP_SERVER>/absensi_rfid/kirimkartu.php?nokartu=<UID>
 * Menyimpan UID ke tabel tmprfid, mencatat ke logs/iot.log, membalas "OK".
 */
require 'config/koneksi.php';
require 'config/log.php';

if (isset($_GET['nokartu']) && trim($_GET['nokartu']) !== '') {
    $nokartu = trim($_GET['nokartu']);

    $stmt = mysqli_prepare($conn, "DELETE FROM tmprfid");
    mysqli_stmt_execute($stmt);

    $stmt = mysqli_prepare($conn, "INSERT INTO tmprfid (nokartu) VALUES (?)");
    mysqli_stmt_bind_param($stmt, "s", $nokartu);
    mysqli_stmt_execute($stmt);

    iot_log("kirimkartu nokartu=" . $nokartu);
    echo "OK";
} else {
    iot_log("kirimkartu GAGAL: parameter nokartu kosong");
    echo "ERROR: parameter nokartu kosong";
}
?>
