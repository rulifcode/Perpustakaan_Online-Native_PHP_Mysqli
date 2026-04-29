<?php
// Add session_start() at the very beginning
session_start();

require_once '../config/config.php';

// Handle DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    header('Content-Type: application/json');
    $user_id = intval($_POST['user_id']);
    
    // Start transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Delete related records first
        $delete_details = "DELETE FROM detail_pengajuan_buku WHERE id_pengajuan IN (SELECT id_pengajuan FROM pengajuan_buku WHERE id_user = ?)";
        $stmt = mysqli_prepare($conn, $delete_details);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        
        $delete_pengajuan = "DELETE FROM pengajuan_buku WHERE id_user = ?";
        $stmt = mysqli_prepare($conn, $delete_pengajuan);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        
        // Delete user
        $delete_user = "DELETE FROM users WHERE id_user = ?";
        $stmt = mysqli_prepare($conn, $delete_user);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        
        mysqli_commit($conn);
        
        echo json_encode(['success' => true, 'message' => 'User berhasil dihapus']);
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus user: ' . $e->getMessage()]);
        exit;
    }
}

// Handle UPDATE request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    header('Content-Type: application/json');
    $user_id = intval($_POST['user_id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $nomor_telepon = mysqli_real_escape_string($conn, $_POST['nomor_telepon']);
    $asal_institusi = mysqli_real_escape_string($conn, $_POST['asal_institusi']);
    
    // Check if username or email already exists (excluding current user)
    $check_query = "SELECT id_user FROM users WHERE (username = ? OR email = ?) AND id_user != ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "ssi", $username, $email, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['success' => false, 'message' => 'Username atau email sudah digunakan']);
        exit;
    }
    
    $update_query = "UPDATE users SET username = ?, nama = ?, email = ?, alamat = ?, kategori = ?, jenis_kelamin = ?, tanggal_lahir = ?, nomor_telepon = ?, asal_institusi = ? WHERE id_user = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "sssssssssi", $username, $nama, $email, $alamat, $kategori, $jenis_kelamin, $tanggal_lahir, $nomor_telepon, $asal_institusi, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Data user berhasil diperbarui']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data user']);
    }
    exit;
}

// Pagination settings
$records_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $records_per_page;

// Get search and filter parameters with consistent validation
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';
$category_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, trim($_GET['category'])) : '';

// Base query with consistent formatting and structure
$base_query = "
    SELECT 
        u.id_user, 
        u.username, 
        u.nama, 
        u.email, 
        u.alamat, 
        u.created_at, 
        u.kategori, 
        u.jenis_kelamin, 
        u.tanggal_lahir, 
        u.nomor_telepon, 
        u.asal_institusi,
        u.foto_profil, 
        u.identitas,
        COALESCE(pinjaman_stats.total_pinjaman, 0) AS total_pinjaman,
        COALESCE(pinjaman_stats.pinjaman_aktif, 0) AS pinjaman_aktif,
        COALESCE(pinjaman_stats.pinjaman_selesai, 0) AS pinjaman_selesai
    FROM users u
    LEFT JOIN (
        SELECT 
            p.id_user,
            CASE 
                WHEN u_inner.kategori = 'pengajar' THEN 
                    COALESCE(SUM(dp.jumlah), 0)
                ELSE 
                    COUNT(p.id_pengajuan)
            END AS total_pinjaman,
            CASE 
                WHEN u_inner.kategori = 'pengajar' THEN 
                    COALESCE(SUM(CASE WHEN p.status IN ('dipinjam', 'pending') THEN dp.jumlah ELSE 0 END), 0)
                ELSE 
                    SUM(CASE WHEN p.status IN ('dipinjam', 'pending') THEN 1 ELSE 0 END)
            END AS pinjaman_aktif,
            CASE 
                WHEN u_inner.kategori = 'pengajar' THEN 
                    COALESCE(SUM(CASE WHEN p.status = 'dikembalikan' THEN dp.jumlah ELSE 0 END), 0)
                ELSE 
                    SUM(CASE WHEN p.status = 'dikembalikan' THEN 1 ELSE 0 END)
            END AS pinjaman_selesai
        FROM pengajuan_buku p
        JOIN users u_inner ON p.id_user = u_inner.id_user
        LEFT JOIN detail_pengajuan_buku dp ON p.id_pengajuan = dp.id_pengajuan
        GROUP BY p.id_user, u_inner.kategori
    ) pinjaman_stats ON u.id_user = pinjaman_stats.id_user
    WHERE u.role = 'user'
";

// Add search conditions with consistent structure
if (!empty($search)) {
    $base_query .= " AND (
        u.username LIKE '%$search%' OR 
        u.nama LIKE '%$search%' OR
        u.email LIKE '%$search%' OR 
        u.alamat LIKE '%$search%'
    )";
}

// Add category filter with consistent structure
if (!empty($category_filter)) {
    $base_query .= " AND u.kategori = '$category_filter'";
}

// Count query for pagination with consistent structure
$count_query = "
    SELECT COUNT(*) as total 
    FROM users u 
    WHERE u.role = 'user'
";

// Apply same filters to count query
if (!empty($search)) {
    $count_query .= " AND (
        u.username LIKE '%$search%' OR 
        u.nama LIKE '%$search%' OR
        u.email LIKE '%$search%' OR 
        u.alamat LIKE '%$search%'
    )";
}

if (!empty($category_filter)) {
    $count_query .= " AND u.kategori = '$category_filter'";
}

// Execute count query with error handling
$count_result = mysqli_query($conn, $count_query);
if (!$count_result) {
    die("Error in count query: " . mysqli_error($conn));
}

$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Execute main query with pagination
$base_query .= " ORDER BY u.created_at DESC LIMIT $records_per_page OFFSET $offset";
$query = mysqli_query($conn, $base_query);

// Check if main query was successful
if (!$query) {
    die("Error in main query: " . mysqli_error($conn));
}

// Get categories for filter dropdown with error handling
$categories_query = "SELECT DISTINCT kategori FROM users WHERE role = 'user' AND kategori IS NOT NULL ORDER BY kategori";
$categories_result = mysqli_query($conn, $categories_query);
if (!$categories_result) {
    die("Error in categories query: " . mysqli_error($conn));
}

// Helper function for safe output
function safe_output($value, $default = 'N/A') {
    return !empty($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $default;
}

// Helper function for date formatting
function format_date($date, $format = 'd-m-Y H:i') {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return '-';
    }
    
    $timestamp = strtotime($date);
    return $timestamp ? date($format, $timestamp) : '-';
}

// Helper function for number formatting
function format_number($number) {
    return number_format((int)$number);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    <title>Laporan Data User - Perpustakaan Litera</title>
    <link rel="stylesheet" href="../assets/css/admin1.css">
    <link rel="stylesheet" href="../assets/css/userdata.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>

    <?php include 'header.php'; ?>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <div class="card">
                <div class="dashboard-header"></div>
                <h2 class="dashboard-title">Data User</h2>

                <!-- Alert Messages -->
                <div id="alertContainer"></div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="GET" class="filter-form">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label class="filter-label" for="search">Pencarian</label>
                                <input type="text" id="search" name="search" class="filter-input"
                                    placeholder="Cari username, nama, email, atau alamat..."
                                    value="<?= safe_output($search, '') ?>">
                            </div>

                            <div class="filter-group">
                                <label class="filter-label" for="category">Kategori</label>
                                <select name="category" id="category" class="filter-select">
                                    <option value="">Semua Kategori</option>
                                    <?php
                                    if ($categories_result && mysqli_num_rows($categories_result) > 0) {
                                        while ($category = mysqli_fetch_assoc($categories_result)) {
                                            $selected = ($category_filter === $category['kategori']) ? 'selected' : '';
                                            $category_name = safe_output(ucfirst($category['kategori']));
                                            echo "<option value='{$category['kategori']}' $selected>$category_name</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="filter-buttons">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="?" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Table Section -->
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Username</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Kategori</th>
                                    <th>Detail</th>
                                    <th>Edit & Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (mysqli_num_rows($query) > 0) {
                                    $no = $offset + 1;
                                    while ($row = mysqli_fetch_assoc($query)) {
                                        $username = safe_output($row['username']);
                                        $nama = safe_output($row['nama']);
                                        $email = safe_output($row['email']);
                                        $kategori = safe_output(ucfirst($row['kategori']));
                                        $total_pinjaman = format_number($row['total_pinjaman']);
                                        $pinjaman_aktif = format_number($row['pinjaman_aktif']);
                                        
                                        // Create safe JSON data
                                        $user_data = [
                                            'id_user' => $row['id_user'],
                                            'username' => $row['username'],
                                            'nama' => $row['nama'],
                                            'email' => $row['email'],
                                            'alamat' => $row['alamat'],
                                            'kategori' => $row['kategori'],
                                            'jenis_kelamin' => $row['jenis_kelamin'],
                                            'tanggal_lahir' => $row['tanggal_lahir'],
                                            'nomor_telepon' => $row['nomor_telepon'],
                                            'asal_institusi' => $row['asal_institusi'],
                                            'foto_profil' => $row['foto_profil'],
                                            'identitas' => $row['identitas'],
                                            'created_at' => $row['created_at'],
                                            'total_pinjaman' => $row['total_pinjaman'],
                                            'pinjaman_aktif' => $row['pinjaman_aktif'],
                                            'pinjaman_selesai' => $row['pinjaman_selesai']
                                        ];
                                        
                                        $user_json = htmlspecialchars(json_encode($user_data), ENT_QUOTES, 'UTF-8');
                                        
                                        echo "<tr>
                                            <td>{$no}</td>
                                            <td>{$username}</td>
                                            <td>{$nama}</td>
                                            <td>{$email}</td>
                                            <td>{$kategori}</td>
                                            <td>
                                                <button class='btn btn-primary' onclick='showUserDetail({$user_json})'>
                                                    <i class='fas fa-eye'></i> Detail
                                                </button>
                                            </td>
                                            <td>
                                                <div class='action-buttons'>
                                                    <button onclick='editUser({$user_json})' class='btn btn-warning btn-sm' title='Edit User'>
                                                        <i class='fas fa-edit'></i>
                                                    </button>
                                                    <button onclick='confirmDelete({$row['id_user']}, \"{$username}\")' class='btn btn-danger btn-sm' title='Hapus User'>
                                                        <i class='fas fa-trash'></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>";
                                        $no++;
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='no-data'>Tidak ada data user yang ditemukan.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_records > 0) : ?>
                    <div class="pagination-container">
                        <div class="pagination-info">
                            Menampilkan <?= $offset + 1 ?> - <?= min($offset + $records_per_page, $total_records) ?>
                            dari <?= format_number($total_records) ?> data
                        </div>

                        <div class="pagination">
                            <?php
                            // Build URL parameters consistently
                            $url_params = array_filter([
                                'search' => $search,
                                'category' => $category_filter
                            ]);
                            
                            // Previous button
                            if ($current_page > 1) {
                                $prev_params = $url_params;
                                $prev_params['page'] = $current_page - 1;
                                $prev_url = '?' . http_build_query($prev_params);
                                echo "<a href='$prev_url' class='pagination-btn'><i class='fas fa-chevron-left'></i> Previous</a>";
                            } else {
                                echo "<span class='pagination-btn disabled'><i class='fas fa-chevron-left'></i> Previous</span>";
                            }
                            
                            // Page numbers with consistent logic
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++) {
                                $page_params = $url_params;
                                $page_params['page'] = $i;
                                $page_url = '?' . http_build_query($page_params);
                                
                                $class = ($i === $current_page) ? 'pagination-btn current' : 'pagination-btn';
                                echo "<a href='$page_url' class='$class'>$i</a>";
                            }
                            
                            // Next button
                            if ($current_page < $total_pages) {
                                $next_params = $url_params;
                                $next_params['page'] = $current_page + 1;
                                $next_url = '?' . http_build_query($next_params);
                                echo "<a href='$next_url' class='pagination-btn'>Next <i class='fas fa-chevron-right'></i></a>";
                            } else {
                                echo "<span class='pagination-btn disabled'>Next <i class='fas fa-chevron-right'></i></span>";
                            }
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- User Detail Modal -->
    <div id="userDetailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user"></i> Detail User</h3>
                <span class="close" onclick="closeModal('userDetailModal')">&times;</span>
            </div>
            <div class="modal-body" id="userDetailContent">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit User</h3>
                <span class="close" onclick="closeModal('editUserModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="edit_user_id" name="user_id">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_username">Username *</label>
                            <input type="text" id="edit_username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_nama">Nama Lengkap *</label>
                            <input type="text" id="edit_nama" name="nama" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_email">Email *</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_alamat">Alamat</label>
                        <textarea id="edit_alamat" name="alamat" rows="3"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_kategori">Kategori *</label>
                            <select id="edit_kategori" name="kategori" required>
                                <option value="">Pilih Kategori</option>
                                <option value="pengajar">Pengajar</option>
                                <option value="pelajar">Pelajar</option>
                                <option value="umum">Umum</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_jenis_kelamin">Jenis Kelamin</label>
                            <select id="edit_jenis_kelamin" name="jenis_kelamin">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit_tanggal_lahir">Tanggal Lahir</label>
                            <input type="date" id="edit_tanggal_lahir" name="tanggal_lahir">
                        </div>
                        <div class="form-group">
                            <label for="edit_nomor_telepon">Nomor Telepon</label>
                            <input type="tel" id="edit_nomor_telepon" name="nomor_telepon">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_asal_institusi">Asal Institusi</label>
                        <input type="text" id="edit_asal_institusi" name="asal_institusi">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeModal('editUserModal')">Batal</button>
                <button type="button" class="btn-modal btn-save" onclick="saveUser()">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
            <div class="loading-overlay" id="editLoadingOverlay">
                <div class="spinner"></div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus</h3>
                <span class="close" onclick="closeModal('deleteModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="delete-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Anda yakin ingin menghapus user ini?</h4>
                    <p>User <strong id="deleteUserName"></strong> akan dihapus secara permanen beserta semua data
                        terkait.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-modal btn-cancel" onclick="closeModal('deleteModal')">Batal</button>
                <button type="button" class="btn-modal btn-delete-confirm" onclick="deleteUser()">
                    <i class="fas fa-trash"></i> Hapus User
                </button>
            </div>
            <div class="loading-overlay" id="deleteLoadingOverlay">
                <div class="spinner"></div>
            </div>
        </div>
    </div>

    <script>
    let currentDeleteId = null;

    // Show user detail modal
    function showUserDetail(user) {
        const modal = document.getElementById('userDetailModal');
        const content = document.getElementById('userDetailContent');

        const detailHtml = `
            <div class="user-detail-grid">
                <div class="detail-section">
                    <h4><i class="fas fa-user"></i> Informasi Dasar</h4>
                    <div class="detail-item">
                        <strong>Foto Profil:</strong><br>
                        ${user.foto_profil ? `<img src="../assets/img/profil/${user.foto_profil}" alt="Foto Profil" style="max-width:100px;border-radius:10px;">` : '<span class="text-muted">Tidak ada foto</span>'}
                    </div>
                    <div class="detail-item">
                        <strong>Username:</strong> ${user.username || '-'}
                    </div>
                    <div class="detail-item">
                        <strong>Nama Lengkap:</strong> ${user.nama || '-'}
                    </div>
                    <div class="detail-item">
                        <strong>Email:</strong> ${user.email || '-'}
                    </div>
                    <div class="detail-item">
                        <strong>Kategori:</strong> ${user.kategori ? user.kategori.charAt(0).toUpperCase() + user.kategori.slice(1) : '-'}
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4><i class="fas fa-info-circle"></i> Informasi Personal</h4>
                    <div class="detail-item">
                        <strong>Jenis Kelamin:</strong> ${user.jenis_kelamin === 'L' ? 'Laki-laki' : (user.jenis_kelamin === 'P' ? 'Perempuan' : '-')}
                    </div>
                    <div class="detail-item">
                        <strong>Tanggal Lahir:</strong> ${formatDate(user.tanggal_lahir) || '-'}
                    </div>
                    <div class="detail-item">
                        <strong>Nomor Telepon:</strong> ${user.nomor_telepon || '-'}
                    </div>
                    <div class="detail-item">
                        <strong>Asal Institusi:</strong> ${user.asal_institusi || '-'}
                    </div>
                    <div class="detail-item">
                        <strong>Alamat:</strong> ${user.alamat || '-'}
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4><i class="fas fa-chart-bar"></i> Statistik Peminjaman</h4>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number">${user.total_pinjaman || 0}</div>
                            <div class="stat-label">Total Pinjaman</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">${user.pinjaman_aktif || 0}</div>
                            <div class="stat-label">Pinjaman Aktif</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">${user.pinjaman_selesai || 0}</div>
                            <div class="stat-label">Pinjaman Selesai</div>
                        </div>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h4><i class="fas fa-clock"></i> Informasi Akun</h4>
                    <div class="detail-item">
                        <strong>Dibuat Pada:</strong> ${formatDateTime(user.created_at)}
                    </div>
                </div>
            </div>
        `;

        content.innerHTML = detailHtml;
        modal.style.display = 'block';
    }

    // Edit user modal
    function editUser(user) {
        document.getElementById('edit_user_id').value = user.id_user;
        document.getElementById('edit_username').value = user.username || '';
        document.getElementById('edit_nama').value = user.nama || '';
        document.getElementById('edit_email').value = user.email || '';
        document.getElementById('edit_alamat').value = user.alamat || '';
        document.getElementById('edit_kategori').value = user.kategori || '';
        document.getElementById('edit_jenis_kelamin').value = user.jenis_kelamin || '';
        document.getElementById('edit_tanggal_lahir').value = user.tanggal_lahir || '';
        document.getElementById('edit_nomor_telepon').value = user.nomor_telepon || '';
        document.getElementById('edit_asal_institusi').value = user.asal_institusi || '';

        document.getElementById('editUserModal').style.display = 'block';
    }

    // Save user changes
    function saveUser() {
        const form = document.getElementById('editUserForm');
        const formData = new FormData(form);
        formData.append('action', 'update');

        // Show loading
        document.getElementById('editLoadingOverlay').style.display = 'flex';

        fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('editLoadingOverlay').style.display = 'none';

                if (data.success) {
                    showAlert('success', data.message);
                    closeModal('editUserModal');
                    // Refresh page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                document.getElementById('editLoadingOverlay').style.display = 'none';
                showAlert('error', 'Terjadi kesalahan: ' + error.message);
            });
    }

    // Confirm delete
    function confirmDelete(userId, username) {
        currentDeleteId = userId;
        document.getElementById('deleteUserName').textContent = username;
        document.getElementById('deleteModal').style.display = 'block';
    }

    // Delete user
    function deleteUser() {
        if (!currentDeleteId) return;

        // Show loading
        document.getElementById('deleteLoadingOverlay').style.display = 'flex';

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('user_id', currentDeleteId);

        fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('deleteLoadingOverlay').style.display = 'none';

                if (data.success) {
                    showAlert('success', data.message);
                    closeModal('deleteModal');
                    // Refresh page after short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                document.getElementById('deleteLoadingOverlay').style.display = 'none';
                showAlert('error', 'Terjadi kesalahan: ' + error.message);
            });
    }

    // Close modal
    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
        currentDeleteId = null;
    }

    // Show alert
    function showAlert(type, message) {
        const alertContainer = document.getElementById('alertContainer');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${message}
            `;

        alertContainer.appendChild(alertDiv);
        alertDiv.style.display = 'block';

        // Auto hide after 5 seconds
        setTimeout(() => {
            alertDiv.style.display = 'none';
            alertContainer.removeChild(alertDiv);
        }, 5000);
    }

    // Helper functions
    function formatDate(dateString) {
        if (!dateString || dateString === '0000-00-00') return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('id-ID');
    }

    function formatDateTime(dateTimeString) {
        if (!dateTimeString || dateTimeString === '0000-00-00 00:00:00') return '-';
        const date = new Date(dateTimeString);
        return date.toLocaleString('id-ID');
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modals = ['userDetailModal', 'editUserModal', 'deleteModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (event.target === modal) {
                closeModal(modalId);
            }
        });
    }

    // Form validation
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveUser();
    });

 //responsive navbar
document.addEventListener('DOMContentLoaded', function() {
    // Fix: Ganti hamburgerBtn menjadi hamburger sesuai dengan ID di HTML
    const hamburgerBtn = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const body = document.body;
    const sidebarLinks = document.querySelectorAll('.sidebar a');

    // Pastikan elemen ditemukan sebelum melanjutkan
    if (!hamburgerBtn || !sidebar || !sidebarOverlay) {
        console.error('Required elements not found');
        return;
    }

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
        console.log('Hamburger clicked'); // Debug log
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

    // Debug: Log ketika script dijalankan
    console.log('Sidebar script loaded successfully');
    console.log('Hamburger button found:', !!hamburgerBtn);
    console.log('Sidebar found:', !!sidebar);
    console.log('Sidebar overlay found:', !!sidebarOverlay);
});
    </script>

</body>

</html>