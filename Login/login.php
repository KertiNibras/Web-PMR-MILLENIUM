<?php
session_start();
// Sesuaikan path koneksi sesuai struktur folder Anda
include '../koneksi.php';

// --- VARIABEL STATUS & PESAN ---
$status = null;
$displayUsername = "";
$redirectLink = "";

// --- LOGIKA LOGIN ---
if (isset($_POST['login'])) {

    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    // Cari akun berdasarkan username
    $sql    = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $sql);

    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);

        // Cek password
        if ($password == $data['password']) {

            // Simpan data ke session
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['role'] = $data['role']; // PENTING: Simpan Role
            $_SESSION['username'] = $data['username'];

            // Tentukan Status Sukses
            $status = 'success';
            $displayUsername = $data['nama'];

            // --- PERUBAHAN DISINI ---
            // Arahkan SEMUA user (Anggota & Pengurus) ke file Dashboard yang sama
            $redirectLink = '../Dashboard Anggota/anggota.php';

            // Jika kamu ingin langsung set notifikasi login success di dashboard
            $_SESSION['login_success'] = true;
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

    <style>
        /* --- VARIABLES --- */
        :root {
            --primary-color: #d90429;
            --primary-hover: #c92a2a;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius: 16px;
            --shadow: 0 10px 25px rgba(226, 56, 56, 0.1);
            --success-bg: #dcfce7;
            --success-icon: #16a34a;
            --error-bg: #fee2e2;
            --error-icon: #dc2626;
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
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            font-weight: 500;
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
            padding: 12px 14px 12px 44px;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            font-size: 0.95rem;
            color: var(--text-main);
            transition: all 0.3s ease;
            background-color: #fff;
        }

        input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(226, 56, 56, 0.1);
        }

        input:focus+.input-icon {
            fill: var(--primary-color);
        }

        .btn {
            display: block;
            width: 100%;
            padding: 13px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            color: #fff;
        }

        .btn-primary {
            background-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }

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
        }

        .btn-back:hover {
            color: var(--primary-color);
            background-color: #fff1f1;
        }

        .link-register {
            display: block;
            margin-top: 15px;
            font-size: 0.85rem;
            color: var(--text-muted);
            text-decoration: none;
        }

        .link-register span {
            color: var(--primary-color);
            font-weight: 600;
        }

        .alert-content {
            margin: 20px 0;
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

        .success .btn {
            background-color: var(--primary-color);
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

        .error .btn {
            background-color: var(--error-icon);
        }
    </style>
</head>

<body>

    <div class="login-card">
        <div class="logo">
            <img src="../Gambar/logpmi.png" alt="Logo PMR">
        </div>

        <?php if ($status === 'success'): ?>
            <div class="alert-content success">
                <div class="icon-wrapper-alert">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" />
                    </svg>
                </div>
                <h2>Login Berhasil!</h2>
                <p class="subtitle">Selamat datang, <strong><?= htmlspecialchars($displayUsername) ?></strong>.<br>Mengalihkan...</p>
                <a href="<?= $redirectLink ?>" class="btn btn-primary">Masuk Dashboard &rarr;</a>
            </div>

        <?php elseif ($status === 'error'): ?>
            <div class="alert-content error">
                <div class="icon-wrapper-alert">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z" />
                    </svg>
                </div>
                <h2>Login Gagal</h2>
                <p class="subtitle">Username atau password salah.</p>
                <a href="login.php" class="btn">Coba Lagi</a>
            </div>

        <?php else: ?>
            <h2>Login PMR</h2>
            <p class="subtitle">Silakan masuk untuk mengakses akun Anda.</p>

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

                <button type="submit" name="login" class="btn btn-primary">Login Sekarang</button>
            </form>

            <a href="../Daftar/register.php" class="link-register">Belum punya akun? <span>Daftar Sekarang</span></a>
            <a href="../Halaman Utama/index.html" class="btn-back">Kembali ke Beranda</a>
        <?php endif; ?>

    </div>

</body>

</html>