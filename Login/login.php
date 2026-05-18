<?php
session_start();
// Sesuaikan path koneksi sesuai struktur folder Anda
require_once __DIR__ . '/../koneksi.php';

// --- VARIABEL STATUS & PESAN ---
 $status = null;
 $displayUsername = "";
 $redirectLink = "";
 $foto_profil = ""; 

// --- LOGIKA LOGIN ---
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $sql    = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $sql);

    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);

        // --- CARA AMAN VERIFIKASI PASSWORD DENGAN HASH ---
        if (password_verify($password, $data['password'])) {
            
            // Simpan data penting ke Session
            $_SESSION['id'] = $data['id'];
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['role'] = $data['role'];
            $_SESSION['username'] = $data['username'];
            
            // SESUAI DATABASE: Kolomnya bernama 'foto_profil'
            $foto_db = $data['foto_profil'] ?? '';
            $_SESSION['foto'] = $foto_db;
            
            // Simpan first_login ke session untuk Welcome Screen di anggota.php
            $_SESSION['first_login'] = $data['first_login'] ?? 0;

            // Set variabel untuk tampilan berhasil
            $status = 'success';
            $displayUsername = $data['nama'];
            $redirectLink = '../Dashboard Anggota/anggota.php';

            // --- LOGIKA FOTO PROFIL (ROBUST) ---
            
            // Tentukan path dasar (absolute path lebih aman)
            $baseDir = realpath(__DIR__ . '/../'); // Menuju folder root project
            $filePath = $baseDir . '/uploads/foto_profil/' . $foto_db;

            // Cek jika ada nama file di DB dan file tersebut benar-benar ada
            if (!empty($foto_db) && file_exists($filePath)) {
                // Path untuk tampilan di browser (relative ke root web)
                $foto_profil = "../uploads/foto_profil/" . htmlspecialchars($foto_db);
            } else {
                // Avatar Default via UI Avatars
                $foto_profil = 'https://ui-avatars.com/api/?name=' . urlencode($data['nama']) . '&background=d90429&color=fff';
            }
        } else {
            $status = 'error'; // Password salah
        }
    } else {
        $status = 'error'; // Username tidak ditemukan
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PMR Millenium</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
     <link rel="icon" href="../Gambar/logpmi.png" type="image/png">

    <style>
        /* --- CSS VARIABLES (Modern & Clean) --- */
        :root {
            --primary-color: #d90429;
            --primary-hover: #b80d24;
            --bg-color: #f1f5f9;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius: 16px;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            --success-bg: #dcfce7;
            --success-icon: #16a34a;
            --error-bg: #fee2e2;
            --error-icon: #dc2626;
            --wa-color: #25D366; /* Warna WhatsApp */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: var(--text-main);
            /* Gradient background halus */
            background-image: linear-gradient(120deg, #fdfbfb 0%, #ebedee 100%);
        }

        .login-card {
            background-color: var(--card-bg);
            width: 100%;
            max-width: 400px;
            padding: 40px 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            text-align: center;
            animation: fadeIn 0.6s ease-out;
            position: relative;
            overflow: hidden;
        }
        
        /* Decorative top bar */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary-color);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
        }

        .logo img {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }

        h2 {
            font-size: 1.5rem;
            margin-bottom: 8px;
            font-weight: 700;
            color: var(--text-main);
        }

        .subtitle {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .field {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 8px;
            margin-left: 2px;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            fill: var(--text-muted);
            pointer-events: none;
            transition: fill 0.3s;
        }

        input {
            width: 100%;
            padding: 14px 14px 14px 44px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 0.95rem;
            color: var(--text-main);
            transition: all 0.3s ease;
            background-color: #f8fafc;
        }
        
        input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(217, 4, 41, 0.1);
            background-color: #fff;
        }

        input:focus + .input-icon {
            fill: var(--primary-color);
        }

        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
            color: #fff;
        }
        /* Solusi: Override style untuk tombol di dalam box error */
        .error .btn {
            background-color: #dc2626; /* Warna merah solid */
            color: #fff; /* Tulisan putih */
            border: none;
            box-shadow: 0 4px 10px rgba(220, 38, 38, 0.3);
        }

        .error .btn:hover {
            background-color: #b91c1c; /* Warna merah lebih gelap saat hover */
            transform: translateY(-2px);
        }

        .btn-primary {
            background-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(217, 4, 41, 0.25);
        }
        
        .btn:active {
            transform: translateY(0);
        }

        /* --- STYLE TAMBAHAN: LUPA PASSWORD --- */
        .forgot-password {
            margin-top: 15px;
            margin-bottom: 5px;
        }

        .forgot-password a {
            color: var(--text-muted);
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s ease;
        }

        .forgot-password a:hover {
            color: var(--wa-color);
        }

        .wa-icon {
            width: 16px;
            height: 16px;
            fill: currentColor; /* Ikut warna teks */
        }
        /* -------------------------------------- */

        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.2s;
            background-color: transparent;
            border: 1px solid transparent;
        }

        .btn-back:hover {
            color: var(--primary-color);
            background-color: #fff1f1;
            border-color: #fecaca;
        }

        /* --- ALERT STYLES --- */
        .alert-content {
            animation: fadeIn 0.6s ease-out;
        }

        .profile-photo-wrapper {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 20px;
            overflow: hidden;
            border: 4px solid var(--success-bg);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            background-color: #eee;
        }

        .profile-photo-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .icon-wrapper-alert {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-wrapper-alert svg {
            width: 40px;
            height: 40px;
        }

        .success .icon-wrapper-alert {
            background-color: var(--success-bg);
        }

        .success .icon-wrapper-alert svg {
            fill: var(--success-icon);
        }

        .success h2 {
            color: var(--success-icon);
        }
        
        .success .subtitle strong {
            color: var(--primary-color);
        }

        .error .icon-wrapper-alert {
            background-color: var(--error-bg);
        }

        .error .icon-wrapper-alert svg {
            fill: var(--error-icon);
        }

        .error h2 {
            color: var(--error-icon);
        }
        
        /* Loading Spinner untuk redirect */
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 8px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="logo">
            <!-- Pastikan path gambar benar -->
            <img src="../Gambar/logpmi.png" alt="Logo PMR">
        </div>

        <?php if ($status === 'success'): ?>
            <div class="alert-content success">
                <div class="profile-photo-wrapper">
                    <img src="<?= $foto_profil ?>" alt="Foto Profil">
                </div>

                <h2>Login Berhasil!</h2>
                <p class="subtitle">Selamat datang, <strong><?= htmlspecialchars($displayUsername) ?></strong>.<br>Anda akan dialihkan secara otomatis...</p>
                
                <a href="<?= $redirectLink ?>" class="btn btn-primary" id="btn-dashboard">
                    Masuk Dashboard &rarr;
                </a>
            </div>
            
            <!-- Auto Redirect Script -->
            <script>
                // Redirect setelah 2 detik
                setTimeout(function() {
                    window.location.href = "<?= $redirectLink ?>";
                }, 2000);
            </script>

        <?php elseif ($status === 'error'): ?>
            <div class="alert-content error">
                <div class="icon-wrapper-alert">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z" />
                    </svg>
                </div>
                <h2>Login Gagal</h2>
                <p class="subtitle">Username atau password yang Anda masukkan salah.</p>
                <a href="login.php" class="btn">Coba Lagi</a>
            </div>

        <?php else: ?>
            <h2>Login PMR</h2>
            <p class="subtitle">Silakan masuk untuk mengakses panel anggota.</p>

            <form action="" method="POST">
                <div class="field">
                    <label for="username">Username</label>
                    <div class="input-group">
                        <input type="text" name="username" id="username" placeholder="Masukkan username" required autocomplete="off">
                        <svg class="input-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                        </svg>
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="password" placeholder="Masukkan password" required>
                        <svg class="input-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z" />
                        </svg>
                    </div>
                </div>

                <button type="submit" name="login" class="btn btn-primary">Login</button>
                
                <!-- TAMBAHAN: Tombol Lupa Password WhatsApp -->
                <div class="forgot-password">
                    <a href="https://wa.me/6281234567890?text=Halo%20Admin%20PMR%2C%20saya%20lupa%20password%20akun%20saya.%20Username%20saya%3A%20" target="_blank" rel="noopener noreferrer">
                        <svg class="wa-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Lupa Password? Hubungi Admin
                    </a>
                </div>
               
            </form>

            <a href="../Halaman Utama/index.php" class="btn-back">Kembali ke Beranda</a>
        <?php endif; ?>

    </div>
</body>
</html>