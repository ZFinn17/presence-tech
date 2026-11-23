<?php
/**
 * diag.php - Halaman diagnostik.
 * Menampilkan kondisi server, database, mode, tmprfid, dan log request dari
 * NodeMCU. Buka lewat http://<IP_LAPTOP>/absensi_rfid/diag.php
 */
require 'config/koneksi.php';
include 'header.php';

$mode = (int) mysqli_fetch_assoc(mysqli_query($conn, "SELECT mode FROM status LIMIT 1"))['mode'];
$labelMode = [1 => "Masuk", 2 => "Istirahat", 3 => "Kembali", 4 => "Pulang"];

$tmp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nokartu FROM tmprfid LIMIT 1"));
$jmlKaryawan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM karyawan"))['n'];
$tgl = date("Y-m-d");
$jmlAbsen = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS n FROM absensi WHERE tgl = '$tgl'"))['n'];

$logFile = __DIR__ . '/logs/iot.log';
$logBaris = file_exists($logFile) ? array_slice(file($logFile), -30) : [];
$host = $_SERVER['HTTP_HOST'];
?>

<h2 class="mb-3">Diagnostik Sistem</h2>

<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="card shadow-sm"><div class="card-body">
      <h6 class="text-muted">Server</h6>
      <p class="mb-1">Waktu server: <b><?= date("Y-m-d H:i:s") ?></b> (<?= date_default_timezone_get() ?>)</p>
      <p class="mb-1">Halaman ini dibuka lewat: <b><?= htmlspecialchars($host) ?></b></p>
      <p class="mb-1">Database: <span class="badge bg-success">TERHUBUNG</span></p>
      <p class="mb-0 small text-muted">Nilai serverBaseUrl di firmware harus:
        <code>http://<?= htmlspecialchars(preg_replace('/:.*/', '', $host)) ?>/absensi_rfid</code>
        (jika alamat di atas masih "localhost", buka ulang halaman ini lewat IP dari <code>ipconfig</code>).</p>
    </div></div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm"><div class="card-body">
      <h6 class="text-muted">Data sistem</h6>
      <p class="mb-1">Mode aktif: <b><?= $mode ?> (<?= $labelMode[$mode] ?? '?' ?>)</b></p>
      <p class="mb-1">UID menunggu di tmprfid: <b><?= $tmp ? htmlspecialchars($tmp['nokartu']) : '(kosong)' ?></b></p>
      <p class="mb-1">Karyawan terdaftar: <b><?= $jmlKaryawan ?></b></p>
      <p class="mb-0">Absensi hari ini: <b><?= $jmlAbsen ?></b></p>
    </div></div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <h6 class="text-muted">30 request terakhir dari NodeMCU (logs/iot.log) — refresh otomatis 3 detik</h6>
    <pre class="mb-0 small" style="min-height:160px;"><?php
      if (count($logBaris) === 0) {
          echo "(belum ada request masuk dari NodeMCU)";
      } else {
          foreach (array_reverse($logBaris) as $b) { echo htmlspecialchars($b); }
      }
    ?></pre>
  </div>
</div>

<script>setTimeout(function () { location.reload(); }, 3000);</script>

<?php include 'footer.php'; ?>
