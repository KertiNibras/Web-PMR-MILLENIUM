<?php
session_start();
include '../koneksi.php';

// Matikan error display untuk menjaga JSON tetap bersih
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// Cek login
if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi tidak valid. Silakan login ulang.']);
    exit;
}

 $id_user = $_SESSION['id'];

// Ambil data JSON dari body request
 $json = file_get_contents('php://input');
 $data = json_decode($json, true);

// Validasi data foto
if (!isset($data['foto']) || empty($data['foto'])) {
    echo json_encode(['status' => 'error', 'message' => 'Foto tidak ditemukan.']);
    exit;
}

 $foto_base64 = $data['foto'];
 $keterangan = isset($data['keterangan']) ? $data['keterangan'] : '-';

// Proses simpan gambar
 $folder = "../uploads/absensi/";
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

// Nama file unik
 $nama_file = "absen_" . $id_user . "_" . time() . ".png";

// Pisahkan header base64
 $image_parts = explode(";base64,", $foto_base64);
if (count($image_parts) < 2) {
    echo json_encode(['status' => 'error', 'message' => 'Format gambar tidak valid.']);
    exit;
}
 $image_base64 = base64_decode($image_parts[1]);
 $file_path = $folder . $nama_file;

// Simpan file
if (file_put_contents($file_path, $image_base64)) {
    // Simpan ke database
    $tanggal = date('Y-m-d');
    $jam = date('H:i:s');
    $status = 'hadir'; // Otomatis hadir karena sudah foto

    // Cek apakah sudah absen hari ini
    $cek = mysqli_query($koneksi, "SELECT id FROM absensi WHERE user_id='$id_user' AND tanggal='$tanggal'");
    if (mysqli_num_rows($cek) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Anda sudah melakukan absensi hari ini.']);
        exit;
    }

    $query = "INSERT INTO absensi (user_id, tanggal, jam, foto, status, keterangan) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "isssss", $id_user, $tanggal, $jam, $nama_file, $status, $keterangan);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['status' => 'success', 'message' => 'Absensi berhasil dicatat.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke database.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan gambar ke server.']);
}
?>