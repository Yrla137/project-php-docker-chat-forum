<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';

    requireLogin();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $groupId = $_POST['group_id'] ?? null;
            $userId = getUserId();

        if (!$groupId) {
            echo "Group ID is required.";
            exit();
        }
        
        try{
            // Start of transaction
            $pdo->beginTransaction();

            // Check if the user is an administrator of the group and if the group exists
            $sql = "SELECT forum_groups.id, group_members.user_id, group_members.role_id, group_roles.name AS role_name FROM forum_groups 
                    JOIN group_members ON forum_groups.id = group_members.group_id 
                    JOIN group_roles ON group_members.role_id = group_roles.id 
                    WHERE forum_groups.id = :group_id AND group_members.user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':group_id' => $groupId, ':user_id' => $userId]);
            $group = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$group) {
                throw new Exception("Group not found.");
            }

            if ($group['role_name'] !== 'administrator') {
                throw new Exception("You are not authorized to delete this group.");
            }

            // Delete related data from child tables before deleting the group
            // Delete posts belonging to discussions in this group
            $sql = "DELETE FROM posts WHERE discussion_id IN (SELECT id FROM discussions WHERE group_id = :group_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':group_id' => $groupId]);

            $sql = "DELETE FROM discussions WHERE group_id = :group_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':group_id' => $groupId]);

            $sql = "DELETE FROM applications WHERE group_id = :group_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':group_id' => $groupId]);

            $sql = "DELETE FROM invitations WHERE group_id = :group_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':group_id' => $groupId]);

            $sql = "DELETE FROM group_members WHERE group_id = :group_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':group_id' => $groupId]);

            $sql = "DELETE FROM forum_groups WHERE id = :group_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':group_id' => $groupId]);

            if ($stmt->rowCount() > 0) {
                // Commit the transaction
                $pdo->commit();
                header("Location: groups.php");
                exit();
            } else {
                throw new Exception("You are not authorized to delete this group or it does not exist.");
            }

        } catch (Exception $e) {
            // Rollback the transaction in case of an error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Database error: " . $e->getMessage());
            echo "Something went wrong, please try again.";
        }

    }