<?php

require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    // Clear all session data and destroy the session.
    $_SESSION = [];
    session_destroy();

    header("Location: ../login.php");
    exit();
}