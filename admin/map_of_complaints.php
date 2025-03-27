<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Get all non-archived complaints
$stmt = $conn->prepare("
    SELECT c.*, u.firstname, u.lastname, u.purok 
    FROM complaints c
    JOIN users u ON c.user_id = u.id
    WHERE c.archived = 0
");
$stmt->execute();
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complaint Map View</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        #map { height: 600px; width: 100%; }
    </style>
</head>
<body>
    <h2>🗺️ Map of All Complaints</h2>
    <a href="dashboard.php">⬅ Back to Dashboard</a><br><br>
    <div id="map"></div>

    <script>
    const baseOSM = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data © OpenStreetMap contributors'
    });

    const baseSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles © Esri'
    });

    const bounds = L.latLngBounds(
        [8.2856, 124.7252],
        [8.3056, 124.7452]
    );

    const map = L.map('map', {
        center: [8.2956, 124.7352],
        zoom: 16,
        minZoom: 15,
        maxZoom: 18,
        maxBounds: bounds,
        maxBoundsViscosity: 1.0,
        layers: [baseOSM]
    });

    const baseMaps = {
        "🗺 Street View": baseOSM,
        "🌐 Satellite View": baseSatellite
    };

    L.control.layers(baseMaps).addTo(map);

    const complaints = <?= json_encode($complaints) ?>;

    complaints.forEach(complaint => {
        if (complaint.latitude && complaint.longitude) {
            const fullName = `${complaint.first_name} ${complaint.middle_name} ${complaint.last_name}`;
            const popupContent = `
                <strong>${complaint.title}</strong><br>
                Type: ${complaint.type}<br>
                Status: ${complaint.status}<br>
                Filed by: ${fullName}<br>
                Purok: ${complaint.purok}<br>
                <a href="filed_complaints.php">View Details</a>
            `;
            L.marker([complaint.latitude, complaint.longitude])
                .addTo(map)
                .bindPopup(popupContent);
        }
    });
</script>

</body>
</html>
