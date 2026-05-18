<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// 1. Cek Sesi
if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi habis. Silakan login ulang.']);
    exit;
}

$id_user = $_SESSION['id'];

// 2. Terima File menggunakan $_FILES (BUKAN json_decode)
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menerima file foto dari browser.']);
    exit;
}

// 3. Siapkan Folder
$folder = __DIR__ . "/../uploads/absensi/"; 
if (!is_dir($folder)) {
    @mkdir($folder, 0755, true); 
}

$nama_file = "absen_" . $id_user . "_" . time() . ".jpg";
$file_path = $folder . $nama_file;

// 4. Pindahkan File Fisik ke Folder
if (!@move_uploaded_file($_FILES['foto']['tmp_name'], $file_path)) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan gambar ke folder server.']);
    exit;
}

$tanggal = date('Y-m-d');
$jam = date('H:i:s');
$status = 'hadir';
$keterangan = '-'; 

// 5. Cek Duplikat
$cek_query = "SELECT id FROM absensi WHERE user_id = ? AND tanggal = ?";
$stmt_cek = mysqli_prepare($koneksi, $cek_query);
mysqli_stmt_bind_param($stmt_cek, "is", $id_user, $tanggal);
mysqli_stmt_execute($stmt_cek);
mysqli_stmt_store_result($stmt_cek);

if (mysqli_stmt_num_rows($stmt_cek) > 0) {
    @unlink($file_path); 
    echo json_encode(['status' => 'error', 'message' => 'Anda sudah absensi hari ini.']);
    exit;
}

// 6. Simpan ke Database
$query = "INSERT INTO absensi (user_id, tanggal, jam, foto, status, keterangan) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "isssss", $id_user, $tanggal, $jam, $nama_file, $status, $keterangan);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'success', 'message' => 'Absensi sukses!']);
} else {
    @unlink($file_path); 
    echo json_encode(['status' => 'error', 'message' => 'Gagal Database: ' . mysqli_error($koneksi)]);
}
?>