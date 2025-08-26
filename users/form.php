<?php
require_once '../config.php';
require_once '../functions.php';

$user = null;
$isEdit = false;

if (isset($_GET['id'])) {
    $user = getById('users', $_GET['id']);
    if ($user) {
        $isEdit = true;
    }
}

$roles = ['admin', 'sales', 'manager'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'Add' ?> User - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="bi bi-people text-primary me-2"></i>
                        <?= $isEdit ? 'Edit' : 'Add' ?> User
                    </h2>
                    <p class="text-muted"><?= $isEdit ? 'Update' : 'Create' ?> user data</p>
                </div>
                <div>
                    <a href="list.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-person-plus me-2"></i>
                            User Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="save.php" method="POST" id="userForm">
                            <?php if ($isEdit): ?>
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?= $user ? htmlspecialchars($user['name']) : '' ?>" 
                                           required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?= $user ? htmlspecialchars($user['email']) : '' ?>" 
                                           required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= $role ?>" 
                                                <?= ($user && $user['role'] === $role) ? 'selected' : '' ?>>
                                                <?= ucfirst($role) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        Password <?= $isEdit ? '' : '<span class="text-danger">*</span>' ?>
                                    </label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           <?= $isEdit ? '' : 'required' ?>>
                                    <?php if ($isEdit): ?>
                                        <small class="text-muted">Leave blank to keep current password</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">
                                        Confirm Password <?= $isEdit ? '' : '<span class="text-danger">*</span>' ?>
                                    </label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           <?= $isEdit ? '' : 'required' ?>>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <a href="list.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>
                                    <?= $isEdit ? 'Update' : 'Save' ?> User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.getElementById('userForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const isEdit = <?= $isEdit ? 'true' : 'false' ?>;
            
            if (!isEdit && password !== confirmPassword) {
                e.preventDefault();
                alert('Password and Confirm Password do not match!');
                return false;
            }
            
            if (isEdit && password && password !== confirmPassword) {
                e.preventDefault();
                alert('Password and Confirm Password do not match!');
                return false;
            }
        });
    </script>
</body>
</html>
