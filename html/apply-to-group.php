<?php

require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/group-membership.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupId = (int) ($_POST['group_id'] ?? 0);
    $userId = getUserId();

    if ($groupId <= 0) {
        echo "Group ID is required.";
        exit();
    }

    try {
        // Check that the group exists.
        $sql = "SELECT id FROM forum_groups WHERE id = :group_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':group_id' => $groupId]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$group) {
            echo "Group not found.";
            exit();
        }

        // Check that the user is not already a member of the group.
        $membership = getGroupMembership($pdo, $groupId, $userId);

        if ($membership) {
            echo "You are already a member of this group.";
            exit();
        }

        // Check whether the user already has an application for this group.
        $sql = "SELECT id, status
                FROM applications
                WHERE group_id = :group_id
                  AND user_id = :user_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':group_id' => $groupId,
            ':user_id' => $userId
        ]);

        $existingApplication = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingApplication && $existingApplication['status'] === 'pending') {
            echo "You already have a pending application for this group.";
            exit();
        }

        if ($existingApplication && $existingApplication['status'] === 'rejected') {
            $sql = "UPDATE applications
                    SET status = 'pending'
                    WHERE id = :application_id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':application_id' => $existingApplication['id']]);

            header("Location: groups.php?application_resubmitted=1");
            exit();
        }

        if ($existingApplication && $existingApplication['status'] === 'approved') {
            $sql = "UPDATE applications
                    SET status = 'pending'
                    WHERE id = :application_id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':application_id' => $existingApplication['id']]);

            header("Location: groups.php?application_resubmitted=1");
            exit();
        }

        // Create a new pending application.
        $sql = "INSERT INTO applications (group_id, user_id, status)
                VALUES (:group_id, :user_id, 'pending')";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':group_id' => $groupId,
            ':user_id' => $userId
        ]);

        header("Location: groups.php?application_submitted=1");
        exit();

    } catch (PDOException $e) {
        error_log("Applying to group failed: " . $e->getMessage());
        echo "Could not submit the application. Please try again.";
        exit();
    }
}