<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $type = $_POST['type'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $lat = $_POST['latitude'];
    $lng = $_POST['longitude'];

    $uploads = [];
    for ($i = 1; $i <= 3; $i++) {
        if (!isset($_FILES["image{$i}"]) || $_FILES["image{$i}"]['error'] != 0) {
            $errors[] = "All 3 images are required.";
            break;
        } else {
            $filename = time() . "_img{$i}_" . basename($_FILES["image{$i}"]["name"]);
            $target_path = "../uploads/" . $filename;
            move_uploaded_file($_FILES["image{$i}"]["tmp_name"], $target_path);
            $uploads[] = $filename;
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO complaints (user_id, type, title, description, image1, image2, image3, latitude, longitude)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id, $type, $title, $description,
            $uploads[0], $uploads[1], $uploads[2],
            $lat, $lng
        ]);
        $success = "✅ Complaint submitted successfully!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>📢 File a Complaint</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <style>
        body {
            background: url('cover.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Segoe UI', sans-serif;
        }

        .container {
            max-width: 700px;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }

        .card-header {
            border-radius: 20px 20px 0 0;
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
            font-size: 20px;
        }

        .form-label {
            font-weight: 500;
        }

        .form-control, .form-select {
            border-radius: 10px;
        }

        #map {
            height: 300px;
            border-radius: 10px;
            margin-top: 10px;
        }

        .btn-primary {
            border-radius: 10px;
            font-weight: bold;
        }

        .btn-secondary {
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .alert {
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container my-5">
    <a href="dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>

    <div class="card shadow">
        <div class="card-header text-center">
            📢 File a Complaint
        </div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php elseif (isset($success)): ?>
                <div class="alert alert-success text-center"><?= $success ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="type" class="form-label">Type of Complaint:</label>
                    <select name="type" id="type" class="form-select" required>
                        <option value="">--Select--</option>
                        <option value="Road">Road Issue</option>
                        <option value="Garbage">Garbage Collection</option>
                        <option value="Water">Water Supply</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="title" class="form-label">Complaint Title:</label>
                    <input type="text" name="title" id="title" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description:</label>
                    <textarea name="description" id="description" rows="4" class="form-control" required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Upload 3 Images:</label>
                    <input type="file" name="image1" class="form-control mb-2" accept="image/*" required>
                    <input type="file" name="image2" class="form-control mb-2" accept="image/*" required>
                    <input type="file" name="image3" class="form-control" accept="image/*" required>
                </div>

                <input type="hidden" name="latitude" id="latitude" required>
                <input type="hidden" name="longitude" id="longitude" required>

                <div class="mb-3">
                    <label class="form-label">📍 Pin Complaint Location</label>
                    <div id="map"></div>
                </div>

                <button type="submit" class="btn btn-primary w-100">🚀 Submit Complaint</button>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    const baseOSM = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data © OpenStreetMap contributors'
    });

    const baseSatellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
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
        layers: [baseOSM]
    });

    L.control.layers({
        "🗺 Street View": baseOSM,
        "🌐 Satellite View": baseSatellite
    }).addTo(map);

    let marker;
    map.on('click', function (e) {
        const lat = e.latlng.lat.toFixed(8);
        const lng = e.latlng.lng.toFixed(8);

        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }
    });
</script>

</body>
</html>
