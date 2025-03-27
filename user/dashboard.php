<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$full_name = $user['first_name'] . ' ' . $user['middle_name'] . ' ' . $user['last_name'];
$full_address = 'Purok ' . $user['purok'] . ', ' . $user['address'];

$msgStmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE user_id = ? AND sender = 'admin' AND is_read = 0");
$msgStmt->execute([$_SESSION['user_id']]);
$unread_messages = $msgStmt->fetchColumn();

// Complaint Summary
$complaintStmt = $conn->prepare("SELECT status, COUNT(*) as count FROM complaints WHERE user_id = ? GROUP BY status");
$complaintStmt->execute([$_SESSION['user_id']]);
$complaintStats = [
    'Pending' => 0,
    'Complaint Received' => 0,
    'Fixing the Issue' => 0,
    'Done' => 0,
];

while ($row = $complaintStmt->fetch(PDO::FETCH_ASSOC)) {
    $complaintStats[$row['status']] = $row['count'];
}

$totalComplaints = array_sum($complaintStats);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - E-Responde</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            transition: all 0.4s ease-in-out;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: url('../homepage assets/cover.png') no-repeat center center/cover;
            color: white;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(0, 100, 0, 0.9);
            padding: 15px 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        .logo {
            height: 60px;
        }

        .menu {
            font-size: 32px;
            background: rgba(0, 128, 0, 0.9);
            padding: 10px 16px;
            border-radius: 12px;
            cursor: pointer;
            color: white;
            display: flex;
            align-items: center;
        }

        .content {
            text-align: center;
            padding: 80px 20px;
        }

        h1 {
            font-size: 2.8rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        p {
            font-size: 1.2rem;
            margin-top: 10px;
        }

        .buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            margin-top: 30px;
        }

        .btn-action {
            background: rgba(255, 255, 255, 0.95);
            color: #000;
            padding: 20px 24px;
            width: 240px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 22px;
            box-shadow: 0 6px 14px rgba(0,0,0,0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .btn-action i {
            font-size: 1.6rem;
            margin-right: 10px;
        }

        .btn-action:hover {
            transform: translateY(-6px) scale(1.04);
            background: #eaffea;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        .sidebar {
            position: fixed;
            top: 0;
            right: -100%;
            width: 320px;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            color: #000;
            padding: 30px;
            box-shadow: -4px 0 20px rgba(0,0,0,0.3);
            z-index: 2000;
            transition: right 0.5s ease;
            backdrop-filter: blur(12px);
            border-top-left-radius: 20px;
            border-bottom-left-radius: 20px;
        }

        .sidebar.active {
            right: 0;
        }

        .sidebar img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .sidebar h5 {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .sidebar p {
            font-size: 0.95rem;
            margin: 3px 0;
        }

        .sidebar a {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            padding: 10px;
            border-radius: 10px;
        }

        .logout {
            background: #dc3545;
            color: white;
        }

        .back-btn {
            background: #6c757d;
            color: white;
        }

        .sidebar small {
            display: block;
            text-align: center;
            margin-top: 30px;
            font-size: 0.8rem;
            color: #666;
        }

        .summary-cards .card {
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .summary-cards .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }

        .summary-cards h5 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .summary-cards h3 {
            font-size: 2rem;
            font-weight: bold;
        }

        @media (max-width: 576px) {
            .btn-action {
                width: 90%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../homepage assets/systemlogo.png" class="logo" alt="E-Responde Logo">
        <div class="menu" onclick="toggleSidebar()"><i class="bi bi-person-circle"></i></div>
    </div>

    <div class="sidebar" id="sidebar">
        <center>
            <img src="./uploads/<?= htmlspecialchars($user['profile_picture']) ?>" alt="Profile Picture">
            <h5><?= htmlspecialchars($full_name) ?></h5>
            <p><?= htmlspecialchars($full_address) ?></p>
            <p><?= htmlspecialchars($user['phone']) ?></p>
            <p><?= htmlspecialchars($user['email']) ?></p>
        </center>
        <a href="dashboard.php" class="back-btn"><i class="bi bi-arrow-left-circle"></i> Back</a>
        <a href="logout.php" class="logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
        <small>&copy; 2025 All Rights Reserved</small>
    </div>

    <div class="content">
        <h1>Welcome, <?= htmlspecialchars($user['first_name']) ?>!</h1>
        <p>What would you like to do today?</p>
        <div class="buttons">
            <a href="file_complaint.php" class="btn-action"><i class="bi bi-pencil-square"></i> File a Complaint</a>
            <a href="complaint_tracker.php" class="btn-action"><i class="bi bi-list-check"></i> Track Complaint</a>
            <a href="map_of_complaints.php" class="btn-action"><i class="bi bi-map"></i> View Map</a>
            <a href="chat.php" class="btn-action">
                <i class="bi bi-chat-dots"></i> Messages<?= $unread_messages > 0 ? " ({$unread_messages})" : "" ?>
            </a>
        </div>

        <div class="summary-cards mt-5">
            <div class="row justify-content-center g-4">
                <div class="col-md-3 col-sm-6">
                    <div class="card text-dark bg-light p-3 rounded-4 shadow">
                        <h5 class="text-success"><i class="bi bi-file-earmark-text"></i> Total Filed</h5>
                        <h3><?= $totalComplaints ?></h3>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="card text-dark bg-light p-3 rounded-4 shadow">
                        <h5 class="text-primary"><i class="bi bi-envelope-open"></i> Recieve</h5>
                        <h3><?= $complaintStats['Fixing the Issue'] ?></h3>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-dark bg-light p-3 rounded-4 shadow">
                    <h5 class="text-warning"><i class="bi bi-hourglass-split"></i> in process</h5>
                        <h3><?= $complaintStats['Complaint Received'] ?></h3>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="card text-dark bg-light p-3 rounded-4 shadow">
                        <h5 class="text-success"><i class="bi bi-check-circle"></i> Resolved</h5>
                        <h3><?= $complaintStats['Done'] ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>
</html>
