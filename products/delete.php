<?php
require_once '../config.php';
require_once '../functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('list.php');
}

$productId = (int)$_GET['id'];

try {
    $pdo = getDB();
    
    // Check if product exists
    $stmt = $pdo->prepare("SELECT id, name, code FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        throw new Exception('Product not found');
    }
    
    // Check if product is being used in sale details
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sale_details WHERE product_id = ?");
    $stmt->execute([$productId]);
    $saleDetailCount = $stmt->fetchColumn();
    
    if ($saleDetailCount > 0) {
        throw new Exception("Cannot delete product '{$product['name']}' because it has {$saleDetailCount} sale record(s)");
    }
    
    // Delete product
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $result = $stmt->execute([$productId]);
    
    if ($result) {
        $message = "Product '{$product['name']}' ({$product['code']}) deleted successfully";
        header("Location: list.php?success=" . urlencode($message));
    } else {
        throw new Exception('Failed to delete product');
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
    header("Location: list.php?error=" . urlencode($error));
}

exit;
?>
