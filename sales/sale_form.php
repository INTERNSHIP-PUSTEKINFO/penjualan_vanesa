<?php
require_once '../config.php';
require_once '../functions.php';

$isEdit = isset($_GET['id']);
$sale = null;
$saleId = null;

if ($isEdit) {
    $saleId = (int)$_GET['id'];
    $sale = getSaleById($saleId);
    if (!$sale) {
        redirect('sales.php?error=Sale not found');
    }
}

// Get data for dropdowns
$customers = getCustomers();
$users = getUsers();

// Generate invoice number for new sales
if (!$isEdit) {
    $invoiceNumber = 'INV-' . date('Ymd') . '-' . rand(100, 999);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'Add' ?> Sale - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="bi bi-receipt text-primary me-2"></i>
                            <?= $isEdit ? 'Edit' : 'Add' ?> Sales Transaction
                        </h4>
                    </div>
                    <div class="card-body">
                        <form action="save_sale.php" method="POST">
                            <?php if ($isEdit): ?>
                                <input type="hidden" name="sale_id" value="<?= $saleId ?>">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="invoice_number" class="form-label">Invoice Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="invoice_number" name="invoice_number" 
                                               value="<?= $isEdit ? htmlspecialchars($sale['invoice_number']) : $invoiceNumber ?>" 
                                               <?= $isEdit ? 'readonly' : '' ?> required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sales_date" class="form-label">Transaction Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="sales_date" name="sales_date" 
                                               value="<?= $isEdit ? date('Y-m-d', strtotime($sale['sales_date'])) : date('Y-m-d') ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                                        <select class="form-select" id="customer_id" name="customer_id" required>
                                            <option value="">Select Customer</option>
                                            <?php foreach ($customers as $customer): ?>
                                                <option value="<?= $customer['id'] ?>" 
                                                    <?= ($isEdit && $sale['customer_id'] == $customer['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($customer['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="user_id" class="form-label">Kasir <span class="text-danger">*</span></label>
                                        <select class="form-select" id="user_id" name="user_id" required>
                                            <option value="">Select Kasir</option>
                                            <?php foreach ($users as $user): ?>
                                                <option value="<?= $user['id'] ?>" 
                                                    <?= ($isEdit && $sale['user_id'] == $user['id']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($user['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="discount" class="form-label">Discount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" class="form-control" id="discount" name="discount" 
                                                   value="<?= $isEdit ? $sale['discount'] : '0' ?>" step="0.01" min="0">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                        <select class="form-select" id="payment_method" name="payment_method" required>
                                            <option value="">Select Method</option>
                                            <option value="cash" <?= ($isEdit && $sale['payment_method'] === 'cash') ? 'selected' : '' ?>>Cash</option>
                                            <option value="transfer" <?= ($isEdit && $sale['payment_method'] === 'transfer') ? 'selected' : '' ?>>Transfer</option>
                                            <option value="qris" <?= ($isEdit && $sale['payment_method'] === 'qris') ? 'selected' : '' ?>>QRIS</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Info:</strong> Setelah transaksi dibuat, Anda akan diarahkan ke halaman Sale Details untuk menambahkan produk dan pembayaran.
                            </div>
                            

                            
                            <div class="d-flex justify-content-between">
                                <a href="sales.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>
                                    <?= $isEdit ? 'Update' : 'Save' ?> Transaction
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
