<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';

    $error = null;

    // Get invitation redirect information from the URL.
    $redirect = $_GET['redirect'] ?? null;
    $token = $_GET['token'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === "POST") {

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Keep invitation information when the login form is submitted.
        $redirect = $_POST['redirect'] ?? null;
        $token = $_POST['token'] ?? null;

        if (empty($username) || empty($password)) {
            $error = "Please fill in all fields.";
        } else {

            try {
                $sql = "SELECT id, username, password_hash
                        FROM users
                        WHERE username = :username";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':username' => $username
                ]);

                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $error = "Invalid username or password.";
                } else {

                    // Check if the entered password matches the stored password hash.
                    $passwordMatch = password_verify(
                        $password,
                        $user['password_hash']
                    );

                    if (!$passwordMatch) {
                        $error = "Invalid username or password.";
                    } else {

                        // Regenerate the session ID after login to prevent session fixation.
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];

                        if ($redirect && $token) {
                            // Continue the invitation flow after login.
                            header(
                                "Location: accept-invitation.php?token=" .
                                urlencode($token)
                            );
                        } else {
                            header("Location: index.php");
                        }

                        exit();
                    }
                }

            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
                $error = "Something went wrong, please try again.";
            }
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

        <form class="login-form" method="POST" action="login.php">

            <div class="login-form-container">

                <?php if ($error): ?>
                    <p class="error">
                        <?php echo htmlspecialchars($error); ?>
                    </p>
                <?php endif; ?>

                <label for="username">Username:</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                >

                <label for="password">Password:</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                >

                <?php if ($redirect && $token): ?>
                    <input
                        type="hidden"
                        name="redirect"
                        value="<?php echo htmlspecialchars($redirect); ?>"
                    >

                    <input
                        type="hidden"
                        name="token"
                        value="<?php echo htmlspecialchars($token); ?>"
                    >
                <?php endif; ?>

                <button class="form-button" type="submit">
                    Login
                </button>

                <?php if ($redirect && $token): ?>
                    <p class="register-link">
                        You don't have an account?
                        <a href="register.php?redirect=accept-invitation.php&token=<?php echo urlencode($token); ?>">
                            Register here
                        </a>
                    </p>
                <?php else: ?>
                    <p class="register-link">
                        Don't have an account?
                        <a href="register.php">Register here</a>
                    </p>
                <?php endif; ?>

            </div>

        </form>

    </body>

</html>
