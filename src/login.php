<?php


?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link rel="stylesheet" href="css/login.css">
    </head>
    <body>
        <?php include "header.php";?>
        <div class="signup-container">
            <h2>Log into you account.</h2>
            <form id="loginForm" method="POST">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="text" id="email" name="email" required>
                    <span class="error" id="emailError"></span>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                    <span class="error" id="passwordError"></span>
                </div>
                <button type="submit" class="signup-btn">Login</button>
                <div id="formError" class="error"></div>
            </form>
        </div>
    </body>
</html>
