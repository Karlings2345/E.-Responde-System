<!DOCTYPE html>
<html>
<head>
    <title>Chat with Admin</title>
    <script>
        // Function to fetch messages using AJAX
        function fetchMessages() {
            fetch('live_chat_ajax.php')
                .then(response => response.json())
                .then(data => {
                    const chatBox = document.getElementById('chatBox');
                    chatBox.innerHTML = '';  // Clear chat box

                    data.forEach(msg => {
                        let msgElement = `<p><strong>${msg.sender.charAt(0).toUpperCase() + msg.sender.slice(1)}:</strong> ${msg.message}
                        <br><small><em>${msg.created_at}</em></small></p><hr>`;
                        chatBox.innerHTML += msgElement;
                    });

                    chatBox.scrollTop = chatBox.scrollHeight;  // Auto-scroll to bottom
                });
        }

        // Auto-fetch messages every 5 seconds
        setInterval(fetchMessages, 5000);

        // Fetch messages initially when page loads
        window.onload = fetchMessages;
    </script>
</head>
<body>
<h2>💬 Chat with Admin</h2>
<a href="dashboard.php">⬅ Back to Dashboard</a><br><br>

<div id="chatBox" style="border:1px solid #aaa; padding:10px; height:300px; overflow-y:scroll;">
    <!-- Messages will be dynamically loaded here -->
</div>

<form method="POST">
    <textarea name="message" rows="3" cols="50" placeholder="Type your message..." required></textarea><br>
    <button type="submit">Send</button>
</form>
</body>
</html>
