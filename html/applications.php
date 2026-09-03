<?php

require_once 'includes/database.php';
require_once 'includes/auth.php';
require_once 'includes/group-membership.php';

requireLogin();

$groupId = (int) ($_GET['group_id'] ?? 0);

if ($groupId <= 0) {
    echo "Group ID is required.";
    exit();
}

// Check that the logged-in user is an administrator of the group.
$membership = getGroupMembership($pdo, $groupId, getUserId());

if (!$membership || $membership['role_name'] !== 'administrator') {
    echo "You do not have permission to view applications for this group.";
    exit();
}

try {
    // Fetch the group.
    $sql = "SELECT id, name AS group_name
            FROM forum_groups
            WHERE id = :group_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
        echo "Group not found.";
        exit();
    }

    // Fetch pending applications for the group.
    $sql = "SELECT applications.id, applications.user_id,
                   applications.status, users.username
            FROM applications
            JOIN users ON applications.user_id = users.id
            WHERE applications.group_id = :group_id
              AND applications.status = 'pending'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Loading applications failed: " . $e->getMessage());
    echo "Could not load applications. Please try again.";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications</title>
</head>
<body>

    <?php require_once 'includes/navbar.php'; ?>

    <main class="applications-container">
        <h2>Pending Applications for <?php echo htmlspecialchars($group['group_name']); ?></h2>

        <?php if (empty($applications)): ?>
            <p class="status-message">No pending applications.</p>
        <?php else: ?>
            <ul class="applications-list">
                <?php foreach ($applications as $application): ?>
                    <li class="application-item">
                        <span><?php echo htmlspecialchars($application['username']); ?></span>

                        <form class="approve-form" method="POST" action="actions/approve-application.php">
                            <input type="hidden" name="application_id" value="<?php echo (int) $application['id']; ?>">
                            <button class="form-button" type="submit">Approve</button>
                        </form>

                        <form class="reject-form" method="POST" action="actions/reject-application.php">
                            <input type="hidden" name="application_id" value="<?php echo (int) $application['id']; ?>">
                            <button class="danger-button" type="submit">Reject</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <div class="back-to-group">
            <a href="group.php?id=<?php echo (int) $group['id']; ?>">Back to Group</a>
        </div>
    </main>

</body>
</html>