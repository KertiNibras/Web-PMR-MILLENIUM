<?php
include '../koneksi.php';

if(!isset($_GET['id'])) exit;

 $id = intval($_GET['id']);
 $q = mysqli_query($koneksi, "SELECT * FROM pendaftaran WHERE id='$id'");
 $d = mysqli_fetch_assoc($q);

if(!$d) { echo "Data tidak ditemukan"; exit; }

 $answers = json_decode($d['answers'], true);

$html = "<table style='width:100%; border-collapse:collapse;'>";

if($answers && is_array($answers)) foreach($answers as $question => $answer) {

    $displayAnswer = htmlspecialchars($answer);

    // Jika jawaban adalah file dari question_file
    if (strpos($answer, 'question_file/') !== false) {

        $filePath = "../uploads/" . $answer;

        if (file_exists($filePath)) {
            $displayAnswer = "<a href='$filePath' target='_blank' 
                                style='color:#007bff; font-weight:bold;'>
                                Lihat File
                              </a>";
        } else {
            $displayAnswer = "<span style='color:red;'>File tidak ditemukan</span>";
        }
    }

    $html .= "
    <tr>
        <td style='padding:8px; border-bottom:1px solid #eee; font-weight:bold; width:40%'>
            ".htmlspecialchars($question)."
        </td>
        <td style='padding:8px; border-bottom:1px solid #eee'>
            ".$displayAnswer."
        </td>
    </tr>";
}

echo $html;
?>