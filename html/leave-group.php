<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';
    require_once 'includes/group-membership.php';

    requireLogin();

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $group_id = $_POST['group_id'] ?? null;
        $user_id = getUserId();

        if(!$group_id){
            echo "Group ID is required.";
            exit();
        }

        // Check if the logged-in user is a member of the group
        $membership = getGroupMembership($pdo, $group_id, $user_id);

        if(!$membership){
            echo "You are not a member of this group.";
            exit();
        }

        if ($membership['role_name'] !== 'member') {
            echo "Only group members can leave the group.";
            exit();
        }

        try{
            // Remove the user from the group
            $sql = "DELETE FROM group_members WHERE group_id = :group_id AND user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':group_id' => $group_id, ':user_id' => $user_id]);

            header("Location: groups.php?action=left");
            exit();
        } catch (Exception $e) {
            echo "Error leaving group: " . $e->getMessage();
            exit();
        }
    }