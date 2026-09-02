<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';
    require_once 'includes/group-membership.php';

    requireLogin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $groupId = $_POST['group_id'] ?? null;
        $userId = getUserId();

        if(!$groupId) {
            echo "Group ID is required.";
            exit();
        }

        $sql = "SELECT id FROM forum_groups WHERE id = :group_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':group_id' => $groupId]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$group) {
            echo "Group not found.";
            exit();
        }

        // Check if the user is already a member of the group
        $membership = getGroupMembership($pdo, $groupId, $userId);

        if ($membership) {
            echo "You are already a member of this group.";
            exit();
        }

        // Check if the user already has a pending application for this group
        $sql = "SELECT id, status FROM applications WHERE group_id = :group_id AND user_id = :user_id";
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

        try {

            if ($existingApplication && $existingApplication['status'] === 'rejected') {
                $sql = "UPDATE applications SET status = 'pending' WHERE id = :application_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':application_id' => $existingApplication['id']]);
                header("Location: groups.php?application_resubmitted=1");
                exit();
            }

            if ($existingApplication && $existingApplication['status'] === 'approved') {
                $sql = "UPDATE applications SET status = 'pending' WHERE id = :application_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([':application_id' => $existingApplication['id']]);
                header("Location: groups.php?application_resubmitted=1");
                exit();
            }

            // Insert the application into the applications table
            $sql = "INSERT INTO applications (group_id, user_id, status) VALUES (:group_id, :user_id, 'pending')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':group_id' => $groupId,
                ':user_id' => $userId
            ]);
            
            header("Location: groups.php?application_submitted=1");
            exit();

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }

    }