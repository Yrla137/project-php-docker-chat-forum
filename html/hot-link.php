<?php
    require(".../settings.php");
    require(".../functions.php");

    $is_ok = false;

    if(isset($_GET['token'])) {
        $token = $_GET['token'] ?? null;
        var_dump($token);

        if (empty($token)) {
            echo "Token is required.";
            exit();
        }

        if (!is_string($token)) {
            echo "Invalid token format.";
            exit();
        }
        $result = $pdo->query("SELECT * FROM invetations WHERE token = '$token'");

        $row = $pdo->fetch_assoc();

        // $rows = $db->fetch_all();

        // if(!empty($rows)) {
        //     var_dump($rows);
        //     $row = $rows[0];

            if($row['used'] == 1 ) {
                echo "This invitation has already been used.";
                exit();
            }
            $is_ok = true;

            if($is_ok) {
                echo "This invitation is valid.";
            } else {
                header("Location: register.php");
                echo "This invitation is not valid.";
                exit();
            }
        }
