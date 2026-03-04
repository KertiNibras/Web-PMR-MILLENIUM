<?php
session_start();
include '../koneksi.php';

// Cek Login & Role
if (!isset($_SESSION['nama'])) {
  header("Location: ../Login/login.php");
  exit;
}
if ($_SESSION['role'] != 'pengurus') {
  echo '<script>alert("AKSES DITOLAK!"); window.location.href="../Dashboard Anggota/anggota.php";</script>';
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

// Query Join
 $sql = "SELECT absensi.*, users.nama 
        FROM absensi 
        JOIN users ON absensi.user_id = users.id 
        ORDER BY absensi.tanggal DESC, absensi.jam DESC";
 $result = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Absensi | PMR Millenium</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="icon" href="../Gambar/logpmi.png" type="image/png">
  <style>
    /* CSS Variabel */
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

    /* Filter Section */
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
      background-color: #fff;
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

    .btn-secondary {
      background-color: var(--text-muted);
    }

    .btn-success {
      background-color: var(--success-color);
    }

    .btn-danger {
      background-color: var(--danger-color);
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
      min-width: 800px;
    }

    .data-table th {
      background-color: var(--primary-color);
      color: white;
      text-align: left;
      padding: 15px;
      font-weight: 600;
    }

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

    .photo-thumb {
      width: 50px;
      height: 50px;
      object-fit: cover;
      border-radius: 8px;
      cursor: pointer;
      border: 2px solid var(--border-color);
      transition: 0.2s;
    }

    .photo-thumb:hover {
      transform: scale(1.1);
      border-color: var(--primary-color);
    }

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

    .status-a {
      color: var(--danger-color);
      background: rgba(239, 68, 68, 0.1);
    }

    /* --- MODAL (BAGIAN YANG DIPERBAIKI) --- */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.7);
      z-index: 2000;
      align-items: center;
      justify-content: center;
      padding: 20px;
      overflow-y: auto; /* Agar modal bisa di-scroll jika layar kecil */
    }

    .modal-content {
      background: white;
      border-radius: var(--radius);
      max-width: 800px; /* Batas lebar */
      width: 100%;
      max-height: 90vh; /* Batas tinggi agar tidak memenuhi layar */
      display: flex;
      flex-direction: column;
      animation: fadeIn 0.3s ease;
      position: relative;
      margin: auto;
      overflow: hidden; /* Penting agar border-radius bekerja */
    }

    .modal-header {
      padding: 15px 20px;
      background: #f8f9fa;
      border-bottom: 1px solid var(--border-color);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-shrink: 0; /* Header tidak mengecil */
    }

    .modal-header h3 {
      font-size: 1.1rem;
      color: var(--text-color);
    }

    .close-modal {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: var(--text-muted);
    }

    .modal-body {
      padding: 20px;
      overflow-y: auto; /* Scroll internal jika gambar terlalu tinggi */
      display: flex;
      justify-content: center;
      align-items: center;
      background: #f1f5f9;
      flex-grow: 1;
      min-height: 0; /* Fix flexbox scroll issue */
    }

    /* CSS UNTUK GAMBAR MODAL */
    .modal-body img {
      max-width: 100%;
      max-height: 70vh; /* Batas tinggi gambar 70% dari tinggi layar */
      width: auto;
      height: auto;
      object-fit: contain; /* Menjaga rasio gambar */
      border-radius: 8px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
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

    .toast.success {
      border-left-color: var(--success-color);
    }

    .toast.info {
      border-left-color: var(--info-color);
    }

    .toast.success i {
      color: var(--success-color);
    }

    .toast.info i {
      color: var(--info-color);
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
      
      /* Penyesuaian modal di mobile */
      .modal-body img {
        max-height: 60vh;
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
            <li>
              <a href="ganti_foto.php"><i class="fa-solid fa-camera"></i> Ganti Foto Profil</a>
            </li>
            <li>
              <a href="ganti_nama.php"><i class="fa-solid fa-user-pen"></i> Ganti Nama</a>
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
        <li><a href="../Dashboard Anggota/anggota.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
        <li class="active"><a href="kelolaabsen.php"><i class="fa-solid fa-calendar-check"></i> Kelola Absensi</a></li>
        <li><a href="kelolaperpus.php"><i class="fa-solid fa-book"></i> Kelola Perpustakaan Digital</a></li>
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
      <div class="page-title">
        <h1>Kelola Absensi</h1>
        <p>Lihat dan kelola riwayat kehadiran anggota PMR.</p>
      </div>

      <!-- Filter Section -->
      <section class="content-header">
        <div class="filter-group">
          <label for="filterTanggal">Tanggal</label>
          <input type="date" id="filterTanggal" class="filter-control">
        </div>
        <div class="filter-group">
          <label for="filterStatus">Status</label>
          <select id="filterStatus" class="filter-control">
            <option value="">Semua Status</option>
            <option value="hadir">Hadir</option>
            <option value="izin">Izin</option>
            <option value="sakit">Sakit</option>
            <option value="alpha">Alpha</option>
          </select>
        </div>
        <div style="display: flex; gap: 10px;">
          <button class="btn btn-secondary" id="resetFilter"><i class="fas fa-redo"></i> Reset</button>
          <button class="btn btn-primary" id="applyFilter"><i class="fas fa-filter"></i> Filter</button>
        </div>
      </section>

      <!-- Table Section -->
      <section class="table-container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
          <h3 style="color: var(--text-color);">Data Kehadiran</h3>
          <div style="display: flex; gap: 10px;">
            <button class="btn btn-success" id="exportExcel" style="padding: 8px 15px; font-size: 0.85rem;">
              <i class="fas fa-file-excel"></i> Excel
            </button>
            <button class="btn btn-danger" id="exportPDF" style="padding: 8px 15px; font-size: 0.85rem;">
              <i class="fas fa-file-pdf"></i> PDF
            </button>
          </div>
        </div>

        <table class="data-table" id="absensiTable">
          <thead>
            <tr>
              <th width="50">No</th>
              <th>Nama Anggota</th>
              <th>Waktu Absensi</th>
              <th>Foto</th>
              <th>Status</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <!-- Data populated by JS -->
          </tbody>
        </table>
      </section>
    </main>
  </div>

  <!-- Modal Preview Foto -->
  <div class="modal" id="photoModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Bukti Kehadiran</h3>
        <button class="close-modal" id="closeModal">&times;</button>
      </div>
      <div class="modal-body">
        <!-- Gambar akan dimasukkan sini oleh JS -->
        <img id="modalImage" src="" alt="Foto">
      </div>
    </div>
  </div>

  <!-- Toast Container -->
  <div id="toastContainer"></div>

  <!-- Libraries -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

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

    // --- DATA LOGIC ---
    const absensiData = <?php
                        $arr = [];
                        if ($result && mysqli_num_rows($result) > 0) {
                          while ($row = mysqli_fetch_assoc($result)) {
                            $row['foto'] = '../uploads/absensi/' . $row['foto'];
                            $arr[] = $row;
                          }
                        }
                        echo json_encode($arr);
                        ?>;

    const tableBody = document.getElementById('tableBody');
    const modal = document.getElementById('photoModal');
    const modalImg = document.getElementById('modalImage');
    const toastContainer = document.getElementById('toastContainer');

    document.addEventListener('DOMContentLoaded', () => {
      // Set default filter hari ini
      document.getElementById('filterTanggal').value = new Date().toISOString().split('T')[0];
      renderTable(absensiData);

      document.getElementById('applyFilter').addEventListener('click', applyFilter);
      document.getElementById('resetFilter').addEventListener('click', resetFilter);
      document.getElementById('exportExcel').addEventListener('click', exportToExcel);
      document.getElementById('exportPDF').addEventListener('click', exportToPDF);

      document.getElementById('closeModal').addEventListener('click', () => modal.style.display = 'none');
      modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.style.display = 'none';
      });
    });

    function formatTanggalIndo(dateStr) {
      if (!dateStr) return '-';
      const date = new Date(dateStr);
      const options = {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
      };
      return date.toLocaleDateString('id-ID', options);
    }

    function renderTable(data) {
      tableBody.innerHTML = '';
      if (data.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding: 30px; color: #999;">Tidak ada data ditemukan.</td></tr>`;
        return;
      }

      data.forEach((item, index) => {
        const row = document.createElement('tr');
        const tanggalTampil = formatTanggalIndo(item.tanggal);
        const waktuStr = item.jam ? `${tanggalTampil}, ${item.jam}` : tanggalTampil;

        const getStatusClass = (s) => {
          if (s === 'hadir') return 'status-h';
          if (s === 'izin') return 'status-i';
          if (s === 'sakit') return 'status-s';
          return 'status-a';
        };

        row.innerHTML = `
          <td>${index + 1}</td>
          <td style="font-weight: 600;">${item.nama}</td>
          <td>${waktuStr}</td>
          <td>
            <img src="${item.foto}" class="photo-thumb" onclick="openModal('${item.foto}')" onerror="this.src='../Gambar/default.jpg'">
          </td>
          <td><span class="status-badge ${getStatusClass(item.status)}">${item.status}</span></td>
          <td>${item.keterangan || '-'}</td>
        `;
        tableBody.appendChild(row);
      });
    }

    function applyFilter() {
      const dateVal = document.getElementById('filterTanggal').value;
      const statVal = document.getElementById('filterStatus').value;

      const filtered = absensiData.filter(item => {
        const matchDate = dateVal ? item.tanggal === dateVal : true;
        const matchStat = statVal ? item.status === statVal : true;
        return matchDate && matchStat;
      });

      renderTable(filtered);
      showToast(`${filtered.length} data ditemukan`, 'info');
    }

    function resetFilter() {
      document.getElementById('filterTanggal').value = '';
      document.getElementById('filterStatus').value = '';
      renderTable(absensiData);
      showToast('Filter direset', 'info');
    }

    function exportToExcel() {
      if (absensiData.length === 0) {
        showToast('Tidak ada data', 'info');
        return;
      }
      const ws = XLSX.utils.json_to_sheet(absensiData.map(i => ({
        Nama: i.nama,
        Tanggal: formatTanggalIndo(i.tanggal),
        Jam: i.jam,
        Status: i.status,
        Keterangan: i.keterangan
      })));
      const wb = XLSX.utils.book_new();
      XLSX.utils.book_append_sheet(wb, ws, "Absensi");
      XLSX.writeFile(wb, "Laporan_Absensi.xlsx");
      showToast('Excel berhasil diunduh', 'success');
    }

    function exportToPDF() {
      if (absensiData.length === 0) {
        showToast('Tidak ada data', 'info');
        return;
      }
      const {
        jsPDF
      } = window.jspdf;
      const doc = new jsPDF();
      doc.text("Laporan Absensi PMR", 14, 20);
      doc.autoTable({
        head: [
          ['Nama', 'Waktu', 'Status', 'Ket']
        ],
        body: absensiData.map(i => [i.nama, `${formatTanggalIndo(i.tanggal)} ${i.jam}`, i.status, i.keterangan]),
        startY: 30,
      });
      doc.save("Laporan_Absensi.pdf");
      showToast('PDF berhasil diunduh', 'success');
    }

    window.openModal = (src) => {
      modalImg.src = src;
      modal.style.display = 'flex';
    };

    function showToast(msg, type) {
      const toast = document.createElement('div');
      toast.className = `toast ${type}`;
      const icon = type === 'success' ? 'fa-check-circle' : 'fa-info-circle';
      toast.innerHTML = `<i class="fas ${icon}"></i> <span>${msg}</span>`;
      toastContainer.appendChild(toast);
      requestAnimationFrame(() => toast.classList.add('show'));
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }
  </script>
</body>

</html>