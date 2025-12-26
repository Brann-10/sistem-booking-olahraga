<?php
// laporan.php - Halaman Laporan
include 'config.php';
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}

include 'header.php';

// Query Laporan Booking per Pengunjung
$sql_pengunjung = "SELECT p.id_pengunjung, p.nama, COUNT(b.id_booking) AS total_booking, SUM(d.jumlah_orang) AS total_orang
              FROM pengunjung p
              JOIN booking_kegiatan b ON p.id_pengunjung = b.id_pengunjung
              JOIN detail_booking d ON b.id_booking = d.id_booking
              GROUP BY p.id_pengunjung, p.nama
              ORDER BY total_booking DESC";
$res_pengunjung = mysqli_query($conn, $sql_pengunjung);

// Query Laporan Kegiatan Paling Sering Dikunjungi
$sql_kegiatan = "SELECT k.id_kegiatan, k.nama_kegiatan, k.pelatih, SUM(d.jumlah_orang) AS total_dikunjungi
             FROM kegiatan k
             JOIN booking_kegiatan b ON k.id_kegiatan = b.id_kegiatan
             JOIN detail_booking d ON b.id_booking = d.id_booking
             GROUP BY k.id_kegiatan, k.nama_kegiatan, k.pelatih
             HAVING total_dikunjungi > 0
             ORDER BY total_dikunjungi DESC
             LIMIT 10";
$res_kegiatan = mysqli_query($conn, $sql_kegiatan);
?>

<div class="container mt-5">
  <h1 class="mb-4">Laporan Kegiatan Olahraga</h1>
  <div class="row">

  <a href="export_booking.php" class="btn btn-success mb-4">
  <i class="bi bi-file-earmark-excel"></i> Download Rekap Booking (Excel)
</a>

    <!-- Laporan per Pengunjung -->
    <div class="col-md-6">
      <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">Laporan Booking per Pengunjung</h5>
        </div>
        <div class="card-body">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Nama Pengunjung</th>
                <th>Total Booking</th>
                <th>Total Orang</th>
              </tr>
            </thead>
            <tbody>
              <?php $i=1; while($row = mysqli_fetch_assoc($res_pengunjung)): ?>
              <tr>
                <td><?= $i++; ?></td>
                <td><?= htmlspecialchars($row['nama']); ?></td>
                <td><?= $row['total_booking']; ?></td>
                <td><?= $row['total_orang']; ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <!-- Laporan Kegiatan Terpopuler -->
    <div class="col-md-6">
      <div class="card mb-4 shadow-sm">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0">Kegiatan Paling Sering Dikunjungi</h5>
        </div>
        <div class="card-body">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Nama Kegiatan</th>
                <th>Pelatih</th>
                <th>Total Pengunjung</th>
              </tr>
            </thead>
            <tbody>
              <?php $j=1; while($row = mysqli_fetch_assoc($res_kegiatan)): ?>
              <tr>
                <td><?= $j++; ?></td>
                <td><?= htmlspecialchars($row['nama_kegiatan']); ?></td>
                <td><?= htmlspecialchars($row['pelatih']); ?></td>
                <td><?= $row['total_dikunjungi']; ?></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
