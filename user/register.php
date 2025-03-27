<?php
session_start();
require_once '../config/db.php';

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name   = $_POST['first_name'];
    $middle_name  = $_POST['middle_name'];
    $last_name    = $_POST['last_name'];
    $phone        = $_POST['phone'];
    $email        = $_POST['email'];
    $purok        = $_POST['purok'];
    $address      = $_POST['address'];
    $username     = $_POST['username'];
    $password     = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $profile_picture = "";
    if ($_FILES["profile_picture"]["name"]) {
        $fileName = uniqid() . "_" . basename($_FILES["profile_picture"]["name"]);
        $targetFilePath = "uploads/" . $fileName;

        if (!is_dir("uploads")) {
            mkdir("uploads", 0777, true);
        }

        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)) {
            $profile_picture = $fileName;
        } else {
            $error = "Failed to upload profile picture.";
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO users 
            (first_name, middle_name, last_name, phone, email, purok, address, username, password, profile_picture) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$first_name, $middle_name, $last_name, $phone, $email, $purok, $address, $username, $password, $profile_picture]);
        $success = "Registration successful! You can now log in.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Registration - E-Responde</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/user_style.css">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body, html {
            height: 100%;
            background: linear-gradient(135deg, #e0f2ff, #c1e0f9);
        }

        .login-container {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 0;
        }

        .login-card {
            width: 90%;
            max-width: 1000px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            background: #fff;
            display: flex;
            flex-direction: row;
        }

        .login-left {
            flex: 1;
            background: url('../homepage assets/cover.png') no-repeat center center/cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-left img {
            width: 70%;
            max-width: 300px;
        }

        .login-right {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .logo-top {
            position: absolute;
            top: 20px;
            left: 20px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            overflow: hidden;
        }

        .form-label {
            font-weight: 500;
        }
    </style>
</head>
<body>

    <img src="../homepage assets/logo.png" alt="E-Responde Logo" class="logo-top">

    <div class="container login-container">
        <div class="login-card">
            <div class="login-left">
                <img src="../homepage assets/systemlogo.png" alt="System Logo">
            </div>

            <div class="login-right">
                <h3 class="mb-4 fw-bold text-success">User Registration</h3>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Purok</label>
                            <input type="text" name="purok" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Profile Picture</label>
                            <input type="file" name="profile_picture" class="form-control" accept="image/*" required>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-person-plus"></i> Register
                        </button>
                    </div>
                </form>

                <p class="mt-3 text-center">
                    Already have an account? <a href="login.php">Login here</a>.
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
