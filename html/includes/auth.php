<?php

    session_start();

    // Function to check if the user is logged in
    function isLoggedIn() {

        return isset($_SESSION['user_id']);
    }

    function requireLogin() {
        if (!isLoggedIn()) {
            // Redirect to login page if the user is not logged in
            header("Location: login.php");
            exit();
        }
    }

    // Function to get the logged-in user's ID
    function getUserId() {
        return $_SESSION['user_id'] ?? null;
        // Return the user ID or null if the user is not logged in
    }