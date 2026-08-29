<?php
    require_once 'includes/database.php';
    require_once 'includes/auth.php';

    if(isLoggedIn()){

        $user_id = getUserId();

        $sql = "SELECT username FROM users WHERE id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$user){
            echo "User not found.";
            exit();
        }

        $username = $user['username'];

    }
?>

<!DOCTYPE html>
<html lang="en">
    
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>

</head>
<body>

    <?php require_once 'includes/navbar.php'; ?>

    <?php if(isLoggedIn()): ?>
            <h1>Welcome back <?php echo htmlspecialchars($username);?>!</h1>
            <p>Ready to talk about something interesting?</p>
        <?php else: ?>
            <h1>Welcome to our website!</h1>
            <p>Do you want to talk about something interesting?</p>
        <?php endif; ?>
    
</body>
</html>