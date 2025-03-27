<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$statusFilter = $_GET['status'] ?? '';
$typeFilter = $_GET['type'] ?? '';

$query = "SELECT * FROM complaints WHERE user_id = ?";
$params = [$user_id];

if ($statusFilter) {
    $query .= " AND status = ?";
    $params[] = $statusFilter;
}
if ($typeFilter) {
    $query .= " AND type = ?";
    $params[] = $typeFilter;
}

$query .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all distinct statuses and types for filter dropdowns
$statusOptions = $conn->query("SELECT DISTINCT status FROM complaints")->fetchAll(PDO::FETCH_COLUMN);
$typeOptions = $conn->query("SELECT DISTINCT type FROM complaints")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Complaints - Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet/dist/leaflet.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css" rel="stylesheet">

    <style>
        .preview-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            margin: 2px;
            cursor: pointer;
        }

        .leaflet-container {
            height: 400px;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 10px;
            font-weight: bold;
        }

        .status-Pending {
            background-color: #ffc107; color: black;
        }
        .status-Complaint\ Received {
            background-color: #0dcaf0; color: black;
        }
        .status-Fixing\ the\ Issue {
            background-color: #fd7e14; color: white;
        }
        .status-Done {
            background-color: #198754; color: white;
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="mb-4">📌 My Complaint Tracker</h2>
    <a href="dashboard.php" class="btn btn-secondary mb-4">⬅ Back to Dashboard</a>

    <!-- Filters -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <label>Status Filter</label>
            <select name="status" class="form-select">
                <option value="">All</option>
                <?php foreach ($statusOptions as $status): ?>
                    <option value="<?= htmlspecialchars($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>>
                        <?= htmlspecialchars($status) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label>Type Filter</label>
            <select name="type" class="form-select">
                <option value="">All</option>
                <?php foreach ($typeOptions as $type): ?>
                    <option value="<?= htmlspecialchars($type) ?>" <?= $typeFilter === $type ? 'selected' : '' ?>>
                        <?= htmlspecialchars($type) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button class="btn btn-primary">🔍 Filter</button>
        </div>
    </form>

    <?php if (empty($complaints)): ?>
        <div class="alert alert-info">You haven't filed any complaints yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Images</th>
                        <th>Filed On</th>
                        <th>Map</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($complaints as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['title']) ?></td>
                        <td><?= htmlspecialchars($c['type']) ?></td>
                        <td>
                            <span class="status-badge status-<?= str_replace(' ', '\\ ', $c['status']) ?>">
                                <?= htmlspecialchars($c['status']) ?>
                            </span>
                        </td>
                        <td>
                            Lat: <?= $c['latitude'] ?><br>
                            Lng: <?= $c['longitude'] ?>
                        </td>
                        <td>
                            <?php for ($i = 1; $i <= 3; $i++): ?>
                                <?php if (!empty($c["image$i"])): ?>
                                    <a href="../uploads/<?= $c["image$i"] ?>" class="glightbox" data-gallery="group<?= $c['id'] ?>">
                                        <img src="../uploads/<?= $c["image$i"] ?>" class="preview-img">
                                    </a>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </td>
                        <td><?= date('F j, Y - g:i A', strtotime($c['created_at'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="viewMap(<?= $c['latitude'] ?>, <?= $c['longitude'] ?>)">📍 View</button>
                        </td>
                        <td>
                            <a href="edit_complaint.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-warning mb-1">✏️ Edit</a>
                            <a href="delete_complaint.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Are you sure you want to delete this complaint?');">🗑 Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Map Modal -->
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Complaint Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="map"></div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>

<script>
    let map;
    function viewMap(lat, lng) {
        const modal = new bootstrap.Modal(document.getElementById('mapModal'));
        modal.show();

        setTimeout(() => {
            if (map) map.remove();
            map = L.map('map').setView([lat, lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            L.marker([lat, lng]).addTo(map).bindPopup("Complaint Location").openPopup();
        }, 300);
    }

    const lightbox = GLightbox({ selector: '.glightbox' });
</script>
</body>
</html> 