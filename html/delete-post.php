<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';

    requireLogin();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $postId = $_POST['post_id'] ?? null;
        $userId = getUserId();

        if (!$postId) {
            echo "Post ID is required.";
            exit();
        }

        $sql = "SELECT id, user_id, discussion_id FROM posts WHERE id = :post_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':post_id' => $postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            echo "Post not found.";
            exit();
        }

        if ($post['user_id'] != $userId) {
            echo "You are not authorized to delete this post.";
            exit();
        }

        $sql = "DELETE FROM posts WHERE id = :post_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':post_id' => $postId]);

        if ($stmt->rowCount() > 0) {
            header("Location: discussion.php?id=" . $post['discussion_id']);
            exit();
        } else {
            echo "Failed to delete the post.";
            exit();
        }

    }