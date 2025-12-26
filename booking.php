<?php
include 'config.php';
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}

include 'header.php';

/* ================= FLASH MESSAGE ================= */
if (!function_exists('flashMessage')) {
    function flashMessage($type, $msg) {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }
}

/* ================= TAMBAH BOOKING ================= */
if (isset($_POST['booking'])) {
    $pengunjung = (int)$_POST['pengunjung_id'];
    $kegiatan   = (int)$_POST['kegiatan_id'];
    $tanggal    = $_POST['tanggal_booking'];
    $jumlah     = (int)$_POST['jumlah_orang'];

    mysqli_begin_transaction($conn);
    try {
        // Ambil kapasitas
        $kap = mysqli_query($conn, "SELECT batas_pengunjung FROM kegiatan WHERE id_kegiatan=$kegiatan FOR UPDATE");
        $kapasitas = mysqli_fetch_assoc($kap)['batas_pengunjung'];

        // Hitung yang sedang aktif
        $pakai = mysqli_query($conn,"
            SELECT COALESCE(SUM(d.jumlah_orang),0) total
            FROM booking_kegiatan b
            JOIN detail_booking d ON b.id_booking=d.id_booking
            WHERE b.id_kegiatan=$kegiatan AND b.waktu_checkout IS NULL
        ");
        $terpakai = mysqli_fetch_assoc($pakai)['total'];

        if ($jumlah > ($kapasitas - $terpakai)) {
            throw new Exception('Kapasitas tidak mencukupi');
        }

        // Insert booking
        $q1 = "INSERT INTO booking_kegiatan
              (id_pengunjung,id_kegiatan,tanggal_booking,waktu_mulai,waktu_selesai)
              SELECT ?,?,?,waktu_mulai,waktu_selesai FROM kegiatan WHERE id_kegiatan=?";
        $s1 = mysqli_prepare($conn,$q1);
        mysqli_stmt_bind_param($s1,'iisi',$pengunjung,$kegiatan,$tanggal,$kegiatan);
        mysqli_stmt_execute($s1);
        $idBooking = mysqli_insert_id($conn);

        // Detail booking
        $q2 = "INSERT INTO detail_booking (id_booking,jumlah_orang) VALUES (?,?)";
        $s2 = mysqli_prepare($conn,$q2);
        mysqli_stmt_bind_param($s2,'ii',$idBooking,$jumlah);
        mysqli_stmt_execute($s2);

        mysqli_commit($conn);
        flashMessage('success','Booking berhasil dibuat');
    } catch (Exception $e) {
        mysqli_rollback($conn);
        flashMessage('danger',$e->getMessage());
    }
    header('Location: booking.php'); exit;
}

/* ================= CHECK-IN ================= */
if (isset($_GET['checkin'])) {
    $id=(int)$_GET['checkin'];
    mysqli_query($conn,"UPDATE booking_kegiatan 
        SET waktu_checkin=NOW(), status_kehadiran='hadir'
        WHERE id_booking=$id AND waktu_checkin IS NULL");
    flashMessage('success','Check-in berhasil');
    header('Location: booking.php'); exit;
}

/* ================= CHECK-OUT ================= */
if (isset($_GET['checkout'])) {
    $id=(int)$_GET['checkout'];
    mysqli_query($conn,"UPDATE booking_kegiatan 
        SET waktu_checkout=NOW()
        WHERE id_booking=$id AND waktu_checkout IS NULL");
    flashMessage('success','Check-out berhasil');
    header('Location: booking.php'); exit;
}

/* ================= DATA ================= */
$pengunjung = mysqli_query($conn,"SELECT id_pengunjung,nama FROM pengunjung ORDER BY nama");
$kegiatan   = mysqli_query($conn,"SELECT id_kegiatan,nama_kegiatan,batas_pengunjung FROM kegiatan WHERE status='aktif'");

$data = mysqli_query($conn,"
    SELECT b.id_booking,p.nama,k.nama_kegiatan,d.jumlah_orang,
           b.tanggal_booking,b.waktu_mulai,b.waktu_selesai,
           b.waktu_checkin,b.waktu_checkout
    FROM booking_kegiatan b
    JOIN pengunjung p ON b.id_pengunjung=p.id_pengunjung
    JOIN kegiatan k ON b.id_kegiatan=k.id_kegiatan
    JOIN detail_booking d ON b.id_booking=d.id_booking
    ORDER BY b.tanggal_booking DESC
");
?>

<div class="card shadow">
  <div class="card-header d-flex justify-content-between">
    <h4>Data Booking Kegiatan</h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookingModal">
      + Booking Baru
    </button>
  </div>

  <div class="card-body">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>#</th><th>Pengunjung</th><th>Kegiatan</th>
          <th>Jumlah</th><th>Tanggal</th><th>Waktu</th>
          <th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php $no=1; while($r=mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($r['nama']) ?></td>
          <td><?= htmlspecialchars($r['nama_kegiatan']) ?></td>
          <td><?= $r['jumlah_orang'] ?></td>
          <td><?= $r['tanggal_booking'] ?></td>
          <td><?= $r['waktu_mulai'].' - '.$r['waktu_selesai'] ?></td>
          <td>
            <?php
              if(!$r['waktu_checkin']) echo '<span class="badge bg-warning">Belum Hadir</span>';
              elseif(!$r['waktu_checkout']) echo '<span class="badge bg-success">Sedang Berlangsung</span>';
              else echo '<span class="badge bg-secondary">Selesai</span>';
            ?>
          </td>
          <td>
            <?php if(!$r['waktu_checkin']): ?>
              <a href="?checkin=<?= $r['id_booking'] ?>" class="btn btn-sm btn-success">Check-in</a>
            <?php elseif(!$r['waktu_checkout']): ?>
              <a href="?checkout=<?= $r['id_booking'] ?>" class="btn btn-sm btn-info">Check-out</a>
            <?php else: ?> -
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ================= MODAL BOOKING ================= -->
<div class="modal fade" id="bookingModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Booking Kegiatan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Pengunjung</label>
            <select name="pengunjung_id" class="form-control" required>
              <option value="">-- Pilih --</option>
              <?php while($p=mysqli_fetch_assoc($pengunjung)): ?>
                <option value="<?= $p['id_pengunjung'] ?>">
                  <?= htmlspecialchars($p['nama']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3">
            <label>Kegiatan</label>
            <select name="kegiatan_id" class="form-control" required>
              <option value="">-- Pilih --</option>
              <?php while($k=mysqli_fetch_assoc($kegiatan)): ?>
                <option value="<?= $k['id_kegiatan'] ?>">
                  <?= htmlspecialchars($k['nama_kegiatan']) ?> (<?= $k['batas_pengunjung'] ?>)
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="mb-3">
            <label>Tanggal</label>
            <input type="date" name="tanggal_booking" class="form-control"
                   min="<?= date('Y-m-d') ?>" required>
          </div>

          <div class="mb-3">
            <label>Jumlah Orang</label>
            <input type="number" name="jumlah_orang" class="form-control" min="1" value="1" required>
          </div>
        </div>
        <div class="modal-footer">
          <button name="booking" class="btn btn-primary">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php include 'footer.php'; ?>
