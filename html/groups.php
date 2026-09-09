<?php

require_once 'includes/database.php';
require_once 'includes/auth.php';

requireLogin();

$error = null;

try {

    // Get all groups with the logged-in user's role and pending application status.
    $sql = "SELECT 
        forum_groups.id,
        forum_groups.name,
        forum_groups.description,
        group_roles.name AS role_name,
        applications.status AS application_status
    FROM forum_groups
    LEFT JOIN group_members 
        ON forum_groups.id = group_members.group_id
        AND group_members.user_id = :membership_user_id
    LEFT JOIN group_roles 
        ON group_members.role_id = group_roles.id
    LEFT JOIN applications
        ON forum_groups.id = applications.group_id
        AND applications.user_id = :application_user_id
        AND applications.status = 'pending'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':membership_user_id' => getUserId(),
        ':application_user_id' => getUserId()
    ]);

    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Could not load groups: " . $e->getMessage());
    $error = "Could not load groups. Please try again.";
    $groups = [];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Groups</title>
    <link rel="stylesheet" href="styles/global.css">
    <link rel="stylesheet" href="styles/groups.css">
    <link rel="stylesheet" href="styles/delete-confirm.css">
</head>

<body>

    <?php require_once 'includes/navbar.php'; ?>

    <main class="groups-page">

        <section class="groups-header">
            <div class="groups-header-text">
                <h1 class="groups-title">Groups</h1>
                <p class="groups-intro">Connect with others through shared thoughts and interests.</p>
            </div>

            <div class="create-group">
                <h2 class="create-group-title">Create a Group</h2>

                <form class="create-group-form" method="POST" action="actions/create-group.php">
                    <input class="form-input" type="text" name="group_name" placeholder="Enter group name" required>
                    <textarea class="form-textarea" name="group_description" placeholder="Enter group description" required></textarea>
                    <button class="form-button" type="submit">Create Group</button>
                </form>
            </div>
        </section>

        <?php if ($error): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <section class="groups-section">
            <div class="groups-list">

                <?php if (empty($groups)): ?>
                    <p class="empty-message">No groups available.</p>
                <?php else: ?>

                    <?php foreach ($groups as $group): ?>
                        <article class="group-card">

                            <div class="group-card-content">
                                <h2 class="group-card-title">
                                    <?php echo htmlspecialchars($group['name']); ?>
                                </h2>

                                <p class="group-card-description">
                                    <?php echo htmlspecialchars($group['description']); ?>
                                </p>
                            </div>

                            <div class="group-card-actions">

                                <?php if ($group['role_name'] !== null): ?>
                                    <a class="group-link" href="group.php?id=<?php echo (int) $group['id']; ?>">
                                        View Group
                                    </a>

                                <?php elseif ($group['application_status'] === 'pending'): ?>
                                    <p class="status-message">Waiting for approval.</p>

                                <?php else: ?>
                                    <form method="POST" action="actions/apply-to-group.php">
                                        <input type="hidden" name="group_id" value="<?php echo (int) $group['id']; ?>">
                                        <button class="form-button" type="submit">Apply to Join</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($group['role_name'] === 'member'): ?>
                                    <form
                                        class="delete-form"
                                        data-delete-message="Are you sure you want to leave this group?"
                                        method="POST"
                                        action="actions/leave-group.php">
                                        <input type="hidden" name="group_id" value="<?php echo (int) $group['id']; ?>">
                                        <button class="danger-button" type="submit">Leave Group</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($group['role_name'] === 'administrator'): ?>
                                    <form
                                        class="delete-form"
                                        data-delete-message="Are you sure you want to delete this group? This action cannot be undone."
                                        method="POST"
                                        action="actions/delete-group.php">
                                        <input type="hidden" name="group_id" value="<?php echo (int) $group['id']; ?>">
                                        <button class="danger-button" type="submit">Delete Group</button>
                                    </form>
                                <?php endif; ?>

                            </div>

                        </article>
                    <?php endforeach; ?>

                <?php endif; ?>

            </div>
        </section>

    </main>

    <?php require_once 'includes/delete-confirm.php'; ?>

</body>
</html>