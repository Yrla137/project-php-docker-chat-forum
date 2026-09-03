<?php

require_once '../includes/database.php';
require_once '../includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $groupId = (int) ($_POST['group_id'] ?? 0);
    $userId = getUserId();

    if ($groupId <= 0) {
        echo "Group ID is required.";
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Check that the group exists and that the user is an administrator.
        $sql = "SELECT forum_groups.id, group_members.user_id, group_members.role_id,
                       group_roles.name AS role_name
                FROM forum_groups
                JOIN group_members ON forum_groups.id = group_members.group_id
                JOIN group_roles ON group_members.role_id = group_roles.id
                WHERE forum_groups.id = :group_id
                  AND group_members.user_id = :user_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':group_id' => $groupId,
            ':user_id' => $userId
        ]);

        $group = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$group) {
            $pdo->rollBack();
            echo "Group not found or you are not a member of this group.";
            exit();
        }

        if ($group['role_name'] !== 'administrator') {
            $pdo->rollBack();
            echo "You do not have permission to delete this group.";
            exit();
        }

        // Delete posts belonging to discussions in the group.
        $sql = "DELETE FROM posts
                WHERE discussion_id IN (
                    SELECT id
                    FROM discussions
                    WHERE group_id = :group_id
                )";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':group_id' => $groupId]);

        // Delete the group's discussions.
        $sql = "DELETE FROM discussions WHERE group_id = :group_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':group_id' => $groupId]);

        // Delete applications to the group.
        $sql = "DELETE FROM applications WHERE group_id = :group_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':group_id' => $groupId]);

        // Delete invitations to the group.
        $sql = "DELETE FROM invitations WHERE group_id = :group_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':group_id' => $groupId]);

        // Delete all group memberships.
        $sql = "DELETE FROM group_members WHERE group_id = :group_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':group_id' => $groupId]);

        // Delete the group.
        $sql = "DELETE FROM forum_groups WHERE id = :group_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':group_id' => $groupId]);

        if ($stmt->rowCount() > 0) {
            $pdo->commit();

            header("Location: ../groups.php");
            exit();
        }

        throw new Exception("The group could not be deleted.");

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log("Deleting group failed: " . $e->getMessage());
        echo "Something went wrong. Please try again.";
        exit();
    }
}