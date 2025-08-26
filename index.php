<?php
require_once 'config.php';
require_once 'functions.php';

// Get counts for dashboard
$pdo = getDB();
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$customerCount = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$productCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$saleCount = $pdo->query("SELECT COUNT(*) FROM sales")->fetchColumn();
$totalSales = $pdo->query("SELECT SUM(grand_total) FROM sales")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 2px 0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .card-stats {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .card-stats:hover {
            transform: translateY(-5px);
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .bg-gradient-primary {
            background: linear-gradient(45deg, #007bff, #0056b3) !important;
        }
        .revenue-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0 sidebar">
                <div class="d-flex flex-column p-3">
                    <h4 class="text-white text-center mb-4">
                        <i class="bi bi-shop"></i> <?= APP_NAME ?>
                    </h4>
                    
                    <hr class="text-white">
                    
                    <h6 class="text-white-50 text-uppercase mb-2">Master Data</h6>
                    <nav class="nav flex-column mb-3">
                        <a class="nav-link" href="users/list.php">
                            <i class="bi bi-people me-2"></i> Users
                        </a>
                        <a class="nav-link" href="customers/list.php">
                            <i class="bi bi-person-badge me-2"></i> Customers
                        </a>
                        <a class="nav-link" href="categories/list.php">
                            <i class="bi bi-tags me-2"></i> Categories
                        </a>
                        <a class="nav-link" href="products/list.php">
                            <i class="bi bi-box me-2"></i> Products
                        </a>
                    </nav>
                    
                    <h6 class="text-white-50 text-uppercase mb-2">Transaksi</h6>
                    <nav class="nav flex-column">
                        <a class="nav-link" href="sales/sales.php">
                            <i class="bi bi-receipt me-2"></i> Sales (Invoices)
                        </a>
                    </nav>
                    
                    <hr class="text-white mt-auto">
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="container-fluid py-4">
                    <div class="row mb-4">
                        <div class="col-12">
                            <h2 class="mb-0">
                                <i class="bi bi-speedometer2 text-primary me-2"></i>
                                Dashboard
                            </h2>
                            <p class="text-muted">Selamat datang di aplikasi manajemen penjualan</p>
                        </div>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card card-stats bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Total Users</h6>
                                            <h3 class="mb-0"><?= $userCount ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-people fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card card-stats bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Total Customers</h6>
                                            <h3 class="mb-0"><?= $customerCount ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-person-badge fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card card-stats bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Total Products</h6>
                                            <h3 class="mb-0"><?= $productCount ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-box fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card card-stats bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Total Sales</h6>
                                            <h3 class="mb-0"><?= $saleCount ?></h3>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-receipt fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Revenue Card -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card revenue-card text-white">
                                <div class="card-body py-4">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h5 class="card-title mb-2">
                                                <i class="bi bi-graph-up-arrow me-2"></i>
                                                Total Revenue
                                            </h5>
                                            <h1 class="mb-2 fw-bold"><?= formatCurrency($totalSales ?? 0) ?></h1>
                                            <p class="mb-0 opacity-75">Dari semua transaksi penjualan</p>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <i class="bi bi-currency-dollar" style="font-size: 4rem; opacity: 0.8;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card card-stats">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="bi bi-lightning text-warning me-2"></i>
                                        Quick Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <a href="sales/form.php" class="btn btn-primary w-100 py-3">
                                                <i class="bi bi-plus-circle me-2"></i>
                                                <br><strong>New Sale</strong>
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="products/form.php" class="btn btn-success w-100 py-3">
                                                <i class="bi bi-box-seam me-2"></i>
                                                <br><strong>Add Product</strong>
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="customers/form.php" class="btn btn-info w-100 py-3">
                                                <i class="bi bi-person-plus me-2"></i>
                                                <br><strong>Add Customer</strong>
                                            </a>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <a href="users/form.php" class="btn btn-warning w-100 py-3">
                                                <i class="bi bi-people me-2"></i>
                                                <br><strong>Add User</strong>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
