<?php
session_start();
include '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";

$result = mysqli_query($conn, "SELECT * FROM users WHERE id_user = $user_id");
$user = mysqli_fetch_assoc($result);

if (isset($_POST['update'])) {
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $nomor_telepon = mysqli_real_escape_string($conn, $_POST['nomor_telepon']);

    $update = mysqli_query($conn, "UPDATE users SET 
        alamat='$alamat', 
        jenis_kelamin='$jenis_kelamin', 
        tanggal_lahir='$tanggal_lahir', 
        nomor_telepon='$nomor_telepon' 
        WHERE id_user=$user_id");

    if ($update) {
        $success = "Profil berhasil diperbarui.";
        $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id_user = $user_id"));
    } else {
        $errors[] = "Gagal memperbarui profil.";
    }
}

if (isset($_POST['upload_foto'])) {
    if ($_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 2 * 1024 * 1024;

        if (in_array($_FILES['foto']['type'], $allowed_types) && $_FILES['foto']['size'] <= $max_size) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = 'profil_' . $user_id . '.' . $ext;
            $upload_path = '../assets/img/profil/' . $filename;

            if (!empty($user['foto_profil']) && file_exists('../assets/img/profil/' . $user['foto_profil']) && $user['foto_profil'] !== $filename) {
                unlink('../assets/img/profil/' . $user['foto_profil']);
            }

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                mysqli_query($conn, "UPDATE users SET foto_profil='$filename' WHERE id_user=$user_id");
                $success = "Foto profil berhasil diunggah.";
                $user['foto_profil'] = $filename;
            } else {
                $errors[] = "Gagal mengunggah file.";
            }
        } else {
            $errors[] = "File harus JPG/PNG dan maksimal 2MB.";
        }
    } else {
        $errors[] = "Terjadi kesalahan saat upload.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Anda — Litera</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/header.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <script src="../assets/js/header.js"></script>
    <script src="../assets/js/animasi.js" defer></script>

    <style>
        /* ============ DESIGN TOKENS (matching reference CSS) ============ */
        :root {
            --navy:       #0d1f4e;
            --navy-mid:   #1f3c88;
            --navy-light: #2c55b5;
            --gold:       #c9a84c;
            --gold-light: #e6c878;
            --cream:      #f7f4ef;
            --cream-dark: #ede9e0;
            --white:      #ffffff;
            --text-body:  #3a3a3a;
            --text-muted: #7a7a7a;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--cream);
            color: var(--text-body);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 40px;
        }

        /* ============ PAGE HEADER (compact, consistent with site hero style) ============ */
        .profile-header {
            background-color: var(--navy);
            padding: 72px 0 0;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: -150px; right: -150px;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(201,168,76,0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .profile-header-inner {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: flex-end;
            gap: 36px;
            padding-bottom: 0;
        }

        /* Avatar in header */
        .header-avatar-wrap {
            position: relative;
            flex-shrink: 0;
            margin-bottom: -40px;
        }

        .header-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid var(--gold);
            object-fit: cover;
            display: block;
            background: var(--navy-mid);
        }

        .header-avatar-icon {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid var(--gold);
            background: var(--navy-mid);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gold);
            font-size: 2.8rem;
        }

        .header-avatar-edit {
            position: absolute;
            bottom: 4px; right: 4px;
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--gold);
            color: var(--navy);
            border: 2px solid var(--navy);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            cursor: pointer;
            transition: background 0.2s;
        }

        .header-avatar-edit:hover { background: var(--gold-light); }

        /* Header meta */
        .header-meta {
            padding-bottom: 24px;
            flex: 1;
        }

        .header-eyebrow {
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 8px;
            display: block;
        }

        .header-meta h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 3.5vw, 2.8rem);
            font-weight: 400;
            color: var(--white);
            line-height: 1.15;
            margin-bottom: 8px;
        }

        .header-meta h1 em {
            font-style: italic;
            color: var(--gold-light);
        }

        .header-meta p {
            font-size: 0.88rem;
            font-weight: 300;
            color: rgba(255,255,255,0.5);
        }

        /* Tab bar at bottom of header */
        .profile-tabs {
            display: flex;
            gap: 0;
            margin-top: 32px;
            border-top: 1px solid rgba(255,255,255,0.06);
        }

        .profile-tab {
            font-size: 0.78rem;
            font-weight: 500;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.4);
            padding: 14px 28px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: color 0.2s, border-color 0.2s;
            user-select: none;
        }

        .profile-tab.active {
            color: var(--gold);
            border-bottom-color: var(--gold);
        }

        .profile-tab:hover { color: rgba(255,255,255,0.75); }

        /* ============ STATUS MESSAGES ============ */
        .status-bar {
            padding: 0 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .alert {
            margin-top: 20px;
            padding: 14px 20px;
            border-radius: 3px;
            font-size: 0.88rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .alert-success {
            background: #eaf5ee;
            color: #1a5c30;
            border-left: 3px solid #2d9349;
        }

        .alert-error {
            background: #fdf0ef;
            color: #8b1a12;
            border-left: 3px solid #c0392b;
        }

        /* ============ MAIN LAYOUT ============ */
        .profile-body {
            max-width: 1200px;
            margin: 0 auto;
            padding: 56px 40px 80px;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 36px;
            align-items: start;
        }

        /* ============ SIDEBAR CARD ============ */
        .sidebar-card {
            background: var(--white);
            border: 1px solid var(--cream-dark);
            border-radius: 4px;
            overflow: hidden;
            position: sticky;
            top: 24px;
        }

        .sidebar-top {
            background: var(--navy);
            padding: 32px 24px 24px;
            text-align: center;
            position: relative;
        }

        .sidebar-top::after {
            content: '';
            position: absolute;
            bottom: -1px; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
        }

        .sidebar-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid rgba(201,168,76,0.5);
            object-fit: cover;
            margin: 0 auto 12px;
            display: block;
        }

        .sidebar-avatar-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid rgba(201,168,76,0.5);
            background: var(--navy-mid);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            color: var(--gold);
            font-size: 2rem;
        }

        .sidebar-username {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 4px;
        }

        .sidebar-role {
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--gold);
        }

        .sidebar-details {
            padding: 20px 24px;
        }

        .sidebar-detail-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--cream-dark);
            font-size: 0.84rem;
        }

        .sidebar-detail-item:last-child { border-bottom: none; }

        .sidebar-detail-item i {
            color: var(--gold);
            width: 14px;
            margin-top: 2px;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        .sidebar-detail-label {
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 2px;
        }

        .sidebar-detail-value {
            font-weight: 400;
            color: var(--text-body);
            word-break: break-word;
        }

        /* Upload section inside sidebar */
        .sidebar-upload {
            padding: 20px 24px;
            border-top: 1px solid var(--cream-dark);
            background: var(--cream);
        }

        .upload-label-text {
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 12px;
            display: block;
        }

        .file-pick-btn {
            width: 100%;
            padding: 10px 16px;
            background: var(--white);
            border: 1px dashed var(--cream-dark);
            border-radius: 3px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.82rem;
            color: var(--text-muted);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: border-color 0.2s, color 0.2s;
            margin-bottom: 10px;
        }

        .file-pick-btn:hover {
            border-color: var(--gold);
            color: var(--navy);
        }

        .file-pick-btn.selected {
            border-color: var(--gold);
            color: var(--navy-mid);
            background: rgba(201,168,76,0.06);
        }

        #foto-input { display: none; }

        .btn-upload-submit {
            width: 100%;
            padding: 10px;
            background: var(--navy);
            color: var(--gold);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            border: none;
            border-radius: 2px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-upload-submit:hover { background: var(--navy-mid); }

        .upload-hint {
            font-size: 0.72rem;
            color: var(--text-muted);
            text-align: center;
            margin-top: 8px;
            display: block;
        }

        /* ============ MAIN FORM PANEL ============ */
        .form-panel {
            background: var(--white);
            border: 1px solid var(--cream-dark);
            border-radius: 4px;
            overflow: hidden;
        }

        .form-panel-header {
            padding: 28px 36px;
            border-bottom: 1px solid var(--cream-dark);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .form-panel-header-left {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .form-panel-eyebrow {
            font-size: 0.68rem;
            font-weight: 500;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--gold);
        }

        .form-panel-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            font-weight: 400;
            color: var(--navy);
        }

        /* Section dividers inside form */
        .form-section {
            padding: 32px 36px;
            border-bottom: 1px solid var(--cream);
        }

        .form-section:last-of-type { border-bottom: none; }

        .form-section-label {
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--cream-dark);
        }

        /* Readonly info rows */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0;
        }

        .info-item {
            padding: 14px 0;
            border-bottom: 1px solid var(--cream-dark);
        }

        .info-item:nth-child(odd) { padding-right: 24px; border-right: 1px solid var(--cream-dark); }
        .info-item:nth-child(even) { padding-left: 24px; }

        .info-item-label {
            font-size: 0.68rem;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .info-item-value {
            font-size: 0.92rem;
            font-weight: 400;
            color: var(--navy);
        }

        /* Editable field rows */
        .field-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .field-grid .field-full { grid-column: 1 / -1; }

        .field-group { display: flex; flex-direction: column; gap: 7px; }

        .field-label {
            font-size: 0.72rem;
            font-weight: 500;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .field-label i {
            color: var(--gold);
            margin-right: 5px;
            font-size: 0.65rem;
        }

        input[type="text"],
        input[type="date"],
        select,
        textarea {
            font-family: 'DM Sans', sans-serif;
            font-size: 0.92rem;
            font-weight: 400;
            color: var(--navy);
            background: var(--cream);
            border: 1px solid var(--cream-dark);
            border-radius: 3px;
            padding: 10px 14px;
            width: 100%;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            appearance: none;
            -webkit-appearance: none;
        }

        select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23c9a84c' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 36px;
        }

        textarea {
            min-height: 90px;
            resize: vertical;
            line-height: 1.65;
        }

        input[type="text"]:focus,
        input[type="date"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            background: var(--white);
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201,168,76,0.12);
        }

        /* Form footer */
        .form-footer {
            padding: 24px 36px;
            background: var(--cream);
            border-top: 1px solid var(--cream-dark);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .form-footer-hint {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 300;
        }

        .btn-save {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--gold);
            color: var(--navy);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 13px 32px;
            border: none;
            border-radius: 2px;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s;
        }

        .btn-save:hover {
            background: var(--gold-light);
            transform: translateY(-2px);
        }

        .btn-save:active { transform: translateY(0); }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 900px) {
            .profile-body {
                grid-template-columns: 1fr;
            }

            .sidebar-card { position: static; }

            .profile-header-inner {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-avatar-wrap { margin-bottom: 0; }

            .profile-tabs { overflow-x: auto; }
        }

        @media (max-width: 640px) {
            .container, .status-bar, .profile-body { padding-left: 20px; padding-right: 20px; }

            .info-grid, .field-grid { grid-template-columns: 1fr; }

            .info-item:nth-child(odd) {
                padding-right: 0;
                border-right: none;
            }

            .info-item:nth-child(even) { padding-left: 0; }

            .form-section { padding: 24px 20px; }
            .form-panel-header { padding: 20px; }
            .form-footer { padding: 20px; flex-direction: column; align-items: stretch; }
            .btn-save { justify-content: center; }
        }
    </style>
</head>
<body>

<?php include '../partials/header_user.php'; ?>

<!-- ===== PAGE HEADER ===== -->
<section class="profile-header">
    <div class="container">
        <div class="profile-header-inner">
            <div class="header-avatar-wrap">
                <?php if (!empty($user['foto_profil']) && file_exists('../assets/img/profil/' . $user['foto_profil'])): ?>
                    <img src="../assets/img/profil/<?= htmlspecialchars($user['foto_profil']) ?>"
                         alt="Foto Profil" class="header-avatar">
                <?php else: ?>
                    <div class="header-avatar-icon"><i class="fas fa-user"></i></div>
                <?php endif; ?>
                <label for="foto-input-header" class="header-avatar-edit" title="Ubah foto">
                    <i class="fas fa-camera"></i>
                </label>
            </div>

            <div class="header-meta">
                <span class="header-eyebrow">Akun Litera</span>
                <h1><em><?= htmlspecialchars($user['nama'] ?? $user['username']) ?></em></h1>
                <p>
                    <?= htmlspecialchars(ucfirst($user['kategori'])) ?> &nbsp;·&nbsp;
                    <?= htmlspecialchars($user['asal_institusi'] ?? '-') ?> &nbsp;·&nbsp;
                    Bergabung <?= date("M Y", strtotime($user['created_at'])) ?>
                </p>
            </div>
        </div>

        <div class="profile-tabs">
            <div class="profile-tab active" data-tab="info">Informasi Profil</div>
            <div class="profile-tab" data-tab="foto">Foto Profil</div>
        </div>
    </div>
</section>

<!-- ===== STATUS MESSAGES ===== -->
<div class="status-bar">
    <?php foreach ($errors as $err): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($err) ?></div>
    <?php endforeach; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
</div>

<!-- ===== MAIN BODY ===== -->
<div class="profile-body">

    <!-- SIDEBAR -->
    <aside class="sidebar-card">
        <div class="sidebar-top">
            <?php if (!empty($user['foto_profil']) && file_exists('../assets/img/profil/' . $user['foto_profil'])): ?>
                <img src="../assets/img/profil/<?= htmlspecialchars($user['foto_profil']) ?>"
                     alt="Foto" class="sidebar-avatar">
            <?php else: ?>
                <div class="sidebar-avatar-icon"><i class="fas fa-user"></i></div>
            <?php endif; ?>
            <div class="sidebar-username"><?= htmlspecialchars($user['username']) ?></div>
            <div class="sidebar-role"><?= htmlspecialchars(ucfirst($user['kategori'])) ?></div>
        </div>

        <div class="sidebar-details">
            <div class="sidebar-detail-item">
                <i class="fas fa-envelope"></i>
                <div>
                    <div class="sidebar-detail-label">Email</div>
                    <div class="sidebar-detail-value"><?= htmlspecialchars($user['email']) ?></div>
                </div>
            </div>
            <div class="sidebar-detail-item">
                <i class="fas fa-university"></i>
                <div>
                    <div class="sidebar-detail-label">Institusi</div>
                    <div class="sidebar-detail-value"><?= htmlspecialchars($user['asal_institusi'] ?? '-') ?></div>
                </div>
            </div>
            <div class="sidebar-detail-item">
                <i class="fas fa-id-card"></i>
                <div>
                    <div class="sidebar-detail-label">Identitas</div>
                    <div class="sidebar-detail-value"><?= htmlspecialchars($user['identitas'] ?? '-') ?></div>
                </div>
            </div>
            <div class="sidebar-detail-item">
                <i class="fas fa-calendar-alt"></i>
                <div>
                    <div class="sidebar-detail-label">Bergabung</div>
                    <div class="sidebar-detail-value"><?= date("d M Y", strtotime($user['created_at'])) ?></div>
                </div>
            </div>
        </div>

        <!-- Upload foto -->
        <div class="sidebar-upload">
            <span class="upload-label-text">Ganti Foto Profil</span>
            <form method="post" enctype="multipart/form-data" id="upload-form">
                <input type="file" name="foto" id="foto-input" accept="image/png, image/jpeg">
                <input type="file" name="foto" id="foto-input-header" accept="image/png, image/jpeg" style="display:none">
                <label for="foto-input" class="file-pick-btn" id="file-label">
                    <i class="fas fa-image"></i>
                    <span id="file-label-text">Pilih Foto…</span>
                </label>
                <button type="submit" name="upload_foto" class="btn-upload-submit">
                    <i class="fas fa-arrow-up"></i> &nbsp;Simpan Foto
                </button>
                <span class="upload-hint">JPG / PNG · Maks. 2 MB</span>
            </form>
        </div>
    </aside>

    <!-- MAIN FORM -->
    <div class="form-panel">
        <div class="form-panel-header">
            <div class="form-panel-header-left">
                <span class="form-panel-eyebrow">Edit Data</span>
                <span class="form-panel-title">Informasi Profil</span>
            </div>
        </div>

        <!-- READONLY section -->
        <div class="form-section">
            <div class="form-section-label">Data Akun</div>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-item-label">Nama Lengkap</div>
                    <div class="info-item-value"><?= htmlspecialchars($user['nama']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Username</div>
                    <div class="info-item-value"><?= htmlspecialchars($user['username']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Email</div>
                    <div class="info-item-value"><?= htmlspecialchars($user['email']) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Kategori</div>
                    <div class="info-item-value"><?= htmlspecialchars(ucfirst($user['kategori'])) ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Identitas</div>
                    <div class="info-item-value"><?= htmlspecialchars($user['identitas'] ?? '-') ?></div>
                </div>
                <div class="info-item">
                    <div class="info-item-label">Asal Institusi</div>
                    <div class="info-item-value"><?= htmlspecialchars($user['asal_institusi'] ?? '-') ?></div>
                </div>
            </div>
        </div>

        <!-- EDITABLE section -->
        <form method="post">
            <div class="form-section">
                <div class="form-section-label">Data Pribadi</div>
                <div class="field-grid">

                    <div class="field-group">
                        <label class="field-label"><i class="fas fa-venus-mars"></i>Jenis Kelamin</label>
                        <select name="jenis_kelamin" required>
                            <option value="L - profil.php:876" <?php if ($user['jenis_kelamin'] == 'L') echo 'selected'; ?>>Laki-laki</option>
                            <option value="P - profil.php:877" <?php if ($user['jenis_kelamin'] == 'P') echo 'selected'; ?>>Perempuan</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label class="field-label"><i class="fas fa-birthday-cake"></i>Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="<?= htmlspecialchars($user['tanggal_lahir'] ?? '') ?>" required>
                    </div>

                    <div class="field-group">
                        <label class="field-label"><i class="fas fa-phone"></i>Nomor Telepon</label>
                        <input type="text" name="nomor_telepon"
                               value="<?= htmlspecialchars($user['nomor_telepon'] ?? '') ?>"
                               placeholder="081234567890" required>
                    </div>

                    <div class="field-group field-full">
                        <label class="field-label"><i class="fas fa-map-marker-alt"></i>Alamat</label>
                        <textarea name="alamat" placeholder="Masukkan alamat lengkap Anda" required><?= htmlspecialchars($user['alamat'] ?? '') ?></textarea>
                    </div>

                </div>
            </div>

            <div class="form-footer">
                <span class="form-footer-hint">* Nama, email, dan username tidak dapat diubah sendiri.</span>
                <button type="submit" name="update" class="btn-save">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div><!-- /.profile-body -->

<?php include '../partials/footer.php'; ?>

<script>
    // File input label update
    const fotoInput = document.getElementById('foto-input');
    const fotoInputHeader = document.getElementById('foto-input-header');
    const fileLabelText = document.getElementById('file-label-text');
    const fileLabel = document.getElementById('file-label');

    function onFileChange(e) {
        const name = e.target.files[0]?.name;
        if (name) {
            fileLabelText.textContent = name.length > 22 ? name.slice(0, 20) + '…' : name;
            fileLabel.classList.add('selected');
            // Sync if header input was used
            if (e.target === fotoInputHeader) {
                const dt = new DataTransfer();
                dt.items.add(e.target.files[0]);
                fotoInput.files = dt.files;
            }
        }
    }

    fotoInput.addEventListener('change', onFileChange);
    fotoInputHeader.addEventListener('change', onFileChange);

    // Tab switching (cosmetic, both sections are visible but tabs highlight active state)
    document.querySelectorAll('.profile-tab').forEach(tab => {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Auto-dismiss alerts after 5s
    document.querySelectorAll('.alert').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 0.4s, transform 0.4s';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(() => el.remove(), 400);
        }, 5000);
    });
</script>
</body>
</html>