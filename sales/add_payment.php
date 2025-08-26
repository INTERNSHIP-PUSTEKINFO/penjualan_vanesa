<?php
require_once '../config.php';
require_once '../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('sales.php?error=Invalid request method');
}

$saleId = $_POST['sale_id'] ?? null;
$amount = $_POST['amount'] ?? null;
$paymentMethod = $_POST['payment_method'] ?? null;
$paymentDate = $_POST['payment_date'] ?? null;

if (!$saleId || !$amount || !$paymentMethod || !$paymentDate) {
    redirect("sale_details.php?id=$saleId&error=All fields are required");
}

try {
    $pdo = getDB();
    
    // Validate sale exists
    $stmt = $pdo->prepare("SELECT id, grand_total, paid_amount FROM sales WHERE id = ?");
    $stmt->execute([$saleId]);
    $sale = $stmt->fetch();
    if (!$sale) {
        throw new Exception("Sale not found");
    }
    
    // Validate amount
    $amount = (float)$amount;
    if ($amount <= 0) {
        throw new Exception("Payment amount must be greater than 0");
    }
    
    // Insert payment (auto_increment)
    $stmt = $pdo->prepare("
        INSERT INTO payments (sale_id, amount, payment_method, payment_date) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$saleId, $amount, $paymentMethod, $paymentDate]);
    
    // Recalculate payment totals (no transaction needed)
    calculatePaymentTotals($saleId);
    
    redirect("sale_details.php?id=$saleId&success=Payment added successfully");
    
} catch (Exception $e) {
    redirect("sale_details.php?id=$saleId&error=" . urlencode($e->getMessage()));
}
?>
