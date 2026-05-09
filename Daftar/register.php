<?php
// Wajib ditaruh paling atas biar bisa baca memori server (Session)
session_start();
require_once __DIR__ . '/../koneksi.php';

// Hitung sisa waktu cooldown dari session
$sisa_waktu = 0;
if (isset($_SESSION['last_submit'])) {
    $selisih = time() - $_SESSION['last_submit'];
    if ($selisih < 60) {
        $sisa_waktu = 60 - $selisih;
    }
}

// Ambil pertanyaan dinamis dari database
$questions = mysqli_query($koneksi, "SELECT * FROM form_questions ORDER BY ordering ASC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Anggota | PMR Millenium</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --primary-color: #d90429;
            --primary-hover: #c92a2a;
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius: 16px;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            --input-radius: 10px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            line-height: 1.6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .reg-card {
            background-color: var(--card-bg);
            width: 100%;
            max-width: 600px;
            padding: 40px 30px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            text-align: center;
            position: relative;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo { margin-bottom: 20px; display: flex; justify-content: center; }
        .logo img { width: 70px; height: 70px; }

        h2 { font-size: 1.5rem; margin-bottom: 8px; font-weight: 700; color: var(--text-main); }
        .subtitle { font-size: 0.9rem; color: var(--text-muted); margin-bottom: 30px; }

        .form-group { margin-bottom: 20px; text-align: left; }
        label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-main); margin-bottom: 8px; margin-left: 2px; }
        .required { color: var(--primary-color); }

        input, select, textarea {
            width: 100%; padding: 12px 14px; border: 1px solid var(--border-color);
            border-radius: var(--input-radius); font-size: 0.95rem; color: var(--text-main);
            background-color: #fff; transition: all 0.3s ease; font-family: 'Inter', sans-serif;
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--primary-color); outline: none; box-shadow: 0 0 0 3px rgba(226, 56, 56, 0.1);
        }
        textarea { resize: vertical; min-height: 100px; }

        .btn-submit {
            display: block; width: 100%; padding: 14px; border-radius: var(--input-radius);
            font-weight: 600; font-size: 1rem; text-decoration: none; border: none;
            cursor: pointer; transition: all 0.2s ease; color: #fff;
            background-color: var(--primary-color); box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); margin-top: 10px;
        }
        .btn-submit:hover:not(:disabled) { background-color: var(--primary-hover); transform: translateY(-2px); box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15); }
        .btn-submit:disabled { background-color: #94a3b8; cursor: not-allowed; transform: none; box-shadow: none; }

        .btn-back {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            width: 100%; padding: 12px; margin-top: 20px; font-size: 0.9rem; font-weight: 500;
            color: var(--text-muted); text-decoration: none; border-radius: var(--input-radius);
            transition: all 0.2s; background-color: transparent;
        }
        .btn-back:hover { color: var(--primary-color); background-color: #f1f5f9; }

        .input-error { border-color: #dc2626 !important; background-color: #fef2f2 !important; }
        .error-msg { color: #dc2626; font-size: 0.75rem; margin-top: 5px; display: none; }

        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5); display: none; justify-content: center;
            align-items: center; z-index: 2000; padding: 20px;
        }
        .modal-overlay.active { display: flex; }
        .modal-content {
            background: white; padding: 30px; border-radius: var(--radius);
            text-align: center; max-width: 400px; width: 100%;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes popIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }

        .success-icon {
            width: 70px; height: 70px; background: #dcfce7; color: #16a34a;
            border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center;
            justify-content: center; font-size: 40px;
        }
        .modal-btn { background: var(--primary-color); color: white; border: none; padding: 10px 25px; border-radius: 8px; cursor: pointer; font-weight: 600; margin-top: 15px; }

        .custom-file-upload { position: relative; width: 100%; margin-top: 5px; }
        .custom-file-upload input[type="file"] { position: absolute; left: 0; top: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
        .file-upload-dummy { border: 2px dashed var(--border-color); border-radius: var(--input-radius); padding: 20px; text-align: center; background-color: #f8fafc; transition: all 0.3s ease; color: var(--text-muted); cursor: pointer; }
        .custom-file-upload input[type="file"]:hover+.file-upload-dummy { border-color: var(--primary-color); background-color: #fff; }
        .file-upload-dummy i { font-size: 24px; margin-bottom: 8px; display: block; color: var(--primary-color); }
        .file-upload-dummy p { font-size: 0.85rem; margin: 0; line-height: 1.4; }
        .file-name { display: block; margin-top: 8px; font-weight: 600; color: var(--primary-color); font-size: 0.9rem; word-break: break-all; }
        .file-selected .file-upload-dummy { border-color: #16a34a; background-color: #f0fdf4; }
        .file-selected .file-upload-dummy i { color: #16a34a; }
    </style>
</head>

<body>

    <div class="reg-card">
        <div class="logo">
            <img src="../Gambar/logpmi.png" alt="Logo PMR" onerror="this.style.display='none'">
        </div>

        <h2>Pendaftaran Anggota</h2>
        <p class="subtitle">SMKN 1 CIBINONG - PMR MILLENIUM</p>

        <form id="pmrForm" method="POST" enctype="multipart/form-data" novalidate>
            <div id="dynamic-questions">
                <?php
                if (mysqli_num_rows($questions) > 0) {
                    while ($q = mysqli_fetch_assoc($questions)) {
                        $req = $q['is_required'] ? 'required' : '';
                        $req_mark = $q['is_required'] ? '<span class="required">*</span>' : '';

                        echo "<div class='form-group'>";
                        echo "<label>{$q['question_text']} {$req_mark}</label>";

                        if ($q['question_type'] == 'text') {
                            echo "<input type='text' name='dyn_{$q['id']}' placeholder='Jawaban Anda' {$req}>";
                        } elseif ($q['question_type'] == 'textarea') {
                            echo "<textarea name='dyn_{$q['id']}' placeholder='Jawaban Anda' {$req}></textarea>";
                        } elseif ($q['question_type'] == 'file') {
                            $fileId = "file_" . $q['id'];
                            echo "<div class='custom-file-upload'>";
                            echo "<input type='file' name='dyn_{$q['id']}' id='{$fileId}' {$req} onchange='showFileName(this)'>";
                            echo "<div class='file-upload-dummy'>";
                            echo "<i class='fa-solid fa-cloud-arrow-up'></i>";
                            echo "<p>Klik atau seret file ke sini</p>";
                            echo "<span class='file-name' id='label_{$q['id']}'>Belum ada file dipilih</span>";
                            echo "</div>";
                            echo "</div>";
                        } elseif ($q['question_type'] == 'select' || $q['question_type'] == 'radio' || $q['question_type'] == 'checkbox') {
                            $opts = json_decode($q['options'], true);
                            
                            if ($q['question_type'] == 'select') {
                                echo "<select name='dyn_{$q['id']}' {$req}>";
                                if ($req) echo "<option value='' disabled selected>Pilih</option>";
                                foreach ($opts as $o) { echo "<option value='$o'>$o</option>"; }
                                echo "</select>";
                            } elseif ($q['question_type'] == 'radio') {
                                echo "<div style='display:flex; flex-direction:column; gap:8px; margin-top:5px;'>";
                                foreach ($opts as $o) {
                                    echo "<label style='display:flex; align-items:center; gap:8px; font-weight:400; cursor:pointer;'>";
                                    echo "<input type='radio' name='dyn_{$q['id']}' value='$o' style='width:auto;' {$req}> $o";
                                    echo "</label>";
                                }
                                echo "</div>";
                            } elseif ($q['question_type'] == 'checkbox') {
                                echo "<div style='display:flex; flex-direction:column; gap:8px; margin-top:5px;'>";
                                foreach ($opts as $o) {
                                    echo "<label style='display:flex; align-items:center; gap:8px; font-weight:400; cursor:pointer;'>";
                                    // PENTING: pakai [] agar dikirim sebagai Array
                                    echo "<input type='checkbox' name='dyn_{$q['id']}[]' value='$o' style='width:auto;'> $o";
                                    echo "</label>";
                                }
                                echo "</div>";
                            }
                        }
                        echo "</div>";
                    }
                } else {
                    echo "<p style='color:var(--text-muted); text-align:center; padding: 20px 0;'>Formulir pendaftaran belum tersedia. Silakan hubungi pengurus.</p>";
                }
                ?>
            </div>

            <?php if (mysqli_num_rows($questions) > 0): ?>
                <button type="submit" class="btn-submit" id="btnSubmit">Kirim Pendaftaran</button>
                <div id="cooldownText" style="display:none; color:#64748b; font-size:0.85rem; margin-top:10px; text-align:center;">
                    Mohon tunggu <strong id="timer" style="color:#d90429;">60</strong> detik untuk mendaftar lagi.
                </div>
            <?php endif; ?>
        </form>

        <a href="../Halaman Utama/index.php" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
        </a>
    </div>

    <div class="modal-overlay" id="successModal">
        <div class="modal-content">
            <div class="success-icon"><i class="fa-solid fa-check"></i></div>
            <h3 style="margin-bottom: 10px; color: var(--text-main);">Pendaftaran Berhasil!</h3>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 15px;">Terima kasih. Data Anda telah kami terima.</p>
            
            <div style="background-color: #fff1f2; border: 1px solid #fecdd3; border-radius: 10px; padding: 15px; text-align: left; margin-bottom: 10px;">
                <p style="font-size: 0.9rem; font-weight: 600; color: #d90429; margin-bottom: 5px;">
                    <i class="fa-brands fa-whatsapp" style="margin-right: 5px;"></i> Langkah Selanjutnya:
                </p>
                <p style="font-size: 0.85rem; color: #1e293b; line-height: 1.5;">
                    Pastikan nomor WhatsApp yang Anda daftarkan aktif. Pengurus akan mengirimkan konfirmasi dan akses login melalui WhatsApp jika Anda diterima.
                </p>
                <p style="font-size: 0.85rem; color: #64748b; margin-top: 10px;">
                    <i class="fa-solid fa-clock" style="margin-right: 5px;"></i> Harap bersabar menunggu proses verifikasi.
                </p>
            </div>
            <button class="modal-btn" onclick="closeModal()">Tutup</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('pmrForm');
            const modal = document.getElementById('successModal');
            const btnSubmit = document.getElementById('btnSubmit');
            const cooldownText = document.getElementById('cooldownText');
            const timerSpan = document.getElementById('timer');

            // Cek apakah ada sisa waktu dari PHP (saat page direfresh)
            let sisaWaktuServer = <?php echo $sisa_waktu; ?>;
            if (sisaWaktuServer > 0) {
                jalankanCooldown(sisaWaktuServer);
            }

            if (!form) return;

            // FUNGSI COOLDOWN
            function jalankanCooldown(waktu) {
                let timeLeft = waktu;
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = "Menunggu Cooldown...";
                cooldownText.style.display = 'block';
                timerSpan.innerText = timeLeft;
                
                const cooldownInterval = setInterval(() => {
                    timeLeft--;
                    timerSpan.innerText = timeLeft;
                    if (timeLeft <= 0) {
                        clearInterval(cooldownInterval);
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = "Kirim Pendaftaran";
                        cooldownText.style.display = 'none';
                    }
                }, 1000);
            }

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                let isValid = true;
                const requiredInputs = form.querySelectorAll('[required]');

                requiredInputs.forEach(input => {
                    if (input.type === 'radio') {
                        const name = input.name;
                        const checked = form.querySelector(`input[name="${name}"]:checked`);
                        if (!checked) {
                            isValid = false;
                            input.parentElement.style.color = 'red';
                        } else {
                            input.parentElement.style.color = 'inherit';
                        }
                    } else {
                        const errorMsg = input.nextElementSibling;
                        input.classList.remove('input-error');
                        if (errorMsg && errorMsg.classList.contains('error-msg')) errorMsg.style.display = 'none';

                        if (!input.value.trim()) {
                            isValid = false;
                            input.classList.add('input-error');
                            if (errorMsg && errorMsg.classList.contains('error-msg')) errorMsg.style.display = 'block';
                        }
                    }
                });

                if (isValid) {
                    btnSubmit.innerHTML = "<i class='fa-solid fa-spinner fa-spin'></i> Memproses...";
                    btnSubmit.disabled = true;

                    const formData = new FormData(form);

                    fetch('proses_register.php', { method: 'POST', body: formData })
                        .then(response => {
                            if (!response.ok) return response.text().then(text => { throw new Error(text) });
                            return response.json();
                        })
                        .then(data => {
                            if (data.status === 'success') {
                                modal.classList.add('active');
                                form.reset();
                                
                                const fileUploads = form.querySelectorAll('.custom-file-upload');
                                fileUploads.forEach(container => {
                                    container.classList.remove('file-selected');
                                    const label = container.querySelector('.file-name');
                                    if (label) label.innerText = 'Belum ada file dipilih';
                                });

                                // MULAI COOLDOWN 60 DETIK
                                jalankanCooldown(60);

                            } else {
                                alert('Terjadi kesalahan: ' + (data.msg || 'Gagal mengirim data.'));
                                btnSubmit.disabled = false;
                                btnSubmit.innerHTML = "Kirim Pendaftaran";
                            }
                        })
                        .catch(error => {
                            console.error('Detail Error:', error.message);
                            alert('Error Detail: ' + error.message);
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = "Kirim Pendaftaran";
                        });
                } else {
                    const firstError = document.querySelector('.input-error');
                    if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        });

        function closeModal() {
            document.getElementById('successModal').classList.remove('active');
        }

        function showFileName(input) {
            const container = input.closest('.custom-file-upload');
            const label = container.querySelector('.file-name');

            if (input.files && input.files.length > 0) {
                const fileName = input.files[0].name;
                label.innerText = fileName;
                container.classList.add('file-selected');
            } else {
                label.innerText = 'Belum ada file dipilih';
                container.classList.remove('file-selected');
            }
        }
    </script>
</body>
</html>