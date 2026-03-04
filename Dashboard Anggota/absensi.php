<?php
session_start();
include '../koneksi.php';

if (!isset($_SESSION['nama'])) {
  echo '<script>alert("Silakan login!"); window.location = "../Login/login.php";</script>';
  exit;
}

// Ambil ID User
if (!isset($_SESSION['id'])) {
  $stmt = mysqli_prepare($koneksi, "SELECT id FROM users WHERE nama = ?");
  mysqli_stmt_bind_param($stmt, "s", $_SESSION['nama']);
  mysqli_stmt_execute($stmt);
  $res = mysqli_stmt_get_result($stmt);
  if($row = mysqli_fetch_assoc($res)) $_SESSION['id'] = $row['id'];
}

 $id_user = $_SESSION['id'];
 $nama_user = htmlspecialchars($_SESSION['nama']);
 $foto_profil = 'https://ui-avatars.com/api/?name=' . urlencode($nama_user) . '&background=d90429&color=fff';
// (Logika foto profil sama seperti sebelumnya)

// Cek Status Absensi Sekarang
 $now_time = date('H:i:s');
 $now_date = date('Y-m-d');
 $q_cek = mysqli_query($koneksi, "SELECT * FROM pengaturan_absensi WHERE tanggal='$now_date' AND status=1 AND waktu_mulai <= '$now_time' AND waktu_selesai >= '$now_time'");
 $is_open = mysqli_num_rows($q_cek) > 0;
 $setting = mysqli_fetch_assoc($q_cek);

// Ambil data absensi user untuk kalender
 $q_riwayat = mysqli_query($koneksi, "SELECT tanggal FROM absensi WHERE user_id='$id_user'");
 $riwayat_tanggal = [];
while($r = mysqli_fetch_assoc($q_riwayat)){
    $riwayat_tanggal[] = $r['tanggal'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Absensi | PMR Millenium</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <!-- CSS sama seperti halaman pengurus, tapi dengan sedikit penyesuaian warna status -->
  <style>
    /* Copy CSS dari halaman pengurus untuk konsistensi, atau gunakan CSS Anda sebelumnya */
    :root { --primary-color: #d90429; --success-color: #10b981; --border-color: #e2e8f0; --bg-color: #f8f9fa; }
    body { font-family: 'Inter', sans-serif; background: var(--bg-color); margin: 0; }
    /* Header CSS (Sama) */
    header { background: #fff; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 100; }
    .logo { display: flex; align-items: center; gap: 10px; font-weight: bold; color: #000; }
    
    /* Container */
    .container { max-width: 800px; margin: 20px auto; padding: 0 15px; }
    
    /* Status Box */
    .status-box { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); text-align: center; margin-bottom: 20px; border: 1px solid var(--border-color); }
    .status-title { font-size: 1.2rem; font-weight: 600; margin-bottom: 10px; }
    .status-time { color: #666; margin-bottom: 15px; }
    
    /* Tombol Absen */
    .btn-absen { width: 100%; padding: 15px; border: none; border-radius: 10px; font-size: 1.1rem; font-weight: bold; color: white; background: var(--primary-color); cursor: pointer; transition: 0.3s; }
    .btn-absen:disabled { background: #ccc; cursor: not-allowed; }
    .btn-absen.active { background: var(--success-color); animation: pulse 2s infinite; }
    
    @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); } 100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } }

    /* Kalender */
    .calendar-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px; background: white; padding: 15px; border-radius: 12px; border: 1px solid var(--border-color); }
    .day-name { text-align: center; font-size: 0.8rem; color: #999; font-weight: 600; }
    .day { aspect-ratio: 1; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 0.9rem; position: relative; cursor: default; color: #444; }
    .day.today { border: 2px solid var(--primary-color); color: var(--primary-color); font-weight: bold; }
    .day.hadir { background: #dcfce7; color: var(--success-color); font-weight: bold; }
    .day.hadir::after { content: '✓'; position: absolute; bottom: 2px; font-size: 0.6rem; }
    .day.other { color: #ddd; }

    /* Modal Camera (Sederhana) */
    .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:999; justify-content:center; align-items:center; }
    .modal-content { background: white; width: 90%; max-width: 500px; padding: 20px; border-radius: 15px; text-align: center; }
    video, #canvas { width: 100%; border-radius: 10px; background: #000; }
  </style>
</head>
<body>

<header>
  <div class="logo"><img src="../Gambar/logpmi.png" width="35"> PMR MILLENIUM</div>
  <div style="display:flex; align-items:center; gap:10px;">
    <span><?= $nama_user ?></span>
    <img src="<?= $foto_profil ?>" width="35" style="border-radius:50%;">
  </div>
</header>

<div class="container">
  
  <!-- Box Status & Tombol -->
  <div class="status-box">
    <div class="status-title" id="statusTitle">Status Absensi</div>
    <div class="status-time" id="statusTime">
        <?= $is_open ? "Dibuka sampai pukul ".date('H:i', strtotime($setting['waktu_selesai'])) : "Absensi Belum Dibuka / Ditutup" ?>
    </div>
    
    <button class="btn-absen <?= $is_open ? 'active' : '' ?>" id="btnAbsen" <?= !$is_open ? 'disabled' : '' ?>>
      <i class="fa-solid fa-camera"></i> Absen Sekarang
    </button>
  </div>

  <!-- Kalender Riwayat -->
  <h3 style="margin-bottom: 15px; color: #444;">Riwayat Kehadiranmu</h3>
  <div class="calendar-nav">
    <button id="prev" style="border:none; background:none; cursor:pointer;"><i class="fa-solid fa-arrow-left"></i></button>
    <span id="monthLabel">November 2023</span>
    <button id="next" style="border:none; background:none; cursor:pointer;"><i class="fa-solid fa-arrow-right"></i></button>
  </div>
  <div class="calendar-grid" id="calGrid"></div>

</div>

<!-- Modal Kamera -->
<div class="modal" id="camModal">
  <div class="modal-content">
    <h3 style="margin-bottom:15px;">Ambil Foto Selfie</h3>
    <video id="video" autoplay playsinline></video>
    <canvas id="canvas" style="display:none;"></canvas>
    <img id="photoPreview" style="display:none; width:100%; border-radius:10px;">
    
    <div style="margin-top:15px; display:flex; gap:10px; justify-content:center;">
        <button class="btn-absen" id="captureBtn" style="width:auto; padding:10px 20px;"><i class="fa fa-camera"></i> Ambil</button>
        <button class="btn-absen active" id="submitBtn" style="display:none; width:auto; padding:10px 20px;">Kirim Absensi</button>
        <button onclick="stopCam()" style="border:none; background:#eee; padding:10px 20px; border-radius:8px;">Batal</button>
    </div>
  </div>
</div>

<script>
const hadirDates = <?= json_encode($riwayat_tanggal) ?>;
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();
const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

function renderCal() {
    const grid = document.getElementById('calGrid');
    const label = document.getElementById('monthLabel');
    label.textContent = monthNames[currentMonth] + " " + currentYear;
    grid.innerHTML = '';

    // Header
    ['Min','Sen','Sel','Rab','Kam','Jum','Sab'].forEach(d => grid.innerHTML += `<div class="day-name">${d}</div>`);

    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    const daysInMonth = new Date(currentYear, currentMonth+1, 0).getDate();
    const today = new Date().toISOString().split('T')[0];

    // Empty cells
    for(let i=0; i<firstDay; i++) grid.innerHTML += `<div class="day other"></div>`;

    // Days
    for(let d=1; d<=daysInMonth; d++) {
        let dateStr = `${currentYear}-${String(currentMonth+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
        let classes = "day";
        if(dateStr === today) classes += " today";
        if(hadirDates.includes(dateStr)) classes += " hadir";
        
        grid.innerHTML += `<div class="${classes}">${d}</div>`;
    }
}
document.getElementById('prev').onclick = () => { currentMonth--; if(currentMonth<0){currentMonth=11; currentYear--;} renderCal(); };
document.getElementById('next').onclick = () => { currentMonth++; if(currentMonth>11){currentMonth=0; currentYear++;} renderCal(); };
renderCal();

// Realtime Check Status (Polling setiap 5 detik)
setInterval(() => {
    fetch('check_status_absen.php').then(r => r.json()).then(res => {
        const btn = document.getElementById('btnAbsen');
        const title = document.getElementById('statusTitle');
        const time = document.getElementById('statusTime');
        
        if(res.status === 'buka'){
            btn.disabled = false;
            btn.classList.add('active');
            title.textContent = "Absensi Dibuka!";
            time.textContent = "Sisa waktu sampai pukul " + res.selesai;
        } else {
            btn.disabled = true;
            btn.classList.remove('active');
            title.textContent = "Absensi Ditutup";
            time.textContent = "Absensi belum tersedia untuk saat ini.";
        }
    });
}, 5000);

// Camera Logic
const video = document.getElementById('video');
const canvas = document.getElementById('canvas');
const modal = document.getElementById('camModal');
let stream = null;

document.getElementById('btnAbsen').onclick = async function() {
    modal.style.display = 'flex';
    stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
    video.srcObject = stream;
    video.style.display = 'block';
    document.getElementById('photoPreview').style.display = 'none';
    document.getElementById('captureBtn').style.display = 'inline-block';
    document.getElementById('submitBtn').style.display = 'none';
};

window.stopCam = () => { if(stream) stream.getTracks().forEach(t => t.stop()); modal.style.display = 'none'; };

document.getElementById('captureBtn').onclick = () => {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);
    
    // Mirror effect jika perlu
    // ...
    
    let dataUrl = canvas.toDataURL('image/jpeg');
    document.getElementById('photoPreview').src = dataUrl;
    document.getElementById('photoPreview').style.display = 'block';
    video.style.display = 'none';
    
    document.getElementById('captureBtn').style.display = 'none';
    document.getElementById('submitBtn').style.display = 'inline-block';
    stopCam(); // Stop camera stream, tapi modal tetap terbuka
    modal.style.display = 'flex'; // Pastikan modal tetap ada
};

document.getElementById('submitBtn').onclick = function() {
    this.innerHTML = "Mengirim...";
    this.disabled = true;
    
    let photoData = document.getElementById('photoPreview').src;
    
    fetch('proses_absensi.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ foto: photoData })
    }).then(r => r.json()).then(res => {
        if(res.success){
            alert("Absensi Berhasil!");
            location.reload();
        } else {
            alert(res.message || "Gagal");
            this.innerHTML = "Kirim Absensi";
            this.disabled = false;
        }
    });
};

</script>
</body>
</html>