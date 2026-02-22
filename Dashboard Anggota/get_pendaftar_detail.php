<?php
include '../koneksi.php';

if(!isset($_GET['id'])) exit;

 $id = intval($_GET['id']);
 $q = mysqli_query($koneksi, "SELECT * FROM pendaftaran WHERE id='$id'");
 $d = mysqli_fetch_assoc($q);

if(!$d) { echo "Data tidak ditemukan"; exit; }

 $answers = json_decode($d['answers'], true);

 $html = "
    <table style='width:100%; border-collapse:collapse;'>
        <tr><td style='padding:8px; border-bottom:1px solid #eee; font-weight:bold'>Nama</td><td style='padding:8px; border-bottom:1px solid #eee'>{$d['nama_lengkap']}</td></tr>
        <tr><td style='padding:8px; border-bottom:1px solid #eee; font-weight:bold'>Kelas</td><td style='padding:8px; border-bottom:1px solid #eee'>{$d['kelas']}</td></tr>
        <tr><td style='padding:8px; border-bottom:1px solid #eee; font-weight:bold'>Jurusan</td><td style='padding:8px; border-bottom:1px solid #eee'>{$d['jurusan']}</td></tr>
        <tr><td style='padding:8px; border-bottom:1px solid #eee; font-weight:bold'>No. WA</td><td style='padding:8px; border-bottom:1px solid #eee'>{$d['no_whatsapp']}</td></tr>
    </table>
    <h4 style='margin-top:20px; margin-bottom:10px; color:#333;'>Jawaban Tambahan</h4>
";

if($answers) {
    $html .= "<table style='width:100%; border-collapse:collapse;'>";
    foreach($answers as $question => $answer) {
        $html .= "<tr><td style='padding:8px; border-bottom:1px solid #eee; font-weight:bold; width:40%'>$question</td><td style='padding:8px; border-bottom:1px solid #eee'>$answer</td></tr>";
    }
    $html .= "</table>";
} else {
    $html .= "<p style='color:#999'>Tidak ada jawaban tambahan.</p>";
}

echo $html;
?>