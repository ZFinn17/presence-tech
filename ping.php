<?php
/**
 * ping.php
 * Dipanggil NodeMCU saat boot untuk menguji: jaringan -> Apache -> PHP -> MySQL.
 */
require 'config/koneksi.php';
require 'config/log.php';

$row  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT mode FROM status LIMIT 1"));
$mode = $row ? (int) $row['mode'] : 0;

iot_log("ping (NodeMCU boot test)");
echo "PONG db=OK mode=" . $mode;
?>
