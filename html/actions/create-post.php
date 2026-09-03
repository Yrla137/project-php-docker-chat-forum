<?php

require_once '../includes/database.php';
require_once '../includes/auth.php';
require_once '../includes/group-membership.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $message = trim($_POST['message'] ?? '');
    $discussion_id = (int) ($_POST['discussion_id'] ?? 0);
    $user_id = getUserId();

    if (empty($message)) {
        echo "Please provide a message for the post.";
        exit();
    }

    if ($discussion_id <= 0) {
        echo "Invalid discussion.";
        exit();
    }

    try {
        // Get the group that the discussion belongs to.
        $sql = "SELECT group_id FROM discussions WHERE id = :discussion_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':discussion_id' => $discussion_id]);
        $discussion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$discussion) {
            echo "Discussion not found.";
            exit();
        }

        // Check that the logged-in user is a member of the group.
        $membership = getGroupMembership($pdo, $discussion['group_id'], $user_id);

        if (!$membership) {
            echo "You are not a member of this group.";
            exit();
        }

        // Create the new post.
        $sql = "INSERT INTO posts (message, discussion_id, user_id)
                VALUES (:message, :discussion_id, :user_id)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':message' => $message,
            ':discussion_id' => $discussion_id,
            ':user_id' => $user_id
        ]);

        header("Location: ../discussion.php?id=" . $discussion_id);
        exit();

    } catch (PDOException $e) {
        error_log("Creating post failed: " . $e->getMessage());
        echo "Creating post failed. Please try again.";
        exit();
    }
}
?>