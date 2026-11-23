<?php
/**
 * index.php
 * Apa: Halaman utama / dashboard sistem.
 * Mengapa: Titik masuk pertama, menampilkan ringkasan singkat
 *          supaya admin langsung tahu kondisi sistem hari ini.
 */
require 'config/koneksi.php';
include 'header.php';

// Hitung jumlah karyawan terdaftar
$totalKaryawan = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM karyawan")
)['total'];

// Hitung jumlah yang sudah absen masuk hari ini
$tglHariIni = date("Y-m-d");
$totalHadir = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM absensi WHERE tgl = '$tglHariIni' AND jam_masuk IS NOT NULL")
)['total'];

// Ambil mode aktif saat ini
$modeRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT mode FROM status LIMIT 1"));
$labelMode = [1 => "Masuk", 2 => "Istirahat", 3 => "Kembali", 4 => "Pulang"];
$modeAktif = $labelMode[$modeRow['mode']] ?? "Tidak diketahui";
?>

<h2 class="mb-4">Dashboard Absensi RFID</h2>

<div class="row g-3">
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="text-muted">Total Karyawan Terdaftar</h6>
        <h2><?= $totalKaryawan ?></h2>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="text-muted">Hadir Hari Ini</h6>
        <h2><?= $totalHadir ?></h2>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h6 class="text-muted">Mode Absensi Aktif</h6>
        <h2><span id="modeAktif" class="badge bg-primary"><?= $modeAktif ?></span></h2>
      </div>
    </div>
  </div>
</div>

<div class="mt-4">
  <a href="tambah.php" class="btn btn-success">+ Registrasi Karyawan Baru</a>
  <a href="scan.php" class="btn btn-primary">Buka Halaman Scan Absensi</a>
</div>

<script>
setInterval(function () {
    $.ajax({url: 'status.php', cache: false, success: function (d) { $('#modeAktif').text(d); }});
}, 1500);
</script>

<?php include 'footer.php'; ?>
