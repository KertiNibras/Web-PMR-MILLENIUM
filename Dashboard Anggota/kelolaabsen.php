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

// Proses Simpan Pengaturan (AJAX Handler)
if (isset($_POST['aksi_simpan_pengaturan'])) {
    $tanggal = $_POST['tanggal'];
    $mulai = $_POST['waktu_mulai'];
    $selesai = $_POST['waktu_selesai'];
    $status = isset($_POST['status']) ? 1 : 0;

    // Cek apakah sudah ada setting, jika ada update, jika tidak insert
    $cek = mysqli_query($koneksi, "SELECT id FROM pengaturan_absensi WHERE tanggal='$tanggal'");
    if (mysqli_num_rows($cek) > 0) {
        $row = mysqli_fetch_assoc($cek);
        mysqli_query($koneksi, "UPDATE pengaturan_absensi SET waktu_mulai='$mulai', waktu_selesai='$selesai', status='$status' WHERE id='".$row['id']."'");
    } else {
        mysqli_query($koneksi, "INSERT INTO pengaturan_absensi (tanggal, waktu_mulai, waktu_selesai, status) VALUES ('$tanggal', '$mulai', '$selesai', '$status')");
    }
    echo json_encode(['success' => true]);
    exit;
}

// Ambil Data User
 $nama_user = htmlspecialchars($_SESSION['nama']);
 $role = $_SESSION['role'];
 $foto_session = isset($_SESSION['foto']) ? $_SESSION['foto'] : '';
 $foto_profil = 'https://ui-avatars.com/api/?name=' . urlencode($nama_user) . '&background=d90429&color=fff';
if (!empty($foto_session) && file_exists("../uploads/foto_profil/" . $foto_session)) {
  $foto_profil = "../uploads/foto_profil/" . $foto_session;
}

// --- PERBAIKAN: Ambil setting hari ini dengan aman ---
 $today = date('Y-m-d');
 $q_setting = mysqli_query($koneksi, "SELECT * FROM pengaturan_absensi WHERE tanggal = '$today' LIMIT 1");
 $setting_hari_ini = mysqli_fetch_assoc($q_setting);

// Tentukan nilai default (jika data NULL/kosong)
 $val_mulai = '07:00';
 $val_selesai = '12:00';
 $val_status = 0; // 0 = Nonaktif
 $val_checked = '';

// Jika data ditemukan, timpa nilai default
if ($setting_hari_ini) {
    $val_mulai = $setting_hari_ini['waktu_mulai'];
    $val_selesai = $setting_hari_ini['waktu_selesai'];
    $val_status = $setting_hari_ini['status'];
    if ($val_status == 1) {
        $val_checked = 'checked';
    }
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
    /* CSS Standar */
    :root { --primary-color: #d90429; --primary-hover: #c92a2a; --bg-color: #f8f9fa; --card-bg: #ffffff; --text-color: #1e293b; --text-muted: #64748b; --border-color: #e2e8f0; --success-color: #10b981; --warning-color: #f59e0b; --info-color: #3b82f6; --shadow-sm: 0 1px 3px rgba(0,0,0,0.05); --radius: 12px; --header-height: 70px; --sidebar-width: 250px; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background-color: var(--bg-color); color: var(--text-color); }
    a { text-decoration: none; color: inherit; }
    
    /* Header & Sidebar */
    header { background: #fff; box-shadow: var(--shadow-sm); position: fixed; width: 100%; top: 0; z-index: 1000; height: var(--header-height); }
    .navbar { display: flex; justify-content: space-between; align-items: center; height: 100%; padding: 0 20px; }
    .logo { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 18px; }
    .logo img { height: 40px; }
    .nav-right { display: flex; align-items: center; gap: 15px; position: relative; }
    .profile-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-color); cursor: pointer; }
    .profile-dropdown { position: absolute; top: 100%; right: 0; margin-top: 10px; background: #fff; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); width: 220px; z-index: 1001; display: none; }
    .profile-dropdown.active { display: block; }
    .menu-toggle { display: none; background: none; border: none; font-size: 24px; cursor: pointer; color: var(--primary-color); }
    
    .dashboard-container { display: flex; min-height: 100vh; padding-top: var(--header-height); }
    .sidebar { width: var(--sidebar-width); background: #fff; border-right: 1px solid var(--border-color); position: sticky; top: var(--header-height); height: calc(100vh - var(--header-height)); overflow-y: auto; flex-shrink: 0; }
    .sidebar ul { list-style: none; padding: 0; }
    .sidebar li { padding: 14px 25px; cursor: pointer; display: flex; align-items: center; gap: 12px; border-left: 4px solid transparent; transition: all 0.2s; }
    .sidebar li:hover, .sidebar li.active { background-color: #fff1f1; color: var(--primary-color); border-left-color: var(--primary-color); }
    
    .main-content { flex: 1; padding: 30px; width: 100%; }
    .page-title h1 { font-size: 1.75rem; color: var(--primary-color); margin-bottom: 5px; }
    .page-title p { color: var(--text-muted); margin-bottom: 25px; }

    /* Card & Form */
    .card { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); padding: 20px; border: 1px solid var(--border-color); margin-bottom: 25px; }
    .control-form { display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end; }
    .form-group { flex: 1; min-width: 150px; }
    .form-group label { display: block; margin-bottom: 8px; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); }
    .form-control { width: 100%; padding: 10px 15px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.95rem; }
    .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; color: white; transition: 0.2s; }
    .btn-primary { background-color: var(--primary-color); }
    .btn-primary:hover { background-color: var(--primary-hover); }
    .btn-success { background-color: var(--success-color); }
    .btn-info { background-color: var(--info-color); }
    
    /* Toggle Switch */
    .switch-container { display: flex; align-items: center; gap: 10px; margin-top: 5px; }
    .switch { position: relative; display: inline-block; width: 50px; height: 26px; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; }
    input:checked + .slider { background-color: var(--success-color); }
    input:checked + .slider:before { transform: translateX(24px); }

    /* Calendar Styles */
    .calendar-container { background: white; border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); }
    .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    .calendar-header h2 { font-size: 1.2rem; color: var(--text-color); }
    .calendar-nav { background: none; border: 1px solid var(--border-color); padding: 5px 10px; border-radius: 6px; cursor: pointer; }
    
    .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; }
    .calendar-day-name { text-align: center; font-weight: 600; color: var(--text-muted); font-size: 0.85rem; padding: 10px; }
    .calendar-day { border: 1px solid var(--border-color); border-radius: 8px; padding: 10px; min-height: 80px; cursor: pointer; transition: 0.2s; position: relative; }
    .calendar-day:hover { background-color: #f8fafc; border-color: var(--primary-color); }
    .calendar-day.today { background-color: #fff1f1; border-color: var(--primary-color); }
    .calendar-day.selected { background-color: var(--primary-color); color: white; }
    .calendar-day.other-month { background-color: #f8f9fa; color: #ccc; pointer-events: none; }
    .date-num { font-weight: 600; margin-bottom: 5px; }
    .calendar-day.selected .date-num { color: white; }
    
    .badge-count { font-size: 0.75rem; background: var(--info-color); color: white; padding: 2px 6px; border-radius: 10px; position: absolute; bottom: 5px; right: 5px; }
    .calendar-day.selected .badge-count { background: white; color: var(--primary-color); }

    /* Detail Table */
    .detail-section { margin-top: 25px; display: none; }
    .detail-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    .detail-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .detail-table th, .detail-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
    .detail-table th { background-color: #f8f9fa; }

    /* Responsive */
    @media (max-width: 992px) {
      .sidebar { position: fixed; right: -260px; top: var(--header-height); height: calc(100vh - var(--header-height)); transition: right 0.3s; z-index: 999; }
      .sidebar.active { right: 0; }
      .menu-toggle { display: block; }
      .logo span { display: none; }
    }
  </style>
</head>
<body>

  <!-- HEADER -->
  <header>
    <nav class="navbar">
      <div class="nav-left"><div class="logo"><img src="../Gambar/logpmi.png" alt="Logo"><span>PMR MILLENIUM</span></div></div>
      <div class="nav-center"></div>
      <div class="nav-right">
        <img src="<?= $foto_profil ?>" alt="Profil" class="profile-img" id="profileBtn">
        <div class="profile-dropdown" id="profileDropdown">
            <div style="padding:15px; border-bottom:1px solid #eee;"><b><?= $nama_user ?></b><br><small><?= $role ?></small></div>
            <a href="ganti_foto.php" style="display:block; padding:12px 15px;"><i class="fa-solid fa-camera"></i> Ganti Foto</a>
            <a href="logout.php" style="display:block; padding:12px 15px;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
        <button class="menu-toggle"><i class="fa-solid fa-bars"></i></button>
      </div>
    </nav>
  </header>

  <div class="dashboard-container">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
      <ul>
        <li><a href="../Dashboard Anggota/anggota.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
        <li class="active"><a href="kelolaabsen.php"><i class="fa-solid fa-calendar-check"></i> Kelola Absensi</a></li>
        <li><a href="kelolaperpus.php"><i class="fa-solid fa-book"></i> Perpustakaan</a></li>
        <li><a href="kelola_pendaftaran.php"><i class="fa-solid fa-users"></i> Pendaftaran</a></li>
      </ul>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <div class="page-title">
        <h1>Kelola Absensi</h1>
        <p>Atur waktu absensi dan lihat rekap kehadiran anggota.</p>
      </div>

      <!-- Form Pengaturan Absensi -->
      <div class="card">
        <h3 style="margin-bottom: 15px;"><i class="fa-solid fa-cog"></i> Pengaturan Absensi</h3>
        <form id="formPengaturan" class="control-form">
          <div class="form-group">
            <label>Tanggal</label>
            <input type="date" id="setTanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="form-group">
            <label>Mulai</label>
            <input type="time" id="setMulai" class="form-control" value="<?= htmlspecialchars($val_mulai) ?>" required>
          </div>
          <div class="form-group">
            <label>Selesai</label>
            <input type="time" id="setSelesai" class="form-control" value="<?= htmlspecialchars($val_selesai) ?>" required>
          </div>
          <div class="form-group">
            <label>Status</label>
            <div class="switch-container">
              <label class="switch">
                <input type="checkbox" id="setStatus" <?= $val_checked ?>>
                <span class="slider"></span>
              </label>
              <span id="labelStatus"><?= ($val_status == 1) ? 'Aktif' : 'Nonaktif' ?></span>
            </div>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan</button>
        </form>
      </div>

      <!-- Kalender -->
      <div class="calendar-container">
        <div class="calendar-header">
          <button class="calendar-nav" id="prevMonth"><i class="fa-solid fa-chevron-left"></i></button>
          <h2 id="currentMonth">November 2023</h2>
          <button class="calendar-nav" id="nextMonth"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
        <div class="calendar-grid" id="calendarGrid">
          <!-- Grid generated by JS -->
        </div>
      </div>

      <!-- Detail Absensi per Tanggal -->
      <div class="detail-section card" id="detailSection">
        <div class="detail-header">
          <h3 id="detailTitle">Rekap Tanggal: -</h3>
          <div style="display:flex; gap:10px;">
            <button class="btn btn-success btn-sm" id="exportExcelDetail"><i class="fa-solid fa-file-excel"></i> Excel</button>
            <button class="btn btn-info btn-sm" id="exportPDFDetail"><i class="fa-solid fa-file-pdf"></i> PDF</button>
          </div>
        </div>
        <table class="detail-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Anggota</th>
              <th>Kelas</th>
              <th>Waktu Absen</th>
              <th>Status</th>
              <th>Bukti</th>
            </tr>
          </thead>
          <tbody id="detailBody"></tbody>
        </table>
      </div>
    </main>
  </div>

  <!-- MODAL FOTO -->
  <div id="photoModal" onclick="this.style.display='none'" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; justify-content:center; align-items:center;">
    <img id="modalImg" src="" style="max-width:90%; max-height:90%; border-radius:10px;">
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

  <script>
    // --- Dropdown & Sidebar Logic ---
    $('#profileBtn').click(() => $('#profileDropdown').toggleClass('active'));
    $('.menu-toggle').click(() => $('#sidebar').toggleClass('active'));
    
    // --- Status Toggle Label ---
    $('#setStatus').change(function() {
        $('#labelStatus').text(this.checked ? 'Aktif' : 'Nonaktif');
    });

    // --- Simpan Pengaturan ---
    $('#formPengaturan').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: '', // Post to self
            method: 'POST',
            data: {
                aksi_simpan_pengaturan: true,
                tanggal: $('#setTanggal').val(),
                waktu_mulai: $('#setMulai').val(),
                waktu_selesai: $('#setSelesai').val(),
                status: $('#setStatus').is(':checked') ? 1 : 0
            },
            dataType: 'json',
            success: res => {
                alert(res.success ? 'Pengaturan berhasil disimpan!' : 'Gagal menyimpan.');
                loadCalendar(currentYear, currentMonth); // Refresh calendar
            }
        });
    });

    // --- Calendar Logic ---
    let currentYear = new Date().getFullYear();
    let currentMonth = new Date().getMonth();
    const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

    function loadCalendar(year, month) {
        $('#currentMonth').text(`${monthNames[month]} ${year}`);
        
        // Get data from server
        $.get(`get_calendar_data.php?month=${month+1}&year=${year}`, function(data){
            renderCalendar(year, month, data);
        });
    }

    function renderCalendar(year, month, attendanceData) {
        const grid = $('#calendarGrid');
        grid.empty();
        
        // Header hari
        const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        days.forEach(d => grid.append(`<div class="calendar-day-name">${d}</div>`));

        const firstDay = new Date(year, month, 1).getDay();
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const today = new Date().toISOString().split('T')[0];

        // Padding awal
        for(let i=0; i<firstDay; i++) grid.append('<div class="calendar-day other-month"></div>');

        // Hari
        for(let day=1; day<=daysInMonth; day++) {
            const dateStr = `${year}-${String(month+1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const isToday = dateStr === today;
            
            // Cek apakah ada data absensi
            const count = attendanceData[dateStr] || 0;
            
            let html = `<div class="calendar-day ${isToday ? 'today' : ''}" data-date="${dateStr}">
                <div class="date-num">${day}</div>
                ${count > 0 ? `<span class="badge-count">${count} Hadir</span>` : ''}
            </div>`;
            grid.append(html);
        }
    }

    // Event Klik Tanggal
    $(document).on('click', '.calendar-day:not(.other-month)', function(){
        const date = $(this).data('date');
        if(!date) return;

        $('.calendar-day').removeClass('selected');
        $(this).addClass('selected');
        
        loadDetail(date);
    });

    function loadDetail(date) {
        $.get(`get_detail_absensi.php?date=${date}`, function(res){
            $('#detailTitle').text(`Rekap Tanggal: ${date}`);
            $('#detailSection').slideDown();
            
            const tbody = $('#detailBody');
            tbody.empty();
            if(res.length === 0){
                tbody.append('<tr><td colspan="6" style="text-align:center">Tidak ada data</td></tr>');
                return;
            }

            let no = 1;
            res.forEach(row => {
                tbody.append(`<tr>
                    <td>${no++}</td>
                    <td>${row.nama}</td>
                    <td>${row.kelas || '-'}</td>
                    <td>${row.jam}</td>
                    <td><span class="badge" style="color:green">${row.status}</span></td>
                    <td><img src='../uploads/absensi/${row.foto}' width='40' onclick="showPhoto(this.src)" style='cursor:pointer; border-radius:4px;'></td>
                </tr>`);
            });
        });
    }

    function showPhoto(src){
        $('#modalImg').attr('src', src);
        $('#photoModal').css('display', 'flex');
    }

    // Nav Kalender
    $('#prevMonth').click(() => { currentMonth--; if(currentMonth<0){currentMonth=11; currentYear--;} loadCalendar(currentYear, currentMonth); });
    $('#nextMonth').click(() => { currentMonth++; if(currentMonth>11){currentMonth=0; currentYear++;} loadCalendar(currentYear, currentMonth); });

    // Init
    loadCalendar(currentYear, currentMonth);

    // Export Logic (Menggunakan data dari tabel yang sedang tampil)
    $('#exportExcelDetail').click(() => {
        let data = [];
        $('#detailBody tr').each(function(){
            let cols = $(this).find('td').map(function() { return $(this).text(); }).get();
            if(cols.length > 0) data.push(cols);
        });
        
        let ws = XLSX.utils.aoa_to_sheet([["No", "Nama", "Kelas", "Waktu", "Status"], ...data]);
        let wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Rekap");
        XLSX.writeFile(wb, "Rekap_Absensi.xlsx");
    });
  </script>
</body>
</html>