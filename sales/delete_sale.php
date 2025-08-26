<?php
require_once '../config.php';
require_once '../functions.php';

$saleId = $_GET['id'] ?? null;

if (!$saleId) {
    redirect('sales.php?error=Invalid request');
}

try {
    $pdo = getDB();
    $pdo->beginTransaction();
    
    // Get sale details before deletion
    $stmt = $pdo->prepare("
        SELECT * FROM sale_details 
        WHERE sale_id = ?
    ");
    $stmt->execute([$saleId]);
    $saleDetails = $stmt->fetchAll();
    
    // Restore product stock for all items
    foreach ($saleDetails as $detail) {
        $stmt = $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $stmt->execute([$detail['quantity'], $detail['product_id']]);
    }
    
    // Delete sale details
    $stmt = $pdo->prepare("DELETE FROM sale_details WHERE sale_id = ?");
    $stmt->execute([$saleId]);
    
    // Delete the sale
    $stmt = $pdo->prepare("DELETE FROM sales WHERE id = ?");
    $stmt->execute([$saleId]);
    
    $pdo->commit();
    redirect("sales.php?success=Sale deleted successfully");
    
} catch (Exception $e) {
    $pdo->rollBack();
    redirect("sales.php?error=" . urlencode($e->getMessage()));
}
?>
