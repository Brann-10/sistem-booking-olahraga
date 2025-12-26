<?php
// Memuat konfigurasi koneksi dan header
include 'config.php';
session_start();
include 'header.php';

// Fungsi flashMessage (jika belum ada di header.php)
if (!function_exists('flashMessage')) {
    function flashMessage($type, $msg) {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
    }
}

// Tambah Anggota tanpa input tanggal (otomatis timestamp)
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    $sql = "INSERT INTO anggota (nama, alamat, tanggal_daftar) VALUES (?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $nama, $alamat);

    if (mysqli_stmt_execute($stmt)) {
        flashMessage('success', 'Anggota berhasil ditambahkan!');
    } else {
        flashMessage('danger', 'Error: ' . mysqli_error($conn));
    }
    mysqli_stmt_close($stmt);
    header('Location: anggota.php');
    exit;
}

// Hapus Anggota (tanpa menghapus histori peminjaman)
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    // Cek peminjaman aktif
    $res = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM peminjaman WHERE id_anggota = $id AND tanggal_kembali IS NULL");
    $row = mysqli_fetch_assoc($res);
    if ($row['cnt'] > 0) {
        flashMessage('danger', 'Anggota memiliki peminjaman aktif, tidak dapat dihapus');
    } else {
        $stmtDel = mysqli_prepare($conn, "DELETE FROM anggota WHERE id_anggota = ?");
        mysqli_stmt_bind_param($stmtDel, 'i', $id);
        if (mysqli_stmt_execute($stmtDel)) {
            flashMessage('success', 'Anggota berhasil dihapus');
        } else {
            flashMessage('danger', 'Error hapus: ' . mysqli_error($conn));
        }
        mysqli_stmt_close($stmtDel);
    }
    header('Location: anggota.php');
    exit;
}

// Tampilkan flash message jika ada
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    echo "<div class=\"alert alert-{$flash['type']} alert-dismissible fade show\" role=\"alert\">";
    echo htmlspecialchars($flash['msg']);
    echo "<button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>";
    echo "</div>";
    unset($_SESSION['flash']);
}

// Ambil daftar anggota
$query = "SELECT * FROM anggota ORDER BY id_anggota DESC";
$result = mysqli_query($conn, $query);
?>

<div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">Data Anggota</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahModal">
            <i class="bi bi-plus-lg"></i> Tambah
        </button>
    </div>
    <div class="card-body">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>Tanggal Daftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= $row['id_anggota']; ?></td>
                    <td><?= htmlspecialchars($row['nama']); ?></td>
                    <td><?= htmlspecialchars($row['alamat']); ?></td>
                    <td><?= $row['tanggal_daftar']; ?></td>
                    <td>
                        <a href="edit_anggota.php?id=<?= $row['id_anggota']; ?>" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="anggota.php?hapus=<?= $row['id_anggota']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="tambahModal">
    <div class="modal-dialog">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Anggota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Alamat</label>
                        <textarea name="alamat" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
