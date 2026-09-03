<?php

require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/group-membership.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_id = (int) ($_POST['group_id'] ?? 0);
    $user_id = getUserId();

    if ($group_id <= 0) {
        echo "Group ID is required.";
        exit();
    }

    // Check that the logged-in user is an administrator of the group.
    $membership = getGroupMembership($pdo, $group_id, $user_id);

    if (!$membership || $membership['role_name'] !== 'administrator') {
        echo "You do not have permission to create an invitation for this group.";
        exit();
    }

    // Generate a secure random token for the invitation.
    $token = bin2hex(random_bytes(32));

    try {
        // Save the invitation.
        $sql = "INSERT INTO invitations (group_id, token, created_by)
                VALUES (:group_id, :token, :created_by)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':group_id' => $group_id,
            ':token' => $token,
            ':created_by' => $user_id
        ]);

        header("Location: ../invitation-link.php?token=" . urlencode($token));
        exit();

    } catch (PDOException $e) {
        error_log("Creating invitation failed: " . $e->getMessage());
        echo "Creating invitation failed. Please try again.";
        exit();
    }
}
