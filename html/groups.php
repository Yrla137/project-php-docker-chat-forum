<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';

    requireLogin();

    $sql = "SELECT 
            forum_groups.id,
            forum_groups.name,
            forum_groups.description,
            group_roles.name AS role_name
        FROM forum_groups
        LEFT JOIN group_members 
            ON forum_groups.id = group_members.group_id
            AND group_members.user_id = :user_id
        LEFT JOIN group_roles 
            ON group_members.role_id = group_roles.id";

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
    </div>

        <?php foreach ($groups as $group): ?>

            <div class="group-information">
                <h3><?php echo htmlspecialchars($group['name']); ?></h3>
                <p><?php echo htmlspecialchars($group['description']); ?></p>

                <?php if ($group['role_name'] !== null): ?>
                    <a href="group.php?id=<?php echo $group['id']; ?>">View Group</a>
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
            </div>

        <?php endforeach; ?>

        <?php require_once 'includes/delete-confirm.php'; ?>
</body>
</html>
