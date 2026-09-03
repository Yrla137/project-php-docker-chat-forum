<?php

require_once '../includes/database.php';
require_once '../includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $discussionId = (int) ($_POST['discussion_id'] ?? 0);
    $userId = getUserId();

    if ($discussionId <= 0) {
        echo "Discussion ID is required.";
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Find the discussion and its creator.
        $sql = "SELECT id, user_id, group_id
                FROM discussions
                WHERE id = :discussion_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':discussion_id' => $discussionId]);
        $discussion = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$discussion) {
            $pdo->rollBack();
            echo "Discussion not found.";
            exit();
        }

        if ((int) $discussion['user_id'] !== (int) $userId) {
            $pdo->rollBack();
            echo "You are not authorized to delete this discussion.";
            exit();
        }

        // Delete all posts belonging to the discussion first.
        $sql = "DELETE FROM posts WHERE discussion_id = :discussion_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':discussion_id' => $discussionId]);

        // Delete the discussion.
        $sql = "DELETE FROM discussions
                WHERE id = :discussion_id
                  AND user_id = :user_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':discussion_id' => $discussionId,
            ':user_id' => $userId
        ]);

        if ($stmt->rowCount() <= 0) {
            throw new Exception("The discussion could not be deleted.");
        }

        $pdo->commit();

        header("Location: ../group.php?id=" . (int) $discussion['group_id']);
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        error_log("Deleting discussion failed: " . $e->getMessage());
        echo "Something went wrong. Please try again.";
        exit();
    }
}