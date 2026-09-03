<?php

require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/group-membership.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    echo "Invitation token is required.";
    exit();
}

if (!isLoggedIn()) {
    header("Location: ../login.php?redirect=actions/accept-invitation.php&token=" . urlencode($token));
    exit();
}

$userId = getUserId();

try {
    // Fetch the invitation.
    $sql = "SELECT id, group_id, created_at, used
            FROM invitations
            WHERE token = :token";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':token' => $token]);
    $invitation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invitation) {
        echo "Invalid invitation token.";
        exit();
    }

    if ((int) $invitation['used'] === 1) {
        echo "This invitation link has already been used.";
        exit();
    }

    // Check if the invitation has expired.
    $createdAt = new DateTime($invitation['created_at']);
    $expirationTime = clone $createdAt;
    $expirationTime->modify('+24 hours');

    $now = new DateTime();

    if ($now > $expirationTime) {
        echo "This invitation link has expired.";
        exit();
    }

    // Check if the user is already a member of the group.
    $membership = getGroupMembership($pdo, $invitation['group_id'], $userId);

    if ($membership) {
        echo "You are already a member of this group.";
        exit();
    }

    $pdo->beginTransaction();

    // Add the user to the group as a member.
    $sql = "INSERT INTO group_members (group_id, user_id, role_id)
            VALUES (
                :group_id,
                :user_id,
                (SELECT id FROM group_roles WHERE name = 'member')
            )";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':group_id' => $invitation['group_id'],
        ':user_id' => $userId
    ]);

    // Mark the invitation as used.
    $sql = "UPDATE invitations
            SET used = 1
            WHERE id = :invitation_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':invitation_id' => $invitation['id']]);

    $pdo->commit();

    header("Location: ../group.php?id=" . (int) $invitation['group_id'] . "&action=joined");
    exit();

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Accepting invitation failed: " . $e->getMessage());
    echo "Could not accept the invitation. Please try again.";
    exit();
}