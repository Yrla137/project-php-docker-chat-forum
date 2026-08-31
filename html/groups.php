<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';

    requireLogin();

    $sql = "SELECT id, name, description FROM forum_groups";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Groups</title>
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

    <?php if (empty($groups)): ?>
        <p>No groups available.</p>
    <?php else: ?>
        <?php foreach ($groups as $group): ?>
            <div class="group-information">
                <h3><?php echo htmlspecialchars($group['name']); ?></h3>
                <p><?php echo htmlspecialchars($group['description']); ?></p>
                <a href="group.php?id=<?php echo $group['id']; ?>">View Group</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>
