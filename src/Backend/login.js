console.log("Login.js loaded");

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("loginForm");
    if (!form) return;

    form.addEventListener("submit", async function (e) {
        e.preventDefault();

        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value;

        document.getElementById("emailError").textContent = "";
        document.getElementById("passwordError").textContent = "";
        document.getElementById("formError").textContent = "";

        if (!email) {
            document.getElementById("emailError").textContent = "Email required";
            return;
        }
        if (!password) {
            document.getElementById("passwordError").textContent = "Password required";
            return;
        }

        try {
            const response = await fetch("api.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    type: "Login",
                    email: email,
                    password: password
                })
            });

            // 🔍 Show what the backend actually returned
            const rawText = await response.text();
            console.log("Raw response:", rawText);

            let result;
            try {
                result = JSON.parse(rawText);
            } catch (parseErr) {
                document.getElementById("formError").textContent =
                    "Invalid JSON from server: " + rawText;
                return;
            }

            if (result.status === "success") {
                window.location.href = "dashboard.php";
            } else {
                console.log(result);
                document.getElementById("formError").textContent =
                    result.data || "Login failed";
            }
        } catch (err) {
            console.error("Fetch error:", err);
            document.getElementById("formError").textContent =
                "Could not connect to server: " + err.message;
        }
    });
});
