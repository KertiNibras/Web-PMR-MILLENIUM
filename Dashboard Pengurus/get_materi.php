<?php
include 'koneksi.php';

$data = [];
$q = mysqli_query($koneksi, "SELECT * FROM perpus_anggota ORDER BY id DESC");

while ($row = mysqli_fetch_assoc($q)) {
  $data[] = $row;
}

echo json_encode($data);
