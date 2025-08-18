<?php
session_start();
include '../config/config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Data Ringkasan
$total_user       = $conn->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'")->fetch_assoc()['total'];
$total_buku       = $conn->query("SELECT COUNT(*) as total FROM buku")->fetch_assoc()['total'];
$total_dipinjam   = $conn->query("SELECT COUNT(*) as total FROM pengajuan_buku WHERE status = 'disetujui'")->fetch_assoc()['total'];
$total_pengajuan  = $conn->query("SELECT COUNT(*) as total FROM pengajuan_buku WHERE status = 'pending'")->fetch_assoc()['total'];

// Statistik kategori user
$kategori_umum     = $conn->query("SELECT COUNT(*) as total FROM users WHERE kategori = 'umum'")->fetch_assoc()['total'];
$kategori_pelajar  = $conn->query("SELECT COUNT(*) as total FROM users WHERE kategori = 'pelajar'")->fetch_assoc()['total'];
$kategori_pengajar = $conn->query("SELECT COUNT(*) as total FROM users WHERE kategori = 'pengajar'")->fetch_assoc()['total'];

// Statistik peminjaman 7 hari terakhir
$pinjam_data = [];
$labels = [];
for ($i = 6; $i >= 0; $i--) {
    $tanggal = date("Y-m-d", strtotime("-$i days"));
    $label = date("D", strtotime("-$i days"));
    $labels[] = $label;

    $count = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE tanggal_pinjam = '$tanggal'")->fetch_assoc()['total'];
    $pinjam_data[] = $count;
}

// Buku terpopuler (paling sering dipinjam)
$buku_populer = $conn->query("
    SELECT b.judul, b.gambar, COUNT(p.id_buku) as jumlah
    FROM peminjaman p
    JOIN buku b ON p.id_buku = b.id_buku
    GROUP BY p.id_buku
    ORDER BY jumlah DESC
    LIMIT 1
")->fetch_assoc();

$judul_buku = $buku_populer['judul'] ?? 'Belum ada data';
$gambar_buku = $buku_populer['gambar'] ?? 'default.jpg';
$total_like = rand(100, 200);  // Dummy
$total_komen = rand(50, 100);  // Dummy
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title>Dashboard Admin</title>
  <link rel="stylesheet" href="../assets/css/admin1.css">
  <link rel="stylesheet" href="../assets/css/sidebar.css">
  <link rel="stylesheet" href="../assets/js/header_admin.js">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<div class="main-content">
   
  <div class="dashboard-header"></div> <h2 class="dashboard-title">Dashboard Admin</h2>

  <div class="summary-cards">
    <div class="card small"><h4>Total Buku</h4><p><?= $total_buku ?></p></div>
    <div class="card small"><h4>Total Pengguna</h4><p><?= $total_user ?></p></div>
    <div class="card small"><h4>Dipinjam</h4><p><?= $total_dipinjam ?></p></div>
    <div class="card small"><h4>Pengajuan</h4><p><?= $total_pengajuan ?></p></div>
  </div>

  <div class="dashboard-charts">
    <div class="card large">
      <h4>Statistik Kategori Pengguna</h4>
      <canvas id="donutChart"></canvas>
    </div>
    <div class="card large">
      <h4>Aktivitas Peminjaman Mingguan</h4>
      <canvas id="lineChart"></canvas>
    </div>
  </div>

  <div class="popular-post">
    <div class="card">
      <h4>Buku Terpopuler</h4>
      <img src="../uploads/<?= $gambar_buku ?>" alt="Buku Populer" style="max-width:100%">
      <p><strong>Judul:</strong> <?= $judul_buku ?></p>
    </div>
  </div>
</div>

<script>
new Chart(document.getElementById('donutChart'), {
  type: 'doughnut',
  data: {
    labels: ['Umum', 'Pelajar', 'Pengajar'],
    datasets: [{
      data: [<?= $kategori_umum ?>, <?= $kategori_pelajar ?>, <?= $kategori_pengajar ?>],
      backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc']
    }]
  },
  options: { plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('lineChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($labels) ?>,
    datasets: [{
      label: 'Jumlah Peminjaman',
      data: <?= json_encode($pinjam_data) ?>,
      fill: true,
      borderColor: '#4e73df',
      backgroundColor: 'rgba(78, 115, 223, 0.1)',
      tension: 0.4
    }]
  },
  options: {
    responsive: true,
    scales: { y: { beginAtZero: true } }
  }
});

//responsive navbar
        document.addEventListener('DOMContentLoaded', function() {
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const body = document.body;
              const sidebarLinks = document.querySelectorAll('.sidebar a');

            // Toggle sidebar
            function toggleSidebar() {
                const isActive = sidebar.classList.contains('active');
                
                if (isActive) {
                    closeSidebar();
                } else {
                    openSidebar();
                }
            }

            // Open sidebar
            function openSidebar() {
                sidebar.classList.add('active');
                sidebarOverlay.classList.add('active');
                hamburgerBtn.classList.add('active');
                body.classList.add('sidebar-open');
            }

            // Close sidebar
            function closeSidebar() {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                hamburgerBtn.classList.remove('active');
                body.classList.remove('sidebar-open');
            }

            // Event listeners
            hamburgerBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleSidebar();
            });

            // Close sidebar when clicking overlay
            sidebarOverlay.addEventListener('click', function(e) {
                e.preventDefault();
                closeSidebar();
            });

            // Close sidebar when clicking outside (mobile)
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target) && !hamburgerBtn.contains(e.target)) {
                        if (sidebar.classList.contains('active')) {
                            closeSidebar();
                        }
                    }
                }
            });

            // Handle sidebar link clicks
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Prevent default only if it's a demo link (#)
                    if (this.getAttribute('href').startsWith('#')) {
                        e.preventDefault();
                    }

                    // Remove active class from all links
                    sidebarLinks.forEach(l => l.classList.remove('active'));
                    
                    // Add active class to clicked link
                    this.classList.add('active');

                    // Close sidebar on mobile after clicking a link
                    if (window.innerWidth <= 768) {
                        setTimeout(() => {
                            closeSidebar();
                        }, 200);
                    }

                    // Here you would normally handle navigation
                    console.log('Navigating to:', this.getAttribute('href'));
                });
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    // Desktop mode - close mobile sidebar
                    closeSidebar();
                }
            });

            // Prevent sidebar from closing when clicking inside it
            sidebar.addEventListener('click', function(e) {
                e.stopPropagation();
            });

            // Handle escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                    closeSidebar();
                }
            });

            // Prevent body scroll when sidebar is open on mobile
            function preventBodyScroll(e) {
                if (sidebar.classList.contains('active') && window.innerWidth <= 768) {
                    if (!sidebar.contains(e.target)) {
                        e.preventDefault();
                    }
                }
            }

            // Add touch events for better mobile experience
            let touchStartX = 0;
            let touchEndX = 0;

            document.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            });

            document.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            });

            function handleSwipe() {
                if (window.innerWidth <= 768) {
                    const swipeDistance = touchEndX - touchStartX;
                    
                    // Swipe right to open (from left edge)
                    if (swipeDistance > 100 && touchStartX < 50 && !sidebar.classList.contains('active')) {
                        openSidebar();
                    }
                    
                    // Swipe left to close
                    if (swipeDistance < -100 && sidebar.classList.contains('active')) {
                        closeSidebar();
                    }
                }
            }
        });
    </script>
</script>

<footer>
  &copy; <?= date('Y') ?> Perpustakaan - Litera
</footer>
</body>
  <script src="../assets/js/header_admin.js"></script>
</html>
