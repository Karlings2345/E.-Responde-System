<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // First, delete dependent complaints related to the user
    $stmt = $conn->prepare("DELETE FROM complaints WHERE user_id = ?");
    $stmt->execute([$id]);

    // Then, delete dependent messages related to the user
    $stmt = $conn->prepare("DELETE FROM messages WHERE user_id = ?");
    $stmt->execute([$id]);

    // Finally, delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: manage_users.php");
    exit;
}

// Fetch all users
$stmt = $conn->prepare("SELECT * FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 30px;
        }

        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .profile-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="dashboard.php" class="btn btn-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-person-plus-fill"></i> Add New User</h4>
        </div>
        <div class="card-body">
            <form action="add_user.php" method="post" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="middle_name" class="form-control" placeholder="Middle Name" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
                </div>

                <div class="col-md-3">
                    <input type="text" name="purok" class="form-control" placeholder="Purok (1-10)" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
                </div>
                <div class="col-md-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                </div>
                <div class="col-md-6">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <div class="col-md-6">
                    <input type="file" name="profile_picture" class="form-control" accept="image/*" required>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" name="add_user" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Add User
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0"><i class="bi bi-people-fill"></i> Registered Users</h4>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered align-middle table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Username</th>
                        <th>Profile</th>
                        <th style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars("{$user['first_name']} {$user['middle_name']} {$user['last_name']}") ?></td>
                        <td>Purok <?= htmlspecialchars($user['purok']) ?>, <?= htmlspecialchars($user['address'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($user['phone']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><img src="../user/uploads/<?= htmlspecialchars($user['profile_picture']) ?>" class="profile-img"></td>
                        <td>
                            <a href="?delete=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">
                                <i class="bi bi-trash3"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
