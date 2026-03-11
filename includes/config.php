<?php
/**
 * Food-Saver Configuration File
 * Database and Application Settings
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'food_saver');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'Food-Saver');
define('APP_TAGLINE', 'Reduce Food Waste. Feed the Hungry.');
define('APP_URL', 'http://localhost/food-saver-php');
define('APP_VERSION', '1.0.0');

// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('FROM_EMAIL', 'noreply@foodsaver.org');
define('FROM_NAME', 'Food-Saver');

// File Upload Configuration
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', APP_URL . '/assets/uploads/');

// Pagination
define('ITEMS_PER_PAGE', 10);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Database Connection Class
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->connection = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

// Helper function to get DB connection
function getDB() {
    return Database::getInstance()->getConnection();
}

// Security Functions
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function generateToken() {
    return bin2hex(random_bytes(32));
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}

// Authentication Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $db = getDB();
    $table = $_SESSION['user_type'] . 's';
    $stmt = $db->prepare("SELECT * FROM {$table} WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function requireAuth($allowedTypes = []) {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . APP_URL . '/pages/login.php');
        exit;
    }
    
    if (!empty($allowedTypes) && !in_array($_SESSION['user_type'], $allowedTypes)) {
        header('Location: ' . APP_URL . '/pages/unauthorized.php');
        exit;
    }
}

function redirectBasedOnRole() {
    if (!isLoggedIn()) return;
    
    switch ($_SESSION['user_type']) {
        case 'admin':
            header('Location: ' . APP_URL . '/dashboards/admin.php');
            break;
        case 'restaurant':
            header('Location: ' . APP_URL . '/dashboards/restaurant.php');
            break;
        case 'ngo':
            header('Location: ' . APP_URL . '/dashboards/ngo.php');
            break;
        case 'user':
            header('Location: ' . APP_URL . '/dashboards/user.php');
            break;
        default:
            header('Location: ' . APP_URL . '/index.php');
    }
    exit;
}

// Notification Functions
function createNotification($recipientId, $recipientType, $title, $message, $type = 'info', $relatedId = null, $relatedType = null) {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO notifications (recipient_id, recipient_type, title, message, type, related_id, related_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$recipientId, $recipientType, $title, $message, $type, $relatedId, $relatedType]);
}

function getUnreadNotifications($recipientId, $recipientType) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM notifications WHERE recipient_id = ? AND recipient_type = ? AND is_read = FALSE ORDER BY created_at DESC LIMIT 10");
    $stmt->execute([$recipientId, $recipientType]);
    return $stmt->fetchAll();
}

// Activity Log Function
function logActivity($userId, $userType, $action, $description = '') {
    $db = getDB();
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $stmt = $db->prepare("INSERT INTO activity_logs (user_id, user_type, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$userId, $userType, $action, $description, $ipAddress, $userAgent]);
}

// Flash Messages
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Format Functions
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

function formatDateTime($date, $format = 'd M Y, h:i A') {
    return date($format, strtotime($date));
}

function formatCurrency($amount, $currency = '₹') {
    return $currency . number_format($amount, 2);
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return formatDate($datetime);
}

// File Upload Function
function uploadFile($file, $directory = '') {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'error' => 'No file uploaded'];
    }
    
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds maximum limit'];
    }
    
    if (!in_array($file['type'], UPLOAD_ALLOWED_TYPES)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }
    
    $uploadDir = UPLOAD_PATH . $directory;
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'path' => $filepath];
    }
    
    return ['success' => false, 'error' => 'Failed to upload file'];
}

// Pagination Function
function getPagination($totalItems, $currentPage = 1, $itemsPerPage = ITEMS_PER_PAGE) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset,
        'has_previous' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages
    ];
}

// Statistics Functions
function getSiteStatistics() {
    $db = getDB();
    $stats = [];
    
    // Meals saved (delivered food)
    $stmt = $db->query("SELECT COUNT(*) as count FROM food_listings WHERE status = 'delivered'");
    $stats['meals_saved'] = $stmt->fetch()['count'] * 10; // Estimate 10 servings per meal
    
    // Restaurants registered
    $stmt = $db->query("SELECT COUNT(*) as count FROM restaurants WHERE status = 'approved'");
    $stats['restaurants'] = $stmt->fetch()['count'];
    
    // NGOs partnered
    $stmt = $db->query("SELECT COUNT(*) as count FROM ngos WHERE status = 'approved'");
    $stats['ngos'] = $stmt->fetch()['count'];
    
    // Donations completed
    $stmt = $db->query("SELECT COUNT(*) as count FROM donations WHERE status = 'completed'");
    $stats['donations'] = $stmt->fetch()['count'];
    
    // Total amount donated
    $stmt = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM donations WHERE status = 'completed'");
    $stats['total_donated'] = $stmt->fetch()['total'];
    
    return $stats;
}

// Generate OTP
function generateOTP($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

// Send Email Function (Placeholder - implement with PHPMailer)
function sendEmail($to, $subject, $body, $altBody = '') {
    // Implement using PHPMailer or similar
    // Return true on success, false on failure
    return true;
}

// Auto-include CSRF token in forms
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}
?>
