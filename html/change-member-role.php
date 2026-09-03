<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';
    require_once 'includes/group-membership.php';

    requireLogin();

        if($_SERVER['REQUEST_METHOD'] === 'POST'){

        $group_id = $_POST['group_id'] ?? null;
        $member_id = $_POST['member_id'] ?? null;
        $new_role = $_POST['new_role'] ?? null;

        if(!$group_id){
            echo "Group ID is required.";
            exit();
        }

        if(!$member_id){
            echo "Member ID is required.";
            exit();
        }

        if(!$new_role){
            echo "New role is required.";
            exit();
        }

        // Check if the logged-in user is an administrator of the group
        $admin_id = getUserId();

        $adminMembership = getGroupMembership($pdo, $group_id, $admin_id);

        if(!$adminMembership || $adminMembership['role_name'] !== 'administrator'){
            echo "You do not have permission to change member roles in this group.";
            exit();
        }

        // Checking allowed roles to prevent unauthorized role changes
        $allowed_roles = ['member', 'administrator'];
        if(!in_array($new_role, $allowed_roles)){
            echo "Invalid role specified.";
            exit();
        }

        $memberMembership = getGroupMembership($pdo, $group_id, $member_id);

        if (!$memberMembership) {
            echo "The specified member is not a member of this group.";
            exit();
        }

        try{
            //Update the member's role in the group
            $sql = "UPDATE group_members SET role_id = (SELECT id FROM group_roles WHERE name = :new_role) WHERE group_id = :group_id AND user_id = :member_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':new_role' => $new_role,
                ':group_id' => $group_id,
                ':member_id' => $member_id
            ]);

            header("Location: group.php?id=" . $group_id . "&action=role_changed");
            exit();
        } catch (Exception $e) {
            echo "Error changing member role: " . $e->getMessage();
            exit();
        }


    }