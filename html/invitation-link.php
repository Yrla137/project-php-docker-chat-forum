<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';

    requireLogin();

    $token = $_GET['token'] ?? null;

    if(!$token){
        echo "Invitation token is required.";
        exit();
    }

    try {
        // Fetch token details from the database
        $sql = "SELECT token, group_id, created_by, created_at, used FROM invitations WHERE token = :token";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':token' => $token]);
        $invitation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$invitation) {
            echo "Invalid invitation token.";
            exit();
        }

        // Check if the invitation has already been used
        if ($invitation['used'] == 1) {
            echo "This invitation link has already been used.";
            exit();
        }

        $createdAt = new DateTime($invitation['created_at']);
        $expirationTime = clone $createdAt;
        // Set expiration time to 24 hours after creation
        $expirationTime->modify('+24 hours');

        $now = new DateTime();

        // Check if the invitation has expired
        if ($now > $expirationTime) {
            echo "This invitation link has expired.";
            exit();
        }

        // Generate the invitation link for the user to accept
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }
        $host = $_SERVER['HTTP_HOST'];
        $invitationLink = $protocol . "://" . $host . "/accept-invitation.php?token=" . urlencode($token);
    
    } catch (PDOException $e) {
        echo "Error loading invitation: " . $e->getMessage();
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation Link</title>
</head>
<body>

    <?php require 'includes/navbar.php'; ?>

    <h2>Invitation Link</h2>

    <a href="<?php echo htmlspecialchars($invitationLink); ?>">
        Click here to accept the invitation
    </a>

</body>
</html>