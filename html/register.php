<?php
    require_once 'includes/database.php';

       if($_SERVER['REQUEST_METHOD'] === "POST"){
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        if(empty($firstname) || empty($lastname) || empty($username) || empty($email) || empty($password)){
            // empty checks if any of the fields following it are empty.
        echo "Please fill in all fields.";
        exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // filter_var checks if something is vaild and in this case with FILTER_VALIDATE_EMAIL it checks if the email is valid.
            echo("$email is not a valid email address");
            exit();
            }
        
        if (strlen($password) < 8) {
            echo "Password must be at least 8 characters long.";
            exit();
        }

        // Hashing $password which is yhe users password and putting it in a new variable.
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
       }




?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>