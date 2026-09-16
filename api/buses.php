<?php

// BookMyBus LK – REST API: Buses (api/buses.php)
// Returns JSON formatted fleet coaches and amenities

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

$res = mysqli_query($conn, "SELECT b.*, (SELECT COUNT(*) FROM seats s WHERE s.bus_id = b.id AND s.status = 'available') AS available_seats FROM buses b ORDER BY b.id ASC");
$buses = [];
while ($row = mysqli_fetch_assoc($res)) {
    $buses[] = $row;
}

echo json_encode([
    'status' => 'success',
    'count' => count($buses),
    'data' => $buses
], JSON_PRETTY_PRINT);
exit;
