<?php
// ============================================
// ARCHITECTURE PORTFOLIO - DATABASE CONNECTION
// Designed by Eng. Misangu Kabizi
// ============================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection settings
$host = 'mysql-1c3c0ac3-aichitectureportfolio.c.aivencloud.com';
$user = 'avnadmin';
$pass = 'AVNS_pTQkXotVlqOKIcrzcmg';
$db_name = 'defaultdb';
$port = 17617;

// Create database connection
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    $conn = mysqli_init();

    // Connection timeout
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 15);

    // Enable SSL for Aiven
    $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

    $conn->real_connect(
        $host,
        $user,
        $pass,
        $db_name,
        $port,
        NULL,
        MYSQLI_CLIENT_SSL
    );

    // Set character encoding
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {

    // Log technical error privately
    error_log("Database connection error: " . $e->getMessage());

    // Show safe message to visitors
    die(
        "Database connection failed. " .
        "Please check your database hostname, credentials, " .
        "database status and SSL configuration."
    );
}


// ============================================
// CREATE USERS TABLE
// ============================================

$conn->query("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fullname VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE,
        username VARCHAR(50) UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
");


// ============================================
// CREATE DESIGNS TABLE
// ============================================

$conn->query("
    CREATE TABLE IF NOT EXISTS designs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(150) NOT NULL,
        category VARCHAR(50),
        description TEXT,
        image_path VARCHAR(255),
        estimated_cost VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
");


// ============================================
// CREATE ORDERS TABLE
// ============================================

$conn->query("
    CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT,
        design_id INT,
        plot_size VARCHAR(100),
        custom_notes TEXT,
        status VARCHAR(50) DEFAULT 'Pending Review',
        architect_reply TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
");


// ============================================
// CREATE FOLLOWERS TABLE
// ============================================

$conn->query("
    CREATE TABLE IF NOT EXISTS followers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNIQUE,
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB
");


// ============================================
// CREATE DEFAULT ARCHITECT ACCOUNT
// ============================================

$check_admin = $conn->prepare("
    SELECT id FROM users WHERE username = ?
");

$username = 'shema';

$check_admin->bind_param("s", $username);
$check_admin->execute();

$result = $check_admin->get_result();

if ($result->num_rows === 0) {

    $admin_pass = password_hash(
        'CHANGE_THIS_ADMIN_PASSWORD',
        PASSWORD_DEFAULT
    );

    $fullname = 'Shema Shingela Daudi';
    $email = 'shema@arch.com';
    $role = 'architect';

    $stmt = $conn->prepare("
        INSERT INTO users
        (fullname, email, username, password, role)
        VALUES (?, ?, ?, ?, ?)
    ");

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

$check_admin->close();

?>
