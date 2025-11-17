<?php
/**
 * AETHERIUM HARDWARE - AUTHENTICATION
 * User login, registration, and session management
 */

require_once 'config.php';
require_once 'database.php';

class Authentication {
    private $userQueries;
    private $maxAttempts = MAX_LOGIN_ATTEMPTS;
    private $lockoutTime = LOCKOUT_TIME;

    public function __construct() {
        $this->userQueries = new UserQueries();
        session_start();
    }

    /**
     * Register new user
     */
    public function register($username, $email, $password, $confirmPassword) {
        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        // Validate email
        if (!validateEmail($email)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        // Validate password
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
        }

        // Check password match
        if ($password !== $confirmPassword) {
            return ['success' => false, 'message' => 'Passwords do not match'];
        }

        // Check if email exists
        if ($this->userQueries->emailExists($email)) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        // Check if username exists
        if ($this->userQueries->usernameExists($username)) {
            return ['success' => false, 'message' => 'Username already taken'];
        }

        // Hash password
        $passwordHash = hashPassword($password);

        // Create user
        try {
            $userId = $this->userQueries->create($username, $email, $passwordHash);
            
            logActivity($userId, 'REGISTER', "User registered with email: $email");
            
            return ['success' => true, 'message' => 'Registration successful', 'userId' => $userId];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }

    /**
     * Login user
     */
    public function login($email, $password) {
        // Validate input
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email and password are required'];
        }

        // Validate email
        if (!validateEmail($email)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }

        // Check login attempts
        if ($this->isLockedOut($email)) {
            return ['success' => false, 'message' => 'Too many login attempts. Please try again later'];
        }

        // Get user
        $user = $this->userQueries->getByEmail($email);

        if (!$user) {
            $this->recordFailedAttempt($email);
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        // Verify password
        if (!verifyPassword($password, $user['password_hash'])) {
            $this->recordFailedAttempt($email);
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        // Clear failed attempts
        $this->clearFailedAttempts($email);

        // Create session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['login_time'] = time();

        // Generate token
        $token = generateToken($user['user_id'], $user['email']);

        logActivity($user['user_id'], 'LOGIN', "User logged in from IP: " . $_SERVER['REMOTE_ADDR']);

        return [
            'success' => true,
            'message' => 'Login successful',
            'userId' => $user['user_id'],
            'token' => $token
        ];
    }

    /**
     * Logout user
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            logActivity($_SESSION['user_id'], 'LOGOUT', 'User logged out');
        }

        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !$this->isSessionExpired();
    }

    /**
     * Get current user
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return $this->userQueries->getById($_SESSION['user_id']);
    }

    /**
     * Check session expiration
     */
    private function isSessionExpired() {
        if (!isset($_SESSION['login_time'])) {
            return true;
        }

        if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
            session_destroy();
            return true;
        }

        $_SESSION['login_time'] = time();
        return false;
    }

    /**
     * Check if account is locked out
     */
    private function isLockedOut($email) {
        $lockoutFile = __DIR__ . '/../logs/lockout_' . md5($email) . '.log';

        if (!file_exists($lockoutFile)) {
            return false;
        }

        $lockoutData = json_decode(file_get_contents($lockoutFile), true);

        if (time() - $lockoutData['timestamp'] > $this->lockoutTime) {
            unlink($lockoutFile);
            return false;
        }

        return true;
    }

    /**
     * Record failed login attempt
     */
    private function recordFailedAttempt($email) {
        $lockoutFile = __DIR__ . '/../logs/lockout_' . md5($email) . '.log';
        $attempts = 1;

        if (file_exists($lockoutFile)) {
            $lockoutData = json_decode(file_get_contents($lockoutFile), true);
            $attempts = $lockoutData['attempts'] + 1;
        }

        if ($attempts >= $this->maxAttempts) {
            file_put_contents($lockoutFile, json_encode([
                'email' => $email,
                'attempts' => $attempts,
                'timestamp' => time()
            ]));
        }
    }

    /**
     * Clear failed login attempts
     */
    private function clearFailedAttempts($email) {
        $lockoutFile = __DIR__ . '/../logs/lockout_' . md5($email) . '.log';

        if (file_exists($lockoutFile)) {
            unlink($lockoutFile);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        try {
            $this->userQueries->update($userId, $data);
            logActivity($userId, 'UPDATE_PROFILE', 'User updated profile information');
            return ['success' => true, 'message' => 'Profile updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Profile update failed: ' . $e->getMessage()];
        }
    }

    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword, $confirmPassword) {
        // Validate input
        if (empty($currentPassword) || empty($newPassword)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }

        // Validate new password
        if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
        }

        // Check password match
        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'New passwords do not match'];
        }

        // Get user
        $user = $this->userQueries->getById($userId);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // Verify current password
        if (!verifyPassword($currentPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        // Hash new password
        $newPasswordHash = hashPassword($newPassword);

        // Update password
        try {
            $this->userQueries->update($userId, ['password_hash' => $newPasswordHash]);
            logActivity($userId, 'CHANGE_PASSWORD', 'User changed password');
            return ['success' => true, 'message' => 'Password changed successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Password change failed: ' . $e->getMessage()];
        }
    }

    /**
     * Verify token
     */
    public function verifyToken($token) {
        $payload = verifyToken($token);

        if (!$payload) {
            return ['success' => false, 'message' => 'Invalid or expired token'];
        }

        return ['success' => true, 'payload' => $payload];
    }
}

// ============================================
// API ENDPOINTS
// ============================================

enableCORS();

$auth = new Authentication();
$method = getRequestMethod();
$body = getRequestBody();

// Determine the action
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'register':
        if ($method === 'POST') {
            $result = $auth->register(
                $body['username'] ?? '',
                $body['email'] ?? '',
                $body['password'] ?? '',
                $body['confirmPassword'] ?? ''
            );
            sendJSON($result, $result['success'] ? 201 : 400);
        }
        break;

    case 'login':
        if ($method === 'POST') {
            $result = $auth->login(
                $body['email'] ?? '',
                $body['password'] ?? ''
            );
            sendJSON($result, $result['success'] ? 200 : 401);
        }
        break;

    case 'logout':
        if ($method === 'POST') {
            $result = $auth->logout();
            sendJSON($result);
        }
        break;

    case 'me':
        if ($method === 'GET') {
            if ($auth->isLoggedIn()) {
                $user = $auth->getCurrentUser();
                sendJSON(['success' => true, 'user' => $user]);
            } else {
                sendError('Not authenticated', 401);
            }
        }
        break;

    case 'update-profile':
        if ($method === 'POST') {
            if ($auth->isLoggedIn()) {
                $result = $auth->updateProfile($_SESSION['user_id'], $body);
                sendJSON($result);
            } else {
                sendError('Not authenticated', 401);
            }
        }
        break;

    case 'change-password':
        if ($method === 'POST') {
            if ($auth->isLoggedIn()) {
                $result = $auth->changePassword(
                    $_SESSION['user_id'],
                    $body['currentPassword'] ?? '',
                    $body['newPassword'] ?? '',
                    $body['confirmPassword'] ?? ''
                );
                sendJSON($result);
            } else {
                sendError('Not authenticated', 401);
            }
        }
        break;

    case 'verify-token':
        if ($method === 'POST') {
            $result = $auth->verifyToken($body['token'] ?? '');
            sendJSON($result);
        }
        break;

    default:
        sendError('Invalid action', 400);
}

?>
