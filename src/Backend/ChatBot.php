<?php

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MealHealthBot - Your Nutrition Assistant</title>
    <link rel="stylesheet" href="css/ChatBot.css">
</head>
<?php include "header.php";?>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <div class="bot-info">
                <div class="bot-avatar">HB</div>
                <div class="bot-details">
                    <h2>HealthBot</h2>
                    <p>Your nutrition and meal planning assistant</p>
                </div>
            </div>
            <div class="status-indicator" id="statusIndicator"></div>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="message bot-message">
                <div class="message-content">
                    <p>Hello! I'm HealthBot planning, nutrition tips, and healthy eating advice. What would you like to know about today?</p>
                </div>
                <div class="message-time">Just now</div>
            </div>
        </div>
        
        <div class="chat-input-container">
            <form id="chatForm" class="chat-form">
                <div class="input-wrapper">
                    <input type="text" id="userInput" placeholder="Ask about meals, recipes, or nutrition..." autocomplete="off">
                    <button type="submit" id="sendButton">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 2L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </form>
            <div class="suggestion-tags">
                <span class="tag" data-query="Quick healthy breakfast ideas">Breakfast</span>
                <span class="tag" data-query="Vegetarian dinner recipes">Dinner</span>
                <span class="tag" data-query="How to meal prep for the week">Meal Prep</span>
                <span class="tag" data-query="Low-carb snack ideas">Snacks</span>
            </div>
        </div>
    </div>

    <script src="ChatBot.js"></script>
</body>

</html>
