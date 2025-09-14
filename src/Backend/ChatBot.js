

document.addEventListener("DOMContentLoaded", function() {
    const chatForm = document.getElementById("chatForm");
    const userInput = document.getElementById("userInput");
    const sendButton = document.getElementById("sendButton");
    const API_URL = "http://localhost:3001/chat";
    
    // Add suggestion tag functionality
    document.querySelectorAll(".tag").forEach(tag => {
        tag.addEventListener("click", () => {
            userInput.value = tag.getAttribute("data-query");
            userInput.focus();
        });
    });

    userInput.addEventListener("keydown", function(e) {
        if (e.key === "Enter" && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event("submit"));
        }
    });
    
    userInput.focus();

    chatForm.addEventListener("submit", async function(e) {
        e.preventDefault();
        handleUserMessage();
    });
    
    function formatTime(date) {
        return date.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
    }

    async function handleUserMessage(){
        const userInput = document.getElementById("userInput");
        const message = userInput.value.trim();
        if(!message)
            return;
        addMessage(message, true);
        userInput.value = "";
        document.getElementById("sendButton").disabled = true;
        await sendToBackend(message);
        document.getElementById("sendButton").disabled = false;
    }

    async function sendToBackend(message){
        showTypingIndicator();
        try{
            const response = await fetch(API_URL, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ message: message }) 
            });
            console.log(response);
            if(!response.ok){
                const errorData = await response.json().catch(()=>({}));
                throw new Error(errorData.details);
            }
            const data = await response.json();
            hideTypingIndicator();
            if(data.error)
                showError(data.details);
            else
                addMessage(data.reply);
        }
        catch(error){
            console.error(error);
        }
    }

    function addMessage(content, isUser = false) {
        const chatMessages = document.getElementById("chatMessages");
        const messageDiv = document.createElement("div");
        messageDiv.className = "message bot-message";
        if(isUser)
            messageDiv.className = "message user-message";
        const messageContent = document.createElement("div");
        messageContent.className = "message-content";
        const messageText = document.createElement("p");
        messageText.textContent = content;
        const messageTime = document.createElement("div");
        messageTime.className = "message-time";
        messageTime.textContent = formatTime(new Date());
        messageContent.appendChild(messageText);
        messageDiv.appendChild(messageContent);
        messageDiv.appendChild(messageTime);
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    function showTypingIndicator() {
        const chatMessages = document.getElementById("chatMessages");
        const typingDiv = document.createElement("div");
        typingDiv.id = "typingIndicator";
        typingDiv.className = "typing-indicator";
        
        for(let i = 0; i < 3; i++){
            const dot = document.createElement("div");
            dot.className = "typing-dot";
            typingDiv.appendChild(dot);
        }
        
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    function hideTypingIndicator() {
        const typingIndicator = document.getElementById("typingIndicator");
        if(typingIndicator)
            typingIndicator.remove();
    }
    
    function showError(message){
        const chatMessages = document.getElementById("chatMessages");
        const errorDiv = document.createElement("div");
        errorDiv.className = "error-message";
        errorDiv.textContent = message;
        
        chatMessages.appendChild(errorDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
});