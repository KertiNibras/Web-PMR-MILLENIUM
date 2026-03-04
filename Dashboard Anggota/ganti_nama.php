<?php
session_start();
include '../koneksi.php';

// Cek Login
if (!isset($_SESSION['id'])) {
  echo '<script>alert("Silakan login terlebih dahulu!"); window.location.href="../Login/login.php";</script>';
  exit;
}

 $id_user = $_SESSION['id'];
 $pesan = "";

// Ambil Data User Saat Ini
 $query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id = $id_user");
 $user = mysqli_fetch_assoc($query_user);

// Proses Update Nama
if (isset($_POST['update'])) {
    $nama_baru = mysqli_real_escape_string($koneksi, $_POST['nama']);
    
    if (empty($nama_baru)) {
        $pesan = '<div class="alert alert-danger">Nama tidak boleh kosong!</div>';
    } else {
        // Update Nama di Database
        $update = mysqli_query($koneksi, "UPDATE users SET nama = '$nama_baru' WHERE id = $id_user");
        
        if ($update) {
            // Update Session agar nama langsung berubah di header
            $_SESSION['nama'] = $nama_baru;
            
            // Refresh data user untuk ditampilkan
            $query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id = $id_user");
            $user = mysqli_fetch_assoc($query_user);
            
            $pesan = '<div class="alert alert-success">Nama berhasil diperbarui!</div>';
        } else {
            $pesan = '<div class="alert alert-danger">Gagal mengubah nama. Coba lagi.</div>';
        }
    }
}

// Ambil Foto Profil
 $foto_session = isset($user['foto_profil']) ? $user['foto_profil'] : '';
 $foto_profil = 'https://ui-avatars.com/api/?name='.urlencode($user['nama']).'&background=d90429&color=fff';
if (!empty($foto_session) && file_exists("../foto_profil/" . $foto_session)) {
    $foto_profil = "../foto_profil/" . $foto_session;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Nama Lengkap</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root { --primary: #d90429; --bg: #f8f9fa; --card: #ffffff; --text: #1e293b; --muted: #64748b; --border: #e2e8f0; }
        body { font-family: 'Segoe UI', sans-serif; background: var(--bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card-box { background: var(--card); padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        h2 { margin-bottom: 10px; color: var(--text); }
        p.subtitle { color: var(--muted); margin-bottom: 30px; font-size: 0.9rem; }
        
        .preview-container { width: 80px; height: 80px; border-radius: 50%; overflow: hidden; margin: 0 auto 20px; border: 3px solid var(--primary); }
        .preview-container img { width: 100%; height: 100%; object-fit: cover; }

        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--text); font-size: 0.9rem; }
        
        /* Input Disabled Style */
        .form-control { width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem; transition: 0.3s; outline: none; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(217, 4, 41, 0.1); }
        
        .form-control:disabled { 
            background-color: #f1f5f9; 
            color: #64748b; 
            cursor: not-allowed; 
            border: 1px dashed var(--border);
        }

        .btn-submit { width: 100%; background: var(--primary); color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; font-size: 1rem; }
        .btn-submit:hover { background: #c92a2a; transform: translateY(-2px); }
        
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-size: 0.9rem; text-align: left; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        .btn-back { display: block; margin-top: 20px; color: var(--muted); text-decoration: none; font-size: 0.9rem; }
        .btn-back:hover { color: var(--primary); }
    </style>
</head>
<body>

<div class="card-box">
    <div class="preview-container">
        <img src="<?= $foto_profil ?>" alt="Foto">
    </div>
    <h2>Ganti Nama</h2>
    <p class="subtitle">Ubah nama lengkap yang tampil di sistem.</p>

    <?php if (!empty($pesan)) echo $pesan; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>Username / NIS</label>
            <!-- Username tidak bisa diedit (disabled) -->
            <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
            <small style="color:#999; font-size:0.75rem; display:block; margin-top:5px;">*Username tidak dapat diubah.</small>
        </div>
        
        <div class="form-group">
            <label>Nama Lengkap Baru</label>
            <input type="text" name="nama" class="form-control" required value="<?= htmlspecialchars($user['nama']) ?>" placeholder="Masukkan nama lengkap baru">
        </div>

        <button type="submit" name="update" class="btn-submit">
            <i class="fas fa-save"></i> Simpan Perubahan
        </button>
    </form>

    <a href="anggota.php" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
</div>

</body>
</html>