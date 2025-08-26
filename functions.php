<?php
require_once 'config.php';

// Generic CRUD functions
function getAll($table, $orderBy = 'id DESC') {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM $table ORDER BY $orderBy");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getById($table, $id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM $table WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function deleteById($table, $id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
    return $stmt->execute([$id]);
}

// Specific functions for each entity
function getUsers() {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getCustomers() {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM customers ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getCategories() {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM categories ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getProducts() {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.name
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getSales() {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT s.*, c.name as customer_name, u.name as user_name 
        FROM sales s 
        LEFT JOIN customers c ON s.customer_id = c.id 
        LEFT JOIN users u ON s.user_id = u.id 
        ORDER BY s.sales_date DESC, s.id DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getSaleDetails($saleId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT sd.*, p.name as product_name, p.code as product_code, p.unit as product_unit 
        FROM sale_details sd 
        LEFT JOIN products p ON sd.product_id = p.id 
        WHERE sd.sale_id = ? 
        ORDER BY sd.id
    ");
    $stmt->execute([$saleId]);
    return $stmt->fetchAll();
}

function getPayments($saleId) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT * FROM payments 
        WHERE sale_id = ? 
        ORDER BY payment_date DESC
    ");
    $stmt->execute([$saleId]);
    return $stmt->fetchAll();
}

function getSaleById($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT s.*, c.name as customer_name, u.name as user_name 
        FROM sales s 
        LEFT JOIN customers c ON s.customer_id = c.id 
        LEFT JOIN users u ON s.user_id = u.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getProductById($id) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getAllProducts() {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.name
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getAllCustomers() {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM customers ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getAllUsers() {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Helper function to get max ID for manual ID generation (only for sales table)
function getMaxId($table) {
    if ($table === 'sales') {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT MAX(id) FROM $table");
        $stmt->execute();
        return $stmt->fetchColumn() ?? 0;
    }
    return 0; // sale_details and payments have auto_increment
}

// Calculation functions - Simplified without transactions and locks
function calculateSaleTotals($saleId) {
    $pdo = getDB();
    
    try {
        // Calculate total amount from sale details
        $stmt = $pdo->prepare("
            SELECT SUM(subtotal) as total_amount 
            FROM sale_details 
            WHERE sale_id = ?
        ");
        $stmt->execute([$saleId]);
        $result = $stmt->fetch();
        $totalAmount = (float)($result['total_amount'] ?? 0);
        
        // Get current sale data (no lock)
        $stmt = $pdo->prepare("SELECT discount, paid_amount FROM sales WHERE id = ?");
        $stmt->execute([$saleId]);
        $sale = $stmt->fetch();
        
        if (!$sale) {
            throw new Exception("Sale not found");
        }
        
        $discount = (float)($sale['discount'] ?? 0);
        $grandTotal = max(0, $totalAmount - $discount);
        $paidAmount = (float)($sale['paid_amount'] ?? 0);
        $changeAmount = max(0, $paidAmount - $grandTotal);
        
        // Update sales table
        $stmt = $pdo->prepare("
            UPDATE sales 
            SET total_amount = ?, grand_total = ?, change_amount = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$totalAmount, $grandTotal, $changeAmount, $saleId]);
        
        return [
            'total_amount' => $totalAmount,
            'grand_total' => $grandTotal,
            'change_amount' => $changeAmount
        ];
        
    } catch (Exception $e) {
        error_log("Error in calculateSaleTotals: " . $e->getMessage());
        throw $e;
    }
}

function calculatePaymentTotals($saleId) {
    $pdo = getDB();
    
    try {
        // Calculate total paid amount from payments
        $stmt = $pdo->prepare("
            SELECT SUM(amount) as total_paid 
            FROM payments 
            WHERE sale_id = ?
        ");
        $stmt->execute([$saleId]);
        $result = $stmt->fetch();
        $totalPaid = (float)($result['total_paid'] ?? 0);
        
        // Get current sale data (no lock)
        $stmt = $pdo->prepare("SELECT grand_total FROM sales WHERE id = ?");
        $stmt->execute([$saleId]);
        $sale = $stmt->fetch();
        
        if (!$sale) {
            throw new Exception("Sale not found");
        }
        
        $grandTotal = (float)($sale['grand_total'] ?? 0);
        $changeAmount = max(0, $totalPaid - $grandTotal);
        
        // Update sales table
        $stmt = $pdo->prepare("
            UPDATE sales 
            SET paid_amount = ?, change_amount = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$totalPaid, $changeAmount, $saleId]);
        
        return [
            'paid_amount' => $totalPaid,
            'change_amount' => $changeAmount
        ];
        
    } catch (Exception $e) {
        error_log("Error in calculatePaymentTotals: " . $e->getMessage());
        throw $e;
    }
}

// Validation functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[0-9+\-\s()]+$/', $phone);
}

function validatePrice($price) {
    return is_numeric($price) && $price >= 0;
}

function validateQuantity($quantity) {
    return is_numeric($quantity) && $quantity > 0;
}

// Security functions
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>
