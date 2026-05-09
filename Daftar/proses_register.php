<?php
// Matikan display error agar tidak merusak output JSON
ini_set('display_errors', 0);

// Aktifkan Session untuk fitur Anti-Spam
session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/../koneksi.php';

// Cek koneksi
if (!$koneksi) {
    echo json_encode(['status' => 'error', 'msg' => 'Koneksi database gagal.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // =========================
    // FITUR ANTI SPAM (Jeda 60 Detik)
    // =========================
    if (isset($_SESSION['last_submit'])) {
        $waktu_lalu = $_SESSION['last_submit'];
        $waktu_sekarang = time();
        $selisih = $waktu_sekarang - $waktu_lalu;
        
        if ($selisih < 60) {
            $tunggu = 60 - $selisih;
            echo json_encode(['status' => 'error', 'msg' => "Sistem Anti-Spam: Harap tunggu $tunggu detik lagi sebelum mendaftar ulang."]);
            exit;
        }
    }

    $nama = "Tidak Diketahui";
    $kelas = "-";
    $jurusan = "-";
    $nohp = "-";

    $answers = [];

    // =========================
    // PROSES INPUT TEXT / SELECT / RADIO / CHECKBOX
    // =========================
    foreach ($_POST as $key => $val) {
        if (strpos($key, 'dyn_') === 0) {
            $q_id = str_replace('dyn_', '', $key);

            $qQuery = mysqli_query($koneksi, "SELECT question_text FROM form_questions WHERE id='$q_id'");

            if ($qQuery && mysqli_num_rows($qQuery) > 0) {
                $qData = mysqli_fetch_assoc($qQuery);
                $questionText = $qData['question_text'];
                $lowerQ = strtolower($questionText);

                // --- Penambal Array (Biar gak error pas pilih checkbox) ---
                if (is_array($val)) {
                    $val = implode(', ', $val);
                }

                $cleanVal = mysqli_real_escape_string($koneksi, $val);
                $answers[$questionText] = $cleanVal;

                // Deteksi kolom utama
                if (strpos($lowerQ, 'nama') !== false) {
                    $nama = $cleanVal;
                } elseif (strpos($lowerQ, 'kelas') !== false) {
                    $kelas = $cleanVal;
                } elseif (strpos($lowerQ, 'jurusan') !== false) {
                    $jurusan = $cleanVal;
                } elseif (strpos($lowerQ, 'whatsapp') !== false || strpos($lowerQ, 'nomor') !== false || strpos($lowerQ, 'no hp') !== false || strpos($lowerQ, 'hp') !== false) {
                    $nohp = $cleanVal;
                }
            }
        }
    }

    // =========================
    // PROSES FILE UPLOAD (TANPA VALIDASI UKURAN/TIPE)
    // =========================
    foreach ($_FILES as $key => $file) {
        if (strpos($key, 'dyn_') === 0 && $file['error'] == 0) {
            $q_id = str_replace('dyn_', '', $key);

            $qQuery = mysqli_query($koneksi, "SELECT question_text FROM form_questions WHERE id='$q_id'");
            if ($qQuery && mysqli_num_rows($qQuery) > 0) {
                $qData = mysqli_fetch_assoc($qQuery);
                $questionText = $qData['question_text'];

                $uploadDir = "../uploads/question_file/";

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

    // Encode array answers ke JSON
    $jsonAnswers = mysqli_real_escape_string($koneksi, json_encode($answers));

    $sql = "INSERT INTO pendaftaran 
            (nama_lengkap, kelas, jurusan, no_whatsapp, answers) 
            VALUES 
            ('$nama', '$kelas', '$jurusan', '$nohp', '$jsonAnswers')";

    if (mysqli_query($koneksi, $sql)) {
        // Catat waktu sukses daftar biar gak bisa spam
        $_SESSION['last_submit'] = time();
        
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Database Error: ' . mysqli_error($koneksi)]);
    }
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Metode request salah.']);
}