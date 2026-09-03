<?php

// Get a user's membership and role in a specific group.
function getGroupMembership($pdo, $groupId, $userId) {
    $sql = "SELECT 
                group_members.role_id,
                group_roles.name AS role_name
            FROM group_members
            JOIN group_roles 
                ON group_members.role_id = group_roles.id
            WHERE group_members.group_id = :group_id
              AND group_members.user_id = :user_id";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':group_id' => $groupId,
        ':user_id' => $userId
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}