<?php

// Get environment variables for database connection
    $db_host = getenv('DB_HOST');
    $db_port = getenv('DB_PORT');
    $db_name = getenv('DB_NAME');
    $db_user = getenv('DB_USER');
    $db_password = getenv('DB_PASSWORD');

    // The DSN (Data Source Name) tells PDO which database to connect to.
    // utf8mb4 supports a wide range of characters including emojis.
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";

    try {
        // Create the PDO connection.
         $pdo = new PDO($dsn, $db_user, $db_password);
        
        // Make PDO throw exceptions when a database error occurs.
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    } catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}