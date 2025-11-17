<?php
/**
 * AETHERIUM HARDWARE - PRODUCTS API
 * Product management and retrieval
 */

require_once 'config.php';
require_once 'database.php';

class ProductsAPI {
    private $productQueries;

    public function __construct() {
        $this->productQueries = new ProductQueries();
    }

    /**
     * Get all products with pagination
     */
    public function getAllProducts($page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;
        $products = $this->productQueries->getAll($limit, $offset);
        $total = $this->productQueries->getTotalCount();

        return [
            'success' => true,
            'data' => $products,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }

    /**
     * Get product by ID
     */
    public function getProduct($productId) {
        $product = $this->productQueries->getById($productId);

        if (!$product) {
            return ['success' => false, 'message' => 'Product not found'];
        }

        return ['success' => true, 'data' => $product];
    }

    /**
     * Search products
     */
    public function searchProducts($query, $page = 1, $limit = ITEMS_PER_PAGE) {
        if (empty($query)) {
            return ['success' => false, 'message' => 'Search query is required'];
        }

        $offset = ($page - 1) * $limit;
        $products = $this->productQueries->search($query, $limit, $offset);

        return [
            'success' => true,
            'data' => $products,
            'query' => $query,
            'page' => $page
        ];
    }

    /**
     * Get products by category
     */
    public function getByCategory($category, $page = 1, $limit = ITEMS_PER_PAGE) {
        if (empty($category)) {
            return ['success' => false, 'message' => 'Category is required'];
        }

        $offset = ($page - 1) * $limit;
        $products = $this->productQueries->getByCategory($category, $limit, $offset);

        return [
            'success' => true,
            'data' => $products,
            'category' => $category,
            'page' => $page
        ];
    }

    /**
     * Get featured products
     */
    public function getFeaturedProducts($limit = 4) {
        $products = $this->productQueries->getFeatured($limit);

        return [
            'success' => true,
            'data' => $products
        ];
    }

    /**
     * Create product (admin only)
     */
    public function createProduct($data) {
        // Validate required fields
        $required = ['name', 'description', 'price', 'category', 'stock'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return ['success' => false, 'message' => ucfirst($field) . ' is required'];
            }
        }

        // Validate price
        if (!is_numeric($data['price']) || $data['price'] < 0) {
            return ['success' => false, 'message' => 'Invalid price'];
        }

        // Validate stock
        if (!is_numeric($data['stock']) || $data['stock'] < 0) {
            return ['success' => false, 'message' => 'Invalid stock quantity'];
        }

        try {
            $productId = $this->productQueries->create(
                sanitizeInput($data['name']),
                sanitizeInput($data['description']),
                floatval($data['price']),
                sanitizeInput($data['category']),
                intval($data['stock'])
            );

            logActivity($_SESSION['user_id'] ?? 'admin', 'CREATE_PRODUCT', "Product created: {$data['name']}");

            return [
                'success' => true,
                'message' => 'Product created successfully',
                'productId' => $productId
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to create product: ' . $e->getMessage()];
        }
    }

    /**
     * Update product (admin only)
     */
    public function updateProduct($productId, $data) {
        $product = $this->productQueries->getById($productId);

        if (!$product) {
            return ['success' => false, 'message' => 'Product not found'];
        }

        // Sanitize data
        $updateData = [];
        foreach ($data as $key => $value) {
            if (in_array($key, ['name', 'description', 'price', 'category', 'stock_quantity', 'status'])) {
                $updateData[$key] = sanitizeInput($value);
            }
        }

        try {
            $this->productQueries->update($productId, $updateData);

            logActivity($_SESSION['user_id'] ?? 'admin', 'UPDATE_PRODUCT', "Product updated: ID $productId");

            return ['success' => true, 'message' => 'Product updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to update product: ' . $e->getMessage()];
        }
    }

    /**
     * Delete product (admin only)
     */
    public function deleteProduct($productId) {
        $product = $this->productQueries->getById($productId);

        if (!$product) {
            return ['success' => false, 'message' => 'Product not found'];
        }

        try {
            $this->productQueries->delete($productId);

            logActivity($_SESSION['user_id'] ?? 'admin', 'DELETE_PRODUCT', "Product deleted: ID $productId");

            return ['success' => true, 'message' => 'Product deleted successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Failed to delete product: ' . $e->getMessage()];
        }
    }

    /**
     * Get product categories
     */
    public function getCategories() {
        $categories = [
            'Processors',
            'Graphics Cards',
            'Storage',
            'Motherboards',
            'Memory',
            'Cooling',
            'Power Supply',
            'Cases'
        ];

        return [
            'success' => true,
            'data' => $categories
        ];
    }
}

// ============================================
// API ENDPOINTS
// ============================================

enableCORS();

$api = new ProductsAPI();
$method = getRequestMethod();
$body = getRequestBody();

// Determine the action
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? null;

switch ($action) {
    case 'list':
        if ($method === 'GET') {
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? ITEMS_PER_PAGE;
            $result = $api->getAllProducts($page, $limit);
            sendJSON($result);
        }
        break;

    case 'get':
        if ($method === 'GET' && $id) {
            $result = $api->getProduct($id);
            sendJSON($result, $result['success'] ? 200 : 404);
        }
        break;

    case 'search':
        if ($method === 'GET') {
            $query = $_GET['q'] ?? '';
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? ITEMS_PER_PAGE;
            $result = $api->searchProducts($query, $page, $limit);
            sendJSON($result, $result['success'] ? 200 : 400);
        }
        break;

    case 'category':
        if ($method === 'GET') {
            $category = $_GET['name'] ?? '';
            $page = $_GET['page'] ?? 1;
            $limit = $_GET['limit'] ?? ITEMS_PER_PAGE;
            $result = $api->getByCategory($category, $page, $limit);
            sendJSON($result, $result['success'] ? 200 : 400);
        }
        break;

    case 'featured':
        if ($method === 'GET') {
            $limit = $_GET['limit'] ?? 4;
            $result = $api->getFeaturedProducts($limit);
            sendJSON($result);
        }
        break;

    case 'categories':
        if ($method === 'GET') {
            $result = $api->getCategories();
            sendJSON($result);
        }
        break;

    case 'create':
        if ($method === 'POST') {
            // Check admin permission (in real app, verify user role)
            $result = $api->createProduct($body);
            sendJSON($result, $result['success'] ? 201 : 400);
        }
        break;

    case 'update':
        if ($method === 'PUT' && $id) {
            // Check admin permission (in real app, verify user role)
            $result = $api->updateProduct($id, $body);
            sendJSON($result, $result['success'] ? 200 : 400);
        }
        break;

    case 'delete':
        if ($method === 'DELETE' && $id) {
            // Check admin permission (in real app, verify user role)
            $result = $api->deleteProduct($id);
            sendJSON($result, $result['success'] ? 200 : 404);
        }
        break;

    default:
        sendError('Invalid action', 400);
}

?>
