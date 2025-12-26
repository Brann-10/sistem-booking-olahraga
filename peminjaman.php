<?php
// peminjaman.php - Halaman Peminjaman Buku
include 'config.php';
session_start();
include 'header.php';

// Helper flash message
if (!function_exists('flashMessage')) {
    function flashMessage($type, $msg) {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }
}

// Proses Tambah Peminjaman
if (isset($_POST['pinjam'])) {
    $anggota = (int) $_POST['anggota_id'];
    $buku = (int) $_POST['buku_id'];
    $jumlah = (int) $_POST['jumlah'];

    // Mulai transaction
    mysqli_begin_transaction($conn);
    try {
        // Cek stok
        $stok_res = mysqli_query($conn, "SELECT stok FROM buku WHERE id_buku = $buku FOR UPDATE");
        $stok_row = mysqli_fetch_assoc($stok_res);
        if ($stok_row['stok'] < $jumlah) {
            throw new Exception('Stok tidak cukup');
        }

        // Insert ke peminjaman
        $sql1 = "INSERT INTO peminjaman (id_anggota, tanggal_pinjam) VALUES (?, NOW())";
        $stmt1 = mysqli_prepare($conn, $sql1);
        mysqli_stmt_bind_param($stmt1, 'i', $anggota);
        mysqli_stmt_execute($stmt1);
        $pid = mysqli_insert_id($conn);

        // Insert ke detail_peminjaman
        $sql2 = "INSERT INTO detail_peminjaman (id_peminjaman, id_buku, jumlah) VALUES (?, ?, ?)";
        $stmt2 = mysqli_prepare($conn, $sql2);
        mysqli_stmt_bind_param($stmt2, 'iii', $pid, $buku, $jumlah);
        mysqli_stmt_execute($stmt2);

        // Update stok buku
        $sql3 = "UPDATE buku SET stok = stok - ? WHERE id_buku = ?";
        $stmt3 = mysqli_prepare($conn, $sql3);
        mysqli_stmt_bind_param($stmt3, 'ii', $jumlah, $buku);
        mysqli_stmt_execute($stmt3);

        mysqli_commit($conn);
        flashMessage('success', 'Peminjaman berhasil');
    } catch (Exception $e) {
        mysqli_rollback($conn);
        flashMessage('danger', 'Error: ' . $e->getMessage());
    }
    header('Location: peminjaman.php'); exit;
}

// Proses Pengembalian
if (isset($_GET['kembali'])) {
    $pid = (int) $_GET['kembali'];
    mysqli_begin_transaction($conn);
    try {
        // Ambil detail
        $det = mysqli_query($conn, "SELECT id_buku, jumlah FROM detail_peminjaman WHERE id_peminjaman = $pid");
        while ($row = mysqli_fetch_assoc($det)) {
            // Update stok
            $stmt = mysqli_prepare($conn, "UPDATE buku SET stok = stok + ? WHERE id_buku = ?");
            mysqli_stmt_bind_param($stmt, 'ii', $row['jumlah'], $row['id_buku']);
            mysqli_stmt_execute($stmt);
        }
        // Update tanggal kembali
        $stmt4 = mysqli_prepare($conn, "UPDATE peminjaman SET tanggal_kembali = NOW() WHERE id_peminjaman = ?");
        mysqli_stmt_bind_param($stmt4, 'i', $pid);
        mysqli_stmt_execute($stmt4);

        mysqli_commit($conn);
        flashMessage('success', 'Buku berhasil dikembalikan');
    } catch (Exception $e) {
        mysqli_rollback($conn);
        flashMessage('danger', 'Error: ' . $e->getMessage());
    }
    header('Location: peminjaman.php'); exit;
}

// Tampilkan flash message
if (isset($_SESSION['flash'])) {
    $f = $_SESSION['flash'];
    echo "<div class=\"alert alert-{$f['type']} alert-dismissible fade show\" role=\"alert\">" . htmlspecialchars($f['msg']) . "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>";
    unset($_SESSION['flash']);
}

// Ambil data anggota dan buku untuk modal
$anggota_list = mysqli_query($conn, "SELECT id_anggota, nama FROM anggota ORDER BY nama");
$buku_list    = mysqli_query($conn, "SELECT id_buku, judul, stok FROM buku WHERE stok > 0 ORDER BY judul");

// Query lapangan peminjaman
$sql = "SELECT p.id_peminjaman, a.nama, b.judul, d.jumlah, p.tanggal_pinjam, p.tanggal_kembali
        FROM peminjaman p
        JOIN anggota a ON p.id_anggota = a.id_anggota
        JOIN detail_peminjaman d ON p.id_peminjaman = d.id_peminjaman
        JOIN buku b ON d.id_buku = b.id_buku
        ORDER BY p.tanggal_pinjam DESC";
$result = mysqli_query($conn, $sql);
?>

<div class="card shadow">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="mb-0">Data Peminjaman</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pinjamModal">
      <i class="bi bi-cart-plus"></i> Peminjaman Baru
    </button>
  </div>
  <div class="card-body">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Anggota</th>
          <th>Buku</th>
          <th>Jumlah</th>
          <th>Tgl Pinjam</th>
          <th>Tgl Kembali</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no=1; while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
          <td><?= $no++; ?></td>
          <td><?= htmlspecialchars($row['nama']); ?></td>
          <td><?= htmlspecialchars($row['judul']); ?></td>
          <td><?= $row['jumlah']; ?></td>
          <td><?= $row['tanggal_pinjam']; ?></td>
          <td><?= $row['tanggal_kembali'] ?: '<span class="text-danger">Belum</span>'; ?></td>
          <td>
            <?php if (!$row['tanggal_kembali']): ?>
            <a href="peminjaman.php?kembali=<?= $row['id_peminjaman']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Konfirmasi pengembalian?')">
              <i class="bi bi-box-arrow-in-left"></i> Kembali
            </a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Peminjaman Baru -->
<div class="modal fade" id="pinjamModal">
  <div class="modal-dialog">
    <form method="POST" action="peminjaman.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Boking Kegiatan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Anggota</label>
            <select name="anggota_id" class="form-control" required>
              <option value="">-- Pilih Anggota --</option>
              <?php while($a = mysqli_fetch_assoc($anggota_list)): ?>
                <option value="<?= $a['id_anggota']; ?>"><?= htmlspecialchars($a['nama']); ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label>Kegiatan</label>
            <select name="buku_id" class="form-control" required>
              <option value="">-- Pilih Kegiatan --</option>
              <?php while($b = mysqli_fetch_assoc($buku_list)): ?>
                <option value="<?= $b['id_buku']; ?>"><?= htmlspecialchars($b['judul']); ?> (Stok: <?= $b['stok']; ?>)</option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label>Jumlah</label>
            <input type="number" name="jumlah" class="form-control" min="1" value="1" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="pinjam" class="btn btn-primary">Pinjam</button>
        </div>
      </div>
    </form>
  </div>
</div>

<?php include 'footer.php'; ?>
