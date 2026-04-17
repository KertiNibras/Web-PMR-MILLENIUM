<?php
session_start();
include '../koneksi.php';

// 1. ATUR HEADER AGAR BISA MEMBACA ERROR
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

 $data = [];

// Cek koneksi
if (!$koneksi) {
    echo json_encode(["error" => "Koneksi database gagal: " . mysqli_connect_error()]);
    exit;
}

// 2. CEK NAMA KOLOM TABEL USERS ANDA DI SINI
// Jika tabel users Anda tidak punya kolom 'kelas', hapus 'u.kelas' di baris bawah ini!
 $sql_base = "SELECT a.tanggal, a.jam, a.foto, a.status, u.nama, u.kelas 
             FROM absensi a 
             JOIN users u ON a.user_id = u.id ";

// Jika request detail per tanggal
if (isset($_GET['tanggal'])) {
    $tgl = mysqli_real_escape_string($koneksi, $_GET['tanggal']);
    $sql = $sql_base . " WHERE a.tanggal = '$tgl' ORDER BY a.jam ASC";
    
    $res = mysqli_query($koneksi, $sql);
    
    // JIKA QUERY GAGAL, KIRIM PESAN ERROR SPESIFIK
    if (!$res) {
        echo json_encode(["error" => "SQL Error: " . mysqli_error($koneksi)]);
        exit;
    }
    
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }
}
// Jika request range tanggal (Export)
elseif (isset($_GET['start']) && isset($_GET['end'])) {
    $start = mysqli_real_escape_string($koneksi, $_GET['start']);
    $end = mysqli_real_escape_string($koneksi, $_GET['end']);
    
    $sql = $sql_base . " WHERE a.tanggal BETWEEN '$start' AND '$end' ORDER BY a.tanggal ASC, a.jam ASC";
    $res = mysqli_query($koneksi, $sql);

    if (!$res) {
        echo json_encode(["error" => "SQL Error: " . mysqli_error($koneksi)]);
        exit;
    }
    
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }
}

echo json_encode($data);
?>