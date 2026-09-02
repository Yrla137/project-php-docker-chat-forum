<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';
    require_once 'includes/group-membership.php';

    requireLogin();

    if (!isset($_GET['id'])) {
        header("Location: groups.php");
        exit();
    }

    $groupId = $_GET['id'];

    try{
        // Fetch group details
        $sql = "SELECT id, name, description FROM forum_groups WHERE id = :group_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':group_id' => $groupId]);
        $group = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$group) {
            echo "Group not found.";
            exit();
        }

        // Check that the logged-in user is a member of this group
        $membership = getGroupMembership($pdo, $groupId, getUserId());

        if (!$membership) {
            echo "You are not a member of this group.";
            exit();
        }

        // Fetch group members and their roles
        $sql = "SELECT users.id AS user_id, users.username, group_roles.name AS group_role FROM group_members 
                JOIN users ON group_members.user_id = users.id 
                JOIN group_roles ON group_members.role_id = group_roles.id 
                WHERE group_members.group_id = :group_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':group_id' => $groupId]);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch discussions related to the group
        $sql = "SELECT discussions.id, discussions.user_id, discussions.subject, discussions.created_at, users.username AS creator FROM discussions 
                JOIN users ON discussions.user_id = users.id 
                WHERE discussions.group_id = :group_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':group_id' => $groupId]);
        $discussions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group</title>
    <link rel="stylesheet" href="styles/delete-confirm.css">
</head>
<body>

    <?php require 'includes/navbar.php'; ?>

    <div class="back-to-groups">
        <button onclick="window.location.href='groups.php'">Back to Groups</button>
    </div>

    <div class="group-details">
        <h2><?php echo htmlspecialchars($group['name']); ?></h2>
        <p><?php echo htmlspecialchars($group['description']); ?></p>
    </div>

    <div class="admin-actions">
        <?php if ($membership['role_name'] === 'administrator'): ?>
            
            <a href="applications.php?group_id=<?php echo $group['id']; ?>">View Applications</a>

            <form method="POST" action="create-invitation.php">
                <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                <button type="submit">Create Invitation Link</button>
            </form>

        <?php endif; ?>
    </div>

    <div class="group-members">
        <h3>Members</h3>
        <ul>
            <?php foreach ($members as $member): ?>
                <li><?php echo htmlspecialchars($member['username']) . " - " . htmlspecialchars($member['group_role']); ?>
                    <?php if( $membership['role_name'] === 'administrator' && $member['group_role'] !== 'administrator'): ?>
                        <form
                            class="delete-form"
                            data-delete-message="Are you sure you want to remove this member from the group?"
                            method="POST"
                            action="remove-member.php">
                            <input type="hidden" name="group_id" value="<?php echo $group['id']; ?>">
                            <input type="hidden" name="member_id" value="<?php echo $member['user_id']; ?>">
                            <button type="submit">Remove</button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="group-discussions">
        <h3>Discussions</h3>
            <?php if (empty($discussions)): ?>
                <p>No discussions available.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($discussions as $discussion): ?>
                    <li>
                        <a href="discussion.php?id=<?php echo $discussion['id']; ?>">
                            <?php echo htmlspecialchars($discussion['subject']); ?>
                        </a> by <?php echo htmlspecialchars($discussion['creator']); ?> on <?php echo htmlspecialchars($discussion['created_at']); ?>
                        
                        <?php if ($discussion['user_id'] == getUserId()): ?>
                            <form
                            class="delete-form"
                            data-delete-message="Are you sure you want to delete this discussion? All posts within this discussion will also be deleted."
                            method="POST"
                            action="delete-discussion.php">
                                <input type="hidden" name="discussion_id" value="<?php echo $discussion['id']; ?>">
                                <button type="submit">Delete</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="create-discussion-form">
        <form method="POST" action="create-discussion.php">
            <input type="text" name="subject" placeholder="Discussion subject" required>
            <input type="hidden" name="group_id" value="<?php echo $groupId; ?>">
            <button type="submit">Start a Discussion</button>
        </form>
    </div>

    <?php require_once 'includes/delete-confirm.php'; ?>

</body>
</html>