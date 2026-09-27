<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'mysql-1c3c0ac3-aichitectureportfolio.c.aivencloud.com';
$user = 'avnadmin';
$pass = 'AVNS_pTQkXotVlqOKIcrzcmg';
$db_name = 'defaultdb';
$Port='17617';
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Database Engine Offline: " . $conn->connect_error);
}

// Instantiate automated database migration
$conn->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($db_name);

// Setup accounts matrix table
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    role VARCHAR(20) DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Setup design works table
$conn->query("CREATE TABLE IF NOT EXISTS designs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150),
    category VARCHAR(50),
    description TEXT,
    image_path VARCHAR(255),
    estimated_cost VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Setup customer orders configuration structure
$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    design_id INT,
    plot_size VARCHAR(100),
    custom_notes TEXT,
    status VARCHAR(50) DEFAULT 'Pending Review',
    architect_reply TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Setup followers tracking ledger
$conn->query("CREATE TABLE IF NOT EXISTS followers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create default portfolio owner credentials if missing
$check_admin = $conn->query("SELECT id FROM users WHERE username='shema'");
if ($check_admin && $check_admin->num_rows == 0) {
    $admin_pass = password_hash('shema123', PASSWORD_BCRYPT);
    $conn->query("INSERT INTO users (fullname, email, username, password, role) VALUES ('Shema Shingela Daudi', 'shema@arch.com', 'shema', '$admin_pass', 'architect')");
}
?>
