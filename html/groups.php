<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';

    requireLogin();

    // SELECT groups information along with a LEFT JOIN to get the role of the logged-in user in each group
    $sql = "SELECT 
        forum_groups.id,
        forum_groups.name,
        forum_groups.description,
        group_roles.name AS role_name,
        applications.status AS application_status
    FROM forum_groups
    LEFT JOIN group_members 
        ON forum_groups.id = group_members.group_id
        AND group_members.user_id = :user_id
    LEFT JOIN group_roles 
        ON group_members.role_id = group_roles.id
    LEFT JOIN applications
        ON forum_groups.id = applications.group_id
        AND applications.user_id = :user_id
        AND applications.status = 'pending'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':user_id' => getUserId()
]);

$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Groups</title>
    <!-- CSS -->
    <link rel="stylesheet" href="styles/delete-confirm.css">
</head>
<body>

    <?php require_once 'includes/navbar.php'; ?>

    <h2>Forum Groups</h2>

    <div class="create-group-form">
        <form method="POST" action="create-group.php">
            <input type="text" name="group_name" placeholder="Enter group name" required>
            <textarea name="group_description" placeholder="Enter group description" required></textarea>
            <button type="submit">Create Group</button>
         </form>

        <?php if (empty($groups)): ?>
            <p>No groups available.</p>
        <?php else: ?>
            <?php foreach ($groups as $group): ?>
                    <div class="group">
                        <h3><?php echo htmlspecialchars($group['name']); ?></h3>
                        <p><?php echo htmlspecialchars($group['description']); ?></p>
                    </div>

                <?php if ($group['role_name'] !== null): ?>
                    <a href="group.php?id=<?php echo $group['id']; ?>">View Group</a>
                
                <?php elseif ($group['application_status'] === 'pending'): ?>
                    <p>Waiting for approval.</p>
                <?php else: ?>
                    <form method="POST" action="apply-to-group.php">
                        <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                        <button type="submit">Apply to join group</button>
                    </form>
                <?php endif; ?>

                <?php if ($group['role_name'] === 'member'): ?>
                    <form
                    class="delete-form"
                    data-delete-message = "Are you sure you want to leave this group?"
                    method="POST"
                    action="leave-group.php">
                        <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                        <button type="submit">Leave Group</button>
                    </form>
                <?php endif; ?>

                <?php if ($group['role_name'] === 'administrator'): ?>
                    <form
                    class="delete-form"
                    data-delete-message = "Are you sure you want to delete this group? This action cannot be undone."
                    method="POST"
                    action="delete-group.php">
                        <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                        <button type="submit">Delete Group</button>
                    </form>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php require_once 'includes/delete-confirm.php'; ?>

</body>
</html>
