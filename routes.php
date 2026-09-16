<?php

// BookMyBus LK – Sri Lanka Intercity Bus Routes (routes.php)
// Features Interactive Leaflet OpenStreetMap Route Visualization

require_once 'includes/auth.php';

$origin_filter = trim($_GET['origin'] ?? '');
$dest_filter = trim($_GET['destination'] ?? '');

$sql = "SELECT r.*, bu.bus_name, bu.bus_number, bu.bus_type, bu.operator, bu.ac_type, bu.wifi, bu.usb_charging,
               (SELECT COUNT(*) FROM seats s WHERE s.bus_id = bu.id AND s.status = 'available') AS available_seats
        FROM routes r
        JOIN buses bu ON r.bus_id = bu.id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($origin_filter)) {
    $sql .= " AND r.origin = ?";
    $params[] = $origin_filter;
    $types .= 's';
}
if (!empty($dest_filter)) {
    $sql .= " AND r.destination = ?";
    $params[] = $dest_filter;
    $types .= 's';
}

$sql .= " ORDER BY r.origin ASC, r.departure_time ASC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$routes = [];
while ($row = mysqli_fetch_assoc($res)) {
    $routes[] = $row;
}
mysqli_stmt_close($stmt);

// City list for filters
$cities = ['Colombo', 'Kandy', 'Galle', 'Negombo', 'Matara', 'Kurunegala', 'Dambulla', 'Anuradhapura', 'Jaffna', 'Nuwara Eliya', 'Ella', 'Ratnapura'];

$page_title = "Bus Routes & Interactive Map";
$active_page = "routes";
include 'includes/header.php';
?>

<div class="container my-5">

    <!-- Page Title & Intro -->
    <div class="p-4 mb-4 bg-white rounded-3 shadow-sm border-start border-4 border-warning">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="section-label mb-2">‍    </span>
                <h2 class="fw-bold text-dark mb-1">Sri Lanka Intercity Bus Routes & Live Map</h2>
                <p class="text-muted mb-0">Browse major expressway and highway routes connecting Western, Central, Southern, and Northern provinces.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <button type="button" class="btn btn-qs-primary fw-bold shadow-sm" onclick="scrollToMap()">
                    <i class="bi bi-map-fill me-1"></i> View Interactive Route Map
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card qs-card mb-5">
        <div class="card-body p-3">
            <form method="GET" action="routes.php" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-geo-alt-fill text-danger"></i></span>
                        <select name="origin" class="form-select">
                            <option value="">-- All Starting Origins --</option>
                            <?php foreach ($cities as $c): ?>
                                <option value="<?php echo e($c); ?>" <?php echo $origin_filter === $c ? 'selected' : ''; ?>><?php echo e($c); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-pin-map-fill text-primary"></i></span>
                        <select name="destination" class="form-select">
                            <option value="">-- All Destinations --</option>
                            <?php foreach ($cities as $c): ?>
                                <option value="<?php echo e($c); ?>" <?php echo $dest_filter === $c ? 'selected' : ''; ?>><?php echo e($c); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-qs-primary w-100 fw-bold">Filter Routes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Routes Grid -->
    <div class="row g-4 mb-5">
        <?php if (empty($routes)): ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-compass fs-1 text-muted d-block mb-3"></i>
                <h5>No routes found matching your filter.</h5>
                <a href="routes.php" class="btn btn-outline-primary btn-sm mt-2">Reset Filters</a>
            </div>
        <?php else: ?>
            <?php foreach ($routes as $r): ?>
                <div class="col-lg-6">
                    <div class="card qs-card h-100">
                        <div class="card-header qs-card-header py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-warning text-dark me-2"><?php echo e($r['route_number']); ?></span>
                                <strong class="fs-5"><?php echo e($r['origin']); ?> &rarr; <?php echo e($r['destination']); ?></strong>
                            </div>
                            <span class="badge bg-success"><?php echo (int)$r['available_seats']; ?> Seats Available</span>
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bus-front text-primary me-1"></i> <?php echo e($r['bus_name']); ?></h6>
                                    <small class="text-muted"><?php echo e($r['operator']); ?> &bull; <?php echo e($r['bus_number']); ?> &bull; <span class="badge bg-light text-dark border"><?php echo e($r['bus_type']); ?></span></small>
                                </div>
                                <div class="text-end">
                                    <span class="text-muted small d-block">One-way Fare:</span>
                                    <span class="fw-bold text-success fs-5"><?php echo format_lkr($r['fare']); ?></span>
                                </div>
                            </div>

                            <div class="row g-2 text-muted small mb-3">
                                <div class="col-6"><i class="bi bi-clock me-1 text-primary"></i> <strong>Departure:</strong> <?php echo e($r['departure_time']); ?></div>
                                <div class="col-6"><i class="bi bi-hourglass-split me-1 text-warning"></i> <strong>Duration:</strong> <?php echo e($r['estimated_duration']); ?></div>
                                <div class="col-6"><i class="bi bi-speedometer2 me-1 text-info"></i> <strong>Distance:</strong> <?php echo (int)$r['distance_km']; ?> km</div>
                                <div class="col-6"><i class="bi bi-snow me-1 text-info"></i> <strong>Climate:</strong> <?php echo e($r['ac_type']); ?><?php echo $r['wifi'] ? ' &bull; Wi-Fi' : ''; ?></div>
                            </div>

                            <?php if (!empty($r['intermediate_stops'])): ?>
                                <div class="p-2 mb-3 bg-light rounded border small">
                                    <strong class="text-dark"><i class="bi bi-geo me-1 text-danger"></i> Key Transit Stops:</strong>
                                    <div class="text-muted text-truncate"><?php echo e($r['intermediate_stops']); ?></div>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="focusRouteOnMap('<?php echo e($r['origin']); ?>', '<?php echo e($r['destination']); ?>')">
                                    <i class="bi bi-geo-alt me-1"></i> Locate on Map
                                </button>
                                <a href="book.php?route_id=<?php echo (int)$r['id']; ?>" class="btn btn-qs-primary btn-sm fw-bold">
                                    Select Seat <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Interactive Sri Lankan Map Section (Leaflet OpenStreetMap) -->
    <div class="card qs-card shadow" id="map-container-section">
        <div class="card-header qs-card-header py-3 d-flex justify-content-between align-items-center">
            <div class="fw-bold fs-5">
                <i class="bi bi-map-fill text-warning me-2"></i> Interactive Sri Lanka Transport Route Map
            </div>
            <span class="badge bg-light text-dark">OpenStreetMap Engine</span>
        </div>
        <div class="card-body p-0">
            <div id="sriLankaMap" style="height: 520px; width: 100%; border-radius: 0 0 12px 12px;"></div>
        </div>
        <div class="card-footer bg-light p-3 small text-muted">
            <i class="bi bi-info-circle me-1"></i> Click on any city pin or route line to view connected bus services, travel distances, and starting terminal locations.
        </div>
    </div>

</div>

<!-- Leaflet JavaScript Map Plotting -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initialize map centered on Sri Lanka
    const map = L.map('sriLankaMap').setView([7.8731, 80.7718], 7.5);

    // Add OpenStreetMap tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | BookMyBus LK'
    }).addTo(map);

    // Sri Lankan Major City Coordinates
    const citiesCoords = {
        'Colombo': [6.9271, 79.8612],
        'Kandy': [7.2906, 80.6337],
        'Galle': [6.0535, 80.2210],
        'Negombo': [7.2008, 79.8737],
        'Matara': [5.9549, 80.5550],
        'Kurunegala': [7.4863, 80.3623],
        'Dambulla': [7.8742, 80.6511],
        'Anuradhapura': [8.3114, 80.4037],
        'Jaffna': [9.6615, 80.0255],
        'Nuwara Eliya': [6.9497, 80.7891],
        'Ella': [6.8667, 81.0466],
        'Ratnapura': [6.6828, 80.4036]
    };

    // Add city markers
    for (let city in citiesCoords) {
        const marker = L.circleMarker(citiesCoords[city], {
            radius: 7,
            fillColor: '#0D2847',
            color: '#E5A910',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(map);

        marker.bindPopup(`<strong>${city} Bus Terminal</strong><br><small>Connected to BookMyBus LK Intercity Network</small>`);
    }

    // Predefined Major Routes Lines
    const activeRoutes = [
        { from: 'Colombo', to: 'Kandy', color: '#0D6EFD', label: 'Colombo &harr; Kandy Express (115 km)' },
        { from: 'Colombo', to: 'Galle', color: '#E5A910', label: 'Southern Expressway (126 km)' },
        { from: 'Colombo', to: 'Matara', color: '#8D021F', label: 'Colombo &harr; Matara Highway (160 km)' },
        { from: 'Colombo', to: 'Jaffna', color: '#28A745', label: 'Northern Superline (396 km)' },
        { from: 'Kandy', to: 'Nuwara Eliya', color: '#6610F2', label: 'Central Highlands Line' },
        { from: 'Kandy', to: 'Ella', color: '#FD7E14', label: 'Hill Country Voyager' }
    ];

    activeRoutes.forEach(r => {
        if (citiesCoords[r.from] && citiesCoords[r.to]) {
            const line = L.polyline([citiesCoords[r.from], citiesCoords[r.to]], {
                color: r.color,
                weight: 4,
                opacity: 0.75,
                dashArray: '8, 6'
            }).addTo(map);

            line.bindPopup(`<strong>${r.label}</strong><br><a href="index.php?origin=${r.from}&destination=${r.to}&search=1" class="btn btn-sm btn-primary text-white mt-1 py-0 px-2" style="font-size:0.75rem;">View Buses</a>`);
        }
    });

    window.focusRouteOnMap = function(origin, destination) {
        scrollToMap();
        if (citiesCoords[origin] && citiesCoords[destination]) {
            const bounds = L.latLngBounds([citiesCoords[origin], citiesCoords[destination]]);
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    };
});

function scrollToMap() {
    const el = document.getElementById('map-container-section');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
}
</script>

<?php include 'includes/footer.php'; ?>
