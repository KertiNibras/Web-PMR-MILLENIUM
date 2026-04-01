<?php
session_start();
include '../koneksi.php'; // Panggil koneksi DB

// Cek Login
if (!isset($_SESSION['nama'])) {
  header("Location: ../Login/login.php");
  exit;
}

// CEK ROLE: Hanya Pengurus yang boleh akses
if ($_SESSION['role'] != 'pengurus') {
  echo '<script>alert("AKSES DITOLAK! Halaman ini khusus Pengurus.");';
  echo 'window.location.href="../Dashboard Anggota/anggota.php";</script>';
  exit;
}

// Ambil Data User untuk Header
$nama_user = htmlspecialchars($_SESSION['nama']);
$role = $_SESSION['role'];
$foto_session = isset($_SESSION['foto']) ? $_SESSION['foto'] : '';
$foto_profil = 'https://ui-avatars.com/api/?name=' . urlencode($nama_user) . '&background=d90429&color=fff';
if (!empty($foto_session) && file_exists("../uploads/foto_profil/" . $foto_session)) {
  $foto_profil = "../uploads/foto_profil/" . $foto_session;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Perpustakaan | PMR Millenium</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
      --success-color: #10b981;
      --warning-color: #f59e0b;
      --danger-color: #ef4444;
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

    /* --- HEADER (Layout 3 Kolom) --- */
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

    /* Tengah */
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

    .page-header {
      margin-bottom: 25px;
    }

    .page-header h1 {
      font-size: 1.75rem;
      color: var(--primary-color);
      margin-bottom: 5px;
    }

    .page-header p {
      color: var(--text-muted);
      font-size: 0.95rem;
    }

    /* --- BUTTONS --- */
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

    .btn-success:hover {
      background-color: #0d9668;
    }

    .btn-secondary {
      background-color: var(--text-muted);
    }

    /* --- FILTER --- */
    .filter-container {
      background: white;
      padding: 20px;
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      margin-bottom: 30px;
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
      align-items: flex-end;
      border: 1px solid var(--border-color);
    }

    .filter-item {
      flex: 1;
      min-width: 150px;
    }

    .filter-item label {
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
      background-color: #fff;
    }

    .filter-control:focus {
      border-color: var(--primary-color);
    }

    /* --- GRID & CARD --- */
    .materials-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 25px;
    }

    .material-card {
      background-color: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      transition: all 0.3s ease;
      border: 1px solid var(--border-color);
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .material-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-md);
      border-color: rgba(217, 4, 41, 0.3);
    }

    .card-top {
      padding: 20px;
      display: flex;
      align-items: flex-start;
      gap: 15px;
    }

    .file-icon {
      width: 45px;
      height: 45px;
      background: #ffebee;
      color: var(--primary-color);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      flex-shrink: 0;
    }

    .card-header-content {
      flex: 1;
      overflow: hidden;
    }

    .material-category {
      font-size: 0.75rem;
      text-transform: uppercase;
      font-weight: 700;
      letter-spacing: 0.5px;
      color: var(--primary-color);
      margin-bottom: 4px;
    }

    .material-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--text-color);
      line-height: 1.4;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .card-body {
      padding: 0 20px 20px 20px;
      flex-grow: 1;
    }

    .material-description {
      font-size: 0.9rem;
      color: var(--text-muted);
      line-height: 1.5;
      display: -webkit-box;
      -webkit-line-clamp: 3;
      line-clamp: 3;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .card-footer {
      padding: 15px 20px;
      border-top: 1px solid var(--border-color);
      background-color: #fafbfc;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .card-meta {
      font-size: 0.8rem;
      color: #999;
    }

    /* Action Buttons */
    .card-actions {
      display: flex;
      gap: 8px;
    }

    .action-btn {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      border: none;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-edit {
      background-color: #fff3cd;
      color: #856404;
    }

    .btn-edit:hover {
      background-color: #ffeeba;
    }

    .btn-delete {
      background-color: #f8d7da;
      color: #721c24;
    }

    .btn-delete:hover {
      background-color: #f5c6cb;
    }

    /* --- MODAL --- */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 2000;
      align-items: center;
      justify-content: center;
      padding: 20px;
      backdrop-filter: blur(4px);
    }

    .modal-content {
      background-color: white;
      border-radius: var(--radius);
      width: 100%;
      max-width: 500px;
      overflow: hidden;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      animation: modalPop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    @keyframes modalPop {
      from {
        opacity: 0;
        transform: scale(0.8);
      }

      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    .modal-header {
      padding: 20px 25px;
      background-color: var(--primary-color);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header h3 {
      font-size: 1.25rem;
      font-weight: 600;
    }

    .close-btn {
      background: none;
      border: none;
      color: white;
      font-size: 1.5rem;
      cursor: pointer;
      opacity: 0.8;
    }

    .close-btn:hover {
      opacity: 1;
    }

    .modal-body {
      padding: 25px;
      max-height: 80vh;
      overflow-y: auto;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--text-color);
      font-size: 0.9rem;
    }

    .form-control {
      width: 100%;
      padding: 12px;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      font-size: 1rem;
      font-family: inherit;
    }

    .form-control:focus {
      border-color: var(--primary-color);
      outline: none;
      box-shadow: 0 0 0 3px rgba(217, 4, 41, 0.1);
    }

    textarea.form-control {
      resize: vertical;
      min-height: 100px;
    }

    /* File Upload */
    .file-upload-wrapper {
      border: 2px dashed var(--border-color);
      border-radius: 8px;
      padding: 30px 20px;
      text-align: center;
      transition: all 0.3s;
      cursor: pointer;
      background: #fafafa;
    }

    .file-upload-wrapper:hover {
      border-color: var(--primary-color);
      background: #fff0f3;
    }

    .file-upload-icon {
      font-size: 2.5rem;
      color: var(--primary-color);
      margin-bottom: 10px;
    }

    .file-upload-text {
      color: var(--text-muted);
      margin-bottom: 5px;
      font-size: 0.95rem;
    }

    .file-name-display {
      font-size: 0.85rem;
      color: var(--success-color);
      font-weight: 600;
      margin-top: 8px;
    }

    /* --- TOAST --- */
    .toast-container {
      position: fixed;
      bottom: 30px;
      right: 30px;
      z-index: 9999;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .toast {
      background: white;
      color: var(--text-color);
      padding: 15px 20px;
      border-radius: 8px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 300px;
      border-left: 5px solid var(--primary-color);
      transform: translateX(120%);
      transition: transform 0.3s ease-out;
    }

    .toast.show {
      transform: translateX(0);
    }

    .toast.success {
      border-left-color: var(--success-color);
    }

    .toast.error {
      border-left-color: var(--danger-color);
    }

    .toast.success i {
      color: var(--success-color);
    }

    .toast.error i {
      color: var(--danger-color);
    }

.btn-modal {
      padding: 13px;
      border-radius: 10px; /* Sama seperti button login */
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

      .page-header {
        text-align: center;
      }

      .page-header .btn {
        width: 100%;
        justify-content: center;
        margin-top: 15px;
      }

      .filter-container {
        flex-direction: column;
        align-items: stretch;
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
        <li><a href="../Dashboard Anggota/anggota.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
        <li><a href="kelolaabsen.php"><i class="fa-solid fa-calendar-check"></i> Kelola Absensi</a></li>
        <li class="active"><a href="kelolaperpus.php"><i class="fa-solid fa-book"></i> Kelola Perpustakaan Digital</a></li>
        <li><a href="kelola_pendaftaran.php"><i class="fa-solid fa-users"></i> Kelola Pendaftaran</a></li>
        <li style="margin-top: 20px; border-top: 1px solid #eee;">
          <a href="javascript:void(0)" onclick="confirmLogout()">
            <i class="fa-solid fa-right-from-bracket"></i> Log Out
          </a>
        </li>
      </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <div class="page-header">
        <h1>Kelola Materi Perpustakaan Digital</h1>
        <p>Kelola dokumen PDF, materi pelatihan, dan panduan untuk anggota.</p>
        <button class="btn btn-primary" id="addMaterialBtn" style="margin-top: 15px;">
          <i class="fas fa-plus"></i> Tambah Materi Baru
        </button>
      </div>

      <!-- Filter Section -->
      <section class="filter-container">
        <div class="filter-item" style="flex: 2;">
          <label for="searchFilter">Cari Materi</label>
          <div style="position: relative;">
            <i class="fas fa-search" style="position: absolute; left: 15px; top: 12px; color: #aaa;"></i>
            <input type="text" id="searchFilter" class="filter-control" placeholder="Ketik judul atau deskripsi..." style="padding-left: 40px;">
          </div>
        </div>
        <div class="filter-item">
          <label for="categoryFilter">Kategori</label>
          <select id="categoryFilter" class="filter-control">
            <option value="">Semua Kategori</option>
            <option value="P3K">P3K</option>
            <option value="Kepalangmerahan">Kepalangmerahan</option>
            <option value="Pertolongan Bencana">Pertolongan Bencana</option>
            <option value="Kesehatan">Kesehatan</option>
            <option value="Lainnya">Lainnya</option>
          </select>
        </div>
        <div class="filter-item">
          <label for="sortFilter">Urutkan</label>
          <select id="sortFilter" class="filter-control">
            <option value="newest">Terbaru</option>
            <option value="oldest">Terlama</option>
            <option value="title">Judul A-Z</option>
          </select>
        </div>
      </section>

      <!-- Materials Grid -->
      <section class="materials-grid" id="materialsGrid"></section>
    </main>
  </div>

  <!-- Modal Form -->
  <div class="modal" id="materialFormModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="formModalTitle">Tambah Materi Baru</h3>
        <button class="close-btn" id="closeFormBtn">&times;</button>
      </div>
      <div class="modal-body">
        <form id="materialForm">
          <div class="form-group">
            <label for="materialTitle">Judul Materi *</label>
            <input type="text" id="materialTitle" class="form-control" required placeholder="Contoh: Panduan P3K Dasar">
          </div>
          <div class="form-group">
            <label for="materialCategory">Kategori *</label>
            <select id="materialCategory" class="form-control" required>
              <option value="">Pilih Kategori</option>
              <option value="P3K">P3K</option>
              <option value="Kepalangmerahan">Kepalangmerahan</option>
              <option value="Pertolongan Bencana">Pertolongan Bencana</option>
              <option value="Kesehatan">Kesehatan</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>
          <div class="form-group">
            <label for="materialDescription">Deskripsi Materi *</label>
            <textarea id="materialDescription" class="form-control" required placeholder="Jelaskan singkat tentang materi ini..."></textarea>
          </div>
          <div class="form-group">
            <label>File PDF *</label>
            <div class="file-upload-wrapper" id="dropZone">
              <div class="file-upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
              <p class="file-upload-text">Klik untuk unggah file PDF</p>
              <input type="file" id="materialFile" accept=".pdf" style="display: none;">
              <div class="file-name-display" id="fileName">Belum ada file dipilih</div>
            </div>
          </div>
          <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 30px;">
            <button type="button" class="btn btn-secondary" id="cancelBtn">Batal</button>
            <button type="submit" class="btn btn-success" id="submitBtn">Simpan Materi</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Toast -->
  <div class="toast-container" id="toastContainer"></div>

  <script>
    /* ================= LOGIKA DROPDOWN & SIDEBAR ================= */
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

    function confirmLogout() {
      if (confirm("Yakin keluar?")) window.location.href = "../logout.php";
    }

    /* ================= DATA & LOGIC ================= */
    let materials = [];
    let currentMaterialId = null;
    let isEditMode = false;

    const materialsGrid = document.getElementById('materialsGrid');
    const materialFormModal = document.getElementById('materialFormModal');
    const materialForm = document.getElementById('materialForm');
    const materialFile = document.getElementById('materialFile');
    const fileNameDisplay = document.getElementById('fileName');
    const dropZone = document.getElementById('dropZone');
    const formModalTitle = document.getElementById('formModalTitle');
    const submitBtn = document.getElementById('submitBtn');
    const toastContainer = document.getElementById('toastContainer');

    document.addEventListener('DOMContentLoaded', () => {
      loadMaterials();
      setupEventListeners();
    });

    function loadMaterials() {
      fetch('get_materi.php')
        .then(res => res.json())
        .then(data => {
          materials = data.map(m => ({
            id: m.id,
            title: m.judul,
            description: m.deskripsi,
            category: m.kategori,
            date: new Date(m.created_at).toLocaleDateString('id-ID', {
              day: 'numeric',
              month: 'short',
              year: 'numeric'
            }),
            fileName: m.file_pdf
          }));
          renderMaterials();
        })
        .catch(err => console.error('Gagal load:', err));
    }

    function renderMaterials(list = materials) {
      materialsGrid.innerHTML = '';
      if (list.length === 0) {
        materialsGrid.innerHTML = `<div style="grid-column:1/-1;text-align:center;color:#999;padding:50px"><i class="fas fa-folder-open" style="font-size:3rem;color:#ddd"></i><h3>Tidak ada materi</h3></div>`;
        return;
      }

      list.forEach(m => {
        if (!m.id) return;
        materialsGrid.innerHTML += `
          <div class="material-card">
            <div class="card-top">
              <div class="file-icon"><i class="fas fa-file-pdf"></i></div>
              <div class="card-header-content">
                <div class="material-category">${m.category}</div>
                <h3 class="material-title">${m.title}</h3>
              </div>
            </div>
            <div class="card-body">
              <p class="material-description">${m.description}</p>
            </div>
            <div class="card-footer">
              <small class="card-meta"><i class="far fa-clock"></i> ${m.date}</small>
              <div class="card-actions">
                <button class="action-btn btn-edit" onclick="openEditModal(${m.id})" title="Edit"><i class="fas fa-pen"></i></button>
                <button class="action-btn btn-delete" onclick="deleteMaterial(${m.id})" title="Hapus"><i class="fas fa-trash"></i></button>
              </div>
            </div>
          </div>`;
      });
    }

    function setupEventListeners() {
      document.getElementById('addMaterialBtn').onclick = openAddModal;
      document.getElementById('closeFormBtn').onclick = closeModal;
      document.getElementById('cancelBtn').onclick = closeModal;
      dropZone.onclick = () => materialFile.click();

      materialFile.onchange = () => {
        if (materialFile.files[0]?.type !== 'application/pdf') {
          showToast('Hanya file PDF yang diizinkan!', 'error');
          materialFile.value = '';
          return;
        }
        fileNameDisplay.textContent = materialFile.files[0].name;
      };

      materialForm.onsubmit = e => {
        e.preventDefault();
        saveMaterial();
      };
      document.getElementById('categoryFilter').onchange = filterMaterials;
      document.getElementById('searchFilter').oninput = filterMaterials;
      document.getElementById('sortFilter').onchange = filterMaterials;
    }

    function openAddModal() {
      isEditMode = false;
      currentMaterialId = null;
      materialForm.reset();
      fileNameDisplay.textContent = 'Belum ada file dipilih';
      formModalTitle.textContent = 'Tambah Materi Baru';
      submitBtn.textContent = 'Simpan';
      materialFormModal.style.display = 'flex';
    }

    window.openEditModal = function(id) {
      const m = materials.find(x => x.id == id);
      if (!m) {
        showToast('Data tidak ditemukan', 'error');
        return;
      }

      isEditMode = true;
      currentMaterialId = id;
      formModalTitle.textContent = 'Edit Materi';
      submitBtn.textContent = 'Update';
      document.getElementById('materialTitle').value = m.title;
      document.getElementById('materialDescription').value = m.description;
      document.getElementById('materialCategory').value = m.category;
      fileNameDisplay.textContent = m.fileName || 'File lama (biarkan kosong jika tidak ganti)';
      materialFormModal.style.display = 'flex';
    };

    function closeModal() {
      materialFormModal.style.display = 'none';
    }

    function saveMaterial() {
      const fd = new FormData();
      fd.append('judul', document.getElementById('materialTitle').value);
      fd.append('deskripsi', document.getElementById('materialDescription').value);
      fd.append('kategori', document.getElementById('materialCategory').value);
      if (materialFile.files[0]) fd.append('file', materialFile.files[0]);

      let url = 'upload_materi.php';
      if (isEditMode) {
        fd.append('id', currentMaterialId);
        url = 'update_materi.php';
      }

      fetch(url, {
          method: 'POST',
          body: fd
        })
        .then(res => res.text())
        .then(res => {
          if (res.trim() === 'success') {
            showToast(isEditMode ? 'Materi berhasil diupdate!' : 'Materi berhasil disimpan!');
            closeModal();
            loadMaterials();
          } else {
            showToast('Terjadi kesalahan: ' + res, 'error');
          }
        });
    }

    window.deleteMaterial = function(id) {
      if (!confirm('Yakin ingin menghapus materi ini?')) return;
      fetch('delete_materi.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'id=' + id
        })
        .then(res => res.text())
        .then(res => {
          if (res.trim() === 'success') {
            showToast('Materi berhasil dihapus');
            loadMaterials();
          } else {
            showToast('Gagal menghapus', 'error');
          }
        });
    };

    function filterMaterials() {
      const cat = document.getElementById('categoryFilter').value;
      const q = document.getElementById('searchFilter').value.toLowerCase();
      const s = document.getElementById('sortFilter').value;
      let f = [...materials];
      if (cat) f = f.filter(x => x.category === cat);
      if (q) f = f.filter(x => x.title.toLowerCase().includes(q) || x.description.toLowerCase().includes(q));
      if (s === 'newest') f.sort((a, b) => b.id - a.id);
      if (s === 'oldest') f.sort((a, b) => a.id - b.id);
      if (s === 'title') f.sort((a, b) => a.title.localeCompare(b.title));
      renderMaterials(f);
    }

    function showToast(msg, type = 'success') {
      const t = document.createElement('div');
      t.className = `toast ${type}`;
      t.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-times-circle'}"></i> ${msg}`;
      toastContainer.appendChild(t);
      setTimeout(() => t.classList.add('show'), 10);
      setTimeout(() => {
        t.classList.remove('show');
        setTimeout(() => t.remove(), 300);
      }, 3000);
    }
  </script>
</body>

</html>