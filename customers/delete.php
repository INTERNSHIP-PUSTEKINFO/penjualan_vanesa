<?php
require_once '../config.php';
require_once '../functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('list.php');
}

$customerId = (int)$_GET['id'];

try {
    $pdo = getDB();
    
    // Check if customer exists
    $stmt = $pdo->prepare("SELECT id, name FROM customers WHERE id = ?");
    $stmt->execute([$customerId]);
    $customer = $stmt->fetch();
    
    if (!$customer) {
        throw new Exception('Customer not found');
    }
    
    // Check if customer is being used in sales
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE customer_id = ?");
    $stmt->execute([$customerId]);
    $salesCount = $stmt->fetchColumn();
    
    if ($salesCount > 0) {
        throw new Exception("Cannot delete customer '{$customer['name']}' because they have {$salesCount} sales record(s)");
    }
    
    // Delete customer
    $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
    $result = $stmt->execute([$customerId]);
    
    if ($result) {
        $message = "Customer '{$customer['name']}' deleted successfully";
        header("Location: list.php?success=" . urlencode($message));
    } else {
        throw new Exception('Failed to delete customer');
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
    header("Location: list.php?error=" . urlencode($error));
}

exit;
?>
