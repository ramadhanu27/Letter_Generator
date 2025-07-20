-- Password Reset Schema for Indonesian PDF Letter Generator
-- Created: 2024

-- Table for storing password reset tokens
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    used_at TIMESTAMP NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_email (email),
    INDEX idx_expires (expires_at),
    INDEX idx_user_id (user_id)
);

-- Table for logging password reset attempts
CREATE TABLE IF NOT EXISTS password_reset_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    action ENUM('request', 'reset', 'expired', 'invalid') NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    success TINYINT(1) DEFAULT 0,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
);

-- Event to automatically clean up expired tokens (runs every hour)
DELIMITER $$
CREATE EVENT IF NOT EXISTS cleanup_expired_reset_tokens
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    DELETE FROM password_reset_tokens 
    WHERE expires_at < NOW() OR used = 1;
END$$
DELIMITER ;

-- Insert default email template for password reset
INSERT INTO email_templates (name, subject, body, variables, is_active, created_at) 
VALUES (
    'password_reset',
    'Reset Password - Indonesian PDF Letter Generator',
    '<html>
<head>
    <title>Reset Password</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; background: #4F46E5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
        .warning { background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Indonesian PDF Letter Generator</h1>
            <p>Reset Password</p>
        </div>
        <div class="content">
            <p>Halo <strong>{{user_name}}</strong>,</p>
            <p>Kami menerima permintaan untuk reset password akun Anda. Klik tombol di bawah untuk reset password:</p>
            
            <div style="text-align: center;">
                <a href="{{reset_link}}" class="button">Reset Password</a>
            </div>
            
            <p>Atau copy dan paste link berikut ke browser Anda:</p>
            <p style="word-break: break-all; background: #f0f0f0; padding: 10px; border-radius: 4px;">{{reset_link}}</p>
            
            <div class="warning">
                <strong>Penting:</strong><br>
                • Link ini akan expired dalam 1 jam<br>
                • Jika Anda tidak meminta reset password, abaikan email ini<br>
                • Jangan bagikan link ini kepada siapa pun
            </div>
            
            <p><strong>Detail Permintaan:</strong></p>
            <ul>
                <li>Waktu: {{request_time}}</li>
                <li>IP Address: {{ip_address}}</li>
            </ul>
        </div>
    </div>
</body>
</html>',
    'user_name,reset_link,request_time,ip_address',
    1,
    NOW()
) ON DUPLICATE KEY UPDATE 
    subject = VALUES(subject),
    body = VALUES(body),
    variables = VALUES(variables),
    updated_at = NOW();
