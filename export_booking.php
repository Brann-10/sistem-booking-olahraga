<?php
include 'config.php';
session_start();

// Proteksi login
if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}

// Header Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=rekap_booking_kegiatan.xls");

echo "<table border='1'>";
echo "<tr>
        <th>No</th>
        <th>Nama Pengunjung</th>
        <th>Kegiatan</th>
        <th>Jumlah Orang</th>
        <th>Tanggal Booking</th>
        <th>Waktu</th>
        <th>Status</th>
      </tr>";

$sql = "SELECT p.nama, k.nama_kegiatan, d.jumlah_orang,
               b.tanggal_booking, b.waktu_mulai, b.waktu_selesai,
               b.waktu_checkin, b.waktu_checkout
        FROM booking_kegiatan b
        JOIN pengunjung p ON b.id_pengunjung = p.id_pengunjung
        JOIN kegiatan k ON b.id_kegiatan = k.id_kegiatan
        JOIN detail_booking d ON b.id_booking = d.id_booking
        ORDER BY b.tanggal_booking DESC";

$result = mysqli_query($conn, $sql);
$no = 1;

while ($row = mysqli_fetch_assoc($result)) {
    if (!$row['waktu_checkin']) {
        $status = 'Belum Hadir';
    } elseif (!$row['waktu_checkout']) {
        $status = 'Sedang Berlangsung';
    } else {
        $status = 'Selesai';
    }

    echo "<tr>
            <td>{$no}</td>
            <td>{$row['nama']}</td>
            <td>{$row['nama_kegiatan']}</td>
            <td>{$row['jumlah_orang']}</td>
            <td>{$row['tanggal_booking']}</td>
            <td>{$row['waktu_mulai']} - {$row['waktu_selesai']}</td>
            <td>{$status}</td>
          </tr>";
    $no++;
}

echo "</table>";
