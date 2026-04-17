<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'pengurus') {
    die("Akses ditolak.");
}

if (isset($_POST['order'])) {
    $order = json_decode($_POST['order']); // Decode array dari JS
    
    foreach ($order as $index => $id) {
        $id = intval($id);
        $rank = $index + 1;
        mysqli_query($koneksi, "UPDATE pengurus SET urutan = $rank WHERE id = $id");
    }
    echo "Sukses";
}
?>