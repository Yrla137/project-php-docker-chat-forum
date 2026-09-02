<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';
    require_once 'includes/group-membership.php';

    requireLogin();

    if($_SERVER['REQUEST_METHOD'] === "POST"){
        $subject = trim($_POST['subject']);
        $group_id = $_POST['group_id'];
        $user_id = getUserId();
        
        // Check that the logged-in user is a member of the group
        $membership = getGroupMembership($pdo, $group_id, $user_id);

        if (!$membership) {
            echo "You are not a member of this group.";
            exit();
        }

        if(empty($subject)){
            echo "Please provide a subject for the discussion.";
            exit();
        }

        try{

            // Insert the new discussion into the discussions table
            $sql = "INSERT INTO discussions (subject, group_id, user_id) VALUES (:subject, :group_id, :user_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':subject' => $subject,
                ':group_id' => $group_id,
                ':user_id' => $user_id
            ]);
            header("Location: group.php?id=" . $group_id);
            exit();

        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            exit();
        }
    }