<?php

/**
 * Password Reset Class
 * Indonesian PDF Letter Generator
 */

require_once __DIR__ . '/../../config/database.php';

class PasswordReset
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    /**
     * Generate and send password reset token
     */
    public function sendResetToken($email)
    {
        try {
            // Check if user exists
            $stmt = $this->conn->prepare("
                SELECT id, username, full_name, email, is_active 
                FROM users 
                WHERE email = ? AND is_active = 1
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                $this->logResetAttempt($email, 'request', false, 'User not found or inactive');
                return [
                    'success' => false,
                    'message' => 'Jika email terdaftar, link reset password akan dikirim.'
                ];
            }

            // Check for recent reset requests (rate limiting)
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count 
                FROM password_reset_tokens 
                WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            ");
            $stmt->execute([$user['id']]);
            $recent_requests = $stmt->fetch()['count'];

            if ($recent_requests > 0) {
                $this->logResetAttempt($email, 'request', false, 'Rate limit exceeded');
                return [
                    'success' => false,
                    'message' => 'Permintaan reset password terlalu sering. Silakan tunggu 5 menit.'
                ];
            }

            // Generate secure token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store token in database
            $stmt = $this->conn->prepare("
                INSERT INTO password_reset_tokens 
                (user_id, email, token, expires_at, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

            $stmt->execute([
                $user['id'],
                $email,
                $token,
                $expires_at,
                $ip_address,
                $user_agent
            ]);

            // Send reset email
            $reset_link = $this->generateResetLink($token);
            $email_sent = $this->sendResetEmail($user, $reset_link, $token);

            if ($email_sent) {
                $this->logResetAttempt($email, 'request', true, 'Reset token sent successfully');
                return [
                    'success' => true,
                    'message' => 'Link reset password telah dikirim ke email Anda.'
                ];
            } else {
                $this->logResetAttempt($email, 'request', false, 'Failed to send email');
                return [
                    'success' => false,
                    'message' => 'Gagal mengirim email. Silakan coba lagi.'
                ];
            }
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            $this->logResetAttempt($email, 'request', false, 'System error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ];
        }
    }

    /**
     * Verify reset token
     */
    public function verifyToken($token)
    {
        try {
            $stmt = $this->conn->prepare("
                SELECT prt.*, u.username, u.full_name, u.email 
                FROM password_reset_tokens prt
                JOIN users u ON prt.user_id = u.id
                WHERE prt.token = ? AND prt.used = 0 AND prt.expires_at > NOW()
            ");
            $stmt->execute([$token]);
            $reset_data = $stmt->fetch();

            if (!$reset_data) {
                $this->logResetAttempt('', 'invalid', false, 'Invalid or expired token');
                return [
                    'success' => false,
                    'message' => 'Token reset password tidak valid atau sudah expired.'
                ];
            }

            return [
                'success' => true,
                'data' => $reset_data
            ];
        } catch (Exception $e) {
            error_log("Token verification error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.'
            ];
        }
    }

    /**
     * Reset password with token
     */
    public function resetPassword($token, $new_password)
    {
        try {
            // Verify token first
            $verification = $this->verifyToken($token);
            if (!$verification['success']) {
                return $verification;
            }

            $reset_data = $verification['data'];

            // Validate password strength
            if (strlen($new_password) < 6) {
                return [
                    'success' => false,
                    'message' => 'Password minimal 6 karakter.'
                ];
            }

            // Hash new password
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

            // Update user password
            $stmt = $this->conn->prepare("
                UPDATE users 
                SET password_hash = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$password_hash, $reset_data['user_id']]);

            // Mark token as used
            $stmt = $this->conn->prepare("
                UPDATE password_reset_tokens 
                SET used = 1, used_at = NOW() 
                WHERE token = ?
            ");
            $stmt->execute([$token]);

            $this->logResetAttempt($reset_data['email'], 'reset', true, 'Password reset successful');

            return [
                'success' => true,
                'message' => 'Password berhasil direset. Silakan login dengan password baru.'
            ];
        } catch (Exception $e) {
            error_log("Password reset error: " . $e->getMessage());
            $this->logResetAttempt('', 'reset', false, 'System error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ];
        }
    }

    /**
     * Generate reset link
     */
    private function generateResetLink($token)
    {
        $base_url = $this->getBaseUrl();
        return $base_url . '/reset-password?token=' . $token;
    }

    /**
     * Get base URL
     */
    private function getBaseUrl()
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        return $protocol . '://' . $host . $path;
    }

    /**
     * Send reset email
     */
    private function sendResetEmail($user, $reset_link, $token)
    {
        $subject = 'Reset Password - Indonesian PDF Letter Generator';

        $message = $this->getEmailTemplate($user, $reset_link);

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: Indonesian PDF Letter Generator <noreply@lettergen.com>',
            'Reply-To: noreply@lettergen.com',
            'X-Mailer: PHP/' . phpversion()
        ];

        // Try to send email, fallback to file-based email for development
        $mail_sent = false;

        if (function_exists('mail')) {
            $mail_sent = mail($user['email'], $subject, $message, implode("\r\n", $headers));
        }

        // If mail() fails, try file-based email for development
        if (!$mail_sent) {
            $file_mail_path = __DIR__ . '/../includes/file_mail.php';
            if (file_exists($file_mail_path)) {
                require_once $file_mail_path;
                $mail_sent = sendFileEmail($user['email'], $subject, $message, implode("\r\n", $headers));
                error_log("Password reset email sent via file-based system. Token: $token, Email: {$user['email']}");
            }
        }

        return $mail_sent;
    }

    /**
     * Get email template
     */
    private function getEmailTemplate($user, $reset_link)
    {
        return "
        <html>
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
            <div class='container'>
                <div class='header'>
                    <h1>Indonesian PDF Letter Generator</h1>
                    <p>🔐 Reset Password</p>
                </div>
                <div class='content'>
                    <p>Halo <strong>{$user['full_name']}</strong>,</p>
                    <p>Kami menerima permintaan untuk reset password akun Anda. Klik tombol di bawah untuk reset password:</p>
                    
                    <div style='text-align: center;'>
                        <a href='$reset_link' class='button'>Reset Password</a>
                    </div>
                    
                    <p>Atau copy dan paste link berikut ke browser Anda:</p>
                    <p style='word-break: break-all; background: #f0f0f0; padding: 10px; border-radius: 4px;'>$reset_link</p>
                    
                    <div class='warning'>
                        <strong>⚠️ Penting:</strong><br>
                        • Link ini akan expired dalam 1 jam<br>
                        • Jika Anda tidak meminta reset password, abaikan email ini<br>
                        • Jangan bagikan link ini kepada siapa pun
                    </div>
                    
                    <p><strong>📋 Detail Permintaan:</strong></p>
                    <ul>
                        <li><strong>Waktu:</strong> " . date('d M Y H:i:s') . " WIB</li>
                        <li><strong>IP Address:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "</li>
                        <li><strong>Browser:</strong> " . substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 50) . "...</li>
                    </ul>
                    
                    <p style='margin-top: 30px; font-size: 12px; color: #666;'>
                        Email ini dikirim otomatis oleh sistem. Mohon jangan membalas email ini.
                    </p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Log reset attempt
     */
    private function logResetAttempt($email, $action, $success, $message)
    {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO password_reset_logs 
                (email, action, ip_address, user_agent, success, message) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

            $stmt->execute([
                $email,
                $action,
                $ip_address,
                $user_agent,
                $success ? 1 : 0,
                $message
            ]);
        } catch (Exception $e) {
            error_log("Failed to log reset attempt: " . $e->getMessage());
        }
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens()
    {
        try {
            $stmt = $this->conn->prepare("
                DELETE FROM password_reset_tokens 
                WHERE expires_at < NOW() OR used = 1
            ");
            $stmt->execute();

            return $stmt->rowCount();
        } catch (Exception $e) {
            error_log("Failed to cleanup expired tokens: " . $e->getMessage());
            return 0;
        }
    }
}
