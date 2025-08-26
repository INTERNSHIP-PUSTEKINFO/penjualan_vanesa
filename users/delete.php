<?php
require_once '../config.php';
require_once '../functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('list.php');
}

$userId = (int)$_GET['id'];

try {
    $pdo = getDB();
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('User not found');
    }
    
    // Check if user is being used in sales
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sales WHERE user_id = ?");
    $stmt->execute([$userId]);
    $salesCount = $stmt->fetchColumn();
    
    if ($salesCount > 0) {
        throw new Exception("Cannot delete user '{$user['name']}' because they have {$salesCount} sales record(s)");
    }
    
    // Delete user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $result = $stmt->execute([$userId]);
    
    if ($result) {
        $message = "User '{$user['name']}' deleted successfully";
        header("Location: list.php?success=" . urlencode($message));
    } else {
        throw new Exception('Failed to delete user');
    }
    
} catch (Exception $e) {
    $error = $e->getMessage();
    header("Location: list.php?error=" . urlencode($error));
}

exit;
?>
