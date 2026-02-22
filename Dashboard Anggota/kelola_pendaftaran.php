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

// Proses Tambah/Edit Pertanyaan (via AJAX Internal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    if ($action == 'save_question') {
        $id = intval($_POST['id'] ?? 0);
        $text = mysqli_real_escape_string($koneksi, $_POST['text']);
        $type = mysqli_real_escape_string($koneksi, $_POST['type']);
        $opts = mysqli_real_escape_string($koneksi, $_POST['options'] ?? '[]');
        $req = intval($_POST['required'] ?? 1);
        $order = intval($_POST['order'] ?? 99);

        if ($id > 0) {
            $q = "UPDATE form_questions SET question_text='$text', question_type='$type', options='$opts', is_required='$req', ordering='$order' WHERE id='$id'";
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
}

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
  <style>
    /* CSS disamakan dengan halaman admin lainnya */
    :root {
      --primary-color: #d90429; --primary-hover: #ef233c; --bg-color: #f8f9fa;
      --text-color: #333; --text-muted: #6c757d; --border-color: #e9ecef;
      --success-color: #27ae60; --danger-color: #e74c3c; --info-color: #17a2b8;
      --shadow-sm: 0 2px 4px rgba(0,0,0,0.05); --radius: 10px;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', Roboto, sans-serif; background-color: var(--bg-color); color: var(--text-color); }
    header { background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); position: fixed; width: 100%; z-index: 1000; }
    .navbar { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; }
    .logo { display: flex; align-items: center; gap: 10px; font-weight: bold; color: #000; font-size: 18px; }
    .logo img { height: 40px; }
    .menu-toggle { display: none; background: none; border: none; font-size: 22px; cursor: pointer; color: var(--primary-color); }
    .dashboard-container { display: flex; min-height: 100vh; padding-top: 70px; }
    .sidebar { width: 250px; background: #fff; border-right: 1px solid var(--border-color); height: calc(100vh - 70px); position: sticky; top: 70px; overflow-y: auto; }
    .sidebar ul { list-style: none; }
    .sidebar li { padding: 14px 25px; cursor: pointer; color: var(--text-color); display: flex; align-items: center; gap: 12px; font-weight: 500; border-left: 4px solid transparent; transition: 0.3s; }
    .sidebar li:hover, .sidebar li.active { background-color: #fff0f3; color: var(--primary-color); border-left-color: var(--primary-color); }
    .sidebar a { text-decoration: none; color: inherit; display: flex; align-items: center; gap: 10px; width: 100%; }
    .main-content { flex: 1; padding: 30px; width: calc(100% - 250px); }
    .page-header { margin-bottom: 25px; }
    .page-header h1 { font-size: 1.5rem; color: var(--primary-color); margin-bottom: 5px; }
    .page-header p { color: var(--text-muted); font-size: 0.9rem; }
    
    /* Tabs */
    .tabs { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid var(--border-color); padding-bottom: 10px; }
    .tab-btn { padding: 10px 20px; border: none; background: none; cursor: pointer; font-weight: 600; color: var(--text-muted); border-bottom: 3px solid transparent; margin-bottom: -12px; }
    .tab-btn.active { color: var(--primary-color); border-bottom-color: var(--primary-color); }

    /* Cards */
    .card { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); padding: 20px; margin-bottom: 20px; border: 1px solid var(--border-color); }
    .btn { padding: 8px 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem; color: white; display: inline-flex; align-items: center; gap: 5px; }
    .btn-primary { background-color: var(--primary-color); }
    .btn-success { background-color: var(--success-color); }
    .btn-danger { background-color: var(--danger-color); }
    
    /* Table */
    .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .data-table th, .data-table td { padding: 12px; text-align: left; border-bottom: 1px solid var(--border-color); }
    .data-table th { background-color: #f8f9fa; font-weight: 600; }
    
    /* Form Builder Item */
    .question-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #fff; border: 1px solid var(--border-color); border-radius: 8px; margin-bottom: 10px; }
    .question-item:hover { border-color: var(--primary-color); }
    .q-info h4 { margin-bottom: 5px; }
    .q-info small { color: var(--text-muted); }

    /* Modal */
    .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; align-items: center; justify-content: center; }
    .modal-content { background: white; padding: 25px; border-radius: var(--radius); width: 90%; max-width: 500px; }
    .form-group { margin-bottom: 15px; }
    .form-group label { display: block; margin-bottom: 5px; font-weight: 600; }
    .form-control { width: 100%; padding: 8px; border: 1px solid var(--border-color); border-radius: 6px; }

    @media (max-width: 992px) {
      .main-content { width: 100%; padding: 20px; }
      .sidebar { position: fixed; top: 70px; left: -260px; }
      .sidebar.active { left: 0; }
      .menu-toggle { display: block; }
    }
  </style>
</head>
<body>

  <header>
    <nav class="navbar">
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
        <li><a href="anggota.php"><i class="fa-solid fa-house"></i> Dashboard</a></li>
        <li><a href="kelolaabsen.php"><i class="fa-solid fa-calendar-check"></i> Kelola Absensi</a></li>
        <li><a href="kelolaperpus.php"><i class="fa-solid fa-book"></i> Kelola Perpustakaan</a></li>
        <li class="active"><a href="kelola_pendaftaran.php"><i class="fa-solid fa-users"></i> Kelola Pendaftaran</a></li>
        <li style="margin-top: 20px; border-top: 1px solid #eee;">
            <a href="javascript:void(0)" onclick="if(confirm('Yakin?')) location.href='../logout.php'">
                <i class="fa-solid fa-right-from-bracket"></i> Log Out
            </a>
        </li>
      </ul>
    </aside>

    <main class="main-content">
      <div class="page-header">
        <h1>Kelola Pendaftaran Anggota</h1>
        <p>Atur formulir pendaftaran dan lihat data pendaftar baru.</p>
      </div>

      <!-- Tabs -->
      <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('builder')">Struktur Formulir</button>
        <button class="tab-btn" onclick="switchTab('list')">Data Pendaftar</button>
      </div>

      <!-- Tab 1: Form Builder -->
      <section id="tab-builder">
        <div class="card">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3>Pertanyaan Formulir</h3>
            <button class="btn btn-primary" onclick="openModal()">
              <i class="fas fa-plus"></i> Tambah Pertanyaan
            </button>
          </div>
          <div id="questionsList">
            <!-- List pertanyaan dimuat di sini -->
            <?php
            if(mysqli_num_rows($questions) > 0) {
                while($q = mysqli_fetch_assoc($questions)) {
                    $req = $q['is_required'] ? '<span style="color:var(--danger-color)">*</span>' : '';
                    $type_label = ucfirst($q['question_type']);
                    echo "<div class='question-item' data-id='{$q['id']}'>
                            <div class='q-info'>
                                <h4>{$q['question_text']} {$req}</h4>
                                <small>Tipe: {$type_label} | Urutan: {$q['ordering']}</small>
                            </div>
                            <div class='q-actions'>
                                <button class='btn btn-success' style='padding:5px 10px' onclick='editQ({$q['id']}, \"{$q['question_text']}\", \"{$q['question_type']}\", \"{$q['options']}\", {$q['is_required']}, {$q['ordering']})'><i class='fas fa-pen'></i></button>
                                <button class='btn btn-danger' style='padding:5px 10px' onclick='deleteQ({$q['id']})'><i class='fas fa-trash'></i></button>
                            </div>
                          </div>";
                }
            } else {
                echo "<p style='color:var(--text-muted); text-align:center'>Belum ada pertanyaan. Silakan tambah pertanyaan baru.</p>";
            }
            ?>
          </div>
        </div>
      </section>

      <!-- Tab 2: Data Pendaftar -->
      <section id="tab-list" style="display: none;">
        <div class="card">
          <h3 style="margin-bottom: 15px;">Daftar Pendaftar Baru</h3>
          <div style="overflow-x: auto;">
            <table class="data-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Nama</th>
                  <th>Kelas</th>
                  <th>Jurusan</th>
                  <th>No. WA</th>
                  <th>Tanggal</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no=1;
                while($p = mysqli_fetch_assoc($pendaftar)) {
                    echo "<tr>
                            <td>{$no}</td>
                            <td>{$p['nama_lengkap']}</td>
                            <td>{$p['kelas']}</td>
                            <td>{$p['jurusan']}</td>
                            <td>{$p['no_whatsapp']}</td>
                            <td>".date('d M Y', strtotime($p['submission_date']))."</td>
                            <td>
                                <button class='btn btn-primary' style='padding:5px 10px' onclick='viewDetail({$p['id']})'><i class='fas fa-eye'></i></button>
                            </td>
                          </tr>";
                    $no++;
                }
                if(mysqli_num_rows($pendaftar) == 0) {
                    echo "<tr><td colspan='7' style='text-align:center; color:var(--text-muted)'>Belum ada pendaftar.</td></tr>";
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
      <h3 style="margin-bottom: 20px;">Form Pertanyaan</h3>
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
          </select>
        </div>
        <div class="form-group" id="opts-group" style="display:none;">
          <label>Pilihan (Pisahkan dengan enter)</label>
          <textarea id="q_opts" class="form-control" rows="3" placeholder="Opsi 1&#10;Opsi 2&#10;Opsi 3"></textarea>
        </div>
        <div class="form-group">
          <label>Urutan</label>
          <input type="number" id="q_order" class="form-control" value="1">
        </div>
        <div class="form-group">
          <label> <input type="checkbox" id="q_req" checked> Wajib Diisi</label>
        </div>
        <div style="text-align: right; margin-top: 20px;">
          <button type="button" class="btn btn-danger" onclick="closeModal()">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Detail Pendaftar -->
  <div class="modal" id="detailModal">
    <div class="modal-content" style="max-width: 600px;">
      <h3 style="margin-bottom: 20px;">Detail Pendaftar</h3>
      <div id="detailContent">Loading...</div>
      <div style="text-align: right; margin-top: 20px;">
        <button class="btn btn-danger" onclick="document.getElementById('detailModal').style.display='none'">Tutup</button>
      </div>
    </div>
  </div>

  <script>
    // Sidebar & Toggle
    document.querySelector('.menu-toggle').onclick = () => document.querySelector('.sidebar').classList.toggle('active');
    
    // Tabs
    function switchTab(tab) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        event.target.classList.add('active');
        document.getElementById('tab-builder').style.display = tab === 'builder' ? 'block' : 'none';
        document.getElementById('tab-list').style.display = tab === 'list' ? 'block' : 'none';
    }

    // Modal Question
    const modal = document.getElementById('questionModal');
    function openModal() { document.getElementById('formQuestion').reset(); document.getElementById('q_id').value = 0; toggleOptions(); modal.style.display = 'flex'; }
    function closeModal() { modal.style.display = 'none'; }

    function toggleOptions() {
        const type = document.getElementById('q_type').value;
        document.getElementById('opts-group').style.display = (type === 'select' || type === 'radio') ? 'block' : 'none';
    }

    function editQ(id, text, type, opts, req, order) {
        document.getElementById('q_id').value = id;
        document.getElementById('q_text').value = text;
        document.getElementById('q_type').value = type;
        document.getElementById('q_order').value = order;
        document.getElementById('q_req').checked = req == 1;
        
        // Parse options
        try {
            const arr = JSON.parse(opts);
            document.getElementById('q_opts').value = arr.join('\n');
        } catch(e) { document.getElementById('q_opts').value = ''; }
        
        toggleOptions();
        modal.style.display = 'flex';
    }

    // Save Question
    document.getElementById('formQuestion').onsubmit = function(e) {
        e.preventDefault();
        
        // Build JSON for options
        let opts = [];
        const type = document.getElementById('q_type').value;
        if(type === 'select' || type === 'radio') {
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
        data.append('order', document.getElementById('q_order').value);

        fetch('', { method: 'POST', body: data })
        .then(res => res.json())
        .then(res => {
            if(res.status === 'success') location.reload();
            else alert('Gagal menyimpan');
        });
    };

    // Delete Question
    function deleteQ(id) {
        if(!confirm('Hapus pertanyaan ini?')) return;
        const data = new FormData();
        data.append('action', 'delete_question');
        data.append('id', id);
        fetch('', { method: 'POST', body: data }).then(res => res.json()).then(res => { if(res.status==='success') location.reload(); });
    }

    // View Detail
    function viewDetail(id) {
        const modalD = document.getElementById('detailModal');
        const content = document.getElementById('detailContent');
        content.innerHTML = 'Loading...';
        modalD.style.display = 'flex';
        
        // Fetch detail via AJAX (simplified: using GET parameter, but better use separate PHP file)
        // Here we just simulate by fetching row data again or passing JSON in onclick
        // For simplicity in this code block, we assume we fetch from backend:
        fetch('get_pendaftar_detail.php?id=' + id)
        .then(res => res.text())
        .then(html => { content.innerHTML = html; });
    }
  </script>
</body>
</html>