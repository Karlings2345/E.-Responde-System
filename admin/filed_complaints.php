<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch non-archived complaints
$stmt = $conn->prepare("
    SELECT c.*, u.first_name, u.middle_name, u.last_name, u.purok 
    FROM complaints c 
    JOIN users u ON c.user_id = u.id
    WHERE c.archived = 0
    ORDER BY c.created_at DESC
");
$stmt->execute();
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Filed Complaints - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #mapModal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1050;
        }

        #mapContainer {
            width: 80%;
            height: 80%;
            background: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
        }

        #map {
            height: 400px;
        }

        img.preview-img {
            width: 60px;
            height: auto;
            margin: 2px;
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-4">
    <a href="dashboard.php" class="btn btn-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>

    <h3 class="mb-4 text-primary"><i class="bi bi-clipboard-data"></i> Filed Complaints</h3>

    <?php if (empty($complaints)): ?>
        <div class="alert alert-info">No complaints have been filed yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>User</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Images</th>
                        <th>Date Filed</th>
                        <th>Map</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($complaints as $row): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']) ?><br>
                            <small class="text-muted">Purok <?= htmlspecialchars($row['purok']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['type']) ?></td>
                        <td>
                            <form action="edit_status.php" method="post">
                                <input type="hidden" name="complaint_id" value="<?= $row['id'] ?>">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="Pending" <?= $row['status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Complaint Received" <?= $row['status'] == 'Complaint Received' ? 'selected' : '' ?>>Complaint Received</option>
                                    <option value="Fixing the Issue" <?= $row['status'] == 'Fixing the Issue' ? 'selected' : '' ?>>Fixing the Issue</option>
                                    <option value="Done" <?= $row['status'] == 'Done' ? 'selected' : '' ?>>Done</option>
                                </select>
                            </form>
                            <?php if ($row['status'] == 'Done'): ?>
                                <a href="archive_complaint.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary mt-2">
                                    <i class="bi bi-archive"></i> Archive
                                </a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small>Lat: <?= htmlspecialchars($row['latitude']) ?><br>
                            Lng: <?= htmlspecialchars($row['longitude']) ?></small>
                        </td>
                        <td>
                            <img src="../uploads/<?= $row['image1'] ?>" class="preview-img">
                            <img src="../uploads/<?= $row['image2'] ?>" class="preview-img">
                            <img src="../uploads/<?= $row['image3'] ?>" class="preview-img">
                        </td>
                        <td><?= date("F j, Y h:i A", strtotime($row['created_at'])) ?></td>
                        <td>
                            <a href="#" class="btn btn-sm btn-outline-primary" onclick="viewMap(<?= $row['latitude'] ?>, <?= $row['longitude'] ?>)">
                                <i class="bi bi-geo-alt-fill"></i> View
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Map Modal -->
<div id="mapModal">
    <div id="mapContainer">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="bi bi-map-fill"></i> Complaint Location</h5>
            <button class="btn btn-sm btn-danger" onclick="closeMap()">Close</button>
        </div>
        <div id="map"></div>
    </div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    let map;

    function viewMap(lat, lng) {
        document.getElementById("mapModal").style.display = "block";

        if (map) map.remove();

        map = L.map('map').setView([lat, lng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        L.marker([lat, lng]).addTo(map).bindPopup("Complaint Location").openPopup();
    }

    function closeMap() {
        document.getElementById("mapModal").style.display = "none";
    }
</script>

</body>
</html>

