<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';
    require_once 'includes/group-membership.php';

    requireLogin();

    if (!isset($_GET['group_id'])) {
        echo "Group ID is required.";
        exit();
    }
    $groupId = $_GET['group_id'];

    // Check if the logged-in user is an administrator of the group
    $membership = getGroupMembership($pdo, $groupId, getUserId());

    if (!$membership || $membership['role_name'] !== 'administrator') {
        echo "You do not have permission to view applications for this group.";
        exit();
    }

    // Fetch group details
    $sql = "SELECT id, name AS group_name FROM forum_groups WHERE id = :group_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    $group = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$group) {
        echo "Group not found.";
        exit();
    }

    // Fetch applications for the group
    $sql = "SELECT applications.id, applications.user_id, applications.status, users.username 
            FROM applications 
            JOIN users ON applications.user_id = users.id 
            WHERE applications.group_id = :group_id AND applications.status = 'pending'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':group_id' => $groupId]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications</title>
</head>
<body>

    <h2>Pending Applications for <?php echo htmlspecialchars($group['group_name']); ?></h2>

    <?php if (empty($applications)): ?>
        <p>No pending applications.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($applications as $application): ?>
                <li>
                    <?php echo htmlspecialchars($application['username']); ?> 

                    <form
                    class="approve-form"
                    method="POST"
                    action="approve-application.php">
                        <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                        <button type="submit">Approve</button>
                    </form>

                    <form
                    class="reject-form"
                    method="POST"
                    action="reject-application.php">
                        <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                        <button type="submit">Reject</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <div class="back-to-group">
        <button onclick="window.location.href='group.php?id=<?php echo $group['id']; ?>'">Back to Group</button>
    
</body>
</html>