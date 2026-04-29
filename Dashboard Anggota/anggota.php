<?php
  session_start();
  include '../koneksi.php'; // Panggil koneksi DB

  // 1. Cek apakah user sudah login
  if (!isset($_SESSION['nama'])) {
    echo '<script type="text/javascript">';
    echo 'window.location.href = "../Login/login.php";';
    echo '</script>';
    exit;
  }

  // 2. AMBIL ROLE & DATA USER
  $role = isset($_SESSION['role']) ? $_SESSION['role'] : 'anggota';
  $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
  $nama_user = htmlspecialchars($_SESSION['nama']);

  $foto_session = isset($_SESSION['foto']) ? $_SESSION['foto'] : ''; 
$foto_profil = 'https://ui-avatars.com/api/?name=' . urlencode($nama_user) . '&background=d90429&color=fff'; // Default UI Avatar

// Pastikan path ke ../uploads/foto_profil/
if (!empty($foto_session)) {
    $path_foto = "../uploads/foto_profil/" . $foto_session;
    if (file_exists($path_foto)) {
        $foto_profil = $path_foto . "?t=" . time(); // Tambah timestamp supaya anti-cache
    }
}

  // ========================================================
  // LOGIKA STATISTIK REALTIME
  // ========================================================
  $bulan_ini = date('m');
  $tahun_ini = date('Y');
  $hari_ini = date('Y-m-d');

  if ($role == 'pengurus') {
    // Hitung Total Anggota
    $q_anggota = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role='anggota'");
    $stat_total_anggota = mysqli_fetch_assoc($q_anggota)['total'];

    // Hitung Kehadiran Bulan Ini
    $q_hadir = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM absensi WHERE MONTH(tanggal)='$bulan_ini' AND YEAR(tanggal)='$tahun_ini'");
    $stat_total_hadir_bulan_ini = mysqli_fetch_assoc($q_hadir)['total'];

    // Hitung Materi
    $q_buku = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM perpustakaan"); // Sesuaikan nama tabel
    $stat_total_buku = mysqli_fetch_assoc($q_buku)['total'];

    // Hitung Pendaftar Baru (Pending)
    $q_daftar = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pendaftaran WHERE status='pending'");
    $stat_pendaftaran_baru = mysqli_fetch_assoc($q_daftar)['total'];

  } else {
    // LOGIKA ANGGOTA (Perhitungan Pertemuan Realtime)
    
    // 1. Hitung Hari Libur di bulan ini
    $tgl_libur_arr = [];
    $q_libur = mysqli_query($koneksi, "SELECT tanggal FROM hari_libur WHERE MONTH(tanggal)='$bulan_ini' AND YEAR(tanggal)='$tahun_ini'");
    while($l = mysqli_fetch_assoc($q_libur)) $tgl_libur_arr[] = $l['tanggal'];

    // 2. Loop tanggal bulan ini
    $total_pertemuan_seharusnya = 0;
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $bulan_ini, $tahun_ini);
    
    for ($d = 1; $d <= $days_in_month; $d++) {
        $tgl_check = sprintf("%04d-%02d-%02d", $tahun_ini, $bulan_ini, $d);
        $dayOfWeek = date('w', strtotime($tgl_check)); // 0=Minggu, 3=Rabu, 5=Jumat
        
        // Hanya hitung jika sudah lewat/sampai hari ini
        if ($tgl_check <= $hari_ini) {
            // Jika Hari Rabu (3) atau Jumat (5)
            if (in_array($dayOfWeek, [3, 5])) {
                // Jika BUKAN hari libur
                if (!in_array($tgl_check, $tgl_libur_arr)) {
                    $total_pertemuan_seharusnya++;
                }
            }
        }
    }

    // 3. Hitung Kehadiran Saya
    $q_hadir_saya = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM absensi WHERE user_id='$user_id' AND MONTH(tanggal)='$bulan_ini' AND YEAR(tanggal)='$tahun_ini'");
    $stat_hadir_saya = mysqli_fetch_assoc($q_hadir_saya)['total'];

    // 4. Persentase
    $stat_persentase = ($total_pertemuan_seharusnya > 0) ? round(($stat_hadir_saya / $total_pertemuan_seharusnya) * 100) : 0;
    $stat_status = ($stat_persentase >= 80) ? 'Baik' : 'Perlu Ditingkatkan';
    $stat_total_pertemuan = $total_pertemuan_seharusnya; // Kirim ke view
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
        --primary-hover: #c92a2a;
        --bg-color: #f8f9fa;
        --card-bg: #ffffff;
        --text-color: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.05);
        --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
        --radius: 12px;
        --header-height: 70px;
        --sidebar-width: 250px;

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
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 100%;
        padding: 0 20px;
        max-width: 100%;
      }

      .nav-left {
        flex: 1;
        display: flex;
        justify-content: flex-start;
        align-items: center;
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

      .nav-center {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
      }

      .nav-right {
        flex: 1;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 15px;
        position: relative;
      }

      .profile-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        padding: 5px 10px;
        border-radius: 50px;
        transition: background 0.2s;
      }

      .profile-btn:hover {
        background-color: #f1f5f9;
      }

      .profile-greeting {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        line-height: 1.2;
      }

      .profile-greeting small {
        color: var(--text-muted);
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: capitalize;
      }

      .profile-greeting span {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text-color);
      }

      .profile-img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary-color);
        flex-shrink: 0;
      }

      .profile-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        margin-top: 10px;
        background: #fff;
        border-radius: 8px;
        box-shadow: var(--shadow-lg);
        width: 220px;
        z-index: 1001;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.2s ease;
        border: 1px solid var(--border-color);
        overflow: hidden;
      }

      .profile-dropdown.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
      }

      .dropdown-header {
        padding: 15px;
        background: #f8f9fa;
        border-bottom: 1px solid var(--border-color);
      }

      .dropdown-header p {
        font-weight: 600;
        color: var(--text-color);
        font-size: 0.9rem;
      }

      .dropdown-header small {
        color: var(--text-muted);
        font-size: 0.75rem;
      }

      .profile-dropdown ul li a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 15px;
        color: var(--text-color);
        font-size: 0.9rem;
        transition: 0.2s;
      }

      .profile-dropdown ul li a:hover {
        background-color: #fff1f1;
        color: var(--primary-color);
      }

      .profile-dropdown ul li a i {
        width: 20px;
        text-align: center;
      }

      .menu-toggle {
        display: none;
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: var(--primary-color);
        z-index: 1001;
      }

      /* --- LAYOUT CONTAINER --- */
      .dashboard-container {
        display: flex;
        min-height: 100vh;
        padding-top: var(--header-height);
      }

      /* --- SIDEBAR --- */
      .sidebar {
        width: var(--sidebar-width);
        background: #fff;
        border-right: 1px solid var(--border-color);
        position: sticky;
        top: var(--header-height);
        height: calc(100vh - var(--header-height));
        overflow-y: auto;
        z-index: 900;
        flex-shrink: 0;
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
        width: 100%;
      }

      .dashboard-welcome {
        margin-bottom: 30px;
      }

      .dashboard-welcome h1 {
        font-size: 1.75rem;
        color: var(--primary-color);
        margin-bottom: 5px;
      }

      /* --- STATISTIK --- */
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

      /* --- CARDS --- */
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
      }

      .card-btn:hover {
        background-color: var(--primary-hover);
      }

      /* --- NOTIFICATION LOGIN --- */
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

      /* --- STYLE MODAL LOGOUT (DIUBAH MIRIP LOGIN) --- */
      .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(3px);
        z-index: 2000;
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
      }

      .modal-overlay.active {
        display: flex;
        opacity: 1;
      }

      .modal-box {
        background: #fff;
        padding: 40px 30px;
        border-radius: var(--radius);
        /* Pakai radius yang sama dengan card login */
        width: 90%;
        max-width: 400px;
        text-align: center;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        border: 1px solid var(--border-color);
        transform: scale(0.9);
        transition: transform 0.3s ease;
      }

      .modal-overlay.active .modal-box {
        transform: scale(1);
      }

      .modal-icon {
        width: 80px;
        height: 80px;
        background: #fee2e2;
        /* Warna background soft red */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: var(--primary-color);
        font-size: 2rem;
      }

      .modal-box h3 {
        margin-bottom: 10px;
        font-size: 1.5rem;
        color: var(--text-color);
        font-weight: 700;
      }

      .modal-box p {
        color: var(--text-muted);
        margin-bottom: 30px;
        font-size: 0.95rem;
        line-height: 1.5;
      }

      .modal-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-direction: column;
        /* Default column untuk mobile feel, bisa diubah row jika lebar */
      }

      @media (min-width: 400px) {
        .modal-actions {
          flex-direction: row;
        }
      }

      .btn-modal {
        padding: 13px;
        border-radius: 10px;
        /* Sama seperti button login */
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
        font-size: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }

      /* Tombol Batal -> Mirip tombol secondary/back */
      .btn-cancel {
        background-color: #f1f5f9;
        color: var(--text-muted);
      }

      .btn-cancel:hover {
        background-color: #e2e8f0;
        color: var(--text-color);
      }

      /* Tombol Logout -> Mirip tombol Login (Merah) */
      .btn-logout {
        background-color: var(--primary-color);
        color: white;
      }

      .btn-logout:hover {
        background-color: var(--primary-hover);
        transform: translateY(-2px);
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
          left: auto;
          right: -260px;
          box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
          border-right: none;
          border-left: 1px solid var(--border-color);
          transition: right 0.3s ease;
        }

        .sidebar.active {
          right: 0;
        }

        .menu-toggle {
          display: block;
        }

        .logo span {
          display: none;
        }

        .profile-greeting {
          display: none;
        }
      }
    </style>
  </head>

  <body>

    <!-- HEADER -->
    <header>
      <nav class="navbar">
        <div class="nav-left">
          <div class="logo">
            <img src="../Gambar/logpmi.png" alt="Logo PMR">
            <span>PMR MILLENIUM</span>
          </div>
        </div>

        <div class="nav-center"></div>

        <div class="nav-right">
          <div class="profile-btn" id="profileBtn">
            <div class="profile-greeting">
              <small><?= ucfirst($role) ?></small>
              <span>Halo, <?= $nama_user ?></span>
            </div>
            <img src="<?= $foto_profil ?>" alt="Foto Profil" class="profile-img">
          </div>

          <div class="profile-dropdown" id="profileDropdown">
            <div class="dropdown-header">
              <p><?= $nama_user ?></p>
              <small><?= ucfirst($role) ?></small>
            </div>
            <ul>
              <li><a href="ganti_foto.php"><i class="fa-solid fa-camera"></i> Ganti Foto Profil</a></li>
              <li><a href="ganti_nama.php"><i class="fa-solid fa-user-pen"></i> Ganti Nama</a></li>
              <li><a href="ganti_password.php"><i class="fa-solid fa-key"></i> Ganti Password</a></li>
            </ul>
          </div>

          <button class="menu-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
        </div>
      </nav>
    </header>

    <!-- NOTIFIKASI LOGIN -->
    <?php if (isset($_SESSION['login_success'])): ?>
      <div id="loginNotification" class="notification">
        <i class="fas fa-check-circle"></i>
        <div>
          <div style="font-weight: 600;">Login Berhasil</div>
          <div style="font-size: 0.85rem; color: #666;">Selamat datang, <b><?= $nama_user ?></b>!</div>
        </div>
      </div>
      <?php unset($_SESSION['login_success']); ?>
    <?php endif; ?>

    <!-- MODAL LOGOUT (STYLE BARU) -->
    <div class="modal-overlay" id="logoutModal">
      <div class="modal-box">
        <div class="modal-icon">
          <i class="fa-solid fa-right-from-bracket"></i>
        </div>
        <h3>Konfirmasi Keluar</h3>
        <p>Apakah Anda yakin ingin keluar dari akun?</p>
        <div class="modal-actions">
          <button class="btn-modal btn-cancel" onclick="closeLogoutModal()">Batal</button>
          <button class="btn-modal btn-logout" onclick="proceedLogout()">Ya, Keluar</button>
        </div>
      </div>
    </div>

    <div class="dashboard-container">
      <!-- SIDEBAR -->
      <aside class="sidebar">
        <ul>
          <li class="active"><a href="anggota.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>

          <?php if ($role == 'pengurus'): ?>
            <li><a href="kelolaabsen.php"><i class="fa-solid fa-calendar-check"></i> Kelola Absensi</a></li>
            <li><a href="kelolaperpus.php"><i class="fa-solid fa-book"></i> Kelola Perpustakaan</a></li>
            <li><a href="kelola_pendaftaran.php"><i class="fa-solid fa-users"></i> Kelola Pendaftaran</a></li>
            <li><a href="kelola_beranda.php"><i class="fa-solid fa-pen-to-square"></i> Edit Halaman Utama</a></li>
            
          <?php else: ?>
            <li><a href="absensi.php"><i class="fa-solid fa-calendar-check"></i> Rekap Absensi</a></li>
            <li><a href="perpus.php"><i class="fa-solid fa-book"></i> Perpustakaan Digital</a></li>
          <?php endif; ?>

          <li style="margin-top: 20px; border-top: 1px solid #eee;">
            <a href="javascript:void(0)" onclick="openLogoutModal()">
              <i class="fa-solid fa-right-from-bracket"></i> Log Out
            </a>
          </li>
          <li>
            <a href="../Halaman Utama/index.php">
              <i class="fa-solid fa-globe"></i>Halaman Utama
            </a>
          </li>
        </ul>
      </aside>

      <!-- KONTEN UTAMA -->
      <main class="main-content">
        <div class="dashboard-welcome">
          <h1>Dashboard <?php echo ucfirst($role); ?></h1>
          <p>Halo, <b><?= $nama_user ?></b>! Selamat datang di portal.</p>
        </div>

        <!-- STATISTIK -->
        <div class="stats-grid">
          <?php if ($role == 'pengurus'): ?>
            <div class="stat-card">
              <div class="stat-icon bg-blue"><i class="fa-solid fa-users"></i></div>
              <div class="stat-info">
                <h2><?= $stat_total_anggota ?></h2>
                <p>Total Anggota</p>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon bg-green"><i class="fa-solid fa-calendar-check"></i></div>
              <div class="stat-info">
                <h2><?= $stat_total_hadir_bulan_ini ?></h2>
                <p>Kehadiran Bulan Ini</p>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon bg-orange"><i class="fa-solid fa-book"></i></div>
              <div class="stat-info">
                <h2><?= $stat_total_buku ?></h2>
                <p>Total Materi</p>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon bg-purple"><i class="fa-solid fa-user-plus"></i></div>
              <div class="stat-info">
                <h2><?= $stat_pendaftaran_baru ?></h2>
                <p>Pendaftaran Baru</p>
              </div>
            </div>
          <?php else: ?>
            <div class="stat-card">
              <div class="stat-icon bg-green"><i class="fa-solid fa-check-circle"></i></div>
              <div class="stat-info">
                <h2><?= $stat_hadir_saya ?></h2>
                <p>Total Kehadiran Saya</p>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon bg-blue"><i class="fa-solid fa-percent"></i></div>
              <div class="stat-info">
                <h2><?= $stat_persentase ?>%</h2>
                <p>Persentase Kehadiran</p>
              </div>
            </div>
            <div class="stat-card">
              <div class="stat-icon bg-orange"><i class="fa-solid fa-star"></i></div>
              <div class="stat-info">
                <h2><?= $stat_status ?></h2>
                <p>Status Keaktifan</p>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <div class="cards">
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
        const profileBtn = document.getElementById('profileBtn');
        const profileDropdown = document.getElementById('profileDropdown');

        // Toggle Sidebar
        if (menuToggle) {
          menuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle('active');
            profileDropdown.classList.remove('active');
          });
        }

        // Toggle Profile Dropdown
        profileBtn.addEventListener('click', (e) => {
          e.stopPropagation();
          profileDropdown.classList.toggle('active');
          sidebar.classList.remove('active');
        });

        // Tutup semua jika klik di luar area
        document.addEventListener('click', (e) => {
          if (window.innerWidth <= 992) {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
              sidebar.classList.remove('active');
            }
          }
          if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.remove('active');
          }
        });

        // Notifikasi Login
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

      // --- FUNGSI MODAL LOGOUT ---
      function openLogoutModal() {
        const modal = document.getElementById('logoutModal');
        modal.classList.add('active');
      }

      function closeLogoutModal() {
        const modal = document.getElementById('logoutModal');
        modal.classList.remove('active');
      }

      function proceedLogout() {
        window.location.href = "../logout.php";
      }

      // Tutup modal jika klik overlay
      document.getElementById('logoutModal').addEventListener('click', function(e) {
        if (e.target === this) {
          closeLogoutModal();
        }
      });
    </script>
  </body>

  </html>