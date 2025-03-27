<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complaint_id'], $_POST['status'])) {
    $complaint_id = $_POST['complaint_id'];
    $status = $_POST['status'];

    // Get complaint info with user email and name
    $stmt = $conn->prepare("
        SELECT complaints.title, users.email, users.first_name, users.last_name
        FROM complaints 
        JOIN users ON complaints.user_id = users.id 
        WHERE complaints.id = ?
    ");
    $stmt->execute([$complaint_id]);
    $complaint = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($complaint) {
        // Update the complaint status
        $updateStmt = $conn->prepare("UPDATE complaints SET status = ? WHERE id = ?");
        $updateStmt->execute([$status, $complaint_id]);

        // Compose the full name
        $fullName = $complaint['first_name'] . ' ' . $complaint['last_name'];

        // Email notification
        $to = $complaint['email'];
        $subject = "📢 E-Responde: Complaint Status Updated";
        $message = "Hi $fullName,\n\n"
                 . "Your complaint titled \"" . $complaint['title'] . "\" has been updated to the status: \"" . $status . "\".\n\n"
                 . "You can log in to the E-Responde system to view the details.\n\n"
                 . "Thank you,\nE-Responde Team";

        $headers = "From: no-reply@e-responde.local";

        // Send email
        mail($to, $subject, $message, $headers);
    }

    header("Location: filed_complaints.php");
    exit;
}
?>
