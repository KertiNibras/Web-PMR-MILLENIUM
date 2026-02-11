<?php
session_start();
if (!isset($_SESSION['nama'])) {
    header("Location: ../Login/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">  
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pengurus - PMR Millenium</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="icon" href="../Gambar/logpmi.png" type="image/png">
  <style>
    /* --- CSS VARIABLES (Sama Persis dengan Perpustakaan) --- */
    :root {
      --primary-color: #d90429; /* PMR Red */
      --primary-hover: #ef233c;
      --secondary-color: #2b2d42;
      --bg-color: #f8f9fa;
      --card-bg: #ffffff;
      --text-color: #333333;
      --text-muted: #6c757d;
      --border-color: #e9ecef;
      --success-color: #27ae60;
      --warning-color: #f39c12;
      --danger-color: #e74c3c;
      --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
      --shadow-md: 0 4px 6px rgba(0,0,0,0.08);
      --radius: 10px;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      background-color: var(--bg-color);
      color: var(--text-color);
      line-height: 1.6;
    }

    /* --- HEADER (SAMA PERSIS DENGAN PERPUSTAKAAN) --- */
    header {
      background: #fff;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      position: fixed;
      width: 100%;
      z-index: 1000;
      animation: fadeSlideUp 1s ease-out;
    }

    .navbar {
      max-width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 20px;
      opacity: 0;
      transform: translateY(-20px);
      animation: fadeInDown 1s ease forwards;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: bold;
      color: #000000;
      font-size: 18px;
    }

    .logo img {
      height: 40px;
    }

    @keyframes fadeInDown {
      to { opacity: 1; transform: translateY(0); }
    }

    .menu-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 22px;
      cursor: pointer;
      color: var(--primary-color);
    }

    /* --- LAYOUT UTAMA --- */
    .dashboard-container {
      display: flex;
      min-height: 100vh;
      padding-top: 70px; /* Space for fixed header */
    }

    /* --- SIDEBAR (Sama Persis dengan Perpustakaan) --- */
    .sidebar {
      width: 250px;
      background: #ffffff;
      padding-top: 20px;
      border-right: 1px solid var(--border-color);
      transition: transform 0.3s ease;
      height: calc(100vh - 70px);
      position: sticky;
      top: 70px;
      overflow-y: auto;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .sidebar li {
      padding: 14px 25px;
      cursor: pointer;
      color: var(--text-color);
      display: flex;
      align-items: center;
      gap: 12px;
      transition: 0.3s;
      width: 100%;
      font-weight: 500;
      border-left: 4px solid transparent;
    }

    .sidebar li:hover {
      background-color: #fff0f3;
      color: var(--primary-color);
    }

    .sidebar li.active {
      background-color: #fff0f3;
      color: var(--primary-color);
      border-left-color: var(--primary-color);
    }

    .sidebar a {
      text-decoration: none;
      color: inherit;
      display: flex;
      align-items: center;
      gap: 10px;
      width: 100%;
    }

    /* --- MAIN CONTENT --- */
    .main-content {
      flex: 1;
      padding: 30px;
      width: calc(100% - 250px);
    }

    /* Dashboard Welcome Header */
    .dashboard-welcome {
      margin-bottom: 30px;
    }

    .dashboard-welcome h1 {
      font-size: 1.8rem;
      color: var(--primary-color);
      margin-bottom: 5px;
    }

    .dashboard-welcome p {
      color: var(--text-muted);
    }

    /* --- DASHBOARD CARDS (Modern Design) --- */
    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }

    .card {
      background-color: var(--card-bg);
      border-radius: var(--radius);
      padding: 30px;
      box-shadow: var(--shadow-sm);
      transition: all 0.3s ease;
      border: 1px solid var(--border-color);
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      text-align: left;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-md);
      border-color: rgba(217, 4, 41, 0.2);
    }

    .card-icon-wrapper {
      width: 50px;
      height: 50px;
      background-color: #ffebee;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary-color);
      font-size: 1.5rem;
      margin-bottom: 20px;
    }

    .card h3 {
      font-size: 1.2rem;
      margin-bottom: 10px;
      color: var(--text-color);
    }

    .card p {
      font-size: 0.9rem;
      color: var(--text-muted);
      margin-bottom: 20px;
      line-height: 1.5;
      flex-grow: 1;
    }

    .card-btn {
      align-self: flex-start;
      background-color: var(--primary-color);
      color: white;
      padding: 10px 20px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: 600;
      font-size: 0.9rem;
      transition: background 0.3s;
      display: inline-block;
      border: none;
      cursor: pointer;
    }

    .card-btn:hover {
      background-color: var(--primary-hover);
    }

    /* --- NOTIFICATION (PHP) --- */
    .notification {
      position: fixed;
      top: 90px;
      right: 20px;
      background: white;
      border-left: 5px solid #27ae60;
      color: #333;
      padding: 15px 25px;
      border-radius: 4px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      z-index: 9999;
      display: none;
      align-items: center;
      gap: 15px;
      animation: slideIn 0.5s ease-out;
    }
    
    .notification i {
      color: #27ae60;
      font-size: 1.5rem;
    }

    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }

    /* --- RESPONSIVE (Sama dengan Perpustakaan) --- */
    @media (max-width: 992px) {
      .main-content {
        width: 100%;
        padding: 20px;
      }
      
      .sidebar {
        width: 250px;
        position: fixed;
        top: 70px;
        left: -250px;
        height: calc(100vh - 70px);
        z-index: 999;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
      }
      
      .sidebar.active {
        left: 0;
      }
      
      .menu-toggle {
        display: block;
      }

      .cards {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 576px) {
      .dashboard-welcome h1 {
        font-size: 1.5rem;
      }
      
      .card {
        padding: 20px;
      }
    }

    /* Tombol Back Mobile (Sama dengan Perpustakaan) */
    .back-btn {
      display: none;
      background: none;
      border: none;
      font-size: 20px;
      color: var(--primary-color);
      cursor: pointer;
    }

    @media (max-width: 992px) {
      .back-btn {
        display: block;
        position: absolute;
        left: 15px;
        top: 20px;
        z-index: 1001;
      }

      .logo {
        margin: 0 auto;
      }

      .menu-toggle {
        position: absolute;
        right: 15px;
        top: 18px;
      }
    }
  </style>
</head>
<body>

  <!-- HEADER (SAMA PERSIS DENGAN PERPUSTAKAAN) -->
  <header>
    <nav class="navbar">
      <button class="back-btn" onclick="goBack()">
        <i class="fa-solid fa-arrow-left"></i>
      </button>

      <div class="logo">
        <img src="../Gambar/logpmi.png" alt="Logo">
        <span>PMR MILLENIUM</span>
      </div>

      <button class="menu-toggle"><i class="fa-solid fa-bars"></i></button>
    </nav>
  </header>

  <!-- NOTIFICATION PHP (Login Success) -->
  <?php if(isset($_SESSION['login_success'])): ?>
  <div id="loginNotification" class="notification">
    <i class="fas fa-check-circle"></i>
    <div>
      <div style="font-weight: bold;">Login Berhasil</div>
      <div style="font-size: 0.9rem; color: #555;">Selamat datang, <?= $_SESSION['nama']; ?>!</div>
    </div>
  </div>
  <?php unset($_SESSION['login_success']); endif; ?>

  <div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <ul>
        <li class="active"><a href="../Dashboard Pengurus/pengurus.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
        <li><a href="../Dashboard Pengurus/kelolaabsen.html"><i class="fa-solid fa-calendar-check"></i> Kelola Absensi</a></li>
        <li><a href="../Dashboard Pengurus/kelolaperpus.html"><i class="fa-solid fa-book"></i> Kelola Perpustakaan Digital</a></li>
        <li><a href=""><i class="fa-solid fa-users"></i> Kelola Akun</a></li>
        <li><a href=""><i class="fa-solid fa-gamepad"></i> Kelola Quiz</a></li>
        <li><a href="../Dashboard Pengurus/kelolaabsen.html"><i class="fa-solid fa-pen-to-square"></i> Edit Beranda</a></li>
        <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a></li>
      </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <div class="dashboard-welcome">
        <h1>Dashboard Pengurus</h1>
        <p>Halo, <?php echo $_SESSION['nama']; ?>! Selamat datang di panel kontrol PMR Millenium.</p>
      </div>

      <div class="cards">
        <!-- Card 1: Absensi -->
        <div class="card">
          <div class="card-icon-wrapper">
            <i class="fa-solid fa-calendar-check"></i>
          </div>
          <h3>Kelola Absensi</h3>
          <p>Lihat catatan kehadiran anggota di kegiatan PMR.</p>
          <button class="card-btn" onclick="location.href='../Dashboard Pengurus/kelolaabsen.html'">Lihat</button>
        </div>

        <!-- Card 2: Perpustakaan -->
        <div class="card">
          <div class="card-icon-wrapper">
            <i class="fa-solid fa-book"></i>
          </div>
          <h3>Kelola Perpustakaan Digital</h3>
          <p>Edit Materi - Materi Seputar PMR.</p>
          <button class="card-btn" onclick="location.href='../Dashboard Pengurus/kelolaperpus.html'">Lihat</button>
        </div>

        <!-- Card 3: Akun -->
        <div class="card">
          <div class="card-icon-wrapper">
            <i class="fa-solid fa-users"></i>
          </div>
          <h3>Kelola Akun</h3>
          <p>Kelola Jabatan Anggota,Akun Dll.</p>
          <!-- Teks Tombol Dikembalikan ke "Cek" sesuai kode asli -->
          <button class="card-btn">Cek</button>
        </div>

        <!-- Card 4: Kuis -->
        <div class="card">
          <div class="card-icon-wrapper">
            <i class="fa-solid fa-gamepad"></i>
          </div>
          <h3>Kelola Kuis</h3>
          <p>Kelola Kuis Seputar PMR.</p>
          <!-- Teks Tombol Dikembalikan ke "Cek" sesuai kode asli -->
          <button class="card-btn">Cek</button>
        </div>
      </div>
      
    </main>
  </div>

  <script>
    // --- Sidebar Logic (Sama dengan Perpustakaan) ---
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    menuToggle.addEventListener('click', () => {
      sidebar.classList.toggle('active');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
      if (window.innerWidth <= 992) {
        if (!sidebar.contains(e.target) && !menu-toggle.contains(e.target)) {
          sidebar.classList.remove('active');
        }
      }
    });

    // Back button logic
    function goBack() {
      // Jika sedang di dashboard, kembali ke login atau history sebelumnya
      window.history.back();
    }
    
    // --- Notification Logic (PHP) ---
    document.addEventListener('DOMContentLoaded', function() {
      const notification = document.getElementById('loginNotification');
      
      if (notification) {
        // Show notification
        notification.style.display = 'flex';
        
        // Hide after 4 seconds
        setTimeout(() => {
          notification.style.opacity = '0';
          notification.style.transform = 'translateX(100%)';
          setTimeout(() => {
            notification.style.display = 'none';
          }, 500);
        }, 4000);
      }
    });
  </script>
</body>
</html>