<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';

    requireLogin();

    if($_SERVER['REQUEST_METHOD'] === 'POST') {

        $discussionId = $_POST['discussion_id'] ?? null;
        $userId = getUserId();

        if (!$discussionId) {
            echo "Discussion ID is required.";
            exit();
        }
        
        try{
            // Start a transaction
            $pdo->beginTransaction();

            // Check if the discussion exists and if the user is the creator of the discussion
            $sql = "SELECT id, user_id, group_id FROM discussions WHERE id = :discussion_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':discussion_id' => $discussionId]);
            $discussion = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$discussion) {
                throw new Exception("Discussion not found.");
            }

            if ($discussion['user_id'] != $userId) {
                throw new Exception("You are not authorized to delete this discussion.");
            }

            // Delete child posts before deleting the discussion
            $sql = "DELETE FROM posts WHERE discussion_id = :discussion_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':discussion_id' => $discussionId]);

            $sql = "DELETE FROM discussions WHERE id = :discussion_id AND user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':discussion_id' => $discussionId, ':user_id' => $userId]);

            if ($stmt->rowCount() > 0) {
                // Commit the transaction
                $pdo->commit();
                header("Location: group.php?id=" . $discussion['group_id']);
                exit();
            } else {
                throw new Exception("You are not authorized to delete this discussion or it does not exist.");
            }

        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Database error: " . $e->getMessage());
            echo "Something went wrong, please try again.";
        }

    }