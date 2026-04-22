<?php
session_start();
include '../koneksi.php';
date_default_timezone_set('Asia/Jakarta');

header('Content-Type: application/json');

 $response = [
    'is_open' => false,
    'message' => 'Absensi Belum Dibuka',
    'jam_mulai' => '',
    'jam_selesai' => ''
];

// Ambil pengaturan dari database
 $sql = mysqli_query($koneksi, "SELECT * FROM pengaturan_absensi LIMIT 1");

if (!$sql) {
    // Jika query gagal (misal tabel tidak ada)
    $response['message'] = 'Error DB: ' . mysqli_error($koneksi);
    echo json_encode($response);
    exit;
}

if (mysqli_num_rows($sql) > 0) {
    $setting = mysqli_fetch_assoc($sql);
    
    $response['jam_mulai'] = $setting['waktu_mulai'];
    $response['jam_selesai'] = $setting['waktu_selesai'];
    
    $tanggal_setting = $setting['tanggal'];
    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');

    // Logika Otomatis
    if ($tanggal_setting == $current_date) {
        if ($current_time >= $setting['waktu_mulai'] && $current_time <= $setting['waktu_selesai']) {
            $response['is_open'] = true;
            $response['message'] = 'Absensi sedang dibuka.';
        } else {
            if ($current_time > $setting['waktu_selesai']) {
                 $response['message'] = 'Waktu absensi sudah berakhir.';
            } else {
                 $response['message'] = 'Absensi belum dimulai (menunggu jam '.$setting['waktu_mulai'].').';
            }
        }
    } else {
        // Jika tanggal tidak sama
        $response['message'] = 'Tidak ada jadwal absensi hari ini.';
    }
} else {
    $response['message'] = 'Pengaturan absensi belum dikonfigurasi.';
}

echo json_encode($response);
?>