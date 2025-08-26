<?php
/**
 * Script untuk import database penjualan_db secara otomatis
 * Pastikan XAMPP MySQL service sudah berjalan
 */

// Konfigurasi database
$host = 'localhost';
$username = 'root';
$password = '';

echo "=== Import Database penjualan_db ===\n";
echo "Pastikan XAMPP MySQL service sudah berjalan!\n\n";

try {
    // Koneksi ke MySQL tanpa database
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Berhasil koneksi ke MySQL server\n";
    
    // Baca file SQL
    $sqlFile = 'penjualan_db.sql';
    if (!file_exists($sqlFile)) {
        die("❌ File $sqlFile tidak ditemukan!\n");
    }
    
    $sql = file_get_contents($sqlFile);
    echo "✓ File SQL berhasil dibaca\n";
    
    // Split SQL statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "✓ Mulai import database...\n";
    
    // Execute setiap statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                echo "✓ Statement berhasil dieksekusi\n";
            } catch (PDOException $e) {
                // Skip error untuk DROP DATABASE dan CREATE DATABASE
                if (strpos($statement, 'DROP DATABASE') !== false || 
                    strpos($statement, 'CREATE DATABASE') !== false ||
                    strpos($statement, 'USE') !== false) {
                    echo "⚠ Statement database di-skip (normal)\n";
                } else {
                    echo "❌ Error: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "\n=== Import Selesai ===\n";
    echo "Database penjualan_db berhasil dibuat!\n";
    echo "Silakan buka aplikasi di: http://localhost/penjualan_vanesa/\n";
    echo "Login dengan:\n";
    echo "- Username: admin\n";
    echo "- Password: password\n";
    
} catch (PDOException $e) {
    echo "❌ Koneksi gagal: " . $e->getMessage() . "\n";
    echo "\nPastikan:\n";
    echo "1. XAMPP sudah berjalan\n";
    echo "2. MySQL service aktif\n";
    echo "3. Username dan password benar\n";
}
?>
