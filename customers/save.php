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
    $name = sanitizeInput($_POST['name']);
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    
    // Validation
    if (empty($name)) {
        throw new Exception('Customer name is required');
    }
    
    if (!empty($email) && !validateEmail($email)) {
        throw new Exception('Invalid email format');
    }
    
    if (!empty($phone) && !validatePhone($phone)) {
        throw new Exception('Invalid phone format');
    }
    
    if ($id) {
        // Update existing customer
        $stmt = $pdo->prepare("
            UPDATE customers 
            SET name = ?, phone = ?, email = ?, address = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$name, $phone, $email, $address, $id]);
        
        $message = 'Customer updated successfully';
    } else {
        // Create new customer
        $stmt = $pdo->prepare("
            INSERT INTO customers (name, phone, email, address) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$name, $phone, $email, $address]);
        
        $message = 'Customer created successfully';
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
