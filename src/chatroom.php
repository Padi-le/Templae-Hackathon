<!-- index.php -->
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>MealHealthBot</title>
  <style>
    body { font-family: Arial; max-width: 720px; margin: 2rem auto; }
    #chat { border: 1px solid #ddd; padding: 1rem; min-height: 200px; }
    .msg { margin: .6rem 0; }
    .user { font-weight: bold; }
    .bot { color: #1a6; }
  </style>
</head>
<body>
  <h1>MealHealthBot</h1>
  <div id="chat"></div>
  <form id="form" onsubmit="return sendMsg();">
    <input id="msg" type="text" placeholder="Ask about recipes, nutrition, etc." style="width:80%;" required />
    <button type="submit">Send</button>
  </form>

  <script>
async function sendMsg() {
  const input = document.getElementById("msg");
  const text = input.value.trim();
  if (!text) return false;

  const chat = document.getElementById("chat");
  chat.innerHTML += `<div class="msg user">You: ${escapeHtml(text)}</div>`;
  input.value = "";

  try {
    console.log('Sending message:', text);
    const resp = await fetch("http://localhost:3001/chat", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ message: text })
    });

    console.log('Response status:', resp.status);

    if (!resp.ok) {
      const errorData = await resp.json();
      const errorMessage = errorData.details || "An unknown server error occurred.";
      chat.innerHTML += `<div class="msg bot">Bot: Error: ${escapeHtml(errorMessage)}</div>`;
      chat.scrollTop = chat.scrollHeight;
      return false;
    }

    const data = await resp.json();
    const reply = data.reply || "No reply";
    chat.innerHTML += `<div class="msg bot">Bot: ${escapeHtml(reply)}</div>`;
    chat.scrollTop = chat.scrollHeight;
  } catch (e) {
    console.error('Network or parsing error:', e);
    chat.innerHTML += `<div class="msg bot">Bot: Network error. Could not connect to the server.</div>`;
    chat.scrollTop = chat.scrollHeight;
  }
  return false;
}

function escapeHtml(unsafe) {
  return unsafe.replace(/[&<"'>]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#039;"}[m]));
}
  </script>
</body>
</html>
