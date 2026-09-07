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
    <link rel="stylesheet" href="styles/global.css">
    <link rel="stylesheet" href="styles/invitation-link.css">
</head>

<body>

    <?php require_once 'includes/navbar.php'; ?>

    <main class="invitation-page">

        <section class="invitation-container">

            <div class="invitation-header">
                <p class="invitation-eyebrow">Private Invitation</p>

                <h1 class="invitation-title">Invitation Link</h1>

                <p class="invitation-description">
                    Your invitation has been created. Copy the link below and send it to the person you want to invite.
                </p>
            </div>

            <div class="invitation-card">
                <p class="invitation-label">Your invitation is ready</p>

                <div class="invitation-link-container">
                    <input
                        class="invitation-link-input"
                        id="invitation-link"
                        type="text"
                        value="<?php echo htmlspecialchars($invitationLink); ?>"
                        readonly>

                    <button class="copy-button" id="copy-invitation" type="button">
                        Copy Link
                    </button>
                </div>

                <p class="copy-message" id="copy-message"></p>

                <p class="invitation-warning">
                    The link can only be used once and expires after 24 hours.
                </p>
            </div>

            <a class="back-link" href="group.php?id=<?php echo (int) $invitation['group_id']; ?>">
                ← Back to Group
            </a>

        </section>

    </main>

    <script>
        const copyButton = document.getElementById('copy-invitation');
        const invitationLink = document.getElementById('invitation-link');
        const copyMessage = document.getElementById('copy-message');

        copyButton.addEventListener('click', async function() {
            await navigator.clipboard.writeText(invitationLink.value);
            copyMessage.textContent = 'Invitation link copied!';
        });
    </script>

</body>
</html>