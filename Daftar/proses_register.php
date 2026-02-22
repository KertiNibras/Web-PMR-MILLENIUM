<?php
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $jurusan = mysqli_real_escape_string($koneksi, $_POST['jurusan']);
    $nohp = mysqli_real_escape_string($koneksi, $_POST['nohp']);

    // Simpan jawaban dinamis ke JSON
    $answers = [];
    // Ambil semua input yang namanya diawali 'dyn_'
    foreach($_POST as $key => $val) {
        if(strpos($key, 'dyn_') === 0) {
            $q_id = str_replace('dyn_', '', $key);
            // Ambil text pertanyaan (optional, atau simpan ID saja)
            $qQuery = mysqli_query($koneksi, "SELECT question_text FROM form_questions WHERE id='$q_id'");
            $qData = mysqli_fetch_assoc($qQuery);
            if($qData) {
                $answers[$qData['question_text']] = $val;
            }
        }
    }

    $jsonAnswers = json_encode($answers);

    $sql = "INSERT INTO pendaftaran (nama_lengkap, kelas, jurusan, no_whatsapp, answers) 
            VALUES ('$nama', '$kelas', '$jurusan', '$nohp', '$jsonAnswers')";

    if(mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => mysqli_error($koneksi)]);
    }
}
?>