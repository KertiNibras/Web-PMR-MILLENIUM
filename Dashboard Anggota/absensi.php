<?php
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
    $_SESSION['role'] = $data_user['role']; // Pastikan role ada di session
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

// Ambil riwayat absensi user ini
$query_absen = mysqli_query($koneksi, "SELECT * FROM absensi WHERE user_id = '$id_user' ORDER BY tanggal DESC, jam DESC");
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
    /* CSS Variabel & Reset */
    :root {
      --primary-color: #d90429;
      --primary-hover: #c92a2a;
      --bg-color: #f8f9fa;
      --card-bg: #ffffff;
      --text-color: #1e293b;
      --text-muted: #64748b;
      --border-color: #e2e8f0;
      --success-color: #10b981;
      --warning-color: #f59e0b;
      --info-color: #3b82f6;
      --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
      --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.05);
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

    /* --- HEADER (Layout 3 Kolom - Sama dengan anggota.php) --- */
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

    /* Kiri: Logo */
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

    /* Tengah: Kosong (Penyeimbang) */
    .nav-center {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    /* Kanan: Profil & Menu */
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

    /* Dropdown Profil */
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

    /* Tombol Hamburger */
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

    /* --- SIDEBAR (Perilaku sama dengan anggota.php) --- */
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

    /* Filter & Header Box */
    .content-header {
      background: white;
      padding: 20px;
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      margin-bottom: 25px;
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      align-items: flex-end;
      border: 1px solid var(--border-color);
    }

    .filter-group {
      flex: 1;
      min-width: 150px;
    }

    .filter-group label {
      display: block;
      margin-bottom: 8px;
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--text-muted);
    }

    .filter-control {
      width: 100%;
      padding: 10px 15px;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      font-size: 0.95rem;
      outline: none;
    }

    .filter-control:focus {
      border-color: var(--primary-color);
    }

    /* Buttons */
    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s ease;
      font-size: 0.9rem;
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
    }

    .btn-secondary {
      background-color: var(--text-muted);
      background-color: #94a3b8;
    }

    .btn-info {
      background-color: var(--info-color);
    }

    /* Table */
    .table-container {
      background: white;
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      padding: 20px;
      overflow-x: auto;
      border: 1px solid var(--border-color);
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
      min-width: 600px;
    }

    .data-table th {
      background-color: var(--primary-color);
      color: white;
      text-align: left;
      padding: 15px;
      font-weight: 600;
      border-radius: 0;
      /* Reset radius untuk th */
    }

    /* Agar sudut tabel rapi */
    .data-table th:first-child {
      border-top-left-radius: var(--radius);
    }

    .data-table th:last-child {
      border-top-right-radius: var(--radius);
    }

    .data-table td {
      padding: 15px;
      border-bottom: 1px solid var(--border-color);
      vertical-align: middle;
    }

    .data-table tr:last-child td {
      border-bottom: none;
    }

    .data-table tr:hover {
      background-color: #f8fafc;
    }

    /* Status Badge */
    .status-badge {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      text-align: center;
      min-width: 80px;
      text-transform: capitalize;
    }

    .status-h {
      color: var(--success-color);
      background: rgba(16, 185, 129, 0.1);
    }

    .status-i {
      color: var(--warning-color);
      background: rgba(245, 158, 11, 0.1);
    }

    .status-s {
      color: var(--info-color);
      background: rgba(59, 130, 246, 0.1);
    }

    /* Thumbnail */
    .history-thumb {
      width: 40px;
      height: 40px;
      object-fit: cover;
      border-radius: 6px;
      cursor: pointer;
      border: 1px solid var(--border-color);
      transition: 0.2s;
    }

    .history-thumb:hover {
      transform: scale(1.1);
      border-color: var(--primary-color);
    }

    /* Modal */
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

    /* Form Input di Modal */
    .form-group {
      margin-bottom: 15px;
      text-align: left;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--text-muted);
    }

    .form-control {
      width: 100%;
      padding: 10px 15px;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      font-size: 0.95rem;
      outline: none;
    }

    .form-control:focus {
      border-color: var(--primary-color);
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

    /* Toast */
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

    .toast.info {
      border-left-color: var(--info-color);
    }

    .toast.info i {
      color: var(--info-color);
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

    /* --- RESPONSIVE --- */
    @media (max-width: 992px) {
      .main-content {
        width: 100%;
        padding: 20px;
      }

      /* Sidebar Muncul dari Kanan */
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

      /* Sembunyikan teks logo di mobile */

      .content-header {
        flex-direction: column;
        align-items: stretch;
      }

      .content-header .btn {
        width: 100%;
        justify-content: center;
      }

      .filter-actions {
        flex-direction: column;
        gap: 10px !important;
      }
    }
  </style>
</head>

<body>

  <!-- HEADER -->
  <header>
    <nav class="navbar">
      <!-- KOLOM KIRI: LOGO -->
      <div class="nav-left">
        <div class="logo">
          <img src="../Gambar/logpmi.png" alt="Logo PMR">
          <span>PMR MILLENIUM</span>
        </div>
      </div>

      <!-- KOLOM TENGAH -->
      <div class="nav-center"></div>

      <!-- KOLOM KANAN: PROFILE & MENU -->
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
            <li>
              <a href="ganti_foto.php"><i class="fa-solid fa-camera"></i> Ganti Foto Profil</a>
            </li>
            <li>
              <a href="ganti_nama.php"><i class="fa-solid fa-user-pen"></i> Ganti Nama</a> <!-- UBAH INI -->
            </li>
            <li>
              <a href="ganti_password.php"><i class="fa-solid fa-key"></i> Ganti Password</a>
            </li>
          </ul>
        </div>

        <button class="menu-toggle" aria-label="Menu"><i class="fa-solid fa-bars"></i></button>
      </div>
    </nav>
  </header>

  <div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar">
      <ul>
        <li><a href="anggota.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
        <li class="active"><a href="absensi.php"><i class="fa-solid fa-calendar-check"></i> Rekap Absensi</a></li>
        <li><a href="perpus.php"><i class="fa-solid fa-book"></i> Perpustakaan Digital</a></li>
        <li style="margin-top: 20px; border-top: 1px solid #eee;">
          <a href="javascript:void(0)" onclick="confirmLogout()">
            <i class="fa-solid fa-right-from-bracket"></i> Log Out
          </a>
        </li>
      </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <div class="page-title">
        <h1>Riwayat Absensi</h1>
        <p>Catat kehadiranmu dan pantau riwayat kegiatan PMR.</p>
      </div>

      <!-- Action Bar & Filter -->
      <section class="content-header">
        <div style="display: flex; gap: 10px;">
          <button class="btn btn-primary" id="btnOpenCamera">
            <i class="fa-solid fa-camera"></i> Absensi Wajah
          </button>
        </div>

        <!-- Filter -->
        <div class="filter-group">
          <label for="sortFilter">Urutkan</label>
          <select id="sortFilter" class="filter-control">
            <option value="newest">Terbaru</option>
            <option value="oldest">Terlama</option>
          </select>
        </div>

        <div class="filter-group">
          <label for="filterStatus">Status</label>
          <select id="filterStatus" class="filter-control">
            <option value="">Semua Status</option>
            <option value="hadir">Hadir</option>
            <option value="izin">Izin</option>
            <option value="sakit">Sakit</option>
          </select>
        </div>

        <div class="filter-actions" style="display: flex; gap: 10px;">
          <button class="btn btn-secondary" id="resetFilter"><i class="fas fa-redo"></i> Reset</button>
          <button class="btn btn-primary" id="applyFilter"><i class="fas fa-filter"></i> Filter</button>
        </div>
      </section>

      <!-- Table -->
      <section class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>Waktu Absensi</th>
              <th>Foto</th>
              <th>Status</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <?php
            if (mysqli_num_rows($query_absen) > 0) {
              while ($row = mysqli_fetch_assoc($query_absen)) {
                $tgl = date('d M Y', strtotime($row['tanggal']));
                $jam = $row['jam'];
                $status_class = 'status-' . substr($row['status'], 0, 1);
                $timestamp_sort = $row['tanggal'] . 'T' . $jam;
                $foto_path = '../uploads/absensi/' . $row['foto'];

                echo "<tr data-timestamp='{$timestamp_sort}' data-status='{$row['status']}'>";
                echo "<td>{$tgl}, {$jam}</td>";
                echo "<td><img src='{$foto_path}' class='history-thumb' onclick='openHistoryPhoto(this.src)' onerror=\"this.src='../Gambar/default.jpg'\"></td>";
                echo "<td><span class='status-badge {$status_class}'>{$row['status']}</span></td>";
                echo "<td>{$row['keterangan']}</td>";
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='4' style='text-align:center; padding:30px; color:#999;'>Belum ada data absensi.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </section>
    </main>
  </div>

  <!-- MODAL CAMERA -->
  <div class="modal" id="cameraModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Formulir Absensi</h3>
        <button class="close-modal" id="btnCloseModal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Status Kehadiran</label>
          <select id="statusSelect" class="form-control">
            <option value="hadir">Hadir</option>
            <option value="izin">Izin</option>
            <option value="sakit">Sakit</option>
          </select>
        </div>

        <div class="form-group">
          <label>Keterangan</label>
          <textarea id="keteranganInput" class="form-control" rows="2" placeholder="Contoh: Sakit demam (jika status sakit/izin)"></textarea>
        </div>

        <div class="camera-wrapper">
          <button class="switch-cam-btn" id="btnSwitchCamera" title="Ganti Kamera">
            <i class="fa-solid fa-camera-rotate"></i>
          </button>
          <video id="video" autoplay playsinline></video>
          <canvas id="canvas" style="display:none;"></canvas>
          <img id="capturedImage" alt="Capture">
        </div>

        <div id="cameraControls" style="margin-top: 15px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
          <button class="btn btn-primary" id="btnCapture"><i class="fa-solid fa-camera"></i> Ambil Foto</button>
          <button class="btn btn-info" id="btnGallery" style="display: inline-flex;">
            <i class="fa-solid fa-images"></i> Dari Galeri
          </button>
          <input type="file" id="fileInput" accept="image/*" style="display: none;">

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

  <!-- MODAL PREVIEW FOTO -->
  <div class="modal" id="historyPhotoModal" onclick="this.style.display='none'">
    <div class="modal-content" style="max-width: 600px;" onclick="event.stopPropagation()">
      <div class="modal-header">
        <h3>Bukti Foto</h3>
        <button class="close-modal" onclick="document.getElementById('historyPhotoModal').style.display='none'">&times;</button>
      </div>
      <div class="modal-body" style="padding:0;">
        <img id="historyPhotoSrc" src="" style="width:100%; display:block;">
      </div>
    </div>
  </div>

  <!-- Toast Container -->
  <div id="toastContainer"></div>

  <script>
    // --- LOGIKA DROPDOWN PROFIL & SIDEBAR ---
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

    // --- LOGIKA FILTER TABEL ---
    const tableBody = document.getElementById("tableBody");
    const originalRows = Array.from(tableBody.querySelectorAll("tr")).map(row => row.cloneNode(true));
    const btnApply = document.getElementById("applyFilter");
    const btnReset = document.getElementById("resetFilter");
    const sortFilter = document.getElementById("sortFilter");
    const statusFilter = document.getElementById("filterStatus");
    const toastContainer = document.getElementById('toastContainer');

    btnApply.addEventListener("click", applyFilter);
    btnReset.addEventListener("click", resetFilter);

    function applyFilter() {
      let rowsToProcess = originalRows.map(row => row.cloneNode(true));
      const selectedStatus = statusFilter.value.toLowerCase();
      const selectedSort = sortFilter.value;

      let filteredRows = rowsToProcess.filter(row => {
        const rowStatus = row.getAttribute('data-status').toLowerCase();
        return (selectedStatus === "" || rowStatus === selectedStatus);
      });

      filteredRows.sort((a, b) => {
        const timeA = a.getAttribute('data-timestamp');
        const timeB = b.getAttribute('data-timestamp');
        const dateA = new Date(timeA);
        const dateB = new Date(timeB);
        if (isNaN(dateA)) return 1;
        if (isNaN(dateB)) return -1;
        return selectedSort === "newest" ? dateB - dateA : dateA - dateB;
      });

      tableBody.innerHTML = "";
      filteredRows.forEach(row => tableBody.appendChild(row));
      showToast(`${filteredRows.length} data ditemukan`, 'info');
    }

    function resetFilter() {
      sortFilter.value = "newest";
      statusFilter.value = "";
      let defaultRows = originalRows.map(row => row.cloneNode(true));
      defaultRows.sort((a, b) => new Date(b.getAttribute('data-timestamp')) - new Date(a.getAttribute('data-timestamp')));
      tableBody.innerHTML = "";
      defaultRows.forEach(row => tableBody.appendChild(row));
      showToast('Filter direset', 'info');
    }

    function showToast(msg, type) {
      const toast = document.createElement('div');
      toast.className = `toast ${type}`;
      let icon = 'fa-info-circle';
      toast.innerHTML = `<i class="fas ${icon}"></i> <span>${msg}</span>`;
      toastContainer.appendChild(toast);
      requestAnimationFrame(() => toast.classList.add('show'));
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    function openHistoryPhoto(src) {
      document.getElementById('historyPhotoSrc').src = src;
      document.getElementById('historyPhotoModal').style.display = 'flex';
    }

    function confirmLogout() {
      if (confirm('Yakin keluar?')) window.location.href = '../logout.php';
    }

    // --- LOGIKA KAMERA & UPLOAD ---
    let currentImageData = null;
    let stream = null;
    let useFrontCamera = true;

    const modal = document.getElementById('cameraModal');
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const capturedImage = document.getElementById('capturedImage');
    const btnCapture = document.getElementById('btnCapture');
    const btnGallery = document.getElementById('btnGallery');
    const btnSwitch = document.getElementById('btnSwitchCamera');
    const fileInput = document.getElementById('fileInput');
    const btnSubmit = document.getElementById('btnSubmit');

    async function startCamera(facingMode) {
      try {
        if (stream) stream.getTracks().forEach(track => track.stop());
        const constraints = {
          video: {
            facingMode: facingMode,
            width: {
              ideal: 1280
            },
            height: {
              ideal: 720
            }
          }
        };
        stream = await navigator.mediaDevices.getUserMedia(constraints);
        video.srcObject = stream;
        video.style.display = 'block';
        capturedImage.style.display = 'none';
        btnSwitch.style.display = 'flex';
        btnCapture.style.display = 'inline-flex';
        btnGallery.style.display = 'inline-flex';
        btnSubmit.style.display = 'none';
        if (facingMode === 'user') video.style.transform = 'scaleX(-1)';
        else video.style.transform = 'scaleX(1)';
      } catch (err) {
        console.error("Error accessing camera: ", err);
        alert("Tidak dapat mengakses kamera. Pastikan izin kamera diberikan.");
      }
    }

    document.getElementById('btnOpenCamera').onclick = () => {
      modal.style.display = 'flex';
      resetModal();
      startCamera(useFrontCamera ? 'user' : 'environment');
    };

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
      showSubmitButton();
    };

    btnGallery.onclick = () => fileInput.click();

    fileInput.onchange = (e) => {
      const file = e.target.files[0];
      if (file) {
        if (file.size > 5 * 1024 * 1024) {
          alert("Ukuran file terlalu besar (Maks 5MB)");
          return;
        }
        const reader = new FileReader();
        reader.onload = (event) => {
          currentImageData = event.target.result;
          capturedImage.src = currentImageData;
          video.style.display = 'none';
          capturedImage.style.display = 'block';
          btnSwitch.style.display = 'none';
          stopCamera();
          showSubmitButton();
        };
        reader.readAsDataURL(file);
      }
    };

    function showSubmitButton() {
      btnCapture.style.display = 'none';
      btnGallery.style.display = 'none';
      btnSubmit.style.display = 'inline-flex';
    }

    btnSubmit.onclick = () => {
      const status = document.getElementById('statusSelect').value;
      const keterangan = document.getElementById('keteranganInput').value;

      if (status !== 'hadir' && keterangan === '') {
        alert("Mohon isi keterangan untuk status Izin/Sakit.");
        return;
      }
      if (!currentImageData) {
        alert("Ambil foto atau pilih gambar dulu!");
        return;
      }

      if (confirm("Yakin mengirim absensi?")) {
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
        btnSubmit.disabled = true;

        fetch('proses_absensi.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              kegiatan: 'Absensi Harian',
              foto: currentImageData,
              status: status,
              keterangan: keterangan
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
            alert('Gagal mengirim data ke server.');
            btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Absensi';
            btnSubmit.disabled = false;
          });
      }
    };

    function resetModal() {
      video.style.display = 'block';
      capturedImage.style.display = 'none';
      document.getElementById('cameraControls').style.display = 'flex';
      document.getElementById('successMessage').style.display = 'none';
      btnCapture.style.display = 'inline-flex';
      btnGallery.style.display = 'inline-flex';
      btnSubmit.style.display = 'none';
      btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Absensi';
      btnSubmit.disabled = false;
      currentImageData = null;
      document.getElementById('statusSelect').value = 'hadir';
      document.getElementById('keteranganInput').value = '';
      fileInput.value = '';
    }
  </script>
</body>

</html>