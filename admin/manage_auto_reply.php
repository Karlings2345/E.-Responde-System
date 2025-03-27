<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Handle add auto reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'], $_POST['keyword'], $_POST['reply'])) {
    $question = trim($_POST['question']);
    $keyword = trim($_POST['keyword']);
    $reply = trim($_POST['reply']);

    if ($question && $keyword && $reply) {
        $stmt = $conn->prepare("INSERT INTO auto_replies (question, keyword, reply) VALUES (?, ?, ?)");
        $stmt->execute([$question, $keyword, $reply]);
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM auto_replies WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
}

// Fetch all auto replies
$stmt = $conn->prepare("SELECT * FROM auto_replies ORDER BY id DESC");
$stmt->execute();
$autoReplies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Chatbot Auto-Replies</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            padding: 20px;
        }

        h2 {
            color: #333;
        }

        form {
            background: #fff;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background-color: #28a745;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 6px;
            overflow: hidden;
        }

        table th, table td {
            padding: 12px;
            border: 1px solid #ddd;
        }

        table th {
            background: #f1f1f1;
        }

        .delete-btn {
            color: red;
            text-decoration: none;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            text-decoration: none;
            color: #007bff;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<a href="dashboard.php" class="back-link">⬅ Back to Dashboard</a>

<h2>🤖 Manage Chatbot Auto-Replies</h2>

<form method="POST">
    <label>❓ Question (for admin reference)</label>
    <input type="text" name="question" required>

    <label>🔑 Keyword (used for matching)</label>
    <input type="text" name="keyword" required>

    <label>💬 Auto-Reply</label>
    <textarea name="reply" rows="3" required></textarea>

    <button type="submit">➕ Add Auto-Reply</button>
</form>

<?php if (count($autoReplies) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Question</th>
                <th>Keyword</th>
                <th>Reply</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($autoReplies as $reply): ?>
                <tr>
                    <td><?= htmlspecialchars($reply['question']) ?></td>
                    <td><?= htmlspecialchars($reply['keyword']) ?></td>
                    <td><?= htmlspecialchars($reply['reply']) ?></td>
                    <td>
                        <a href="?delete=<?= $reply['id'] ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this auto-reply?')">🗑️ Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No auto-replies yet.</p>
<?php endif; ?>

</body>
</html>
