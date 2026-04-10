<?php
// Matikan error reporting untuk production
error_reporting(0);
ini_set('display_errors', 0);

session_start();
include '../koneksi.php';

header('Content-Type: application/json');

// Cek koneksi
if (!$koneksi) {
    echo json_encode(['is_open' => false, 'message' => 'Koneksi Database Gagal']);
    exit;
}

// Cek tabel pengaturan
 $checkTable = mysqli_query($koneksi, "SHOW TABLES LIKE 'pengaturan_absensi'");
if (mysqli_num_rows($checkTable) == 0) {
    // Jika tabel belum ada, buat otomatis dengan NAMA KOLOM YANG BENAR
    // Ubah 'jam_mulai' -> 'waktu_mulai' dan 'jam_selesai' -> 'waktu_selesai'
    // agar cocok dengan kode kelolaabsen.php
    $sql_create = "CREATE TABLE pengaturan_absensi (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        tanggal DATE NOT NULL,
        waktu_mulai TIME NOT NULL,
        waktu_selesai TIME NOT NULL,
        status ENUM('aktif','tidak') DEFAULT 'tidak'
    )";
    mysqli_query($koneksi, $sql_create);
    mysqli_query($koneksi, "INSERT INTO pengaturan_absensi (tanggal, waktu_mulai, waktu_selesai, status) VALUES (CURRENT_DATE(), '07:00:00', '09:00:00', 'tidak')");
}

 $query = mysqli_query($koneksi, "SELECT * FROM pengaturan_absensi LIMIT 1");
 $setting = mysqli_fetch_assoc($query);

 $response = [
    'is_open' => false,
    'message' => 'Absensi Belum Dibuka',
    'jam_mulai' => '', // Key JSON tetap 'jam_mulai' agar tidak merusak script JS di frontend
    'jam_selesai' => ''
];

if ($setting && $setting['status'] == 'aktif') {
    $now = date('H:i:s');
    $today = date('Y-m-d');
    
    // Gunakan nama kolom baru: waktu_mulai & waktu_selesai
    if ($setting['tanggal'] == $today) {
        if ($now >= $setting['waktu_mulai'] && $now <= $setting['waktu_selesai']) {
            $response['is_open'] = true;
            // Ambil data dari kolom baru, masukkan ke key lama untuk kompatibilitas JS
            $response['jam_mulai'] = $setting['waktu_mulai'];
            $response['jam_selesai'] = $setting['waktu_selesai'];
        } else if ($now < $setting['waktu_mulai']) {
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