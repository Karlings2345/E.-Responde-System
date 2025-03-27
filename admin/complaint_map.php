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
    WHERE c.archived = 0
");
$stmt->execute();
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Complaint Map - Admin</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            background-color: #f4f6f9;
            color: #333;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            margin-top: 0;
            font-size: 26px;
            color: #2c3e50;
        }

        h3 {
            margin-bottom: 20px;
            color: #666;
        }

        a.back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 14px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s;
        }

        a.back-btn:hover {
            background-color: #2980b9;
        }

        #map {
            height: 600px;
            width: 100%;
            border-radius: 10px;
            border: 2px solid #ccc;
        }

        .legend {
            background: white;
            padding: 12px 16px;
            line-height: 20px;
            color: #333;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            font-size: 14px;
        }

        .legend i {
            width: 14px;
            height: 14px;
            float: left;
            margin-right: 8px;
            margin-top: 3px;
            opacity: 0.9;
            border-radius: 50%;
        }

        @media (max-width: 768px) {
            .container {
                margin: 10px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🗺 Complaint Map</h2>
    <a href="dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
    <h3>Total Complaints: <?= count($complaints) ?></h3>

    <div id="map"></div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
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

    const complaints = <?= json_encode($complaints) ?>;

    const statusColors = {
        "Pending": "red",
        "Complaint Received": "orange",
        "Fixing the Issue": "blue",
        "Done": "green"
    };

    complaints.forEach(complaint => {
        if (complaint.latitude && complaint.longitude) {
            const fullName = `${complaint.first_name} ${complaint.middle_name} ${complaint.last_name}`;
            const popup = `
                <strong>${complaint.title}</strong><br>
                Type: ${complaint.type}<br>
                Status: ${complaint.status}<br>
                Filed by: ${fullName}<br>
                Purok: ${complaint.purok}<br>
                <a href="filed_complaints.php">View Details</a>
            `;
            const marker = L.circleMarker([complaint.latitude, complaint.longitude], {
                radius: 8,
                color: statusColors[complaint.status] || 'gray',
                fillColor: statusColors[complaint.status] || 'gray',
                fillOpacity: 0.85
            });
            marker.bindPopup(popup).addTo(map);
        }
    });

    // Legend
    const legend = L.control({ position: 'bottomright' });

    legend.onAdd = function (map) {
        const div = L.DomUtil.create('div', 'legend');
        const statuses = {
            "Pending": "red",
            "Complaint Received": "orange",
            "Fixing the Issue": "blue",
            "Done": "green"
        };

        div.innerHTML = '<strong>Status Legend</strong><br>';
        for (const status in statuses) {
            div.innerHTML += `<i style="background:${statuses[status]}"></i> ${status}<br>`;
        }
        return div;
    };

    legend.addTo(map);
</script>

</body>
</html>
