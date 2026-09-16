<?php

// BookMyBus LK – Seat Selection Page (book.php)
// Interactive bus seat picker — "Sunset Highway" theme

require_once 'includes/auth.php';

$route_id    = isset($_GET['route_id'])    ? (int)$_GET['route_id']    : 0;
$schedule_id = isset($_GET['schedule_id']) ? (int)$_GET['schedule_id'] : 0;

if ($route_id <= 0 && $schedule_id <= 0) {
    header("Location: index.php");
    exit;
}

if ($schedule_id > 0) {
    $sql = "SELECT s.id AS schedule_id, s.travel_date, s.departure_time, s.arrival_time, s.fare, s.status AS trip_status,
                   r.id AS route_id, r.origin, r.destination, r.distance_km, r.estimated_duration, r.start_location, r.end_location,
                   b.id AS bus_id, b.bus_name, b.bus_number, b.total_seats, b.bus_type, b.operator, b.ac_type, b.wifi, b.usb_charging
            FROM schedules s
            JOIN routes r ON s.route_id = r.id
            JOIN buses b ON s.bus_id = b.id
            WHERE s.id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $schedule_id);
} else {
    $sql = "SELECT NULL AS schedule_id, CURDATE() AS travel_date, r.departure_time, NULL AS arrival_time, r.fare, 'Scheduled' AS trip_status,
                   r.id AS route_id, r.origin, r.destination, r.distance_km, r.estimated_duration, r.start_location, r.end_location,
                   b.id AS bus_id, b.bus_name, b.bus_number, b.total_seats, b.bus_type, b.operator, b.ac_type, b.wifi, b.usb_charging
            FROM routes r
            JOIN buses b ON r.bus_id = b.id
            WHERE r.id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $route_id);
}

mysqli_stmt_execute($stmt);
$res  = mysqli_stmt_get_result($stmt);
$trip = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$trip) {
    die("<div class='container my-5 text-center'>
            <div class='alert alert-danger'>
                <h4>Route not found</h4>
                <p>The requested bus trip does not exist.</p>
                <a href='index.php' class='btn-qs-primary btn-qs-sm mt-2'>Back to Home</a>
            </div>
         </div>");
}

$bus_id      = (int)$trip['bus_id'];
$route_id    = (int)$trip['route_id'];
$fare_amount = (float)$trip['fare'];

// Fetch seats
$seat_stmt = mysqli_prepare($conn, "SELECT seat_number, status FROM seats WHERE bus_id = ? ORDER BY seat_number ASC");
mysqli_stmt_bind_param($seat_stmt, "i", $bus_id);
mysqli_stmt_execute($seat_stmt);
$seat_res = mysqli_stmt_get_result($seat_stmt);
$seats    = [];
while ($row = mysqli_fetch_assoc($seat_res)) {
    $seats[(int)$row['seat_number']] = $row['status'];
}
mysqli_stmt_close($seat_stmt);

$avail_count  = count(array_filter($seats, fn($s) => $s === 'available'));
$booked_count = count(array_filter($seats, fn($s) => $s === 'booked'));

$page_title  = "Select Your Seat — " . $trip['origin'] . " to " . $trip['destination'];
$active_page = "";
include 'includes/header.php';
?>

<div class="container" style="padding-top:36px;padding-bottom:60px">

    <!-- ── Trip Header ────────────────────────────────────── -->
    <div class="trip-header-card">
        <div class="row align-items-center g-3">
            <div class="col-lg-8">
                <!-- Breadcrumb -->
                <div style="font-size:.78rem;color:var(--qs-text-muted);margin-bottom:10px">
                    <a href="index.php" style="color:var(--qs-text-muted);text-decoration:none">Home</a>
                    <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i>
                    <a href="index.php?origin=<?php echo urlencode($trip['origin']); ?>&destination=<?php echo urlencode($trip['destination']); ?>&search=1" style="color:var(--qs-text-muted);text-decoration:none">
                        <?php echo e($trip['origin']); ?> &rarr; <?php echo e($trip['destination']); ?>
                    </a>
                    <i class="bi bi-chevron-right mx-1" style="font-size:.65rem"></i>
                    <span style="color:var(--qs-navy);font-weight:600">Select Seat</span>
                </div>

                <!-- Route -->
                <div class="trip-route-display">
                    <span class="city-name"><?php echo e($trip['origin']); ?></span>
                    <div class="arrow-wrap">
                        <div class="arrow-line"></div>
                        <i class="bi bi-caret-right-fill arrow-icon"></i>
                        <div class="arrow-line"></div>
                    </div>
                    <span class="city-name"><?php echo e($trip['destination']); ?></span>
                </div>

                <!-- Meta -->
                <div class="trip-meta">
                    <div class="trip-meta-item">
                        <i class="bi bi-bus-front-fill"></i>
                        <span><?php echo e($trip['bus_name']); ?> &bull; <?php echo e($trip['bus_number']); ?></span>
                    </div>
                    <div class="trip-meta-item">
                        <i class="bi bi-clock"></i>
                        <span><?php echo date('h:i A', strtotime($trip['departure_time'])); ?> departure</span>
                    </div>
                    <div class="trip-meta-item">
                        <i class="bi bi-calendar3"></i>
                        <span><?php echo date('d M Y', strtotime($trip['travel_date'])); ?></span>
                    </div>
                    <div class="trip-meta-item">
                        <i class="bi bi-hourglass-split"></i>
                        <span><?php echo e($trip['estimated_duration']); ?> &bull; <?php echo (int)$trip['distance_km']; ?> km</span>
                    </div>
                </div>

                <!-- Amenity badges -->
                <div class="d-flex gap-2 flex-wrap mt-2">
                    <span class="qs-badge qs-badge-navy"><?php echo e($trip['bus_type']); ?></span>
                    <span class="qs-badge qs-badge-grey"><?php echo e($trip['ac_type']); ?></span>
                    <?php if ($trip['wifi']): ?>
                        <span class="qs-badge qs-badge-grey"><i class="bi bi-wifi"></i> Wi-Fi</span>
                    <?php endif; ?>
                    <?php if ($trip['usb_charging']): ?>
                        <span class="qs-badge qs-badge-grey"><i class="bi bi-usb-plug"></i> USB</span>
                    <?php endif; ?>
                    <span class="qs-badge qs-badge-green">
                        <i class="bi bi-check-circle-fill"></i> <?php echo $avail_count; ?> seats available
                    </span>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="fare-display">
                    <div class="fare-label">Fare per seat</div>
                    <div class="fare-amount"><?php echo format_lkr($fare_amount); ?></div>
                    <div class="fare-sub">Includes taxes &amp; highway tolls</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Main Content: Seat Grid + Summary ──────────────── -->
    <div class="row g-4 justify-content-center">

        <!-- Left: Seat Layout -->
        <div class="col-lg-6">
            <div class="qs-card">
                <div class="qs-card-header">
                    <i class="bi bi-grid-3x3-gap-fill" style="color:var(--qs-orange)"></i>
                    Choose Your Seat
                </div>
                <div style="padding:20px">

                    <!-- Legend -->
                    <div class="seat-legend">
                        <div class="legend-item">
                            <div class="legend-dot available"></div>
                            <span>Available</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot selected"></div>
                            <span>Your Selection</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-dot booked"></div>
                            <span>Booked</span>
                        </div>
                    </div>

                    <!-- Bus shell -->
                    <div class="bus-shell">
                        <!-- Cockpit -->
                        <div class="bus-cockpit">
                            <div class="windshield-label">
                                <i class="bi bi-wind"></i> FRONT
                            </div>
                            <div class="driver-block">
                                <i class="bi bi-person-fill"></i> DRIVER
                            </div>
                        </div>

                        <!-- Seat grid — 7 rows × 4 + rear 2 -->
                        <div class="seat-grid">
                            <?php
                            for ($r = 0; $r < 7; $r++):
                                $s1 = ($r * 4) + 1;  $s2 = ($r * 4) + 2;
                                $s3 = ($r * 4) + 3;  $s4 = ($r * 4) + 4;
                                $st1 = $seats[$s1] ?? 'available';
                                $st2 = $seats[$s2] ?? 'available';
                                $st3 = $seats[$s3] ?? 'available';
                                $st4 = $seats[$s4] ?? 'available';
                            ?>
                            <div class="seat-row">
                                <div class="seat-pair">
                                    <div class="seat <?php echo $st1; ?>" data-seat="<?php echo $s1; ?>" title="Seat <?php echo $s1; ?> — <?php echo ucfirst($st1); ?>">
                                        <?php echo $s1; ?>
                                    </div>
                                    <div class="seat <?php echo $st2; ?>" data-seat="<?php echo $s2; ?>" title="Seat <?php echo $s2; ?> — <?php echo ucfirst($st2); ?>">
                                        <?php echo $s2; ?>
                                    </div>
                                </div>
                                <div class="aisle">
                                    <span style="color:var(--qs-border)">&bull;</span>
                                </div>
                                <div class="seat-pair">
                                    <div class="seat <?php echo $st3; ?>" data-seat="<?php echo $s3; ?>" title="Seat <?php echo $s3; ?> — <?php echo ucfirst($st3); ?>">
                                        <?php echo $s3; ?>
                                    </div>
                                    <div class="seat <?php echo $st4; ?>" data-seat="<?php echo $s4; ?>" title="Seat <?php echo $s4; ?> — <?php echo ucfirst($st4); ?>">
                                        <?php echo $s4; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endfor; ?>

                            <!-- Rear row: seats 29, 30 + exit markers -->
                            <?php
                            $st29 = $seats[29] ?? 'available';
                            $st30 = $seats[30] ?? 'available';
                            ?>
                            <div class="seat-row">
                                <div class="seat-pair">
                                    <div class="seat <?php echo $st29; ?>" data-seat="29" title="Seat 29 — <?php echo ucfirst($st29); ?>">29</div>
                                    <div class="seat <?php echo $st30; ?>" data-seat="30" title="Seat 30 — <?php echo ucfirst($st30); ?>">30</div>
                                </div>
                                <div class="aisle"></div>
                                <div class="seat-pair">
                                    <div style="width:44px;height:44px;border:1.5px dashed var(--qs-border);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:.55rem;font-weight:700;color:var(--qs-text-muted);letter-spacing:.5px">EXIT</div>
                                    <div style="width:44px;height:44px;border:1.5px dashed var(--qs-border);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:.55rem;font-weight:700;color:var(--qs-text-muted);letter-spacing:.5px">DOOR</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Seat count summary -->
                    <div class="d-flex justify-content-center gap-4 mt-3" style="font-size:.8rem;color:var(--qs-text-muted);font-weight:500">
                        <span><span style="color:var(--qs-green);font-weight:700"><?php echo $avail_count; ?></span> available</span>
                        <span><span style="color:var(--qs-red);font-weight:700"><?php echo $booked_count; ?></span> booked</span>
                        <span><span style="color:var(--qs-navy);font-weight:700"><?php echo count($seats); ?></span> total</span>
                    </div>

                </div>
            </div>
        </div>

        <!-- Right: Booking Summary -->
        <div class="col-lg-5">
            <div class="booking-summary-panel">
                <div class="panel-header">
                    <i class="bi bi-receipt" style="color:var(--qs-orange)"></i>
                    Booking Summary
                </div>
                <div class="panel-body">

                    <div class="summary-row">
                        <div class="s-label">Route</div>
                        <div class="s-value" style="font-weight:700"><?php echo e($trip['origin']); ?> &rarr; <?php echo e($trip['destination']); ?></div>
                    </div>

                    <div class="summary-row">
                        <div class="s-label">Service</div>
                        <div class="s-value" style="font-size:.85rem"><?php echo e($trip['bus_name']); ?><br><span style="color:var(--qs-text-muted);font-weight:400"><?php echo e($trip['operator']); ?></span></div>
                    </div>

                    <div class="summary-row">
                        <div class="s-label">Departure</div>
                        <div class="s-value" style="color:var(--qs-orange)"><?php echo date('h:i A', strtotime($trip['departure_time'])); ?><br><span style="font-size:.8rem;color:var(--qs-text-muted);font-weight:400"><?php echo date('d M Y', strtotime($trip['travel_date'])); ?></span></div>
                    </div>

                    <div class="summary-row" style="flex-direction:column;gap:8px;align-items:stretch">
                        <div class="s-label">Selected Seat</div>
                        <div class="seat-display empty" id="display_seat_number">— No seat selected —</div>
                    </div>

                    <div class="summary-row" style="border-bottom:none;padding-bottom:0">
                        <div class="s-label">Total Fare</div>
                        <div class="total-fare" id="display_fare">Rs. 0.00</div>
                    </div>

                    <!-- Action form -->
                    <form action="confirm.php" method="POST" id="seatBookingForm" style="margin-top:20px">
                        <input type="hidden" name="route_id"    value="<?php echo (int)$trip['route_id']; ?>">
                        <input type="hidden" name="schedule_id" value="<?php echo (int)$trip['schedule_id']; ?>">
                        <input type="hidden" name="travel_date" value="<?php echo e($trip['travel_date']); ?>">
                        <input type="hidden" name="seat_number" id="selected_seat_input" value="">

                        <button type="submit" class="btn-qs-primary w-100" style="padding:.8rem;font-size:.95rem;justify-content:center;border-radius:var(--radius-md)" id="continueBtn" disabled>
                            Continue to Details <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="index.php" style="font-size:.82rem;color:var(--qs-text-muted);text-decoration:none">
                            <i class="bi bi-arrow-left me-1"></i> Back to search
                        </a>
                    </div>

                    <!-- Help tip -->
                    <div id="seat-tip" style="background:var(--qs-bg);border-radius:var(--radius-sm);padding:10px 14px;margin-top:14px;font-size:.8rem;color:var(--qs-text-muted);text-align:center">
                        <i class="bi bi-info-circle me-1"></i>
                        Tap a <strong style="color:var(--qs-green)">green seat</strong> on the bus layout to select it.
                    </div>

                </div>
            </div>
        </div>

    </div><!-- /row -->
</div><!-- /container -->

<!-- ── Seat Selection JS ───────────────────────────────────── -->
<script>
const farePerSeat     = <?php echo json_encode($fare_amount); ?>;
const availableSeats  = document.querySelectorAll('.seat.available');
const bookedSeats     = document.querySelectorAll('.seat.booked');
const selectedInput   = document.getElementById('selected_seat_input');
const displaySeat     = document.getElementById('display_seat_number');
const displayFare     = document.getElementById('display_fare');
const continueBtn     = document.getElementById('continueBtn');
const seatTip         = document.getElementById('seat-tip');

function formatLKR(val) {
    return 'Rs. ' + val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

availableSeats.forEach(function(seat) {
    seat.addEventListener('click', function() {
        const seatNo = this.getAttribute('data-seat');

        if (this.classList.contains('selected')) {
            // Deselect
            this.classList.remove('selected');
            this.classList.add('available');
            selectedInput.value = '';
            displaySeat.textContent = '— No seat selected —';
            displaySeat.classList.add('empty');
            displayFare.textContent = 'Rs. 0.00';
            continueBtn.disabled = true;
            seatTip.innerHTML = '<i class="bi bi-info-circle me-1"></i>Tap a <strong style="color:var(--qs-green)">green seat</strong> on the bus layout to select it.';
        } else {
            // Deselect previous
            document.querySelectorAll('.seat.selected').forEach(function(el) {
                el.classList.remove('selected');
                el.classList.add('available');
            });

            // Select this
            this.classList.remove('available');
            this.classList.add('selected');
            selectedInput.value = seatNo;

            displaySeat.textContent = 'Seat ' + seatNo;
            displaySeat.classList.remove('empty');
            displayFare.textContent = formatLKR(farePerSeat);
            continueBtn.disabled = false;

            seatTip.innerHTML = '<i class="bi bi-check-circle-fill me-1" style="color:var(--qs-green)"></i>Seat <strong>' + seatNo + '</strong> selected — click continue to proceed.';
        }
    });
});

bookedSeats.forEach(function(seat) {
    seat.addEventListener('click', function() {
        const seatNo = this.getAttribute('data-seat');
        seatTip.innerHTML = '<i class="bi bi-x-circle-fill me-1" style="color:var(--qs-red)"></i>Seat <strong>' + seatNo + '</strong> is already booked. Please choose a green seat.';
    });
});
</script>

<?php include 'includes/footer.php'; ?>
