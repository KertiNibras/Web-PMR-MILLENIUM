<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['nama'])) {
    echo json_encode([]);
    exit;
}

// Pastikan nama tabel sesuai dengan yang ada di DB (perpustakaan)
 $sql = "SELECT * FROM perpustakaan ORDER BY id DESC";
 $result = mysqli_query($koneksi, $sql);

 $data = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($data);
?>