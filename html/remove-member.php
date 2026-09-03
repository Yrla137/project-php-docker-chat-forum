<?php

require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/group-membership.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $group_id = (int) ($_POST['group_id'] ?? 0);
    $member_id = (int) ($_POST['member_id'] ?? 0);
    $admin_id = getUserId();

    if ($group_id <= 0) {
        echo "Group ID is required.";
        exit();
    }

    if ($member_id <= 0) {
        echo "Member ID is required.";
        exit();
    }

    // Check that the logged-in user is an administrator of the group.
    $adminMembership = getGroupMembership($pdo, $group_id, $admin_id);

    if (!$adminMembership || $adminMembership['role_name'] !== 'administrator') {
        echo "You do not have permission to remove members from this group.";
        exit();
    }

    // Check that the selected user is a member of the group.
    $memberMembership = getGroupMembership($pdo, $group_id, $member_id);

    if (!$memberMembership) {
        echo "The specified member is not a member of this group.";
        exit();
    }

    // Only ordinary members can be removed.
    if ($memberMembership['role_name'] !== 'member') {
        echo "Administrators cannot be removed from the group.";
        exit();
    }

    try {
        $sql = "DELETE FROM group_members
                WHERE group_id = :group_id
                  AND user_id = :member_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':group_id' => $group_id,
            ':member_id' => $member_id
        ]);

        header("Location: group.php?id=" . $group_id . "&action=member_removed");
        exit();

    } catch (PDOException $e) {
        error_log("Removing member failed: " . $e->getMessage());
        echo "Could not remove the member. Please try again.";
        exit();
    }
}