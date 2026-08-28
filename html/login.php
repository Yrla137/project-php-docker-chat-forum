<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <h1>Login</h1>

    <form method="POST" action="login.php">
        <div class="login-form-container">

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <p><a href="register.php">Don't have an account? Register here.</a></p>

            <button type="submit">Login</button>

        </div>
    
</body>
</html>