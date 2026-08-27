<?php

// GET environment variables for database connection
    $db_host = getenv('DB_HOST');
    $db_port = getenv('DB_PORT');
    $db_name = getenv('DB_NAME');
    $db_user = getenv('DB_USER');
    $db_password = getenv('DB_PASSWORD');

// PDO connection string
    // The DSN (Data Source Name) tells PDO which database to connect to.
    $dsn = "mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4";

    // charsert=utf8mb4 is used to ensure that the database can store a wide range of characters, including emojis and special symbols.

    try {
        // Create the connection between PHP and MySQL using PDO and the provided connection string, username, and password needed to access the database.
         $pdo = new PDO($dsn, $db_user, $db_password);
        
        // Make PDO throw exceptions when a database error occurs.
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        // Stop the script if the database connection fails.
        die("Database connection failed: " . $e->getMessage());
    }