<?php
/**
 * AETHERIUM HARDWARE - DATABASE HELPER
 * Database connection and query methods
 */

require_once 'config.php';

class Database {
    private $conn;
    private $stmt;

    public function __construct() {
        $this->connect();
    }

    /**
     * Connect to database
     */
    private function connect() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

        if ($this->conn->connect_error) {
            die('Database Connection Error: ' . $this->conn->connect_error);
        }

        $this->conn->set_charset('utf8mb4');
    }

    /**
     * Prepare statement
     */
    public function prepare($sql) {
        $this->stmt = $this->conn->prepare($sql);
        
        if (!$this->stmt) {
            die('Prepare Error: ' . $this->conn->error);
        }

        return $this;
    }

    /**
     * Bind parameters
     */
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = MYSQLI_TYPE_LONG;
                    break;
                case is_float($value):
                    $type = MYSQLI_TYPE_DOUBLE;
                    break;
                default:
                    $type = MYSQLI_TYPE_STRING;
            }
        }

        $this->stmt->bind_param($type, $value);
        return $this;
    }

    /**
     * Execute statement
     */
    public function execute() {
        if (!$this->stmt->execute()) {
            die('Execute Error: ' . $this->stmt->error);
        }

        return $this;
    }

    /**
     * Get single result
     */
    public function getSingle() {
        $result = $this->stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Get all results
     */
    public function getAll() {
        $result = $this->stmt->get_result();
        $rows = [];

        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Get row count
     */
    public function rowCount() {
        return $this->stmt->num_rows;
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->conn->insert_id;
    }

    /**
     * Get affected rows
     */
    public function affectedRows() {
        return $this->stmt->affected_rows;
    }

    /**
     * Close connection
     */
    public function close() {
        if ($this->stmt) {
            $this->stmt->close();
        }
        $this->conn->close();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        $this->conn->begin_transaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        $this->conn->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        $this->conn->rollback();
    }

    /**
     * Escape string
     */
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }
}

/**
 * USER QUERIES
 */
class UserQueries {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Create user
     */
    public function create($username, $email, $passwordHash) {
        $this->db->prepare('INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())');
        $this->db->bind('sss', $username, $email, $passwordHash);
        $this->db->execute();

        return $this->db->lastInsertId();
    }

    /**
     * Get user by email
     */
    public function getByEmail($email) {
        $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $this->db->bind('s', $email);
        $this->db->execute();

        return $this->db->getSingle();
    }

    /**
     * Get user by ID
     */
    public function getById($userId) {
        $this->db->prepare('SELECT * FROM users WHERE user_id = ? LIMIT 1');
        $this->db->bind('i', $userId);
        $this->db->execute();

        return $this->db->getSingle();
    }

    /**
     * Update user
     */
    public function update($userId, $data) {
        $updates = [];
        $types = '';
        $values = [];

        foreach ($data as $key => $value) {
            $updates[] = "$key = ?";
            $types .= 's';
            $values[] = $value;
        }

        $values[] = $userId;
        $types .= 'i';

        $sql = 'UPDATE users SET ' . implode(', ', $updates) . ', updated_at = NOW() WHERE user_id = ?';
        
        $this->db->prepare($sql);
        $this->db->bind($types, ...$values);
        $this->db->execute();

        return $this->db->affectedRows();
    }

    /**
     * Delete user
     */
    public function delete($userId) {
        $this->db->prepare('DELETE FROM users WHERE user_id = ?');
        $this->db->bind('i', $userId);
        $this->db->execute();

        return $this->db->affectedRows();
    }

    /**
     * Check if email exists
     */
    public function emailExists($email) {
        $this->db->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
        $this->db->bind('s', $email);
        $this->db->execute();

        return $this->db->rowCount() > 0;
    }

    /**
     * Check if username exists
     */
    public function usernameExists($username) {
        $this->db->prepare('SELECT user_id FROM users WHERE username = ? LIMIT 1');
        $this->db->bind('s', $username);
        $this->db->execute();

        return $this->db->rowCount() > 0;
    }
}

/**
 * PRODUCT QUERIES
 */
class ProductQueries {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all products
     */
    public function getAll($limit = 0, $offset = 0) {
        $sql = 'SELECT * FROM products WHERE status = "active"';
        
        if ($limit > 0) {
            $sql .= ' LIMIT ' . intval($limit) . ' OFFSET ' . intval($offset);
        }

        $this->db->prepare($sql);
        $this->db->execute();

        return $this->db->getAll();
    }

    /**
     * Get product by ID
     */
    public function getById($productId) {
        $this->db->prepare('SELECT * FROM products WHERE product_id = ? AND status = "active" LIMIT 1');
        $this->db->bind('i', $productId);
        $this->db->execute();

        return $this->db->getSingle();
    }

    /**
     * Search products
     */
    public function search($query, $limit = 0, $offset = 0) {
        $searchTerm = '%' . $query . '%';
        $sql = 'SELECT * FROM products WHERE (name LIKE ? OR description LIKE ?) AND status = "active"';
        
        if ($limit > 0) {
            $sql .= ' LIMIT ' . intval($limit) . ' OFFSET ' . intval($offset);
        }

        $this->db->prepare($sql);
        $this->db->bind('ss', $searchTerm, $searchTerm);
        $this->db->execute();

        return $this->db->getAll();
    }

    /**
     * Get products by category
     */
    public function getByCategory($category, $limit = 0, $offset = 0) {
        $sql = 'SELECT * FROM products WHERE category = ? AND status = "active"';
        
        if ($limit > 0) {
            $sql .= ' LIMIT ' . intval($limit) . ' OFFSET ' . intval($offset);
        }

        $this->db->prepare($sql);
        $this->db->bind('s', $category);
        $this->db->execute();

        return $this->db->getAll();
    }

    /**
     * Get featured products
     */
    public function getFeatured($limit = 4) {
        $this->db->prepare('SELECT * FROM products WHERE featured = 1 AND status = "active" LIMIT ?');
        $this->db->bind('i', $limit);
        $this->db->execute();

        return $this->db->getAll();
    }

    /**
     * Create product
     */
    public function create($name, $description, $price, $category, $stock) {
        $this->db->prepare('INSERT INTO products (name, description, price, category, stock_quantity, status, created_at) VALUES (?, ?, ?, ?, ?, "active", NOW())');
        $this->db->bind('ssdsi', $name, $description, $price, $category, $stock);
        $this->db->execute();

        return $this->db->lastInsertId();
    }

    /**
     * Update product
     */
    public function update($productId, $data) {
        $updates = [];
        $types = '';
        $values = [];

        foreach ($data as $key => $value) {
            $updates[] = "$key = ?";
            $types .= 's';
            $values[] = $value;
        }

        $values[] = $productId;
        $types .= 'i';

        $sql = 'UPDATE products SET ' . implode(', ', $updates) . ', updated_at = NOW() WHERE product_id = ?';
        
        $this->db->prepare($sql);
        $this->db->bind($types, ...$values);
        $this->db->execute();

        return $this->db->affectedRows();
    }

    /**
     * Delete product
     */
    public function delete($productId) {
        $this->db->prepare('UPDATE products SET status = "inactive" WHERE product_id = ?');
        $this->db->bind('i', $productId);
        $this->db->execute();

        return $this->db->affectedRows();
    }

    /**
     * Get total count
     */
    public function getTotalCount() {
        $this->db->prepare('SELECT COUNT(*) as total FROM products WHERE status = "active"');
        $this->db->execute();
        $result = $this->db->getSingle();

        return $result['total'];
    }
}

?>
