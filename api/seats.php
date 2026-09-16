<?php

// BookMyBus LK – REST API: Seats (api/seats.php)
// Live seat availability map for a given bus

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

$bus_id = (int)($_GET['bus_id'] ?? 0);
$route_id = (int)($_GET['route_id'] ?? 0);

if ($route_id > 0 && $bus_id <= 0) {
    $r_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT bus_id FROM routes WHERE id = $route_id"));
    if ($r_row) {
        $bus_id = (int)$r_row['bus_id'];
    }
}

if ($bus_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Please provide a valid bus_id or route_id.']);
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT seat_number, status FROM seats WHERE bus_id = ? ORDER BY seat_number ASC");
mysqli_stmt_bind_param($stmt, "i", $bus_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$seats = [];
$avail = 0;

while ($row = mysqli_fetch_assoc($res)) {
    $seats[] = [
        'seat_number' => (int)$row['seat_number'],
        'status' => $row['status']
    ];
    if ($row['status'] === 'available') $avail++;
}
mysqli_stmt_close($stmt);

echo json_encode([
    'status' => 'success',
    'bus_id' => $bus_id,
    'total_seats' => count($seats),
    'available_count' => $avail,
    'booked_count' => count($seats) - $avail,
    'seats' => $seats
], JSON_PRETTY_PRINT);
exit;
