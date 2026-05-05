<?php
session_start();
include '../koneksi.php';

// 1. Cek Login
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pengurus') {
    die("error_auth"); // Hentikan dengan pesan singkat
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 2. Validasi Input
    if (empty($_POST['judul']) || empty($_POST['kategori'])) {
        die("error_input_kosong");
    }

    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);

    // 3. Validasi File
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== 0) {
         // Jika upload baru wajib ada file
         die("error_file_tidak_ada");
    }

    $file = $_FILES['file'];
    $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($fileExt != 'pdf') {
        die("error_hanya_pdf");
    }

    $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($file['name']));
    
    // Path: naik 1 level dari folder saat ini, lalu masuk uploads/materi
    $targetDir = __DIR__ . "/../uploads/materi/"; 
    $targetFile = $targetDir . $fileName;

    // 4. Buat Folder jika belum ada (penting!)
    if (!is_dir($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            die("error_gagal_buat_folder");
        }
    }

    // 5. Pindahkan File
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {

        // 6. Simpan ke Database
        $query = "INSERT INTO perpustakaan (judul, deskripsi, kategori, file_pdf) VALUES ('$judul','$deskripsi','$kategori','$fileName')";
        
        if (mysqli_query($koneksi, $query)) {
            echo "success";
        } else {
            // Jika DB gagal, hapus file yang sudah terlanjur upload biar tidak sampah
            unlink($targetFile); 
            die("error_db: " . mysqli_error($koneksi)); // Tampilkan error DB
        }

    } else {
        // ini sering terjadi karena permission folder
        die("error_move_uploaded_file - Cek Permission Folder 'uploads/materi'");
    }
}
?>