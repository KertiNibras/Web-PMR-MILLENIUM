<?php
session_start();
include '../koneksi.php';

// Cek Login & Role
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pengurus') {
    echo "error_auth";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = intval($_POST['id']);

    // Ambil nama file dulu
    // UBAH $conn -> $koneksi
    $get = mysqli_query($koneksi, "SELECT file_pdf FROM perpustakaan WHERE id='$id'");
    $data = mysqli_fetch_assoc($get);

    if ($data) {
        // Path disesuaikan
        $filePath = "../uploads/materi/" . $data['file_pdf'];

        // Hapus file fisik jika ada
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Hapus data di DB
        $delete = mysqli_query($koneksi, "DELETE FROM perpustakaan WHERE id='$id'");

        if ($delete) {
            echo "success";
        } else {
            echo "error_db";
        }
    } else {
        echo "error_notfound";
    }
}
?>