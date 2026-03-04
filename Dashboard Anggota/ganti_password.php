<?php
session_start();
include '../koneksi.php';

// Cek Login
if (!isset($_SESSION['nama'])) {
  header("Location: ../Login/login.php");
  exit;
}

 $id_user = $_SESSION['id'];
 $nama_user = htmlspecialchars($_SESSION['nama']);
 $role = $_SESSION['role'];

// Logika Penentuan Foto Profil (Sederhanakan untuk halaman ini)
 $foto_session = isset($_SESSION['foto']) ? $_SESSION['foto'] : '';
 $foto_profil = 'https://ui-avatars.com/api/?name=' . urlencode($nama_user) . '&background=d90429&color=fff';
if (!empty($foto_session) && file_exists("../foto_profil/" . $foto_session)) {
  $foto_profil = "../foto_profil/" . $foto_session;
}

// Proses Ganti Password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $current_pass = $_POST['current_password'];
  $new_pass = $_POST['new_password'];
  $confirm_pass = $_POST['confirm_password'];

  // 1. Ambil password hash yang ada di database
  $query = mysqli_query($koneksi, "SELECT password FROM users WHERE id = '$id_user'");
  $data = mysqli_fetch_assoc($query);

  // 2. Verifikasi Password Lama
  if (!password_verify($current_pass, $data['password'])) {
    $error_msg = "Password lama yang Anda masukkan salah!";
  } 
  // 3. Cek apakah password baru cocok dengan konfirmasi
  elseif ($new_pass !== $confirm_pass) {
    $error_msg = "Password baru dan konfirmasi password tidak cocok!";
  } 
  // 4. Cek kekuatan password (min 6 karakter)
  elseif (strlen($new_pass) < 6) {
    $error_msg = "Password baru minimal harus 6 karakter!";
  } 
  // 5. Proses Update
  else {
    $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
    $update = mysqli_query($koneksi, "UPDATE users SET password = '$new_hash' WHERE id = '$id_user'");
    
    if ($update) {
      $success_msg = "Password berhasil diubah! Silakan login ulang untuk keamanan.";
      // Opsional: Bisa langsung logout paksa atau biarkan user login ulang nanti
    } else {
      $error_msg = "Terjadi kesalahan sistem. Gagal mengubah password.";
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ganti Password - PMR Millenium</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="icon" href="../Gambar/logpmi.png" type="image/png">
  <style>
    :root {
      --primary-color: #d90429;
      --primary-hover: #c92a2a;
      --bg-color: #f8f9fa;
      --card-bg: #ffffff;
      --text-color: #1e293b;
      --text-muted: #64748b;
      --border-color: #e2e8f0;
      --shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
      --radius: 12px;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg-color);
      margin: 0;
      display: flex;
      min-height: 100vh;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .form-container {
      background: var(--card-bg);
      padding: 30px 40px;
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      border: 1px solid var(--border-color);
      width: 100%;
      max-width: 450px;
    }

    .header-title {
      text-align: center;
      margin-bottom: 25px;
    }

    .header-title i {
      font-size: 40px;
      color: var(--primary-color);
      margin-bottom: 10px;
    }

    .header-title h2 {
      margin: 0;
      color: var(--text-color);
    }

    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 0.9rem; color: var(--text-color); }
    
    .input-wrapper {
      position: relative;
    }
    
    .input-wrapper input {
      width: 100%;
      padding: 12px 40px 12px 15px;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      font-size: 1rem;
      transition: border-color 0.2s;
      box-sizing: border-box;
    }

    .input-wrapper input:focus { outline: none; border-color: var(--primary-color); }
    
    .toggle-password {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: var(--text-muted);
    }

    .btn-submit {
      width: 100%;
      padding: 12px;
      background: var(--primary-color);
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: background 0.2s;
    }

    .btn-submit:hover { background: var(--primary-hover); }

    .btn-back {
      display: block;
      text-align: center;
      margin-top: 20px;
      color: var(--text-muted);
      text-decoration: none;
      font-size: 0.9rem;
    }
    .btn-back:hover { color: var(--primary-color); }

    .alert {
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 0.9rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .alert-error { background-color: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
    .alert-success { background-color: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
  </style>
</head>
<body>

<div class="form-container">
  <div class="header-title">
    <i class="fa-solid fa-key"></i>
    <h2>Ganti Password</h2>
    <p style="font-size: 0.9rem; color: var(--text-muted);">Pastikan password baru aman dan mudah diingat.</p>
  </div>

  <?php if (isset($error_msg)): ?>
    <div class="alert alert-error">
      <i class="fas fa-exclamation-circle"></i> <?= $error_msg ?>
    </div>
  <?php endif; ?>

  <?php if (isset($success_msg)): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle"></i> <?= $success_msg ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="form-group">
      <label>Password Lama</label>
      <div class="input-wrapper">
        <input type="password" name="current_password" placeholder="Masukkan password lama" required>
        <span class="toggle-password" onclick="toggleInput(this)"><i class="fas fa-eye"></i></span>
      </div>
    </div>

    <div class="form-group">
      <label>Password Baru</label>
      <div class="input-wrapper">
        <input type="password" name="new_password" placeholder="Minimal 6 karakter" required>
        <span class="toggle-password" onclick="toggleInput(this)"><i class="fas fa-eye"></i></span>
      </div>
    </div>

    <div class="form-group">
      <label>Konfirmasi Password Baru</label>
      <div class="input-wrapper">
        <input type="password" name="confirm_password" placeholder="Ulangi password baru" required>
        <span class="toggle-password" onclick="toggleInput(this)"><i class="fas fa-eye"></i></span>
      </div>
    </div>

    <button type="submit" class="btn-submit">Simpan Password</button>
  </form>

  <a href="javascript:history.back()" class="btn-back"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
</div>

<script>
  function toggleInput(icon) {
    const input = icon.previousElementSibling;
    const i = icon.querySelector('i');
    if (input.type === "password") {
      input.type = "text";
      i.classList.remove('fa-eye');
      i.classList.add('fa-eye-slash');
    } else {
      input.type = "password";
      i.classList.remove('fa-eye-slash');
      i.classList.add('fa-eye');
    }
  }
</script>

</body>
</html>