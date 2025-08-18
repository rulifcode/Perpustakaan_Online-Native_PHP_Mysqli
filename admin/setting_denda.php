<?php
session_start();
include '../config/config.php';

// Cek login admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Proses update setting denda
if (isset($_POST['update_setting'])) {
    $hari_batas = mysqli_real_escape_string($conn, $_POST['hari_batas']);
    $nominal_perhari = mysqli_real_escape_string($conn, $_POST['nominal_perhari']);
    $hari_batas_pengajar = mysqli_real_escape_string($conn, $_POST['hari_batas_pengajar']);
    $nominal_per_buku_pengajar = mysqli_real_escape_string($conn, $_POST['nominal_per_buku_pengajar']);

    // Validasi input
    if ($hari_batas < 0 || $nominal_perhari < 0 || $hari_batas_pengajar < 0 || $nominal_per_buku_pengajar < 0) {
        echo "<script>alert('Nilai tidak boleh negatif!'); location.href='setting_denda.php';</script>";
        exit;
    }

    // Cek apakah data sudah ada
    $cek_data = mysqli_query($conn, "SELECT * FROM setting_denda LIMIT 1");
    
    if (mysqli_num_rows($cek_data) > 0) {
        // Update data yang sudah ada
        $update = mysqli_query($conn, "UPDATE setting_denda SET 
            hari_batas = '$hari_batas',
            nominal_perhari = '$nominal_perhari',
            hari_batas_pengajar = '$hari_batas_pengajar',
            nominal_per_buku_pengajar = '$nominal_per_buku_pengajar'
        ");
    } else {
        // Insert data baru
        $update = mysqli_query($conn, "INSERT INTO setting_denda 
            (hari_batas, nominal_perhari, hari_batas_pengajar, nominal_per_buku_pengajar) 
            VALUES ('$hari_batas', '$nominal_perhari', '$hari_batas_pengajar', '$nominal_per_buku_pengajar')
        ");
    }

    if ($update) {
        echo "<script>alert('Setting denda berhasil diperbarui!'); location.href='setting_denda.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui setting denda!'); location.href='setting_denda.php';</script>";
    }
    exit;
}

// Ambil data setting denda yang ada
$setting_query = mysqli_query($conn, "SELECT * FROM setting_denda LIMIT 1");
$setting = mysqli_fetch_assoc($setting_query);

// Default values jika belum ada data
$default_values = [
    'hari_batas' => 7,
    'nominal_perhari' => 1000,
    'hari_batas_pengajar' => 14,
    'nominal_per_buku_pengajar' => 5000
];

if (!$setting) {
    $setting = $default_values;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Setting Denda - Litera</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <link rel="stylesheet" href="../assets/css/admin1.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="wrapper">


        <div class="main-content">
            <div class="page-header">
                <h2 class="dashboard-title">
                    <i class="fas fa-cog"></i> Setting Denda Perpustakaan
                </h2>
            </div>

            <div class="container">
                <!-- Card Setting Denda -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-money-bill-wave"></i> Pengaturan Denda</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="setting-form">
                            <!-- Setting untuk Pelajar/Umum -->
                            <div class="setting-section">
                                <h4 class="section-title">
                                    <i class="fas fa-graduation-cap"></i> 
                                    Pengaturan Denda untuk Pelajar/Umum
                                </h4>
                                
                                <div class="form-row">
                                    <div class="input-group">
                                        <label for="hari_batas">
                                            <i class="fas fa-calendar-day"></i> 
                                            Batas Hari Keterlambatan:
                                        </label>
                                        <input type="number" 
                                               name="hari_batas" 
                                               id="hari_batas"
                                               value="<?= $setting['hari_batas'] ?>" 
                                               min="0" 
                                               max="365"
                                               class="form-control" 
                                               required>
                                        <small class="form-help">Jumlah hari toleransi sebelum dikenakan denda</small>
                                    </div>

                                    <div class="input-group">
                                        <label for="nominal_perhari">
                                            <i class="fas fa-coins"></i> 
                                            Denda per Hari (Rp):
                                        </label>
                                        <input type="number" 
                                               name="nominal_perhari" 
                                               id="nominal_perhari"
                                               value="<?= $setting['nominal_perhari'] ?>" 
                                               min="0" 
                                               max="100000"
                                               step="500"
                                               class="form-control" 
                                               required>
                                        <small class="form-help">Nominal denda yang dikenakan per hari keterlambatan</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Setting untuk Pengajar -->
                            <div class="setting-section">
                                <h4 class="section-title">
                                    <i class="fas fa-chalkboard-teacher"></i> 
                                    Pengaturan Denda untuk Pengajar
                                </h4>
                                
                                <div class="form-row">
                                    <div class="input-group">
                                        <label for="hari_batas_pengajar">
                                            <i class="fas fa-calendar-day"></i> 
                                            Batas Hari Keterlambatan:
                                        </label>
                                        <input type="number" 
                                               name="hari_batas_pengajar" 
                                               id="hari_batas_pengajar"
                                               value="<?= $setting['hari_batas_pengajar'] ?>" 
                                               min="0" 
                                               max="365"
                                               class="form-control" 
                                               required>
                                        <small class="form-help">Jumlah hari toleransi sebelum dikenakan denda</small>
                                    </div>

                                    <div class="input-group">
                                        <label for="nominal_per_buku_pengajar">
                                            <i class="fas fa-coins"></i> 
                                            Denda per Buku per Hari (Rp):
                                        </label>
                                        <input type="number" 
                                               name="nominal_per_buku_pengajar" 
                                               id="nominal_per_buku_pengajar"
                                               value="<?= $setting['nominal_per_buku_pengajar'] ?>" 
                                               min="0" 
                                               max="100000"
                                               step="1000"
                                               class="form-control" 
                                               required>
                                        <small class="form-help">Nominal denda per buku per hari keterlambatan</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="update_setting" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan Pengaturan
                                </button>
                                <button type="reset" class="btn btn-secondary" onclick="resetForm()">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Card Preview Denda -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3><i class="fas fa-calculator"></i> Preview Perhitungan Denda</h3>
                    </div>

                <!-- Card Informasi -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3><i class="fas fa-info-circle"></i> Informasi Penting</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-content">
                            <div class="info-item">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                <div>
                                    <strong>Perhatian:</strong>
                                    <p>Perubahan setting denda akan berlaku untuk semua pengembalian yang diproses setelah pengaturan ini disimpan.</p>
                                </div>
                            </div>
                            
                            <div class="info-item">
                                <i class="fas fa-calculator text-info"></i>
                                <div>
                                    <strong>Cara Perhitungan:</strong>
                                    <ul>
                                        <li><strong>Pelajar/Umum:</strong> Denda = Jumlah hari terlambat × Nominal per hari</li>
                                        <li><strong>Pengajar:</strong> Denda = Jumlah hari terlambat × Nominal per buku × Jumlah buku</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="info-item">
                                <i class="fas fa-lightbulb text-success"></i>
                                <div>
                                    <strong>Rekomendasi:</strong>
                                    <p>Gunakan nominal denda yang wajar dan sesuai dengan kemampuan pengguna perpustakaan.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        &copy; <?= date('Y') ?> Perpustakaan - Litera
    </footer>

    <script>
        // Function untuk reset form ke nilai default
        function resetForm() {
            if (confirm('Reset pengaturan ke nilai default?')) {
                document.getElementById('hari_batas').value = <?= $default_values['hari_batas'] ?>;
                document.getElementById('nominal_perhari').value = <?= $default_values['nominal_perhari'] ?>;
                document.getElementById('hari_batas_pengajar').value = <?= $default_values['hari_batas_pengajar'] ?>;
                document.getElementById('nominal_per_buku_pengajar').value = <?= $default_values['nominal_per_buku_pengajar'] ?>;
            }
        }

        // Format number input
        function formatNumber(input) {
            let value = input.value.replace(/\D/g, '');
            input.value = value;
        }

        // Add event listeners for number formatting
        document.addEventListener('DOMContentLoaded', function() {
            const numberInputs = document.querySelectorAll('input[type="number"]');
            numberInputs.forEach(function(input) {
                input.addEventListener('input', function() {
                    formatNumber(this);
                });
            });
        });

        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);

        // Form validation
        document.querySelector('.setting-form').addEventListener('submit', function(e) {
            const inputs = this.querySelectorAll('input[type="number"]');
            let hasError = false;
            
            inputs.forEach(function(input) {
                if (parseInt(input.value) < 0) {
                    alert('Nilai ' + input.previousElementSibling.textContent + ' tidak boleh negatif!');
                    input.focus();
                    hasError = true;
                    return false;
                }
            });
            
            if (hasError) {
                e.preventDefault();
            }
        });
    </script>

    <style>
        .setting-form .setting-section {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .setting-form .section-title {
            color: #495057;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dee2e6;
            font-size: 1.1em;
        }

        .setting-form .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .setting-form .form-help {
            color: #6c757d;
            font-size: 0.875em;
            margin-top: 5px;
            display: block;
        }

        .form-actions {
            text-align: center;
            padding: 20px 0;
            border-top: 1px solid #dee2e6;
            margin-top: 20px;
        }

        .form-actions .btn {
            margin: 0 10px;
            min-width: 150px;
        }

        .preview-section {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
        }

        .preview-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .preview-item {
            background: white;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #dee2e6;
            text-align: center;
        }

        .denda-amount {
            color: #dc3545;
            font-weight: bold;
            font-size: 1.1em;
        }

        .info-content {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #17a2b8;
        }

        .info-item i {
            font-size: 1.5em;
            margin-top: 5px;
        }

        .info-item ul {
            margin: 10px 0 0 20px;
        }

        .info-item p, .info-item ul {
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .setting-form .form-row {
                grid-template-columns: 1fr;
            }
            
            .preview-content {
                grid-template-columns: 1fr;
            }
            
            .form-actions .btn {
                display: block;
                margin: 10px auto;
                width: 100%;
                max-width: 200px;
            }
        }
        
    </style>
       <script>
         const hamburger = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function toggleSidebar() {
            hamburger.classList.toggle('active');
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            
            // Prevent body scroll when sidebar is open
            if (sidebar.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        function closeSidebar() {
            hamburger.classList.remove('active');
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Event listeners
        hamburger.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', closeSidebar);

        // Close sidebar when clicking on menu items (mobile)
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });

        // Prevent scroll on touch devices when sidebar is open
        let touchStartY = 0;
        
        document.addEventListener('touchstart', (e) => {
            touchStartY = e.touches[0].clientY;
        });

        document.addEventListener('touchmove', (e) => {
            if (sidebar.classList.contains('active') && !sidebar.contains(e.target)) {
                const touchY = e.touches[0].clientY;
                const touchDelta = touchStartY - touchY;
                
                // Prevent scroll if not scrolling within sidebar
                if (Math.abs(touchDelta) > 5) {
                    e.preventDefault();
                }
            }
        }, { passive: false });
    </script>
</body>
</html>