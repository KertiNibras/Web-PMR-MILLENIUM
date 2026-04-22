<?php
session_start();
include '../koneksi.php';

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

// ========================
// BAGIAN PENTING: CEK STRUKTUR TABEL
// ========================
// Coba cek apakah tabel absensi punya kolom 'user_id' dan 'kelas'
// Jika tidak, kita gunakan cara biasa (tanpa JOIN).

 $cek_user_id = mysqli_query($koneksi, "SHOW COLUMNS FROM absensi LIKE 'user_id'");
 $cek_kelas_absensi = mysqli_query($koneksi, "SHOW COLUMNS FROM absensi LIKE 'kelas'");
 $ada_user_id = mysqli_num_rows($cek_user_id) > 0;
 $ada_kelas_di_absensi = mysqli_num_rows($cek_kelas_absensi) > 0;

 $sql = "";

// 1. Logika untuk Detail Tanggal (Modal)
if (isset($_GET['tanggal'])) {
    $tgl = mysqli_real_escape_string($koneksi, $_GET['tanggal']);
    
    // Prioritaskan JOIN jika ada user_id
    if ($ada_user_id) {
        // Cek apakah tabel users punya kolom kelas
        $cek_kelas_users = mysqli_query($koneksi, "SHOW COLUMNS FROM users LIKE 'kelas'");
        $punya_kelas_users = mysqli_num_rows($cek_kelas_users) > 0;
        
        $select_kelas = $punya_kelas_users ? "u.kelas" : "'-' as kelas";
        
        $sql = "SELECT a.tanggal, a.jam, a.foto, a.status, u.nama, $select_kelas 
                FROM absensi a 
                JOIN users u ON a.user_id = u.id 
                WHERE a.tanggal = '$tgl' 
                ORDER BY a.jam ASC";
    } else {
        // Jika tidak ada relasi, ambil langsung dari tabel absensi
        $sql = "SELECT tanggal, jam, foto, status, nama, kelas FROM absensi WHERE tanggal = '$tgl' ORDER BY jam ASC";
    }
}
// 2. Logika untuk Export (Range Tanggal)
elseif (isset($_GET['start']) && isset($_GET['end'])) {
    $start = mysqli_real_escape_string($koneksi, $_GET['start']);
    $end = mysqli_real_escape_string($koneksi, $_GET['end']);

    if ($ada_user_id) {
        $cek_kelas_users = mysqli_query($koneksi, "SHOW COLUMNS FROM users LIKE 'kelas'");
        $punya_kelas_users = mysqli_num_rows($cek_kelas_users) > 0;
        
        $select_kelas = $punya_kelas_users ? "u.kelas" : "'-' as kelas";

        $sql = "SELECT a.tanggal, a.jam, a.foto, a.status, u.nama, $select_kelas 
                FROM absensi a 
                JOIN users u ON a.user_id = u.id 
                WHERE a.tanggal BETWEEN '$start' AND '$end' 
                ORDER BY a.tanggal ASC, a.jam ASC";
    } else {
        $sql = "SELECT tanggal, jam, foto, status, nama, kelas FROM absensi WHERE tanggal BETWEEN '$start' AND '$end' ORDER BY tanggal ASC, jam ASC";
    }
}

// Eksekusi Query
if (!empty($sql)) {
    $res = mysqli_query($koneksi, $sql);
    
    if (!$res) {
        // Kirim error spesifik ke browser untuk debugging
        echo json_encode(["error" => "SQL Error: " . mysqli_error($koneksi), "query" => $sql]);
        exit;
    }
    
    while ($row = mysqli_fetch_assoc($res)) {
        $data[] = $row;
    }
}

echo json_encode($data);
?>