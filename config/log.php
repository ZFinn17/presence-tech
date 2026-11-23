<?php
/**
 * config/log.php
 * Mencatat setiap request dari NodeMCU ke logs/iot.log supaya kita bisa
 * membuktikan apakah request benar-benar sampai ke server.
 */
function iot_log($pesan) {
    $dir = __DIR__ . '/../logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    $ip    = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '-';
    $baris = date('Y-m-d H:i:s') . ' | dari ' . $ip . ' | ' . $pesan . "\n";
    @file_put_contents($dir . '/iot.log', $baris, FILE_APPEND | LOCK_EX);
}
?>
