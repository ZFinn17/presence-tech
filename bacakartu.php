<?php
/**
 * bacakartu.php  (v2)
 * Logika inti absensi, dipanggil scan.php lewat AJAX.
 * Perubahan v2: UID "diklaim" dulu (DELETE + cek affected_rows) sebelum
 * diproses, sehingga tidak mungkin diproses dua kali oleh dua polling yang
 * kebetulan berjalan bersamaan (mencegah data absensi ganda).
 */
require 'config/koneksi.php';
require 'config/log.php';

$result = mysqli_query($conn, "SELECT nokartu FROM tmprfid LIMIT 1");
if (!$result || mysqli_num_rows($result) == 0) {
    echo "";
    exit;
}

$row = mysqli_fetch_assoc($result);
$nokartu = $row['nokartu'];

// Klaim UID: hanya satu proses yang berhasil menghapus baris ini
$stmt = mysqli_prepare($conn, "DELETE FROM tmprfid WHERE nokartu = ?");
mysqli_stmt_bind_param($stmt, "s", $nokartu);
mysqli_stmt_execute($stmt);
if (mysqli_stmt_affected_rows($stmt) < 1) {
    echo "";
    exit;
}

// Cek kartu terdaftar
$stmt = mysqli_prepare($conn, "SELECT * FROM karyawan WHERE nokartu = ?");
mysqli_stmt_bind_param($stmt, "s", $nokartu);
mysqli_stmt_execute($stmt);
$cek = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($cek) == 0) {
    iot_log("bacakartu: kartu $nokartu TIDAK terdaftar");
    echo "Maaf kartu tidak dikenali (UID: $nokartu)";
    exit;
}

$karyawan = mysqli_fetch_assoc($cek);

// Mode aktif
$statusRow = mysqli_fetch_assoc(mysqli_query($conn, "SELECT mode FROM status LIMIT 1"));
$mode = (int) $statusRow['mode'];

$tgl = date("Y-m-d");
$jam = date("H:i:s");

$stmt = mysqli_prepare($conn, "SELECT * FROM absensi WHERE nokartu = ? AND tgl = ?");
mysqli_stmt_bind_param($stmt, "ss", $nokartu, $tgl);
mysqli_stmt_execute($stmt);
$cekAbsen = mysqli_stmt_get_result($stmt);
$sudahAdaRecord = mysqli_num_rows($cekAbsen) > 0;

$pesan = "";

switch ($mode) {
    case 1: // Masuk
        if (!$sudahAdaRecord) {
            $stmt = mysqli_prepare($conn, "INSERT INTO absensi (nokartu, tgl, jam_masuk) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sss", $nokartu, $tgl, $jam);
            mysqli_stmt_execute($stmt);
            $pesan = "Selamat Datang, " . $karyawan['nama'];
        } else {
            $pesan = $karyawan['nama'] . " sudah tercatat masuk hari ini";
        }
        break;

    case 2: // Istirahat
        if ($sudahAdaRecord) {
            $stmt = mysqli_prepare($conn, "UPDATE absensi SET jam_istirahat = ? WHERE nokartu = ? AND tgl = ?");
            mysqli_stmt_bind_param($stmt, "sss", $jam, $nokartu, $tgl);
            mysqli_stmt_execute($stmt);
            $pesan = "Selamat Istirahat, " . $karyawan['nama'];
        } else {
            $pesan = $karyawan['nama'] . " belum absen masuk hari ini";
        }
        break;

    case 3: // Kembali
        if ($sudahAdaRecord) {
            $stmt = mysqli_prepare($conn, "UPDATE absensi SET jam_kembali = ? WHERE nokartu = ? AND tgl = ?");
            mysqli_stmt_bind_param($stmt, "sss", $jam, $nokartu, $tgl);
            mysqli_stmt_execute($stmt);
            $pesan = "Selamat Kembali Bekerja, " . $karyawan['nama'];
        } else {
            $pesan = $karyawan['nama'] . " belum absen masuk hari ini";
        }
        break;

    case 4: // Pulang
        if ($sudahAdaRecord) {
            $stmt = mysqli_prepare($conn, "UPDATE absensi SET jam_pulang = ? WHERE nokartu = ? AND tgl = ?");
            mysqli_stmt_bind_param($stmt, "sss", $jam, $nokartu, $tgl);
            mysqli_stmt_execute($stmt);
            $pesan = "Selamat Jalan, " . $karyawan['nama'];
        } else {
            $pesan = $karyawan['nama'] . " belum absen masuk hari ini";
        }
        break;

    default:
        $pesan = "Mode tidak dikenali";
}

iot_log("bacakartu: $nokartu (" . $karyawan['nama'] . ") mode=$mode -> $pesan");
echo $pesan;
?>
