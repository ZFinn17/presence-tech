<?php
/**
 * rekap.php
 * Apa: Menampilkan riwayat absensi seluruh karyawan.
 * Bisa difilter berdasarkan tanggal lewat query string ?tgl=YYYY-MM-DD
 */
require 'config/koneksi.php';
include 'header.php';

$tglFilter = isset($_GET['tgl']) && $_GET['tgl'] !== '' ? $_GET['tgl'] : date("Y-m-d");

$stmt = mysqli_prepare($conn, "
    SELECT a.*, k.nama
    FROM absensi a
    JOIN karyawan k ON k.nokartu = a.nokartu
    WHERE a.tgl = ?
    ORDER BY a.jam_masuk ASC
");
mysqli_stmt_bind_param($stmt, "s", $tglFilter);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<h2 class="mb-4">Rekap Absensi</h2>

<form method="GET" class="row g-2 mb-4">
  <div class="col-auto">
    <input type="date" name="tgl" class="form-control" value="<?= htmlspecialchars($tglFilter) ?>">
  </div>
  <div class="col-auto">
    <button type="submit" class="btn btn-primary">Filter Tanggal</button>
  </div>
</form>

<table class="table table-bordered table-striped bg-white">
  <thead class="table-dark">
    <tr>
      <th>Nama</th>
      <th>Tanggal</th>
      <th>Jam Masuk</th>
      <th>Jam Istirahat</th>
      <th>Jam Kembali</th>
      <th>Jam Pulang</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
      <td><?= htmlspecialchars($row['nama']) ?></td>
      <td><?= htmlspecialchars($row['tgl']) ?></td>
      <td><?= $row['jam_masuk'] ?? '-' ?></td>
      <td><?= $row['jam_istirahat'] ?? '-' ?></td>
      <td><?= $row['jam_kembali'] ?? '-' ?></td>
      <td><?= $row['jam_pulang'] ?? '-' ?></td>
    </tr>
    <?php endwhile; ?>
    <?php if (mysqli_num_rows($result) === 0): ?>
    <tr><td colspan="6" class="text-center text-muted">Tidak ada data absensi pada tanggal ini</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php include 'footer.php'; ?>
