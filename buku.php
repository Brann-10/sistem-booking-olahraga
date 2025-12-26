<?php
// Memuat konfigurasi koneksi dan header
include 'config.php';
session_start();
include 'header.php';

// Fungsi flashMessage
if (!function_exists('flashMessage')) {
    function flashMessage($type, $msg) {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }
}

// Proses Update Kegiatan (dari modal)
if (isset($_POST['update'])) {
    $id = (int) $_POST['id_kegiatan'];
    $nama_kegiatan = mysqli_real_escape_string($conn, $_POST['nama_kegiatan_edit']);
    $pelatih = mysqli_real_escape_string($conn, $_POST['pelatih_edit']);
    $waktu_mulai = $_POST['waktu_mulai_edit'];
    $waktu_selesai = $_POST['waktu_selesai_edit'];
    $batas_pengunjung = (int) $_POST['batas_pengunjung_edit'];

    $sql = "UPDATE kegiatan SET nama_kegiatan = ?, pelatih = ?, waktu_mulai = ?, waktu_selesai = ?, batas_pengunjung = ? WHERE id_kegiatan = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssssii', $nama_kegiatan, $pelatih, $waktu_mulai, $waktu_selesai, $batas_pengunjung, $id);
    if (mysqli_stmt_execute($stmt)) {
        flashMessage('success', 'Kegiatan berhasil diupdate!');
    } else {
        flashMessage('danger', 'Error update: ' . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt);
    header('Location: kegiatan.php');
    exit;
}

// Tambah Kegiatan
if (isset($_POST['tambah'])) {
    $nama_kegiatan = mysqli_real_escape_string($conn, $_POST['nama_kegiatan']);
    $pelatih = mysqli_real_escape_string($conn, $_POST['pelatih']);
    $waktu_mulai = $_POST['waktu_mulai'];
    $waktu_selesai = $_POST['waktu_selesai'];
    $batas_pengunjung = (int) $_POST['batas_pengunjung'];
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);

    $sql = "INSERT INTO kegiatan (nama_kegiatan, pelatih, waktu_mulai, waktu_selesai, batas_pengunjung, lokasi) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'sssssi', $nama_kegiatan, $pelatih, $waktu_mulai, $waktu_selesai, $batas_pengunjung, $lokasi);
    if (mysqli_stmt_execute($stmt)) {
        flashMessage('success', 'Kegiatan berhasil ditambahkan!');
    } else {
        flashMessage('danger', 'Error tambah: ' . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt);
    header('Location: kegiatan.php');
    exit;
}

// Hapus Kegiatan
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $sql = "DELETE FROM kegiatan WHERE id_kegiatan = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    if (mysqli_stmt_execute($stmt)) {
        flashMessage('success', 'Kegiatan berhasil dihapus!');
    } else {
        flashMessage('danger', 'Error hapus: ' . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt);
    header('Location: kegiatan.php');
    exit;
}

// Proses Pencarian
$search = '';
$params = [];
$sql = "SELECT * FROM kegiatan";
if (!empty($_GET['q'])) {
    $search = trim($_GET['q']);
    $like = "%" . mysqli_real_escape_string($conn, $search) . "%";
    $sql .= " WHERE nama_kegiatan LIKE ? OR pelatih LIKE ?";
    $params = ['ss', $like, $like];
}
$sql .= " ORDER BY id_kegiatan DESC";

// Eksekusi query
if ($params) {
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, ...$params);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, $sql);
}

// Tampilkan flash message
if (isset($_SESSION['flash'])) {
    $f = $_SESSION['flash'];
    echo "<div class=\"alert alert-{$f['type']} alert-dismissible fade show\" role=\"alert\">";
    echo htmlspecialchars($f['msg']);
    echo "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\"></button></div>";
    unset($_SESSION['flash']);
}
?>

<div class="card shadow">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="mb-0">Data Kegiatan</h3>
    <div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
        <i class="bi bi-plus-lg"></i> Tambah
      </button>
    </div>
  </div>
  <div class="card-body">
    <form class="mb-3" method="GET" action="buku.php">
      <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Cari judul atau pengarang..." value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
      </div>
    </form>
    <table class="table table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Judul</th>
          <th>Pelatih</th>
          <th>Tahun</th>
          <th>Batas Pengunjung</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
          <td><?= $row['id_buku']; ?></td>
          <td><?= htmlspecialchars($row['judul']); ?></td>
          <td><?= htmlspecialchars($row['pengarang']); ?></td>
          <td><?= $row['tahun_terbit']; ?></td>
          <td><?= $row['stok']; ?></td>
          <td>
            <button class="btn btn-sm btn-warning editBtn" 
                    data-id="<?= $row['id_buku'] ?>" 
                    data-judul="<?= htmlspecialchars($row['judul'], ENT_QUOTES) ?>" 
                    data-pengarang="<?= htmlspecialchars($row['pengarang'], ENT_QUOTES) ?>" 
                    data-tahun="<?= $row['tahun_terbit'] ?>" 
                    data-stok="<?= $row['stok'] ?>" 
                    data-bs-toggle="modal" data-bs-target="#editModal">
              <i class="bi bi-pencil"></i>
            </button>
            <a href="buku.php?hapus=<?= $row['id_buku'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
              <i class="bi bi-trash"></i>
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah Buku -->
<div class="modal fade" id="tambahModal">
  <div class="modal-dialog">
    <form method="POST" action="buku.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Judul</label>
            <input type="text" name="judul" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Pengarang</label>
            <input type="text" name="pengarang" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Tahun Terbit</label>
            <input type="number" name="tahun_terbit" class="form-control" required min="1900" max="2099">
          </div>
          <div class="mb-3">
            <label>Stok</label>
            <input type="number" name="stok" class="form-control" required min="0">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Buku -->
<div class="modal fade" id="editModal">
  <div class="modal-dialog">
    <form method="POST" action="buku.php">
      <input type="hidden" name="id_buku" id="edit-id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Buku</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Judul</label>
            <input type="text" name="judul_edit" id="edit-judul" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Pengarang</label>
            <input type="text" name="pengarang_edit" id="edit-pengarang" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Tahun Terbit</label>
            <input type="number" name="tahun_terbit_edit" id="edit-tahun" class="form-control" required min="1900" max="2099">
          </div>
          <div class="mb-3">
            <label>Stok</label>
            <input type="number" name="stok_edit" id="edit-stok" class="form-control" required min="0">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="update" class="btn btn-success">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
// Populate edit modal fields when edit button is clicked
var editModal = document.getElementById('editModal');
editModal.addEventListener('show.bs.modal', function (event) {
  var button = event.relatedTarget;
  document.getElementById('edit-id').value = button.getAttribute('data-id');
  document.getElementById('edit-judul').value = button.getAttribute('data-judul');
  document.getElementById('edit-pengarang').value = button.getAttribute('data-pengarang');
  document.getElementById('edit-tahun').value = button.getAttribute('data-tahun');
  document.getElementById('edit-stok').value = button.getAttribute('data-stok');
});
</script>

<?php include 'footer.php'; ?>
