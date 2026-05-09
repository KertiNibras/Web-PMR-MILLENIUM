<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json');

// Pastikan user sudah login
if (!isset($_SESSION['id'])) {
    echo json_encode(['is_open' => false, 'message' => 'Sesi habis, silakan login ulang', 'sudah_absen' => false]);
    exit;
}

 $id_user = $_SESSION['id'];

 $response = [
    'is_open' => false,
    'message' => 'Absensi Belum Dibuka',
    'jam_mulai' => '',
    'jam_selesai' => '',
    'sudah_absen' => false // Tambahan penting untuk pengecekan
];

// Ambil tanggal dan waktu saat ini
 $current_date = date('Y-m-d');
 $current_time = date('H:i:s');

// ========================================================
// CEK APAKAH USER SUDAH ABSEN HARI INI
// ========================================================
 $stmt_check = mysqli_prepare($koneksi, "SELECT id FROM absensi WHERE user_id = ? AND tanggal = ? AND status = 'hadir'");
mysqli_stmt_bind_param($stmt_check, "is", $id_user, $current_date);
mysqli_stmt_execute($stmt_check);
 $result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) > 0) {
    $response['sudah_absen'] = true;
}
// ========================================================

// Cari jadwal yang TANGGALNYA sama dengan HARI INI
 $sql = mysqli_query($koneksi, "SELECT * FROM pengaturan_absensi WHERE tanggal = '$current_date' LIMIT 1");

if ($sql && mysqli_num_rows($sql) > 0) {
    $setting = mysqli_fetch_assoc($sql);
    
    $response['jam_mulai'] = $setting['waktu_mulai'];
    $response['jam_selesai'] = $setting['waktu_selesai'];
    
    // Cek apakah waktu sekarang ada di antara jam mulai dan jam selesai
    if ($current_time >= $setting['waktu_mulai'] && $current_time <= $setting['waktu_selesai']) {
        $response['is_open'] = true;
        $response['message'] = 'Absensi sedang dibuka.';
    } else {
        if ($current_time > $setting['waktu_selesai']) {
             $response['message'] = 'Waktu absensi sudah berakhir.';
        } else {
             $response['message'] = 'Absensi belum dimulai (menunggu jam '.date('H:i', strtotime($setting['waktu_mulai'])).').';
        }
    }
} else {
    // Jika tidak ada baris jadwal untuk hari ini
    $response['message'] = 'Tidak ada jadwal latihan hari ini.';
}

echo json_encode($response);
?>