<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
header('Content-Type: application/json'); // Wajib ada agar JS bisa membaca response sebagai JSON

// Cek Login & Role
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pengurus') {
    echo json_encode(['status' => 'error', 'message' => 'Akses Ditolak!']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'ID tidak valid!']);
        exit;
    }

    // Ambil nama file dulu
    $get = mysqli_query($koneksi, "SELECT file_pdf FROM perpustakaan WHERE id='$id'");
    $data = mysqli_fetch_assoc($get);

    if ($data) {
        // Gunakan __DIR__ agar path tidak mudah rusak
        $filePath = __DIR__ . "/../uploads/materi/" . $data['file_pdf'];

        // Hapus file fisik jika ada
        if (!empty($data['file_pdf']) && file_exists($filePath)) {
            unlink($filePath);
        }

        // Hapus data di DB
        $delete = mysqli_query($koneksi, "DELETE FROM perpustakaan WHERE id='$id'");

        if ($delete) {
            echo json_encode(['status' => 'success', 'message' => 'Materi berhasil dihapus!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus dari database.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Data materi tidak ditemukan.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Metode request salah.']);
}
?>