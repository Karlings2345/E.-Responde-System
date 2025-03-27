<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: complaint_tracker.php");
    exit;
}

// Fetch the complaint
$stmt = $conn->prepare("SELECT * FROM complaints WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$complaint = $stmt->fetch();

if (!$complaint) {
    echo "Invalid complaint or unauthorized access.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars(trim($_POST['title']));
    $description = htmlspecialchars(trim($_POST['description']));

    $img1 = $complaint['image1'];
    $img2 = $complaint['image2'];
    $img3 = $complaint['image3'];

    // Replace images if new ones are uploaded
    for ($i = 1; $i <= 3; $i++) {
        $field = "image{$i}";
        if ($_FILES[$field]['name']) {
            $filename = time() . "_updated_img{$i}_" . basename($_FILES[$field]['name']);
            $target_path = "../uploads/" . $filename;
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $target_path)) {
                // Optional: delete old image
                if (file_exists("../uploads/" . $complaint[$field])) {
                    unlink("../uploads/" . $complaint[$field]);
                }
                ${"img$i"} = $filename;
            }
        }
    }

    $stmt = $conn->prepare("
        UPDATE complaints 
        SET title = ?, description = ?, image1 = ?, image2 = ?, image3 = ? 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$title, $description, $img1, $img2, $img3, $id, $_SESSION['user_id']]);

    header("Location: complaint_tracker.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Complaint</title>
    <!-- Link to a clean Google font for modern typography -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="user_style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fc;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: auto;
            padding-top: 30px;
        }

        h2 {
            font-size: 2.2rem;
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .btn-back {
            font-size: 1rem;
            color: #007bff;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            transition: color 0.3s ease;
        }

        .btn-back:hover {
            color: #0056b3;
        }

        .card {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: 600;
            color: #555;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"], textarea, input[type="file"] {
            width: 100%;
            padding: 12px;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ccc;
            margin-top: 5px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus, textarea:focus, input[type="file"]:focus {
            border-color: #007bff;
            outline: none;
        }

        textarea {
            resize: vertical;
        }

        .image-preview {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .image-preview img {
            width: 120px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
            border: 2px solid #ddd;
        }

        .file-input-label {
            font-size: 1rem;
            font-weight: 600;
            color: #555;
            margin-top: 20px;
        }

        button[type="submit"] {
            background-color: #28a745;
            color: #fff;
            padding: 12px 20px;
            font-size: 1.1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #218838;
        }

        .footer {
            text-align: center;
            color: #aaa;
            font-size: 0.9rem;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="complaint_tracker.php" class="btn-back">⬅ Back to Tracker</a>

        <div class="card">
            <h2>Edit Your Complaint</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Title:</label>
                    <input type="text" id="title" name="title" value="<?= htmlspecialchars($complaint['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($complaint['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Current Images:</label>
                    <div class="image-preview">
                        <img src="../uploads/<?= $complaint['image1'] ?>" alt="Image 1">
                        <img src="../uploads/<?= $complaint['image2'] ?>" alt="Image 2">
                        <img src="../uploads/<?= $complaint['image3'] ?>" alt="Image 3">
                    </div>
                </div>

                <div class="form-group">
                    <div class="file-input-label">Replace Image 1:</div>
                    <input type="file" name="image1" accept="image/*">
                </div>

                <div class="form-group">
                    <div class="file-input-label">Replace Image 2:</div>
                    <input type="file" name="image2" accept="image/*">
                </div>

                <div class="form-group">
                    <div class="file-input-label">Replace Image 3:</div>
                    <input type="file" name="image3" accept="image/*">
                </div>

                <div class="form-group">
                    <button type="submit">Update Complaint</button>
                </div>
            </form>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2025 Your Company | All Rights Reserved</p>
    </div>
</body>
</html>
