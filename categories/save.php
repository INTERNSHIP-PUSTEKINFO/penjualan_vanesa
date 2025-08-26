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
    $description = sanitizeInput($_POST['description'] ?? '');
    
    // Validation
    if (empty($name)) {
        throw new Exception('Category name is required');
    }
    
    if (strlen($name) > 100) {
        throw new Exception('Category name is too long (max 100 characters)');
    }
    
    if ($id) {
        // Update existing category
        $stmt = $pdo->prepare("
            UPDATE categories 
            SET name = ?, description = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$name, $description, $id]);
        
        $message = 'Category updated successfully';
    } else {
        // Check if category name already exists
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetch()) {
            throw new Exception('Category name already exists');
        }
        
        // Create new category
        $stmt = $pdo->prepare("
            INSERT INTO categories (name, description) 
            VALUES (?, ?)
        ");
        $stmt->execute([$name, $description]);
        
        $message = 'Category created successfully';
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
