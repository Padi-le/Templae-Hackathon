    <!-- public/index.html -->
    <!DOCTYPE html>
    <html>
    <head>
        <title>Global Chat</title>
        <style>
            /* Basic styling for chat interface */
        </style>
    </head>
    <body>
        <ul id="messages"></ul>
        <form id="form" action="">
            <input id="input" autocomplete="off" /><button>Send</button>
        </form>

        <script src="http://localhost:3001/socket.io/socket.io.js"></script>
        <script>
            const socket = io("http://localhost:3001");
            const form = document.getElementById('form');
            const input = document.getElementById('input');
            const messages = document.getElementById('messages');

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                if (input.value) {
                    socket.emit('chat message', input.value);
                    input.value = '';
                }
            });

            socket.on('chat message', (msg) => {
                console.log(msg);
                const item = document.createElement('li');
                item.textContent = msg;
                messages.appendChild(item);
                window.scrollTo(0, document.body.scrollHeight);
            });
        </script>
    </body>
    </html>