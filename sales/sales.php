<?php
require_once '../config.php';
require_once '../functions.php';

$sales = getSales();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="bi bi-receipt text-primary me-2"></i>
                        Sales Transactions
                    </h2>
                    <p class="text-muted">Manage sales invoices and payments</p>
                </div>
                <div>
                    <a href="../index.php" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                    </a>
                    <a href="sale_form.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Add Transaction
                    </a>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Sales Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="salesTable" class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Invoice</th>
                                <th>Customer</th>
                                <th>Kasir</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Discount</th>
                                <th>Grand Total</th>
                                <th>Payment Method</th>
                                <th>Paid Amount</th>
                                <th>Change</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary"><?= htmlspecialchars($sale['invoice_number']) ?></span>
                                </td>
                                <td><?= htmlspecialchars($sale['customer_name']) ?></td>
                                <td><?= htmlspecialchars($sale['user_name']) ?></td>
                                <td>
                                    <?php if (!empty($sale['sales_date'])): ?>
                                        <?= date('d/m/Y', strtotime($sale['sales_date'])) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="text-primary"><?= formatCurrency($sale['total_amount']) ?></strong>
                                </td>
                                <td>
                                    <?php if ($sale['discount'] > 0): ?>
                                        <span class="text-success">-<?= formatCurrency($sale['discount']) ?></span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong class="text-success"><?= formatCurrency($sale['grand_total']) ?></strong>
                                </td>
                                <td>
                                    <?php if (!empty($sale['payment_method'])): ?>
                                        <span class="badge bg-<?= $sale['payment_method'] === 'cash' ? 'success' : ($sale['payment_method'] === 'transfer' ? 'primary' : 'warning') ?>">
                                            <?= ucfirst($sale['payment_method']) ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($sale['paid_amount'] > 0): ?>
                                        <span class="text-info"><?= formatCurrency($sale['paid_amount']) ?></span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($sale['change_amount'] > 0): ?>
                                        <span class="text-warning"><?= formatCurrency($sale['change_amount']) ?></span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="sale_details.php?id=<?= $sale['id'] ?>" class="btn btn-sm btn-outline-info me-1" title="Product Details">
                                            <i class="bi bi-list-ul"></i>
                                        </a>
                                        <a href="sale_form.php?id=<?= $sale['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteSale(<?= $sale['id'] ?>, '<?= htmlspecialchars($sale['invoice_number']) ?>')" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete sale: <strong id="deleteSaleInvoice"></strong>?</p>
                    <p class="text-danger">This will also delete all related sale details!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#salesTable').DataTable({
                order: [[0, 'desc']],
                pageLength: 25,
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });
        });

        function deleteSale(saleId, invoiceNumber) {
            document.getElementById('deleteSaleInvoice').textContent = invoiceNumber;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
            
            document.getElementById('confirmDelete').onclick = function() {
                window.location.href = `delete_sale.php?id=${saleId}`;
            };
        }
    </script>
</body>
</html>
