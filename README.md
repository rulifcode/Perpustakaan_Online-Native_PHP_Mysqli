# 📚 Perpustakaan Online
### *Modern Library Management System*

<div align="center">
  
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-005C84?style=for-the-badge&logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-323330?style=for-the-badge&logo=javascript&logoColor=F7DF1E)

**🚀 Sistem manajemen perpustakaan modern yang dibangun dengan PHP Native & MySQLi**

[📖 Demo](#demo) • [⚡ Instalasi](#instalasi--setup) • [🎯 Fitur](#fitur-utama) • [📱 Screenshots](#screenshots)

</div>

---

## 🌟 Tentang Project

**Perpustakaan Online** adalah aplikasi web full-stack yang memungkinkan pengelolaan perpustakaan digital dengan antarmuka yang intuitif dan fitur lengkap. Dibangun menggunakan teknologi web fundamental tanpa ketergantungan framework eksternal.

### 💡 Mengapa Memilih Aplikasi Ini?
- ⚡ **Pure & Lightweight** - Tanpa framework, loading cepat
- 🔒 **Secure Authentication** - Sistem login yang aman
- 📊 **Comprehensive Reports** - Laporan detail & analytics
- 📱 **Responsive Design** - Kompatibel semua perangkat
- 🎨 **Clean UI/UX** - Interface modern dan user-friendly

---

## ✨ Fitur Utama

<table>
<tr>
<td width="50%">

### 👥 **User Management**
- 🔐 Multi-level authentication
- 👤 Profile management
- 🛡️ Role-based access control
- 📝 User registration system

### 📚 **Book Management** 
- ➕ Add/Edit/Delete books
- 🖼️ Image upload support
- 🔍 Advanced search & filter
- 📋 Category management

</td>
<td width="50%">

### 📊 **Transaction System**
- 📥 Book borrowing system
- 📤 Return management
- ⏰ Due date tracking
- 💰 Fine calculation

### 📈 **Reports & Analytics**
- 📊 Borrowing statistics
- 📅 Monthly/yearly reports
- 👥 User activity tracking
- 📋 Inventory reports

</td>
</tr>
</table>

---

## 🛠️ Tech Stack

<div align="center">

| Frontend | Backend | Database | Server |
|:--------:|:-------:|:--------:|:------:|
| ![HTML5](https://img.shields.io/badge/-HTML5-E34F26?style=flat-square&logo=html5&logoColor=white) | ![PHP](https://img.shields.io/badge/-PHP-777BB4?style=flat-square&logo=php&logoColor=white) | ![MySQL](https://img.shields.io/badge/-MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white) | ![XAMPP](https://img.shields.io/badge/-XAMPP-FB7A24?style=flat-square&logo=xampp&logoColor=white) |
| ![CSS3](https://img.shields.io/badge/-CSS3-1572B6?style=flat-square&logo=css3&logoColor=white) | ![MySQLi](https://img.shields.io/badge/-MySQLi-005C84?style=flat-square&logo=mysql&logoColor=white) | ![phpMyAdmin](https://img.shields.io/badge/-phpMyAdmin-6C78AF?style=flat-square&logo=phpmyadmin&logoColor=white) | ![Laragon](https://img.shields.io/badge/-Laragon-0E83CD?style=flat-square&logo=laragon&logoColor=white) |
| ![JavaScript](https://img.shields.io/badge/-JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=black) | - | - | ![Apache](https://img.shields.io/badge/-Apache-D22128?style=flat-square&logo=apache&logoColor=white) |

</div>

---

## 🚀 Instalasi & Setup

### 📋 **Requirements**
- PHP 7.4+ atau PHP 8.x
- MySQL 5.7+ atau MariaDB
- Web Server (Apache/Nginx)
- XAMPP/Laragon/WAMP

### ⚡ **Quick Start**

```bash
# 1. Clone repository
git clone https://github.com/rulifcode/Perpustakaan_Online-Native_PHP_Mysqli.git
cd Perpustakaan_Online-Native_PHP_Mysqli

# 2. Pindahkan ke direktori server
# Untuk XAMPP: pindah ke htdocs/
# Untuk Laragon: pindah ke www/

# 3. Import database
# - Buka phpMyAdmin
# - Buat database baru: perpustakaan_online
# - Import file: database/perpustakaan_online.sql
```

### ⚙️ **Konfigurasi Database**

```php
// config/database.php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'perpustakaan_online');
?>
```

### 🎯 **Default Login**

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin123` |
| User | `user` | `user123` |

---

## 📱 Screenshots

<div align="center">

### 🏠 **Dashboard**
*Modern dashboard dengan statistik real-time*

### 📚 **Book Management**
*Interface intuitif untuk mengelola koleksi buku*

### 👥 **User Management**
*Sistem manajemen pengguna yang komprehensif*

</div>

---

## 🗂️ Struktur Project

```
Perpustakaan_Online/
├── 📁 assets/
│   ├── 📁 css/           # Stylesheets
│   ├── 📁 js/            # JavaScript files
│   ├── 📁 images/        # Images & icons
│   └── 📁 uploads/       # Upload directory
├── 📁 config/
│   ├── database.php      # Database configuration
│   └── functions.php     # Helper functions
├── 📁 modules/
│   ├── 📁 auth/          # Authentication
│   ├── 📁 books/         # Book management
│   ├── 📁 users/         # User management
│   ├── 📁 transactions/  # Borrowing system
│   └── 📁 reports/       # Reports & analytics
├── 📁 database/
│   └── perpustakaan_online.sql
├── index.php             # Entry point
└── README.md
```

---

## 🤝 Contributing

Kontribusi sangat diterima! Silakan:

1. 🍴 Fork repository
2. 🌟 Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. 💫 Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. 🚀 Push ke branch (`git push origin feature/AmazingFeature`)
5. 📬 Buka Pull Request

---

## 📄 License

Project ini dilisensikan under **MIT License** - lihat file [LICENSE](LICENSE) untuk detail.

---

## 👨‍💻 Author

<div align="center">

**Rulifcode**

[![GitHub](https://img.shields.io/badge/-GitHub-181717?style=flat-square&logo=github)](https://github.com/rulifcode)
[![LinkedIn](https://img.shields.io/badge/-LinkedIn-0077B5?style=flat-square&logo=linkedin&logoColor=white)](https://linkedin.com/in/ruliffadrian)
[![Instagram](https://img.shields.io/badge/-Instagram-E4405F?style=flat-square&logo=instagram&logoColor=white)](https://instagram.com/ruliffadrian)

*"Code with passion, build with purpose"*

</div>

---

<div align="center">

### ⭐ **Jika project ini membantu, berikan star ya!** ⭐

**Made with ❤️ in Indonesia**

</div>
