<?php
/**
 * File-based Email System for Development
 * Saves emails to files instead of sending them
 */

function sendFileEmail($to, $subject, $message, $headers = "") {
    $email_dir = __DIR__ . "/../emails/";
    if (!is_dir($email_dir)) {
        mkdir($email_dir, 0777, true);
    }
    
    $timestamp = date("Y-m-d_H-i-s");
    $filename = $email_dir . "email_" . $timestamp . "_" . uniqid() . ".html";
    
    // Extract reset link from message if it's a password reset email
    $reset_link = '';
    if (preg_match('/href=["\']([^"\']*reset-password[^"\']*)["\']/', $message, $matches)) {
        $reset_link = $matches[1];
    }
    
    $email_content = "<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>$subject</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: #4F46E5; color: white; padding: 15px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px; }
        .link-highlight { background: #FEF3C7; border: 2px solid #F59E0B; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
        .link { font-size: 14px; font-weight: bold; color: #4F46E5; word-break: break-all; }
        .meta { background: #F3F4F6; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .content { border: 1px solid #E5E7EB; padding: 20px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>📧 Development Email</h1>
            <p>Email yang akan dikirim ke: <strong>$to</strong></p>
        </div>
        
        <div class='meta'>
            <h3>📋 Email Details</h3>
            <p><strong>To:</strong> $to</p>
            <p><strong>Subject:</strong> $subject</p>
            <p><strong>Timestamp:</strong> " . date('d M Y H:i:s') . "</p>
            <p><strong>File:</strong> " . basename($filename) . "</p>";
    
    if ($reset_link) {
        $email_content .= "
            <div class='link-highlight'>
                <h3>🔐 Password Reset Link Detected</h3>
                <div class='link'>$reset_link</div>
                <p><small>Click this link to reset password (for testing)</small></p>
                <a href='$reset_link' target='_blank' style='display: inline-block; background: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; margin-top: 10px;'>
                    Open Reset Link
                </a>
            </div>";
    }
    
    $email_content .= "
        </div>
        
        <div class='content'>
            <h3>📨 Email Content</h3>
            $message
        </div>
        
        <div style='margin-top: 20px; padding: 15px; background: #DBEAFE; border-radius: 8px; text-align: center;'>
            <p><strong>💡 Development Mode:</strong> Email disimpan ke file, tidak dikirim ke email asli.</p>
            <p><small>Untuk production, configure SMTP server yang sebenarnya.</small></p>
        </div>
    </div>
</body>
</html>";
    
    $result = file_put_contents($filename, $email_content);
    
    if ($result !== false) {
        // Log the email for debugging
        error_log("File email saved: $filename (Reset link: $reset_link)");
        return true;
    } else {
        error_log("Failed to save file email: $filename");
        return false;
    }
}

/**
 * Override mail() function for development
 */
function dev_mail($to, $subject, $message, $headers = '') {
    return sendFileEmail($to, $subject, $message, $headers);
}

/**
 * Get list of saved emails
 */
function getEmailFiles() {
    $email_dir = __DIR__ . "/../emails/";
    if (!is_dir($email_dir)) {
        return [];
    }
    
    $files = glob($email_dir . "email_*.html");
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a); // Sort by newest first
    });
    
    return $files;
}

/**
 * Extract reset link from email file
 */
function extractResetLinkFromEmailFile($filename) {
    if (!file_exists($filename)) {
        return null;
    }
    
    $content = file_get_contents($filename);
    
    // Try to find reset link
    if (preg_match('/href=["\']([^"\']*reset-password[^"\']*)["\']/', $content, $matches)) {
        return $matches[1];
    }
    
    return null;
}
?>
