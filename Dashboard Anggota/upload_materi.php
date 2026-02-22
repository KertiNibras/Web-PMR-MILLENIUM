<?php
session_start();
include '../koneksi.php'; // Pastikan variabel di file ini adalah $koneksi

// Cek Login & Role (Keamanan tambahan)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pengurus') {
    echo "error_auth";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // UBAH $conn MENJADI $koneksi
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);

    if (!isset($_FILES['file'])) {
        echo "error_file";
        exit;
    }

    $file = $_FILES['file'];
    $fileName = time() . '_' . basename($file['name']);
    
    // UBAH PATH: Karena file ini di Dashboard Anggota, 
    // naik 1 level (../) lalu masuk uploads/materi
    $targetDir = "../uploads/materi/"; 
    $targetFile = $targetDir . $fileName;

    // Cek ekstensi
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if ($fileExt != 'pdf') {
        echo "error_ext";
        exit;
    }

    // Buat folder jika belum ada
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {

        // UBAH $conn MENJADI $koneksi
        $insert = mysqli_query($koneksi, 
            "INSERT INTO perpustakaan (judul, deskripsi, kategori, file_pdf)
             VALUES ('$judul','$deskripsi','$kategori','$fileName')"
        );

        if ($insert) {
            echo "success";
        } else {
            echo "error_db";
        }

    } else {
        echo "error_upload";
    }
}
?>