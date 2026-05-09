<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// Pastikan session ID user ada berdasarkan struktur tabel users
if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi login tidak ditemukan. Silakan login ulang.']);
    exit;
}

 $id_user = $_SESSION['id'];

 $json = file_get_contents('php://input');
 $data = json_decode($json, true);

if (!isset($data['foto']) || empty($data['foto'])) {
    echo json_encode(['status' => 'error', 'message' => 'Data foto tidak diterima oleh server.']);
    exit;
}

 $foto_base64 = $data['foto'];
 $keterangan = isset($data['keterangan']) ? mysqli_real_escape_string($koneksi, $data['keterangan']) : '-';

// Folder simpan foto (SESUAIKAN DENGAN STRUCTURE PROJECT KALIAN)
 $folder = __DIR__ . "/../uploads/absensi/"; 
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

 $nama_file = "absen_" . $id_user . "_" . time() . ".png";
 $image_parts = explode(";base64,", $foto_base64);

if (count($image_parts) < 2) {
    echo json_encode(['status' => 'error', 'message' => 'Format base64 gambar tidak valid.']);
    exit;
}

 $image_base64 = base64_decode($image_parts[1]);
 $file_path = $folder . $nama_file;

if (!file_put_contents($file_path, $image_base64)) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan gambar ke server. Cek permission folder.']);
    exit;
}

 $tanggal = date('Y-m-d');
 $jam = date('H:i:s');
 $status = 'hadir';

// Cek duplikat absensi
 $cek_query = "SELECT id FROM absensi WHERE user_id = ? AND tanggal = ?";
 $stmt_cek = mysqli_prepare($koneksi, $cek_query);
mysqli_stmt_bind_param($stmt_cek, "is", $id_user, $tanggal);
mysqli_stmt_execute($stmt_cek);
mysqli_stmt_store_result($stmt_cek);

if (mysqli_stmt_num_rows($stmt_cek) > 0) {
    unlink($file_path); 
    echo json_encode(['status' => 'error', 'message' => 'Anda sudah melakukan absensi hari ini.']);
    exit;
}

// Insert ke database
 $query = "INSERT INTO absensi (user_id, tanggal, jam, foto, status, keterangan) VALUES (?, ?, ?, ?, ?, ?)";
 $stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "isssss", $id_user, $tanggal, $jam, $nama_file, $status, $keterangan);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['status' => 'success', 'message' => 'Absensi berhasil dicatat!']);
} else {
    unlink($file_path); 
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke database: ' . mysqli_error($koneksi)]);
}
?>