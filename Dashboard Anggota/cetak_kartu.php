<?php
require_once __DIR__ . '/../koneksi.php';
// Sesuaikan path ke library FPDF Anda
require('assets/fpdf/fpdf.php');

if(!isset($_GET['id'])) die("ID tidak ada");

$id = intval($_GET['id']);
$q = mysqli_query($koneksi, "SELECT * FROM pendaftaran WHERE id='$id'");
$d = mysqli_fetch_assoc($q);

if(!$d) die("Data tidak ditemukan");

// ================= AMBIL & OLAH DATA =================

// 1. LOGIKA NAMA: Singkat otomatis jika kepanjangan
$nama_asli = trim($d['nama_lengkap']);
$words = explode(' ', $nama_asli);

// Jika namanya lebih dari 2 kata dan panjang karakternya lebih dari 20 huruf
if (count($words) > 2 && strlen($nama_asli) > 20) {
    $nama_cetak = $words[0] . ' ' . $words[1]; // Ambil 2 kata pertama utuh
    for ($i = 2; $i < count($words); $i++) {
        $nama_cetak .= ' ' . strtoupper(substr($words[$i], 0, 1)) . '.'; // Sisa kata disingkat huruf depannya
    }
} else {
    $nama_cetak = $nama_asli;
}
// Batas aman maksimal 26 karakter agar tidak merusak layout
$nama_cetak = strtoupper(substr($nama_cetak, 0, 26)); 

// 2. LOGIKA KELAS: Hapus angka romawi (X, XI, XII)
$kelas_asli = $d['kelas'];
// Regex ini akan mencari X, XI, XII di awal kata dan menghapusnya
$kelas_bersih = trim(preg_replace('/^(X|XI|XII)\s+/i', '', $kelas_asli));
$jurusan = isset($d['jurusans']) ? $d['jurusans'] : ''; 

// Gabungkan Kelas dan Jurusan jika perlu
$teks_kelas = $kelas_bersih;
if (!empty($jurusan) && strpos($kelas_bersih, $jurusan) === false) {
    $teks_kelas .= ' ' . $jurusan;
}

$username = $d['generated_username'];
$password = $d['generated_password'];

// 3. LOGIKA FOTO: Cari dari JSON
$answers = json_decode($d['answers'], true);
$foto_path = '';
if(is_array($answers)) {
    foreach($answers as $key => $val) {
        // Sistem akan mencari pertanyaan yang ada kata "foto"-nya
        if(stripos($key, 'foto') !== false && strpos($val, 'question_file/') !== false) {
            $foto_path = "../uploads/" . $val;
            break;
        }
    }
}

// ================= MULAI BUAT PDF =================
// Buat PDF Landscape (Ukuran Kartu ID Standar: 86mm x 54mm)
$pdf = new FPDF('L', 'mm', array(86, 54));
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

// Background Dasar Kartu (Putih Bersih)
$pdf->SetFillColor(255, 255, 255);
$pdf->Rect(0, 0, 86, 54, 'F');
// Border luar abu-abu tipis sebagai batas potong kartu
$pdf->SetDrawColor(220, 220, 220);
$pdf->Rect(0, 0, 86, 54, 'D'); 

// Header (Tanpa Blok Merah)
// Tambahkan Logo Sekolah (Kampak) di kiri atas
$logo_sekolah = '../Gambar/kampak.png';
if(file_exists($logo_sekolah)) {
    $pdf->Image($logo_sekolah, 3, 2, 10); 
}

// Tambahkan Logo PMI di kanan atas
$logo_pmi = '../Gambar/logpmi.png';
if(file_exists($logo_pmi)) {
    $pdf->Image($logo_pmi, 73, 2, 10); 
}

// Teks Header di Tengah
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetTextColor(217, 4, 41); // Teks Berwarna Merah PMR
$pdf->SetXY(13, 3); 
$pdf->Cell(60, 5, 'KARTU ANGGOTA PMR', 0, 1, 'C');

$pdf->SetFont('Arial', '', 7);
$pdf->SetTextColor(0, 0, 0); // Teks Hitam
$pdf->SetXY(13, 8);
$pdf->Cell(60, 4, 'SMK NEGERI 1 CIBINONG', 0, 1, 'C');

// Garis Pembatas Header (Garis Merah Tipis di bawah logo)
$pdf->SetDrawColor(217, 4, 41); // Warna Merah
$pdf->SetLineWidth(0.5);
$pdf->Line(3, 14, 83, 14);
$pdf->SetLineWidth(0.2); // Kembalikan ketebalan garis normal

// Area Foto Profil (Kiri)
$x_foto = 5;
$y_foto = 18; 
$w_foto = 21;
$h_foto = 28;

// Bingkai Foto (Garis Merah)
$pdf->SetDrawColor(217, 4, 41);
$pdf->SetLineWidth(0.5);
$pdf->Rect($x_foto, $y_foto, $w_foto, $h_foto, 'D');
$pdf->SetLineWidth(0.2); 

// Render Foto
if(file_exists($foto_path) && !is_dir($foto_path)) {
    $ext = strtolower(pathinfo($foto_path, PATHINFO_EXTENSION));
    if($ext == 'png' || $ext == 'jpg' || $ext == 'jpeg') {
        // Foto di-render sedikit lebih kecil agar masuk ke dalam bingkai
        $pdf->Image($foto_path, $x_foto + 0.5, $y_foto + 0.5, $w_foto - 1, $h_foto - 1);
    }
} else {
    // Kalau foto tidak ada / error
    $pdf->SetTextColor(150, 150, 150);
    $pdf->SetFont('Arial', '', 6);
    $pdf->SetXY($x_foto, $y_foto + 12);
    $pdf->Cell($w_foto, 5, 'NO PHOTO', 0, 0, 'C');
}

// Area Data Teks (Kanan)
$x_text = 29;
$y_text_start = 18;

// Nama Anggota (Menggunakan Variabel yang sudah disingkat otomatis)
$pdf->SetTextColor(0, 0, 0); 
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetXY($x_text, $y_text_start);
$pdf->Cell(52, 5, $nama_cetak, 0, 1, 'L'); 

// Kelas (Tanpa romawi)
$pdf->SetFont('Arial', '', 7);
$pdf->SetXY($x_text, $y_text_start + 5);
$pdf->Cell(52, 4, 'Kelas : ' . $teks_kelas, 0, 1, 'L');

// Garis Pemisah Halus
$pdf->SetDrawColor(220, 220, 220);
$pdf->Line($x_text, $y_text_start + 10, $x_text + 52, $y_text_start + 10);

// Judul Akses Login
$pdf->SetTextColor(217, 4, 41); // Merah
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetXY($x_text, $y_text_start + 11);
$pdf->Cell(52, 4, 'Akses Login Website:', 0, 1, 'L');

// Kotak Informasi Login (Abu-abu muda)
$pdf->SetFillColor(245, 245, 245);
$pdf->Rect($x_text, $y_text_start + 15, 52, 13, 'F');

// Username
$pdf->SetTextColor(50, 50, 50);
$pdf->SetFont('Arial', '', 7); 
$pdf->SetXY($x_text + 2, $y_text_start + 16);
$pdf->Cell(15, 4, 'Username', 0, 0, 'L');
$pdf->Cell(33, 4, ': ' . $username, 0, 1, 'L');

// Password
$pdf->SetXY($x_text + 2, $y_text_start + 21);
$pdf->Cell(15, 4, 'Password', 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 7); // Password di-bold
$pdf->Cell(33, 4, ': ' . $password, 0, 1, 'L');

// Footer Bawah (Garis Merah)
$pdf->SetFillColor(217, 4, 41);
$pdf->Rect(0, 51, 86, 3, 'F');

$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'I', 5);
$pdf->SetXY(0, 51);
$pdf->Cell(86, 3, 'Kartu ini bersifat rahasia dan dicetak otomatis oleh sistem.', 0, 0, 'C');

// Tampilkan PDF
$pdf->Output('I', 'Kartu_'.$nama_asli.'.pdf');
?>