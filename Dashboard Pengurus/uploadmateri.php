<?php
include 'koneksi.php';

$judul     = $_POST['judul'];
$kategori  = $_POST['kategori'];
$deskripsi = $_POST['deskripsi'];

$file = $_FILES['file'];
$namaFile = time() . "_" . $file['name'];
move_uploaded_file($file['tmp_name'], "uploads/" . $namaFile);

mysqli_query($koneksi, "
  INSERT INTO perpus_anggota 
  (judul, kategori, deskripsi, file_pdf) 
  VALUES 
  ('$judul','$kategori','$deskripsi','$namaFile')
");

echo "success";
