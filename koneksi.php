<?php
// Konfigurasi Database
$host = "localhost";
$user = "root";      // default XAMPP
$pass = "";          // default XAMPP
$db   = "pmrmillenium";   // ganti dengan nama database kamu

// Koneksi Ke MySQL
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek Koneksi
if (mysqli_connect_errno()) {
    echo "Gagal terhubung ke database : " . mysqli_connect_error();
    exit;
}
?>
