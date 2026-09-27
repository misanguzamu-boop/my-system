<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'mysql-1c3c0ac3-aichitectureportfolio.c.aivencloud.com';
$user = 'avnadmin';
$pass = 'AVNS_pTQkXotVlqOKIcrzcmg';
$db_name = 'defaultdb';
$port = 17617;

// Connect using correct host, database and port
$conn = new mysqli($host, $user, $pass, $db_name, $port);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

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

// Setup customer orders
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

// Setup followers
$conn->query("CREATE TABLE IF NOT EXISTS followers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE,
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Create default architect account
$check_admin = $conn->query(
    "SELECT id FROM users WHERE username='shema'"
);

if ($check_admin && $check_admin->num_rows == 0) {
    $admin_pass = password_hash(
        'shema123',
        PASSWORD_BCRYPT
    );

    $stmt = $conn->prepare(
        "INSERT INTO users
        (fullname, email, username, password, role)
        VALUES (?, ?, ?, ?, ?)"
    );

    $fullname = 'Shema Shingela Daudi';
    $email = 'shema@arch.com';
    $username = 'shema';
    $role = 'architect';

    $stmt->bind_param(
        "sssss",
        $fullname,
        $email,
        $username,
        $admin_pass,
        $role
    );

    $stmt->execute();
    $stmt->close();
}
?>
