<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Users Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        body { background: #f5f7fa; font-family: 'Segoe UI', Arial, sans-serif; }
        .online-dashboard { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); padding: 32px; text-align: center; }
        .online-count { font-size: 3.5rem; font-weight: bold; color: #43a047; margin-bottom: 8px; }
        .online-label { font-size: 1.2rem; color: #555; margin-bottom: 24px; }
        .gender-stats { display: flex; justify-content: center; gap: 40px; margin-top: 32px; }
        .gender-card { background: #e3f2fd; border-radius: 12px; padding: 24px 32px; min-width: 120px; box-shadow: 0 2px 8px rgba(33,150,243,0.07); }
        .gender-icon { font-size: 2.5rem; }
        .gender-label { font-size: 1.1rem; color: #1976d2; margin-top: 8px; }
        .gender-count { font-size: 2rem; font-weight: 600; color: #1976d2; }
        .pulse { animation: pulse 1.5s infinite; }
        @keyframes pulse {
            0% { text-shadow: 0 0 0 #43a047; }
            50% { text-shadow: 0 0 16px #43a047; }
            100% { text-shadow: 0 0 0 #43a047; }
        }
    </style>
    <script src="dashboard.js"></script>
</head>
<?php include "header.php";?>
<body>
    <div class="online-dashboard">
        <div id="user-info" style="margin-bottom:32px;">
            <div style="font-size:1.2rem;margin-bottom:8px;color:#1976d2;">
                Welcome, <b><?php echo htmlspecialchars($user['name'] . ' ' . $user['surname']); ?></b>
            </div>
            <div style="font-size:1rem;color:#555;">Email: <?php echo htmlspecialchars($user['email']); ?></div>
            <div style="font-size:1rem;color:#555;">Streak: <?php echo (int)$user['streaks']; ?> days</div>
            <div style="font-size:1rem;color:#555;">Points: <?php echo (int)$user['points']; ?></div>
        </div>

        <div id="online-count" class="online-count pulse">
            <?php echo $totalOnline; ?>
        </div>
        <div class="online-label">Users Online Now</div>

        <div class="gender-stats">
            <div class="gender-card">
                <div class="gender-icon">&#9794;</div>
                <div class="gender-label">Male</div>
                <div id="male-count" class="gender-count"><?php echo $male; ?></div>
            </div>
            <div class="gender-card">
                <div class="gender-icon">&#9792;</div>
                <div class="gender-label">Female</div>
                <div id="female-count" class="gender-count"><?php echo $female; ?></div>
            </div>
        </div>
    </div>
</body>
</html>