<?php
session_start();
// 1. Cek apakah user sudah login
if (!isset($_SESSION['nama'])) {
  echo '<script type="text/javascript">';
  echo 'alert("Silakan login terlebih dahulu!");';
  echo 'window.location.href = "../Login/login.php";';
  echo '</script>';
  exit;
}

// 2. AMBIL ROLE
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'anggota';
$user_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0; // Asumsi ada ID di session

// ========================================================
// LOGIKA STATISTIK (Simulasi Data - Ganti dengan Query DB)
// ========================================================

if ($role == 'pengurus') {
  // STATISTIK UNTUK PENGURUS (Data Global)
  // TODO: Ganti dengan Query SQL COUNT
  // Contoh: SELECT COUNT(*) FROM users WHERE role='anggota'
  $stat_total_anggota = 25;

  // Contoh: SELECT COUNT(*) FROM absensi WHERE MONTH(tanggal) = MONTH(NOW())
  $stat_total_hadir_bulan_ini = 150;

  // Contoh: SELECT COUNT(*) FROM perpustakaan
  $stat_total_buku = 12;

  // Contoh: SELECT COUNT(*) FROM pendaftaran WHERE status='pending'
  $stat_pendaftaran_baru = 3;
} else {
  // STATISTIK UNTUK ANGGOTA (Data Pribadi)
  // TODO: Ganti dengan Query SQL WHERE user_id = $user_id

  // Contoh: SELECT COUNT(*) FROM absensi WHERE user_id = $user_id AND status='Hadir'
  $stat_hadir_saya = 8;

  // Contoh: SELECT COUNT(*) FROM absensi WHERE user_id = $user_id
  $stat_total_pertemuan = 10;

  $stat_persentase = ($stat_total_pertemuan > 0) ? round(($stat_hadir_saya / $stat_total_pertemuan) * 100) : 0;

  // Logika sederhana status (bisa disesuaikan)
  $stat_status = ($stat_persentase >= 80) ? 'Baik' : 'Perlu Ditingkatkan';
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - PMR Millenium</title>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <!-- Icon Tab -->
  <link rel="icon" href="../Gambar/logpmi.png" type="image/png">

  <style>
    /* --- CSS VARIABLES --- */
    :root {
      --primary-color: #d90429;
      /* Merah PMR */
      --primary-hover: #c92a2a;
      --bg-color: #f8f9fa;
      --card-bg: #ffffff;
      --text-color: #1e293b;
      --text-muted: #64748b;
      --border-color: #e2e8f0;
      --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
      --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.05);
      --radius: 12px;
      --header-height: 70px;

      /* Warna Statistik Tambahan */
      --stat-blue: #3b82f6;
      --stat-green: #10b981;
      --stat-orange: #f59e0b;
      --stat-purple: #8b5cf6;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', 'Segoe UI', sans-serif;
      background-color: var(--bg-color);
      color: var(--text-color);
      line-height: 1.6;
    }

    a {
      text-decoration: none;
      color: inherit;
    }

    ul {
      list-style: none;
    }

    /* --- HEADER --- */
    header {
      background: #fff;
      box-shadow: var(--shadow-sm);
      position: fixed;
      width: 100%;
      top: 0;
      z-index: 1000;
      height: var(--header-height);
    }

    .navbar {
      max-width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      height: 100%;
      padding: 0 20px;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 700;
      font-size: 18px;
      color: #000;
    }

    .logo img {
      height: 40px;
    }

    .menu-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: var(--primary-color);
    }

    .back-btn {
      display: none;
      background: none;
      border: none;
      font-size: 20px;
      color: var(--primary-color);
      cursor: pointer;
    }

    /* --- LAYOUT --- */
    .dashboard-container {
      display: flex;
      min-height: 100vh;
      padding-top: var(--header-height);
    }

    /* --- SIDEBAR --- */
    .sidebar {
      width: 250px;
      background: #fff;
      border-right: 1px solid var(--border-color);
      position: sticky;
      top: var(--header-height);
      height: calc(100vh - var(--header-height));
      overflow-y: auto;
      z-index: 900;
    }

    .sidebar li {
      padding: 14px 25px;
      cursor: pointer;
      color: var(--text-color);
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 12px;
      border-left: 4px solid transparent;
      transition: all 0.2s;
    }

    .sidebar li:hover,
    .sidebar li.active {
      background-color: #fff1f1;
      color: var(--primary-color);
      border-left-color: var(--primary-color);
    }

    .sidebar a {
      display: flex;
      align-items: center;
      gap: 10px;
      width: 100%;
    }

    /* --- MAIN CONTENT --- */
    .main-content {
      flex: 1;
      padding: 30px;
    }

    .dashboard-welcome {
      margin-bottom: 30px;
    }

    .dashboard-welcome h1 {
      font-size: 1.75rem;
      color: var(--primary-color);
      margin-bottom: 5px;
    }

    /* --- STATISTIK SECTION (BARU) --- */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 20px;
      margin-bottom: 35px;
    }

    .stat-card {
      background: var(--card-bg);
      padding: 20px;
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-color);
      display: flex;
      align-items: center;
      gap: 20px;
      transition: transform 0.2s;
    }

    .stat-card:hover {
      transform: translateY(-3px);
    }

    .stat-icon {
      width: 56px;
      height: 56px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      color: #fff;
      flex-shrink: 0;
    }

    .stat-info h2 {
      font-size: 1.5rem;
      margin-bottom: 2px;
      color: var(--text-color);
    }

    .stat-info p {
      font-size: 0.85rem;
      color: var(--text-muted);
      font-weight: 500;
    }

    /* Warna Icon Statistik */
    .bg-red {
      background-color: var(--primary-color);
    }

    .bg-blue {
      background-color: var(--stat-blue);
    }

    .bg-green {
      background-color: var(--stat-green);
    }

    .bg-orange {
      background-color: var(--stat-orange);
    }

    .bg-purple {
      background-color: var(--stat-purple);
    }

    /* --- CARDS (WARNA MERAH SERAGAM) --- */
    .cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
    }

    .card {
      background: var(--card-bg);
      border-radius: var(--radius);
      padding: 25px;
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-color);
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      text-align: left;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-md);
      border-color: rgba(217, 4, 41, 0.3);
    }

    /* Style Icon & Button */
    .card-icon-wrapper {
      width: 48px;
      height: 48px;
      background-color: #ffebee;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--primary-color);
      font-size: 1.4rem;
      margin-bottom: 20px;
    }

    .card h3 {
      font-size: 1.15rem;
      margin-bottom: 8px;
      color: var(--text-color);
    }

    .card p {
      font-size: 0.9rem;
      color: var(--text-muted);
      margin-bottom: 20px;
      flex-grow: 1;
    }

    .card-btn {
      background-color: var(--primary-color);
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.9rem;
      transition: 0.3s;
      border: none;
      cursor: pointer;
      align-self: flex-start;
    }

    .card-btn:hover {
      background-color: var(--primary-hover);
    }

    /* --- NOTIFICATION --- */
    .notification {
      position: fixed;
      top: 85px;
      right: 20px;
      background: white;
      border-left: 5px solid #10b981;
      color: #333;
      padding: 15px 20px;
      border-radius: 4px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      z-index: 1100;
      display: none;
      align-items: center;
      gap: 12px;
      animation: slideIn 0.4s ease forwards;
      min-width: 280px;
    }

    .notification i {
      color: #10b981;
      font-size: 1.2rem;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(50px);
      }

      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    @keyframes fadeOut {
      to {
        opacity: 0;
        transform: translateX(50px);
      }
    }

    /* --- RESPONSIVE --- */
    @media (max-width: 992px) {
      .main-content {
        width: 100%;
        padding: 20px;
      }

      .sidebar {
        position: fixed;
        top: var(--header-height);
        left: -260px;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
      }

      .sidebar.active {
        left: 0;
      }

      .menu-toggle,
      .back-btn {
        display: block;
      }

      .cards {
        grid-template-columns: 1fr;
      }

      .navbar {
        position: relative;
      }

      .back-btn {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
      }

      .menu-toggle {
        position: absolute;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
      }

      .logo {
        margin: 0 auto;
      }

      /* Stat responsive */
      .stats-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>

  <!-- HEADER -->
  <header>
    <nav class="navbar">
      <button class="back-btn" onclick="goBack()" aria-label="Kembali"><i class="fa-solid fa-arrow-left"></i></button>

      <div class="logo">
        <img src="../Gambar/logpmi.png" alt="Logo PMR">
        <span>PMR MILLENIUM</span>
      </div>

      <button class="menu-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
    </nav>
  </header>

  <!-- NOTIFIKASI -->
  <?php if (isset($_SESSION['login_success'])): ?>
    <div id="loginNotification" class="notification">
      <i class="fas fa-check-circle"></i>
      <div>
        <div style="font-weight: 600;">Login Berhasil</div>
        <div style="font-size: 0.85rem; color: #666;">Selamat datang, <b><?= htmlspecialchars($_SESSION['nama']); ?></b>!</div>
      </div>
    </div>
    <?php unset($_SESSION['login_success']); ?>
  <?php endif; ?>

  <div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <ul>
        <li class="active"><a href="anggota.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>

        <?php if ($role == 'pengurus'): ?>
          <li><a href="kelolaabsen.php"><i class="fa-solid fa-calendar-check"></i> Kelola Absensi</a></li>
          <li><a href="kelolaperpus.php"><i class="fa-solid fa-book"></i> Kelola Perpustakaan Digital</a></li>
          <li><a href="kelola_pendaftaran.php"><i class="fa-solid fa-users"></i> Kelola Pendaftaran</a></li>
        <?php else: ?>
          <li><a href="absensi.php"><i class="fa-solid fa-calendar-check"></i> Rekap Absensi</a></li>
          <li><a href="perpus.php"><i class="fa-solid fa-book"></i> Perpustakaan Digital</a></li>
        <?php endif; ?>

        <li style="margin-top: 20px; border-top: 1px solid #eee;">
          <a href="javascript:void(0)" onclick="confirmLogout()">
            <i class="fa-solid fa-right-from-bracket"></i> Log Out
          </a>
        </li>
      </ul>
    </aside>

    <!-- KONTEN UTAMA -->
    <main class="main-content">
      <div class="dashboard-welcome">
        <h1>Dashboard <?php echo ucfirst($role); ?></h1>
        <p>Halo, <b><?= htmlspecialchars($_SESSION['nama']); ?></b>! Selamat datang di portal.</p>
      </div>

      <!-- BAGIAN STATISTIK (BARU) -->
      <div class="stats-grid">
        <?php if ($role == 'pengurus'): ?>
          <!-- Statistik Pengurus -->
          <div class="stat-card">
            <div class="stat-icon bg-blue">
              <i class="fa-solid fa-users"></i>
            </div>
            <div class="stat-info">
              <h2><?= $stat_total_anggota ?></h2>
              <p>Total Anggota</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon bg-green">
              <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div class="stat-info">
              <h2><?= $stat_total_hadir_bulan_ini ?></h2>
              <p>Kehadiran Bulan Ini</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon bg-orange">
              <i class="fa-solid fa-book"></i>
            </div>
            <div class="stat-info">
              <h2><?= $stat_total_buku ?></h2>
              <p>Total Materi</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon bg-purple">
              <i class="fa-solid fa-user-plus"></i>
            </div>
            <div class="stat-info">
              <h2><?= $stat_pendaftaran_baru ?></h2>
              <p>Pendaftaran Baru</p>
            </div>
          </div>

        <?php else: ?>
          <!-- Statistik Anggota -->
          <div class="stat-card">
            <div class="stat-icon bg-green">
              <i class="fa-solid fa-check-circle"></i>
            </div>
            <div class="stat-info">
              <h2><?= $stat_hadir_saya ?></h2>
              <p>Total Kehadiran Saya</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon bg-blue">
              <i class="fa-solid fa-percent"></i>
            </div>
            <div class="stat-info">
              <h2><?= $stat_persentase ?>%</h2>
              <p>Persentase Kehadiran</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon bg-orange">
              <i class="fa-solid fa-star"></i>
            </div>
            <div class="stat-info">
              <h2><?= $stat_status ?></h2>
              <p>Status Keaktifan</p>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <!-- AKHIR STATISTIK -->

      <div class="cards">
        <!-- CARD MENU TETAP SAMA SEPERTI SEBELUMNYA -->
        <?php if ($role == 'pengurus'): ?>
          <div class="card">
            <div class="card-icon-wrapper"><i class="fa-solid fa-pen-to-square"></i></div>
            <h3>Lihat Absensi</h3>
            <p>Melihat data absensi anggota</p>
            <button class="card-btn" onclick="window.location.href='kelolaabsen.php'">Lihat Data</button>
          </div>
          <div class="card">
            <div class="card-icon-wrapper"><i class="fa-solid fa-book"></i></div>
            <h3>Kelola Perpustakaan</h3>
            <p>Tambah atau hapus buku digital dan materi.</p>
            <button class="card-btn" onclick="window.location.href='kelolaperpus.php'">Kelola</button>
          </div>
          <div class="card">
            <div class="card-icon-wrapper"><i class="fa-solid fa-users"></i></div>
            <h3>Kelola Pendaftaran</h3>
            <p>Mengelola data pendaftaran anggota baru.</p>
            <button class="card-btn" onclick="window.location.href='kelola_pendaftaran.php'">Kelola</button>
          </div>
        <?php else: ?>
          <div class="card">
            <div class="card-icon-wrapper"><i class="fa-solid fa-calendar-check"></i></div>
            <h3>Rekap Absensi</h3>
            <p>Lihat riwayat kehadiran kamu.</p>
            <button class="card-btn" onclick="window.location.href='absensi.php'">Lihat Data</button>
          </div>
          <div class="card">
            <div class="card-icon-wrapper"><i class="fa-solid fa-book-open"></i></div>
            <h3>Perpustakaan Digital</h3>
            <p>Akses buku panduan P3K dan materi pelatihan.</p>
            <button class="card-btn" onclick="window.location.href='perpus.php'">Buka Perpus</button>
          </div>
        <?php endif; ?>
      </div>

    </main>
  </div>

  <!-- JAVASCRIPT -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const menuToggle = document.querySelector('.menu-toggle');
      const sidebar = document.querySelector('.sidebar');

      menuToggle.addEventListener('click', (e) => {
        e.stopPropagation();
        sidebar.classList.toggle('active');
      });

      document.addEventListener('click', (e) => {
        if (window.innerWidth <= 992) {
          if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            sidebar.classList.remove('active');
          }
        }
      });

      const notification = document.getElementById('loginNotification');
      if (notification) {
        notification.style.display = 'flex';
        setTimeout(() => {
          notification.style.animation = 'fadeOut 0.5s ease forwards';
          setTimeout(() => {
            notification.style.display = 'none';
          }, 500);
        }, 4000);
      }
    });

    function goBack() {
      window.history.back();
    }

    function confirmLogout() {
      if (confirm("Apakah Anda yakin ingin keluar dari akun?")) {
        window.location.href = "../logout.php";
      }
    }
  </script>
</body>

</html>