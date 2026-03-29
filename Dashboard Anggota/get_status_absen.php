<?php
// Matikan error reporting
error_reporting(0);
ini_set('display_errors', 0);

session_start();
include '../koneksi.php';

header('Content-Type: application/json');

// Cek koneksi
if (!$koneksi) {
    echo json_encode(['is_open' => false, 'message' => 'Koneksi DB Gagal']);
    exit;
}

// Cek tabel pengaturan
 $checkTable = mysqli_query($koneksi, "SHOW TABLES LIKE 'pengaturan_absensi'");
if (mysqli_num_rows($checkTable) == 0) {
    // Jika tabel belum ada, buat otomatis
    $sql_create = "CREATE TABLE pengaturan_absensi (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tanggal DATE NOT NULL,
        jam_mulai TIME NOT NULL,
        jam_selesai TIME NOT NULL,
        status ENUM('aktif','tidak') DEFAULT 'tidak'
    )";
    mysqli_query($koneksi, $sql_create);
    mysqli_query($koneksi, "INSERT INTO pengaturan_absensi (tanggal, jam_mulai, jam_selesai, status) VALUES (CURRENT_DATE(), '07:00:00', '09:00:00', 'tidak')");
}

 $query = mysqli_query($koneksi, "SELECT * FROM pengaturan_absensi LIMIT 1");
 $setting = mysqli_fetch_assoc($query);

 $response = [
    'is_open' => false,
    'message' => 'Absensi Belum Dibuka',
    'jam_mulai' => '',
    'jam_selesai' => ''
];

if ($setting && $setting['status'] == 'aktif') {
    $now = date('H:i:s');
    $today = date('Y-m-d');
    
    if ($setting['tanggal'] == $today) {
        if ($now >= $setting['jam_mulai'] && $now <= $setting['jam_selesai']) {
            $response['is_open'] = true;
            $response['jam_mulai'] = $setting['jam_mulai'];
            $response['jam_selesai'] = $setting['jam_selesai'];
        } else if ($now < $setting['jam_mulai']) {
            $response['message'] = 'Absensi belum dimulai';
        } else {
            $response['message'] = 'Waktu absensi sudah lewat';
        }
    } else {
        $response['message'] = 'Tidak ada jadwal absensi hari ini';
    }
}

echo json_encode($response);
?>