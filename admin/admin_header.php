<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$admin_id = $_SESSION['admin_id'];
$stmt = $conn->prepare("SELECT barangay FROM admins WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<style>
    body {
        font-family: Arial, sans-serif;
        background: #eef1f5;
        margin: 0;
        padding: 0;
    }

    .header {
        background-color: #007bff;
        color: white;
        padding: 15px;
        text-align: center;
        position: relative;
    }

    .header img {
        position: absolute;
        left: 20px;
        top: 10px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
    }

    .logout {
        position: absolute;
        right: 20px;
        top: 15px;
        background: #dc3545;
        color: white;
        padding: 5px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .logout:hover {
        background: #c82333;
    }

    .sidebar {
        width: 200px;
        background-color: #343a40;
        color: white;
        position: fixed;
        top: 70px;
        right: 0;
        bottom: 0;
        padding-top: 20px;
        z-index: 999;
    }

    .sidebar a {
        color: white;
        display: block;
        padding: 10px 20px;
        text-decoration: none;
    }

    .sidebar a:hover {
        background-color: #495057;
    }

    .content {
        margin-right: 200px;
        padding: 30px;
    }
</style>

<div class="header">
    <img src="../assets/lgu-logo.png" alt="LGU Logo">
    <h1>E-Responde Admin Dashboard</h1>
    <form method="post" action="logout.php" style="display:inline;">
        <button class="logout" type="submit">Logout</button>
    </form>
</div>

<div class="sidebar">
    <a href="dashboard.php">🏠 Dashboard</a>
    <a href="manage_users.php">👥 Manage Users</a>
    <a href="filed_complaints.php">📂 Filed Complaints</a>
    <a href="complaint_map.php">🗺️ Complaint Map</a>
    <a href="complaint_archive.php">🗃️ Archives</a>
    <a href="message_list.php">💬 Live Chat</a>
    <a href="manage_auto_reply.php">🤖 Auto Replies</a>
</div>
