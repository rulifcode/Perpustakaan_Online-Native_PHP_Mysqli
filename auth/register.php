<?php
session_start();
include '../config/config.php';
require '../vendor/autoload.php'; // jika pakai Composer & PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$success = '';
$error = '';

// Fungsi dapatkan id keanggotaan
function getIdKeanggotaan($kategori) {
    switch (strtolower($kategori)) {
        case 'pelajar': return 7;
        case 'umum': return 2;
        case 'pengajar': return 6;
        default: return null;
    }
}

// Fungsi validasi password yang kuat
function validatePassword($password) {
    // Minimal 8 karakter
    if (strlen($password) < 8) {
        return "Password minimal harus 8 karakter.";
    }
    
    // Harus mengandung huruf
    if (!preg_match('/[a-zA-Z]/', $password)) {
        return "Password harus mengandung minimal satu huruf.";
    }
    
    // Harus mengandung angka
    if (!preg_match('/[0-9]/', $password)) {
        return "Password harus mengandung minimal satu angka.";
    }
    
    // Tidak boleh hanya huruf saja
    if (preg_match('/^[a-zA-Z]+$/', $password)) {
        return "Password tidak boleh hanya terdiri dari huruf saja.";
    }
    
    // Tidak boleh hanya angka saja
    if (preg_match('/^[0-9]+$/', $password)) {
        return "Password tidak boleh hanya terdiri dari angka saja.";
    }
    
    return true; // Password valid
}

// Fungsi validasi NIK (Nomor Induk Kependudukan)
function validateNIK($nik) {
    // NIK harus 16 digit
    if (strlen($nik) !== 16) {
        return "NIK harus terdiri dari 16 digit.";
    }
    
    // NIK harus berupa angka
    if (!preg_match('/^[0-9]{16}$/', $nik)) {
        return "NIK hanya boleh berisi angka.";
    }
    
    // Validasi format NIK (6 digit kode wilayah + 6 digit tanggal lahir + 4 digit nomor urut)
    $tanggal_lahir = substr($nik, 6, 6);
    
    // Validasi tanggal lahir dalam NIK (ddmmyy)
    $dd = substr($tanggal_lahir, 0, 2);
    $mm = substr($tanggal_lahir, 2, 2);
    $yy = substr($tanggal_lahir, 4, 2);
    
    // Untuk perempuan, tanggal ditambah 40
    $dd_actual = $dd > 40 ? $dd - 40 : $dd;
    
    if ($dd_actual < 1 || $dd_actual > 31) {
        return "Format tanggal dalam NIK tidak valid.";
    }
    
    if ($mm < 1 || $mm > 12) {
        return "Format bulan dalam NIK tidak valid.";
    }
    
    return true; // NIK valid
}

// Fungsi validasi NISN/NIM untuk Pelajar/Mahasiswa
function validateNISN_NIM($nisn_nim) {
    // NISN = 10 digit, NIM biasanya 8-15 digit (bervariasi per institusi)
    $length = strlen($nisn_nim);
    
    if ($length < 8 || $length > 15) {
        return "NISN/NIM harus terdiri dari 8-15 digit.";
    }
    
    // Harus berupa angka
    if (!preg_match('/^[0-9]+$/', $nisn_nim)) {
        return "NISN/NIM hanya boleh berisi angka.";
    }
    
    // Validasi khusus NISN (10 digit)
    if ($length === 10) {
        // NISN format: tahun masuk (4 digit) + nomor urut (6 digit)
        $tahun = substr($nisn_nim, 0, 4);
        if ($tahun < 1990 || $tahun > date('Y')) {
            return "Tahun dalam NISN tidak valid.";
        }
    }
    
    return true; // NISN/NIM valid
}

// Fungsi validasi NIP (Nomor Induk Pegawai) untuk Pengajar
function validateNIP($nip) {
    // NIP PNS = 18 digit, NIP Non-PNS bisa bervariasi 8-18 digit
    $length = strlen($nip);
    
    if ($length < 8 || $length > 18) {
        return "NIP harus terdiri dari 8-18 digit.";
    }
    
    // Harus berupa angka
    if (!preg_match('/^[0-9]+$/', $nip)) {
        return "NIP hanya boleh berisi angka.";
    }
    
    // Validasi khusus NIP PNS (18 digit)
    if ($length === 18) {
        // Format NIP: YYYYMMDDYYYYMMDDXX
        $tgl_lahir = substr($nip, 0, 8);
        $tgl_angkat = substr($nip, 8, 8);
        
        // Validasi tanggal lahir
        $tahun_lahir = substr($tgl_lahir, 0, 4);
        $bulan_lahir = substr($tgl_lahir, 4, 2);
        $hari_lahir = substr($tgl_lahir, 6, 2);
        
        if ($tahun_lahir < 1940 || $tahun_lahir > date('Y') - 17) {
            return "Tahun lahir dalam NIP tidak valid.";
        }
        
        if ($bulan_lahir < 1 || $bulan_lahir > 12) {
            return "Bulan lahir dalam NIP tidak valid.";
        }
        
        if ($hari_lahir < 1 || $hari_lahir > 31) {
            return "Tanggal lahir dalam NIP tidak valid.";
        }
        
        // Validasi tanggal pengangkatan
        $tahun_angkat = substr($tgl_angkat, 0, 4);
        if ($tahun_angkat < 1970 || $tahun_angkat > date('Y')) {
            return "Tahun pengangkatan dalam NIP tidak valid.";
        }
    }
    
    return true; // NIP valid
}

if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $kategori = $_POST['kategori'];
    $identitas = $_POST['identitas'] ?? null;
    $asal_institusi = $_POST['asal_institusi'] ?? null;
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $telepon = trim($_POST['nomor_telepon']);
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $role = 'user';
    $created_at = date('Y-m-d H:i:s');

    // Validasi field kosong
    if (empty($username) || empty($email) || empty($password) || empty($kategori) ||
        empty($nama) || empty($alamat) || empty($telepon) || empty($tanggal_lahir) || empty($jenis_kelamin)) {
        $error = "Semua field wajib diisi.";
    } 
    // Validasi format email
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } 
    else {
        // Validasi password
        $passwordValidation = validatePassword($password);
        if ($passwordValidation !== true) {
            $error = $passwordValidation;
        } 
        // Validasi identitas berdasarkan kategori
        elseif ($kategori === 'umum') {
            if (empty($identitas)) {
                $error = "NIK KTP wajib diisi untuk kategori umum.";
            } else {
                $nikValidation = validateNIK($identitas);
                if ($nikValidation !== true) {
                    $error = $nikValidation;
                }
            }
        }
        elseif ($kategori === 'pelajar') {
            if (empty($identitas)) {
                $error = "NISN/NIM wajib diisi untuk kategori pelajar/mahasiswa.";
            } else {
                $nisnValidation = validateNISN_NIM($identitas);
                if ($nisnValidation !== true) {
                    $error = $nisnValidation;
                }
            }
            if (empty($asal_institusi)) {
                $error = "Asal institusi wajib diisi untuk kategori pelajar/mahasiswa.";
            }
        }
        elseif ($kategori === 'pengajar') {
            if (empty($identitas)) {
                $error = "NIP wajib diisi untuk kategori pengajar.";
            } else {
                $nipValidation = validateNIP($identitas);
                if ($nipValidation !== true) {
                    $error = $nipValidation;
                }
            }
            if (empty($asal_institusi)) {
                $error = "Asal institusi wajib diisi untuk kategori pengajar.";
            }
        }
        
        if (empty($error)) {
            $domain = substr(strrchr($email, "@"), 1);
            $allowed_domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com', 'student.unsri.ac.id'];

            if (!in_array($domain, $allowed_domains)) {
                $error = "Gunakan email dengan domain valid seperti gmail.com, yahoo.com.";
            } else {
                $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
                $stmt->bind_param("ss", $username, $email);
                $stmt->execute();
                $res = $stmt->get_result();

                if ($res->num_rows > 0) {
                    $error = "Username atau Email sudah digunakan.";
                } else {
                    $verify_token = bin2hex(random_bytes(32));
                    $is_verified = 0;
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $id_keanggotaan = getIdKeanggotaan($kategori);

                    $stmt = $conn->prepare("INSERT INTO users 
                        (username, email, password, kategori, identitas, asal_institusi, id_keanggotaan, nama, role, created_at, alamat, nomor_telepon, tanggal_lahir, jenis_kelamin, verify_token, is_verified) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                    $stmt->bind_param("ssssssissssssssi", 
                        $username, $email, $hashed, $kategori, $identitas, $asal_institusi, $id_keanggotaan,
                        $nama, $role, $created_at, $alamat, $telepon, $tanggal_lahir, $jenis_kelamin,
                        $verify_token, $is_verified
                    );

                    if ($stmt->execute()) {
                        // Kirim email verifikasi
                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host = 'smtp.gmail.com'; // atau smtp dari hosting
                            $mail->SMTPAuth = true;
                            $mail->Username = 'emailmu@gmail.com'; // ganti
                            $mail->Password = 'apppassword'; // ganti
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port = 587;

                            $mail->setFrom('emailmu@gmail.com', 'Perpustakaan');
                            $mail->addAddress($email, $nama);
                            $mail->isHTML(true);
                            $mail->Subject = 'Verifikasi Email Anda';
                            $link = "http://localhost/perpustakaan_app/auth/verify.php?token=$verify_token";
                            $mail->Body = "Klik link berikut untuk verifikasi akun Anda:<br><a href='$link'>$link</a>";
                            $mail->send();

                            $success = "Pendaftaran berhasil. Silakan cek email untuk verifikasi.";
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
    <title>Register</title>
    <link rel="icon" type="image/png" href="../assets/img/favicon-32x32.png" />
    <link rel="stylesheet" href="../assets/css/register.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
    function toggleExtraFields() {
        const kategori = document.querySelector('select[name="kategori"]').value;
        const identitasField = document.querySelector('input[name="identitas"]');
        const labelIdentitas = document.getElementById('label-identitas');
        const institusiField = document.getElementById('asal-institusi');
        const extraFields = document.getElementById('extra-fields');

        if (kategori === 'pelajar') {
            extraFields.classList.add('show');
            labelIdentitas.innerText = "NISN / NIM:";
            identitasField.placeholder = "NISN atau NIM";
            identitasField.style.display = 'block';
            institusiField.style.display = 'block';
        } else if (kategori === 'pengajar') {
            extraFields.classList.add('show');
            labelIdentitas.innerText = "NIP:";
            identitasField.placeholder = "NIP";
            identitasField.style.display = 'block';
            institusiField.style.display = 'block';
        } else if (kategori === 'umum') {
            extraFields.classList.add('show');
            labelIdentitas.innerText = "Nomor KTP:";
            identitasField.placeholder = "Nomor KTP";
            identitasField.style.display = 'block';
            institusiField.style.display = 'none';
            institusiField.value = '';
        } else {
            extraFields.classList.remove('show');
            identitasField.value = '';
            institusiField.value = '';
        }
    }

    // Fungsi untuk mempertahankan nilai form setelah submit
    function preserveFormValues() {
        <?php if (isset($_POST['register'])): ?>
        document.querySelector('input[name="username"]').value = "<?= htmlspecialchars($_POST['username'] ?? '') ?>";
        document.querySelector('input[name="email"]').value = "<?= htmlspecialchars($_POST['email'] ?? '') ?>";
        document.querySelector('input[name="nama"]').value = "<?= htmlspecialchars($_POST['nama'] ?? '') ?>";
        document.querySelector('input[name="alamat"]').value = "<?= htmlspecialchars($_POST['alamat'] ?? '') ?>";
        document.querySelector('input[name="nomor_telepon"]').value = "<?= htmlspecialchars($_POST['nomor_telepon'] ?? '') ?>";
        document.querySelector('input[name="tanggal_lahir"]').value = "<?= htmlspecialchars($_POST['tanggal_lahir'] ?? '') ?>";
        document.querySelector('select[name="jenis_kelamin"]').value = "<?= htmlspecialchars($_POST['jenis_kelamin'] ?? '') ?>";
        document.querySelector('select[name="kategori"]').value = "<?= htmlspecialchars($_POST['kategori'] ?? '') ?>";
        document.querySelector('input[name="identitas"]').value = "<?= htmlspecialchars($_POST['identitas'] ?? '') ?>";
        document.querySelector('input[name="asal_institusi"]').value = "<?= htmlspecialchars($_POST['asal_institusi'] ?? '') ?>";
        toggleExtraFields();
        <?php endif; ?>
    }
    </script>
</head>
<body onload="toggleExtraFields(); preserveFormValues();">
<div class="login-container">
    <div class="login-box">
        <h2>Daftar Akun User</h2>   
            <?php if (isset($success) && $success): ?>
                <div class="message success"><?= htmlspecialchars($success) ?></div>
            <?php elseif (isset($error) && $error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        
        <form method="POST" autocomplete="off">
            <div class="form-grid">
                <input type="text" name="username" placeholder="Username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" class="full-width">
                <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" class="full-width">
                
                <input type="password" name="password" placeholder="Password (min 8 karakter, huruf + angka)" required class="full-width">
                <input type="text" name="nama" placeholder="Nama Lengkap" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" class="full-width">
                
                <input type="text" name="alamat" placeholder="Alamat" required value="<?= htmlspecialchars($_POST['alamat'] ?? '') ?>" class="full-width">
                
                <input type="text" name="nomor_telepon" placeholder="Nomor Telepon" required value="<?= htmlspecialchars($_POST['nomor_telepon'] ?? '') ?>">
                <input type="date" name="tanggal_lahir" required value="<?php echo htmlspecialchars($_POST['tanggal_lahir'] ?? ''); ?>">

                <select name="jenis_kelamin" required>
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="L" <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] === 'L') ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="P" <?= (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] === 'P') ? 'selected' : '' ?>>Perempuan</option>
                </select>

                <select name="kategori" onchange="toggleExtraFields()" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="umum" <?= (isset($_POST['kategori']) && $_POST['kategori'] === 'umum') ? 'selected' : '' ?>>Umum</option>
                    <option value="pelajar" <?= (isset($_POST['kategori']) && $_POST['kategori'] === 'pelajar') ? 'selected' : '' ?>>Pelajar</option>
                    <option value="pengajar" <?= (isset($_POST['kategori']) && $_POST['kategori'] === 'pengajar') ? 'selected' : '' ?>>Pengajar</option>
                </select>

                <div id="extra-fields">
                    <label id="label-identitas">Identitas:</label>
                    <input type="text" name="identitas" placeholder="" value="<?= htmlspecialchars($_POST['identitas'] ?? '') ?>">
                    <input type="text" name="asal_institusi" id="asal-institusi" placeholder="Asal Sekolah/Kampus" value="<?= htmlspecialchars($_POST['asal_institusi'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" name="register">Daftar</button>
        </form>
        <p><a href="login.php">Kembali ke Login</a></p>
    </div>
</div>
</body>
</html>