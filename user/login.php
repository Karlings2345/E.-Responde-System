<?php
session_start();
require_once '../config/db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Login - E-Responde</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom User CSS -->
    <link rel="stylesheet" href="assets/css/user_style.css">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body, html {
            height: 100%;
            background: linear-gradient(135deg, #e0f2ff, #c1e0f9);
        }

        .login-container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
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
            padding: 50px;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }

        .position-relative {
            position: relative;
        }

        .logo-top {
    position: absolute;
    top: 20px;
    left: 20px;
    width: 120px;
    height: 120px; /* Make it equal to width */
    border-radius: 50%;
    object-fit: cover;
    overflow: hidden;
}

    </style>
</head>
<body>

    <!-- Top Left Logo -->
    <img src="../homepage assets/logo.png" alt="E-Responde Logo" class="logo-top">

    <div class="container login-container">
        <div class="login-card">
            <!-- Left: Big Logo / Image -->
            <div class="login-left">
                <img src="../homepage assets/systemlogo.png" alt="System Logo">
            </div>

            <!-- Right: Login Form -->
            <div class="login-right">
                <h3 class="mb-4 fw-bold text-primary">User Login</h3>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>

                    <div class="mb-3 position-relative">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" id="passwordField" required>
                        <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </button>
                    </div>
                </form>

                <p class="mt-3 text-center">
                    Don't have an account? <a href="register.php">Register here</a>.
                </p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Toggle Password Visibility -->
    <script>
        const togglePassword = document.getElementById("togglePassword");
        const passwordField = document.getElementById("passwordField");

        togglePassword.addEventListener("click", () => {
            const type = passwordField.getAttribute("type") === "password" ? "text" : "password";
            passwordField.setAttribute("type", type);
            togglePassword.classList.toggle("bi-eye");
            togglePassword.classList.toggle("bi-eye-slash");
        });
    </script>
</body>
</html>
