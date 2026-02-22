<?php
include '../koneksi.php';

// UBAH $conn MENJADI $koneksi
 $query = mysqli_query($koneksi, "SELECT * FROM perpustakaan ORDER BY id DESC");

 $data = [];

while ($row = mysqli_fetch_assoc($query)) {
    $data[] = $row;
}

header('Content-Type: application/json');
echo json_encode($data);
?>