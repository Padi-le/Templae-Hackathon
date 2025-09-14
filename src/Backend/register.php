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

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

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
    <script>
        document.getElementById("registerForm").addEventListener("submit", async function(e) {
            e.preventDefault();

            const data = {
                name: document.getElementById("name").value,
                surname: document.getElementById("surname").value,
                email: document.getElementById("email").value,
                password: document.getElementById("password").value,
                gender: document.getElementById("gender").value
            };

            try {
                const response = await fetch("api.php?endpoint=register", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data)
                });

                const result = await response.json();
                document.getElementById("response").innerText = JSON.stringify(result);

                if (result.status === "success" && result.apikey) {
                    localStorage.setItem("api_key", result.apikey);
                }
            } catch (err) {
                document.getElementById("response").innerText = "Error: " + err;
            }
        });
    </script>

</body>

</html>