<?php
    
    require_once 'includes/database.php';
    require_once 'includes/auth.php';

    if($_SERVER['REQUEST_METHOD'] === "POST"){
        $username = trim($_POST['username']);
        $password = $_POST['password'];

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
            header("Location: index.php");
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

            <p><a href="register.php">Don't have an account? Register here.</a></p>

        </div>

    </form>
    
</body>
</html>