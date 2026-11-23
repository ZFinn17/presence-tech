<?php
/**
 * scan.php (v2)
 * Halaman scan absensi. Polling status.php (mode) dan bacakartu.php (kartu).
 * v2: polling dihentikan saat tab tidak terlihat (document.hidden) agar tidak
 *     "memakan" UID yang sedang dipakai untuk registrasi di tab lain.
 */
include 'header.php';
?>

<h2 class="mb-4">Scan Absensi</h2>

<div class="card shadow-sm mb-4">
  <div class="card-body text-center">
    <h6 class="text-muted">Mode Absensi Aktif</h6>
    <h1><span id="modeAktif" class="badge bg-primary">Memuat...</span></h1>
    <small class="text-muted">Tekan push button pada alat untuk mengganti mode</small>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body text-center" style="min-height: 120px;">
    <h6 class="text-muted">Status Terakhir</h6>
    <h3 id="pesanAbsen" class="text-secondary">Menunggu kartu di-tap...</h3>
  </div>
</div>

<p class="text-muted small mt-3">
  Catatan: biarkan halaman ini TERBUKA dan terlihat selama absensi berlangsung.
  Jangan buka halaman ini bersamaan dengan halaman Registrasi.
</p>

<script>
setInterval(function () {
    $.ajax({url: 'status.php', cache: false, success: function (data) {
        $('#modeAktif').text(data);
    }});
}, 1500);

setInterval(function () {
    if (document.hidden) { return; }   // jangan memproses kartu saat tab tersembunyi
    $.ajax({url: 'bacakartu.php', cache: false, success: function (data) {
        if (data.trim() !== '') {
            $('#pesanAbsen').text(data);
            if (data.indexOf('tidak dikenali') !== -1 || data.indexOf('belum absen') !== -1) {
                $('#pesanAbsen').removeClass('text-success').addClass('text-danger');
            } else {
                $('#pesanAbsen').removeClass('text-danger').addClass('text-success');
            }
        }
    }});
}, 1500);
</script>

<?php include 'footer.php'; ?>
