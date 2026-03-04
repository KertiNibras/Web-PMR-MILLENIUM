<?php
include '../koneksi.php';
 $month = $_GET['month'];
 $year = $_GET['year'];

// Query hitung group by tanggal
 $sql = "SELECT tanggal, COUNT(*) as total FROM absensi WHERE MONTH(tanggal) = '$month' AND YEAR(tanggal) = '$year' GROUP BY tanggal";
 $res = mysqli_query($koneksi, $sql);

 $data = [];
while($row = mysqli_fetch_assoc($res)){
    $data[$row['tanggal']] = $row['total'];
}
echo json_encode($data);