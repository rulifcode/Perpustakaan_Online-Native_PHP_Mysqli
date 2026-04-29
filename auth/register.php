<?php
session_start();
include '../config/config.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$success = '';
$error = '';

function getIdKeanggotaan($kategori) {
    switch (strtolower($kategori)) {
        case 'pelajar': return 7;
        case 'umum': return 2;
        case 'pengajar': return 6;
        default: return null;
    }
}

function validatePassword($password) {
    if (strlen($password) < 8) return "Password minimal harus 8 karakter.";
    if (!preg_match('/[a-zA-Z]/', $password)) return "Password harus mengandung minimal satu huruf.";
    if (!preg_match('/[0-9]/', $password)) return "Password harus mengandung minimal satu angka.";
    if (preg_match('/^[a-zA-Z]+$/', $password)) return "Password tidak boleh hanya terdiri dari huruf saja.";
    if (preg_match('/^[0-9]+$/', $password)) return "Password tidak boleh hanya terdiri dari angka saja.";
    return true;
}

function validateNIK($nik) {
    if (strlen($nik) !== 16) return "NIK harus terdiri dari 16 digit.";
    if (!preg_match('/^[0-9]{16}$/', $nik)) return "NIK hanya boleh berisi angka.";
    $dd = substr($nik, 6, 2);
    $mm = substr($nik, 8, 2);
    $dd_actual = $dd > 40 ? $dd - 40 : $dd;
    if ($dd_actual < 1 || $dd_actual > 31) return "Format tanggal dalam NIK tidak valid.";
    if ($mm < 1 || $mm > 12) return "Format bulan dalam NIK tidak valid.";
    return true;
}

function validateNISN_NIM($nisn_nim) {
    $length = strlen($nisn_nim);
    if ($length < 8 || $length > 15) return "NISN/NIM harus terdiri dari 8-15 digit.";
    if (!preg_match('/^[0-9]+$/', $nisn_nim)) return "NISN/NIM hanya boleh berisi angka.";
    if ($length === 10) {
        $tahun = substr($nisn_nim, 0, 4);
        if ($tahun < 1990 || $tahun > date('Y')) return "Tahun dalam NISN tidak valid.";
    }
    return true;
}

function validateNIP($nip) {
    $length = strlen($nip);
    if ($length < 8 || $length > 18) return "NIP harus terdiri dari 8-18 digit.";
    if (!preg_match('/^[0-9]+$/', $nip)) return "NIP hanya boleh berisi angka.";
    if ($length === 18) {
        $tahun_lahir = substr($nip, 0, 4);
        $bulan_lahir = substr($nip, 4, 2);
        $hari_lahir  = substr($nip, 6, 2);
        $tahun_angkat = substr($nip, 8, 4);
        if ($tahun_lahir < 1940 || $tahun_lahir > date('Y') - 17) return "Tahun lahir dalam NIP tidak valid.";
        if ($bulan_lahir < 1 || $bulan_lahir > 12) return "Bulan lahir dalam NIP tidak valid.";
        if ($hari_lahir < 1 || $hari_lahir > 31) return "Tanggal lahir dalam NIP tidak valid.";
        if ($tahun_angkat < 1970 || $tahun_angkat > date('Y')) return "Tahun pengangkatan dalam NIP tidak valid.";
    }
    return true;
}

if (isset($_POST['register'])) {
    $username       = trim($_POST['username']);
    $email          = trim($_POST['email']);
    $password       = $_POST['password'];
    $kategori       = $_POST['kategori'];
    $identitas      = $_POST['identitas'] ?? null;
    $asal_institusi = $_POST['asal_institusi'] ?? null;
    $nama           = trim($_POST['nama']);
    $alamat         = trim($_POST['alamat']);
    $telepon        = trim($_POST['nomor_telepon']);
    $tanggal_lahir  = $_POST['tanggal_lahir'];
    $jenis_kelamin  = $_POST['jenis_kelamin'];
    $role           = 'user';
    $created_at     = date('Y-m-d H:i:s');

    if (empty($username) || empty($email) || empty($password) || empty($kategori) ||
        empty($nama) || empty($alamat) || empty($telepon) || empty($tanggal_lahir) || empty($jenis_kelamin)) {
        $error = "Semua field wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        $pwVal = validatePassword($password);
        if ($pwVal !== true) {
            $error = $pwVal;
        } elseif ($kategori === 'umum') {
            if (empty($identitas)) { $error = "NIK KTP wajib diisi untuk kategori umum."; }
            else { $v = validateNIK($identitas); if ($v !== true) $error = $v; }
        } elseif ($kategori === 'pelajar') {
            if (empty($identitas)) { $error = "NISN/NIM wajib diisi untuk kategori pelajar."; }
            else { $v = validateNISN_NIM($identitas); if ($v !== true) $error = $v; }
            if (empty($error) && empty($asal_institusi)) $error = "Asal institusi wajib diisi.";
        } elseif ($kategori === 'pengajar') {
            if (empty($identitas)) { $error = "NIP wajib diisi untuk kategori pengajar."; }
            else { $v = validateNIP($identitas); if ($v !== true) $error = $v; }
            if (empty($error) && empty($asal_institusi)) $error = "Asal institusi wajib diisi.";
        }

        if (empty($error)) {
            $domain = substr(strrchr($email, "@"), 1);
            $allowed_domains = ['gmail.com','yahoo.com','outlook.com','hotmail.com','student.unsri.ac.id'];
            if (!in_array($domain, $allowed_domains)) {
                $error = "Gunakan email dengan domain valid seperti gmail.com, yahoo.com.";
            } else {
                $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
                $stmt->bind_param("ss", $username, $email);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    $error = "Username atau Email sudah digunakan.";
                } else {
                    $verify_token = bin2hex(random_bytes(32));
                    $is_verified  = 0;
                    $hashed       = password_hash($password, PASSWORD_DEFAULT);
                    $id_keanggotaan = getIdKeanggotaan($kategori);

                    $stmt = $conn->prepare("INSERT INTO users 
                        (username,email,password,kategori,identitas,asal_institusi,id_keanggotaan,nama,role,created_at,alamat,nomor_telepon,tanggal_lahir,jenis_kelamin,verify_token,is_verified) 
                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                    $stmt->bind_param("ssssssissssssssi",
                        $username,$email,$hashed,$kategori,$identitas,$asal_institusi,$id_keanggotaan,
                        $nama,$role,$created_at,$alamat,$telepon,$tanggal_lahir,$jenis_kelamin,
                        $verify_token,$is_verified
                    );

                    if ($stmt->execute()) {
                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host       = 'smtp.gmail.com';
                            $mail->SMTPAuth   = true;
                            $mail->Username   = 'emailmu@gmail.com';
                            $mail->Password   = 'apppassword';
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port       = 587;
                            $mail->setFrom('emailmu@gmail.com', 'Litera');
                            $mail->addAddress($email, $nama);
                            $mail->isHTML(true);
                            $mail->Subject = 'Verifikasi Email Anda — Litera';
                            $link = "http://localhost/perpustakaan_app/auth/verify.php?token=$verify_token";
                            $mail->Body = "Klik link berikut untuk verifikasi akun Anda:<br><a href='$link'>$link</a>";
                            $mail->send();
                            $success = "Pendaftaran berhasil! Silakan cek email Anda untuk verifikasi akun.";
                        } catch (Exception $e) {
                            $error = "Pendaftaran berhasil, tetapi email verifikasi gagal dikirim.";
                        }
                    } else {
                        $error = "Terjadi kesalahan saat menyimpan data.";
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Daftar Akun — Litera</title>
<link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/register.css">
</head>

<body onload="toggleExtraFields(); preserveFormValues();">

<canvas id="stars"></canvas>

<div class="register-wrapper">

    <!-- ══ SIDEBAR ══ -->
    <aside class="register-sidebar">

        <a href="../index.php" class="brand-logo">
            <img src="../assets/img/logoTr.png" alt="Litera">
            <div>
                <span class="brand-name">Litera</span>
                <span class="brand-sub">Digital Library</span>
            </div>
        </a>

        <div class="sidebar-hero">
            <div class="sidebar-eyebrow"><span></span>Bergabung<span></span></div>
            <h2>Buat akun dan mulai<br><em>membaca hari ini.</em></h2>
            <p>Daftarkan diri Anda untuk mengakses koleksi digital dan layanan perpustakaan modern.</p>
        </div>

        <div class="sidebar-steps">
            <div class="step-item">
                <div class="step-num">01</div>
                <div class="step-text">
                    <span class="step-title">Data Pribadi</span>
                    <span class="step-desc">Nama, tanggal lahir, kontak</span>
                </div>
            </div>
            <div class="step-item">
                <div class="step-num">02</div>
                <div class="step-text">
                    <span class="step-title">Akun & Keamanan</span>
                    <span class="step-desc">Username, email, password</span>
                </div>
            </div>
            <div class="step-item">
                <div class="step-num">03</div>
                <div class="step-text">
                    <span class="step-title">Kategori Keanggotaan</span>
                    <span class="step-desc">Umum, pelajar, atau pengajar</span>
                </div>
            </div>
        </div>

        <a href="login.php" class="sidebar-back">
            <i class="fa-solid fa-arrow-left"></i>
            Kembali ke halaman masuk
        </a>

    </aside>

    <!-- ══ MAIN FORM ══ -->
    <main class="register-main">

        <div class="form-header">
            <div class="form-label">Pendaftaran Baru</div>
            <h1>Daftar Akun</h1>
            <p>Lengkapi semua informasi di bawah ini untuk membuat akun</p>
        </div>

        <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php elseif($error): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">

            <!-- SECTION 1: Data Pribadi -->
            <div class="form-section">
                <div class="section-label">Data Pribadi</div>
                <div class="form-grid">

                    <div class="input-group col-full">
                        <label class="input-label">Nama Lengkap</label>
                        <div class="input-wrap">
                            <input type="text" name="nama"
                                   placeholder="Nama lengkap sesuai identitas"
                                   value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                                   required>
                            <i class="fa-regular fa-id-card"></i>
                        </div>
                    </div>

                    <div class="input-group col-2">
                        <label class="input-label">Alamat</label>
                        <div class="input-wrap">
                            <input type="text" name="alamat"
                                   placeholder="Alamat lengkap"
                                   value="<?= htmlspecialchars($_POST['alamat'] ?? '') ?>"
                                   required>
                            <i class="fa-solid fa-location-dot"></i>
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">Nomor Telepon</label>
                        <div class="input-wrap">
                            <input type="text" name="nomor_telepon"
                                   placeholder="08123456789"
                                   value="<?= htmlspecialchars($_POST['nomor_telepon'] ?? '') ?>"
                                   required>
                            <i class="fa-solid fa-phone"></i>
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">Tanggal Lahir</label>
                        <div class="input-wrap">
                            <input type="date" name="tanggal_lahir"
                                   value="<?= htmlspecialchars($_POST['tanggal_lahir'] ?? '') ?>"
                                   required>
                            <i class="fa-regular fa-calendar"></i>
                        </div>
                    </div>

                    <div class="input-group col-2">
                        <label class="input-label">Jenis Kelamin</label>
                        <div class="input-wrap select-wrap">
                            <select name="jenis_kelamin" required>
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="L" <?= (($_POST['jenis_kelamin'] ?? '') === 'L') ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="P" <?= (($_POST['jenis_kelamin'] ?? '') === 'P') ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                            <i class="fa-solid fa-venus-mars"></i>
                        </div>
                    </div>

                </div>
            </div>

            <!-- SECTION 2: Akun & Keamanan -->
            <div class="form-section">
                <div class="section-label">Akun &amp; Keamanan</div>
                <div class="form-grid">

                    <div class="input-group">
                        <label class="input-label">Username</label>
                        <div class="input-wrap">
                            <input type="text" name="username"
                                   placeholder="Buat username unik"
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                   required>
                            <i class="fa-regular fa-user"></i>
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">Email</label>
                        <div class="input-wrap">
                            <input type="email" name="email"
                                   placeholder="email@domain.com"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   required>
                            <i class="fa-regular fa-envelope"></i>
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">Password</label>
                        <div class="input-wrap">
                            <input type="password" name="password"
                                   placeholder="Min. 8 karakter, huruf + angka"
                                   required>
                            <i class="fa-solid fa-lock"></i>
                        </div>
                    </div>

                </div>
            </div>

            <!-- SECTION 3: Keanggotaan -->
            <div class="form-section">
                <div class="section-label">Kategori Keanggotaan</div>
                <div class="form-grid">

                    <div class="input-group col-full">
                        <label class="input-label">Kategori</label>
                        <div class="input-wrap select-wrap">
                            <select name="kategori" onchange="toggleExtraFields()" required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="umum"    <?= (($_POST['kategori'] ?? '') === 'umum')    ? 'selected' : '' ?>>Umum</option>
                                <option value="pelajar" <?= (($_POST['kategori'] ?? '') === 'pelajar') ? 'selected' : '' ?>>Pelajar / Mahasiswa</option>
                                <option value="pengajar"<?= (($_POST['kategori'] ?? '') === 'pengajar')? 'selected' : '' ?>>Pengajar / Dosen</option>
                            </select>
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                    </div>

                    <div id="extra-fields">

                        <div class="input-group col-2">
                            <label class="input-label" id="label-identitas">Identitas</label>
                            <div class="input-wrap">
                                <input type="text" name="identitas"
                                       placeholder=""
                                       value="<?= htmlspecialchars($_POST['identitas'] ?? '') ?>">
                                <i class="fa-solid fa-fingerprint"></i>
                            </div>
                        </div>

                        <div class="input-group col-2" id="asal-institusi-wrap">
                            <label class="input-label">Asal Institusi</label>
                            <div class="input-wrap">
                                <input type="text" name="asal_institusi"
                                       id="asal-institusi"
                                       placeholder="Nama sekolah / kampus / lembaga"
                                       value="<?= htmlspecialchars($_POST['asal_institusi'] ?? '') ?>">
                                <i class="fa-solid fa-school"></i>
                            </div>
                        </div>

                    </div>

                </div>
            </div>

            <button type="submit" name="register" class="btn-register">
                Buat Akun Sekarang
            </button>

        </form>

    </main>

</div>

<script src="../assets/js/login-effect.js"></script>
<script>
function toggleExtraFields() {
    const kategori      = document.querySelector('select[name="kategori"]').value;
    const extraFields   = document.getElementById('extra-fields');
    const identitasInput= document.querySelector('input[name="identitas"]');
    const labelIdentitas= document.getElementById('label-identitas');
    const institusiWrap = document.getElementById('asal-institusi-wrap');
    const institusiInput= document.getElementById('asal-institusi');

    if (!kategori) {
        extraFields.classList.remove('show');
        return;
    }

    extraFields.classList.add('show');

    if (kategori === 'umum') {
        labelIdentitas.innerText  = 'Nomor KTP (NIK)';
        identitasInput.placeholder = 'Masukkan 16 digit NIK KTP';
        institusiWrap.style.display = 'none';
        institusiInput.value = '';
    } else if (kategori === 'pelajar') {
        labelIdentitas.innerText  = 'NISN / NIM';
        identitasInput.placeholder = 'Masukkan NISN atau NIM';
        institusiWrap.style.display = '';
    } else if (kategori === 'pengajar') {
        labelIdentitas.innerText  = 'NIP';
        identitasInput.placeholder = 'Masukkan NIP';
        institusiWrap.style.display = '';
    }
}

function preserveFormValues() {
    <?php if (isset($_POST['register'])): ?>
    toggleExtraFields();
    <?php endif; ?>
}
</script>

</body>
</html>