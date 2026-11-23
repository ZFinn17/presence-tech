<?php
/**
 * edit.php
 * Apa: Form edit data karyawan (nama & alamat).
 * Catatan: nokartu dibuat readonly (tidak bisa diubah lewat form
 * ini) karena mengubah nokartu setelah terdaftar berisiko memutus
 * riwayat absensi yang sudah tercatat atas nomor kartu tersebut.
 */
require 'config/koneksi.php';
include 'header.php';

if (!isset($_GET['id'])) {
    die("ID karyawan tidak ditemukan.");
}

$id = (int) $_GET['id'];
$stmt = mysqli_prepare($conn, "SELECT * FROM karyawan WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$karyawan = mysqli_fetch_assoc($result);

if (!$karyawan) {
    die("Data karyawan tidak ditemukan.");
}
?>

<h2 class="mb-4">Edit Data Karyawan</h2>

<form action="proses_edit.php" method="POST" class="bg-white p-4 rounded shadow-sm">
  <input type="hidden" name="id" value="<?= $karyawan['id'] ?>">
  <div class="mb-3">
    <label class="form-label">No. Kartu</label>
    <input type="text" class="form-control" value="<?= htmlspecialchars($karyawan['nokartu']) ?>" readonly>
  </div>
  <div class="mb-3">
    <label class="form-label">Nama Karyawan</label>
    <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($karyawan['nama']) ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Alamat</label>
    <input type="text" class="form-control" name="alamat" value="<?= htmlspecialchars($karyawan['alamat']) ?>" required>
  </div>
  <button type="submit" class="btn btn-warning">Simpan Perubahan</button>
  <a href="datakaryawan.php" class="btn btn-secondary">Batal</a>
</form>

<?php include 'footer.php'; ?>
