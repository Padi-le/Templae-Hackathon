<link rel="stylesheet" href="css/header.css">
<header class="header">
    <div class="header-content">
        <a href="index.php" class="logo">
            <i class="heartbeat">❤</i>
            <span>Healthy</span>
        </a>

        <nav class="nav-links">
            <a href="index.php">Home</a>
            <div id="partially-visible" class="partial-nav" style="display:none">
                <a href="dashboard.php">Dashboard</a>
                <a href="chatBot.php">Chat Bot</a>
                <a href="chatRoo.php">Global Chat</a>
            </div>
        </nav>

        <div class="nav-actions" class="nav-actions" style="display:none">
            <a href="login.php" class="login-btn">Login</a>
            <a href="signup.php" class="signup-btn">Sign Up</a>
        </div>
    </div>
    <script>
        if(localStorage.getItem("api_key"))
            document.getElementById("partially-visible").style.display = "block";
        else
            document.getElementById("nav-actions").style.display = "block";
    </script>
</header>