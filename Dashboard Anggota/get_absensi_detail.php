<?php
session_start();
require_once __DIR__ . '/../koneksi.php';
header('Content-Type: application/json');

// Bisa dipanggil dengan ?tanggal=YYYY-MM-DD atau ?start=...&end=... (untuk export)
if (isset($_GET['start']) && isset($_GET['end'])) {
    // Mode Export
    $start = mysqli_real_escape_string($koneksi, $_GET['start']);
    $end = mysqli_real_escape_string($koneksi, $_GET['end']);
    
    $query = "SELECT a.tanggal, a.jam, a.status, a.keterangan, u.nama, u.kelas 
              FROM absensi a 
              JOIN users u ON a.user_id = u.id 
              WHERE a.tanggal BETWEEN ? AND ? 
              ORDER BY a.tanggal DESC, a.jam DESC";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "ss", $start, $end);
    
} elseif (isset($_GET['tanggal'])) {
    // Mode Detail Hari Ini
    $tanggal = mysqli_real_escape_string($koneksi, $_GET['tanggal']);
    
    $query = "SELECT a.tanggal, a.jam, a.status, a.keterangan, a.foto, u.nama, u.kelas 
              FROM absensi a 
              JOIN users u ON a.user_id = u.id 
              WHERE a.tanggal = ? 
              ORDER BY a.jam ASC";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "s", $tanggal);
} else {
    echo json_encode([]);
    exit;
}

mysqli_stmt_execute($stmt);
 $result = mysqli_stmt_get_result($stmt);
 $data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>