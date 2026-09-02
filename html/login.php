<?php
    
    require_once 'includes/database.php';
    require_once 'includes/auth.php';

    // Get invitation redirect information from the URL
    $redirect = $_GET['redirect'] ?? null;
    $token = $_GET['token'] ?? null;

    if($_SERVER['REQUEST_METHOD'] === "POST"){
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        // Get invitation redirect information from the POST data
        $redirect = $_POST['redirect'] ?? null;
        $token = $_POST['token'] ?? null;

        if(empty($username) || empty($password)){
            echo "Please fill in all fields.";
            exit();
        }

        try{
            $sql = "SELECT id, username, password_hash FROM users WHERE username = :username";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':username' => $username]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if(!$user){
                echo "Invalid username or password.";
                exit();
            }

            $passwordMatch = password_verify($password, $user['password_hash']);
            // password_verify checks if the provided password matches the hashed password stored in the database.
            if(!$passwordMatch){
                echo "Invalid username or password.";
                exit();
            }

            session_regenerate_id(true);
            // session_regenerate_id(true) generates a new session ID for the user, which helps prevent session fixation attacks.
            // The true parameter ensures that the old session is deleted.

            $_SESSION['user_id'] = $user['id'];
            // Store the user's ID in the session to keep them logged in.

            if ($redirect && $token) {
                // If the user came from an invitation, return to the invitation after login.
                header("Location: accept-invitation.php?token=" . urlencode($token));
            } else {
                // Else continue with the normal login flow.
                header("Location: index.php");
            }
            exit();
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            echo "Something went wrong, please try again.";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>

    <h1>Login</h1>

    <form method="POST" action="login.php">

        <div class="login-form-container">

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Login</button>

            <?php if ($redirect && $token): ?>
                <a href="register.php?redirect=accept-invitation.php&token=<?php echo urlencode($token); ?>">
                    Register here
                </a>
            <?php else: ?>
                <a href="register.php">Register here</a>
            <?php endif; ?>

            <?php if ($redirect && $token): ?>
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <?php endif; ?>

        </div>

    </form>
    
</body>
</html>