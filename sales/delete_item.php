<?php
require_once '../config.php';
require_once '../functions.php';

$itemId = $_GET['id'] ?? null;
$saleId = $_GET['sale_id'] ?? null;

if (!$itemId || !$saleId) {
    redirect('sales.php?error=Invalid request');
}

try {
    $pdo = getDB();
    
    // Get item details before deletion
    $stmt = $pdo->prepare("
        SELECT * FROM sale_details 
        WHERE id = ? AND sale_id = ?
    ");
    $stmt->execute([$itemId, $saleId]);
    $item = $stmt->fetch();
    
    if (!$item) {
        throw new Exception("Sale item not found");
    }
    
    // Delete the sale detail
    $stmt = $pdo->prepare("DELETE FROM sale_details WHERE id = ? AND sale_id = ?");
    $stmt->execute([$itemId, $saleId]);
    
    // Restore product stock
    $stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
    $stmt->execute([$item['quantity'], $item['product_id']]);
    
    // Recalculate sale totals (no transaction needed)
    calculateSaleTotals($saleId);
    
    redirect("sale_details.php?id=$saleId&success=Item removed successfully");
    
} catch (Exception $e) {
    redirect("sale_details.php?id=$saleId&error=" . urlencode($e->getMessage()));
}
?>
