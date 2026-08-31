<?php

    function getGroupMembership($pdo, $groupId, $userId) {
        $sql = "SELECT role_id FROM group_members WHERE group_id = :group_id AND user_id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':group_id' => $groupId,
            ':user_id' => $userId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
}