<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user chat summaries with unread count
$stmt = $conn->prepare("SELECT 
    m.user_id,
    CONCAT(u.first_name, ' ', u.middle_name, ' ', u.last_name) AS full_name,
    u.email,
    u.phone,
    u.purok,
    u.address,
    COUNT(CASE WHEN m.sender = 'user' AND m.is_read = 0 THEN 1 END) AS unread_count,
    MAX(m.created_at) as last_message_time
FROM messages m
JOIN users u ON m.user_id = u.id
GROUP BY m.user_id
ORDER BY last_message_time DESC");
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Message List - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 20px;
        }

        h2 {
            margin-top: 0;
        }

        a {
            text-decoration: none;
            color: #3498db;
        }

        .nav-links {
            margin-bottom: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .card h3 {
            margin: 0;
        }

        .unread {
            background-color: red;
            color: white;
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 20px;
            margin-left: 10px;
        }

        .contact-info {
            font-size: 0.9em;
            color: #555;
            margin-top: 5px;
        }

        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <h2>📬 User Message List</h2>

    <div class="nav-links">
        <a href="dashboard.php">⬅ Back to Dashboard</a> |
        <a href="manage_auto_reply.php">🤖 Manage Chatbot Auto-Replies</a>
    </div>

    <?php if (count($results) > 0): ?>
        <?php foreach ($results as $row): ?>
            <div class="card">
                <h3>
                    <a href="user_chat.php?user_id=<?= $row['user_id'] ?>">
                        👤 <?= htmlspecialchars($row['full_name']) ?>
                    </a>
                    <?php if ($row['unread_count'] > 0): ?>
                        <span class="unread"><?= $row['unread_count'] ?> unread</span>
                    <?php endif; ?>
                </h3>

                <div class="contact-info">
                    📧 Email: <?= htmlspecialchars($row['email']) ?><br>
                    📞 Phone: <?= htmlspecialchars($row['phone']) ?><br>
                    🏡 Address: <?= htmlspecialchars('Purok ' . $row['purok'] . ', ' . $row['address']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No user messages yet.</p>
    <?php endif; ?>
</body>
</html>
