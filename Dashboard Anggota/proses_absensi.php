<?php
session_start();
include '../koneksi.php';

// ==========================================
// 1. VALIDASI SESI
// ==========================================
if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi tidak valid, silakan login ulang.']);
    exit;
}

// ==========================================
// 2. VALIDASI WAKTU ABSENSI (REALTIME)
// ==========================================
// Cek apakah pengurus membuka absensi saat ini
 $now_time = date('H:i:s');
 $now_date = date('Y-m-d');

// Query cek ke tabel pengaturan_absensi
 $cek_jadwal = mysqli_query($koneksi, "SELECT id FROM pengaturan_absensi 
                                      WHERE tanggal = '$now_date' 
                                      AND status = 1 
                                      AND waktu_mulai <= '$now_time' 
                                      AND waktu_selesai >= '$now_time'");

if (mysqli_num_rows($cek_jadwal) == 0) {
    echo json_encode(['status' => 'error', 'message' => 'Absensi saat ini ditutup atau belum dibuka.']);
    exit;
}

// ==========================================
// 3. VALIDASI DUPLIKAT ABSENSI HARI INI
// ==========================================
 $user_id = $_SESSION['id'];
 $cek_absen = mysqli_query($koneksi, "SELECT id FROM absensi WHERE user_id = '$user_id' AND tanggal = '$now_date'");
if (mysqli_num_rows($cek_absen) > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Anda sudah melakukan absensi hari ini.']);
    exit;
}

// ==========================================
// 4. AMBIL & PROSES DATA INPUT
// ==========================================
 $input = json_decode(file_get_contents('php://input'), true);
 $photoData = $input['foto'] ?? '';

// Validasi Foto
if (empty($photoData)) {
    echo json_encode(['status' => 'error', 'message' => 'Foto wajib diambil.']);
    exit;
}

// Proses Simpan Gambar
 $fileName = '';
 $image_parts = explode(";base64,", $photoData);

if (count($image_parts) == 2) {
    $image_type_aux = explode("image/", $image_parts[0]);
    $image_type = $image_type_aux[1]; // png, jpeg, dll
    $image_base64 = base64_decode($image_parts[1]);

    $allowed_types = ['png', 'jpeg', 'jpg', 'webp'];
    if (!in_array($image_type, $allowed_types)) {
        echo json_encode(['status' => 'error', 'message' => 'Format gambar tidak didukung.']);
        exit;
    }

    // Nama file unik: absen_iduser_timestamp.ext
    $fileName = 'absen_' . $user_id . '_' . time() . '.' . $image_type;
    
    // Pastikan folder uploads/absensi ada
    if (!is_dir('../uploads/absensi')) {
        mkdir('../uploads/absensi', 0777, true);
    }

    $filePath = '../uploads/absensi/' . $fileName;
    file_put_contents($filePath, $image_base64);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Data gambar tidak valid.']);
    exit;
}

// ==========================================
// 5. SIMPAN KE DATABASE
// ==========================================
// Data otomatis sesuai permintaan:
// - Status: 'hadir' (otomatis)
// - Keterangan: '-' (kosong/default)
// - Kegiatan: Dihapus atau diisi default 'Absensi Harian'

 $status_default = 'hadir';
 $keterangan_default = '-'; // Atau bisa dikosongkan ''
 $kegiatan_default = 'Absensi Harian'; // Default jika kolom di DB masih ada

// Gunakan mysqli_real_escape_string untuk keamanan
 $safe_user_id = mysqli_real_escape_string($koneksi, $user_id);
 $safe_tanggal = mysqli_real_escape_string($koneksi, $now_date);
 $safe_jam = mysqli_real_escape_string($koneksi, $now_time);
 $safe_fileName = mysqli_real_escape_string($koneksi, $fileName);
 $safe_status = mysqli_real_escape_string($koneksi, $status_default);
 $safe_keterangan = mysqli_real_escape_string($koneksi, $keterangan_default);
 $safe_kegiatan = mysqli_real_escape_string($koneksi, $kegiatan_default);

// Query Insert (Sesuaikan kolom 'kegiatan' jika masih ada di tabel DB)
 $query = "INSERT INTO absensi (user_id, tanggal, jam, foto, status, keterangan, kegiatan) 
          VALUES ('$safe_user_id', '$safe_tanggal', '$safe_jam', '$safe_fileName', '$safe_status', '$safe_keterangan', '$safe_kegiatan')";

if (mysqli_query($koneksi, $query)) {
    echo json_encode(['status' => 'success', 'message' => 'Absensi berhasil dicatat!']);
} else {
    // Log error jika perlu
    echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan sistem.']);
}
?>