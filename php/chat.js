const chatInput = document.getElementById('chatInput');
const chatContainer = document.getElementById('chat-container');

    chatInput.addEventListener('keyup', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault();

            const userMessage = chatInput.value.trim(); 

            if (userMessage !== '') {

                sendMessage(userMessage);
                console.log(userMessage);

                chatInput.value = '';
            }
        }
    });

async function sendMessage(message) {
    try {

        // Display user message in chat container
        const userDiv = document.createElement('div');
        userDiv.classList.add('message');
        userDiv.classList.add('user');
        userDiv.textContent = `You: ${message}`;
        chatContainer.appendChild(userDiv);

        
        const response = await fetch('chat.php', { // Target your chat.php script
            method: 'POST', // Use POST method to send data in the body
            headers: {
                'Content-Type': 'application/json', // Tell the server we're sending JSON
                'Accept': 'application/json' // Tell the server we prefer JSON in return
            },
            // Convert the JavaScript object into a JSON string for the request body
            body: JSON.stringify({
                message: message,
            })
        });

        // Check if the response is OK (HTTP status 200-299)
        if (!response.ok) {
            const errorText = await response.text(); // Get raw error response for debugging
            console.error('Network response was not ok:', response.status, errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        // Parse the JSON response from chat.php
        const data = await response.json();

        // Display AI response
        const aiDiv = document.createElement('div');
        aiDiv.classList.add('message');
        aiDiv.classList.add('ai');
        aiDiv.textContent = `AI: ${data.response}`; // Assuming chat.php sends back { "response": "AI text" }
        chatContainer.appendChild(aiDiv);

        chatContainer.scrollTop = chatContainer.scrollHeight; // Scroll to bottom

    } catch (error) {
        console.error('Error sending message:', error);
        const errorDiv = document.createElement('div');
        errorDiv.textContent = `Error: ${error.message}`;
        errorDiv.style.color = 'red';
        chatContainer.appendChild(errorDiv);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
}