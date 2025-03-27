<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT c.*, u.first_name, u.middle_name, u.last_name, u.purok 
    FROM complaints c 
    JOIN users u ON c.user_id = u.id
    WHERE c.archived = 1
    ORDER BY c.created_at DESC
");

$stmt->execute();
$archived = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Archived Complaints</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        h2 {
            margin-top: 0;
        }
        a {
            text-decoration: none;
            color: #3498db;
            margin-bottom: 10px;
            display: inline-block;
        }
        table {
            background: white;
            border-collapse: collapse;
            width: 100%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        .status {
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            font-size: 0.9em;
            display: inline-block;
        }
        .Pending { background-color: #f39c12; }
        .Complaint_Received { background-color: #2980b9; }
        .Fixing_the_Issue { background-color: #e67e22; }
        .Done { background-color: #27ae60; }
    </style>
</head>
<body>
    <h2>🗃 Archived Complaints</h2>
    <a href="dashboard.php">⬅ Back to Dashboard</a><br><br>

    <?php if (empty($archived)): ?>
        <p>No archived complaints.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>User</th>
                <th>Title</th>
                <th>Status</th>
                <th>Date Filed</th>
            </tr>
            <?php foreach ($archived as $row): ?>
                <?php
                    $statusClass = str_replace(' ', '_', $row['status']); // convert to valid class name
                ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']) ?><br>
                        Purok <?= htmlspecialchars($row['purok']) ?>
                    </td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><span class="status <?= $statusClass ?>"><?= htmlspecialchars($row['status']) ?></span></td>
                    <td><?= htmlspecialchars($row['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</body>
</html>
