<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Perpustakaan | PMR Millenium</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="icon" href="../Gambar/logpmi.png" type="image/png">
  <style>
    /* --- CSS VARIABLES & RESET --- */
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

    /* --- HEADER (TIDAK DIUBAH SESUAI REQUEST) --- */
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

    /* --- SIDEBAR --- */
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

    /* Page Header */
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      background: white;
      padding: 20px;
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
    }

    .header-titles h1 {
      font-size: 1.5rem;
      color: var(--primary-color);
      margin-bottom: 5px;
    }

    .header-titles p {
      color: var(--text-muted);
      font-size: 0.9rem;
    }

    /* --- BUTTON STYLES --- */
    .btn {
      padding: 10px 20px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s ease;
      font-size: 0.9rem;
    }

    .btn-primary {
      background-color: var(--primary-color);
      color: white;
    }

    .btn-primary:hover {
      background-color: var(--primary-hover);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(217, 4, 41, 0.3);
    }

    .btn-success {
      background-color: var(--success-color);
      color: white;
    }

    .btn-success:hover {
      background-color: #219653;
      transform: translateY(-2px);
    }

    .btn-warning {
      background-color: var(--warning-color);
      color: white;
    }

    .btn-warning:hover {
      background-color: #e67e22;
    }

    .btn-danger {
      background-color: var(--danger-color);
      color: white;
    }

    .btn-danger:hover {
      background-color: #c0392b;
    }

    .btn-secondary {
      background-color: #95a5a6;
      color: white;
    }
    
    .btn-secondary:hover {
      background-color: #7f8c8d;
    }

    .btn-sm {
      padding: 6px 12px;
      font-size: 0.8rem;
    }

    /* --- FILTER SECTION (REDESIGN) --- */
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
    }

    .filter-item {
      flex: 1;
      min-width: 200px;
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
      border-radius: 6px;
      font-size: 0.95rem;
      background-color: #fff;
      transition: border-color 0.3s;
    }

    .filter-control:focus {
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(217, 4, 41, 0.1);
    }

    /* --- MATERIALS GRID (REDESIGN) --- */
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
      display: inline-block;
    }

    .material-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--text-color);
      margin-bottom: 0;
      line-height: 1.4;
      display: -webkit-box;
      
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

    /* --- MODAL FORM --- */
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
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
      animation: modalPop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    @keyframes modalPop {
      from { opacity: 0; transform: scale(0.8); }
      to { opacity: 1; transform: scale(1); }
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
      transition: 0.2s;
    }

    .close-btn:hover {
      opacity: 1;
      transform: scale(1.1);
    }

    .modal-body {
      padding: 25px;
      max-height: 80vh;
      overflow-y: auto;
    }

    /* Form Elements */
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
      border-radius: 6px;
      font-size: 1rem;
      transition: border 0.3s;
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

    /* --- TOAST NOTIFICATION --- */
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
      box-shadow: 0 5px 15px rgba(0,0,0,0.15);
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 300px;
      border-left: 5px solid var(--primary-color);
      transform: translateX(120%);
      transition: transform 0.3s ease-out;
      font-size: 0.95rem;
    }

    .toast.show {
      transform: translateX(0);
    }

    .toast.success { border-left-color: var(--success-color); }
    .toast.error { border-left-color: var(--danger-color); }
    .toast i { font-size: 1.2rem; }
    .toast.success i { color: var(--success-color); }
    .toast.error i { color: var(--danger-color); }

    /* --- RESPONSIVE --- */
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

      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      .btn {
        width: 100%;
        justify-content: center;
      }
    }

    @media (max-width: 576px) {
      .materials-grid {
        grid-template-columns: 1fr;
      }
      
      .filter-container {
        flex-direction: column;
        align-items: stretch;
      }
      
      .header-titles {
        width: 100%;
      }
    }

    /* Tombol Back Mobile (Original logic preserved) */
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
  <!-- HEADER (TIDAK DIUBAH) -->
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

  <div class="dashboard-container">
    <!-- SIDEBAR (Dibiarkan, namun styling disesuaikan agar konsisten) -->
    <aside class="sidebar">
      <ul>
        <li><a href="../Dashboard Pengurus/pengurus.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
        <li><a href="../Dashboard Pengurus/kelolaabsen.html"><i class="fa-solid fa-calendar-check"></i> Kelola Absensi</a></li>
        <li class="active"><a href="../Dashboard Pengurus/kelolaperpus.html"><i class="fa-solid fa-book"></i> Kelola Perpustakaan Digital</a></li>
        <li><a href=""><i class="fa-solid fa-users"></i> Kelola Akun</a></li>
        <li><a href=""><i class="fa-solid fa-gamepad"></i> Kelola Quiz</a></li>
        <li><a href="../Dashboard Pengurus/kelolaabsen.html"><i class="fa-solid fa-pen-to-square"></i> Edit Beranda</a></li>
        <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a></li>
      </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <!-- Page Header -->
      <div class="page-header">
        <div class="header-titles">
          <h1>Kelola Materi Perpustakaan Digital</h1>
          <p>Kelola dokumen PDF, materi pelatihan, dan panduan untuk anggota.</p>
        </div>
        <button class="btn btn-primary" id="addMaterialBtn">
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
      <section class="materials-grid" id="materialsGrid">
        <!-- Materi akan di-render lewat JavaScript -->
      </section>
    </main>
  </div>

  <!-- Modal Form (Tambah/Edit) -->
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
              <div class="file-upload-icon">
                <i class="fas fa-cloud-upload-alt"></i>
              </div>
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

  <!-- Toast Notification Container -->
  <div class="toast-container" id="toastContainer"></div>

  <script>
/* ================= SIDEBAR ================= */
const menuToggle = document.querySelector('.menu-toggle');
const sidebar = document.querySelector('.sidebar');

menuToggle.addEventListener('click', () => {
  sidebar.classList.toggle('active');
});

document.addEventListener('click', (e) => {
  if (window.innerWidth <= 992) {
    if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
      sidebar.classList.remove('active');
    }
  }
});

function goBack() {
  window.history.back();
}

/* ================= DATA ================= */
let materials = [];
let currentMaterialId = null;
let isEditMode = false;

/* ================= DOM ================= */
const materialsGrid = document.getElementById('materialsGrid');
const materialFormModal = document.getElementById('materialFormModal');
const materialForm = document.getElementById('materialForm');
const materialFile = document.getElementById('materialFile');
const fileNameDisplay = document.getElementById('fileName');
const dropZone = document.getElementById('dropZone');
const formModalTitle = document.getElementById('formModalTitle');
const submitBtn = document.getElementById('submitBtn');
const toastContainer = document.getElementById('toastContainer');

/* ================= INIT ================= */
document.addEventListener('DOMContentLoaded', () => {
  loadMaterials();
  setupEventListeners();
});

/* ================= LOAD ================= */
function loadMaterials() {
  fetch('../Dashboard Pengurus/get_materi.php')
    .then(res => res.json())
    .then(data => {
      materials = data.map(m => ({
        id: m.id,
        title: m.judul,
        description: m.deskripsi,
        category: m.kategori,
        date: new Date(m.created_at).toLocaleDateString('id-ID'),
        fileName: m.file_pdf
      }));
      renderMaterials();
    });
}

/* ================= RENDER ================= */
function renderMaterials(list = materials) {
  materialsGrid.innerHTML = '';

  if (list.length === 0) {
    materialsGrid.innerHTML = `
      <div style="grid-column:1/-1;text-align:center;color:#999;padding:50px">
        <i class="fas fa-folder-open" style="font-size:3rem;color:#ddd"></i>
        <h3>Tidak ada materi</h3>
      </div>`;
    return;
  }

  list.forEach(m => {
    materialsGrid.innerHTML += `
      <div class="material-card">
        <div class="card-top">
          <div class="file-icon"><i class="fas fa-file-pdf"></i></div>
          <div>
            <div class="material-category">${m.category}</div>
            <h3>${m.title}</h3>
          </div>
        </div>
        <div class="card-body">
          <p>${m.description}</p>
        </div>
        <div class="card-footer">
          <small><i class="far fa-clock"></i> ${m.date}</small>
          <div>
            <button onclick="openEditModal(${m.id})"><i class="fas fa-pen"></i></button>
            <button onclick="deleteMaterial(${m.id})"><i class="fas fa-trash"></i></button>
          </div>
        </div>
      </div>`;
  });
}

/* ================= EVENT ================= */
function setupEventListeners() {
  document.getElementById('addMaterialBtn').onclick = openAddModal;
  document.getElementById('closeFormBtn').onclick = closeModal;
  document.getElementById('cancelBtn').onclick = closeModal;

  dropZone.onclick = () => materialFile.click();

  materialFile.onchange = () => {
    if (materialFile.files[0]?.type !== 'application/pdf') {
      showToast('Hanya PDF!', 'error');
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

/* ================= MODAL ================= */
function openAddModal() {
  isEditMode = false;
  currentMaterialId = null;
  materialForm.reset();
  fileNameDisplay.textContent = 'Belum ada file';
  formModalTitle.textContent = 'Tambah Materi';
  submitBtn.textContent = 'Simpan';
  materialFormModal.style.display = 'flex';
}

window.openEditModal = function(id) {
  const m = materials.find(x => x.id === id);
  if (!m) return;

  isEditMode = true;
  currentMaterialId = id;
  formModalTitle.textContent = 'Edit Materi';
  submitBtn.textContent = 'Update';

  materialTitle.value = m.title;
  materialDescription.value = m.description;
  materialCategory.value = m.category;
  fileNameDisplay.textContent = m.fileName || 'File tersimpan';

  materialFormModal.style.display = 'flex';
};

function closeModal() {
  materialFormModal.style.display = 'none';
}

/* ================= SAVE ================= */
function saveMaterial() {
  const fd = new FormData();
  fd.append('judul', materialTitle.value);
  fd.append('deskripsi', materialDescription.value);
  fd.append('kategori', materialCategory.value);

  if (materialFile.files[0]) {
    fd.append('file', materialFile.files[0]);
  }

  let url = '../Dashboard Pengurus/upload_materi.php';

  if (isEditMode) {
    fd.append('id', currentMaterialId);
    url = '../Dashboard Pengurus/update_materi.php';
  }

  fetch(url, { method: 'POST', body: fd })
    .then(res => res.text())
    .then(res => {
      if (res === 'success') {
        showToast('Berhasil disimpan');
        closeModal();
        loadMaterials();
      } else {
        showToast('Gagal menyimpan', 'error');
      }
    });
}

/* ================= DELETE ================= */
window.deleteMaterial = function(id) {
  if (!confirm('Hapus materi ini?')) return;

  fetch('../Dashboard Pengurus/delete_materi.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'id=' + id
  })
  .then(res => res.text())
  .then(res => {
    if (res === 'success') {
      showToast('Materi dihapus');
      loadMaterials();
    }
  });
};

/* ================= FILTER ================= */
function filterMaterials() {
  let f = [...materials];
  const cat = categoryFilter.value;
  const q = searchFilter.value.toLowerCase();
  const s = sortFilter.value;

  if (cat) f = f.filter(x => x.category === cat);
  if (q) f = f.filter(x => x.title.toLowerCase().includes(q));

  if (s === 'newest') f.sort((a,b)=>b.id-a.id);
  if (s === 'oldest') f.sort((a,b)=>a.id-b.id);
  if (s === 'title') f.sort((a,b)=>a.title.localeCompare(b.title));

  renderMaterials(f);
}

/* ================= TOAST ================= */
function showToast(msg, type='success') {
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.innerHTML = `<i class="fas fa-check-circle"></i> ${msg}`;
  toastContainer.appendChild(t);
  setTimeout(()=>t.remove(),3000);
}
</script>

</body>
</html>