<?php
// Tambahkan Timezone agar sinkron dengan waktu lokal
date_default_timezone_set('Asia/Jakarta');

session_start();
include '../koneksi.php';

// Cek Login
if (!isset($_SESSION['nama'])) {
  echo '<script>alert("Silakan login terlebih dahulu!"); window.location.href = "../Login/login.php";</script>';
  exit;
}

// --- LOGIKA AMBIL ID USER & ROLE ---
if (!isset($_SESSION['id'])) {
  $nama_session = $_SESSION['nama'];
  $stmt = mysqli_prepare($koneksi, "SELECT id, role FROM users WHERE nama = ?");
  mysqli_stmt_bind_param($stmt, "s", $nama_session);
  mysqli_stmt_execute($stmt);
  $result_id = mysqli_stmt_get_result($stmt);

  if (mysqli_num_rows($result_id) > 0) {
    $data_user = mysqli_fetch_assoc($result_id);
    $_SESSION['id'] = $data_user['id'];
    $_SESSION['role'] = $data_user['role'];
  } else {
    echo '<script>alert("Data user tidak ditemukan!"); window.location.href = "../logout.php";</script>';
    exit;
  }
}

$id_user = $_SESSION['id'];
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'anggota';
$nama_user = htmlspecialchars($_SESSION['nama']);

// Logika Foto Profil
$foto_session = isset($_SESSION['foto']) ? $_SESSION['foto'] : '';
$foto_profil = 'https://ui-avatars.com/api/?name=' . urlencode($nama_user) . '&background=d90429&color=fff';
if (!empty($foto_session) && file_exists("../uploads/foto_profil/" . $foto_session)) {
  $foto_profil = "../uploads/foto_profil/" . $foto_session;
}

// LOGIC: Kalender
$month = isset($_GET['m']) ? intval($_GET['m']) : date('m');
$year = isset($_GET['y']) ? intval($_GET['y']) : date('Y');

// Ambil riwayat absensi user di bulan/tahun tersebut
// Hanya ambil yang statusnya 'hadir' agar sinkron dengan logika pengurus
$query_absen = mysqli_query($koneksi, "SELECT tanggal, status FROM absensi WHERE user_id = '$id_user' AND MONTH(tanggal) = '$month' AND YEAR(tanggal) = '$year'");
$riwayat_absen = [];
while ($row = mysqli_fetch_assoc($query_absen)) {
  // Hanya simpan tanggal jika statusnya 'hadir'
  // Jika di DB pakai kapital (Hadir), gunakan strtolower
  if (strtolower($row['status']) == 'hadir') {
    $riwayat_absen[$row['tanggal']] = $row['status'];
  }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rekap Absensi | PMR Millenium</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="icon" href="../Gambar/logpmi.png" type="image/png">

  <style>
    :root {
      --primary-color: #d90429;
      --primary-hover: #c92a2a;
      --bg-color: #f8f9fa;
      --text-color: #1e293b;
      --text-muted: #64748b;
      --border-color: #e2e8f0;
      --success-color: #10b981;
      --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
      --radius: 12px;
      --header-height: 70px;
      --sidebar-width: 250px;
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

    /* HEADER & SIDEBAR */
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
      cursor: pointer;
      padding: 5px;
      border-radius: 50px;
      transition: background 0.2s;
    }

    .profile-btn:hover {
      background-color: #f1f5f9;
    }

    .profile-img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid var(--primary-color);
    }

    .profile-dropdown {
      position: absolute;
      top: 100%;
      right: 0;
      margin-top: 10px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
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

    .dashboard-container {
      display: flex;
      min-height: 100vh;
      padding-top: var(--header-height);
    }

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

    /* Main Content */
    .main-content {
      flex: 1;
      padding: 30px;
      width: 100%;
    }

    .page-title h1 {
      font-size: 1.75rem;
      color: var(--primary-color);
      margin-bottom: 5px;
    }

    .page-title p {
      color: var(--text-muted);
      font-size: 0.9rem;
      margin-bottom: 25px;
    }

    /* Status Box */
    .status-box {
      background: white;
      padding: 25px;
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      margin-bottom: 25px;
      text-align: center;
      border: 1px solid var(--border-color);
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 15px;
    }

    .status-box.inactive {
      background: #fee2e2;
      border-color: #fecaca;
    }

    .status-box.active {
      background: #dcfce7;
      border-color: #bbf7d0;
    }

    .status-icon {
      font-size: 3rem;
      margin-bottom: 10px;
    }

    .status-box.inactive .status-icon {
      color: var(--primary-color);
    }

    .status-box.active .status-icon {
      color: var(--success-color);
    }

    .status-title {
      font-size: 1.2rem;
      font-weight: 700;
      margin-bottom: 5px;
    }

    .status-time {
      font-size: 0.9rem;
      color: var(--text-muted);
    }

    /* Buttons */
    .btn {
      padding: 12px 25px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s ease;
      font-size: 0.95rem;
      color: white;
    }

    .btn-primary {
      background-color: var(--primary-color);
      box-shadow: 0 4px 6px rgba(217, 4, 41, 0.2);
    }

    .btn-primary:hover {
      background-color: var(--primary-hover);
      transform: translateY(-1px);
    }

    .btn-success {
      background-color: var(--success-color);
      box-shadow: 0 4px 6px rgba(16, 184, 129, 0.2);
    }

    .btn-success:hover {
      background-color: #059669;
      transform: translateY(-1px);
    }

    .btn:disabled {
      background-color: #cbd5e1;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    /* Calendar Styles */
    .calendar-container {
      background: white;
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      padding: 20px;
      border: 1px solid var(--border-color);
    }

    .calendar-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 1px solid var(--border-color);
    }

    .calendar-header h2 {
      font-size: 1.2rem;
      color: var(--text-color);
    }

    .calendar-nav {
      display: flex;
      gap: 10px;
    }

    .calendar-nav a {
      padding: 8px 15px;
      background: var(--bg-color);
      border-radius: 6px;
      color: var(--text-color);
      font-weight: 600;
      transition: 0.2s;
    }

    .calendar-nav a:hover {
      background: var(--primary-color);
      color: white;
    }

    .calendar-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      gap: 5px;
    }

    .calendar-day-name {
      text-align: center;
      font-weight: 600;
      color: var(--text-muted);
      font-size: 0.85rem;
      padding: 10px;
    }

    .calendar-day {
      border: 1px solid var(--border-color);
      border-radius: 8px;
      min-height: 80px;
      padding: 8px;
      position: relative;
      background: #fff;
      transition: 0.2s;
    }

    .calendar-day:hover {
      background: #f8fafc;
    }

    .calendar-day.empty {
      background: #f8f9fa;
      border-color: transparent;
    }

    .calendar-day.today {
      border-color: var(--primary-color);
      border-width: 2px;
    }

    .day-number {
      font-weight: 600;
      color: var(--text-muted);
      font-size: 0.9rem;
      margin-bottom: 5px;
    }

    .calendar-day.today .day-number {
      color: var(--primary-color);
    }

    .attendance-mark {
      display: flex;
      align-items: center;
      justify-content: center;
      height: calc(100% - 25px);
      color: var(--success-color);
      font-size: 2rem;
    }

    .attendance-mark i {
      background: #dcfce7;
      padding: 10px;
      border-radius: 50%;
    }

    /* Modal & Camera */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.6);
      z-index: 2000;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .modal-content {
      background: white;
      border-radius: var(--radius);
      max-width: 500px;
      width: 100%;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      position: relative;
      animation: fadeIn 0.3s ease;
    }

    .modal-header {
      padding: 15px 20px;
      background: var(--primary-color);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header h3 {
      font-size: 1.1rem;
    }

    .close-modal {
      background: none;
      border: none;
      color: white;
      font-size: 1.5rem;
      cursor: pointer;
      line-height: 1;
    }

    .modal-body {
      padding: 20px;
      overflow-y: auto;
    }

    .camera-wrapper {
      width: 100%;
      background: #1e293b;
      border-radius: 8px;
      overflow: hidden;
      margin: 15px 0;
      position: relative;
      aspect-ratio: 4/3;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    #video,
    #capturedImage {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    #video {
      transform: scaleX(-1);
    }

    #capturedImage {
      display: none;
    }

    .switch-cam-btn {
      position: absolute;
      top: 15px;
      right: 15px;
      background: rgba(255, 255, 255, 0.2);
      border: 2px solid rgba(255, 255, 255, 0.7);
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      color: white;
      z-index: 10;
    }

    /* TAMBAHAN: STYLE MODAL LOGOUT */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(4px);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 9999;
      opacity: 0;
      visibility: hidden;
      transition: all 0.3s ease;
    }

    .modal-overlay.active {
      opacity: 1;
      visibility: visible;
    }

    .modal-box {
      background: white;
      padding: 30px;
      border-radius: 16px;
      text-align: center;
      width: 90%;
      max-width: 400px;
      transform: scale(0.9);
      transition: transform 0.3s ease;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .modal-overlay.active .modal-box {
      transform: scale(1);
    }

    .modal-icon {
      width: 70px;
      height: 70px;
      background: #fee2e2;
      color: var(--primary-color);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 2rem;
    }

    .modal-box h3 {
      margin-bottom: 10px;
      color: var(--text-color);
    }

    .modal-box p {
      color: var(--text-muted);
      margin-bottom: 25px;
      font-size: 0.95rem;
    }

    .modal-actions {
      display: flex;
      gap: 15px;
      justify-content: center;
    }

    .btn-modal {
      padding: 12px 20px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      border: none;
      transition: 0.2s;
      font-size: 0.95rem;
      flex: 1;
    }

    .btn-cancel {
      background-color: #f1f5f9;
      color: var(--text-muted);
    }

    .btn-cancel:hover {
      background-color: #e2e8f0;
      color: var(--text-color);
    }

    .btn-logout {
      background-color: var(--primary-color);
      color: white;
    }

    .btn-logout:hover {
      background-color: var(--primary-hover);
    }

    .toast {
      position: fixed;
      top: 90px;
      right: 20px;
      background: white;
      color: var(--text-color);
      padding: 15px 20px;
      border-radius: 8px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 280px;
      z-index: 9999;
      transform: translateX(120%);
      transition: transform 0.3s ease-out;
      border-left: 5px solid var(--primary-color);
    }

    .toast.show {
      transform: translateX(0);
    }

    .toast.success {
      border-left-color: var(--success-color);
    }

    .toast.success i {
      color: var(--success-color);
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
        z-index: 999;
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

  <!-- MODAL LOGOUT -->
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
        <li><a href="anggota.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
        <li class="active"><a href="absensi.php"><i class="fa-solid fa-calendar-check"></i> Rekap Absensi</a></li>
        <li><a href="perpus.php"><i class="fa-solid fa-book"></i> Perpustakaan Digital</a></li>
        <li style="margin-top: 20px; border-top: 1px solid #eee;">
          <!-- UBAH: onclick sekarang memanggil modal -->
          <a href="javascript:void(0)" onclick="openLogoutModal()"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
        </li>
      </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <div class="page-title">
        <h1>Absensi & Kehadiran</h1>
        <p>Lakukan absensi harianmu dan pantau riwayat kehadiran.</p>
      </div>

      <!-- Status Box -->
      <section class="status-box" id="attendanceStatus">
        <div class="status-icon"><i class="fas fa-spinner fa-spin"></i></div>
        <div class="status-title">Memeriksa status absensi...</div>
      </section>

      <!-- Kalender Rekap -->
      <section class="calendar-container">
        <div class="calendar-header">
          <h2><?= date('F Y', strtotime("$year-$month-01")) ?></h2>
          <div class="calendar-nav">
            <?php
            $prev_month = $month - 1;
            $prev_year = $year;
            if ($prev_month == 0) {
              $prev_month = 12;
              $prev_year--;
            }
            $next_month = $month + 1;
            $next_year = $year;
            if ($next_month == 13) {
              $next_month = 1;
              $next_year++;
            }
            ?>
            <a href="?m=<?= $prev_month ?>&y=<?= $prev_year ?>"><i class="fas fa-chevron-left"></i></a>
            <a href="?m=<?= date('m') ?>&y=<?= date('Y') ?>">Hari Ini</a>
            <a href="?m=<?= $next_month ?>&y=<?= $next_year ?>"><i class="fas fa-chevron-right"></i></a>
          </div>
        </div>

        <div class="calendar-grid">
          <div class="calendar-day-name">Min</div>
          <div class="calendar-day-name">Sen</div>
          <div class="calendar-day-name">Sel</div>
          <div class="calendar-day-name">Rab</div>
          <div class="calendar-day-name">Kam</div>
          <div class="calendar-day-name">Jum</div>
          <div class="calendar-day-name">Sab</div>

          <?php
          $first_day = date('w', strtotime("$year-$month-01"));
          $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
          $today = date('Y-m-d');

          for ($i = 0; $i < $first_day; $i++) {
            echo "<div class='calendar-day empty'></div>";
          }

          for ($day = 1; $day <= $days_in_month; $day++) {
            $date_val = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
            $is_today = ($date_val == $today) ? 'today' : '';
            echo "<div class='calendar-day $is_today'>";
            echo "<div class='day-number'>$day</div>";
            // LOGIC: Hanya tampilkan centang jika ada di array $riwayat_absen (yg sudah difilter 'hadir')
            if (isset($riwayat_absen[$date_val])) {
              echo "<div class='attendance-mark' title='Hadir'><i class='fas fa-check-circle'></i></div>";
            }
            echo "</div>";
          }
          ?>
        </div>
      </section>
    </main>
  </div>

  <!-- MODAL CAMERA -->
  <div class="modal" id="cameraModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Ambil Foto Absensi</h3>
        <button class="close-modal" id="btnCloseModal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="camera-wrapper">
          <button class="switch-cam-btn" id="btnSwitchCamera" title="Ganti Kamera"><i class="fa-solid fa-camera-rotate"></i></button>
          <video id="video" autoplay playsinline></video>
          <canvas id="canvas" style="display:none;"></canvas>
          <img id="capturedImage" alt="Capture">
        </div>

        <div id="cameraControls" style="margin-top: 15px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
          <button class="btn btn-primary" id="btnCapture"><i class="fa-solid fa-camera"></i> Ambil Foto</button>
          <button class="btn btn-success" id="btnSubmit" style="display:none; width: 100%;"><i class="fa-solid fa-paper-plane"></i> Kirim Absensi</button>
        </div>

        <div id="successMessage" style="display:none; text-align:center;">
          <div style="color: var(--success-color); font-size: 3rem; margin: 20px 0;"><i class="fa-solid fa-check-circle"></i></div>
          <h4>Absensi Berhasil!</h4>
          <button class="btn btn-primary" style="margin-top: 15px;" onclick="location.reload()">Selesai</button>
        </div>
      </div>
    </div>
  </div>

  <div id="toastContainer"></div>

  <script>
    // --- LOGIC DROPDOWN & SIDEBAR ---
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');

    menuToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      sidebar.classList.toggle('active');
      profileDropdown.classList.remove('active');
    });
    profileBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      profileDropdown.classList.toggle('active');
      sidebar.classList.remove('active');
    });
    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 992) {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) sidebar.classList.remove('active');
      }
      if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) profileDropdown.classList.remove('active');
    });

    // --- REALTIME STATUS CHECK ---
    const statusBox = document.getElementById('attendanceStatus');

    async function checkAttendanceStatus() {
      try {
        const res = await fetch('get_status_absen.php');
        const data = await res.json();
        renderStatus(data);
      } catch (error) {
        statusBox.innerHTML = `<div class="status-icon"><i class="fas fa-exclamation-triangle"></i></div><div class="status-title">Gagal memuat status</div><div class="status-time">Periksa koneksi atau file get_status_absen.php</div>`;
        statusBox.className = 'status-box inactive';
      }
    }

    function renderStatus(data) {
      if (data.is_open) {
        statusBox.className = 'status-box active';
        statusBox.innerHTML = `
          <div class="status-icon"><i class="fas fa-door-open"></i></div>
          <div class="status-title">Absensi Dibuka</div>
          <div class="status-time">Waktu: ${data.jam_mulai} - ${data.jam_selesai} WIB</div>
          <button class="btn btn-primary" id="btnOpenCamera"><i class="fa-solid fa-camera"></i> Absensi Sekarang</button>`;
        document.getElementById('btnOpenCamera').onclick = () => {
          document.getElementById('cameraModal').style.display = 'flex';
          resetModal();
          startCamera(useFrontCamera ? 'user' : 'environment');
        };
      } else {
        statusBox.className = 'status-box inactive';
        let message = data.message || "Absensi Belum Dibuka";
        statusBox.innerHTML = `
          <div class="status-icon"><i class="fas fa-door-closed"></i></div>
          <div class="status-title">${message}</div>
          <div class="status-time">Silakan tunggu pengurus membuka absensi.</div>`;
      }
    }

    checkAttendanceStatus();
    setInterval(checkAttendanceStatus, 5000);

    // --- LOGIKA KAMERA ---
    let currentImageData = null;
    let stream = null;
    let useFrontCamera = true;
    const modal = document.getElementById('cameraModal');
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const capturedImage = document.getElementById('capturedImage');
    const btnCapture = document.getElementById('btnCapture');
    const btnSwitch = document.getElementById('btnSwitchCamera');
    const btnSubmit = document.getElementById('btnSubmit');

    async function startCamera(facingMode) {
      try {
        if (stream) stream.getTracks().forEach(track => track.stop());
        stream = await navigator.mediaDevices.getUserMedia({
          video: {
            facingMode: facingMode,
            width: {
              ideal: 1280
            },
            height: {
              ideal: 720
            }
          }
        });
        video.srcObject = stream;
        video.style.display = 'block';
        capturedImage.style.display = 'none';
        btnSwitch.style.display = 'flex';
        btnCapture.style.display = 'inline-flex';
        btnSubmit.style.display = 'none';
        video.style.transform = facingMode === 'user' ? 'scaleX(-1)' : 'scaleX(1)';
      } catch (err) {
        alert("Tidak dapat mengakses kamera.");
        console.error(err);
      }
    }

    document.getElementById('btnCloseModal').onclick = () => {
      modal.style.display = 'none';
      stopCamera();
    };
    window.onclick = (e) => {
      if (e.target == modal) {
        modal.style.display = 'none';
        stopCamera();
      }
    };

    function stopCamera() {
      if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
      }
    }

    btnSwitch.onclick = async () => {
      useFrontCamera = !useFrontCamera;
      await startCamera(useFrontCamera ? 'user' : 'environment');
    };

    btnCapture.onclick = () => {
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      let ctx = canvas.getContext('2d');
      if (useFrontCamera) {
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
      }
      ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
      if (useFrontCamera) ctx.setTransform(1, 0, 0, 1, 0, 0);
      currentImageData = canvas.toDataURL('image/png');
      capturedImage.src = currentImageData;
      video.style.display = 'none';
      capturedImage.style.display = 'block';
      stopCamera();
      btnSwitch.style.display = 'none';
      btnCapture.style.display = 'none';
      btnSubmit.style.display = 'inline-flex';
    };

    btnSubmit.onclick = () => {
      if (!currentImageData) {
        alert("Ambil foto dulu!");
        return;
      }
      btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
      btnSubmit.disabled = true;
      fetch('proses_absensi.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            foto: currentImageData
          })
        })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success') {
            document.getElementById('cameraControls').style.display = 'none';
            document.getElementById('successMessage').style.display = 'block';
          } else {
            alert('Error: ' + data.message);
            btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Absensi';
            btnSubmit.disabled = false;
          }
        })
        .catch(err => {
          console.error(err);
          alert('Gagal mengirim data.');
          btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Absensi';
          btnSubmit.disabled = false;
        });
    };

    function resetModal() {
      video.style.display = 'block';
      capturedImage.style.display = 'none';
      document.getElementById('cameraControls').style.display = 'flex';
      document.getElementById('successMessage').style.display = 'none';
      btnCapture.style.display = 'inline-flex';
      btnSubmit.style.display = 'none';
      btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Absensi';
      btnSubmit.disabled = false;
      currentImageData = null;
    }

    // --- FUNGSI MODAL LOGOUT ---
    function openLogoutModal() {
      document.getElementById('logoutModal').classList.add('active');
    }

    function closeLogoutModal() {
      document.getElementById('logoutModal').classList.remove('active');
    }

    function proceedLogout() {
      window.location.href = "../logout.php";
    }

    // Close modal jika klik overlay (area luar kotak)
    document.getElementById('logoutModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeLogoutModal();
      }
    });
  </script>
</body>

</html>