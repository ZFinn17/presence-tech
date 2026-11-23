<?php
/**
 * datakaryawan.php
 * Apa: Menampilkan seluruh data karyawan yang terdaftar.
 * Mengapa: Admin butuh melihat, mengedit, dan menghapus data karyawan.
 */
require 'config/koneksi.php';
include 'header.php';

$result = mysqli_query($conn, "SELECT * FROM karyawan ORDER BY nama ASC");
?>

<h2 class="mb-4">Data Karyawan</h2>
<a href="tambah.php" class="btn btn-success mb-3">+ Tambah Karyawan</a>

<table class="table table-bordered table-striped bg-white">
  <thead class="table-dark">
    <tr>
      <th>No</th>
      <th>No. Kartu</th>
      <th>Nama</th>
      <th>Alamat</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
      <td><?= $no++ ?></td>
      <td><?= htmlspecialchars($row['nokartu']) ?></td>
      <td><?= htmlspecialchars($row['nama']) ?></td>
      <td><?= htmlspecialchars($row['alamat']) ?></td>
      <td>
        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
        <a href="hapus.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger"
           onclick="return confirm('Yakin ingin menghapus <?= htmlspecialchars($row['nama']) ?>?')">Hapus</a>
      </td>
    </tr>
    <?php endwhile; ?>
    <?php if (mysqli_num_rows($result) === 0): ?>
    <tr><td colspan="5" class="text-center text-muted">Belum ada data karyawan</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<?php include 'footer.php'; ?>
