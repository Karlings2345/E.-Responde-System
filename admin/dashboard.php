<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

try {
    $check = $conn->query("SELECT COUNT(*) FROM auto_replies")->fetchColumn();
    if ($check == 0) {
        $defaults = [
            ['How to file a complaint?', 'Click on "File a Complaint" on your dashboard and fill out the form.'],
            ['How to check complaint status?', 'Go to "Complaint Tracker" in your dashboard to view status updates.'],
            ['What is E-Responde?', 'E-Responde is a platform to help you file and track complaints for your local area.'],
            ['How many photos should I upload?', 'Please upload at least 3 photos related to your complaint.']
        ];

        $stmt = $conn->prepare("INSERT INTO auto_replies (question, reply) VALUES (?, ?)");
        foreach ($defaults as $pair) {
            $stmt->execute($pair);
        }
    }
} catch (PDOException $e) {
    // Silent fail
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - E-Responde</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
        }

        .header {
            background-color: #004080;
            color: white;
            padding: 15px 30px;
        }

        .sidebar {
            background-color: #e9ecef;
            min-height: 100vh;
            padding-top: 20px;
        }

        .sidebar a {
            text-decoration: none;
            display: block;
            padding: 10px 20px;
            color: #333;
            border-radius: 10px;
            transition: 0.3s ease;
        }

        .sidebar a:hover {
            background-color: #d4d4d4;
            color: #004080;
        }

        .quick-box {
            transition: all 0.3s ease;
            border-radius: 10px;
        }

        .quick-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .lgu-logo {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
        }

        .card-link {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header d-flex justify-content-between align-items-center">
    <h1 class="h4 m-0">Admin Dashboard</h1>
    <button class="btn btn-light d-md-none" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
        <i class="bi bi-list fs-4"></i>
    </button>
</div>

<!-- Sidebar Offcanvas for small screens (slides from right) -->
<div class="offcanvas offcanvas-end d-md-none" tabindex="-1" id="sidebarMenu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div class="text-center mb-3">
            <img src="../assets/lgu-logo-placeholder.png" alt="LGU Logo" class="lgu-logo mb-2">
            <p class="mb-1 fw-semibold">Welcome, <?= htmlspecialchars($admin_name) ?></p>
        </div>
        <a href="manage_users.php"><i class="bi bi-people-fill me-2"></i>Manage Users</a>
        <a href="filed_complaints.php"><i class="bi bi-file-earmark-text me-2"></i>Filed Complaints</a>
        <a href="complaint_map.php"><i class="bi bi-map-fill me-2"></i>Map of Complaints</a>
        <a href="complaint_archives.php"><i class="bi bi-folder-fill me-2"></i>Complaint Archives</a>
        <a href="message_list.php"><i class="bi bi-chat-left-text-fill me-2"></i>Messages</a>
        <a href="manage_auto_reply.php"><i class="bi bi-robot me-2"></i>Chatbot Auto-Replies</a>
        <hr>
        <a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
    </div>
</div>

<!-- Body Layout -->
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar for desktop -->
        <div class="col-md-2 sidebar d-none d-md-block">
            <div class="text-center mb-3">
                <img src="../assets/lgu-logo-placeholder.png" alt="LGU Logo" class="lgu-logo mb-2">
                <p class="mb-1 fw-semibold">Welcome, <?= htmlspecialchars($admin_name) ?></p>
            </div>
            <a href="manage_users.php"><i class="bi bi-people-fill me-2"></i>Manage Users</a>
            <a href="filed_complaints.php"><i class="bi bi-file-earmark-text me-2"></i>Filed Complaints</a>
            <a href="complaint_map.php"><i class="bi bi-map-fill me-2"></i>Map of Complaints</a>
            <a href="complaint_archives.php"><i class="bi bi-folder-fill me-2"></i>Complaint Archives</a>
            <a href="message_list.php"><i class="bi bi-chat-left-text-fill me-2"></i>Messages</a>
            <a href="manage_auto_reply.php"><i class="bi bi-robot me-2"></i>Chatbot Auto-Replies</a>
            <hr>
            <a href="logout.php" class="text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
        </div>

        <!-- Main Dashboard Content -->
        <div class="col-md-10 p-4">
            <h2 class="mb-3">Dashboard Overview</h2>
            <center><p>This is where you will manage users, complaints, messages, and chatbot auto-replies.</p></center>

            <div class="row g-4 mt-4">
                <div class="col-md-4">
                    <a href="manage_users.php" class="card-link">
                        <div class="p-4 bg-white border shadow-sm quick-box text-center">
                            <i class="bi bi-people-fill fs-1 text-primary"></i>
                            <h5 class="mt-2">Manage Users</h5>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="filed_complaints.php" class="card-link">
                        <div class="p-4 bg-white border shadow-sm quick-box text-center">
                            <i class="bi bi-file-earmark-text fs-1 text-success"></i>
                            <h5 class="mt-2">Filed Complaints</h5>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="complaint_map.php" class="card-link">
                        <div class="p-4 bg-white border shadow-sm quick-box text-center">
                            <i class="bi bi-map-fill fs-1 text-info"></i>
                            <h5 class="mt-2">Map of Complaints</h5>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="complaint_archives.php" class="card-link">
                        <div class="p-4 bg-white border shadow-sm quick-box text-center">
                            <i class="bi bi-folder-fill fs-1 text-warning"></i>
                            <h5 class="mt-2">Complaint Archives</h5>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="message_list.php" class="card-link">
                        <div class="p-4 bg-white border shadow-sm quick-box text-center">
                            <i class="bi bi-chat-left-text-fill fs-1 text-danger"></i>
                            <h5 class="mt-2">Messages</h5>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="manage_auto_reply.php" class="card-link">
                        <div class="p-4 bg-white border shadow-sm quick-box text-center">
                            <i class="bi bi-robot fs-1 text-dark"></i>
                            <h5 class="mt-2">Chatbot Auto-Replies</h5>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
