<?php
require_once 'config/database.php';
require_once 'classes/User.php';

class Admin extends User
{
    // Check if current user is admin
    public static function isAdmin()
    {
        if (!self::isLoggedIn()) {
            return false;
        }

        // Check session first for performance
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            return true;
        }

        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
            return true;
        }

        // Fallback to database check
        $current_user = self::getCurrentUser();
        if (!$current_user) {
            return false;
        }

        try {
            $database = new Database();
            $conn = $database->getConnection();

            $stmt = $conn->prepare("SELECT role FROM users WHERE id = ? AND role = 'admin' AND is_active = 1");
            $stmt->execute([$current_user['id']]);
            $user = $stmt->fetch();

            $is_admin = $user && $user['role'] === 'admin';

            // Update session if admin
            if ($is_admin) {
                $_SESSION['role'] = 'admin';
                $_SESSION['is_admin'] = true;
            }

            return $is_admin;
        } catch (Exception $e) {
            error_log("Admin check error: " . $e->getMessage());
            return false;
        }
    }

    // Require admin access
    public static function requireAdmin($redirect_url = 'admin_login.php')
    {
        if (!self::isLoggedIn()) {
            header("Location: $redirect_url");
            exit;
        }

        if (!self::isAdmin()) {
            header("Location: $redirect_url");
            exit;
        }
    }

    // Log admin activity
    public static function logActivity($action, $target_type = null, $target_id = null, $description = '')
    {
        if (!self::isAdmin()) {
            return false;
        }

        $current_user = self::getCurrentUser();

        try {
            $database = new Database();
            $conn = $database->getConnection();

            $stmt = $conn->prepare("
                INSERT INTO admin_logs (admin_id, action, target_type, target_id, description, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            return $stmt->execute([
                $current_user['id'],
                $action,
                $target_type,
                $target_id,
                $description,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);
        } catch (Exception $e) {
            error_log("Admin log error: " . $e->getMessage());
            return false;
        }
    }

    // Get dashboard statistics
    public static function getDashboardStats()
    {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            $stmt = $conn->query("SELECT * FROM admin_dashboard_stats");
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            return [
                'total_users' => 0,
                'total_admins' => 0,
                'total_letters' => 0,
                'total_user_templates' => 0,
                'total_global_templates' => 0,
                'new_users_today' => 0,
                'letters_today' => 0,
                'activities_today' => 0
            ];
        }
    }

    // Get users with pagination and filters
    public static function getUsers($page = 1, $limit = 20, $search = '', $role = '', $status = '')
    {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            $offset = ($page - 1) * $limit;
            $where_conditions = ["1=1"];
            $params = [];

            if ($search) {
                $where_conditions[] = "(full_name LIKE ? OR email LIKE ? OR username LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            if ($role) {
                $where_conditions[] = "role = ?";
                $params[] = $role;
            }

            if ($status === 'active') {
                $where_conditions[] = "is_active = 1";
            } elseif ($status === 'inactive') {
                $where_conditions[] = "is_active = 0";
            }

            $where_clause = implode(" AND ", $where_conditions);

            // Get total count
            $count_query = "SELECT COUNT(*) as total FROM users WHERE $where_clause";
            $stmt = $conn->prepare($count_query);
            $stmt->execute($params);
            $total = $stmt->fetch()['total'];

            // Get users
            $query = "
                SELECT u.*, p.city, p.province, 
                       (SELECT COUNT(*) FROM generated_letters WHERE user_id = u.id) as letter_count,
                       (SELECT COUNT(*) FROM saved_templates WHERE user_id = u.id) as template_count
                FROM users u 
                LEFT JOIN user_profiles p ON u.id = p.user_id 
                WHERE $where_clause 
                ORDER BY u.created_at DESC 
                LIMIT $limit OFFSET $offset
            ";
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $users = $stmt->fetchAll();

            return [
                'users' => $users,
                'total' => $total,
                'pages' => ceil($total / $limit),
                'current_page' => $page
            ];
        } catch (Exception $e) {
            error_log("Get users error: " . $e->getMessage());
            return ['users' => [], 'total' => 0, 'pages' => 0, 'current_page' => 1];
        }
    }

    // Update user status
    public static function updateUserStatus($user_id, $is_active)
    {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            $result = $stmt->execute([$is_active ? 1 : 0, $user_id]);

            if ($result) {
                $action = $is_active ? 'user_activated' : 'user_deactivated';
                self::logActivity($action, 'user', $user_id, "User status changed to " . ($is_active ? 'active' : 'inactive'));
            }

            return $result;
        } catch (Exception $e) {
            error_log("Update user status error: " . $e->getMessage());
            return false;
        }
    }

    // Delete user and all related data
    public static function deleteUser($user_id)
    {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            // Get user info for logging
            $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if (!$user) {
                return false;
            }

            $conn->beginTransaction();

            // Delete will cascade due to foreign key constraints
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $result = $stmt->execute([$user_id]);

            if ($result) {
                $conn->commit();
                self::logActivity('user_deleted', 'user', $user_id, "Deleted user: {$user['username']} ({$user['email']})");
                return true;
            } else {
                $conn->rollBack();
                return false;
            }
        } catch (Exception $e) {
            if (isset($conn)) {
                $conn->rollBack();
            }
            error_log("Delete user error: " . $e->getMessage());
            return false;
        }
    }

    // Reset user password
    public static function resetUserPassword($user_id, $new_password)
    {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            $password_hash = hashPassword($new_password);

            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $result = $stmt->execute([$password_hash, $user_id]);

            if ($result) {
                self::logActivity('password_reset', 'user', $user_id, "Admin reset user password");
            }

            return $result;
        } catch (Exception $e) {
            error_log("Reset password error: " . $e->getMessage());
            return false;
        }
    }

    // Get system settings
    public static function getSystemSettings()
    {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            $stmt = $conn->query("SELECT * FROM system_settings ORDER BY setting_key");
            $settings = $stmt->fetchAll();

            $result = [];
            foreach ($settings as $setting) {
                $value = $setting['setting_value'];

                // Convert based on type
                switch ($setting['setting_type']) {
                    case 'boolean':
                        $value = $value === 'true';
                        break;
                    case 'integer':
                        $value = (int)$value;
                        break;
                    case 'json':
                        $value = json_decode($value, true);
                        break;
                }

                $result[$setting['setting_key']] = [
                    'value' => $value,
                    'type' => $setting['setting_type'],
                    'description' => $setting['description']
                ];
            }

            return $result;
        } catch (Exception $e) {
            error_log("Get settings error: " . $e->getMessage());
            return [];
        }
    }

    // Update system setting
    public static function updateSystemSetting($key, $value, $type = 'string')
    {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            $current_user = self::getCurrentUser();

            // Convert value based on type
            switch ($type) {
                case 'boolean':
                    $value = $value ? 'true' : 'false';
                    break;
                case 'json':
                    $value = json_encode($value);
                    break;
                default:
                    $value = (string)$value;
            }

            $stmt = $conn->prepare("
                UPDATE system_settings 
                SET setting_value = ?, updated_by = ?, updated_at = NOW() 
                WHERE setting_key = ?
            ");
            $result = $stmt->execute([$value, $current_user['id'], $key]);

            if ($result) {
                self::logActivity('setting_updated', 'system', null, "Updated setting: $key");
            }

            return $result;
        } catch (Exception $e) {
            error_log("Update setting error: " . $e->getMessage());
            return false;
        }
    }

    // Log system error
    public static function logError($error_type, $error_message, $file_path = '', $line_number = 0, $stack_trace = '')
    {
        try {
            $database = new Database();
            $conn = $database->getConnection();
            $current_user = self::getCurrentUser();

            $stmt = $conn->prepare("
                INSERT INTO error_logs (error_type, error_message, file_path, line_number, user_id, ip_address, user_agent, stack_trace) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            return $stmt->execute([
                $error_type,
                $error_message,
                $file_path,
                $line_number,
                $current_user['id'] ?? null,
                $_SERVER['REMOTE_ADDR'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $stack_trace
            ]);
        } catch (Exception $e) {
            error_log("Log error failed: " . $e->getMessage());
            return false;
        }
    }
}
