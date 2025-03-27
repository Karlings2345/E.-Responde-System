<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid access.";
    header("Location: filed_complaints.php");
    exit;
}

$id = $_GET['id'];

// Check if ID is numeric to prevent SQL injection bypass
if (!is_numeric($id)) {
    $_SESSION['error'] = "Invalid complaint ID.";
    header("Location: filed_complaints.php");
    exit;
}

// Optional: check if complaint exists
$stmt = $conn->prepare("SELECT * FROM complaints WHERE id = ? AND archived = 0");
$stmt->execute([$id]);
$complaint = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$complaint) {
    $_SESSION['error'] = "Complaint not found or already archived.";
    header("Location: filed_complaints.php");
    exit;
}

// Archive the complaint
$update = $conn->prepare("UPDATE complaints SET archived = 1 WHERE id = ?");
$update->execute([$id]);

$_SESSION['success'] = "Complaint archived successfully.";
header("Location: filed_complaints.php");
exit;
?>
