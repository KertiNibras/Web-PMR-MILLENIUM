<?php
include '../koneksi.php';
 $date = $_GET['date'];

 $sql = "SELECT absensi.*, users.nama, users.kelas FROM absensi JOIN users ON absensi.user_id = users.id WHERE absensi.tanggal = '$date'";
 $res = mysqli_query($koneksi, $sql);

 $data = [];
while($row = mysqli_fetch_assoc($res)){
    $data[] = $row;
}
echo json_encode($data);