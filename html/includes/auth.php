<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Check if the user is logged in.
    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // Require the user to be logged in before accessing the page.
    function requireLogin() {
        if (!isLoggedIn()) {
            header("Location: login.php");
            exit();
        }
    }

    // Get the logged-in user's ID, or null if no user is logged in.
    function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }