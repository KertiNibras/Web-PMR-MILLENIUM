<?php
include '../koneksi.php';
// Sesuaikan path ke library FPDF Anda
require('assets/fpdf/fpdf.php');

if(!isset($_GET['id'])) die("ID tidak ada");

$id = intval($_GET['id']);
$q = mysqli_query($koneksi, "SELECT * FROM pendaftaran WHERE id='$id'");
$d = mysqli_fetch_assoc($q);

if(!$d) die("Data tidak ditemukan");

// ================= AMBIL & OLAH DATA =================
$nama = $d['nama_lengkap'];

// Sihir PHP: Menghapus angka romawi (X, XI, XII) di awal teks kelas otomatis!
// Jadi "XI RPL 1" otomatis berubah jadi "RPL 1"
$kelas_asli = $d['kelas'];
$kelas_bersih = trim(preg_replace('/^(X|XI|XII)\s+/i', '', $kelas_asli));

// Set Angkatan (Sementara di-hardcode 26, sesuaikan jika ada di database)
$angkatan = "26"; 

$username = $d['generated_username'];
$password = $d['generated_password'];

// Cari Foto dari JSON
$answers = json_decode($d['answers'], true);
$foto_path = '';
if(is_array($answers)) {
    foreach($answers as $key => $val) {
        if(stripos($key, 'foto') !== false && strpos($val, 'question_file/') !== false) {
            $foto_path = "../uploads/" . $val;
            break;
        }
    }
}

// ================= MULAI BUAT PDF =================
$pdf = new FPDF('L', 'mm', array(86, 54));
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

// 1. Background Dasar Kartu (Putih Bersih)
$pdf->SetFillColor(255, 255, 255);
$pdf->Rect(0, 0, 86, 54, 'F');
$pdf->SetDrawColor(220, 220, 220);
$pdf->Rect(0, 0, 86, 54, 'D'); 

// 2. Header
$logo_sekolah = '../Gambar/kampak.png';
if(file_exists($logo_sekolah)) {
    $pdf->Image($logo_sekolah, 3, 2, 10); 
}

$logo_pmi = '../Gambar/logpmi.png';
if(file_exists($logo_pmi)) {
    $pdf->Image($logo_pmi, 73, 2, 10); 
}

// Teks Header di Tengah
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(217, 4, 41); 
$pdf->SetXY(13, 3); 
$pdf->Cell(60, 5, 'KARTU ANGGOTA PMR', 0, 1, 'C');

$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(0, 0, 0); 
$pdf->SetXY(13, 8);
$pdf->Cell(60, 4, 'SMK NEGERI 1 CIBINONG', 0, 1, 'C');

// Garis Pembatas Header 
$pdf->SetDrawColor(217, 4, 41);
$pdf->SetLineWidth(0.5);
$pdf->Line(3, 14, 83, 14);
$pdf->SetLineWidth(0.2); 

// 3. Area Foto Profil (Kiri)
$x_foto = 5;
$y_foto = 18; 
$w_foto = 21;
$h_foto = 28;

// Bingkai Foto 
$pdf->SetDrawColor(217, 4, 41);
$pdf->SetLineWidth(0.5);
$pdf->Rect($x_foto, $y_foto, $w_foto, $h_foto, 'D');
$pdf->SetLineWidth(0.2); 

// Render Foto
if(file_exists($foto_path) && !is_dir($foto_path)) {
    $ext = strtolower(pathinfo($foto_path, PATHINFO_EXTENSION));
    if($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg') {
        $pdf->Image($foto_path, $x_foto + 0.5, $y_foto + 0.5, $w_foto - 1, $h_foto - 1);
    }
} else {
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetFont('Arial', '', 6);
    $pdf->SetXY($x_foto, $y_foto + 12);
    $pdf->Cell($w_foto, 5, 'NO PHOTO', 0, 0, 'C');
}

// 4. Area Data Teks (Kanan)
$x_text = 29;

// Nama Anggota
$pdf->SetTextColor(0, 0, 0); 
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetXY($x_text, 17);
$pdf->Cell(52, 4, strtoupper(substr($nama, 0, 25)), 0, 1, 'L'); 

// Kelas (Tanpa angka romawi)
$pdf->SetFont('Arial', '', 7);
$pdf->SetXY($x_text, 21);
$pdf->Cell(52, 4, 'Kelas      : ' . $kelas_bersih, 0, 1, 'L');

// Angkatan
$pdf->SetXY($x_text, 25);
$pdf->Cell(52, 4, 'Angkatan : ' . $angkatan, 0, 1, 'L');

// Garis Pemisah Halus
$pdf->SetDrawColor(220, 220, 220);
$pdf->Line($x_text, 30, $x_text + 52, 30);

// Judul Akses Login
$pdf->SetTextColor(217, 4, 41);
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY($x_text, 31);
$pdf->Cell(52, 4, 'Akses Login Website:', 0, 1, 'L');

// 5. Kotak Informasi Login 
$pdf->SetFillColor(245, 245, 245);
$pdf->Rect($x_text, 35, 52, 13, 'F');

// Username
$pdf->SetTextColor(50, 50, 50);
$pdf->SetFont('Arial', '', 7); 
$pdf->SetXY($x_text + 2, 36);
$pdf->Cell(15, 4, 'Username', 0, 0, 'L');
$pdf->Cell(33, 4, ': ' . $username, 0, 1, 'L');

// Password
$pdf->SetXY($x_text + 2, 41);
$pdf->Cell(15, 4, 'Password', 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 7); 
$pdf->Cell(33, 4, ': ' . $password, 0, 1, 'L');

// 6. Footer Bawah 
$pdf->SetFillColor(217, 4, 41);
$pdf->Rect(0, 51, 86, 3, 'F');

$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'I', 5);
$pdf->SetXY(0, 51);
$pdf->Cell(86, 3, 'Kartu ini bersifat rahasia dan dicetak otomatis oleh sistem.', 0, 0, 'C');

// Tampilkan PDF
$pdf->Output('I', 'Kartu_'.$nama.'.pdf');
?>