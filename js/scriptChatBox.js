   document.addEventListener('DOMContentLoaded', function() {
      const chatContainer = document.getElementById('chatContainer');
      const aenBubble = document.getElementById('aen-bubble');

      // ✅ Make sure both elements exist
      if (aenBubble && chatContainer) {
        aenBubble.addEventListener('click', () => {
          if (chatContainer.style.display === 'flex') {
            chatContainer.style.display = 'none';
          } else {
            chatContainer.style.display = 'flex';
          }
        });
      } else {
        console.error('Chat elements not found in the DOM.');
      }
    });

async function sendMessage() {
  const input = document.getElementById('userInput');
  const chatBox = document.getElementById('chatBox');
  const text = input.value.trim();
  if (!text) return;

  // Display user message
  const userMsg = document.createElement('div');
  userMsg.className = 'message user';
  userMsg.textContent = text;
  chatBox.appendChild(userMsg);
  chatBox.scrollTop = chatBox.scrollHeight;
  input.value = '';

  // Fetch bot response from PHP
  try {
    const response = await fetch('chat_response.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'message=' + encodeURIComponent(text)
    });

    const data = await response.json();
    const botMsg = document.createElement('div');
    botMsg.className = 'message bot';
    botMsg.textContent = data.response;
    chatBox.appendChild(botMsg);
    chatBox.scrollTop = chatBox.scrollHeight;
  } catch (error) {
    const botMsg = document.createElement('div');
    botMsg.className = 'message bot';
    botMsg.textContent = "⚠️ There was an error connecting to the server.";
    chatBox.appendChild(botMsg);
  }
}