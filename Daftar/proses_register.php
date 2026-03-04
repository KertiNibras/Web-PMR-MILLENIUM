<?php
// 1. PENTING: Matikan display error agar tidak merusak output JSON
ini_set('display_errors', 0);
// 2. Set header bahwa output adalah JSON
header('Content-Type: application/json');

include '../koneksi.php';

// Cek koneksi
if (!$koneksi) {
    echo json_encode(['status' => 'error', 'msg' => 'Koneksi database gagal.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = "Tidak Diketahui";
    $kelas = "-";
    $jurusan = "-";
    $nohp = "-";

    $answers = [];

    // =========================
    // PROSES INPUT TEXT / SELECT / RADIO
    // =========================
    foreach ($_POST as $key => $val) {
        if (strpos($key, 'dyn_') === 0) {
            $q_id = str_replace('dyn_', '', $key);

            // PERBAIKAN: Hanya ambil 'question_text' karena kolom 'type' tidak ada di SELECT
            // Jika butuh tipe, ubah query menjadi: "SELECT question_text, question_type FROM ..."
            $qQuery = mysqli_query($koneksi, "SELECT question_text FROM form_questions WHERE id='$q_id'");

            if ($qQuery && mysqli_num_rows($qQuery) > 0) {
                $qData = mysqli_fetch_assoc($qQuery);
                $questionText = $qData['question_text'];
                $lowerQ = strtolower($questionText);

                $cleanVal = mysqli_real_escape_string($koneksi, $val);
                $answers[$questionText] = $cleanVal;

                // Deteksi kolom utama
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

    // =========================
    // PROSES FILE UPLOAD
    // =========================
    foreach ($_FILES as $key => $file) {
        if (strpos($key, 'dyn_') === 0 && $file['error'] == 0) {
            $q_id = str_replace('dyn_', '', $key);

            $qQuery = mysqli_query($koneksi, "SELECT question_text FROM form_questions WHERE id='$q_id'");
            if ($qQuery && mysqli_num_rows($qQuery) > 0) {
                $qData = mysqli_fetch_assoc($qQuery);
                $questionText = $qData['question_text'];

                $uploadDir = "../uploads/question_file/";

                // Cek dan buat folder jika belum ada
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                // Nama file unik untuk menghindari duplikat
                $newFileName = "file_" . time() . "_" . rand(100, 999) . "." . $ext;
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    // Simpan path relatif ke array answers
                    $answers[$questionText] = "question_file/" . $newFileName;
                } else {
                    // Log error jika upload gagal (opsional)
                    // error_log("Gagal upload file ke: " . $destination);
                }
            }
        }
    }

    // Encode array answers ke JSON
    $jsonAnswers = mysqli_real_escape_string($koneksi, json_encode($answers));

    $sql = "INSERT INTO pendaftaran 
            (nama_lengkap, kelas, jurusan, no_whatsapp, answers) 
            VALUES 
            ('$nama', '$kelas', '$jurusan', '$nohp', '$jsonAnswers')";

    if (mysqli_query($koneksi, $sql)) {
        echo json_encode(['status' => 'success']);
    } else {
        // Kirim pesan error database ke JavaScript
        echo json_encode(['status' => 'error', 'msg' => 'Database Error: ' . mysqli_error($koneksi)]);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Metode request salah.']);
}
