-- Update Database Schema untuk Admin System
-- Indonesian PDF Letter Generator

-- 1. Tambah kolom role ke tabel users
ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' AFTER email_verified;

-- 2. Update user admin yang sudah ada (ganti 'admin@lettergen.com' dengan email admin yang sesuai)
UPDATE users SET role = 'admin' WHERE email = 'admin@lettergen.com' OR username = 'admin';

-- 3. Buat tabel admin_logs untuk tracking admin activities
CREATE TABLE admin_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50), -- 'user', 'letter', 'template', 'system'
    target_id INT,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin_logs_admin_id (admin_id),
    INDEX idx_admin_logs_action (action),
    INDEX idx_admin_logs_created_at (created_at)
);

-- 4. Buat tabel system_settings untuk konfigurasi aplikasi
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

-- 5. Insert default system settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description) VALUES
('maintenance_mode', 'false', 'boolean', 'Enable/disable maintenance mode'),
('registration_enabled', 'true', 'boolean', 'Allow new user registration'),
('max_file_size', '5242880', 'integer', 'Maximum file upload size in bytes (5MB)'),
('session_timeout', '86400', 'integer', 'Session timeout in seconds (24 hours)'),
('admin_email', 'admin@lettergen.com', 'string', 'Administrator email address'),
('site_name', 'Indonesian PDF Letter Generator', 'string', 'Website name'),
('smtp_enabled', 'false', 'boolean', 'Enable SMTP email sending'),
('smtp_host', '', 'string', 'SMTP server host'),
('smtp_port', '587', 'integer', 'SMTP server port'),
('smtp_username', '', 'string', 'SMTP username'),
('smtp_password', '', 'string', 'SMTP password'),
('backup_enabled', 'true', 'boolean', 'Enable automatic database backup'),
('backup_frequency', 'daily', 'string', 'Backup frequency (daily, weekly, monthly)');

-- 6. Buat tabel global_templates untuk template yang bisa digunakan semua user
CREATE TABLE global_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_type ENUM('pernyataan', 'izin', 'kuasa') NOT NULL,
    template_name VARCHAR(100) NOT NULL,
    template_data JSON NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_global_templates_type (template_type),
    INDEX idx_global_templates_active (is_active)
);

-- 7. Insert sample global templates
INSERT INTO global_templates (template_type, template_name, template_data, description, created_by) VALUES
('pernyataan', 'Template Pernyataan Umum', '{"nama": "", "tempatTanggalLahir": "", "alamat": "", "pernyataan": "Saya menyatakan bahwa informasi yang saya berikan adalah benar dan dapat dipertanggungjawabkan.", "tanggal": ""}', 'Template standar untuk surat pernyataan umum', 1),
('izin', 'Template Izin Kerja', '{"nama": "", "jabatan": "", "instansi": "", "alamatPenerima": "Manager HRD", "instansiPenerima": "", "keperluan": "keperluan pribadi", "tanggalMulai": "", "tanggalSelesai": "", "tanggal": ""}', 'Template standar untuk surat izin kerja', 1),
('kuasa', 'Template Kuasa Umum', '{"pemberi": "", "penerima": "", "keperluan": "mengurus dokumen administrasi", "tanggal": ""}', 'Template standar untuk surat kuasa umum', 1);

-- 8. Buat tabel error_logs untuk system error tracking
CREATE TABLE error_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    error_type VARCHAR(50) NOT NULL,
    error_message TEXT NOT NULL,
    file_path VARCHAR(255),
    line_number INT,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    stack_trace TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_error_logs_type (error_type),
    INDEX idx_error_logs_created_at (created_at)
);

-- 9. Buat view untuk admin dashboard statistics
CREATE VIEW admin_dashboard_stats AS
SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
    (SELECT COUNT(*) FROM users WHERE role = 'admin') as total_admins,
    (SELECT COUNT(*) FROM generated_letters) as total_letters,
    (SELECT COUNT(*) FROM saved_templates) as total_user_templates,
    (SELECT COUNT(*) FROM global_templates WHERE is_active = 1) as total_global_templates,
    (SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()) as new_users_today,
    (SELECT COUNT(*) FROM generated_letters WHERE DATE(created_at) = CURDATE()) as letters_today,
    (SELECT COUNT(*) FROM activity_logs WHERE DATE(created_at) = CURDATE()) as activities_today;

-- 10. Update indexes untuk performance
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_last_login ON users(last_login);
CREATE INDEX idx_generated_letters_created_at ON generated_letters(created_at);
CREATE INDEX idx_activity_logs_action ON activity_logs(action);

-- 11. Buat stored procedure untuk cleanup old logs
DELIMITER //
CREATE PROCEDURE CleanupOldLogs()
BEGIN
    -- Hapus activity logs lebih dari 6 bulan
    DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);
    
    -- Hapus error logs lebih dari 3 bulan
    DELETE FROM error_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 3 MONTH);
    
    -- Hapus admin logs lebih dari 1 tahun
    DELETE FROM admin_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
END //
DELIMITER ;

-- 12. Buat event untuk auto cleanup (optional, perlu SUPER privilege)
-- CREATE EVENT auto_cleanup_logs
-- ON SCHEDULE EVERY 1 WEEK
-- DO CALL CleanupOldLogs();
