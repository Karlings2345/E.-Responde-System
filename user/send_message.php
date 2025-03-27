<?php
// user/send_message.php

session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Unauthorized";
    exit;
}

$user_id = $_SESSION['user_id'];
$message = trim($_POST['message'] ?? '');

if (empty($message)) {
    echo "Message cannot be empty.";
    exit;
}

// Save user message
$stmt = $conn->prepare("INSERT INTO messages (user_id, sender, message, created_at, is_read) VALUES (?, 'user', ?, NOW(), 0)");
$stmt->execute([$user_id, $message]);

// === AUTO-REPLY BOT LOGIC ===

// Step 1: Check if admin is offline (no recent admin reply within 10 minutes)
$check_admin = $conn->prepare("SELECT created_at FROM messages WHERE user_id = ? AND sender = 'admin' ORDER BY created_at DESC LIMIT 1");
$check_admin->execute([$user_id]);
$last_admin_msg = $check_admin->fetchColumn();

$admin_offline = true;
if ($last_admin_msg) {
    $last_time = strtotime($last_admin_msg);
    if ((time() - $last_time) < 600) { // Admin replied within last 10 minutes
        $admin_offline = false;
    }
}

// Step 2: Check if message matches a keyword and send auto-reply
if ($admin_offline) {
    $search = $conn->prepare("SELECT reply FROM auto_replies WHERE ? LIKE CONCAT('%', keyword, '%') LIMIT 1");
    $search->execute([$message]);
    $bot_reply = $search->fetchColumn();

    if ($bot_reply) {
        $insert_bot = $conn->prepare("INSERT INTO messages (user_id, sender, message, created_at, is_read) VALUES (?, 'admin', ?, NOW(), 0)");
        $insert_bot->execute([$user_id, $bot_reply]);
    }
}

echo "Message sent.";
?>
