<?php
session_start();
include '../koneksi.php';

// Cek Login & Role
if (!isset($_SESSION['nama'])) { header("Location: ../Login/login.php"); exit; }
if ($_SESSION['role'] != 'pengurus') {
  echo '<script>alert("AKSES DITOLAK!"); window.location.href="../Dashboard Anggota/anggota.php";</script>';
  exit;
}

 $nama_user = htmlspecialchars($_SESSION['nama']);
 $role = $_SESSION['role'];
 $foto_session = isset($_SESSION['foto']) ? $_SESSION['foto'] : '';
 $foto_profil = 'https://ui-avatars.com/api/?name=' . urlencode($nama_user) . '&background=d90429&color=fff';
if (!empty($foto_session) && file_exists("../uploads/foto_profil/" . $foto_session)) {
  $foto_profil = "../uploads/foto_profil/" . $foto_session;
}

// --- LOGIC HANDLE SETTINGS (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_absensi'])) {
    $tanggal = $_POST['tanggal'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $status = isset($_POST['status_aktif']) ? 'aktif' : 'tidak';

    $cek = mysqli_query($koneksi, "SELECT id FROM pengaturan_absensi LIMIT 1");
    if (mysqli_num_rows($cek) > 0) {
        $row = mysqli_fetch_assoc($cek);
        mysqli_query($koneksi, "UPDATE pengaturan_absensi SET tanggal='$tanggal', jam_mulai='$jam_mulai', jam_selesai='$jam_selesai', status='$status' WHERE id=".$row['id']);
    } else {
        mysqli_query($koneksi, "INSERT INTO pengaturan_absensi (tanggal, jam_mulai, jam_selesai, status) VALUES ('$tanggal', '$jam_mulai', '$jam_selesai', '$status')");
    }
    echo "<script>alert('Pengaturan absensi berhasil diperbarui!'); window.location.href='kelolaabsen.php';</script>";
}

// Ambil Pengaturan Saat Ini
 $set_query = mysqli_query($koneksi, "SELECT * FROM pengaturan_absensi LIMIT 1");
 $settings = mysqli_fetch_assoc($set_query);
if (!$settings) {
    $settings = ['tanggal' => date('Y-m-d'), 'jam_mulai' => '07:00', 'jam_selesai' => '09:00', 'status' => 'tidak'];
}

// Ambil data kalender
 $month = isset($_GET['m']) ? intval($_GET['m']) : date('m');
 $year = isset($_GET['y']) ? intval($_GET['y']) : date('Y');

// Hitung total hadir per hari
 $rekap_harian = [];
 $sql_rekap = "SELECT tanggal, COUNT(*) as total FROM absensi WHERE MONTH(tanggal) = '$month' AND YEAR(tanggal) = '$year' GROUP BY tanggal";
 $res_rekap = mysqli_query($koneksi, $sql_rekap);
while($r = mysqli_fetch_assoc($res_rekap)) {
    $rekap_harian[$r['tanggal']] = $r['total'];
}
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
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', 'Segoe UI', sans-serif; background-color: var(--bg-color); color: var(--text-color); line-height: 1.6; }
    a { text-decoration: none; color: inherit; }
    ul { list-style: none; }

    /* Layout & Navbar */
    header { background: #fff; box-shadow: var(--shadow-sm); position: fixed; width: 100%; top: 0; z-index: 1000; height: var(--header-height); }
    .navbar { display: flex; justify-content: space-between; align-items: center; height: 100%; padding: 0 20px; max-width: 100%; }
    .nav-left { flex: 1; display: flex; justify-content: flex-start; align-items: center; }
    .logo { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 18px; color: #000; }
    .logo img { height: 40px; }
    .nav-center { flex: 1; display: flex; justify-content: center; align-items: center; }
    .nav-right { flex: 1; display: flex; justify-content: flex-end; align-items: center; gap: 15px; position: relative; }
    .profile-btn { display: flex; align-items: center; cursor: pointer; padding: 5px; border-radius: 50px; transition: background 0.2s; }
    .profile-btn:hover { background-color: #f1f5f9; }
    .profile-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-color); }
    .profile-dropdown { position: absolute; top: 100%; right: 0; margin-top: 10px; background: #fff; border-radius: 8px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15); width: 220px; z-index: 1001; opacity: 0; visibility: hidden; transform: translateY(-10px); transition: all 0.2s ease; border: 1px solid var(--border-color); overflow: hidden; }
    .profile-dropdown.active { opacity: 1; visibility: visible; transform: translateY(0); }
    .dropdown-header { padding: 15px; background: #f8f9fa; border-bottom: 1px solid var(--border-color); }
    .dropdown-header p { font-weight: 600; font-size: 0.9rem; }
    .dropdown-header small { color: var(--text-muted); font-size: 0.75rem; }
    .profile-dropdown ul li a { display: flex; align-items: center; gap: 10px; padding: 12px 15px; font-size: 0.9rem; transition: 0.2s; }
    .profile-dropdown ul li a:hover { background-color: #fff1f1; color: var(--primary-color); }
    .menu-toggle { display: none; background: none; border: none; font-size: 24px; cursor: pointer; color: var(--primary-color); z-index: 1001; }
    .dashboard-container { display: flex; min-height: 100vh; padding-top: var(--header-height); }
    .sidebar { width: var(--sidebar-width); background: #fff; border-right: 1px solid var(--border-color); position: sticky; top: var(--header-height); height: calc(100vh - var(--header-height)); overflow-y: auto; z-index: 900; flex-shrink: 0; }
    .sidebar li { padding: 14px 25px; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 12px; border-left: 4px solid transparent; transition: all 0.2s; }
    .sidebar li:hover, .sidebar li.active { background-color: #fff1f1; color: var(--primary-color); border-left-color: var(--primary-color); }
    .sidebar a { display: flex; align-items: center; gap: 10px; width: 100%; }

    /* Main Content */
    .main-content { flex: 1; padding: 30px; width: 100%; }
    .page-title h1 { font-size: 1.75rem; color: var(--primary-color); margin-bottom: 5px; }
    .page-title p { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 25px; }

    /* Control Box */
    .control-box { background: white; padding: 25px; border-radius: var(--radius); box-shadow: var(--shadow-sm); margin-bottom: 25px; border: 1px solid var(--border-color); }
    .control-box h3 { margin-bottom: 20px; font-size: 1.1rem; display: flex; align-items: center; gap: 10px; }
    .control-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; align-items: end; }
    .form-group { display: flex; flex-direction: column; gap: 8px; }
    .form-group label { font-size: 0.85rem; font-weight: 600; color: var(--text-muted); }
    .form-control { width: 100%; padding: 10px 15px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.95rem; }
    .form-control:focus { border-color: var(--primary-color); outline: none; }
    .toggle-switch { display: flex; align-items: center; gap: 10px; margin-top: 5px; }
    .switch { position: relative; display: inline-block; width: 50px; height: 26px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 26px; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: var(--success-color); }
    input:checked + .slider:before { transform: translateX(22px); }

    /* Buttons */
    .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s ease; font-size: 0.9rem; color: white; }
    .btn-primary { background-color: var(--primary-color); }
    .btn-primary:hover { background-color: var(--primary-hover); }
    .btn-success { background-color: var(--success-color); }
    .btn-danger { background-color: #ef4444; }

    /* Calendar Styles */
    .calendar-container { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); padding: 20px; border: 1px solid var(--border-color); }
    .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color); }
    .calendar-header h2 { font-size: 1.2rem; }
    .calendar-nav { display: flex; gap: 10px; }
    .calendar-nav a { padding: 8px 15px; background: var(--bg-color); border-radius: 6px; font-weight: 600; transition: 0.2s; }
    .calendar-nav a:hover { background: var(--primary-color); color: white; }
    .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; }
    .calendar-day-name { text-align: center; font-weight: 600; color: var(--text-muted); font-size: 0.85rem; padding: 10px; }
    .calendar-day { border: 1px solid var(--border-color); border-radius: 8px; min-height: 80px; padding: 8px; position: relative; background: #fff; cursor: pointer; transition: 0.2s; display: flex; flex-direction: column; }
    .calendar-day:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    .calendar-day.empty { background: #f8f9fa; border-color: transparent; cursor: default; }
    
    /* Specific Day Styles */
    .calendar-day.today { border-color: var(--primary-color); border-width: 2px; }
    .day-number { font-weight: 600; color: var(--text-muted); font-size: 0.9rem; margin-bottom: auto; }
    .calendar-day.today .day-number { color: var(--primary-color); }
    
    /* PERUBAHAN WARNA ABSENSI */
    .bg-hadir { background-color: #dcfce7 !important; border-color: #16a34a !important; }
    .bg-hadir .day-number { color: #166534; }
    
    .bg-tidak-hadir { background-color: #fee2e2 !important; border-color: #dc2626 !important; }
    .bg-tidak-hadir .day-number { color: #991b1b; }

    .attendance-count { font-size: 0.8rem; background: rgba(0,0,0,0.05); color: #166534; padding: 4px 8px; border-radius: 4px; margin-top: 5px; text-align: center; font-weight: 600; }
    .bg-tidak-hadir .attendance-count { display: none; } /* Sembunyikan count 0 di merah */

    /* Modal List */
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); z-index: 2000; align-items: center; justify-content: center; padding: 20px; }
    .modal-content { background: white; border-radius: var(--radius); max-width: 900px; width: 100%; overflow: hidden; animation: fadeIn 0.3s ease; display: flex; flex-direction: column; max-height: 90vh; }
    .modal-header { padding: 15px 20px; background: var(--primary-color); color: white; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
    .modal-header h3 { font-size: 1.1rem; }
    .close-modal { background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; }
    .modal-body { padding: 20px; overflow-y: auto; }
    
    /* Table Styles */
    .data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .data-table th, .data-table td { padding: 12px; border-bottom: 1px solid var(--border-color); text-align: left; vertical-align: middle; }
    .data-table th { background-color: #f8f9fa; font-weight: 600; }
    
    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; background: #dcfce7; color: #166534; text-transform: capitalize; }
    
    .photo-thumb { width: 40px; height: 40px; object-fit: cover; border-radius: 4px; cursor: pointer; border: 1px solid #eee; transition: transform 0.2s; }
    .photo-thumb:hover { transform: scale(1.1); border-color: var(--primary-color); }

    /* Modal Khusus Foto */
    .modal-img-content { background: transparent; max-width: 90vw; max-height: 90vh; display: flex; align-items: center; justify-content: center; position: relative; }
    .modal-img-content img { max-width: 100%; max-height: 85vh; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.5); }
    .close-img-modal { position: absolute; top: -10px; right: -10px; background: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-weight: bold; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 10; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    
    @media (max-width: 992px) {
      .sidebar { position: fixed; top: var(--header-height); left: auto; right: -260px; transition: right 0.3s ease; z-index: 999; }
      .sidebar.active { right: 0; }
      .menu-toggle { display: block; }
      .logo span { display: none; }
      .control-grid { grid-template-columns: 1fr; }
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
  </style>
</head>

<body>
  <!-- HEADER & SIDEBAR -->
  <header>
    <nav class="navbar">
      <div class="nav-left">
        <div class="logo"><img src="../Gambar/logpmi.png" alt="Logo PMR"><span>PMR MILLENIUM</span></div>
      </div>
      <div class="nav-center"></div>
      <div class="nav-right">
        <div class="profile-btn" id="profileBtn">
          <img src="<?= $foto_profil ?>" alt="Foto Profil" class="profile-img">
        </div>
        <div class="profile-dropdown" id="profileDropdown">
          <div class="dropdown-header"><p><?= $nama_user ?></p><small><?= ucfirst($role) ?></small></div>
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
    <aside class="sidebar">
      <ul>
        <li><a href="../Dashboard Anggota/anggota.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
        <li class="active"><a href="kelolaabsen.php"><i class="fa-solid fa-calendar-check"></i> Kelola Absensi</a></li>
        <li><a href="kelolaperpus.php"><i class="fa-solid fa-book"></i> Kelola Perpustakaan Digital</a></li>
        <li><a href="kelola_pendaftaran.php"><i class="fa-solid fa-users"></i> Kelola Pendaftaran</a></li>
        <li style="margin-top: 20px; border-top: 1px solid #eee;">
          <a href="javascript:void(0)" onclick="confirmLogout()"><i class="fa-solid fa-right-from-bracket"></i> Log Out</a>
        </li>
      </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <div class="page-title">
        <h1>Kelola Absensi</h1>
        <p>Atur waktu absensi dan lihat rekap kehadiran anggota.</p>
      </div>

      <!-- Control Box -->
      <section class="control-box">
        <h3><i class="fas fa-cog"></i> Pengaturan Absensi</h3>
        <form method="POST" action="">
          <div class="control-grid">
            <div class="form-group">
              <label>Tanggal Absensi</label>
              <input type="date" name="tanggal" class="form-control" value="<?= $settings['tanggal'] ?>" required>
            </div>
            <div class="form-group">
              <label>Jam Mulai</label>
              <input type="time" name="waktu_mulai" class="form-control" value="<?= $settings['waktu_mulai'] ?>" required>
            </div>
            <div class="form-group">
              <label>Jam Selesai</label>
              <input type="time" name="waktu_selesai" class="form-control" value="<?= $settings['waktu_selesai'] ?>" required>
            </div>
            <div class="form-group">
              <label>Status</label>
              <div class="toggle-switch">
                <label class="switch">
                  <input type="checkbox" name="status_aktif" <?= $settings['status'] == 'aktif' ? 'checked' : '' ?>>
                  <span class="slider"></span>
                </label>
                <span id="statusLabel" style="font-weight: 600; color: <?= $settings['status'] == 'aktif' ? 'var(--success-color)' : 'var(--text-muted)' ?>">
                  <?= $settings['status'] == 'aktif' ? 'DIBUKA' : 'DITUTUP' ?>
                </span>
              </div>
            </div>
            <div class="form-group" style="align-self: end;">
              <button type="submit" name="update_absensi" class="btn btn-primary" style="width: 100%;"><i class="fas fa-save"></i> Simpan</button>
            </div>
          </div>
        </form>
      </section>

      <!-- Export Box (FITUR BARU) -->
      <section class="control-box" style="border-left: 4px solid var(--primary-color);">
        <h3><i class="fas fa-file-export"></i> Export Rekap Data</h3>
        <div class="control-grid">
            <div class="form-group">
                <label>Dari Tanggal</label>
                <input type="date" id="exportStart" class="form-control" value="<?= date('Y-m-01') ?>">
            </div>
            <div class="form-group">
                <label>Sampai Tanggal</label>
                <input type="date" id="exportEnd" class="form-control" value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group" style="align-self: end;">
                <div style="display: flex; gap: 10px;">
                    <button onclick="exportRange('excel')" class="btn btn-success"><i class="fas fa-file-excel"></i> Excel</button>
                    <button onclick="exportRange('pdf')" class="btn btn-danger"><i class="fas fa-file-pdf"></i> PDF</button>
                </div>
            </div>
        </div>
      </section>

      <!-- Calendar -->
      <section class="calendar-container">
        <div class="calendar-header">
          <h2><?= date('F Y', strtotime("$year-$month-01")) ?></h2>
          <div class="calendar-nav">
            <?php
            $prev_month = $month - 1; $prev_year = $year;
            if ($prev_month == 0) { $prev_month = 12; $prev_year--; }
            $next_month = $month + 1; $next_year = $year;
            if ($next_month == 13) { $next_month = 1; $next_year++; }
            ?>
            <a href="?m=<?= $prev_month ?>&y=<?= $prev_year ?>"><i class="fas fa-chevron-left"></i></a>
            <a href="?m=<?= date('m') ?>&y=<?= date('Y') ?>">Hari Ini</a>
            <a href="?m=<?= $next_month ?>&y=<?= $next_year ?>"><i class="fas fa-chevron-right"></i></a>
          </div>
        </div>

        <div class="calendar-grid">
          <div class="calendar-day-name">Min</div><div class="calendar-day-name">Sen</div>
          <div class="calendar-day-name">Sel</div><div class="calendar-day-name">Rab</div>
          <div class="calendar-day-name">Kam</div><div class="calendar-day-name">Jum</div><div class="calendar-day-name">Sab</div>

          <?php
            $first_day = date('w', strtotime("$year-$month-01"));
            $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $today = date('Y-m-d');

            for ($i = 0; $i < $first_day; $i++) { echo "<div class='calendar-day empty'></div>"; }

            for ($day = 1; $day <= $days_in_month; $day++) {
                $date_val = "$year-$month-" . str_pad($day, 2, '0', STR_PAD_LEFT);
                $dayOfWeek = date('w', strtotime($date_val)); // 0=Min, 3=Rab, 5=Jumat
                
                $classes = ['calendar-day'];
                $content = "";

                // Logika Warna
                if ($date_val == $today) $classes[] = 'today';

                if (isset($rekap_harian[$date_val])) {
                    // Jika ada absen -> HIJAU
                    $classes[] = 'bg-hadir';
                    $count = $rekap_harian[$date_val];
                    $content = "<div class='day-number'>$day</div><div class='attendance-count'>$count Hadir</div>";
                } elseif ($dayOfWeek == 3 || $dayOfWeek == 5) {
                    // Jika Rabu/Jumat & tidak ada absen -> MERAH
                    $classes[] = 'bg-tidak-hadir';
                    $content = "<div class='day-number'>$day</div><div style='font-size:0.7rem; color:#991b1b; text-align:center;'>Latihan</div>";
                } else {
                    // Hari biasa
                    $content = "<div class='day-number'>$day</div>";
                }

                echo "<div class='" . implode(' ', $classes) . "' onclick=\"openDateDetail('$date_val')\">";
                echo $content;
                echo "</div>";
            }
          ?>
        </div>
      </section>
    </main>
  </div>

  <!-- Modal Detail Tanggal -->
  <div class="modal" id="detailModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="modalTitle">Detail Kehadiran</h3>
        <button class="close-modal" onclick="document.getElementById('detailModal').style.display='none'">&times;</button>
      </div>
      <div class="modal-body">
        <!-- Tombol Export di modal dihapus, fokus melihat detail saja -->
        <table class="data-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Kelas</th>
              <th>Waktu</th>
              <th>Foto Bukti</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="detailBody"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Modal Khusus Foto (Zoom) -->
  <div class="modal" id="imageModal" onclick="this.style.display='none'">
    <div class="modal-img-content" onclick="event.stopPropagation()">
      <span class="close-img-modal" onclick="document.getElementById('imageModal').style.display='none'">&times;</span>
      <img id="fullImage" src="" alt="Foto Bukti">
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

  <script>
    // Logic Dropdown & Sidebar
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');

    menuToggle.addEventListener('click', (e) => { e.stopPropagation(); sidebar.classList.toggle('active'); profileDropdown.classList.remove('active'); });
    profileBtn.addEventListener('click', (e) => { e.stopPropagation(); profileDropdown.classList.toggle('active'); sidebar.classList.remove('active'); });
    document.addEventListener('click', (e) => {
      if (window.innerWidth <= 992) { if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) sidebar.classList.remove('active'); }
      if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) profileDropdown.classList.remove('active');
    });
    function confirmLogout() { if (confirm("Yakin keluar?")) window.location.href = "../logout.php"; }

    // Toggle Label Update
    const toggleInput = document.querySelector('input[name="status_aktif"]');
    const statusLabel = document.getElementById('statusLabel');
    if(toggleInput) {
        toggleInput.addEventListener('change', function() {
            if(this.checked) {
                statusLabel.textContent = "DIBUKA";
                statusLabel.style.color = "var(--success-color)";
            } else {
                statusLabel.textContent = "DITUTUP";
                statusLabel.style.color = "var(--text-muted)";
            }
        });
    }

    // --- Calendar Detail Logic ---
    function openDateDetail(dateStr) {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const dateObj = new Date(dateStr);
        document.getElementById('modalTitle').innerText = "Kehadiran: " + dateObj.toLocaleDateString('id-ID', options);

        fetch(`get_absensi_detail.php?tanggal=${dateStr}`)
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('detailBody');
                tbody.innerHTML = '';
                
                if(data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:#999;">Tidak ada data kehadiran.</td></tr>`;
                } else {
                    data.forEach((item, index) => {
                        let kelasDisplay = item.kelas ? item.kelas : '-'; 
                        tbody.innerHTML += `
                            <tr>
                                <td>${index+1}</td>
                                <td>${item.nama}</td>
                                <td>${kelasDisplay}</td>
                                <td>${item.jam}</td>
                                <td>
                                    <img src="../uploads/absensi/${item.foto}" class="photo-thumb" onclick="viewPhoto(this.src)" onerror="this.src='../Gambar/default.jpg'">
                                </td>
                                <td><span class="status-badge">${item.status}</span></td>
                            </tr>
                        `;
                    });
                }
                document.getElementById('detailModal').style.display = 'flex';
            })
            .catch(err => {
                console.error("Error fetching detail:", err);
                alert("Gagal memuat data.");
            });
    }

    // Fungsi untuk membuka modal foto
    function viewPhoto(src) {
        const modal = document.getElementById('imageModal');
        const img = document.getElementById('fullImage');
        img.src = src;
        modal.style.display = 'flex';
    }

    // --- FITUR EXPORT RANGE BARU ---
    function exportRange(type) {
        const start = document.getElementById('exportStart').value;
        const end = document.getElementById('exportEnd').value;

        if (!start || !end) {
            alert("Silakan pilih rentang tanggal terlebih dahulu.");
            return;
        }

        // Fetch data berdasarkan range
        fetch(`get_absensi_detail.php?start=${start}&end=${end}`)
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) {
                    alert("Tidak ada data pada rentang tanggal tersebut.");
                    return;
                }

                if (type === 'excel') {
                    exportToExcel(data, start, end);
                } else if (type === 'pdf') {
                    exportToPDF(data, start, end);
                }
            })
            .catch(err => {
                console.error("Error:", err);
                alert("Gagal mengambil data untuk export.");
            });
    }

    function exportToExcel(data, start, end) {
        const ws_data = [
            ["No", "Nama", "Kelas", "Tanggal", "Jam", "Status"]
        ];
        
        data.forEach((item, index) => {
            ws_data.push([
                index + 1,
                item.nama,
                item.kelas || '-',
                item.tanggal,
                item.jam,
                item.status
            ]);
        });

        const ws = XLSX.utils.aoa_to_sheet(ws_data);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Rekap Absensi");
        XLSX.writeFile(wb, `Rekap_Absensi_${start}_sd_${end}.xlsx`);
    }

    function exportToPDF(data, start, end) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        doc.setFontSize(18);
        doc.text("Rekap Absensi PMR Millenium", 14, 22);
        doc.setFontSize(11);
        doc.text(`Periode: ${start} s/d ${end}`, 14, 30);

        const tableColumn = ["No", "Nama", "Kelas", "Tanggal", "Jam", "Status"];
        const tableRows = [];

        data.forEach((item, index) => {
            tableRows.push([
                index + 1,
                item.nama,
                item.kelas || '-',
                item.tanggal,
                item.jam,
                item.status
            ]);
        });

        doc.autoTable(tableColumn, tableRows, { startY: 35 });
        doc.save(`Rekap_Absensi_${start}_sd_${end}.pdf`);
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