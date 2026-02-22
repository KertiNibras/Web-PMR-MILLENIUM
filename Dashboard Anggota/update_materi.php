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
    // UBAH $conn -> $koneksi
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);

    // Cek apakah ada file baru diupload
    if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {

        $file = $_FILES['file'];
        $fileName = time() . '_' . basename($file['name']);
        // Path disesuaikan: keluar 1 folder dari Dashboard Anggota
        $targetDir = "../uploads/materi/";
        $targetFile = $targetDir . $fileName;

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
            
            // Hapus file lama
            $oldFileQuery = mysqli_query($koneksi, "SELECT file_pdf FROM perpustakaan WHERE id='$id'");
            $oldData = mysqli_fetch_assoc($oldFileQuery);
            if ($oldData && !empty($oldData['file_pdf'])) {
                $oldFilePath = $targetDir . $oldData['file_pdf'];
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }

            // Update DB dengan file baru
            $query = mysqli_query($koneksi,
                "UPDATE perpustakaan 
                 SET judul='$judul', deskripsi='$deskripsi',
                     kategori='$kategori', file_pdf='$fileName'
                 WHERE id='$id'"
            );

            if ($query) {
                echo "success";
            } else {
                echo "error_db";
            }

        } else {
            echo "error_upload";
        }

    } else {
        // Update tanpa ganti file
        $query = mysqli_query($koneksi,
            "UPDATE perpustakaan 
             SET judul='$judul', deskripsi='$deskripsi',
                 kategori='$kategori'
             WHERE id='$id'"
        );

        if ($query) {
            echo "success";
        } else {
            echo "error_db";
        }
    }
}
?>