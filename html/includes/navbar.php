<?php
    //DIR means the current directory of the file, so this ensures that the auth.php file is included correctly regardless of where this file is included from.
    require_once __DIR__ . '/auth.php';
?>

    <div class="navbar-container">

        <nav class="navbar">
            <ul class="navbar-list">
                <li><a href="index.php">Home</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="groups.php">Groups</a></li>
                    <li>
                        <form class="logout-form" method="POST" action="logout.php">
                        <!-- When purring the logout file in action, it will send a POST request to the logout.php file which will log the user out. -->
                            <button type="submit">Logout</button>
                        </form>
                    </li>

                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>

    </div>