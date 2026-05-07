<?php
session_start();
include '../koneksi.php';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    mysqli_query($koneksi, "UPDATE pendaftaran SET card_sent=1 WHERE id='$id'");
    echo "ok";
}
?>