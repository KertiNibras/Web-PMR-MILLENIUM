<?php
session_start();
// Cek apakah user sudah login
if (!isset($_SESSION['nama'])) {
  echo '<script type="text/javascript">';
  echo 'alert("Silakan login terlebih dahulu!");';
  echo 'window.location.href = "../Login/login.php";';
  echo '</script>';
  exit;
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
      --success-color: #10b981;
      --warning-color: #f59e0b;
      --danger-color: #ef4444;
      --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
      --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.05);
      --radius: 12px;
      --header-height: 70px;
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
      margin-right: 10px;
    }

    /* --- LAYOUT --- */
    .dashboard-container {
      display: flex;
      min-height: 100vh;
      padding-top: var(--header-height);
    }

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

    .main-content {
      flex: 1;
      padding: 30px;
    }

    /* --- PAGE HEADER --- */
    .page-header {
      margin-bottom: 30px;
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

    /* --- FILTER --- */
    .filter-container {
      background: white;
      padding: 20px;
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      margin-bottom: 30px;
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      align-items: flex-end;
      border: 1px solid var(--border-color);
    }

    .filter-item {
      flex: 1;
      min-width: 200px;
    }

    .filter-item label {
      display: block;
      margin-bottom: 8px;
      font-size: 0.8rem;
      font-weight: 600;
      color: var(--text-muted);
      margin-left: 4px;
    }

    .filter-control {
      width: 100%;
      padding: 10px 15px;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      font-size: 0.95rem;
      outline: none;
      background-color: #fff;
      transition: border-color 0.3s;
    }

    .filter-control:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(217, 4, 41, 0.1);
    }

    /* --- GRID & CARD (DESAIN DISAMAKAN) --- */
    .materials-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
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

    /* Card Top */
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

    /* Card Body */
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

    /* Card Footer (TAMBAH TANGGAL) */
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
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s;
    }

    .btn-download:hover {
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
      <button class="back-btn" onclick="goBack()"><i class="fa-solid fa-arrow-left"></i></button>
      <div class="logo">
        <img src="../Gambar/logpmi.png" alt="Logo">
        <span>PMR MILLENIUM</span>
      </div>
      <button class="menu-toggle"><i class="fa-solid fa-bars"></i></button>
    </nav>
  </header>

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
    // --- Sidebar Logic ---
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');

    menuToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      sidebar.classList.toggle('active');
    });
    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 992 && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) sidebar.classList.remove('active');
    });

    function goBack() {
      window.history.back();
    }

    function confirmLogout() {
      if (confirm("Yakin keluar?")) window.location.href = "../logout.php";
    }

    // --- DATA & LOGIC ---
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
            // Format tanggal: "25 Okt 2023"
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
  </script>
</body>

</html>