<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';
    require_once 'includes/group-membership.php';

    requireLogin();

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $group_id = $_POST['group_id'] ?? null;
        $member_id = $_POST['member_id'] ?? null;
        $admin_id = getUserId();

        if(!$group_id){
            echo "Group ID is required.";
            exit();
        }

        if(!$member_id){
            echo "Member ID is required.";
            exit();
        }

        // Check if the logged-in user is an administrator of the group
        $adminMembership = getGroupMembership($pdo, $group_id, $admin_id);

        if(!$adminMembership || $adminMembership['role_name'] !== 'administrator'){
            echo "You do not have permission to remove members from this group.";
            exit();
        }

        $memberMembership = getGroupMembership($pdo, $group_id, $member_id);

        // Check if the member to be removed is actually a member of the group
        if (!$memberMembership) {
            echo "The specified member is not a member of this group.";
            exit();
        }

        // Check if the member to be removed is an administrator
        if ($memberMembership['role_name'] !== 'member') {
            echo "Administrators cannot be removed from the group.";
            exit();
        }

        try{

            // Remove the member from the group
            $sql = "DELETE FROM group_members WHERE group_id = :group_id AND user_id = :member_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':group_id' => $group_id, ':member_id' => $member_id]);

            header("Location: group.php?id=" . $group_id . "&action=member_removed");
            exit();
        } catch (Exception $e) {
            echo "Error removing member: " . $e->getMessage();
            exit();
        }
    }