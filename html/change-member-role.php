<?php

require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/group-membership.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_id = (int) ($_POST['group_id'] ?? 0);
    $member_id = (int) ($_POST['member_id'] ?? 0);
    $new_role = $_POST['new_role'] ?? '';
    $admin_id = getUserId();

    if ($group_id <= 0) {
        echo "Group ID is required.";
        exit();
    }

    if ($member_id <= 0) {
        echo "Member ID is required.";
        exit();
    }

    if (empty($new_role)) {
        echo "New role is required.";
        exit();
    }

    // Check that the logged-in user is an administrator of the group.
    $adminMembership = getGroupMembership($pdo, $group_id, $admin_id);

    if (!$adminMembership || $adminMembership['role_name'] !== 'administrator') {
        echo "You do not have permission to change member roles in this group.";
        exit();
    }

    // Check that the member is not the administrator.
    if ($member_id === (int) $admin_id) {
        echo "You cannot change your own role.";
        exit();
    }

    // Only allow roles that exist in the application.
    $allowed_roles = ['member', 'administrator'];

    if (!in_array($new_role, $allowed_roles, true)) {
        echo "Invalid role specified.";
        exit();
    }

    // Check that the selected user is a member of the group.
    $memberMembership = getGroupMembership($pdo, $group_id, $member_id);

    if (!$memberMembership) {
        echo "The specified member is not a member of this group.";
        exit();
    }

    try {
        // Update the member's role.
        $sql = "UPDATE group_members
                SET role_id = (
                    SELECT id
                    FROM group_roles
                    WHERE name = :new_role
                )
                WHERE group_id = :group_id
                  AND user_id = :member_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':new_role' => $new_role,
            ':group_id' => $group_id,
            ':member_id' => $member_id
        ]);

        header("Location: group.php?id=" . $group_id . "&action=role_changed");
        exit();

    } catch (PDOException $e) {
        error_log("Changing member role failed: " . $e->getMessage());
        echo "Could not change the member role. Please try again.";
        exit();
    }
}