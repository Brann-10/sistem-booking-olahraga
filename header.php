<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pengunjung Kegiatan Olahraga</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-activity"></i> Sistem Kegiatan</a>
    <div class="navbar-nav">
        <a class="nav-link" href="pengunjung.php"><i class="bi bi-people"></i> Pengunjung</a>
            <a class="nav-link" href="kegiatan.php"><i class="bi bi-calendar-event"></i> Kegiatan</a>
        <a class="nav-link" href="booking.php"><i class="bi bi-calendar-plus"></i> Booking</a>
            <a class="nav-link" href="laporan.php"><i class="bi bi-graph-up"></i> Laporan</a>
            <a href="logout.php" class="btn btn-outline-light btn-sm ms-2">
  Logout
</a>

    </div>
</div>
</nav>
    
<div class="container mt-4">
    <?php if(isset($_SESSION['flash'])): ?>
        <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show">
        <?= $_SESSION['flash']['msg'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>