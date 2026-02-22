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
    /* CSS Sama persis dengan kode sebelumnya */
    :root {
      --primary-color: #d90429;
      --primary-hover: #ef233c;
      --bg-color: #f8f9fa;
      --text-color: #333333;
      --text-muted: #6c757d;
      --border-color: #e9ecef;
      --success-color: #27ae60;
      --warning-color: #f39c12;
      --danger-color: #e74c3c;
      --info-color: #17a2b8;
      --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
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

    header {
      background: #fff;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      position: fixed;
      width: 100%;
      z-index: 1000;
    }

    .navbar {
      max-width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px 20px;
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

    .menu-toggle {
      display: none;
      background: none;
      border: none;
      font-size: 22px;
      cursor: pointer;
      color: var(--primary-color);
    }

    .dashboard-container {
      display: flex;
      min-height: 100vh;
      padding-top: 70px;
    }

    .sidebar {
      width: 250px;
      background: #ffffff;
      border-right: 1px solid var(--border-color);
      height: calc(100vh - 70px);
      position: sticky;
      top: 70px;
      overflow-y: auto;
    }

    .sidebar ul {
      list-style: none;
    }

    .sidebar li {
      padding: 14px 25px;
      cursor: pointer;
      color: var(--text-color);
      display: flex;
      align-items: center;
      gap: 12px;
      transition: 0.3s;
      font-weight: 500;
      border-left: 4px solid transparent;
    }

    .sidebar li:hover,
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

    .main-content {
      flex: 1;
      padding: 30px;
      width: calc(100% - 250px);
    }

    .page-title h1 {
      font-size: 1.5rem;
      color: var(--primary-color);
      margin-bottom: 5px;
    }

    .page-title p {
      color: var(--text-muted);
      font-size: 0.9rem;
      margin-bottom: 25px;
    }

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
    }

    .filter-group {
      flex: 1;
      min-width: 200px;
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
      border-radius: 6px;
      font-size: 0.95rem;
    }

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
      color: white;
    }

    .btn-primary {
      background-color: var(--primary-color);
    }

    .btn-secondary {
      background-color: #95a5a6;
    }

    .btn-success {
      background-color: var(--success-color);
    }

    .btn-danger {
      background-color: var(--danger-color);
    }

    .table-container {
      background: white;
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      padding: 20px;
      overflow-x: auto;
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

    .data-table td {
      padding: 15px;
      border-bottom: 1px solid var(--border-color);
      vertical-align: middle;
    }

    .data-table tr:hover {
      background-color: #fafafa;
    }

    .photo-thumb {
      width: 50px;
      height: 50px;
      object-fit: cover;
      border-radius: 8px;
      cursor: pointer;
      border: 2px solid var(--border-color);
      transition: 0.3s;
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
      text-transform: uppercase;
    }

    .status-h {
      color: var(--success-color);
      background: rgba(39, 174, 96, 0.15);
    }

    .status-i {
      color: var(--warning-color);
      background: rgba(243, 156, 18, 0.15);
    }

    .status-s {
      color: var(--info-color);
      background: rgba(23, 162, 184, 0.15);
    }

    .status-a {
      color: var(--danger-color);
      background: rgba(231, 76, 60, 0.15);
    }

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
    }

    .modal-content {
      background: white;
      border-radius: var(--radius);
      max-width: 600px;
      width: 100%;
      overflow: hidden;
    }

    .modal-header {
      padding: 15px 20px;
      background: var(--bg-color);
      border-bottom: 1px solid var(--border-color);
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-body {
      padding: 20px;
      text-align: center;
    }

    .modal-body img {
      max-width: 100%;
      border-radius: 8px;
    }

    .close-modal {
      background: none;
      border: none;
      font-size: 1.5rem;
      cursor: pointer;
      color: var(--text-muted);
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
      min-width: 300px;
      border-left: 5px solid var(--primary-color);
      transform: translateX(120%);
      transition: transform 0.3s ease-out;
      z-index: 9999;
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

    .toast i {
      font-size: 1.2rem;
    }

    .toast.success i {
      color: var(--success-color);
    }

    .toast.info i {
      color: var(--info-color);
    }

    @media (max-width: 992px) {
      .main-content {
        width: 100%;
        padding: 20px;
      }

      .sidebar {
        width: 250px;
        position: fixed;
        top: 70px;
        left: -260px;
        height: calc(100vh - 70px);
        z-index: 999;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
      }

      .sidebar.active {
        left: 0;
      }

      .menu-toggle {
        display: block;
      }

      .content-header {
        flex-direction: column;
        align-items: stretch;
      }

      .back-btn {
        display: block;
        position: absolute;
        left: 15px;
        top: 20px;
        z-index: 1001;
        background: none;
        border: none;
        font-size: 20px;
        color: var(--primary-color);
        cursor: pointer;
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

    .back-btn {
      display: none;
    }
  </style>
</head>

<body>

  <!-- HEADER & SIDEBAR (Sama persis) -->
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
    <aside class="sidebar">
      <ul>
        <li><a href="../Dashboard Anggota/anggota.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
        <li class="active"><a href="kelolaabsen.php"><i class="fa-solid fa-calendar-check"></i> Kelola Absensi</a></li>
        <li><a href="kelolaperpus.php"><i class="fa-solid fa-book"></i> Kelola Perpustakaan Digital</a></li>
        <li><a href=""><i class="fa-solid fa-users"></i> Kelola Pendaftaran</a></li>
        <li style="margin-top: 20px; border-top: 1px solid #eee;">
          <a href="javascript:void(0)" onclick="confirmLogout()">
            <i class="fa-solid fa-right-from-bracket"></i> Log Out
          </a>
        </li>
      </ul>
    </aside>

    <main class="main-content">
      <div class="page-title">
        <h1>Kelola Absensi</h1>
        <p>Lihat dan kelola riwayat kehadiran anggota PMR.</p>
      </div>

      <!-- Filter Section (Hapus Kegiatan) -->
      <section class="content-header">
        <div class="filter-group">
          <label for="filterTanggal">Tanggal</label>
          <input type="date" id="filterTanggal" class="filter-control">
        </div>
        <!-- Filter Kegiatan Dihapus -->
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
              <!-- Kolom Kegiatan Dihapus -->
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
        <h3 style="font-size: 1.1rem; color: var(--text-color);">Bukti Kehadiran</h3>
        <button class="close-modal" id="closeModal">&times;</button>
      </div>
      <div class="modal-body">
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
    // Ambil data PHP
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

    function renderTable(data) {
      tableBody.innerHTML = '';

      if (data.length === 0) {
        // Colspan diubah jadi 6 karena kolom berkurang
        tableBody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding: 30px; color: #999;">Tidak ada data ditemukan.</td></tr>`;
        return;
      }

      data.forEach((item, index) => {
        const row = document.createElement('tr');
        const dateObj = new Date(item.tanggal);
        const dateStr = dateObj.toLocaleDateString('id-ID', {
          day: 'numeric',
          month: 'short',
          year: 'numeric'
        });
        const waktuStr = item.jam ? `${dateStr}, ${item.jam}` : dateStr;

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
          <!-- Kolom Kegiatan Dihapus -->
          <td>
            <img src="${item.foto}" class="photo-thumb" onclick="openModal('${item.foto}')">
          </td>
          <td>
            <span class="status-badge ${getStatusClass(item.status)}">${item.status}</span>
          </td>
          <td>${item.keterangan}</td>
        `;
        tableBody.appendChild(row);
      });
    }

    function applyFilter() {
      const dateVal = document.getElementById('filterTanggal').value;
      const statVal = document.getElementById('filterStatus').value; // Hanya filter tanggal & status

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

    // Export Functions (Disesuaikan tanpa kegiatan)
    function exportToExcel() {
      if (absensiData.length === 0) {
        showToast('Tidak ada data', 'info');
        return;
      }
      const ws = XLSX.utils.json_to_sheet(absensiData.map(i => ({
        Nama: i.nama,
        Waktu: `${i.tanggal} ${i.jam}`,
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
        ], // Header disesuaikan
        body: absensiData.map(i => [i.nama, `${i.tanggal} ${i.jam}`, i.status, i.keterangan]),
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

    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    menuToggle.addEventListener('click', () => sidebar.classList.toggle('active'));
    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 992) {
        if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) sidebar.classList.remove('active');
      }
    });

    function goBack() {
      window.history.back();
    }

    function confirmLogout() {
      if (confirm("Apakah Anda yakin ingin keluar?")) {
        window.location.href = "../logout.php";
      }
    }
  </script>
</body>

</html>