<?php

require_once 'includes/database.php';
require_once 'includes/auth.php';

requireLogin();

$token = $_GET['token'] ?? '';

if (empty($token)) {
    echo "Invitation token is required.";
    exit();
}

try {
    // Fetch the invitation.
    $sql = "SELECT token, group_id, created_by, created_at, used
            FROM invitations
            WHERE token = :token";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':token' => $token]);
    $invitation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$invitation) {
        echo "Invalid invitation token.";
        exit();
    }

    // Check if the invitation has already been used.
    if ((int) $invitation['used'] === 1) {
        echo "This invitation link has already been used.";
        exit();
    }

    // Check if the invitation is older than 24 hours.
    $createdAt = new DateTime($invitation['created_at']);
    $expirationTime = clone $createdAt;
    $expirationTime->modify('+24 hours');

    $now = new DateTime();

    if ($now > $expirationTime) {
        echo "This invitation link has expired.";
        exit();
    }

    // Build the link used to accept the invitation.
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $protocol = 'https';
    } else {
        $protocol = 'http';
    }

    $host = $_SERVER['HTTP_HOST'];
    $invitationLink = $protocol . "://" . $host . "/actions/accept-invitation.php?token=" . urlencode($token);

} catch (PDOException $e) {
    error_log("Loading invitation failed: " . $e->getMessage());
    echo "Could not load the invitation. Please try again.";
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

    <main class="invitation-container">
        <h2>Invitation Link</h2>

        <a class="invitation-link" href="<?php echo htmlspecialchars($invitationLink); ?>">
            Click here to accept the invitation
        </a>
    </main>

</body>
</html>