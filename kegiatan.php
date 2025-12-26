<?php
// Memuat konfigurasi koneksi dan header
include 'config.php';
session_start();
if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}

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
    $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi_edit']);

    $sql = "UPDATE kegiatan SET nama_kegiatan = ?, pelatih = ?, waktu_mulai = ?, waktu_selesai = ?, batas_pengunjung = ?, lokasi = ? WHERE id_kegiatan = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssssisi', $nama_kegiatan, $pelatih, $waktu_mulai, $waktu_selesai, $batas_pengunjung, $lokasi, $id);
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
    mysqli_stmt_bind_param($stmt, 'ssssis', $nama_kegiatan, $pelatih, $waktu_mulai, $waktu_selesai, $batas_pengunjung, $lokasi);
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
    <form class="mb-3" method="GET" action="kegiatan.php">
      <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Cari nama kegiatan atau pelatih..." value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i></button>
      </div>
    </form>
    <table class="table table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Nama Kegiatan</th>
          <th>Pelatih</th>
          <th>Waktu Mulai</th>
          <th>Waktu Selesai</th>
          <th>Batas Pengunjung</th>
          <th>Lokasi</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
          <td><?= $row['id_kegiatan']; ?></td>
          <td><?= htmlspecialchars($row['nama_kegiatan']); ?></td>
          <td><?= htmlspecialchars($row['pelatih']); ?></td>
          <td><?= $row['waktu_mulai']; ?></td>
          <td><?= $row['waktu_selesai']; ?></td>
          <td><?= $row['batas_pengunjung']; ?></td>
          <td><?= htmlspecialchars($row['lokasi']); ?></td>
          <td>
            <button class="btn btn-sm btn-warning editBtn" 
                    data-id="<?= $row['id_kegiatan'] ?>" 
                    data-nama_kegiatan="<?= htmlspecialchars($row['nama_kegiatan'], ENT_QUOTES) ?>" 
                    data-pelatih="<?= htmlspecialchars($row['pelatih'], ENT_QUOTES) ?>" 
                    data-waktu_mulai="<?= $row['waktu_mulai'] ?>" 
                    data-waktu_selesai="<?= $row['waktu_selesai'] ?>" 
                    data-batas_pengunjung="<?= $row['batas_pengunjung'] ?>" 
                    data-lokasi="<?= htmlspecialchars($row['lokasi'], ENT_QUOTES) ?>" 
                    data-bs-toggle="modal" data-bs-target="#editModal">
              <i class="bi bi-pencil"></i>
            </button>
            <a href="kegiatan.php?hapus=<?= $row['id_kegiatan'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
              <i class="bi bi-trash"></i>
            </a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah Kegiatan -->
<div class="modal fade" id="tambahModal">
  <div class="modal-dialog">
    <form method="POST" action="kegiatan.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Kegiatan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Nama Kegiatan</label>
            <input type="text" name="nama_kegiatan" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Pelatih</label>
            <input type="text" name="pelatih" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Waktu Mulai</label>
            <input type="time" name="waktu_mulai" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Waktu Selesai</label>
            <input type="time" name="waktu_selesai" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Batas Pengunjung</label>
            <input type="number" name="batas_pengunjung" class="form-control" required min="1">
          </div>
          <div class="mb-3">
            <label>Lokasi</label>
            <input type="text" name="lokasi" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit Kegiatan -->
<div class="modal fade" id="editModal">
  <div class="modal-dialog">
    <form method="POST" action="kegiatan.php">
      <input type="hidden" name="id_kegiatan" id="edit-id">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Kegiatan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Nama Kegiatan</label>
            <input type="text" name="nama_kegiatan_edit" id="edit-nama_kegiatan" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Pelatih</label>
            <input type="text" name="pelatih_edit" id="edit-pelatih" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Waktu Mulai</label>
            <input type="time" name="waktu_mulai_edit" id="edit-waktu_mulai" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Waktu Selesai</label>
            <input type="time" name="waktu_selesai_edit" id="edit-waktu_selesai" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Batas Pengunjung</label>
            <input type="number" name="batas_pengunjung_edit" id="edit-batas_pengunjung" class="form-control" required min="1">
          </div>
          <div class="mb-3">
            <label>Lokasi</label>
            <input type="text" name="lokasi_edit" id="edit-lokasi" class="form-control" required>
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
  document.getElementById('edit-nama_kegiatan').value = button.getAttribute('data-nama_kegiatan');
  document.getElementById('edit-pelatih').value = button.getAttribute('data-pelatih');
  document.getElementById('edit-waktu_mulai').value = button.getAttribute('data-waktu_mulai');
  document.getElementById('edit-waktu_selesai').value = button.getAttribute('data-waktu_selesai');
  document.getElementById('edit-batas_pengunjung').value = button.getAttribute('data-batas_pengunjung');
  document.getElementById('edit-lokasi').value = button.getAttribute('data-lokasi');
});
</script>

<?php include 'footer.php'; ?>