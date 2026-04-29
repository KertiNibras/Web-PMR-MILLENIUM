<?php
session_start();
include 'koneksi.php'; // Sesuaikan path jika perlu (../koneksi.php jika di subfolder)
require('libs/fpdf.php'); // Pastikan path ke library FPDF benar

// Keamanan
if (!isset($_SESSION['nama']) || $_SESSION['role'] != 'pengurus') {
    die("<script>alert('Akses Ditolak'); window.close();</script>");
}

if (!isset($_GET['id'])) die("ID tidak ditemukan.");

 $id = intval($_GET['id']);
 $q = mysqli_query($koneksi, "SELECT * FROM pendaftaran WHERE id='$id'");
 $data = mysqli_fetch_assoc($q);

if (!$data) die("Data tidak ditemukan.");

// Jika belum ada username (belum dikonfirmasi)
if (empty($data['generated_username'])) {
    die("<script>alert('Data akun belum dibuat. Klik Terima dulu!'); window.close();</script>");
}

// Ambil Data
 $nama = $data['nama_lengkap'];
 $kelas = $data['kelas'];
 $user = $data['generated_username'];
 $pass = $data['generated_password'];
 $tanggal = date('d M Y');

// Ambil No HP (Cek kolom dulu, kalau kosong cari di JSON)
 $no_hp = $data['no_hp'];
if(empty($no_hp)) {
    $answers = json_decode($data['answers'], true);
    if(is_array($answers)) {
        foreach($answers as $val) {
            // Heuristik sederhana: cari angka panjang 10-13 digit
            if(preg_match('/^08[0-9]{8,11}$/', $val)) { $no_hp = $val; break; }
        }
    }
}

// Ambil Foto
 $foto_path = 'uploads/question_file/default.jpg'; 
 $answers_arr = json_decode($data['answers'], true);
if (is_array($answers_arr)) {
    foreach ($answers_arr as $val) {
        if (strpos($val, 'question_file/') !== false) {
            $temp_path = 'uploads/' . $val;
            if (file_exists($temp_path)) $foto_path = $temp_path;
        }
    }
}

// ==========================================================
// PROSES BUAT PDF
// ==========================================================
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'KARTU ANGGOTA PMR', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 6, 'MILLENNIUM', 0, 1, 'C');
        $this->Ln(5);
    }
}

 $pdf = new PDF('L', 'mm', array(100, 60)); // Ukuran ID Card
 $pdf->AddPage();
 $pdf->SetFont('Arial', '', 10);

// Layout Foto
 $pdf->Rect(10, 30, 25, 30); // Kotak foto
 $ext = pathinfo($foto_path, PATHINFO_EXTENSION);
if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']) && file_exists($foto_path)) {
    $pdf->Image($foto_path, 10, 30, 25, 30);
} else {
    $pdf->SetXY(10, 42); $pdf->Cell(25, 5, 'NO PHOTO', 0, 0, 'C');
}

// Layout Data
 $pdf->SetXY(40, 30);
 $pdf->SetFont('Arial', 'B', 12);
 $pdf->Cell(50, 7, strtoupper($nama), 0, 2);
 $pdf->SetFont('Arial', '', 10);
 $pdf->Cell(50, 6, 'Kelas: ' . $kelas, 0, 2);

 $pdf->Ln(5);
 $pdf->SetFillColor(240, 240, 240);
 $pdf->Rect(40, $pdf->GetY(), 50, 15, 'F');
 $pdf->SetFont('Arial', 'B', 9);
 $pdf->SetX(42);
 $pdf->Cell(40, 5, 'Akun Login:', 0, 2);
 $pdf->SetFont('Arial', '', 9);
 $pdf->SetX(42);
 $pdf->Cell(40, 5, 'User: ' . $user, 0, 2);
 $pdf->SetX(42);
 $pdf->Cell(40, 5, 'Pass: ' . $pass, 0, 2);

// Simpan File
 $filename = 'kartu_'.$nama.'.pdf';
 $filepath = 'uploads/kartu/'.$filename;
if (!is_dir('uploads/kartu')) mkdir('uploads/kartu', 0777, true);
 $pdf->Output('F', $filepath);

// ==========================================================
// KIRIM WHATSAPP
// ==========================================================
 $pesan = "Halo *$nama*, selamat pendaftaran PMR Anda diterima!\n\nBerikut akun login Anda:\nUsername: *$user*\nPassword: *$pass*\n\nDownload Kartu Anggota:";
 $wa_link = "https://wa.me/".preg_replace('/[^0-9]/', '', $no_hp)."?text=".urlencode($pesan);

// Eksekusi JS
echo "<script>
        var link = document.createElement('a');
        link.href = '".$filepath."';
        link.download = '".$filename."';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.location.href = '".$wa_link."';
      </script>";
?>