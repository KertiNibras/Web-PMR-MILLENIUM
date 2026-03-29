<?php
// 1. Matikan semua error reporting agar tidak mengganggu JSON
error_reporting(0); 
ini_set('display_errors', 0);

session_start();
include '../koneksi.php';

// Set header JSON
header('Content-Type: application/json');

// 2. Cek koneksi database
if (!$koneksi) {
    echo json_encode(['error' => 'Koneksi database gagal. Cek file koneksi.php.']);
    exit;
}

// 3. Validasi input
if (!isset($_GET['tanggal'])) {
    echo json_encode([]);
    exit;
}

 $tanggal = $_GET['tanggal'];

// 4. Query yang aman (ambil data user yang ada di tabel absensi)
// CATATAN: Pastikan di tabel users ada kolom 'kelas'. Jika tidak ada, hapus bagian users.kelas di bawah.
 $sql = "SELECT absensi.id, absensi.user_id, absensi.tanggal, absensi.jam, absensi.foto, absensi.status, absensi.keterangan, users.nama
        FROM absensi 
        JOIN users ON absensi.user_id = users.id 
        WHERE absensi.tanggal = '$tanggal'
        ORDER BY absensi.jam ASC";

 $result = mysqli_query($koneksi, $sql);

 $data = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
} else {
    // Jika query error, kirim pesan error (opsional, untuk debug)
    // echo json_encode(['error' => mysqli_error($koneksi)]);
    // exit;
}

echo json_encode($data);
?>