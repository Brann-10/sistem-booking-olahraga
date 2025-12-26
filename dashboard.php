<?php
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}
include 'config.php';
include 'header.php';



// Total Pengunjung
$qPengunjung = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pengunjung");
$pengunjung = mysqli_fetch_assoc($qPengunjung);

// Total Kegiatan
$qKegiatan = mysqli_query($conn, "SELECT COUNT(*) AS total FROM kegiatan");
$kegiatan = mysqli_fetch_assoc($qKegiatan);

// Total Booking
$qBooking = mysqli_query($conn, "SELECT COUNT(*) AS total FROM booking_kegiatan");
$booking = mysqli_fetch_assoc($qBooking);

// Booking Aktif
$qAktif = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM booking_kegiatan 
     WHERE waktu_checkin IS NOT NULL 
     AND waktu_checkout IS NULL"
);

// Top Kegiatan Terfavorit
$qTop = mysqli_query(
    $conn,
    "SELECT k.nama_kegiatan, SUM(d.jumlah_orang) AS total_orang
     FROM booking_kegiatan b
     JOIN detail_booking d ON b.id_booking = d.id_booking
     JOIN kegiatan k ON b.id_kegiatan = k.id_kegiatan
     GROUP BY k.id_kegiatan, k.nama_kegiatan
     ORDER BY total_orang DESC
     LIMIT 5"
);

$aktif = mysqli_fetch_assoc($qAktif);
?>

<div class="row">

<div class="card mt-4 shadow">
  <div class="card-header bg-secondary text-white">
    <h5 class="mb-0">Top 5 Kegiatan Terfavorit</h5>
  </div>
  <div class="card-body">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>No</th>
          <th>Nama Kegiatan</th>
          <th>Total Pengunjung</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; while ($row = mysqli_fetch_assoc($qTop)): ?>
        <tr>
          <td><?= $no++; ?></td>
          <td><?= htmlspecialchars($row['nama_kegiatan']); ?></td>
          <td><?= $row['total_orang']; ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

  <div class="col-md-3">
    <div class="card text-bg-primary shadow text-center">
      <div class="card-body">
        <h6>Total Pengunjung</h6>
        <h2><?= $pengunjung['total']; ?></h2>
      </div>
    </div>
  </div>
  
  <div class="col-md-3">
    <div class="card text-bg-success shadow text-center">
      <div class="card-body">
        <h6>Total Kegiatan</h6>
        <h2><?= $kegiatan['total']; ?></h2>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card text-bg-warning shadow text-center">
      <div class="card-body">
        <h6>Total Booking</h6>
        <h2><?= $booking['total']; ?></h2>
      </div>
    </div>
  </div>

  <div class="col-md-3">
    <div class="card text-bg-danger shadow text-center">
      <div class="card-body">
        <h6>Booking Aktif</h6>
        <h2><?= $aktif['total']; ?></h2>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
