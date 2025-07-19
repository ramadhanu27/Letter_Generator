# 🔐 Sistem Autentikasi - Indonesian PDF Letter Generator

## 📋 **Ringkasan Sistem**

Sistem autentikasi lengkap telah diimplementasikan untuk aplikasi Indonesian PDF Letter Generator dengan fitur-fitur modern dan keamanan tinggi.

## 🗄️ **Struktur Database**

### **Tabel Utama:**

#### **1. users**

```sql
- id (Primary Key)
- username (Unique)
- email (Unique)
- password_hash
- full_name
- phone
- organization
- position
- created_at, updated_at
- last_login
- is_active
- email_verified
- verification_token
- reset_token, reset_token_expires
```

#### **2. user_profiles**

```sql
- id (Primary Key)
- user_id (Foreign Key)
- avatar_url
- address, city, province, postal_code
- date_of_birth, gender
- bio
- preferences (JSON)
```

#### **3. user_sessions**

```sql
- id (Primary Key)
- user_id (Foreign Key)
- session_token (Unique)
- ip_address, user_agent
- expires_at
- created_at
```

#### **4. saved_templates**

```sql
- id (Primary Key)
- user_id (Foreign Key)
- template_type (pernyataan, izin, kuasa)
- template_name
- template_data (JSON)
- is_default
- created_at, updated_at
```

#### **5. generated_letters**

```sql
- id (Primary Key)
- user_id (Foreign Key)
- letter_type
- letter_title
- letter_data (JSON)
- pdf_filename, file_size
- created_at
```

#### **6. activity_logs**

```sql
- id (Primary Key)
- user_id (Foreign Key)
- action
- description
- ip_address, user_agent
- created_at
```

## 🔧 **Fitur Keamanan**

### **1. Password Security**

- **Hashing**: Menggunakan `password_hash()` PHP dengan algoritma bcrypt
- **Validasi**: Minimal 8 karakter dengan huruf besar, kecil, dan angka
- **Strength Checking**: Validasi kekuatan password real-time

### **2. Session Management**

- **Secure Sessions**: HttpOnly, Secure, SameSite cookies
- **Session Regeneration**: Otomatis regenerasi session ID
- **Session Timeout**: Konfigurasi timeout yang dapat disesuaikan
- **Remember Me**: Token persistent untuk login otomatis

### **3. CSRF Protection**

- **Token Generation**: Token CSRF unik untuk setiap form
- **Validation**: Validasi token pada setiap request POST
- **Expiration**: Token memiliki waktu kadaluarsa

### **4. Input Validation & Sanitization**

- **Sanitization**: Semua input dibersihkan dari karakter berbahaya
- **Validation**: Validasi email, username, dan format data
- **XSS Prevention**: Escape output untuk mencegah XSS

### **5. Rate Limiting**

- **Login Attempts**: Pembatasan percobaan login
- **Account Lockout**: Kunci akun setelah percobaan gagal berlebihan
- **IP Tracking**: Pelacakan IP untuk deteksi aktivitas mencurigakan

## 📁 **Struktur File**

```
/
├── config/
│   └── database.php          # Konfigurasi database dan security
├── classes/
│   └── User.php             # Class utama untuk autentikasi
├── api/
│   └── auth.php             # API endpoints untuk autentikasi
├── database/
│   └── schema.sql           # Schema database lengkap
├── login.php                # Halaman login
├── register.php             # Halaman registrasi
├── dashboard.php            # Dashboard user
├── app.php                  # Aplikasi utama (authenticated)
├── .htaccess               # Konfigurasi Apache security
├── .env.example            # Template environment variables
└── AUTHENTICATION_SYSTEM.md # Dokumentasi ini
```

## 🚀 **Instalasi & Setup**

### **1. Database Setup**

```bash
# Buat database
mysql -u root -p
CREATE DATABASE letter_generator_db;

# Import schema
mysql -u root -p letter_generator_db < database/schema.sql
```

### **2. Environment Configuration**

```bash
# Copy environment template
cp .env.example .env

# Edit konfigurasi database
nano .env
```

### **3. File Permissions**

```bash
# Set permissions untuk direktori logs
mkdir logs
chmod 755 logs

# Set permissions untuk uploads
mkdir uploads
chmod 755 uploads
```

### **4. Apache Configuration**

- Pastikan mod_rewrite enabled
- Copy .htaccess ke root directory
- Konfigurasi virtual host jika diperlukan

## 🔐 **Penggunaan API**

### **1. Login**

```javascript
POST /api/auth.php?action=login
Content-Type: application/json

{
    "email_or_username": "user@example.com",
    "password": "password123",
    "remember_me": true
}
```

### **2. Register**

```javascript
POST /api/auth.php?action=register
Content-Type: application/json

{
    "username": "newuser",
    "email": "user@example.com",
    "password": "password123",
    "confirm_password": "password123",
    "full_name": "John Doe",
    "phone": "081234567890",
    "organization": "PT Example",
    "position": "Manager"
}
```

### **3. Check Authentication**

```javascript
GET /api/auth.php?action=check
```

### **4. Logout**

```javascript
POST /api/auth.php?action=logout
```

### **5. Update Profile**

```javascript
POST /api/auth.php?action=profile
Content-Type: application/json

{
    "full_name": "John Doe Updated",
    "phone": "081234567890",
    "organization": "PT New Company",
    "position": "Senior Manager",
    "address": "Jl. Example No. 123",
    "city": "Jakarta",
    "province": "DKI Jakarta"
}
```

### **6. Change Password**

```javascript
POST /api/auth.php?action=change-password
Content-Type: application/json

{
    "current_password": "oldpassword",
    "new_password": "newpassword123",
    "confirm_password": "newpassword123"
}
```

## 🎯 **Flow Aplikasi**

### **1. User Registration**

1. User mengisi form registrasi
2. Validasi input di client-side dan server-side
3. Hash password dengan bcrypt
4. Simpan user ke database
5. Buat user profile default
6. Log aktivitas registrasi
7. Redirect ke halaman login dengan pesan sukses

### **2. User Login**

1. User input email/username dan password
2. Validasi input dan check rate limiting
3. Verifikasi password dengan hash di database
4. Buat session dan session token
5. Set session variables
6. Log aktivitas login
7. Redirect ke dashboard

### **3. Protected Pages**

1. Check session validity
2. Verify session token di database
3. Load user data
4. Regenerate session jika diperlukan
5. Allow access atau redirect ke login

### **4. Logout**

1. Hapus session token dari database
2. Clear session variables
3. Destroy session
4. Log aktivitas logout
5. Redirect ke halaman login

## 🛡️ **Keamanan Best Practices**

### **1. Password Security**

- Gunakan algoritma hashing yang kuat (bcrypt)
- Implementasi password strength requirements
- Tidak pernah simpan password plain text
- Implementasi password reset yang aman

### **2. Session Security**

- Gunakan secure session configuration
- Regenerate session ID secara berkala
- Implementasi session timeout
- Store session data di database untuk kontrol lebih baik

### **3. Input Validation**

- Validasi semua input di server-side
- Sanitize input untuk mencegah injection
- Gunakan prepared statements untuk database queries
- Implementasi CSRF protection

### **4. Error Handling**

- Jangan expose sensitive information di error messages
- Log semua error untuk monitoring
- Gunakan generic error messages untuk user
- Implementasi proper HTTP status codes

### **5. Monitoring & Logging**

- Log semua aktivitas autentikasi
- Monitor failed login attempts
- Track suspicious activities
- Implementasi alerting untuk security events

## 📊 **Monitoring & Analytics**

### **Activity Logs**

- User registration, login, logout
- Profile updates, password changes
- Failed login attempts
- Suspicious activities

### **Session Tracking**

- Active sessions per user
- Session duration analytics
- Device/browser tracking
- Geographic login tracking

### **Security Metrics**

- Failed login rate
- Account lockout frequency
- Password strength distribution
- Session security compliance

## 🔄 **Maintenance & Updates**

### **Regular Tasks**

- Clean expired sessions
- Archive old activity logs
- Update security configurations
- Monitor for security vulnerabilities

### **Database Maintenance**

- Regular backups
- Index optimization
- Query performance monitoring
- Data retention policies

### **Security Updates**

- Keep PHP and dependencies updated
- Monitor security advisories
- Regular security audits
- Penetration testing

## 🎨 **UI/UX Features**

### **Login Page**

- Modern gradient design dengan glass effect
- Password visibility toggle
- Remember me option
- Responsive design untuk mobile
- Loading states dan error handling

### **Registration Page**

- Multi-step form dengan validasi real-time
- Password strength indicator
- Auto-fill suggestions
- Terms and conditions agreement
- Success confirmation dengan redirect

### **Dashboard**

- Welcome section dengan user info
- Quick action cards
- Statistics overview
- Recent activity feed
- Responsive navigation

### **Security Features**

- Auto-logout pada inactivity
- Session timeout warnings
- Password change notifications
- Login activity tracking
- Device management

## 🚀 **Quick Start Guide**

### **1. Setup Database**

```bash
# Login ke MySQL
mysql -u root -p

# Buat database
CREATE DATABASE letter_generator_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Import schema
USE letter_generator_db;
SOURCE database/schema.sql;

# Verify tables
SHOW TABLES;
```

### **2. Configure Environment**

```bash
# Copy environment file
cp .env.example .env

# Edit database credentials
DB_HOST=localhost
DB_NAME=letter_generator_db
DB_USER=root
DB_PASS=your_password
```

### **3. Test Installation**

1. Akses `http://localhost/register.php`
2. Daftar akun baru
3. Login dengan akun yang dibuat
4. Akses dashboard dan aplikasi

### **4. Default Accounts**

```
Admin Account:
- Username: admin
- Email: admin@lettergen.com
- Password: password (change immediately!)

Demo Account:
- Username: demo_user
- Email: demo@example.com
- Password: password
```

## 🔧 **Troubleshooting**

### **Common Issues**

**Database Connection Failed**

- Check database credentials in .env
- Verify MySQL service is running
- Ensure database exists and accessible

**Session Issues**

- Check PHP session configuration
- Verify write permissions on session directory
- Clear browser cookies and try again

**File Upload Issues**

- Check upload_max_filesize in php.ini
- Verify uploads directory permissions
- Ensure .htaccess rules are applied

**Permission Denied**

- Set proper file permissions (755 for directories, 644 for files)
- Check Apache/Nginx user permissions
- Verify .htaccess is working

Sistem autentikasi ini memberikan foundation yang kuat dan aman untuk aplikasi Indonesian PDF Letter Generator dengan fitur-fitur modern yang diharapkan user saat ini.
