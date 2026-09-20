<?php

require_once 'includes/database.php';

$error = null;

// Get invitation information from the URL, if the user came from an invitation.
$redirect = $_GET['redirect'] ?? null;
$token = $_GET['token'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    // Check that all required parameters exist in the request.
    if (
        isset($_POST['firstname']) &&
        isset($_POST['lastname']) &&
        isset($_POST['username']) &&
        isset($_POST['email']) &&
        isset($_POST['password'])
    ) {
        $firstname = trim($_POST['firstname']);
        $lastname = trim($_POST['lastname']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Keep the invitation information when the registration form is submitted.
        $redirect = $_POST['redirect'] ?? null;
        $token = $_POST['token'] ?? null;

        // Check that all required fields have been filled in.
        if (
            empty($firstname) ||
            empty($lastname) ||
            empty($username) ||
            empty($email) ||
            empty($password)
        ) {
            $error = "Please fill in all fields.";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";

        } elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long.";

        } else {
            // Hash the password before storing it in the database.
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            try {
                $sql = "INSERT INTO users 
                        (first_name, last_name, username, email, password_hash)
                        VALUES 
                        (:firstname, :lastname, :username, :email, :password)";

                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    ':firstname' => $firstname,
                    ':lastname' => $lastname,
                    ':username' => $username,
                    ':email' => $email,
                    ':password' => $password_hash
                ]);

                if ($redirect && $token) {
                    // Continue to login while keeping the invitation token.
                    header(
                        "Location: login.php?redirect=actions/accept-invitation.php&token=" . urlencode($token)
                    );
                } else {
                    header("Location: login.php");
                }

                exit();

            } catch (PDOException $e) {

                if (isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
                    // MySQL error 1062 means that a UNIQUE value already exists.
                    $error = "Username or email already exists.";
                } else {
                    error_log("Database error: " . $e->getMessage());
                    $error = "Something went wrong, please try again.";
                }
            }
        }

    } else {
        $error = "Required fields are missing.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles/global.css">
    <link rel="stylesheet" href="styles/register.css">
</head>

<body>

    <main class="auth-page register-page">
        <section class="auth-container">

            <div class="auth-intro">
                <p class="auth-eyebrow">Enter the community</p>
                <h1 class="auth-title">Register</h1>
                <p class="auth-description">
                    Create an account and find your place in the conversation.
                </p>
            </div>

            <div class="auth-card">
                <form class="register-form" method="POST" action="register.php">

                    <?php if ($error): ?>
                        <p class="error-message">
                            <?php echo htmlspecialchars($error); ?>
                        </p>
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstname">First Name</label>
                            <input class="form-input" type="text" id="firstname" name="firstname" required>
                        </div>

                        <div class="form-group">
                            <label for="lastname">Last Name</label>
                            <input class="form-input" type="text" id="lastname" name="lastname" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input class="form-input" type="text" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input class="form-input" type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input class="form-input" type="password" id="password" name="password" required minlength="8">
                        <p class="form-hint">Password must be at least 8 characters long.</p>
                    </div>

                    <?php if ($redirect && $token): ?>
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <?php endif; ?>

                    <button class="form-button" type="submit">Register</button>

                    <p class="auth-link">
                        Already have an account?
                        <a href="login.php">Login here</a>
                    </p>

                </form>
            </div>

            <a class="home-link" href="index.php">← Back to Home</a>

        </section>
    </main>

</body>
</html>