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

// Fetch complaint to get image filenames
$stmt = $conn->prepare("SELECT * FROM complaints WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$complaint = $stmt->fetch();

if ($complaint) {
    // Delete images from server
    for ($i = 1; $i <= 3; $i++) {
        $img = $complaint["image{$i}"];
        $path = "../uploads/" . $img;
        if ($img && file_exists($path)) {
            unlink($path);
        }
    }

    // Delete complaint from DB
    $stmt = $conn->prepare("DELETE FROM complaints WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $_SESSION['user_id']]);
}

header("Location: complaint_tracker.php");
exit;
