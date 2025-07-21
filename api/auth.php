<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/User.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'login':
            if ($method !== 'POST') {
                errorResponse('Method not allowed', 405);
            }
            handleLogin();
            break;

        case 'register':
            if ($method !== 'POST') {
                errorResponse('Method not allowed', 405);
            }
            handleRegister();
            break;

        case 'logout':
            if ($method !== 'POST') {
                errorResponse('Method not allowed', 405);
            }
            handleLogout();
            break;

        case 'check':
            if ($method !== 'GET') {
                errorResponse('Method not allowed', 405);
            }
            handleCheckAuth();
            break;

        case 'profile':
            if ($method === 'GET') {
                handleGetProfile();
            } elseif ($method === 'POST') {
                handleUpdateProfile();
            } else {
                errorResponse('Method not allowed', 405);
            }
            break;

        case 'change-password':
            if ($method !== 'POST') {
                errorResponse('Method not allowed', 405);
            }
            handleChangePassword();
            break;

        default:
            errorResponse('Invalid action', 400);
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    errorResponse('Internal server error', 500);
}

function handleLogin()
{
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        $input = $_POST;
    }

    $email_or_username = sanitizeInput($input['email_or_username'] ?? '');
    $password = $input['password'] ?? '';
    $remember_me = $input['remember_me'] ?? false;

    if (empty($email_or_username) || empty($password)) {
        errorResponse('Email/username dan password wajib diisi');
    }

    $user = new User();
    $result = $user->login($email_or_username, $password, $remember_me);

    if ($result['success']) {
        successResponse('Login berhasil', $result);
    } else {
        errorResponse($result['message'], 401);
    }
}

function handleRegister()
{
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        $input = $_POST;
    }

    $required_fields = ['username', 'email', 'password', 'full_name'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            errorResponse("Field $field wajib diisi");
        }
    }

    $data = [
        'username' => sanitizeInput($input['username']),
        'email' => sanitizeInput($input['email']),
        'password' => $input['password'],
        'confirm_password' => $input['confirm_password'] ?? '',
        'full_name' => sanitizeInput($input['full_name']),
        'phone' => sanitizeInput($input['phone'] ?? ''),
        'organization' => sanitizeInput($input['organization'] ?? ''),
        'position' => sanitizeInput($input['position'] ?? ''),
        'address' => sanitizeInput($input['address'] ?? ''),
        'city' => sanitizeInput($input['city'] ?? ''),
        'province' => sanitizeInput($input['province'] ?? '')
    ];

    $user = new User();
    $result = $user->register($data);

    if ($result['success']) {
        successResponse($result['message'], ['user_id' => $result['user_id']]);
    } else {
        if (isset($result['errors'])) {
            errorResponse('Validation failed', 422, ['errors' => $result['errors']]);
        } else {
            errorResponse($result['message']);
        }
    }
}

function handleLogout()
{
    $user = new User();
    $result = $user->logout();

    if ($result['success']) {
        successResponse($result['message']);
    } else {
        errorResponse($result['message']);
    }
}

function handleCheckAuth()
{
    if (User::isLoggedIn()) {
        $current_user = User::getCurrentUser();
        successResponse('User is authenticated', ['user' => $current_user]);
    } else {
        errorResponse('User not authenticated', 401);
    }
}

function handleGetProfile()
{
    if (!User::isLoggedIn()) {
        errorResponse('User not authenticated', 401);
    }

    $current_user = User::getCurrentUser();
    $user = new User();
    $user_data = $user->getUserById($current_user['id']);

    if ($user_data) {
        // Remove sensitive data
        unset($user_data['password_hash']);
        unset($user_data['verification_token']);
        unset($user_data['reset_token']);

        successResponse('Profile retrieved successfully', ['user' => $user_data]);
    } else {
        errorResponse('User not found', 404);
    }
}

function handleUpdateProfile()
{
    if (!User::isLoggedIn()) {
        errorResponse('User not authenticated', 401);
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        $input = $_POST;
    }

    $current_user = User::getCurrentUser();
    $user = new User();

    $data = [
        'full_name' => sanitizeInput($input['full_name'] ?? ''),
        'phone' => sanitizeInput($input['phone'] ?? ''),
        'organization' => sanitizeInput($input['organization'] ?? ''),
        'position' => sanitizeInput($input['position'] ?? ''),
        'address' => sanitizeInput($input['address'] ?? ''),
        'city' => sanitizeInput($input['city'] ?? ''),
        'province' => sanitizeInput($input['province'] ?? ''),
        'postal_code' => sanitizeInput($input['postal_code'] ?? '')
    ];

    $result = $user->updateProfile($current_user['id'], $data);

    if ($result['success']) {
        successResponse($result['message']);
    } else {
        errorResponse($result['message']);
    }
}

function handleChangePassword()
{
    if (!User::isLoggedIn()) {
        errorResponse('User not authenticated', 401);
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        $input = $_POST;
    }

    $current_password = $input['current_password'] ?? '';
    $new_password = $input['new_password'] ?? '';
    $confirm_password = $input['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        errorResponse('Semua field password wajib diisi');
    }

    if ($new_password !== $confirm_password) {
        errorResponse('Password baru dan konfirmasi tidak cocok');
    }

    $current_user = User::getCurrentUser();
    $user = new User();

    $result = $user->changePassword($current_user['id'], $current_password, $new_password);

    if ($result['success']) {
        successResponse($result['message']);
    } else {
        errorResponse($result['message']);
    }
}

// Enhanced error response function
function errorResponse($message, $code = 400, $additional_data = [])
{
    $response = [
        'success' => false,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    if (!empty($additional_data)) {
        $response = array_merge($response, $additional_data);
    }

    http_response_code($code);
    echo json_encode($response);
    exit;
}

// Enhanced success response function
function successResponse($message, $data = null)
{
    $response = [
        'success' => true,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];

    if ($data !== null) {
        $response['data'] = $data;
    }

    http_response_code(200);
    echo json_encode($response);
    exit;
}
