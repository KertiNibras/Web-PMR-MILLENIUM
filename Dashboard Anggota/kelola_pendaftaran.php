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

// ================== BLOK PROSES AJAX (SUDAH DIPERBAIKI) ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  // 1. Set Header JSON di awal
  header('Content-Type: application/json');
  
  // 2. Ambil Action-nya dulu
  $action = $_POST['action'];

    if ($action == 'delete_pendaftar') {
    $id = intval($_POST['id']);

    // 1. Ambil data answers terlebih dahulu untuk mendapatkan nama file
    $get_data = mysqli_query($koneksi, "SELECT answers FROM pendaftaran WHERE id='$id'");
    $data_row = mysqli_fetch_assoc($get_data);
    
    if ($data_row) {
        $answers_json = $data_row['answers'];
        // Decode JSON menjadi array
        $answers_arr = json_decode($answers_json, true);

        // Cek apakah ada data file di dalam answers
        if (is_array($answers_arr)) {
            foreach ($answers_arr as $key => $value) {
                // Jika value mengandung path "question_file/", maka itu adalah file
                if (strpos($value, 'question_file/') !== false) {
                    $file_path = "../uploads/" . $value; // Bentuk path lengkap: ../uploads/question_file/namafile.jpg
                    
                    // Hapus file jika ada
                    if (file_exists($file_path)) {
                        unlink($file_path); // Fungsi hapus file di PHP
                    }
                }
            }
        }
    }

    // 2. Hapus data dari database
    $del = mysqli_query($koneksi, "DELETE FROM pendaftaran WHERE id='$id'");
    if ($del) {
      echo json_encode(['status' => 'success']);
    } else {
      echo json_encode(['status' => 'error', 'msg' => mysqli_error($koneksi)]);
    }
    exit;
  }
  // 4. Logika Lainnya
  if ($action == 'save_question') {
    $id = intval($_POST['id'] ?? 0);
    $text = mysqli_real_escape_string($koneksi, $_POST['text']);
    $type = mysqli_real_escape_string($koneksi, $_POST['type']);
    $opts = mysqli_real_escape_string($koneksi, $_POST['options'] ?? '[]');
    $req = intval($_POST['required'] ?? 1);

    $current_count = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM form_questions"))['total'];
    $order = $current_count + 1;

    if ($id > 0) {
      $q = "UPDATE form_questions SET question_text='$text', question_type='$type', options='$opts', is_required='$req' WHERE id='$id'";
    } else {
      $q = "INSERT INTO form_questions (question_text, question_type, options, is_required, ordering) VALUES ('$text', '$type', '$opts', '$req', '$order')";
    }

    if (mysqli_query($koneksi, $q)) {
      echo json_encode(['status' => 'success']);
    } else {
      echo json_encode(['status' => 'error', 'msg' => mysqli_error($koneksi)]);
    }
    exit;
  }

  if ($action == 'delete_question') {
    $id = intval($_POST['id']);
    if (mysqli_query($koneksi, "DELETE FROM form_questions WHERE id='$id'")) {
      echo json_encode(['status' => 'success']);
    } else {
      echo json_encode(['status' => 'error']);
    }
    exit;
  }

  if ($action == 'update_order') {
    $ids = json_decode($_POST['ids']);
    $success = true;
    foreach ($ids as $index => $id) {
      $order_val = $index + 1;
      $id = intval($id);
      $u = "UPDATE form_questions SET ordering='$order_val' WHERE id='$id'";
      if (!mysqli_query($koneksi, $u)) {
        $success = false;
      }
    }
    echo json_encode(['status' => $success ? 'success' : 'error']);
    exit;
  }
}
// =========================================================================

// Ambil Data Pertanyaan
 $questions = mysqli_query($koneksi, "SELECT * FROM form_questions ORDER BY ordering ASC");

// Ambil Data Pendaftar
 $pendaftar = mysqli_query($koneksi, "SELECT * FROM pendaftaran ORDER BY submission_date DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Pendaftaran | PMR Millenium</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="icon" href="../Gambar/logpmi.png" type="image/png">
  <!-- Library SortableJS untuk Drag & Drop -->
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
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

    /* Tabs */
    .tabs {
      display: flex;
      gap: 5px;
      margin-bottom: 20px;
      border-bottom: 2px solid var(--border-color);
    }

    .tab-btn {
      padding: 10px 20px;
      border: none;
      background: none;
      cursor: pointer;
      font-weight: 600;
      color: var(--text-muted);
      border-bottom: 3px solid transparent;
      margin-bottom: -2px;
    }

    .tab-btn.active {
      color: var(--primary-color);
      border-bottom-color: var(--primary-color);
    }

    /* Cards/Container */
    .content-card {
      background: white;
      padding: 25px;
      border-radius: var(--radius);
      box-shadow: var(--shadow-sm);
      border: 1px solid var(--border-color);
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

    .btn-danger {
      background-color: var(--danger-color);
    }

    .btn-secondary {
      background-color: var(--text-muted);
    }

    /* Table */
    .table-container {
      overflow-x: auto;
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
      min-width: 700px;
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

    /* Form Builder Item */
    .question-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px;
      background: #fff;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      margin-bottom: 10px;
      transition: all 0.2s ease;
      cursor: move;
    }

    .question-item:hover {
      border-color: var(--primary-color);
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .question-item.sortable-ghost {
      opacity: 0.4;
      background: #f0f0f0;
    }

    .question-item.sortable-chosen {
      border: 2px dashed var(--primary-color);
      background: #fff;
    }

    .q-info {
      display: flex;
      align-items: center;
      gap: 15px;
      flex: 1;
    }

    .q-info .drag-handle {
      color: var(--text-muted);
      font-size: 1.2rem;
      cursor: grab;
    }

    .q-info .drag-handle:active {
      cursor: grabbing;
    }

    .q-text-con h4 {
      margin-bottom: 5px;
      font-size: 1rem;
    }

    .q-text-con small {
      color: var(--text-muted);
      font-size: 0.8rem;
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 2000;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .modal-content {
      background: white;
      padding: 25px;
      border-radius: var(--radius);
      width: 100%;
      max-width: 500px;
      animation: fadeIn 0.3s ease;
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

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      font-size: 0.9rem;
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
        <li><a href="kelolaperpus.php"><i class="fa-solid fa-book"></i> Kelola Perpustakaan Digital</a></li>
        <li class="active"><a href="kelola_pendaftaran.php"><i class="fa-solid fa-users"></i> Kelola Pendaftaran</a></li>
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
        <h1>Kelola Pendaftaran Anggota</h1>
        <p>Atur formulir pendaftaran dan lihat data pendaftar baru.</p>
      </div>

      <!-- Tabs -->
      <div class="tabs">
        <button class="tab-btn active" id="btn-builder" onclick="switchTab('builder')">Struktur Formulir</button>
        <button class="tab-btn" id="btn-list" onclick="switchTab('list')">Data Pendaftar</button>
      </div>

      <!-- Tab 1: Form Builder -->
      <section id="tab-builder">
        <div class="content-card">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: var(--text-color);">Daftar Pertanyaan</h3>
            <button class="btn btn-primary" onclick="openModal()">
              <i class="fas fa-plus"></i> Tambah Pertanyaan
            </button>
          </div>
          <div id="questionsList">
            <?php
            if (mysqli_num_rows($questions) > 0) {
              while ($q = mysqli_fetch_assoc($questions)) {
                $req = $q['is_required'] ? '<span style="color:var(--danger-color)">*</span>' : '';
                $type_label = ucfirst($q['question_type']);
                echo "<div class='question-item' data-id='{$q['id']}'>
                        <div class='q-info'>
                            <i class='fa-solid fa-grip-vertical drag-handle'></i>
                            <div class='q-text-con'>
                                <h4>{$q['question_text']} {$req}</h4>
                                <small>Tipe: {$type_label}</small>
                            </div>
                        </div>
                        <div style='display:flex; gap:5px;'>
                            <button class='btn btn-success' style='padding:5px 10px' onclick='editQ({$q['id']}, \"{$q['question_text']}\", \"{$q['question_type']}\", " . json_encode($q['options']) . ", {$q['is_required']})'><i class='fas fa-pen'></i></button>
                            <button class='btn btn-danger' style='padding:5px 10px' onclick='deleteQ({$q['id']})'><i class='fas fa-trash'></i></button>
                        </div>
                      </div>";
              }
            } else {
              echo "<p style='color:var(--text-muted); text-align:center; padding: 20px 0; background:white; border-radius:8px;'>Belum ada pertanyaan. Silakan tambah pertanyaan baru.</p>";
            }
            ?>
          </div>
        </div>
      </section>

      <!-- Tab 2: Data Pendaftar -->
      <section id="tab-list" style="display: none;">
        <div class="content-card">
          <h3 style="margin-bottom: 15px; color: var(--text-color);">Daftar Pendaftar Baru</h3>
          <div class="table-container">
            <table class="data-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Tanggal</th>
                  <th>Nama</th>
                  <th>Kelas</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = 1;
                // Reset pointer query agar bisa digunakan lagi
                if ($pendaftar) mysqli_data_seek($pendaftar, 0);

                if (mysqli_num_rows($pendaftar) > 0) {
                  while ($p = mysqli_fetch_assoc($pendaftar)) {
                    echo "<tr>
                                <td>{$no}</td>
                                <td>" . date('d M Y', strtotime($p['submission_date'])) . "</td>
                                <td>{$p['nama_lengkap']}</td>
                                <td>{$p['kelas']}</td>
                                <td>
                                    <!-- Tombol Lihat Detail -->
                                    <button class='btn btn-primary' style='padding:5px 10px' onclick='viewDetail({$p['id']})'><i class='fas fa-eye'></i></button>
                                    <!-- Tombol Hapus Baru -->
                                    <button class='btn btn-danger' style='padding:5px 10px' onclick='deletePendaftar({$p['id']})'><i class='fas fa-trash'></i></button>
                                </td>
                              </tr>";
                    $no++;
                  }
                } else {
                  echo "<tr><td colspan='5' style='text-align:center; color:var(--text-muted); padding: 20px;'>Belum ada pendaftar.</td></tr>";
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Modal Form Pertanyaan -->
  <div class="modal" id="questionModal">
    <div class="modal-content">
      <h3 style="margin-bottom: 20px; color: var(--primary-color);">Form Pertanyaan</h3>
      <form id="formQuestion">
        <input type="hidden" id="q_id" value="0">
        <div class="form-group">
          <label>Pertanyaan</label>
          <input type="text" id="q_text" class="form-control" required placeholder="Contoh: Alasan bergabung?">
        </div>
        <div class="form-group">
          <label>Tipe Jawaban</label>
          <select id="q_type" class="form-control" onchange="toggleOptions()">
            <option value="text">Teks Singkat</option>
            <option value="textarea">Paragraf</option>
            <option value="select">Pilihan (Dropdown)</option>
            <option value="radio">Pilihan (Radio)</option>
            <option value="file">Upload File</option>
          </select>
        </div>
        <div class="form-group" id="opts-group" style="display:none;">
          <label>Pilihan (Pisahkan dengan enter)</label>
          <textarea id="q_opts" class="form-control" rows="3" placeholder="Opsi 1&#10;Opsi 2&#10;Opsi 3"></textarea>
        </div>
        <div class="form-group">
          <label> <input type="checkbox" id="q_req" checked> Wajib Diisi</label>
        </div>
        <div style="text-align: right; margin-top: 20px;">
          <button type="button" class="btn btn-secondary" onclick="closeModal()">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Detail Pendaftar -->
  <div class="modal" id="detailModal">
    <div class="modal-content" style="max-width: 600px;">
      <h3 style="margin-bottom: 20px; color: var(--primary-color);">Detail Pendaftar</h3>
      <div id="detailContent">Loading...</div>
      <div style="text-align: right; margin-top: 20px;">
        <button class="btn btn-danger" onclick="document.getElementById('detailModal').style.display='none'">Tutup</button>
      </div>
    </div>
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

    // ============================
    // 1. TAB HANDLING
    // ============================
    function switchTab(tabName) {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.getElementById('btn-' + tabName).classList.add('active');

      document.getElementById('tab-builder').style.display = tabName === 'builder' ? 'block' : 'none';
      document.getElementById('tab-list').style.display = tabName === 'list' ? 'block' : 'none';
      history.pushState(null, null, '#' + tabName);
    }

    window.addEventListener('load', () => {
      let hash = window.location.hash.substring(1);
      if (hash === 'list') switchTab('list');
      else switchTab('builder');
      initSortable();
    });

    window.addEventListener('hashchange', () => {
      let hash = window.location.hash.substring(1);
      if (hash === 'list') switchTab('list');
      else switchTab('builder');
    });

    // ============================
    // 2. DRAG & DROP SORTING
    // ============================
    function initSortable() {
      const el = document.getElementById('questionsList');
      if (el && typeof Sortable !== 'undefined') {
        new Sortable(el, {
          animation: 150,
          handle: '.drag-handle',
          ghostClass: 'sortable-ghost',
          chosenClass: 'sortable-chosen',
          onEnd: function(evt) {
            const items = el.querySelectorAll('.question-item');
            const ids = [];
            items.forEach(item => ids.push(item.dataset.id));
            saveOrder(ids);
          }
        });
      }
    }

    function saveOrder(ids) {
      const data = new FormData();
      data.append('action', 'update_order');
      data.append('ids', JSON.stringify(ids));
      fetch('', {
          method: 'POST',
          body: data
        })
        .then(res => res.json())
        .then(res => {
          if (res.status !== 'success') alert('Gagal menyimpan urutan');
        });
    }

    // ============================
    // 3. MODAL & CRUD HANDLING
    // ============================
    const modal = document.getElementById('questionModal');

    function openModal() {
      document.getElementById('formQuestion').reset();
      document.getElementById('q_id').value = 0;
      toggleOptions();
      modal.style.display = 'flex';
    }

    function closeModal() {
      modal.style.display = 'none';
    }

    function toggleOptions() {
      const type = document.getElementById('q_type').value;
      const optsGroup = document.getElementById('opts-group');

      if (type === 'select' || type === 'radio') {
        optsGroup.style.display = 'block';
      } else {
        optsGroup.style.display = 'none';
      }
    }

    function editQ(id, text, type, opts, req) {
      document.getElementById('q_id').value = id;
      document.getElementById('q_text').value = text;
      document.getElementById('q_type').value = type;
      document.getElementById('q_req').checked = req == 1;

      try {
        if (typeof opts === 'string') {
          try {
            const arr = JSON.parse(opts);
            document.getElementById('q_opts').value = arr.join('\n');
          } catch (e) {
            document.getElementById('q_opts').value = '';
          }
        } else if (Array.isArray(opts)) {
          document.getElementById('q_opts').value = opts.join('\n');
        }
      } catch (e) {
        document.getElementById('q_opts').value = '';
      }

      toggleOptions();
      modal.style.display = 'flex';
    }

    document.getElementById('formQuestion').onsubmit = function(e) {
      e.preventDefault();
      let opts = [];
      const type = document.getElementById('q_type').value;
      if (type === 'select' || type === 'radio') {
        const text = document.getElementById('q_opts').value;
        opts = text.split('\n').filter(t => t.trim() !== '');
      }

      const data = new FormData();
      data.append('action', 'save_question');
      data.append('id', document.getElementById('q_id').value);
      data.append('text', document.getElementById('q_text').value);
      data.append('type', type);
      data.append('options', JSON.stringify(opts));
      data.append('required', document.getElementById('q_req').checked ? 1 : 0);

      fetch('', {
          method: 'POST',
          body: data
        })
        .then(res => res.json())
        .then(res => {
          if (res.status === 'success') location.reload();
          else alert('Gagal menyimpan');
        });
    };

    function deleteQ(id) {
      if (!confirm('Hapus pertanyaan ini?')) return;
      const data = new FormData();
      data.append('action', 'delete_question');
      data.append('id', id);
      fetch('', {
          method: 'POST',
          body: data
        })
        .then(res => res.json())
        .then(res => {
          if (res.status === 'success') location.reload();
        });
    }

    function viewDetail(id) {
      const modalD = document.getElementById('detailModal');
      const content = document.getElementById('detailContent');
      content.innerHTML = 'Loading...';
      modalD.style.display = 'flex';
      fetch('get_pendaftar_detail.php?id=' + id)
        .then(res => res.text())
        .then(html => {
          content.innerHTML = html;
        });
    }
        function deletePendaftar(id) {
      if (!confirm('Yakin ingin menghapus data pendaftar ini?')) return;

      const data = new FormData();
      data.append('action', 'delete_pendaftar');
      data.append('id', id);

      fetch('', {
          method: 'POST',
          body: data
        })
        .then(res => res.json())
        .then(res => {
          if (res.status === 'success') {
            alert('Data berhasil dihapus.');
            location.reload(); // Reload halaman untuk update tabel
          } else {
            alert('Gagal menghapus: ' + (res.msg || 'Error unknown'));
          }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Terjadi kesalahan sistem.');
        });
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