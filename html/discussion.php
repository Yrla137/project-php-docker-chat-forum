<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';
    require_once 'includes/group-membership.php';

    requireLogin();

    if (!isset($_GET['id'])) {
        header("Location: groups.php");
        exit();
    }

    $discussionId = $_GET['id'];

    try {
        // Get the discussion and the username of its creator.
        $sql = "SELECT discussions.id, discussions.subject, discussions.group_id, discussions.user_id,
                    discussions.created_at, users.username AS creator
                FROM discussions
                JOIN users ON discussions.user_id = users.id
                WHERE discussions.id = :discussion_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':discussion_id' => $discussionId]);
        $discussion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$discussion) {
            echo "Discussion not found.";
            exit();
        }

        $userId = getUserId();

        // Check that the logged-in user belongs to the discussion's group.
        $membership = getGroupMembership($pdo, $discussion['group_id'], $userId);

        if (!$membership) {
            echo "You are not a member of this group.";
            exit();
        }

        // Get all posts in the discussion and the username of each author.
        $sql = "SELECT posts.id, posts.discussion_id, posts.user_id, posts.message,
                    posts.created_at, users.username AS author
                FROM posts
                JOIN users ON posts.user_id = users.id
                WHERE posts.discussion_id = :discussion_id
                ORDER BY posts.created_at ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':discussion_id' => $discussionId]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Could not load discussion: " . $e->getMessage());
        die("Could not load the discussion. Please try again.");
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discussion</title>
    <link rel="stylesheet" href="styles/delete-confirm.css">
</head>
<body>

<?php require_once 'includes/navbar.php'; ?>

<div class="discussion-container">

    <div class="discussion-header">
        <h2 class="discussion-title"><?php echo htmlspecialchars($discussion['subject']); ?></h2>
    </div>

    <div class="group-posts">
        <h3>Posts</h3>

        <?php if (empty($posts)): ?>
            <p class="status-message">No posts available.</p>
        <?php else: ?>
            <ul class="post-list">
                <?php foreach ($posts as $post): ?>
                    <li class="post-item">
                        <p class="post-message"><?php echo htmlspecialchars($post['message']); ?></p>

                        <p class="post-meta">
                            by <?php echo htmlspecialchars($post['author']); ?>
                            on <?php echo htmlspecialchars($post['created_at']); ?>
                        </p>

                        <?php if ($post['user_id'] == getUserId()): ?>
                            <form class="delete-form" data-delete-message="Are you sure you want to delete this post?" method="POST" action="delete-post.php">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button class="danger-button" type="submit">Delete</button>
                            </form>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <div class="create-post-form">
        <form method="POST" action="create-post.php">
            <textarea class="form-textarea" name="message" placeholder="Message" required></textarea>
            <input type="hidden" name="discussion_id" value="<?php echo $discussionId; ?>">
            <button class="form-button" type="submit">Send message</button>
        </form>
    </div>

    <div class="back-to-group">
        <button class="form-button" onclick="window.location.href='group.php?id=<?php echo $discussion['group_id']; ?>'">Back to Group</button>
    </div>

</div>

<?php require_once 'includes/delete-confirm.php'; ?>

</body>
</html>