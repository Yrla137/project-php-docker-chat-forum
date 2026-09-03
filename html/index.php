<?php

require_once 'includes/database.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    $user_id = getUserId();

    try {
        // Fetch the logged-in user's username.
        $sql = "SELECT username
                FROM users
                WHERE id = :user_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo "User not found.";
            exit();
        }

        $username = $user['username'];

    } catch (PDOException $e) {
        error_log("Loading user on home page failed: " . $e->getMessage());
        echo "Could not load the page. Please try again.";
        exit();
    }
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

    <main class="home-container">
        <section class="home-hero">

            <?php if (isLoggedIn()): ?>
                <div class="home-content logged-in-home">
                    <h1 class="home-title">
                        Welcome <?php echo htmlspecialchars($username); ?>!
                    </h1>

                    <p class="home-text">
                        Ready to talk about something interesting?
                    </p>

                    <a class="home-button" href="groups.php">Explore Groups</a>
                </div>
            <?php else: ?>
                <div class="home-content logged-out-home">
                    <h1 class="home-title">Welcome to our website!</h1>

                    <p class="home-text">
                        Do you want to talk about something interesting?
                    </p>

                    <div class="home-actions">
                        <a class="home-button" href="login.php">Login</a>
                        <a class="home-button secondary-button" href="register.php">Register</a>
                    </div>
                </div>
            <?php endif; ?>

        </section>
    </main>

</body>
</html>