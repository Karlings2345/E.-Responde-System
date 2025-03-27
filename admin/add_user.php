<?php
require_once '../config/db.php';

if (isset($_POST['add_user'])) {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $purok = $_POST['purok'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Handle profile picture upload
    $profile = $_FILES['profile_picture'];
    $profile_name = time() . '_' . basename($profile['name']);
    $target_dir = "../uploads/";
    $target_file = $target_dir . $profile_name;

    if (move_uploaded_file($profile['tmp_name'], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO users 
            (first_name, middle_name, last_name, purok, phone, email, username, password, profile_picture, address)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Static address for now (you can change this to dynamic if needed)
        $address = 'Lingating, Baungon, Bukidnon';

        $stmt->execute([
            $first_name,
            $middle_name,
            $last_name,
            $purok,
            $phone,
            $email,
            $username,
            $password,
            $profile_name,
            $address
        ]);

        header("Location: manage_users.php");
        exit;
    } else {
        echo "❌ Error uploading profile picture.";
    }
}
?>
