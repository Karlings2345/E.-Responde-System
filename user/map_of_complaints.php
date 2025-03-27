<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get complaints for the logged-in user
$stmt = $conn->prepare("SELECT * FROM complaints WHERE user_id = ? AND archived = 0");
$stmt->execute([$_SESSION['user_id']]);
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🗺 Your Complaint Map</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body {
            background-color: #f8f9fa;
        }

        #map {
            height: 600px;
            width: 100%;
            border-radius: 8px;
        }

        .map-wrapper {
            border: 1px solid #ced4da;
            border-radius: 10px;
            overflow: hidden;
        }
    </style>
</head>
<body>

<div class="container my-5">
    <a href="dashboard.php" class="btn btn-secondary mb-3">⬅ Back to Dashboard</a>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">📍 My Complaints on the Map</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Below is a map view of all your submitted complaints. Click markers to view details.</p>
            <div class="map-wrapper">
                <div id="map"></div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    // Base tile layers
    const street = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data © OpenStreetMap contributors'
    });

    const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles © Esri'
    });

    const bounds = L.latLngBounds(
        [8.3300, 124.6280],
        [8.3400, 124.6400]
    );

    const map = L.map('map', {
        center: [8.33544, 124.63408],
        zoom: 16,
        minZoom: 15,
        maxZoom: 18,
        maxBounds: bounds,
        maxBoundsViscosity: 1.0,
        layers: [street]
    });

    L.control.layers({
        "🗺 Street View": street,
        "🌐 Satellite View": satellite
    }).addTo(map);

    // Load complaints from PHP
    const complaints = <?= json_encode($complaints) ?>;

    complaints.forEach(c => {
        if (c.latitude && c.longitude) {
            const popup = `
                <strong>${c.title}</strong><br>
                <b>Type:</b> ${c.type}<br>
                <b>Status:</b> ${c.status}<br>
                <b>Description:</b><br> ${c.description}
            `;
            L.marker([c.latitude, c.longitude])
                .addTo(map)
                .bindPopup(popup);
        }
    });
</script>

</body>
</html>
