<?php
/**
 * nokartu.php
 * Apa: Endpoint AJAX yang dipanggil dari halaman tambah.php untuk
 *      mengecek apakah ada UID kartu baru yang sedang menunggu di
 *      tabel tmprfid (dipakai khusus untuk alur REGISTRASI, bukan
 *      untuk absensi).
 */
require 'config/koneksi.php';

$result = mysqli_query($conn, "SELECT nokartu FROM tmprfid LIMIT 1");

if ($row = mysqli_fetch_assoc($result)) {
    echo $row['nokartu'];
} else {
    echo "";
}
?>
