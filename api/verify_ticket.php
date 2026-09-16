<?php

// BookMyBus LK – REST API: Verify Ticket (api/verify_ticket.php)
// Endpoint for conductor scanners & mobile apps

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../db.php';

$ref = trim($_GET['ref'] ?? ($_POST['ref'] ?? ''));

if (empty($ref)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Booking reference or QR token required.']);
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT b.booking_ref, b.passenger_name, b.seat_number, b.total_amount, b.payment_status, b.booking_status, b.booking_date,
                                      r.origin, r.destination, r.departure_time, bu.bus_name, bu.bus_number
                               FROM bookings b
                               JOIN routes r ON b.route_id = r.id
                               JOIN buses bu ON r.bus_id = bu.id
                               WHERE b.booking_ref = ? OR b.qr_token = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "ss", $ref, $ref);
mysqli_stmt_execute($stmt);
$ticket = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$ticket) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'valid' => false,
        'message' => 'Ticket reference not found in system.'
    ]);
    exit;
}

$is_valid = ($ticket['booking_status'] === 'confirmed');

echo json_encode([
    'status' => 'success',
    'valid' => $is_valid,
    'boarding_allowed' => $is_valid,
    'ticket' => $ticket
], JSON_PRETTY_PRINT);
exit;
