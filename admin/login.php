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
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid login credentials.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - E-Responde</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Style -->
    <style>
    body {
        background: url('pl.jpg') no-repeat center center fixed, 
                    linear-gradient(135deg, #28a745, #218838);
        background-size: cover;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        font-family: 'Segoe UI', sans-serif;
    }

    .login-card {
        background: rgba(255, 255, 255, 0.8); /* Slight transparency to let the background image show through */
        border-radius: 20px;
        box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        padding: 40px 30px;
        width: 100%;
        max-width: 400px;
        animation: fadeIn 0.5s ease-in-out;
    }

    .login-card h2 {
        font-weight: 700;
        color: #28a745;
    }

    .form-control:focus {
        box-shadow: 0 0 5px rgba(40,167,69,0.8);
        border-color: #28a745;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .btn-success {
        transition: 0.3s ease;
    }

    .btn-success:hover {
        background-color: #1e7e34;
    }
</style>

</head>
<body>

    <div class="login-card">
        <h2 class="text-center mb-4">Admin Login</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" name="password" class="form-control" id="password" required>
                    <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                        <i class="bi bi-eye-slash" id="eyeIcon"></i> <!-- Eye icon for password visibility toggle -->
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100">Login</button>
        </form>

        <p class="text-center mt-3">
            Don’t have an admin account? <a href="register.php">Register here</a>.
        </p>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Get the password input and the eye icon
        const password = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const eyeIcon = document.getElementById('eyeIcon');

        // Toggle password visibility
        togglePassword.addEventListener('click', function() {
            if (password.type === 'password') {
                password.type = 'text';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            } else {
                password.type = 'password';
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            }
        });
    </script>

</body>
</html>
