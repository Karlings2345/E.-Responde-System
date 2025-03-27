<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$user_id = $_GET['user_id'];

$stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', middle_name, ' ', last_name) AS full_name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO messages (user_id, sender, message, created_at) VALUES (?, 'admin', ?, NOW())");
        $stmt->execute([$user_id, $message]);
    }
    header("Location: user_chat.php?user_id=$user_id");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Chat with <?= htmlspecialchars($user['full_name']) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        h2 {
            margin-top: 0;
        }

        #chatBox {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            height: 400px;
            overflow-y: scroll;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border: 1px solid #ccc;
            margin-bottom: 15px;
        }

        .message {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 20px;
            max-width: 70%;
            clear: both;
        }

        .admin {
            background-color: #d1e7dd;
            float: right;
            text-align: right;
        }

        .user {
            background-color: #f8d7da;
            float: left;
        }

        .timestamp {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
            display: block;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            resize: vertical;
        }

        button {
            margin-top: 8px;
            padding: 10px 20px;
            border: none;
            background: #3498db;
            color: white;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            background: #2980b9;
        }

        a {
            text-decoration: none;
            color: #3498db;
        }
    </style>

    <script>
        function fetchMessages() {
            const userId = <?= json_encode($user_id) ?>;
            fetch('user_chat_ajax.php?user_id=' + userId)
                .then(response => response.json())
                .then(data => {
                    const chatBox = document.getElementById('chatBox');
                    chatBox.innerHTML = '';
                    data.forEach(msg => {
                        const msgClass = msg.sender === 'admin' ? 'admin' : 'user';
                        chatBox.innerHTML += `
                            <div class="message ${msgClass}">
                                ${msg.message}
                                <span class="timestamp">${msg.created_at}</span>
                            </div>
                        `;
                    });
                    chatBox.scrollTop = chatBox.scrollHeight;
                });
        }

        setInterval(fetchMessages, 5000);
        window.onload = fetchMessages;
    </script>
</head>
<body>

<h2>💬 Chat with <?= htmlspecialchars($user['full_name']) ?></h2>
<a href="message_list.php">⬅ Back to Message List</a>

<div id="chatBox">
    <!-- Messages will load here -->
</div>

<form method="POST">
    <textarea name="message" rows="3" placeholder="Type your reply..." required></textarea>
    <button type="submit">Send</button>
</form>

</body>
</html>
