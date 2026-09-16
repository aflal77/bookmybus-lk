<?php

// BookMyBus LK – REST API: Routes (api/routes.php)
// Returns JSON formatted intercity bus routes

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

$origin = trim($_GET['origin'] ?? '');
$destination = trim($_GET['destination'] ?? '');

$sql = "SELECT r.id, r.route_number, r.origin, r.destination, r.departure_time, r.fare,
               r.distance_km, r.estimated_duration, r.intermediate_stops,
               b.id AS bus_id, b.bus_name, b.bus_number, b.bus_type, b.operator, b.ac_type, b.wifi, b.usb_charging,
               (SELECT COUNT(*) FROM seats s WHERE s.bus_id = b.id AND s.status = 'available') AS available_seats
        FROM routes r
        JOIN buses b ON r.bus_id = b.id
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($origin)) {
    $sql .= " AND r.origin = ?";
    $params[] = $origin;
    $types .= 's';
}
if (!empty($destination)) {
    $sql .= " AND r.destination = ?";
    $params[] = $destination;
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

echo json_encode([
    'status' => 'success',
    'count' => count($routes),
    'data' => $routes
], JSON_PRETTY_PRINT);
exit;
