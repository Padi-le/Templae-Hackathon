
document.addEventListener("DOMContentLoaded", function(){
    document.getElementById("registerForm").addEventListener("submit", async function(e) {
        e.preventDefault();
        console.log({
    type:"Register",
    name: document.getElementById("name").value.trim(),
    surname: document.getElementById("surname").value.trim(),
    email: document.getElementById("email").value.trim(),
    password: document.getElementById("password").value.trim(),
    gender: document.getElementById("gender").value.trim()
});

        try {
            const response = await fetch("api.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    type: "Register",
                    name: document.getElementById("name").value.trim(),
                    surname: document.getElementById("surname").value.trim(),
                    email: document.getElementById("email").value.trim(),
                    password: document.getElementById("password").value.trim(),
                    gender: document.getElementById("gender").value.trim()
                })
            });
            const result = await response.json();
            if(result.status === "success" && result.data.apikey){
                localStorage.setItem("api_key", result.data.apikey);
                window.location.href = "login.php";
            }
        }
        catch (err) {
            document.getElementById("response").innerText = "Error: " + err;
        }
    });
});