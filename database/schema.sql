-- Database Schema untuk Indonesian PDF Letter Generator
-- Database: letter_generator_db

CREATE DATABASE IF NOT EXISTS letter_generator_db;
USE letter_generator_db;

-- Tabel Users untuk autentikasi
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    organization VARCHAR(100),
    position VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(255) NULL,
    reset_token VARCHAR(255) NULL,
    reset_token_expires TIMESTAMP NULL
);

-- Tabel User Profiles untuk informasi tambahan
CREATE TABLE user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    avatar_url VARCHAR(255),
    address TEXT,
    city VARCHAR(50),
    province VARCHAR(50),
    postal_code VARCHAR(10),
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other'),
    bio TEXT,
    preferences JSON,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel Saved Templates untuk menyimpan template yang sering digunakan
CREATE TABLE saved_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    template_type ENUM('pernyataan', 'izin', 'kuasa') NOT NULL,
    template_name VARCHAR(100) NOT NULL,
    template_data JSON NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel Generated Letters untuk history surat yang dibuat
CREATE TABLE generated_letters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    letter_type ENUM('pernyataan', 'izin', 'kuasa') NOT NULL,
    letter_title VARCHAR(200) NOT NULL,
    letter_data JSON NOT NULL,
    pdf_filename VARCHAR(255),
    file_size INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel Sessions untuk manajemen sesi login
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Tabel Activity Logs untuk tracking aktivitas user
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes untuk performa
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_created_at ON users(created_at);
CREATE INDEX idx_saved_templates_user_id ON saved_templates(user_id);
CREATE INDEX idx_generated_letters_user_id ON generated_letters(user_id);
CREATE INDEX idx_generated_letters_created_at ON generated_letters(created_at);
CREATE INDEX idx_user_sessions_token ON user_sessions(session_token);
CREATE INDEX idx_user_sessions_expires ON user_sessions(expires_at);
CREATE INDEX idx_activity_logs_user_id ON activity_logs(user_id);
CREATE INDEX idx_activity_logs_created_at ON activity_logs(created_at);

-- Sample data untuk testing
INSERT INTO users (username, email, password_hash, full_name, phone, organization, position) VALUES
('admin', 'admin@lettergen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '081234567890', 'Letter Generator Inc', 'System Administrator'),
('demo_user', 'demo@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Demo User', '081234567891', 'PT Demo Company', 'Staff');

-- Sample user profiles
INSERT INTO user_profiles (user_id, address, city, province, postal_code, preferences) VALUES
(1, 'Jl. Admin No. 1', 'Jakarta', 'DKI Jakarta', '12345', '{"theme": "light", "language": "id", "notifications": true}'),
(2, 'Jl. Demo No. 2', 'Bandung', 'Jawa Barat', '54321', '{"theme": "light", "language": "id", "notifications": false}');

-- Sample saved templates
INSERT INTO saved_templates (user_id, template_type, template_name, template_data, is_default) VALUES
(2, 'izin', 'Template Izin Karyawan', '{"nama": "Demo User", "jabatan": "Staff IT", "instansi": "PT Demo Company", "alamatPenerima": "Manager HRD", "instansiPenerima": "PT Demo Company"}', TRUE);
