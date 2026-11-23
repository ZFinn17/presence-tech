<?php
/**
 * tambah.php
 * Apa: Form registrasi karyawan baru. Field "No. Kartu" terisi
 *      otomatis saat kartu RFID baru ditempelkan ke reader.
 * Bagaimana: JavaScript melakukan polling (setInterval) ke
 *      nokartu.php setiap 1 detik untuk mengecek apakah ada UID
 *      baru di tabel tmprfid, lalu mengisi field secara otomatis.
 */
include 'header.php';
?>

<h2 class="mb-4">Registrasi Karyawan Baru</h2>

<div class="alert alert-info">
  Tempelkan kartu RFID baru ke reader. Nomor kartu akan otomatis
  terisi di bawah ini dalam beberapa detik.
</div>

<form action="proses_tambah.php" method="POST" class="bg-white p-4 rounded shadow-sm">
  <div class="mb-3">
    <label class="form-label">No. Kartu (otomatis)</label>
    <input type="text" class="form-control" id="nokartu" name="nokartu" readonly required>
  </div>
  <div class="mb-3">
    <label class="form-label">Nama Karyawan</label>
    <input type="text" class="form-control" name="nama" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Alamat</label>
    <input type="text" class="form-control" name="alamat" required>
  </div>
  <button type="submit" class="btn btn-success">Simpan Karyawan</button>
  <a href="datakaryawan.php" class="btn btn-secondary">Batal</a>
</form>

<script>
// Polling otomatis ke nokartu.php setiap 1 detik
setInterval(function () {
    $.get('nokartu.php', function (data) {
        if (data.trim() !== '') {
            $('#nokartu').val(data.trim());
        }
    });
}, 1000);
</script>

<?php include 'footer.php'; ?>
