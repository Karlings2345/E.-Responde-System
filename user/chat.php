<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Mark admin messages as read
$conn->prepare("UPDATE messages SET is_read = 1 WHERE user_id = ? AND sender = 'admin'")->execute([$user_id]);

// Fetch messages
$stmt = $conn->prepare("SELECT * FROM messages WHERE user_id = ? ORDER BY created_at ASC");
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch auto replies for suggested questions
$auto_replies = $conn->query("SELECT question, reply FROM auto_replies LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Handle sending message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $msg = trim($_POST['message']);
    $insert = $conn->prepare("INSERT INTO messages (user_id, message, sender) VALUES (?, ?, 'user')");
    $insert->execute([$user_id, $msg]);

    // Auto-reply based on keyword match
    $auto_stmt = $conn->query("SELECT question, reply FROM auto_replies");
    foreach ($auto_stmt as $row) {
        if (stripos($msg, $row['question']) !== false) {
            $autoReply = $conn->prepare("INSERT INTO messages (user_id, message, sender) VALUES (?, ?, 'admin')");
            $autoReply->execute([$user_id, $row['reply']]);
            break;
        }
    }

    header("Location: chat.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chat | E-Responde</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <!-- Bootstrap Icons CDN -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f2f4f8;
        }

        .chat-card {
            max-width: 700px;
            margin: 50px auto;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .chat-header {
            background-color: #0d6efd;
            color: white;
            padding: 20px;
            font-size: 1.2rem;
        }

        .chat-body {
            padding: 20px;
            background-color: #fff;
        }

        .chat-box {
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            background: #f9f9f9;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        .admin {
            align-items: flex-start;
        }

        .user {
            align-items: flex-end;
        }

        .bubble {
            padding: 12px 18px;
            border-radius: 20px;
            max-width: 75%;
            word-wrap: break-word;
        }

        .bubble-admin {
            background-color: #e0eaff;
            color: #003366;
        }

        .bubble-user {
            background-color: #c8f7c5;
            color: #084c2f;
        }

        .timestamp {
            font-size: 0.8rem;
            color: #888;
            margin-top: 5px;
        }

        .suggested {
            margin-bottom: 15px;
        }

        .suggested .badge {
            cursor: pointer;
            padding: 10px 14px;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .form-control {
            border-radius: 12px;
        }

        .btn-primary {
            border-radius: 12px;
            padding: 10px 30px;
        }

        @media (max-width: 576px) {
            .bubble {
                max-width: 100%;
            }
        }
    </style>

    <script>
        function fillMessage(msg) {
            document.getElementById('msgBox').value = msg;
            document.getElementById('msgBox').focus();
        }
    </script>
</head>
<body>

<div class="container">
    <div class="chat-card card">
        <div class="chat-header">
            💬 Chat with Admin
        </div>
        <div class="chat-body">

            <!-- Back to Dashboard -->
            <a href="dashboard.php" class="btn btn-link text-decoration-none mb-3">⬅ Back to Dashboard</a>

            <!-- Suggested Questions -->
            <div class="suggested mb-3">
                <strong>Suggested Questions:</strong><br>
                <?php foreach ($auto_replies as $suggest): ?>
                    <span class="badge bg-info text-dark me-1 mb-2" onclick="fillMessage('<?= htmlspecialchars($suggest['question'], ENT_QUOTES) ?>')">
                        <?= htmlspecialchars($suggest['question']) ?>
                    </span>
                <?php endforeach; ?>
            </div>

            <!-- Chat Messages -->
            <div class="chat-box mb-4">
                <?php foreach ($messages as $msg): ?>
                    <div class="message <?= $msg['sender'] === 'admin' ? 'admin' : 'user' ?>">
                        <div class="bubble <?= $msg['sender'] === 'admin' ? 'bubble-admin' : 'bubble-user' ?>">
                            <?= nl2br(htmlspecialchars($msg['message'])) ?>
                        </div>
                        <span class="timestamp"><?= date('M d, Y h:i A', strtotime($msg['created_at'])) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Send Message -->
             
            <form method="post">
    <div class="d-flex align-items-center bg-white rounded-pill px-3 py-2 shadow-sm">
        <textarea name="message" id="msgBox" rows="1" class="form-control border-0 bg-transparent" required placeholder="Type a message..." style="resize: none; box-shadow: none;"></textarea>
        <button type="submit" class="btn btn-primary rounded-circle ms-2 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
            <i class="bi bi-send-fill text-white"></i>
        </button>
    </div>
</form>
<script>
    const msgBox = document.getElementById('msgBox');
    msgBox.addEventListener('input', () => {
        msgBox.style.height = 'auto';
        msgBox.style.height = (msgBox.scrollHeight) + 'px';
    });
</script>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
