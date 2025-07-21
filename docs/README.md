# 📄 Indonesian PDF Letter Generator

> **Sistem Pembuat Surat PDF Profesional untuk Indonesia**

Aplikasi web modern yang memungkinkan pengguna membuat surat resmi dalam format PDF dengan template yang telah disesuaikan untuk standar surat-menyurat Indonesia.

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?logo=javascript&logoColor=black)

## ✨ **Fitur Utama**

### 🎯 **Core Features**

- **📝 Template Surat Profesional** - Template surat resmi sesuai standar Indonesia
- **🔐 Sistem Autentikasi** - Login dan registrasi pengguna yang aman
- **📄 Generate PDF** - Konversi surat ke format PDF berkualitas tinggi
- **💾 Manajemen Surat** - Simpan, edit, dan kelola surat yang telah dibuat
- **👤 Profil Pengguna** - Kelola informasi pribadi dan organisasi
- **📱 Responsive Design** - Tampilan optimal di semua perangkat

### 🎨 **Design Features**

- **Modern Glass Morphism UI** - Antarmuka modern dengan efek kaca
- **Indonesian Color Palette** - Skema warna yang sesuai dengan identitas Indonesia
- **Dark Theme Professional** - Tema gelap yang elegan dan profesional
- **High Contrast Accessibility** - Kontras tinggi untuk kemudahan baca
- **Smooth Animations** - Animasi halus untuk pengalaman pengguna yang baik

## 🚀 **Quick Start**

### **Prerequisites**

- PHP 8.0 atau lebih tinggi
- MySQL 8.0 atau lebih tinggi
- Web server (Apache/Nginx)
- Composer (untuk dependency management)

### **Installation**

1. **Clone Repository**

```bash
git clone https://github.com/yourusername/indonesian-pdf-letter-generator.git
cd indonesian-pdf-letter-generator
```

2. **Setup Database**

```bash
# Buat database MySQL
mysql -u root -p
CREATE DATABASE surat_generator;
```

3. **Import Database Schema**

```bash
mysql -u root -p surat_generator < database/schema.sql
```

4. **Configure Environment**

```bash
# Copy dan edit file konfigurasi
cp config/config.example.php config/config.php
```

5. **Edit Database Configuration**

```php
// config/config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'surat_generator');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

6. **Install Dependencies**

```bash
composer install
```

7. **Set Permissions**

```bash
chmod 755 uploads/
chmod 755 temp/
```

8. **Start Development Server**

```bash
php -S localhost:8000
```

9. **Access Application**

```
http://localhost:8000
```

## 📁 **Struktur Proyek**

```
indonesian-pdf-letter-generator/
├── 📁 auth/                    # Sistem autentikasi
│   ├── login.php              # Halaman login
│   ├── register.php           # Halaman registrasi
│   └── logout.php             # Proses logout
├── 📁 assets/                 # Asset statis
│   ├── 📁 css/               # Stylesheet
│   ├── 📁 js/                # JavaScript files
│   └── 📁 images/            # Gambar dan ikon
├── 📁 config/                 # Konfigurasi aplikasi
│   ├── config.php            # Konfigurasi database
│   └── database.php          # Koneksi database
├── 📁 includes/               # File include
│   ├── header.php            # Header template
│   ├── footer.php            # Footer template
│   └── functions.php         # Fungsi utility
├── 📁 templates/              # Template surat
│   ├── formal_letter.php     # Template surat resmi
│   ├── business_letter.php   # Template surat bisnis
│   └── personal_letter.php   # Template surat pribadi
├── 📁 uploads/                # File upload pengguna
├── 📁 temp/                   # File temporary
├── 📁 database/               # Database schema
│   └── schema.sql            # SQL schema
├── home.php                   # Halaman utama
├── profile.php               # Halaman profil
├── create-letter.php         # Buat surat baru
├── my-letters.php            # Daftar surat pengguna
└── README.md                 # Dokumentasi ini
```

## 🎨 **Design System**

### **Color Palette**

```css
/* Indonesian Professional Color Scheme */
--primary-blue: #1e40af; /* Primary actions */
--primary-navy: #1e293b; /* Headers & navigation */
--dark-navy: #0f172a; /* Background base */
--indonesian-red: #b91c1c; /* Accent & alerts */
--golden-yellow: #d97706; /* Highlights & CTAs */
--slate-gray: #334155; /* Text primary */
--text-primary: #f8fafc; /* Light text */
--text-secondary: #e2e8f0; /* Secondary text */
```

### **Typography**

- **Primary Font**: Inter (modern, readable)
- **Heading Weights**: 700-900 (Bold to Black)
- **Body Weights**: 400-600 (Regular to Semibold)
- **Font Sizes**: Responsive scale (14px - 48px)

### **Components**

- **Glass Morphism Cards** - Backdrop blur dengan transparency
- **Gradient Buttons** - Multi-color gradients dengan hover effects
- **Modern Form Inputs** - High contrast dengan rounded corners
- **Animated Icons** - FontAwesome dengan custom animations

## 🔧 **Konfigurasi**

### **Database Configuration**

```php
// config/config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'surat_generator');
define('DB_USER', 'username');
define('DB_PASS', 'password');
define('DB_CHARSET', 'utf8mb4');
```

### **Application Settings**

```php
// config/config.php
define('APP_NAME', 'Indonesian PDF Letter Generator');
define('APP_VERSION', '1.0.0');
define('UPLOAD_MAX_SIZE', '5MB');
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf']);
```

## 📚 **API Documentation**

### **Authentication Endpoints**

```php
POST /auth/login.php          # User login
POST /auth/register.php       # User registration
GET  /auth/logout.php         # User logout
```

### **Letter Management**

```php
GET    /my-letters.php        # Get user letters
POST   /create-letter.php     # Create new letter
PUT    /edit-letter.php       # Update letter
DELETE /delete-letter.php     # Delete letter
GET    /download-pdf.php      # Download PDF
```

### **User Profile**

```php
GET  /profile.php             # Get user profile
POST /profile.php             # Update profile
POST /upload-avatar.php       # Upload profile picture
```

## 🧪 **Testing**

### **Manual Testing**

1. **Authentication Flow**

   - Registrasi pengguna baru
   - Login dengan kredensial valid
   - Logout dan session management

2. **Letter Creation**

   - Buat surat dengan template berbeda
   - Generate PDF dan verifikasi output
   - Simpan dan edit surat yang ada

3. **Responsive Design**
   - Test di berbagai ukuran layar
   - Verifikasi mobile compatibility
   - Check touch interactions

### **Browser Compatibility**

- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

## 🚀 **Deployment**

### **Production Setup**

1. **Server Requirements**

   - PHP 8.0+ dengan extensions: PDO, GD, mbstring
   - MySQL 8.0+ atau MariaDB 10.5+
   - SSL Certificate untuk HTTPS
   - Minimum 1GB RAM, 10GB storage

2. **Environment Configuration**

```bash
# Set production environment
export APP_ENV=production
export DB_HOST=your_production_host
export DB_NAME=your_production_db
```

3. **Security Hardening**
   - Enable HTTPS
   - Set secure session cookies
   - Configure proper file permissions
   - Enable SQL injection protection

## 🤝 **Contributing**

Kami menyambut kontribusi dari komunitas! Silakan ikuti panduan berikut:

1. **Fork** repository ini
2. **Create** feature branch (`git checkout -b feature/AmazingFeature`)
3. **Commit** perubahan (`git commit -m 'Add some AmazingFeature'`)
4. **Push** ke branch (`git push origin feature/AmazingFeature`)
5. **Open** Pull Request

### **Development Guidelines**

- Ikuti PSR-12 coding standards untuk PHP
- Gunakan semantic commit messages
- Tambahkan tests untuk fitur baru
- Update dokumentasi jika diperlukan

## 📄 **License**

Distributed under the MIT License. See `LICENSE` for more information.

## 👥 **Team**

- **Lead Developer** - [Your Name](https://github.com/yourusername)
- **UI/UX Designer** - [Designer Name](https://github.com/designerusername)
- **Backend Developer** - [Backend Dev](https://github.com/backendusername)

## 📞 **Support**

Jika Anda mengalami masalah atau memiliki pertanyaan:

- 📧 **Email**: support@lettergen.id
- 🐛 **Bug Reports**: [GitHub Issues](https://github.com/yourusername/indonesian-pdf-letter-generator/issues)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/yourusername/indonesian-pdf-letter-generator/discussions)
- 📖 **Documentation**: [Wiki](https://github.com/yourusername/indonesian-pdf-letter-generator/wiki)

## 🙏 **Acknowledgments**

- [FontAwesome](https://fontawesome.com/) untuk icon library
- [Tailwind CSS](https://tailwindcss.com/) untuk utility classes
- [TCPDF](https://tcpdf.org/) untuk PDF generation
- [Inter Font](https://rsms.me/inter/) untuk typography

---

<div align="center">

**⭐ Jika project ini membantu Anda, berikan star di GitHub! ⭐**

Made with ❤️ for Indonesian community

</div>
