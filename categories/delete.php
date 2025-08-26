<?php
require_once '../config.php';
require_once '../functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('list.php');
}

$categoryId = (int)$_GET['id'];

try {
    $pdo = getDB();
    
    // Check if category exists
    $stmt = $pdo->prepare("SELECT id, name FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();
    
    if (!$category) {
        throw new Exception('Category not found');
    }
    
    // Check if category is being used in products
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    $productCount = $stmt->fetchColumn();
    
    if ($productCount > 0) {
        throw new Exception("Cannot delete category '{$category['name']}' because it has {$productCount} product(s)");
    }
    
    // Delete category
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $result = $stmt->execute([$categoryId]);
    
    if ($result) {
        $message = "Category '{$category['name']}' deleted successfully";
        header("Location: list.php?success=" . urlencode($message));
    } else {
        throw new Exception('Failed to delete category');
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
    header("Location: list.php?error=" . urlencode($error));
}

exit;
?>
