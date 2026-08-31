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

    try{

        $sql = "SELECT discussions.id, discussions.subject, discussions.group_id, discussions.user_id, discussions.created_at, users.username AS creator FROM discussions 
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
        $membership = getGroupMembership($pdo, $discussion['group_id'], $userId);

        if (!$membership) {
            echo "You are not a member of this group.";
            exit();
        }

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
    <title>Discussion</title>
</head>
<body>
    
</body>
</html>