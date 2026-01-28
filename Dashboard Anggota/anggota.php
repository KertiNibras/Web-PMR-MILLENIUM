<?php
session_start();
if (!isset($_SESSION['nama'])) {
    header("Location: ../Login/login.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="id">  
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Anggota - PMR Millenium</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="icon" href="../Gambar/logpmi.png" type="image/png">
  <style>
    * {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}
/* import font dari Google Fonts */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

body {
  font-family: 'Segoe UI', sans-serif;
  background-size: 100%;
  background-repeat: no-repeat; 
}

/* Notification Styles */
.notification {
  position: fixed;
  top: 80px;
  right: 20px;
  background: linear-gradient(135deg, #4CAF50, #45a049);
  color: white;
  padding: 15px 25px;
  border-radius: 8px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 12px;
  animation: slideInRight 0.5s ease-out, fadeOut 0.5s ease-in 2.5s forwards;
  transform: translateX(120%);
}

.notification i {
  font-size: 20px;
  animation: pulse 2s infinite;
}

.notification-content {
  display: flex;
  flex-direction: column;
}

.notification-title {
  font-weight: bold;
  font-size: 16px;
}

.notification-message {
  font-size: 14px;
  opacity: 0.9;
}

@keyframes slideInRight {
  from {
    transform: translateX(120%);
  }
  to {
    transform: translateX(0);
  }
}

@keyframes fadeOut {
  from {
    opacity: 1;
    transform: translateX(0);
  }
  to {
    opacity: 0;
    transform: translateX(120%);
  }
}

@keyframes pulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
  100% {
    transform: scale(1);
  }
}

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

.nav-links {
  list-style: none;
  display: flex;
  gap: 5px;
  align-items: center;
  transition: color 0.3s ease;
}


.nav-links li a {
  position: relative;
  transition: color 0.3s ease;
  text-decoration: none;
  color: #000;
  font-size: 14px;
}

.btn-header {
  background-color: #e60000;
 color: white;
 text-decoration: none;
  padding: 10px 36px;
  border-radius: 20px;
  font-weight: bold;
  font-size: 14px;
}

.btn-primary {
  background-color: #e60000;
  color: white;
  padding: 12px 20px;
  border-radius: 5px;
  font-weight: bold;
  text-decoration: none;
}


a {
  text-decoration: none;
}
    @keyframes slide {
      
    0% { background-image: url('Gambar/background.png'); }
    33% { background-image: url('Gambar/background2.png'); }
    66% { background-image: url('Gambar/background3.png'); }
    100% { background-image: url('Gambar/background.png'); }
}
.highlight-section {
  background: #fefefe;
  padding: 20px 20px;
}

.section-title {
  text-align: center;
  font-size: 2rem;
  margin-bottom: 30px;
  color: #e53935;
}



@keyframes fadeInDown {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Shrink header saat scroll */
.navbar.shrink {
  padding: 10px 20px;
  transition: padding 0.3s ease;
}
.nav-links li a::after {
  content: '';
  position: absolute;
  width: 0%;
  height: 2px;
  bottom: -4px;
  left: 0;
  background-color: red;
  transition: width 0.3s ease;
}

.nav-links li a:hover::after {
  width: 100%;
}

/* Logo pulse saat hover */
.navbar .logo:hover {
  animation: pulse 0.6s ease;
}


/* Layout */
.dashboard-container {
  display: flex;
  min-height: 100vh;
}

/* Sidebar */
.sidebar {
  width: 220px;
  background: #ffffff;
  padding-top: 75px;
  box-shadow: 2px 0 6px rgba(0,0,0,0.05);
  
}

.sidebar ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar li {
  padding: 15px 20px;
  cursor: pointer;
  color: #444;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: 0.3s;
  display: block;
  width: 100%;
}

.sidebar a {
  text-decoration: none;
  color: inherit;
  display: flex;
  align-items: center;
  gap: 10px;
}
.sidebar li:hover,
.sidebar li.active {
  background: #d90429;
  color: white;
}

/* Main Content */
.main-content {
  flex: 1;
  padding: 100px;
}

.main-content h1 {
  font-size: 1.6rem;
  margin-bottom: 50px;
}

/* Cards */
.cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
}

.card {
  background: white;
  border-radius: 15px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  padding: 20px;
  text-align: center;
  transition: 0.3s;
}

.card:hover {
  transform: translateY(-5px);
}

.card .icon {
  font-size: 40px;
  color: #d90429;
  margin-bottom: 15px;
}

.card button {
  margin-top: 10px;
  background: #d90429;
  border: none;
  color: white;
  padding: 8px 20px;
  border-radius: 8px;
  cursor: pointer;
  transition: 0.3s;
}

.card button:hover {
  background: #b30322;
}
/* --- SIDEBAR RESPONSIF --- */
.menu-toggle {
  display: none;
  background: none;
  border: none;
  font-size: 22px;
  cursor: pointer;
  color: #d90429;
}

@media (max-width: 768px) {
  .menu-toggle {
    display: block;
  }

  .dashboard-container {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
    position: absolute;
    top: 65px;
    left: 0;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transform: translateY(-120%);
    transition: transform 0.3s ease;
    z-index: 999;
  }

  .sidebar.active {
    transform: translateY(0);
  }

  .sidebar ul {
    flex-direction: column;
    border: none;
  }

  .sidebar li {
    text-align: center;
    padding: 12px;
  }

  .main-content {
    padding: 90px 20px;
  }
  
  /* Notification mobile */
  .notification {
    top: 70px;
    right: 10px;
    left: 10px;
    transform: translateX(0) translateY(-100%);
    animation: slideInDown 0.5s ease-out, fadeOutUp 0.5s ease-in 2.5s forwards;
  }
  
  @keyframes slideInDown {
    from {
      transform: translateY(-100%);
    }
    to {
      transform: translateY(0);
    }
  }
  
  @keyframes fadeOutUp {
    from {
      opacity: 1;
      transform: translateY(0);
    }
    to {
      opacity: 0;
      transform: translateY(-100%);
    }
  }
}
/* tombol hamburger disembunyikan di desktop */
.menu-toggle {
  display: none;
  background: none;
  border: none;
  font-size: 22px;
  cursor: pointer;
  color: #d90429;
}

/* tombol logout tampil di desktop */
.logout-btn {
  background-color: #e60000;
  color: white;
  text-decoration: none;
  padding: 10px 36px;
  border-radius: 20px;
  font-weight: bold;
  font-size: 14px;
}

/* aturan saat layar kecil (mobile) */
@media (max-width: 768px) {
  /* tampilkan hamburger, sembunyikan logout */
  .menu-toggle {
    display: block;
  }

  .logout-btn {
    display: none;
  }

  /* sidebar versi mobile */
  .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transform: translateY(-120%);
    transition: transform 0.3s ease;
    z-index: 999;
  }

  .sidebar.active {
    transform: translateY(0);
  }
}

  </style>
</head>
<body>
  <header>
    <header>
  <nav class="navbar">
    <div class="logo">
      <img src="../Gambar/logpmi.png" alt="Logo">
      <span>PMR MILLENIUM</span>
    </div>

    <!-- tombol hamburger -->
    <button class="menu-toggle"><i class="fa-solid fa-bars"></i></button>
  </nav>
</header>
  </header>
  
  <!-- Notification Element -->
  <?php if(isset($_SESSION['login_success'])): ?>
  <div id="loginNotification" class="notification">
    <i class="fas fa-check-circle"></i>
    <div class="notification-content">
      <div class="notification-title">Login Berhasil</div>
      <div class="notification-message">Selamat datang, <?= $_SESSION['nama']; ?>! Anda telah login sebagai Anggota.</div>
    </div>
  </div>
<?php unset($_SESSION['login_success']); endif; ?>

  
  <div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar">
  <ul>
    <li class="active"><a href="anggota.html"><i class="fa-solid fa-house"></i> Dashboard</a></li>
    <li><a href="absensi.html"><i class="fa-solid fa-calendar-check"></i> Rekap Absensi</a></li>
    <li><a href="perpus.html"><i class="fa-solid fa-book"></i> Perpustakaan Digital</a></li>
    <li><a href=""><i class="fa-solid fa-gamepad"></i> Quiz</a></li>
    <li><a href="../logout.php"><i class="fa-solid fa-right-from-bracket"></i>Log Out</a></li>
  </ul>
</aside>

    <!-- Main Content -->
    <main class="main-content">
      <h1>Selamat Datang, <?php echo $_SESSION['nama']; ?>!</h1>



      <div class="cards">
        <div class="card">
          <i class="fa-solid fa-calendar-check icon"></i>
          <h3>Rekap Absensi</h3>
          <p>Lihat catatan kehadiran kamu di kegiatan PMR.</p>
          <button onclick="location.href='absensi.html'">Lihat</button>
        </div>
        <div class="card">
          <i class="fa-solid fa-book icon"></i>
          <h3>Perpustakaan Digital</h3>
          <p>Akses koleksi buku dan materi pelatihan secara online.</p>
          <button onclick="location.href='perpus.html'">Buka</button>
        </div>
        <div class="card">
          <i class="fa-solid fa-gamepad icon"></i>
          <h3>Quiz PMR</h3>
          <p>Uji pengetahuanmu tentang pertolongan pertama dan PMR.</p>
          <button onclick="location.href=''">Mulai</button>
      </div>
    </main>
  </div>
</body>
<script>
  const menuToggle = document.querySelector(".menu-toggle");
  const sidebar = document.querySelector(".sidebar");

  menuToggle.addEventListener("click", () => {
    sidebar.classList.toggle("active");
  });
  
  // Cek parameter URL untuk login success
  const url = new URLSearchParams(window.location.search);
  
  // Tampilkan notifikasi jika login berhasil
  document.addEventListener('DOMContentLoaded', function() {
    const notification = document.getElementById('loginNotification');
    
    // Tampilkan notifikasi setelah halaman dimuat
    setTimeout(() => {
      notification.style.display = 'flex';
      notification.style.transform = 'translateX(0)';
      
      // Sembunyikan notifikasi setelah 3 detik
      setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(120%)';
        setTimeout(() => {
          notification.style.display = 'none';
        }, 500);
      }, 3000);
    }, 500);
    
    // Juga cek parameter URL untuk login berhasil
    if (url.get('login') === 'success' && url.get('type') === 'anggota') {
      console.log("Berhasil login sebagai Anggota!");
    }
  });
</script>

</html>