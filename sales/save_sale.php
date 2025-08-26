<?php
require_once '../config.php';
require_once '../functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('sales.php?error=Invalid request method');
}

$isEdit = isset($_POST['sale_id']);
$invoiceNumber = $_POST['invoice_number'] ?? null;
$customerId = $_POST['customer_id'] ?? null;
$userId = $_POST['user_id'] ?? null;
$salesDate = $_POST['sales_date'] ?? null;
$discount = $_POST['discount'] ?? 0;
$paymentMethod = $_POST['payment_method'] ?? null;

// For new sales, we don't need total_amount or payment_amount initially
// These will be set when products are added and payments are made

if (!$invoiceNumber || !$customerId || !$userId || !$salesDate) {
    redirect('sales.php?error=All required fields must be filled');
}

try {
    $pdo = getDB();
    $pdo->beginTransaction();
    
    // Validate customer exists
    $stmt = $pdo->prepare("SELECT id FROM customers WHERE id = ?");
    $stmt->execute([$customerId]);
    if (!$stmt->fetch()) {
        throw new Exception("Customer not found");
    }
    
    // Validate user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        throw new Exception("User not found");
    }
    
    // Validate invoice number uniqueness (for new sales)
    if (!$isEdit) {
        $stmt = $pdo->prepare("SELECT id FROM sales WHERE invoice_number = ?");
        $stmt->execute([$invoiceNumber]);
        if ($stmt->fetch()) {
            throw new Exception("Invoice number already exists");
        }
    }
    
    $discount = (float)$discount;
    
    if ($discount < 0) {
        throw new Exception("Discount cannot be negative");
    }
    
    if ($isEdit) {
        // Update existing sale (only basic info, not totals)
        $saleId = (int)$_POST['sale_id'];
        
        // Lock the sale record for update
        $stmt = $pdo->prepare("SELECT * FROM sales WHERE id = ? FOR UPDATE");
        $stmt->execute([$saleId]);
        $currentSale = $stmt->fetch();
        
        if (!$currentSale) {
            throw new Exception("Sale not found");
        }
        
        // Update only basic sale info, not totals
        $stmt = $pdo->prepare("
            UPDATE sales 
            SET customer_id = ?, user_id = ?, sales_date = ?, discount = ?, 
                payment_method = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$customerId, $userId, $salesDate, $discount, $paymentMethod, $saleId]);
        
        // Note: For edit, totals will be recalculated when products/payments are modified
        
    } else {
        // Create new sale with 0 totals initially
        $nextId = getMaxId('sales') + 1;
        
        $stmt = $pdo->prepare("
            INSERT INTO sales (id, invoice_number, customer_id, user_id, sales_date, 
                              total_amount, discount, grand_total, paid_amount, 
                              change_amount, payment_method, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, 0, ?, 0, 0, 0, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([$nextId, $invoiceNumber, $customerId, $userId, $salesDate, 
                       $discount, $paymentMethod]);
        
        echo "✅ Sale created successfully with ID: $nextId<br>";
        echo "📝 Next step: Add products to this sale<br>";
    }
    
    $pdo->commit();
    
    // Redirect after successful commit
    if ($isEdit) {
        redirect("sales.php?success=Sale updated successfully");
    } else {
        // For new sales, redirect to sale details to add products
        redirect("sale_details.php?id=$nextId&success=Sale created successfully. Now add products to this sale.");
    }
    
} catch (Exception $e) {
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    redirect("sales.php?error=" . urlencode($e->getMessage()));
}
?>
