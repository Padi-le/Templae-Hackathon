<?php
// chatRoom.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Chat Room</title>
    <link rel="stylesheet" href="css/chatRoom.css">
</head>
<?php include "header.php";?>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <div class="chat-info">
                <div class="chat-avatar">GC</div>
                <div class="chat-details">
                    <h2>Global Chat Room</h2>
                    <p>Connect with users worldwide</p>
                </div>
            </div>
            <div class="online-indicator">
                <span id="onlineCount">0</span> online
            </div>
        </div>
        
        <div class="chat-messages" id="messages">
            <div class="message system-message">
                <div class="message-content">
                    <p>Welcome to the Global Chat Room! Start chatting with people around the world.</p>
                </div>
                <div class="message-time">Just now</div>
            </div>
        </div>
        
        <div class="chat-input-container">
            <form id="form" class="chat-form">
                <div class="input-wrapper">
                    <input type="text" id="input" placeholder="Type your message here..." autocomplete="off">
                    <button type="submit" id="sendButton">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22 2L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M22 2L15 22L11 13L2 9L22 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="http://localhost:3001/socket.io/socket.io.js"></script>
    <script>
        const socket = io("http://localhost:3001");
        const form = document.getElementById('form');
        const input = document.getElementById('input');
        const messages = document.getElementById('messages');
        const onlineCount = document.getElementById('onlineCount');
        const sendButton = document.getElementById('sendButton');

        // Generate a random username for this session
        const randomUsername = "User" + Math.floor(Math.random() * 10000);
        let isConnected = false;

        // Handle connection events
        socket.on('connect', () => {
            isConnected = true;
            console.log('Connected to chat server');
            addSystemMessage('Connected to chat server');
        });

        socket.on('disconnect', () => {
            isConnected = false;
            console.log('Disconnected from chat server');
            addSystemMessage('Disconnected from chat server');
        });

        socket.on('user count', (count) => {
            onlineCount.textContent = count;
        });

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (input.value.trim() && isConnected) {
                socket.emit('chat message', {
                    username: randomUsername,
                    message: input.value.trim(),
                    timestamp: new Date().toISOString()
                });
                input.value = '';
            }
        });

        socket.on('chat message', (data) => {
            addMessage(data.message, data.username, data.timestamp, data.username === randomUsername);
        });

        function addMessage(message, username, timestamp, isMyMessage = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isMyMessage ? 'my-message' : 'other-message'}`;
            
            const messageContent = document.createElement('div');
            messageContent.className = 'message-content';
            
            if (!isMyMessage) {
                const senderSpan = document.createElement('div');
                senderSpan.className = 'message-sender';
                senderSpan.textContent = username;
                messageContent.appendChild(senderSpan);
            }
            
            const messageText = document.createElement('p');
            messageText.textContent = message;
            
            const messageTime = document.createElement('div');
            messageTime.className = 'message-time';
            messageTime.textContent = formatTime(new Date(timestamp));
            
            messageContent.appendChild(messageText);
            messageDiv.appendChild(messageContent);
            messageDiv.appendChild(messageTime);
            
            messages.appendChild(messageDiv);
            
            messages.scrollTop = messages.scrollHeight;
        }

        function addSystemMessage(message) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'message system-message';
            
            const messageContent = document.createElement('div');
            messageContent.className = 'message-content';
            
            const messageText = document.createElement('p');
            messageText.textContent = message;
            
            const messageTime = document.createElement('div');
            messageTime.className = 'message-time';
            messageTime.textContent = formatTime(new Date());
            
            messageContent.appendChild(messageText);
            messageDiv.appendChild(messageContent);
            messageDiv.appendChild(messageTime);
            
            messages.appendChild(messageDiv);
            
            messages.scrollTop = messages.scrollHeight;
        }

        function formatTime(date) {
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        input.addEventListener('focus', () => {
            document.querySelector('.chat-input-container').classList.add('focused');
        });

        input.addEventListener('blur', () => {
            document.querySelector('.chat-input-container').classList.remove('focused');
        });

        socket.on('connect_error', (error) => {
            addSystemMessage('Connection error: Unable to connect to chat server');
            console.error('Connection error:', error);
        });

        socket.on('reconnect', (attemptNumber) => {
            addSystemMessage('Reconnected to chat server');
            console.log('Reconnected after', attemptNumber, 'attempts');
        });
    </script>
</body>

</html>