# 🛒 Sales CRUD Application

Aplikasi CRUD berbasis **PHP + MySQL (PDO)** untuk manajemen penjualan dengan fitur master-detail transaksi lengkap.

## ✨ Fitur Utama

### 🏠 Menu Utama

- **Dashboard** dengan statistik real-time
- **Master Data**: Users, Customers, Categories, Products
- **Transaksi**: Sales (Invoices) → Sale Details → Payments

### 🔐 Master Data Management

- **Users**: CRUD pengguna dengan role (admin/kasir/manajer)
- **Customers**: CRUD pelanggan dengan data lengkap
- **Categories**: CRUD kategori produk
- **Products**: CRUD produk dengan foreign key ke kategori

### 💰 Transaksi Sales

- **Sales (Master)**: Buat invoice dengan customer, kasir, tanggal, metode pembayaran
- **Sale Details**: Tambah/edit/hapus item produk dalam invoice
- **Payments**: Catat pembayaran per invoice dengan metode beragam

### 🧮 Fitur Otomatis

- Hitung `subtotal = quantity × price` otomatis
- Update `total_amount` dan `grand_total` otomatis
- Update `paid_amount` dan `change_amount` otomatis
- Validasi stock saat transaksi
- Update stock produk otomatis

## 🚀 Teknologi

- **Backend**: PHP 7.4+ (PDO, prepared statements)
- **Database**: MySQL 5.7+
- **Frontend**: Bootstrap 5, DataTables, Bootstrap Icons
- **Architecture**: Modular, MVC-like structure
- **Security**: Input sanitization, password hashing, SQL injection prevention

## 📋 Database Schema

### Tabel Utama

```sql
users (id, name, email, password, role, created_at, updated_at)
customers (id, name, phone, email, address, created_at, updated_at)
categories (id, name, description, created_at, updated_at)
products (id, code, name, category_id, price, stock, unit, created_at, updated_at)
sales (id, invoice_number, customer_id, user_id, total_amount, discount, grand_total, payment_method, paid_amount, change_amount, sale_date, created_at, updated_at)
sale_details (id, sale_id, product_id, quantity, price, subtotal, created_at, updated_at)
payments (id, sale_id, amount, payment_date, payment_method, created_at, updated_at)
```

### Relasi

- `products.category_id` → `categories.id`
- `sales.customer_id` → `customers.id`
- `sales.user_id` → `users.id`
- `sale_details.sale_id` → `sales.id`
- `sale_details.product_id` → `products.id`
- `payments.sale_id` → `sales.id`

## 🛠️ Instalasi

### 1. Prerequisites

- XAMPP/WAMP/LAMP dengan PHP 7.4+
- MySQL 5.7+
- Web browser modern

### 2. Setup Database

```bash
# Import database schema
mysql -u root -p < database.sql

# Atau copy-paste isi file database.sql ke phpMyAdmin
```

### 3. Konfigurasi

Edit `config.php` sesuai environment:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sales_app');
define('DB_USER', 'root');
define('DB_PASS', '');
define('APP_URL', 'http://localhost/test');
```

### 4. Akses Aplikasi

```
http://localhost/penjualan_vanesa/
```

## 📁 Struktur File

```
test/
├── config.php              # Database & app config
├── functions.php           # Helper functions & CRUD operations
├── index.php              # Dashboard & menu utama
├── database.sql           # Database schema
├── README.md              # Dokumentasi ini
├── users/                 # Users CRUD
│   ├── list.php          # List users
│   ├── form.php          # Add/edit user form
│   ├── save.php          # Save/update user
│   └── delete.php        # Delete user
├── customers/             # Customers CRUD
│   ├── list.php          # List customers
│   ├── form.php          # Add/edit customer form
│   ├── save.php          # Save/update customer
│   └── delete.php        # Delete customer
├── categories/            # Categories CRUD
├── products/              # Products CRUD
└── sales/                 # Sales CRUD
    ├── list.php          # List sales/invoices
    ├── form.php          # Add/edit sale form
    ├── details.php       # Sale details & items
    ├── add_item.php      # Add sale item
    ├── add_payment.php   # Add payment
    └── delete.php        # Delete sale
```

## 🔄 Alur Transaksi

### 1. Buat Sale (Invoice)

```
Dashboard → Sales → New Sale → Form:
- Pilih Customer
- Pilih Kasir (User)
- Set Tanggal
- Pilih Metode Pembayaran
- Simpan → Buat record di tabel `sales`
```

### 2. Tambah Item Produk

```
Sales List → Detail → Add Item → Form:
- Pilih Product (dropdown)
- Set Quantity
- Price otomatis dari product.price
- Subtotal = qty × price (otomatis)
- Simpan → Insert ke `sale_details`
- Update `sales.total_amount` & `grand_total`
```

### 3. Catat Pembayaran

```
Sale Details → Add Payment → Form:
- Input Amount
- Pilih Metode Pembayaran
- Set Tanggal
- Simpan → Insert ke `payments`
- Update `sales.paid_amount` & `change_amount`
```

## 🎯 Fitur CRUD Lengkap

### ✅ Create

- Form input dengan validasi
- Foreign key dropdown dengan label referensi
- Auto-calculate fields (subtotal, totals, change)

### ✅ Read

- DataTables dengan search, sort, pagination
- Join query untuk foreign key display
- Dashboard dengan statistik real-time

### ✅ Update

- Form edit dengan data existing
- Validation & error handling
- Auto-update related tables

### ✅ Delete

- Confirmation modal
- Check foreign key constraints
- Cascade delete untuk related records

## 🔒 Security Features

- **PDO Prepared Statements** - Mencegah SQL injection
- **Input Sanitization** - XSS prevention
- **Password Hashing** - bcrypt encryption
- **Session Management** - User authentication
- **Role-based Access** - Menu filtering

## 📱 UI/UX Features

- **Responsive Design** - Bootstrap 5
- **Interactive Tables** - DataTables
- **Modern Icons** - Bootstrap Icons
- **Color-coded Status** - Badges & alerts
- **Modal Forms** - Clean user experience
- **Real-time Updates** - Auto-refresh totals

## 🧪 Testing

### Sample Data

Database sudah include sample data:

- Users: admin, kasir1, manajer
- Customers: John Doe, Jane Smith
- Categories: Elektronik, Pakaian, Makanan
- Products: Laptop, Smartphone, Kaos, Nasi Goreng

### Test Flow

1. Login dengan sample user
2. Buat customer baru
3. Buat sale invoice
4. Tambah produk ke invoice
5. Catat pembayaran
6. Verifikasi totals update otomatis

## 🚨 Troubleshooting

### Common Issues

1. **Database Connection Error**

   - Check XAMPP MySQL service running
   - Verify database credentials in `config.php`

2. **Page Not Found**

   - Ensure `.htaccess` not blocking
   - Check file permissions

3. **Foreign Key Error**
   - Verify related records exist
   - Check database constraints

### Debug Mode

```php
// In config.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## 🔮 Roadmap

### Phase 2

- [ ] User authentication & login system
- [ ] Role-based menu access
- [ ] Report generation (PDF/Excel)
- [ ] Stock alerts & notifications

### Phase 3

- [ ] Multi-currency support
- [ ] Tax calculation
- [ ] Customer credit system
- [ ] Advanced reporting

## 📞 Support

Untuk pertanyaan atau bug report:

1. Check troubleshooting section
2. Review error logs
3. Verify database schema
4. Test with sample data

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

---

**Happy Coding! 🎉**

Dibuat dengan ❤️ menggunakan PHP + MySQL + Bootstrap
