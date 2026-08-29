<?php
    require_once 'includes/database.php';

       if($_SERVER['REQUEST_METHOD'] === "POST"){
        $firstname = trim($_POST['firstname']);
        $lastname = trim($_POST['lastname']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if(empty($firstname) || empty($lastname) || empty($username) || empty($email) || empty($password)){
            // empty checks if any of the fields following it are empty.
        echo "Please fill in all fields.";
        exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // filter_var checks if something is vaild and in this case with FILTER_VALIDATE_EMAIL it checks if the email is valid.
            echo("Please enter a valid email address.");
            exit();
            }

        if (strlen($password) < 8) {
            echo "Password must be at least 8 characters long.";
            exit();
        }

        // Hashing $password which is yhe users password and putting it in a new variable.
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        
       try {
            $sql = "INSERT INTO users (first_name, last_name, username, email, password_hash) VALUES (:firstname, :lastname, :username, :email, :password)";
            $stmt = $pdo->prepare($sql);
            // The prepare method is used to prepare the SQL statement for execution. It helps prevent SQL injection by allowing you to bind parameters to the statement.
            $stmt->execute([
                // The execute method is used to run the prepared statement with the provided values.
                ':firstname' => $firstname,
                ':lastname' => $lastname,
                ':username' => $username,
                ':email' => $email,
                ':password' => $password_hash
            ]);
            header('Location: login.php');
            exit();
        } catch (PDOException $e) {
            if ($e->errorInfo[1] === 1062) {
                // 1062 indicates a duplicate entry error.
                echo "Username or email already exists.";
            } else {
                error_log("Database error: " . $e->getMessage());
                echo "Something went wrong, please try again.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>

    <h1>Register new user</h1>

    <form method="POST" action="register.php">
        <div class="register-form-container">

            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" required>

            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" required>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <p>Password must be at least 8 characters long.</p>

            <button type="submit">Register</button>

        </div>
    </form>
    
</body>
</html>