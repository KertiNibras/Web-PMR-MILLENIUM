<?php
session_start();
include '../koneksi.php';

// Cek Login & Role (Keamanan)
if (!isset($_SESSION['nama']) || $_SESSION['role'] != 'pengurus') {
    die("Akses ditolak.");
}

// Ambil parameter tanggal
 $start_date = isset($_GET['start']) ? $_GET['start'] : date('Y-m-01');
 $end_date = isset($_GET['end']) ? $_GET['end'] : date('Y-m-d');

// Query Data Absensi
 $sql = "SELECT a.tanggal, a.jam, u.nama, u.kelas, a.status 
        FROM absensi a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.tanggal BETWEEN '$start_date' AND '$end_date' 
        ORDER BY a.tanggal DESC, a.jam ASC";
 $result = mysqli_query($koneksi, $sql);

// --- PENTING: SET PATH FONT DULU SEBELUM LOAD LIBRARY ---
define('FPDF_FONTPATH', 'assets/fpdf/font/');

// --- PENTING: LOAD FILE LIBRARY ---
require('assets/fpdf/fpdf.php');

// Fungsi helper untuk format tanggal Indonesia
function format_tanggal($tanggal_mysql) {
    $bulan = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $split = explode('-', $tanggal_mysql);
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
}

class PDF extends FPDF {
    // Header Laporan (Kop Surat)
    function Header() {
        // --- BAGIAN KOP SURAT ---
        
        // Variabel posisi
        $logo_height = 22; // Tinggi logo
        $y_pos = 5;        // Posisi vertikal mulai

        // 1. Logo Kiri (Sekolah)
        $logo_kiri = '../Gambar/kampak.png';
        if (file_exists($logo_kiri)) {
            $this->Image($logo_kiri, 10, $y_pos, 22, $logo_height);
        } else {
            // Fallback jika tidak ada
            if (file_exists('../Gambar/logpmi.png')) {
                 $this->Image('../Gambar/logpmi.png', 10, $y_pos, 22, $logo_height);
            }
        }

        // 2. Logo Kanan (PMI/PMR)
        // Posisi x = 210 (lebar kertas) - 10 (margin) - 22 (lebar logo)
        $logo_kanan = 'assets/img/logopmi.png'; // Sesuaikan path logo PMI/PMR Anda
        if (file_exists($logo_kanan)) {
            $this->Image($logo_kanan, 178, $y_pos, 22, $logo_height);
        } else {
            // Fallback ke logo PMR di folder Gambar jika ada
             if (file_exists('../Gambar/logpmi.png')) {
                 $this->Image('../Gambar/logpmi.png', 178, $y_pos, 22, $logo_height);
            }
        }

        // 3. Identitas Sekolah (Tengah)
        // Lebar kertas A4 = 210mm. Kita set lebar text area = 150mm, sehingga margin kiri = 30mm
        $this->SetY($y_pos + 2); // Sedikit turun dari atas
        $this->SetX(30); 
        
        $this->SetFont('Times', 'B', 14);
        $this->Cell(150, 6, 'SMK NEGERI 1 CIBINONG', 0, 1, 'C');
        
        $this->SetX(30);
        $this->SetFont('Times', '', 11);
        $this->Cell(150, 5, 'JL. RAYA CIBINONG NO. 123, KOTA CIBINONG', 0, 1, 'C');
        
        $this->SetX(30);
        $this->SetFont('Times', '', 9);
        $this->Cell(150, 5, 'Telp: (021) 1234567 | Email: info@sekolah.sch.id', 0, 1, 'C');

        // 4. Garis Pembatas Kop (Ganda)
        // SetY ke bawah sedikit agar tidak menimpa logo
        $this->SetY($y_pos + $logo_height + 2); 
        
        $this->SetLineWidth(1);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->SetLineWidth(0.2);
        $this->Line(10, $this->GetY()+1, 200, $this->GetY()+1);
        
        $this->Ln(6); // Spasi setelah garis

        // --- JUDUL LAPORAN ---
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 6, 'LAPORAN REKAP ABSENSI PMR MILLENIUM', 0, 1, 'C');
        
        $this->SetFont('Arial', '', 10);
        global $start_date, $end_date;
        $periode_text = 'Periode: ' . format_tanggal($start_date) . ' s/d ' . format_tanggal($end_date);
        $this->Cell(0, 5, $periode_text, 0, 1, 'C');
        $this->Ln(4);

        // --- HEADER TABEL ---
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(230, 230, 230);
        $this->SetTextColor(0, 0, 0);
        
        // Lebar Kolom: 10+60+25+35+20+40 = 190
        $this->Cell(10, 7, 'No', 1, 0, 'C', true);
        $this->Cell(60, 7, 'Nama Lengkap', 1, 0, 'C', true);
        $this->Cell(25, 7, 'Kelas', 1, 0, 'C', true);
        $this->Cell(35, 7, 'Tanggal', 1, 0, 'C', true);
        $this->Cell(20, 7, 'Jam', 1, 0, 'C', true);
        $this->Cell(40, 7, 'Status', 1, 1, 'C', true);
        
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 9);
    }

    // Footer
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo(), 0, 0, 'C');
    }
}

 $pdf = new PDF();
 $pdf->AddPage();
 $pdf->SetFont('Arial', '', 9);

 $no = 1;
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if ($no % 2 == 0) {
            $pdf->SetFillColor(248, 248, 248); 
        } else {
            $pdf->SetFillColor(255, 255, 255); 
        }

        $pdf->Cell(10, 6, $no++, 1, 0, 'C', true);
        $pdf->Cell(60, 6, $row['nama'], 1, 0, 'L', true);
        $pdf->Cell(25, 6, $row['kelas'], 1, 0, 'C', true);
        $tgl_format = format_tanggal($row['tanggal']);
        $pdf->Cell(35, 6, $tgl_format, 1, 0, 'C', true);
        $pdf->Cell(20, 6, $row['jam'], 1, 0, 'C', true);
        $pdf->Cell(40, 6, ucfirst($row['status']), 1, 1, 'C', true);
    }
} else {
    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, 'Tidak ada data pada periode ini.', 0, 1, 'C');
}

 $pdf->Output('I', "Laporan_Absensi_{$start_date}.pdf");
?>