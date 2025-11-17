<?php
/**
 * AETHERIUM HARDWARE - CONFIGURATION FILE
 * Database and Application Settings
 */

// ============================================
// DATABASE CONFIGURATION
// ============================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'password');
define('DB_NAME', 'aetherium_hardware');
define('DB_PORT', 3306);

// ============================================
// APPLICATION SETTINGS
// ============================================

define('APP_NAME', 'Aetherium Hardware');
define('APP_URL', 'http://localhost:8000');
define('APP_ENV', 'development');
define('APP_DEBUG', true);

// ============================================
// SECURITY SETTINGS
// ============================================

define('JWT_SECRET', 'your-secret-key-change-this-in-production');
define('SESSION_TIMEOUT', 3600); // 1 hour
define('PASSWORD_MIN_LENGTH', 8);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutes

// ============================================
// CORS AND HEADERS
// ============================================

define('ALLOWED_ORIGINS', ['http://localhost:8000', 'http://localhost:3000']);
define('ALLOWED_METHODS', ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']);

// ============================================
// FILE UPLOAD SETTINGS
// ============================================

define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// ============================================
// EMAIL SETTINGS
// ============================================

define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USER', 'your-email@gmail.com');
define('MAIL_PASS', 'your-app-password');
define('MAIL_FROM', 'noreply@aetherium-hardware.com');
define('MAIL_FROM_NAME', 'Aetherium Hardware');

// ============================================
// PAGINATION
// ============================================

define('ITEMS_PER_PAGE', 12);

// ============================================
// ERROR HANDLING
// ============================================

error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? 1 : 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// ============================================
// TIMEZONE
// ============================================

date_default_timezone_set('UTC');

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Get the database connection
 */
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        
        if ($conn->connect_error) {
            throw new Exception('Database connection failed: ' . $conn->connect_error);
        }
        
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (Exception $e) {
        error_log($e->getMessage());
        die('Database connection error');
    }
}

/**
 * Sanitize user input
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate JWT token
 */
function generateToken($userId, $email) {
    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
    $payload = json_encode([
        'iss' => APP_URL,
        'aud' => APP_URL,
        'iat' => time(),
        'exp' => time() + (7 * 24 * 60 * 60), // 7 days
        'userId' => $userId,
        'email' => $email
    ]);
    
    $base64Header = base64_encode($header);
    $base64Payload = base64_encode($payload);
    $signature = hash_hmac('sha256', "$base64Header.$base64Payload", JWT_SECRET, true);
    $base64Signature = base64_encode($signature);
    
    return "$base64Header.$base64Payload.$base64Signature";
}

/**
 * Verify JWT token
 */
function verifyToken($token) {
    $parts = explode('.', $token);
    
    if (count($parts) !== 3) {
        return false;
    }
    
    list($base64Header, $base64Payload, $base64Signature) = $parts;
    
    $signature = hash_hmac('sha256', "$base64Header.$base64Payload", JWT_SECRET, true);
    $expectedSignature = base64_encode($signature);
    
    if (!hash_equals($base64Signature, $expectedSignature)) {
        return false;
    }
    
    $payload = json_decode(base64_decode($base64Payload), true);
    
    if ($payload['exp'] < time()) {
        return false;
    }
    
    return $payload;
}

/**
 * Send JSON response
 */
function sendJSON($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Send error response
 */
function sendError($message, $statusCode = 400) {
    sendJSON(['error' => $message], $statusCode);
}

/**
 * Send success response
 */
function sendSuccess($data, $message = 'Success', $statusCode = 200) {
    sendJSON(['success' => true, 'message' => $message, 'data' => $data], $statusCode);
}

/**
 * Enable CORS
 */
function enableCORS() {
    header('Access-Control-Allow-Origin: ' . (in_array($_SERVER['HTTP_ORIGIN'] ?? '', ALLOWED_ORIGINS) ? $_SERVER['HTTP_ORIGIN'] : ALLOWED_ORIGINS[0]));
    header('Access-Control-Allow-Methods: ' . implode(', ', ALLOWED_METHODS));
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Access-Control-Allow-Credentials: true');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * Get request method
 */
function getRequestMethod() {
    return $_SERVER['REQUEST_METHOD'];
}

/**
 * Get request body
 */
function getRequestBody() {
    return json_decode(file_get_contents('php://input'), true);
}

/**
 * Log activity
 */
function logActivity($userId, $action, $details = '') {
    $logFile = __DIR__ . '/../logs/activity.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] User: $userId | Action: $action | Details: $details\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

?>
