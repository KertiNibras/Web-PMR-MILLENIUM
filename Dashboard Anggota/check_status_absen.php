<?php
include '../koneksi.php';
session_start();
// Cek apakah ada baris di pengaturan_absensi yang aktif DAN waktu sekarang dalam rentang
 $now_time = date('H:i:s');
 $now_date = date('Y-m-d');

 $q = mysqli_query($koneksi, "SELECT * FROM pengaturan_absensi WHERE tanggal='$now_date' AND status=1 AND waktu_mulai <= '$now_time' AND waktu_selesai >= '$now_time'");
if(mysqli_num_rows($q) > 0){
    $row = mysqli_fetch_assoc($q);
    echo json_encode(['status' => 'buka', 'selesai' => $row['waktu_selesai']]);
} else {
    echo json_encode(['status' => 'tutup']);
}