<?php

require_once '../includes/database.php';
require_once '../includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $group_name = trim($_POST['group_name'] ?? '');
    $group_description = trim($_POST['group_description'] ?? '');
    
    if (empty($group_name)) {
        echo "Please provide a group name.";
        exit();
    }

    if (empty($group_description)) {
        echo "Please provide a group description.";
        exit();
    }

    $created_by = getUserId();

    try {
        // Create the group and add its creator as administrator in one transaction.
        $pdo->beginTransaction();

        // Create the new group.
        $sql = "INSERT INTO forum_groups (name, description, created_by)
                VALUES (:group_name, :group_description, :created_by)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':group_name' => $group_name,
            ':group_description' => $group_description,
            ':created_by' => $created_by
        ]);

        $group_id = $pdo->lastInsertId();

        // Get the administrator role ID.
        $sql = "SELECT id FROM group_roles WHERE name = 'administrator'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        $role = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$role) {
            throw new Exception("Administrator role not found.");
        }

        $role_id = $role['id'];

        // Add the group creator as an administrator.
        $sql = "INSERT INTO group_members (group_id, user_id, role_id)
                VALUES (:group_id, :user_id, :role_id)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':group_id' => $group_id,
            ':user_id' => $created_by,
            ':role_id' => $role_id
        ]);

        $pdo->commit();
        
        header("Location: ../groups.php");
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log("Creating group failed: " . $e->getMessage());
        echo "Something went wrong. Please try again.";
        exit();
    }
}