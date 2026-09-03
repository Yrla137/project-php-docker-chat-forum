<?php

require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/group-membership.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $application_id = (int) ($_POST['application_id'] ?? 0);

    if ($application_id <= 0) {
        echo "Application ID is required.";
        exit();
    }

    try {
        // Get the application.
        $sql = "SELECT id, user_id, group_id, status
                FROM applications
                WHERE id = :application_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':application_id' => $application_id]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$application) {
            echo "Application not found.";
            exit();
        }

        if ($application['status'] !== 'pending') {
            echo "This application has already been processed.";
            exit();
        }

        // Check that the logged-in user is an administrator of the group.
        $membership = getGroupMembership($pdo, $application['group_id'], getUserId());

        if (!$membership || $membership['role_name'] !== 'administrator') {
            echo "You do not have permission to reject applications for this group.";
            exit();
        }

        // Reject the application.
        $sql = "UPDATE applications
                SET status = 'rejected'
                WHERE id = :application_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':application_id' => $application_id]);

        header("Location: applications.php?group_id=" . $application['group_id'] . "&action=rejected");
        exit();

    } catch (PDOException $e) {
        error_log("Rejecting application failed: " . $e->getMessage());
        echo "Could not reject the application. Please try again.";
        exit();
    }
}