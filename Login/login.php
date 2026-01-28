<?php
session_start();
include '../koneksi.php'; // koneksi di root

// Jika tombol login ditekan
if (isset($_POST['login'])) {

    $username     = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    // Cari akun berdasarkan username
    $sql    = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $sql);

    // Jika akun ditemukan
    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);

        // Cek password
        if ($password == $data['password']) {

            // Simpan data ke session
            $_SESSION['nama'] = $data['nama'];
            $_SESSION['role'] = $data['role'];
            $_SESSION['username'] = $data['username'];

            // Redirect sesuai role + kirim query success
           // Simpan session untuk notifikasi sekali tampil
$_SESSION['login_success'] = true;

if ($data['role'] == 'anggota') {
    header("Location: ../Dashboard Anggota/anggota.php");
    exit;
} 

if ($data['role'] == 'pengurus') {
    header("Location: ../Dashboard Pengurus/pengurus.php");
    exit;
}


        } else {
            // Password salah
            echo "<script>
                    alert('Password salah!');
                    window.location.href='login.php';
                  </script>";
            exit;
        }

    } else {
        // username tidak ditemukan
        echo "<script>
                alert('Akun tidak ditemukan!');
                window.location.href='login.php';
              </script>";
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="login.css">
  <link rel="icon" href="../Gambar/logpmi.png" type="image/png">
  <title>Login - PMR Millenium</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Montserrat', sans-serif;
    }

    body {
      background-color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      height: 100vh;
      overflow: hidden;
    }

    body::before {
      content: "";
      position: absolute;
      top: -30%;
      left: -10%;
      width: 140%;
      height: 160%;
      background: linear-gradient(120deg,
          rgba(226, 56, 56, 0.18) 0%,
          rgba(255, 193, 193, 0.12) 25%,
          rgba(255, 255, 255, 0.02) 50%,
          rgba(255, 230, 230, 0.06) 75%,
          rgba(226, 56, 56, 0.12) 100%);
      background-size: 300% 300%;
      filter: blur(48px);
      z-index: 0;
      transform: rotate(8deg);
      animation: bgShift 12s ease-in-out infinite;
      border-radius: 30%;
      pointer-events: none;
    }

    /* subtle floating radial highlights */
    body::after {
      content: "";
      position: absolute;
      right: -10%;
      bottom: -20%;
      width: 40vmax;
      height: 40vmax;
      background: radial-gradient(circle at 30% 30%, rgba(255, 200, 200, 0.08), transparent 30%),
        radial-gradient(circle at 70% 70%, rgba(226, 56, 56, 0.06), transparent 20%);
      filter: blur(24px);
      z-index: 0;
      animation: floatSpot 14s ease-in-out infinite;
      pointer-events: none;
    }

    @keyframes bgShift {
      0% {
        background-position: 0% 50%;
        transform: rotate(6deg) scale(1);
      }

      50% {
        background-position: 100% 50%;
        transform: rotate(8deg) scale(1.03);
      }

      100% {
        background-position: 0% 50%;
        transform: rotate(6deg) scale(1);
      }
    }

    @keyframes floatSpot {
      0% {
        transform: translateY(0) scale(1);
        opacity: 0.95;
      }

      50% {
        transform: translateY(-6%) scale(1.02);
        opacity: 1;
      }

      100% {
        transform: translateY(0) scale(1);
        opacity: 0.95;
      }
    }

    .container {
      background-color: #fff;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
      position: relative;
      overflow: hidden;
      width: 960px;
      max-width: 100%;
      min-height: 620px;
    }

    .container p {
      font-size: 14px;
      line-height: 20px;
      /* letter-spacing: 0.3px; */
      margin: 20px 0;
    }

    .container span {
      font-size: 12px;
    }

    .container a {
      color: #333;
      font-size: 13px;
      text-decoration: none;
      margin: 15px 0 10px;
    }

    .container button {
      background-color: #e23838;
      color: #fff;
      font-size: 12px;
      padding: 10px 45px;
      border: 1px solid transparent;
      border-radius: 8px;
      font-weight: 600;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin-top: 10px;
      cursor: pointer;
    }

    .container button.hidden {
      background-color: transparent;
      border-color: #fff;
    }

    .container form {
      background-color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      padding: 0 40px;
      height: 100%;
    }

    .container input {
      background-color: #eee;
      border: none;
      margin: 8px 0;
      padding: 10px 15px;
      font-size: 13px;
      border-radius: 8px;
      width: 100%;
      outline: none;
    }

    .form-container {
      position: absolute;
      padding: 5%;
      top: 0;
      height: 90%;
      transition: all 0.6s ease-in-out;
    }

    .sign-in {
      left: 0;
      width: 50%;
      z-index: 2;
    }

    .social-icons {
      margin: 20px 0;
    }

    .social-icons a {
      border: 1px solid #ccc;
      border-radius: 20%;
      display: inline-flex;
      justify-content: center;
      align-items: center;
      margin: 0 3px;
      width: 40px;
      height: 40px;
    }

    .welcome-panel {
      position: absolute;
      top: 0;
      left: 50%;
      width: 50%;
      height: 100%;
      overflow: hidden;
      transition: all 0.6s ease-in-out;
      border-radius: 150px 0 0 100px;
      z-index: 1000;
    }



    .welcome-bg {
      background-color: #e23838;
      height: 100%;
      color: #fff;
      position: relative;
      left: -100%;
      height: 100%;
      width: 200%;
      transform: translateX(0);
      transition: all 0.6s ease-in-out;
    }



    .welcome-content {
      position: absolute;
      width: 50%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      padding: 0 30px;
      text-align: center;
      top: 0;
      transform: translateX(0);
      transition: all 0.6s ease-in-out;
    }



    .welcome-content {
      right: 0;
      transform: translateX(0);
    }


    .logo img {
      width: 60px;
      margin: 10px;
    }

    .btn-kembali {

      background-color: white;
      padding: 8px 10px;
      border-radius: 5px;
      text-decoration: none;
      font-weight: bold;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
      z-index: 1000;
    }

    .copyright {
      position: absolute;
      bottom: -55px;
      /* Bisa diganti jadi 5px atau 30px sesuai selera */
      left: 50%;
      transform: translateX(-50%);
      font-size: 13px;
      color: #777;
      text-align: center;
      width: 100%;
      letter-spacing: 0.3px;
    }

    /* ============================= */
    /* RESPONSIVE DESIGN (MOBILE SIMPLER VERSION) */
    /* ============================= */

    /* Untuk layar kecil (HP / mobile) */
    @media (max-width: 768px) {
  body {
    height: auto;
    padding: 20px;
    overflow-y: auto;
    background: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
  }

  /* Container nyatu sama background */
  .container {
    width: 100%;
    min-height: auto;
    background: transparent;
    box-shadow: none;
    border: none;
    position: relative;
  }

  /* Hilangkan panel merah dan efek */
  .welcome-panel,
  .container.active .welcome-panel {
    display: none !important;
  }

  /* Form login nyatu dengan latar */
  .sign-in {
    position: relative;
    width: 100%;
    background: transparent;
    box-shadow: none;
    border: none;
    border-radius: 0;
    padding: 0 20px;
    margin: 0 auto;
  }

  /* Input & tombol tetap clean */
  .container input {
    width: 100%;
    padding: 14px 16px;
    font-size: 15px;
    background: #f3f3f3;
    border: none;
    border-radius: 8px;
    margin-top: 10px;
  }

  .container button {
    width: 100%;
    padding: 14px 0;
    font-size: 14px;
    border-radius: 8px;
    border: none;
    background: #e23838;
    color: white;
    font-weight: 600;
    margin-top: 16px;
  }

  .container button:hover {
    background: #c72d2d;
  }

  /* Logo & tombol kembali */
  .logo img {
    width: 55px;
    margin: 20px auto 10px auto;
    display: block;
  }

  .btn-kembali {
    display: inline-block;
    background: #fff;
    color: #e23838;
    padding: 8px 12px;
    font-size: 13px;
    border-radius: 6px;
    text-decoration: none;
    margin-top: 10px;
    border: 1px solid #e23838;
  } 

  /* Copyright di bawah layar */
  .copyright {
    position: fixed;
    bottom: 10px;
    left: 50%;
    transform: translateX(-50%);
    text-align: center;
    font-size: 12px;
    color: #888;
    width: 100%;
  }

  /* Hilangkan efek animasi latar */
  body::before,
  body::after {
    display: none;
  }
}

    /* ============================= */
    /* HEADER MOBILE */
    /* ============================= */
    .mobile-header {
      display: none;
      /* default: tidak muncul di desktop */
    }

    /* Tampil hanya di mobile */
    @media (max-width: 768px) {
      .mobile-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        position: sticky;
        top: 0;
        z-index: 100;
      }

      .title-header {
        font-size: 18px;
        font-weight: 700;
        color: #e23838;
      }

      @keyframes fadeIn {
        from {
          opacity: 0;
          transform: translateY(-5px);
        }

        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      .copyright {
        position: fixed;
        bottom: 10px;
        text-align: center;
        width: 100%;
        font-size: 13px;
        color: #777;
      }

    }

    /* Tombol Daftar hanya muncul di layar mobile */
    .btn-daftar-mobile {
      display: none;
      /* sembunyikan di desktop */
    }

    @media (max-width: 768px) {
      .btn-daftar-mobile {
        display: inline-block;
        margin-top: 10px;
        width: 265px;
        text-align: center;
        background-color: #fff;
        color: #e23838;
        border: 1.5px solid #e23838;
        padding: 12px ;
        border-radius: 8px;
        font-weight: 600;
        transition: 0.2s;
      }

      .btn-daftar-mobile:hover {
        background-color: #e23838;
        color: #fff;
      }
    }
  </style>
</head>

<body>

  <header class="mobile-header">
    <h1 class="title-header">PMR MILLENIUM</h1>
    </ul>
    </nav>
  </header>

  <div class="container" id="container">
    <div class="form-container sign-in">
      <form action="" method="POST">
        <div class="logo">
          <img src="../Gambar/logpmi.png" alt="PMR Millenium Logo">
        </div>
        <h1>Login</h1>
        <input type="text" name="username" placeholder="Username" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit" name="login">Login</button>


        <a href="../Daftar/register.html" target="_blank"
          class="btn-daftar-mobile">Ingin Bergabung? Daftar Sekarang.</a>
        <a href="../Halaman Utama/index.html" class="btn-kembali">← Kembali ke Beranda</a>
        <p class="copyright">© 2025 PMR Millenium. All rights reserved.</p>
      </form>
    </div>
    <div class="welcome-panel">
      <div class="welcome-bg">
        <div class="welcome-content">
        </div>
        <div class="welcome-content">
          <h1>Selamat Datang!</h1>
          <p>"PMR bukan sekadar organisasi, ini adalah panggilan hati."</p>
          <button class="hidden" id="register">Daftar</button>
        </div>
      </div>
    </div>
  </div>
  <script>

    const registerButton = document.getElementById('register');
    registerButton.addEventListener('click', () => {
      window.location.href = '../Daftar/register.html';
    });


  </script>

</body>

</html>