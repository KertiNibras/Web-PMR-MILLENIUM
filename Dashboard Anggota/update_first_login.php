<?php
session_start();
require_once __DIR__ . '/../koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false]);
    exit;
}

 $user_id = $_SESSION['id'];
 $query = mysqli_query($koneksi, "UPDATE users SET first_login = 0 WHERE id = '$user_id'");

if ($query) {
    $_SESSION['first_login'] = 0;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>