<?php
require_once '../config/db.php';

$errors = [];

// Allowed Barangay IDs
$valid_ids = ["BRGY-001", "BRGY-002", "BRGY-003", "BRGY-004", "BRGY-005"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $phone = trim($_POST['phone']);
    $barangay_id = trim($_POST['barangay_id']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($full_name) || empty($email) || empty($address) || empty($phone) || empty($barangay_id) || empty($password) || empty($confirm_password)) {
        $errors[] = "All fields are required.";
    }

    if (!in_array($barangay_id, $valid_ids)) {
        $errors[] = "Invalid Barangay ID.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        // Save to database
        $username = "admin";  // Default username
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert admin details into the database
        $stmt = $conn->prepare("INSERT INTO admins (full_name, email, address, phone, barangay_id, username, password)
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([ $full_name, $email, $address, $phone, $barangay_id, $username, $hashed_password ]);

        // Redirect to login page after successful registration
        header("Location: login.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #28a745, #6dbf6d); /* Green gradient background */
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 28px;
        }

        .errors {
            color: #ff0000;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .errors li {
            margin-bottom: 10px;
        }

        label {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            display: block;
        }

        input, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: all 0.3s ease-in-out;
        }

        input:focus, select:focus {
            border-color: #28a745; /* Green focus border */
            outline: none;
        }

        button {
            padding: 12px;
            font-size: 16px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }

        button:hover {
            background-color: #218838; /* Darker green on hover */
        }

        p {
            text-align: center;
            margin-top: 20px;
        }

        a {
            color: #28a745; /* Green link */
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .input-group {
            display: flex;
            align-items: center;
        }

        .input-group-text {
            cursor: pointer;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin: 10px;
            }

            h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Admin Registration</h2>

    <?php if (!empty($errors)): ?>
        <ul class="errors">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post">
        <label for="full_name">Full Name</label>
        <input type="text" id="full_name" name="full_name" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="address">Address</label>
        <input type="text" id="address" name="address" required>

        <label for="phone">Phone</label>
        <input type="text" id="phone" name="phone" required>

        <label for="barangay_id">Barangay ID</label>
        <select id="barangay_id" name="barangay_id" required>
            <option value="">-- Select Barangay ID --</option>
            <?php foreach ($valid_ids as $id): ?>
                <option value="<?= $id ?>"><?= $id ?></option>
            <?php endforeach; ?>
        </select>

        <label for="password">Password</label>
        <div class="input-group">
            <input type="password" name="password" id="password" required class="form-control">
            <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                <i class="bi bi-eye-slash" id="eyeIcon"></i>
            </span>
        </div>

        <label for="confirm_password">Confirm Password</label>
        <div class="input-group">
            <input type="password" name="confirm_password" id="confirm_password" required class="form-control">
            <span class="input-group-text" id="toggleConfirmPassword" style="cursor: pointer;">
                <i class="bi bi-eye-slash" id="confirmEyeIcon"></i>
            </span>
        </div>

        <button type="submit">Register</button>
    </form>

    <p>Already registered? <a href="login.php">Login here</a>.</p>
</div>

<!-- Bootstrap and Icons -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>

<script>
    // Password visibility toggle
    const password = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const eyeIcon = document.getElementById('eyeIcon');
    const confirmPassword = document.getElementById('confirm_password');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const confirmEyeIcon = document.getElementById('confirmEyeIcon');

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

    toggleConfirmPassword.addEventListener('click', function() {
        if (confirmPassword.type === 'password') {
            confirmPassword.type = 'text';
            confirmEyeIcon.classList.remove('bi-eye-slash');
            confirmEyeIcon.classList.add('bi-eye');
        } else {
            confirmPassword.type = 'password';
            confirmEyeIcon.classList.remove('bi-eye');
            confirmEyeIcon.classList.add('bi-eye-slash');
        }
    });
</script>

</body>
</html>
