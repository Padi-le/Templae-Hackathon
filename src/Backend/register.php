<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/register.css">
    <script src="js/register.js"></script>
</head>
<body>

    <?php include 'header.php'; ?>

    <div class="form-container">
        <h1>Register</h1>

        <form id="registerForm">

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required><br>

            <label for="surname">Surname:</label>
            <input type="text" id="surname" name="surname" required><br>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>

            <label for="phone_number">Phone number:</label>
            <input type="text" id="phone_number" name="phone_number" required><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

            <label for="type">User Type:</label>
            <select id="type" name="type" required>
            <option value="User">User</option>
            </select><br>

            <button type="submit" id="submitBtn">Sign Up</button>
        </form>

        <div id="error-message" style="display: none;"></div>
    </div>


</body>

</html>