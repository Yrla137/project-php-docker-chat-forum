<?php

// Use __DIR__ so auth.php is included relative to this file.
require_once __DIR__ . '/auth.php';

?>

<div class="navbar-container">

    <nav class="navbar">
        <ul class="navbar-list">

            <li>
                <a class="navbar-link" href="index.php">Home</a>
            </li>

            <?php if (isLoggedIn()): ?>

                <li>
                    <a class="navbar-link" href="groups.php">Groups</a>
                </li>

                <li>
                    <form class="logout-form" method="POST" action="actions/logout.php">
                        <button class="navbar-logout-button" type="submit">Logout</button>
                    </form>
                </li>

            <?php else: ?>

                <li>
                    <a class="navbar-link" href="login.php">Login</a>
                </li>

                <li>
                    <a class="navbar-link" href="register.php">Register</a>
                </li>

            <?php endif; ?>

        </ul>
    </nav>

</div>