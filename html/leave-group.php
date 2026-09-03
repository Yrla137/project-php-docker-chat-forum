<?php

require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/group-membership.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupId = (int) ($_POST['group_id'] ?? 0);
    $user_id = getUserId();

    if ($groupId <= 0) {
        echo "Group ID is required.";
        exit();
    }

    // Check that the logged-in user is a member of the group.
    $membership = getGroupMembership($pdo, $groupId, $user_id);

    if (!$membership) {
        echo "You are not a member of this group.";
        exit();
    }

    if ($membership['role_name'] !== 'member') {
        echo "Only group members can leave the group.";
        exit();
    }

    try {
        // Remove the user's membership from the group.
        $sql = "DELETE FROM group_members
                WHERE group_id = :group_id
                  AND user_id = :user_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':group_id' => $groupId,
            ':user_id' => $user_id
        ]);

        header("Location: groups.php?action=left");
        exit();

    } catch (PDOException $e) {
        error_log("Leaving group failed: " . $e->getMessage());
        echo "Could not leave the group. Please try again.";
        exit();
    }
}