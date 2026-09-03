<?php

require_once '../includes/database.php';
require_once '../includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int) ($_POST['post_id'] ?? 0);
    $userId = getUserId();

    if ($postId <= 0) {
        echo "Post ID is required.";
        exit();
    }

    try {
        // Find the post and its owner.
        $sql = "SELECT id, user_id, discussion_id
                FROM posts
                WHERE id = :post_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':post_id' => $postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            echo "Post not found.";
            exit();
        }

        // Users may only delete their own posts.
        if ((int) $post['user_id'] !== (int) $userId) {
            echo "You are not authorized to delete this post.";
            exit();
        }

        $sql = "DELETE FROM posts WHERE id = :post_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':post_id' => $postId]);

        if ($stmt->rowCount() > 0) {
            header("Location: ../discussion.php?id=" . (int) $post['discussion_id']);
            exit();
        }

        echo "Failed to delete the post.";
        exit();

    } catch (PDOException $e) {
        error_log("Deleting post failed: " . $e->getMessage());
        echo "Could not delete the post. Please try again.";
        exit();
    }
}