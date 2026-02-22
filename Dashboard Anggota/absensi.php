<?php
session_start();
include '../koneksi.php';

// Cek Login
if (!isset($_SESSION['nama'])) {
    echo '<script>alert("Silakan login terlebih dahulu!"); window.location.href = "../Login/login.php";</script>';
    exit;
}

// --- LOGIKA AMBIL ID USER ---
if (!isset($_SESSION['id'])) {
    $nama_session = $_SESSION['nama'];
    $query_id = mysqli_query($koneksi, "SELECT id FROM users WHERE nama = '$nama_session'");
    if (mysqli_num_rows($query_id) > 0) {
        $data_user = mysqli_fetch_assoc($query_id);
        $_SESSION['id'] = $data_user['id'];
    } else {
        echo '<script>alert("Data user tidak ditemukan!"); window.location.href = "../logout.php";</script>';
        exit;
    }
}
 $id_user = $_SESSION['id'];

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
  font-family: "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  background-color: var(--bg-color);
  color: var(--text-color);
  line-height: 1.6;
}

/* Header */
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

/* Layout */
.dashboard-container {
  display: flex;
  min-height: 100vh;
  padding-top: 70px;
}

/* Sidebar */
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
  width: 100%;
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

/* Main Content */
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

/* Filters / Action Bar */
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

/* Buttons */
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
.btn-primary:hover {
  background-color: var(--primary-hover);
}
.btn-success {
  background-color: var(--success-color);
}
.btn-secondary {
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
}
.data-table td {
  padding: 15px;
  border-bottom: 1px solid var(--border-color);
  vertical-align: middle;
}
.data-table tr:hover {
  background-color: #fafafa;
}

/* Photo & Status */
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

/* Modal */
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
  max-width: 500px;
  width: 100%;
  overflow: hidden;
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
}
.modal-body {
  padding: 20px;
  text-align: center;
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
  border-radius: 6px;
  font-size: 0.95rem;
}

.camera-wrapper {
  width: 100%;
  background: #000;
  border-radius: 8px;
  overflow: hidden;
  margin: 15px 0;
  position: relative;
  aspect-ratio: 4/3;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* PERBAIKAN CSS: Object fit cover agar gambar gallery tidak gepeng */
#video,
#capturedImage {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
#video {
  transform: scaleX(-1);
} /* Kamera live tetap mirror */
#capturedImage {
  display: none;
  transform: none;
} /* Hasil tidak mirror */

/* Responsive */
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

      <!-- Action Bar -->
      <section class="content-header">
        <div style="display: flex; gap: 10px;">
            <button class="btn btn-primary" id="btnOpenCamera">
              <i class="fa-solid fa-camera"></i> Absensi Wajah
            </button>
        </div>
        
        <div class="filter-group">
          <label>Urutkan Tanggal</label>
          <select id="sortFilter" class="filter-control">
            <option value="newest">Terbaru</option>
            <option value="oldest">Terlama</option>
          </select>
        </div>
        
        <div class="filter-group">
          <label>Status</label>
          <select id="statusFilter" class="filter-control">
            <option value="">Semua Status</option>
            <option value="hadir">Hadir</option>
            <option value="izin">Izin</option>
            <option value="sakit">Sakit</option>
          </select>
        </div>
      </section>

      <!-- Table -->
      <section class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>Waktu Absensi</th>
              <th>Status</th>
              <th>Keterangan</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <?php
            if (mysqli_num_rows($query_absen) > 0) {
                while ($row = mysqli_fetch_assoc($query_absen)) {
                    $tgl = date('d M Y', strtotime($row['tanggal']));
                    $status_class = 'status-' . substr($row['status'], 0, 1);
                    echo "<tr>";
                    echo "<td>{$tgl}, {$row['jam']}</td>";
                    echo "<td><span class='status-badge {$status_class}'>{$row['status']}</span></td>";
                    echo "<td>{$row['keterangan']}</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3' style='text-align:center; padding:30px; color:#999;'>Belum ada data absensi.</td></tr>";
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
        
        <!-- Form Input -->
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
          <video id="video" autoplay playsinline></video>
          <canvas id="canvas" style="display:none;"></canvas>
          <img id="capturedImage" alt="Capture">
        </div>

        <div id="cameraControls" style="margin-top: 15px; display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
          <button class="btn btn-primary" id="btnCapture"><i class="fa-solid fa-camera"></i> Ambil Foto</button>
          <button class="btn btn-secondary" id="btnGallery" style="display: inline-flex;">
            <i class="fa-solid fa-images"></i> Dari Galeri
          </button>
          <input type="file" id="fileInput" accept="image/*" style="display: none;">
          
          <button class="btn btn-success" id="btnSubmit" style="display:none; width: 100%;"><i class="fa-solid fa-paper-plane"></i> Kirim Absensi</button>
        </div>
        
        <div id="successMessage" style="display:none;">
            <div style="color: var(--success-color); font-size: 3rem; margin: 20px 0;"><i class="fa-solid fa-check-circle"></i></div>
            <h4>Absensi Berhasil!</h4>
            <button class="btn btn-primary" style="margin-top: 15px;" onclick="location.reload()">Selesai</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // --- Filter Logic ---
    const sortFilter = document.getElementById("sortFilter");
    const statusFilter = document.getElementById("statusFilter");
    const tableBody = document.getElementById("tableBody");

    sortFilter.addEventListener("change", applyFilter);
    statusFilter.addEventListener("change", applyFilter);

    function applyFilter() {
        let rows = Array.from(tableBody.querySelectorAll("tr"));
        const selectedStatus = statusFilter.value.toLowerCase();
        
        rows.forEach(row => {
            const statusCell = row.querySelector(".status-badge");
            if (!statusCell) return;
            const statusText = statusCell.textContent.toLowerCase();
            row.style.display = (selectedStatus === "" || statusText === selectedStatus) ? "" : "none";
        });

        const visibleRows = rows.filter(row => row.style.display !== "none");
        visibleRows.sort((a, b) => {
            const dateA = new Date(a.cells[0].innerText);
            const dateB = new Date(b.cells[0].innerText);
            return sortFilter.value === "newest" ? dateB - dateA : dateA - dateB;
        });
        tableBody.innerHTML = "";
        visibleRows.forEach(row => tableBody.appendChild(row));
    }

    // --- Camera & Gallery Logic ---
    let currentImageData = null;
    const modal = document.getElementById('cameraModal');
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const capturedImage = document.getElementById('capturedImage');
    const btnCapture = document.getElementById('btnCapture');
    const btnGallery = document.getElementById('btnGallery');
    const fileInput = document.getElementById('fileInput');
    const btnSubmit = document.getElementById('btnSubmit');
    let stream = null;

    document.querySelector('.menu-toggle').onclick = () => document.querySelector('.sidebar').classList.toggle('active');

    // Open Modal
    document.getElementById('btnOpenCamera').onclick = async () => {
        modal.style.display = 'flex';
        resetModal();
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } });
            video.srcObject = stream;
            video.style.display = 'block';
            capturedImage.style.display = 'none';
        } catch (err) { alert("Kamera error: " + err); }
    };
    
    document.getElementById('btnCloseModal').onclick = () => { modal.style.display = 'none'; stopCamera(); };
    window.onclick = (e) => { if(e.target == modal) { modal.style.display = 'none'; stopCamera(); } };

    function stopCamera() { if(stream) { stream.getTracks().forEach(track => track.stop()); stream = null; } }

    // 1. Logika Ambil Foto dari Kamera
    btnCapture.onclick = () => {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        let ctx = canvas.getContext('2d');
        
        // Balik canvas agar tidak mirror
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        ctx.setTransform(1, 0, 0, 1, 0, 0);

        currentImageData = canvas.toDataURL('image/png');
        
        capturedImage.src = currentImageData;
        video.style.display = 'none';
        capturedImage.style.display = 'block';
        
        stopCamera();
        showSubmitButton();
    };

    // 2. Logika Ambil dari Galeri (DIPERBAIKI)
    btnGallery.onclick = () => { fileInput.click(); };

    fileInput.onchange = (e) => {
        const file = e.target.files[0];
        if (file) {
            // Validasi ukuran (Max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert("Ukuran file terlalu besar (Maks 5MB)");
                return;
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                // Ini yang Penting: Simpan hasil baca file ke variabel global
                currentImageData = event.target.result;
                
                // Tampilkan di preview
                capturedImage.src = currentImageData;
                video.style.display = 'none';
                capturedImage.style.display = 'block';
                stopCamera();
                showSubmitButton();
            };
            reader.readAsDataURL(file); // Baca file sebagai Base64
        }
    };

    function showSubmitButton() {
        btnCapture.style.display = 'none';
        btnGallery.style.display = 'none';
        btnSubmit.style.display = 'inline-flex';
    }

    // 3. Logika Kirim Absensi
    btnSubmit.onclick = () => {
        const status = document.getElementById('statusSelect').value;
        const keterangan = document.getElementById('keteranganInput').value;

        if(status !== 'hadir' && keterangan === '') {
            alert("Mohon isi keterangan untuk status Izin/Sakit.");
            return;
        }
        
        // Cek apakah ada data gambar
        if (!currentImageData) {
            alert("Ambil foto atau pilih gambar dulu!");
            return;
        }

        if(confirm("Yakin mengirim absensi?")) {
            btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
            btnSubmit.disabled = true;

            fetch('proses_absensi.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    kegiatan: 'Absensi Harian', 
                    foto: currentImageData,
                    status: status,
                    keterangan: keterangan
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
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
        video.style.display = 'block'; capturedImage.style.display = 'none';
        document.getElementById('cameraControls').style.display = 'flex';
        document.getElementById('successMessage').style.display = 'none';
        
        btnCapture.style.display = 'inline-flex';
        btnGallery.style.display = 'inline-flex';
        btnSubmit.style.display = 'none';
        
        btnCapture.innerHTML = '<i class="fa-solid fa-camera"></i> Ambil Foto';
        btnCapture.disabled = false;
        btnSubmit.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Kirim Absensi';
        btnSubmit.disabled = false;
        
        currentImageData = null;
        document.getElementById('statusSelect').value = 'hadir';
        document.getElementById('keteranganInput').value = '';
        fileInput.value = ''; 
    }

    function goBack() { window.history.back(); }
    function confirmLogout() { if(confirm('Yakin keluar?')) window.location.href = '../logout.php'; }
  </script>
</body>
</html>