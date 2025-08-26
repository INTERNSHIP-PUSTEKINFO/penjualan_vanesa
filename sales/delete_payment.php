<?php
require_once '../config.php';
require_once '../functions.php';

$paymentId = $_GET['id'] ?? null;
$saleId = $_GET['sale_id'] ?? null;

if (!$paymentId || !$saleId) {
    redirect('sales.php?error=Invalid request');
}

try {
    $pdo = getDB();
    
    // Get payment details before deletion
    $stmt = $pdo->prepare("
        SELECT * FROM payments 
        WHERE id = ? AND sale_id = ?
    ");
    $stmt->execute([$paymentId, $saleId]);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        throw new Exception("Payment not found");
    }
    
    // Delete the payment
    $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ? AND sale_id = ?");
    $stmt->execute([$paymentId, $saleId]);
    
    // Recalculate payment totals (no transaction needed)
    calculatePaymentTotals($saleId);
    
    redirect("sale_details.php?id=$saleId&success=Payment removed successfully");
    
} catch (Exception $e) {
    redirect("sale_details.php?id=$saleId&error=" . urlencode($e->getMessage()));
}
?>
