<?php
require_once '../config.php';
require_once '../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('sales.php?error=Invalid request method');
}

$saleId = $_POST['sale_id'] ?? null;
$productId = $_POST['product_id'] ?? null;
$quantity = $_POST['quantity'] ?? null;
$price = $_POST['price'] ?? null;

if (!$saleId || !$productId || !$quantity || !$price) {
    redirect("sale_details.php?id=$saleId&error=All fields are required");
}

try {
    $pdo = getDB();
    
    // Validate sale exists
    $stmt = $pdo->prepare("SELECT id FROM sales WHERE id = ?");
    $stmt->execute([$saleId]);
    if (!$stmt->fetch()) {
        throw new Exception("Sale not found");
    }
    
    // Validate product exists and check stock
    $stmt = $pdo->prepare("SELECT id, name, stock FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if (!$product) {
        throw new Exception("Product not found");
    }
    
    if ($product['stock'] < $quantity) {
        throw new Exception("Insufficient stock. Available: " . $product['stock']);
    }
    
    // Calculate subtotal
    $subtotal = $quantity * $price;
    
    // Insert sale detail (auto_increment)
    $stmt = $pdo->prepare("
        INSERT INTO sale_details (sale_id, product_id, quantity, price, subtotal) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$saleId, $productId, $quantity, $price, $subtotal]);
    
    // Update product stock
    $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    $stmt->execute([$quantity, $productId]);
    
    // Recalculate sale totals (no transaction needed)
    calculateSaleTotals($saleId);
    
    redirect("sale_details.php?id=$saleId&success=Item added successfully");
    
} catch (Exception $e) {
    redirect("sale_details.php?id=$saleId&error=" . urlencode($e->getMessage()));
}
?>
