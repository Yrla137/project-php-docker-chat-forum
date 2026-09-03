<?php

    require_once '../includes/database.php';
    require_once '../includes/auth.php';
    require_once '../includes/group-membership.php';

    requireLogin();

    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $group_id = (int) ($_POST['group_id'] ?? 0);
        $user_id = getUserId();

        if (empty($subject)) {
            echo "Please provide a subject for the discussion.";
            exit();
        }

        if (empty($message)) {
            echo "Please write the first post.";
            exit();
        }

        if ($group_id <= 0) {
            echo "Invalid group.";
            exit();
        }

        // Check that the logged-in user is a member of the group
        $membership = getGroupMembership($pdo, $group_id, $user_id);

        if (!$membership) {
            echo "You are not a member of this group.";
            exit();
        }

        try {
            $pdo->beginTransaction();

            // Create the discussion
            $sql = "INSERT INTO discussions (subject, group_id, user_id)
                    VALUES (:subject, :group_id, :user_id)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':subject' => $subject,
                ':group_id' => $group_id,
                ':user_id' => $user_id
            ]);

            // Get the ID of the discussion that was just created
            $discussion_id = $pdo->lastInsertId();

            // Create the first post in the new discussion
            $sql = "INSERT INTO posts (discussion_id, user_id, message)
                    VALUES (:discussion_id, :user_id, :message)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':discussion_id' => $discussion_id,
                ':user_id' => $user_id,
                ':message' => $message
            ]);

            $pdo->commit();

            header("Location: ../discussion.php?id=" . $discussion_id);
            exit();

        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log("Creating discussion failed: " . $e->getMessage());
            echo "Creating discussion failed. Please try again.";
            exit();
        }
    }
