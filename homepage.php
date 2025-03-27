<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>E-Responde | Welcome</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            height: 100vh;
            background: url('./homepage assets/cover.png') no-repeat center center/cover;
            position: relative;
            color: #000;
        }

        .top-logo {
    position: absolute;
    top: 20px;
    left: 20px;
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%; /* makes it a perfect circle */
    border: 3px solid white; /* optional: clean white border */
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); /* optional: subtle shadow */
}


        .content-wrapper {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
        }

        .system-logo {
            width: 200px;
            margin: 20px 0;
        }

        h1 {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 20px;
        }

        p {
            max-width: 700px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.95rem;
            color: #111;
        }

        .btn-group {
            margin-top: 30px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-register {
            background-color: #28c445;
            color: white;
            padding: 12px 30px;
            font-weight: bold;
            border-radius: 30px;
            border: none;
            transition: background 0.3s;
        }

        .btn-register:hover {
            background-color: #1da233;
        }

        .btn-login {
            border: 2px solid #000;
            background: transparent;
            padding: 12px 30px;
            font-weight: bold;
            border-radius: 30px;
            color: #000;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background-color: #000;
            color: white;
        }

        @media (max-width: 576px) {
            .system-logo {
                width: 150px;
            }

            h1 {
                font-size: 1.4rem;
            }

            p {
                font-size: 0.85rem;
            }

            .btn-group {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>
</head>
<body>

    <!-- LGU Logo (top-left corner) -->
    <img src="./homepage assets/logo.png" alt="LGU Logo" class="top-logo">

    <!-- Centered Content -->
    <div class="content-wrapper">
        <h1>E • RESPONDE COMPLAINT SYSTEM</h1>

        <img src="./homepage assets/systemlogo.png" alt="System Logo" class="system-logo">

        <p>
            A FRONTLINE GOVERNMENT PLATFORM ENSURING FAST, EFFICIENT, AND TRANSPARENT PUBLIC SERVICE IN ADDRESSING COMMUNITY CONCERNS, ENHANCING EMERGENCY RESPONSE, AND FOSTERING A MORE RESPONSIVE LOCAL GOVERNANCE
        </p>

        <div class="btn-group">
            <a href="user/register.php" class="btn-register">REGISTER NOW</a>
            <a href="user/login.php" class="btn-login">LOG IN</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php if (isset($_GET['deleted'])): ?>
    <script>
        alert("Your account has been deleted by the administrator.");
    </script>
    <?php endif; ?>
</body>
</html>
