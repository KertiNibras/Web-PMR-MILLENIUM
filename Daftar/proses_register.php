<?php
// 1. MATIKAN display_errors DI PRODUCTION, TAPI AKTIFKAN UNTUK DEBUG
ini_set('display_errors', 1); // Ubah jadi 0 kalau sudah jalan
error_reporting(E_ALL);

header('Content-Type: application/json');

require_once __DIR__ . '/../koneksi.php';

if (!$koneksi) {
    echo json_encode(['status' => 'error', 'msg' => 'Koneksi database gagal.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = "Tidak Diketahui";
    $kelas = "-";
    $jurusan = "-";
    $nohp = "-"; // Variabel ini benar

    $answers = [];

    // PROSES INPUT TEXT
    foreach ($_POST as $key => $val) {
        if (strpos($key, 'dyn_') === 0) {
            $q_id = str_replace('dyn_', '', $key);
            $qQuery = mysqli_query($koneksi, "SELECT question_text FROM form_questions WHERE id='$q_id'");

            if ($qQuery && mysqli_num_rows($qQuery) > 0) {
                $qData = mysqli_fetch_assoc($qQuery);
                $questionText = $qData['question_text'];
                $lowerQ = strtolower($questionText);

                $cleanVal = mysqli_real_escape_string($koneksi, $val);
                $answers[$questionText] = $cleanVal;

                if (strpos($lowerQ, 'nama') !== false) {
                    $nama = $cleanVal;
                } elseif (strpos($lowerQ, 'kelas') !== false) {
                    $kelas = $cleanVal;
                } elseif (strpos($lowerQ, 'jurusan') !== false) {
                    $jurusan = $cleanVal;
                } elseif (strpos($lowerQ, 'whatsapp') !== false || strpos($lowerQ, 'nomor') !== false) {
                    $nohp = $cleanVal;
                }
            }
        }
    }

    // PROSES FILE UPLOAD
    foreach ($_FILES as $key => $file) {
        if (strpos($key, 'dyn_') === 0 && $file['error'] == 0) {
            $q_id = str_replace('dyn_', '', $key);
            $qQuery = mysqli_query($koneksi, "SELECT question_text FROM form_questions WHERE id='$q_id'");
            if ($qQuery && mysqli_num_rows($qQuery) > 0) {
                $qData = mysqli_fetch_assoc($qQuery);
                $questionText = $qData['question_text'];

                $uploadDir = __DIR__ . "/../uploads/question_file/";
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $newFileName = "file_" . time() . "_" . rand(100, 999) . "." . $ext;
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $answers[$questionText] = "question_file/" . $newFileName;
                }
            }
        }
    }

    $jsonAnswers = mysqli_real_escape_string($koneksi, json_encode($answers));

    // PERBAIKAN QUERY:
    // 1. Kolom 'jurusans' (sesuaikan dengan database Anda, cek apakah pakai 's' atau tidak)
    // 2. Variabel '$nohp' (bukan $no_hp)
    $sql = "INSERT INTO pendaftaran 
            (nama_lengkap, kelas, jurusans, no_whatsapp, answers) 
            VALUES 
            ('$nama', '$kelas', '$jurusan', '$nohp', '$jsonAnswers')";

    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        // Error spesifik dari MySQL akan muncul di sini
        echo json_encode(['status' => 'error', 'msg' => 'SQL Error: ' . mysqli_error($koneksi)]);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Metode request salah.']);
}