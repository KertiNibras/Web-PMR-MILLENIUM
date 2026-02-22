<?php
session_start();
include '../koneksi.php';

// Validasi Login
if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi tidak valid.']);
    exit;
}

// Ambil data JSON dari JS
 $input = json_decode(file_get_contents('php://input'), true);
 $kegiatan = $input['kegiatan'] ?? '';
 $photoData = $input['foto'] ?? '';

// Ambil status dan keterangan
 $status = $input['status'] ?? 'hadir';
 $keterangan = $input['keterangan'] ?? '';

if (empty($kegiatan)) {
    echo json_encode(['status' => 'error', 'message' => 'Kegiatan wajib diisi.']);
    exit;
}

// Proses Simpan Gambar
 $fileName = '';

if (!empty($photoData)) {
    $image_parts = explode(";base64,", $photoData);

    if (count($image_parts) == 2) {
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);

        $allowed_types = ['png', 'jpeg', 'jpg', 'webp'];
        if (!in_array($image_type, $allowed_types)) {
            echo json_encode(['status' => 'error', 'message' => 'Format gambar tidak didukung.']);
            exit;
        }

        $fileName = 'absen_' . time() . '_' . $_SESSION['id'] . '.' . $image_type;
        
        // Pastikan folder ada
        if (!is_dir('../uploads/absensi')) {
            mkdir('../uploads/absensi', 0777, true);
        }

        $filePath = '../uploads/absensi/' . $fileName;
        file_put_contents($filePath, $image_base64);
    }
}

// --- PROSES DATA UNTUK DATABASE ---

 $user_id = $_SESSION['id'];
 $tanggal = date('Y-m-d');
 $jam = date('H:i:s');

// Logika Keterangan (Jika kosong, isi default)
if ($status !== 'hadir' && empty($keterangan)) {
    $keterangan = ucfirst($status); 
}

// ==========================================
// PERBAIKAN UTAMA: SANITASI DATA
// ==========================================
// Gunakan mysqli_real_escape_string agar aman dari karakter khusus (seperti tanda petik)
 $safe_user_id = mysqli_real_escape_string($koneksi, $user_id);
 $safe_kegiatan = mysqli_real_escape_string($koneksi, $kegiatan);
 $safe_status = mysqli_real_escape_string($koneksi, $status);
 $safe_keterangan = mysqli_real_escape_string($koneksi, $keterangan);
 $safe_fileName = mysqli_real_escape_string($koneksi, $fileName);
 $safe_tanggal = mysqli_real_escape_string($koneksi, $tanggal);
 $safe_jam = mysqli_real_escape_string($koneksi, $jam);

 $query = "INSERT INTO absensi (user_id, kegiatan, tanggal, jam, foto, status, keterangan) 
          VALUES ('$safe_user_id', '$safe_kegiatan', '$safe_tanggal', '$safe_jam', '$safe_fileName', '$safe_status', '$safe_keterangan')";

if (mysqli_query($koneksi, $query)) {
    echo json_encode(['status' => 'success', 'message' => 'Absensi berhasil disimpan.']);
} else {
    // Jangan tampilkan mysqli_error di produksi, cukup di log saja.
    echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan ke database.']);
}
?>