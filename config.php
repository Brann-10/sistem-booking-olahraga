<?php
// Konfigurasi koneksi database
$host   = 'localhost';
$user   = 'root';
$pass   = '';
$dbname = 'db_olahraga';

// Buat koneksi
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Cek koneksi
if (mysqli_connect_errno()) {
    die('Koneksi gagal: ' . mysqli_connect_error());
}
?>
