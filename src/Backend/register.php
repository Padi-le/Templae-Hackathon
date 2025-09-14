<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="css/register.css">
    <script src="register.js"></script>
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

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="">-- Select Gender --</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
            </select><br><br>
            <button type="submit" id="submitBtn">Sign Up</button>
        </form>
    </div>
</body>
</html>