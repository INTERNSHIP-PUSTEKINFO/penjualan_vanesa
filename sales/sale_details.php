<?php
require_once '../config.php';
require_once '../functions.php';

$saleId = $_GET['id'] ?? null;
if (!$saleId) {
    redirect('sales.php?error=Invalid sale ID');
}

$sale = getSaleById($saleId);
if (!$sale) {
    redirect('sales.php?error=Sale not found');
}

$saleDetails = getSaleDetails($saleId);
$products = getAllProducts();
$payments = getPayments($saleId);

// Calculate totals
$totalAmount = array_sum(array_column($saleDetails, 'subtotal'));
$grandTotal = max(0, $totalAmount - ($sale['discount'] ?? 0));
$paidAmount = array_sum(array_column($payments, 'amount'));
$changeAmount = max(0, $paidAmount - $grandTotal);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Details - <?= htmlspecialchars($sale['invoice_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">
                    <i class="bi bi-receipt"></i> 
                    Sale Details: <?= htmlspecialchars($sale['invoice_number']) ?>
                </h2>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="sales.php">Sales</a></li>
                        <li class="breadcrumb-item active">Sale Details</li>
                    </ol>
                </nav>
            </div>
            <div>
                <a href="sales.php" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Back to Sales
                </a>
                <a href="sale_form.php?id=<?= $saleId ?>" class="btn btn-outline-primary">
                    <i class="bi bi-pencil"></i> Edit Sale
                </a>
            </div>
        </div>

        <!-- Sale Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Sale Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <strong>Invoice:</strong><br>
                                <span class="badge bg-primary fs-6"><?= htmlspecialchars($sale['invoice_number']) ?></span>
                            </div>
                            <div class="col-6">
                                <strong>Date:</strong><br>
                                <?= !empty($sale['sales_date']) ? date('d/m/Y', strtotime($sale['sales_date'])) : '-' ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <strong>Customer:</strong><br>
                                <?= htmlspecialchars($sale['customer_name'] ?? 'Unknown') ?>
                            </div>
                            <div class="col-6">
                                <strong>Cashier:</strong><br>
                                <?= htmlspecialchars($sale['user_name'] ?? 'Unknown') ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-calculator"></i> Financial Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <strong>Total Amount:</strong><br>
                                <span class="text-primary fs-5"><?= formatCurrency($totalAmount) ?></span>
                            </div>
                            <div class="col-6">
                                <strong>Discount:</strong><br>
                                <?= ($sale['discount'] ?? 0) > 0 ? '<span class="text-success">-' . formatCurrency($sale['discount']) . '</span>' : '-' ?>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <strong>Grand Total:</strong><br>
                                <span class="text-success fs-5 fw-bold"><?= formatCurrency($grandTotal) ?></span>
                            </div>
                            <div class="col-6">
                                <strong>Paid Amount:</strong><br>
                                <span class="text-info fs-5"><?= formatCurrency($paidAmount) ?></span>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <strong>Change:</strong><br>
                                <span class="text-warning fs-5"><?= formatCurrency($changeAmount) ?></span>
                            </div>
                            <div class="col-6">
                                <strong>Status:</strong><br>
                                <?php if ($paidAmount >= $grandTotal): ?>
                                    <span class="badge bg-success">Paid</span>
                                <?php elseif ($paidAmount > 0): ?>
                                    <span class="badge bg-warning">Partial</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Unpaid</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-box"></i> Products in Transaction</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="bi bi-plus-circle"></i> Add Product
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($saleDetails)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-box text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No products added yet. Click "Add Product" to start.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>No</th>
                                    <th>Product</th>
                                    <th>Code</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($saleDetails as $index => $detail): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= htmlspecialchars($detail['product_name']) ?></td>
                                    <td><code><?= htmlspecialchars($detail['product_code']) ?></code></td>
                                    <td><?= $detail['quantity'] ?></td>
                                    <td><?= formatCurrency($detail['price']) ?></td>
                                    <td><strong class="text-primary"><?= formatCurrency($detail['subtotal']) ?></strong></td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="editProduct(<?= $detail['id'] ?>, '<?= htmlspecialchars($detail['product_name']) ?>', <?= $detail['quantity'] ?>, <?= $detail['price'] ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteProduct(<?= $detail['id'] ?>, '<?= htmlspecialchars($detail['product_name']) ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment History Section -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Payment History</h5>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                    <i class="bi bi-plus-circle"></i> Add Payment
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($payments)): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-credit-card text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No payments recorded yet. Click "Add Payment" to start.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($payment['payment_date'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $payment['payment_method'] === 'cash' ? 'success' : ($payment['payment_method'] === 'transfer' ? 'primary' : 'warning') ?>">
                                            <?= ucfirst($payment['payment_method']) ?>
                                        </span>
                                    </td>
                                    <td><strong class="text-success"><?= formatCurrency($payment['amount']) ?></strong></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="deletePayment(<?= $payment['id'] ?>, <?= $saleId ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Product to Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_item.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="sale_id" value="<?= $saleId ?>">
                        
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product <span class="text-danger">*</span></label>
                            <select class="form-select" id="product_id" name="product_id" required>
                                <option value="">Select Product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= $product['id'] ?>" data-price="<?= $product['price'] ?>">
                                        <?= htmlspecialchars($product['name']) ?> - <?= htmlspecialchars($product['code']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="quantity" name="quantity" 
                                   min="1" value="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="price" name="price" 
                                       step="0.01" min="0" required readonly>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subtotal" class="form-label">Subtotal</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control" id="subtotal" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Payment Modal -->
    <div class="modal fade" id="addPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_payment.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="sale_id" value="<?= $saleId ?>">
                        
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="amount" name="amount" 
                                       step="0.01" min="0" required>
                            </div>
                            <small class="text-muted">Remaining: <?= formatCurrency($grandTotal - $paidAmount) ?></small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" id="payment_date" 
                                   name="payment_date" value="<?= date('Y-m-d\TH:i') ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Add Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="update_item.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="edit_item_id" name="item_id">
                        <input type="hidden" name="sale_id" value="<?= $saleId ?>">
                        
                        <div class="mb-3">
                            <label for="edit_product_name" class="form-label">Product</label>
                            <input type="text" class="form-control" id="edit_product_name" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_quantity" name="quantity" 
                                   min="1" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_price" class="form-label">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" class="form-control" id="edit_price" name="price" 
                                       step="0.01" min="0" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_subtotal" class="form-label">Subtotal</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control" id="edit_subtotal" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Debug: Check if form exists
        console.log('Form elements:', {
            product_id: document.getElementById('product_id'),
            quantity: document.getElementById('quantity'),
            price: document.getElementById('price'),
            subtotal: document.getElementById('subtotal')
        });

        // Product selection
        document.getElementById('product_id').addEventListener('change', function() {
            console.log('Product changed:', this.value);
            const selectedOption = this.options[this.selectedIndex];
            const price = selectedOption.dataset.price || 0;
            console.log('Selected price:', price);
            document.getElementById('price').value = price;
            calculateSubtotal();
        });

        document.getElementById('quantity').addEventListener('input', function() {
            console.log('Quantity changed:', this.value);
            calculateSubtotal();
        });
        document.getElementById('price').addEventListener('input', function() {
            console.log('Price changed:', this.value);
            calculateSubtotal();
        });

        function calculateSubtotal() {
            const quantity = parseFloat(document.getElementById('quantity').value) || 0;
            const price = parseFloat(document.getElementById('price').value) || 0;
            const subtotal = quantity * price;
            console.log('Calculating subtotal:', quantity, '*', price, '=', subtotal);
            document.getElementById('subtotal').value = formatCurrency(subtotal);
        }

        // Form submission debug
        document.addEventListener('DOMContentLoaded', function() {
            const addProductForm = document.querySelector('#addProductModal form');
            if (addProductForm) {
                console.log('Add product form found');
                addProductForm.addEventListener('submit', function(e) {
                    console.log('Form submitted!');
                    console.log('Form data:', {
                        sale_id: this.querySelector('[name="sale_id"]').value,
                        product_id: this.querySelector('[name="product_id"]').value,
                        quantity: this.querySelector('[name="quantity"]').value,
                        price: this.querySelector('[name="price"]').value
                    });
                    
                    // Check if all required fields are filled
                    const productId = this.querySelector('[name="product_id"]').value;
                    const quantity = this.querySelector('[name="quantity"]').value;
                    const price = this.querySelector('[name="price"]').value;
                    
                    if (!productId || !quantity || !price) {
                        e.preventDefault();
                        alert('Please fill all required fields!');
                        console.log('Validation failed:', { productId, quantity, price });
                        return false;
                    }
                    
                    console.log('Form validation passed, submitting...');
                });
            } else {
                console.log('Add product form not found!');
            }
        });

        // Edit product
        function editProduct(itemId, productName, quantity, price) {
            document.getElementById('edit_item_id').value = itemId;
            document.getElementById('edit_product_name').value = productName;
            document.getElementById('edit_quantity').value = quantity;
            document.getElementById('edit_price').value = price;
            calculateEditSubtotal();
            
            new bootstrap.Modal(document.getElementById('editProductModal')).show();
        }

        document.getElementById('edit_quantity').addEventListener('input', calculateEditSubtotal);
        document.getElementById('edit_price').addEventListener('input', calculateEditSubtotal);

        function calculateEditSubtotal() {
            const quantity = parseFloat(document.getElementById('edit_quantity').value) || 0;
            const price = parseFloat(document.getElementById('edit_price').value) || 0;
            const subtotal = quantity * price;
            document.getElementById('edit_subtotal').value = formatCurrency(subtotal);
        }

        // Delete product
        function deleteProduct(itemId, productName) {
            if (confirm(`Are you sure you want to remove "${productName}" from this transaction?`)) {
                window.location.href = `delete_item.php?id=${itemId}&sale_id=<?= $saleId ?>`;
            }
        }

        // Delete payment
        function deletePayment(paymentId, saleId) {
            if (confirm('Are you sure you want to delete this payment?')) {
                window.location.href = `delete_payment.php?id=${paymentId}&sale_id=${saleId}`;
            }
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount);
        }

        // Initialize calculations
        calculateSubtotal();
    </script>
</body>
</html>
