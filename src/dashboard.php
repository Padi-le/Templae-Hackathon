<?php
// dashboard.php
session_start();
// Example: get user info from session or database
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'User';

// Dynamic calories for each meal (could be loaded from DB in real app)
$meals = [
    'breakfast' => [
        'name' => 'Oatmeal with fruit',
        'calories' => 350,
        'time' => '7:00 - 8:00 AM'
    ],
    'lunch' => [
        'name' => 'Grilled chicken salad',
        'calories' => 600,
        'time' => '12:00 - 1:00 PM'
    ],
    'dinner' => [
        'name' => 'Salmon with veggies',
        'calories' => 700,
        'time' => '6:00 - 7:00 PM'
    ],
    'snacks' => [
        'name' => 'Greek yogurt',
        'calories' => 200,
        'time' => '3:00 - 4:00 PM'
    ]
];
$total_calories = array_sum(array_column($meals, 'calories'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="dashboard-container">
        <nav class="sidebar">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="contact.php">Contact Us</a></li>
            </ul>
        </nav>
        <main class="main-content">
            <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <section class="today-meal-plan">
                <h2>Today's Meal Plan</h2>
                <ul>
                    <li>
                        <strong>Breakfast:</strong> <span id="breakfast"><?php echo htmlspecialchars($meals['breakfast']['name']); ?></span><br>
                        <span class="meal-detail">Calories: <?php echo $meals['breakfast']['calories']; ?> kcal | Optimal time: <?php echo $meals['breakfast']['time']; ?></span>
                    </li>
                    <li>
                        <strong>Lunch:</strong> <span id="lunch"><?php echo htmlspecialchars($meals['lunch']['name']); ?></span><br>
                        <span class="meal-detail">Calories: <?php echo $meals['lunch']['calories']; ?> kcal | Optimal time: <?php echo $meals['lunch']['time']; ?></span>
                    </li>
                    <li>
                        <strong>Dinner:</strong> <span id="dinner"><?php echo htmlspecialchars($meals['dinner']['name']); ?></span><br>
                        <span class="meal-detail">Calories: <?php echo $meals['dinner']['calories']; ?> kcal | Optimal time: <?php echo $meals['dinner']['time']; ?></span>
                    </li>
                    <li>
                        <strong>Snacks:</strong> <span id="snacks"><?php echo htmlspecialchars($meals['snacks']['name']); ?></span><br>
                        <span class="meal-detail">Calories: <?php echo $meals['snacks']['calories']; ?> kcal | Optimal time: <?php echo $meals['snacks']['time']; ?></span>
                        <div class="total-calories" style="margin-top:8px;font-weight:600;color:#2E7D32;">Total Calories: <?php echo $total_calories; ?> kcal</div>
                    </li>
                </ul>
                <button id="generate-plan">Generate New Meal Plan</button>
            </section>
            <section class="quick-stats">
                <h2>Quick Stats</h2>
                <ul>
                    <li>Calories: <span id="calories"><?php echo $total_calories; ?></span></li>
                    <li>Macros: <span id="macros">Protein 100g, Carbs 250g, Fat 70g</span></li>
                    <li>Diet: <span id="diet">Vegetarian</span></li>
                </ul>
            </section>
            <section class="recent-activity">
                <h2>Recent Activity</h2>
                <ul>
                    <li>Generated a new meal plan</li>
                    <li>Updated preferences</li>
                </ul>
            </section>
        </main>
    </div>
</body>
</html>
