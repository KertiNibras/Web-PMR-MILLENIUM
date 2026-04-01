<?php
session_start();
include '../koneksi.php'; // Panggil koneksi DB

// Cek Login
if (!isset($_SESSION['nama'])) {
  echo '<script>alert("Silakan login terlebih dahulu!"); window.location.href = "../Login/login.php";</script>';
  exit;
}

// --- LOGIKA AMBIL DATA USER ---
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'anggota';
$nama_user = htmlspecialchars($_SESSION['nama']);

// Logika Foto Profil
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
  <title>Perpustakaan Digital | PMR Millenium</title>
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

    /* Tengah: Kosong */
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

    .page-header h1 {
      font-size: 1.75rem;
      color: var(--primary-color);
      margin-bottom: 5px;
    }

    .page-header p {
      color: var(--text-muted);
      font-size: 0.95rem;
      margin-bottom: 25px;
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
      font-size: 0.8rem;
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
      width: 48px;
      height: 48px;
      background: #ffebee;
      color: var(--primary-color);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
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
      margin-bottom: 6px;
      display: inline-block;
    }

    .material-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--text-color);
      margin: 0;
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
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .btn-download {
      background-color: var(--primary-color);
      color: white;
      padding: 8px 16px;
      border-radius: 8px;
      font-size: 0.85rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s;
    }

    .btn-download:hover {
      background-color: var(--primary-hover);
      transform: translateY(-1px);
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

      .filter-container {
        flex-direction: column;
        align-items: stretch;
      }

      .materials-grid {
        grid-template-columns: 1fr;
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
        <li><a href="anggota.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
        <li><a href="absensi.php"><i class="fa-solid fa-calendar-check"></i> Rekap Absensi</a></li>
        <li class="active"><a href="perpus.php"><i class="fa-solid fa-book"></i> Perpustakaan Digital</a></li>
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
        <h1>Perpustakaan Digital</h1>
        <p>Akses materi pelatihan dan panduan PMR secara gratis.</p>
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

    function confirmLogout() {
      if (confirm("Yakin keluar?")) window.location.href = "../logout.php";
    }

    // --- LOGIKA DATA MATERI ---
    let materials = [];
    const materialsGrid = document.getElementById('materialsGrid');

    document.addEventListener('DOMContentLoaded', function() {
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
            fileName: m.file_pdf,
            fileUrl: '../uploads/materi/' + m.file_pdf
          }));
          renderMaterials();
        })
        .catch(err => {
          console.error('Gagal memuat materi:', err);
          materialsGrid.innerHTML = '<p style="color:red; text-align:center;">Gagal memuat data materi.</p>';
        });
    }

    function renderMaterials(filteredMaterials = null) {
      const materialsToRender = filteredMaterials || materials;
      materialsGrid.innerHTML = '';

      if (materialsToRender.length === 0) {
        materialsGrid.innerHTML = `
          <div style="grid-column: 1 / -1; text-align: center; padding: 50px; color: #999;">
            <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 15px; color: #ddd;"></i>
            <h3>Tidak ada materi ditemukan</h3>
            <p>Belum ada materi yang diunggah atau sesuai pencarian.</p>
          </div>`;
        return;
      }

      materialsToRender.forEach(material => {
        const card = document.createElement('div');
        card.className = 'material-card';
        card.innerHTML = `
          <div class="card-top">
            <div class="file-icon"><i class="fas fa-file-pdf"></i></div>
            <div class="card-header-content">
              <div class="material-category">${material.category}</div>
              <h3 class="material-title">${material.title}</h3>
            </div>
          </div>
          <div class="card-body">
            <p class="material-description">${material.description}</p>
          </div>
          <div class="card-footer">
            <small class="card-meta"><i class="far fa-clock"></i> ${material.date}</small>
            <a href="${material.fileUrl}" target="_blank" class="btn-download">
              <i class="fas fa-download"></i> Download PDF
            </a>
          </div>`;
        materialsGrid.appendChild(card);
      });
    }

    function setupEventListeners() {
      document.getElementById('categoryFilter').addEventListener('change', filterMaterials);
      document.getElementById('searchFilter').addEventListener('input', filterMaterials);
      document.getElementById('sortFilter').addEventListener('change', filterMaterials);
    }

    function filterMaterials() {
      const category = document.getElementById('categoryFilter').value;
      const searchTerm = document.getElementById('searchFilter').value.toLowerCase();
      const sortBy = document.getElementById('sortFilter').value;

      let filtered = [...materials];

      if (category) filtered = filtered.filter(m => m.category === category);
      if (searchTerm) filtered = filtered.filter(m => m.title.toLowerCase().includes(searchTerm) || m.description.toLowerCase().includes(searchTerm));

      if (sortBy === 'newest') filtered.sort((a, b) => b.id - a.id);
      else if (sortBy === 'oldest') filtered.sort((a, b) => a.id - b.id);
      else if (sortBy === 'title') filtered.sort((a, b) => a.title.localeCompare(b.title));

      renderMaterials(filtered);
    }

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