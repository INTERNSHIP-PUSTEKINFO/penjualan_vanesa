<?php
require_once '../config.php';
require_once '../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('list.php');
}

try {
    $pdo = getDB();
    
    // Get form data
    $id = $_POST['id'] ?? null;
    $code = sanitizeInput($_POST['code']);
    $name = sanitizeInput($_POST['name']);
    $categoryId = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $unit = sanitizeInput($_POST['unit']);
    
    // Validation
    if (empty($code) || empty($name) || $categoryId <= 0) {
        throw new Exception('All required fields must be filled');
    }
    
    if (!validatePrice($price)) {
        throw new Exception('Invalid price value');
    }
    
    if ($stock < 0) {
        throw new Exception('Stock cannot be negative');
    }
    
    if (empty($unit)) {
        throw new Exception('Unit is required');
    }
    
    // Check if category exists
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    if (!$stmt->fetch()) {
        throw new Exception('Selected category not found');
    }
    
    if ($id) {
        // Update existing product
        $stmt = $pdo->prepare("
            UPDATE products 
            SET name = ?, category_id = ?, price = ?, stock = ?, unit = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$name, $categoryId, $price, $stock, $unit, $id]);
        
        $message = 'Product updated successfully';
    } else {
        // Check if product code already exists
        $stmt = $pdo->prepare("SELECT id FROM products WHERE code = ?");
        $stmt->execute([$code]);
        if ($stmt->fetch()) {
            throw new Exception('Product code already exists');
        }
        
        // Create new product
        $stmt = $pdo->prepare("
            INSERT INTO products (code, name, category_id, price, stock, unit) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$code, $name, $categoryId, $price, $stock, $unit]);
        
        $message = 'Product created successfully';
    }
    
    // Redirect with success message
    header("Location: list.php?success=" . urlencode($message));
    exit;
    
} catch (Exception $e) {
    // Redirect with error message
    $error = $e->getMessage();
    $redirectUrl = isset($_POST['id']) ? "form.php?id=" . $_POST['id'] : "form.php";
    header("Location: $redirectUrl?error=" . urlencode($error));
    exit;
}
?>
