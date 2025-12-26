<?php
session_start();

if (isset($_SESSION['login'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if (isset($_POST['login'])) {
    if ($_POST['username'] === 'admin' && $_POST['password'] === 'admin123') {
        $_SESSION['login'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Username atau password salah';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container d-flex justify-content-center align-items-center" style="min-height:100vh">
  <div class="card p-4" style="width:350px">
    <h4 class="text-center">Login Admin</h4>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <input name="username" class="form-control mb-2" placeholder="Username" required>
      <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
      <button name="login" class="btn btn-primary w-100">Login</button>
    </form>

    <div class="text-center mt-3">
      <a href="index.php">← Kembali ke Beranda</a>
    </div>
  </div>
</div>

</body>
</html>
