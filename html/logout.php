<?php
    require_once 'includes/auth.php';

    // Log out the user by destroying the session
    if($_SERVER['REQUEST_METHOD'] === "POST") {
        $_SESSION = [];
        // Clear all session variables
        session_destroy();
        // Destroys the session
        header("Location: login.php");
        exit();
    }
?>