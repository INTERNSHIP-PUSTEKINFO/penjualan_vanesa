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
    $email = sanitizeInput($_POST['email']);
    $role = sanitizeInput($_POST['role']);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['password'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($role)) {
        throw new Exception('All required fields must be filled');
    }
    
    if (!validateEmail($email)) {
        throw new Exception('Invalid email format');
    }
    
    if (!in_array($role, ['admin', 'sales', 'manager'])) {
        throw new Exception('Invalid role selected');
    }
    
    // Check if email already exists (for new users or when email changed)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id ?? 0]);
    if ($stmt->fetch()) {
        throw new Exception('Email already exists');
    }
    
    if ($id) {
        // Update existing user
        if (!empty($password)) {
            // Password provided, update it
            if (strlen($password) < 6) {
                throw new Exception('Password must be at least 6 characters');
            }
            
            $hashedPassword = hashPassword($password);
            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = ?, email = ?, role = ?, password = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $stmt->execute([$name, $email, $role, $hashedPassword, $id]);
        } else {
            // No password provided, keep existing
            $stmt = $pdo->prepare("
                UPDATE users 
                SET name = ?, email = ?, role = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $stmt->execute([$name, $email, $role, $id]);
        }
        
        $message = 'User updated successfully';
    } else {
        // Create new user
        if (empty($password)) {
            throw new Exception('Password is required for new users');
        }
        
        if (strlen($password) < 6) {
            throw new Exception('Password must be at least 6 characters');
        }
        
        $hashedPassword = hashPassword($password);
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, role, password, created_at, updated_at) 
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$name, $email, $role, $hashedPassword]);
        
        $message = 'User created successfully';
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
