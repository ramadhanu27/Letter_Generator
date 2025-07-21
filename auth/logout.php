<?php

/**
 * Logout functionality
 * Indonesian PDF Letter Generator
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/User.php';

// Logout user
User::logout();

// Redirect to login page
header('Location: /surat/login');
exit;
