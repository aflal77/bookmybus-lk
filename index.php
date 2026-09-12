<?php

// BookMyBus LK – Homepage (index.php)
// "Sunset Highway" — Modern Sri Lankan Bus Reservation

require_once 'includes/auth.php';

// Sri Lankan cities
$cities = [
    'Colombo', 'Kandy', 'Galle', 'Negombo', 'Matara',
    'Kurunegala', 'Dambulla', 'Anuradhapura', 'Jaffna',
    'Nuwara Eliya', 'Ella', 'Ratnapura', 'Maharagama'
];

// Search inputs
$origin      = trim($_GET['origin'] ?? '');
$destination = trim($_GET['destination'] ?? '');
$travel_date = trim($_GET['date'] ?? date('Y-m-d'));
$bus_type    = trim($_GET['bus_type'] ?? '');
$is_search   = isset($_GET['search']);

$search_error   = '';
$search_results = [];

if ($is_search) {
    if (empty($origin) || empty($destination)) {
        $search_error = "Please select both an origin and a destination city.";
    } elseif ($origin === $destination) {
        $search_error = "Origin and destination cannot be the same city.";
    } else {
        $sql = "SELECT r.id AS route_id, r.route_number, r.origin, r.destination, r.departure_time, r.fare,
                       r.distance_km, r.estimated_duration, r.intermediate_stops,
                       b.id AS bus_id, b.bus_name, b.bus_number, b.total_seats, b.bus_type, b.operator, b.ac_type, b.wifi, b.usb_charging,
                       (SELECT COUNT(*) FROM seats s WHERE s.bus_id = b.id AND s.status = 'available') AS available_seats
                FROM routes r
                JOIN buses b ON r.bus_id = b.id
                WHERE r.origin = ? AND r.destination = ?";

        $params = [$origin, $destination];
        $types  = "ss";

        if (!empty($bus_type)) {
            $sql    .= " AND b.bus_type = ?";
            $params[] = $bus_type;
            $types  .= "s";
        }

        $sql .= " ORDER BY r.departure_time ASC";

        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            while ($row = mysqli_fetch_assoc($res)) {
                $search_results[] = $row;
            }
            mysqli_stmt_close($stmt);
        } else {
            $search_error = "Search error. Please try again.";
        }
    }
}

// Live stats from DB
$stats_routes     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM routes"))['c'] ?? 12;
$stats_buses      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM buses WHERE status='active'"))['c'] ?? 6;
$stats_seats      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM seats WHERE status='available'"))['c'] ?? 150;
$stats_passengers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM bookings WHERE booking_status != 'cancelled'"))['c'] ?? 25;

// Featured reviews
$reviews_res     = mysqli_query($conn, "SELECT rev.*, u.name AS user_name, r.origin, r.destination, bu.bus_name
                                         FROM reviews rev
                                         JOIN users u ON rev.user_id = u.id
                                         JOIN routes r ON rev.route_id = r.id
                                         JOIN buses bu ON rev.bus_id = bu.id
                                         WHERE rev.status = 'approved'
                                         ORDER BY rev.rating DESC, rev.id DESC LIMIT 3");
$featured_reviews = [];
while ($rw = mysqli_fetch_assoc($reviews_res)) {
    $featured_reviews[] = $rw;
}

$page_title  = "Travel Across Sri Lanka, Your Way";
$active_page = "home";
include 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════════
     HERO SECTION
     ══════════════════════════════════════════════════════════ -->
<section class="qs-hero">
    <div class="container position-relative" style="z-index:1">
        <div class="row align-items-center g-5">

            <!-- Left: Copy -->
            <div class="col-lg-6">
                <div class="hero-eyebrow">
                    Sri Lanka's Modern Bus Platform
                </div>
                <h1>Your journey<br>starts with the<br><em>right seat.</em></h1>
                <p class="hero-sub">
                    Search Sri Lankan bus routes, choose your seat, and book your journey in minutes — with instant digital tickets.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="#search-section" class="btn-qs-primary btn-qs-lg">
                        <i class="bi bi-search"></i> Search Buses
                    </a>
                    <a href="routes.php" class="btn-qs-lg" style="background:rgba(255,255,255,.1);color:#fff;border:1.5px solid rgba(255,255,255,.2);border-radius:var(--radius-md);padding:.75rem 1.6rem;font-size:1rem;font-weight:600;display:inline-flex;align-items:center;gap:8px;text-decoration:none;transition:var(--transition);" onmouseover="this.style.background='rgba(255,255,255,.18)'" onmouseout="this.style.background='rgba(255,255,255,.1)'">
                        <i class="bi bi-map"></i> View All Routes
                    </a>
                </div>

                <!-- Animated route illustration -->
                <div class="route-illustration mt-5">
                    <div class="route-svg-wrap">
                        <svg viewBox="0 0 500 80" xmlns="http://www.w3.org/2000/svg" style="overflow:visible">
                            <!-- City dots -->
                            <circle cx="20" cy="40" r="5" fill="#FF7A3D"/>
                            <circle cx="480" cy="40" r="5" fill="#FF7A3D"/>

                            <!-- Route line -->
                            <line x1="20" y1="40" x2="480" y2="40" stroke="#334155" stroke-width="1.5" stroke-dasharray="4 4"/>

                            <!-- Orange animated line -->
                            <line x1="20" y1="40" x2="480" y2="40" stroke="url(#routeGrad)" stroke-width="2.5" stroke-linecap="round" class="route-anim-line"/>

                            <!-- Stop dots along route -->
                            <circle cx="160" cy="40" r="3.5" fill="#475467" opacity=".6"/>
                            <circle cx="260" cy="40" r="3.5" fill="#475467" opacity=".6"/>
                            <circle cx="360" cy="40" r="3.5" fill="#475467" opacity=".6"/>

                            <!-- City labels -->
                            <text x="20" y="24" class="route-city-label" fill="#94A3B8" text-anchor="middle" font-family="Inter,sans-serif" font-size="9" font-weight="700" letter-spacing="1">KANDY</text>
                            <text x="480" y="24" class="route-city-label" fill="#94A3B8" text-anchor="middle" font-family="Inter,sans-serif" font-size="9" font-weight="700" letter-spacing="1">COLOMBO</text>

                            <!-- Stop labels -->
                            <text x="160" y="58" fill="#64748B" text-anchor="middle" font-family="Inter,sans-serif" font-size="7.5" font-weight="500">Peradeniya</text>
                            <text x="260" y="58" fill="#64748B" text-anchor="middle" font-family="Inter,sans-serif" font-size="7.5" font-weight="500">Warakapola</text>
                            <text x="360" y="58" fill="#64748B" text-anchor="middle" font-family="Inter,sans-serif" font-size="7.5" font-weight="500">Kadawatha</text>

                            <!-- Animated bus -->
                            <g class="route-bus">
                                <rect x="0" y="0" width="26" height="14" rx="3" fill="#FF7A3D"/>
                                <rect x="3" y="3" width="4" height="5" rx="1" fill="rgba(255,255,255,.5)"/>
                                <rect x="10" y="3" width="4" height="5" rx="1" fill="rgba(255,255,255,.5)"/>
                                <rect x="17" y="3" width="4" height="5" rx="1" fill="rgba(255,255,255,.5)"/>
                                <circle cx="7" cy="15" r="2.5" fill="#101828"/>
                                <circle cx="19" cy="15" r="2.5" fill="#101828"/>
                            </g>

                            <defs>
                                <linearGradient id="routeGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%"   stop-color="#FF7A3D" stop-opacity=".9"/>
                                    <stop offset="100%" stop-color="#FFB38A" stop-opacity=".3"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Right: Search card -->
            <div class="col-lg-6" id="search-section">
                <div class="qs-search-card">

                    <div class="search-title">
                        <i class="bi bi-bus-front-fill" style="color:var(--qs-orange)"></i>
                        Find available buses
                    </div>

                    <?php if (!empty($search_error)): ?>
                        <div class="alert alert-danger mb-4" role="alert">
                            <i class="bi bi-exclamation-circle-fill me-2"></i><?php echo e($search_error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="GET" action="index.php" id="searchForm">
                        <input type="hidden" name="search" value="1">

                        <!-- From / To row -->
                        <div class="row g-2 align-items-end mb-3">
                            <div class="col">
                                <label class="qs-label" for="origin">From</label>
                                <div class="qs-input-group">
                                    <i class="bi bi-circle-fill qs-input-icon" style="font-size:.5rem;color:var(--qs-orange)"></i>
                                    <div class="qs-select-wrap">
                                        <select class="qs-select" name="origin" id="origin" required>
                                            <option value="">Select city</option>
                                            <?php foreach ($cities as $c): ?>
                                                <option value="<?php echo e($c); ?>" <?php echo $origin === $c ? 'selected' : ''; ?>><?php echo e($c); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="col-auto pb-1">
                                <button type="button" class="swap-btn" id="swapBtn" title="Swap cities">
                                    <i class="bi bi-arrow-left-right" style="font-size:.8rem"></i>
                                </button>
                            </div>

                            <div class="col">
                                <label class="qs-label" for="destination">To</label>
                                <div class="qs-input-group">
                                    <i class="bi bi-geo-alt-fill qs-input-icon" style="font-size:.9rem;color:var(--qs-orange)"></i>
                                    <div class="qs-select-wrap">
                                        <select class="qs-select" name="destination" id="destination" required>
                                            <option value="">Select city</option>
                                            <?php foreach ($cities as $c): ?>
                                                <option value="<?php echo e($c); ?>" <?php echo $destination === $c ? 'selected' : ''; ?>><?php echo e($c); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date / Class row -->
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <label class="qs-label" for="tdate">Travel Date</label>
                                <div class="qs-input-group">
                                    <i class="bi bi-calendar3 qs-input-icon"></i>
                                    <input type="date" name="date" id="tdate" class="qs-input"
                                           value="<?php echo e($travel_date); ?>"
                                           min="<?php echo date('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="qs-label" for="btype">Bus Class</label>
                                <div class="qs-select-wrap">
                                    <select class="qs-select" name="bus_type" id="btype">
                                        <option value="">All Classes</option>
                                        <option value="Super Luxury" <?php echo $bus_type === 'Super Luxury' ? 'selected' : ''; ?>>Super Luxury</option>
                                        <option value="Luxury"       <?php echo $bus_type === 'Luxury'       ? 'selected' : ''; ?>>Luxury</option>
                                        <option value="Semi Luxury"  <?php echo $bus_type === 'Semi Luxury'  ? 'selected' : ''; ?>>Semi Luxury</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-search" id="searchBtn">
                            <i class="bi bi-search"></i>
                            <span id="searchBtnText">Search Buses</span>
                        </button>
                    </form>

                </div>
            </div>

        </div><!-- /row -->
    </div><!-- /container -->
</section>

<!-- ══════════════════════════════════════════════════════════
     STATS BAR
     ══════════════════════════════════════════════════════════ -->
<div class="qs-stats-bar">
    <div class="container">
        <div class="stats-inner">
            <div class="qs-stat-item">
                <div class="stat-number" data-target="<?php echo (int)$stats_routes; ?>"><?php echo (int)$stats_routes; ?><span class="accent">+</span></div>
                <div class="stat-label">Active Routes</div>
            </div>
            <div class="qs-stat-item">
                <div class="stat-number" data-target="<?php echo (int)$stats_buses; ?>"><?php echo (int)$stats_buses; ?></div>
                <div class="stat-label">Express Buses</div>
            </div>
            <div class="qs-stat-item">
                <div class="stat-number" data-target="<?php echo (int)$stats_seats; ?>"><?php echo (int)$stats_seats; ?><span class="accent">+</span></div>
                <div class="stat-label">Seats Available</div>
            </div>
            <div class="qs-stat-item">
                <div class="stat-number" data-target="<?php echo (int)$stats_passengers; ?>"><?php echo (int)$stats_passengers; ?><span class="accent">+</span></div>
                <div class="stat-label">Passengers Served</div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     SEARCH RESULTS (shown after search)
     ══════════════════════════════════════════════════════════ -->
<?php if ($is_search): ?>
<section class="qs-section" id="results-anchor" style="background:var(--qs-bg)">
    <div class="container">

        <div class="results-header">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="qs-badge qs-badge-orange">Search Results</span>
                    <?php if (!empty($search_results)): ?>
                        <span class="qs-badge qs-badge-grey"><?php echo count($search_results); ?> bus<?php echo count($search_results) !== 1 ? 'es' : ''; ?> found</span>
                    <?php endif; ?>
                </div>
                <h3><?php echo e($origin); ?> &rarr; <?php echo e($destination); ?></h3>
                <div class="results-meta">
                    <i class="bi bi-calendar3 me-1"></i>
                    <?php echo date('l, d F Y', strtotime($travel_date)); ?>
                    <?php if (!empty($bus_type)): ?>
                        &bull; <?php echo e($bus_type); ?>
                    <?php endif; ?>
                </div>
            </div>
            <a href="index.php" class="btn-qs-secondary btn-qs-sm">
                <i class="bi bi-arrow-counterclockwise"></i> New Search
            </a>
        </div>

        <?php if (empty($search_results) && empty($search_error)): ?>
            <!-- Empty state -->
            <div class="qs-card">
                <div class="qs-empty-state">
                    <div class="empty-icon">
                        <i class="bi bi-bus-front"></i>
                    </div>
                    <h4 style="font-weight:700;color:var(--qs-navy);margin-bottom:8px">No buses found for this route</h4>
                    <p style="color:var(--qs-text-muted);margin-bottom:24px;font-size:.9rem">
                        There are currently no scheduled services from <strong><?php echo e($origin); ?></strong> to <strong><?php echo e($destination); ?></strong>.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="routes.php" class="btn-qs-primary btn-qs-sm"><i class="bi bi-map"></i> Browse All Routes</a>
                        <a href="index.php" class="btn-qs-secondary btn-qs-sm"><i class="bi bi-arrow-left"></i> Try Another Search</a>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($search_results as $b): ?>
                    <?php
                    $avail     = (int)$b['available_seats'];
                    $available = $avail > 0;
                    ?>
                    <div class="bus-result-card">

                        <!-- Card top: route + times -->
                        <div class="card-top">
                            <div class="d-flex align-items-center gap-4 flex-wrap">
                                <!-- Departure -->
                                <div>
                                    <div style="font-size:1.4rem;font-weight:800;color:var(--qs-navy);letter-spacing:-0.5px;line-height:1">
                                        <?php
                                        $dep = $b['departure_time'];
                                        // Format as 08:30 AM
                                        echo date('h:i A', strtotime($dep));
                                        ?>
                                    </div>
                                    <div style="font-size:.75rem;color:var(--qs-text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-top:2px">
                                        <?php echo e($b['origin']); ?>
                                    </div>
                                </div>

                                <!-- Route line -->
                                <div style="flex:1;min-width:80px;display:flex;flex-direction:column;align-items:center;gap:3px">
                                    <div style="font-size:.72rem;color:var(--qs-text-muted);font-weight:500">
                                        <i class="bi bi-clock me-1"></i><?php echo e($b['estimated_duration']); ?>
                                    </div>
                                    <div style="width:100%;display:flex;align-items:center;gap:3px">
                                        <div style="width:6px;height:6px;border-radius:50%;background:var(--qs-orange);flex-shrink:0"></div>
                                        <div style="flex:1;height:1.5px;background:linear-gradient(90deg,var(--qs-orange),var(--qs-peach),#D0D5DD)"></div>
                                        <i class="bi bi-bus-front-fill" style="color:var(--qs-orange);font-size:.8rem;flex-shrink:0"></i>
                                        <div style="flex:1;height:1.5px;background:linear-gradient(90deg,#D0D5DD,var(--qs-peach),var(--qs-orange))"></div>
                                        <div style="width:6px;height:6px;border-radius:50%;background:var(--qs-orange);flex-shrink:0"></div>
                                    </div>
                                    <div style="font-size:.72rem;color:var(--qs-text-muted);font-weight:500">
                                        <?php echo (int)$b['distance_km']; ?> km
                                    </div>
                                </div>

                                <!-- Arrival est. -->
                                <div style="text-align:right">
                                    <div style="font-size:1.4rem;font-weight:800;color:var(--qs-navy);letter-spacing:-0.5px;line-height:1">
                                        <?php
                                        // Calculate approximate arrival
                                        $arr_ts = strtotime($dep) + ($b['distance_km'] * 72); // ~72 sec/km rough avg
                                        echo date('h:i A', $arr_ts);
                                        ?>
                                    </div>
                                    <div style="font-size:.75rem;color:var(--qs-text-muted);font-weight:600;text-transform:uppercase;letter-spacing:.5px;margin-top:2px">
                                        <?php echo e($b['destination']); ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Seats badge -->
                            <div class="ms-auto ps-3 flex-shrink-0">
                                <?php if ($available): ?>
                                    <span class="qs-badge qs-badge-green">
                                        <i class="bi bi-check-circle-fill"></i> <?php echo $avail; ?> seats left
                                    </span>
                                <?php else: ?>
                                    <span class="qs-badge qs-badge-red">
                                        <i class="bi bi-x-circle-fill"></i> Fully Booked
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Card body: bus details -->
                        <div class="card-body-area">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span style="font-weight:700;font-size:.92rem;color:var(--qs-navy)">
                                    <?php echo e($b['bus_name']); ?>
                                </span>
                                <span style="font-size:.8rem;color:var(--qs-text-muted)"><?php echo e($b['bus_number']); ?></span>
                                <span class="qs-badge qs-badge-navy"><?php echo e($b['bus_type']); ?></span>
                                <span class="qs-badge qs-badge-grey"><?php echo e($b['ac_type']); ?></span>
                                <?php if ($b['wifi']): ?>
                                    <span class="qs-badge qs-badge-grey"><i class="bi bi-wifi"></i> Wi-Fi</span>
                                <?php endif; ?>
                                <?php if ($b['usb_charging']): ?>
                                    <span class="qs-badge qs-badge-grey"><i class="bi bi-usb-plug"></i> USB</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size:.8rem;color:var(--qs-text-muted);margin-top:4px">
                                Operated by <?php echo e($b['operator']); ?>
                                &bull; Route <?php echo e($b['route_number']); ?>
                            </div>
                        </div>

                        <!-- Card footer: fare + CTA -->
                        <div class="card-footer-area">
                            <div>
                                <div style="font-size:.72rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--qs-text-muted)">Fare per seat</div>
                                <div style="font-size:1.35rem;font-weight:800;color:var(--qs-green);letter-spacing:-0.3px"><?php echo format_lkr($b['fare']); ?></div>
                            </div>
                            <div>
                                <?php if ($available): ?>
                                    <a href="book.php?route_id=<?php echo (int)$b['route_id']; ?>"
                                       class="btn-qs-primary btn-qs-sm">
                                        Select Seat <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                <?php else: ?>
                                    <button class="btn-qs-primary btn-qs-sm" disabled>
                                        Fully Booked
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════
     POPULAR ROUTES
     ══════════════════════════════════════════════════════════ -->
<section class="qs-section" style="background:#fff">
    <div class="container">
        <div class="section-header text-center mx-auto" style="max-width:540px">
            <div class="section-label mx-auto" style="width:fit-content">Popular Routes</div>
            <h2>Popular Sri Lankan Routes</h2>
            <p>Frequent daily express services connecting Sri Lanka's major cities.</p>
        </div>

        <div class="row g-3">
            <!-- Route cards -->
            <?php
            $popular_routes = [
                ['from'=>'Colombo',    'to'=>'Kandy',  'badge'=>'EX-01 Express',   'price'=>'From Rs. 2,400','duration'=>'3h 15m','km'=>115,'desc'=>'Central expressway luxury service via Kadawatha & Peradeniya'],
                ['from'=>'Galle',      'to'=>'Ella',   'badge'=>'Scenic Route',    'price'=>'From Rs. 3,200','duration'=>'4h 45m','km'=>198,'desc'=>'Coastal to misty hill country route via Udawalawe'],
                ['from'=>'Maharagama', 'to'=>'Matara', 'badge'=>'Southern Hwy',   'price'=>'From Rs. 1,950','duration'=>'2h 15m','km'=>152,'desc'=>'Direct Southern Expressway E01 nonstop coach'],
                ['from'=>'Colombo',    'to'=>'Jaffna', 'badge'=>'Northern Express','price'=>'From Rs. 3,800','duration'=>'7h 30m','km'=>396,'desc'=>'Luxury overnight A/C sleeper coach via Anuradhapura & A9'],
                ['from'=>'Colombo',    'to'=>'Galle',  'badge'=>'Southern Hwy',   'price'=>'From Rs. 1,800','duration'=>'1h 45m','km'=>126,'desc'=>'Nonstop Southern Expressway E01 luxury service'],
                ['from'=>'Kandy',      'to'=>'Ella',   'badge'=>'Hill Country',    'price'=>'From Rs. 2,900','duration'=>'4h 30m','km'=>138,'desc'=>'Scenic tea plantation route via Nuwara Eliya & Welimada'],
            ];
            foreach ($popular_routes as $pr):
            ?>
            <div class="col-lg-4 col-md-6">
                <a href="index.php?origin=<?php echo urlencode($pr['from']); ?>&destination=<?php echo urlencode($pr['to']); ?>&search=1" class="route-card">
                    <div class="route-card-header">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="route-from-to">
                                <?php echo e($pr['from']); ?>
                                <i class="bi bi-arrow-right" style="font-size:.8rem;color:var(--qs-peach)"></i>
                                <?php echo e($pr['to']); ?>
                            </div>
                            <span class="qs-badge" style="background:rgba(255,122,61,.2);color:var(--qs-peach);font-size:.68rem;flex-shrink:0">
                                <?php echo e($pr['badge']); ?>
                            </span>
                        </div>
                        <div class="route-price"><?php echo e($pr['price']); ?></div>
                    </div>
                    <div class="route-card-body">
                        <p style="font-size:.83rem;color:var(--qs-text-secondary);margin-bottom:10px;line-height:1.5">
                            <?php echo e($pr['desc']); ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <div style="font-size:.78rem;color:var(--qs-text-muted);font-weight:500">
                                <i class="bi bi-clock me-1"></i><?php echo e($pr['duration']); ?>
                                &bull; <?php echo e($pr['km']); ?> km
                            </div>
                            <span style="font-size:.78rem;font-weight:600;color:var(--qs-orange)">
                                View buses <i class="bi bi-arrow-right"></i>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     HOW IT WORKS
     ══════════════════════════════════════════════════════════ -->
<section class="qs-section" style="background:var(--qs-bg)">
    <div class="container">
        <div class="section-header text-center mx-auto" style="max-width:520px">
            <div class="section-label mx-auto" style="width:fit-content">How It Works</div>
            <h2>Book your seat in 4 steps</h2>
            <p>Simple, transparent, and hassle-free bus reservations.</p>
        </div>

        <div class="row g-4">
            <?php
            $steps = [
                ['icon'=>'bi-search',         'color'=>'#EFF6FF','icon_color'=>'#3B82F6','title'=>'Search Your Route','desc'=>'Select your origin, destination, and travel date to find available buses.'],
                ['icon'=>'bi-grid-3x3-gap',   'color'=>'var(--qs-orange-light)','icon_color'=>'var(--qs-orange)','title'=>'Choose Your Seat','desc'=>'Pick your preferred window or aisle seat on the interactive bus layout.'],
                ['icon'=>'bi-person-vcard',   'color'=>'#F0FDF4','icon_color'=>'var(--qs-green)','title'=>'Enter Your Details','desc'=>'Provide passenger information and confirm your booking instantly.'],
                ['icon'=>'bi-qr-code-scan',   'color'=>'#FEF9EE','icon_color'=>'#D97706','title'=>'Get Your Ticket','desc'=>'Receive a digital e-ticket with QR code — show it on your phone to board.'],
            ];
            foreach ($steps as $i => $step):
            ?>
            <div class="col-md-6 col-lg-3">
                <div class="how-step">
                    <div class="step-icon" style="background:<?php echo $step['color']; ?>">
                        <i class="bi <?php echo $step['icon']; ?>" style="color:<?php echo $step['icon_color']; ?>;font-size:1.5rem"></i>
                        <div class="step-number"><?php echo $i + 1; ?></div>
                    </div>
                    <h5><?php echo $step['title']; ?></h5>
                    <p><?php echo $step['desc']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     WHY QUICKSEAT
     ══════════════════════════════════════════════════════════ -->
<section class="qs-section" style="background:#fff">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="section-label">Why BookMyBus LK</div>
                <h2 style="margin-bottom:12px">Built for Sri Lankan<br>intercity travel</h2>
                <p style="color:var(--qs-text-secondary);margin-bottom:32px;line-height:1.7">
                    BookMyBus LK modernizes Sri Lanka's intercity travel by combining reliable bus operators with real-time seat tracking and secure booking technology.
                </p>

                <div class="feature-item">
                    <div class="feature-icon" style="background:var(--qs-green-light)">
                        <i class="bi bi-shield-check" style="color:var(--qs-green)"></i>
                    </div>
                    <div>
                        <h6>Double-Booking Prevention</h6>
                        <p>ACID-compliant database transactions with row-level locking ensure your seat can never be double-booked.</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon" style="background:#EFF6FF">
                        <i class="bi bi-clock-history" style="color:#3B82F6"></i>
                    </div>
                    <div>
                        <h6>Real-Time Seat Availability</h6>
                        <p>Live seat inventory reflects current booking status — what you see is what you get.</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon" style="background:var(--qs-orange-light)">
                        <i class="bi bi-qr-code" style="color:var(--qs-orange)"></i>
                    </div>
                    <div>
                        <h6>Instant QR Digital Tickets</h6>
                        <p>Conductors and station staff verify ticket authenticity in seconds using any mobile browser.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="qs-card p-0 overflow-hidden">
                    <div class="qs-card-header" style="background:var(--qs-navy);color:#fff;border-bottom:none;padding:18px 22px">
                        <i class="bi bi-compass-fill" style="color:var(--qs-orange)"></i>
                        Key Transport Terminals
                    </div>
                    <?php
                    $terminals = [
                        ['name'=>'Colombo Bastian Mawatha','desc'=>'Central private intercity departures','badge'=>'Main Hub'],
                        ['name'=>'Makumbura Multimodal Center','desc'=>'Southern Expressway luxury feeder','badge'=>'Expressway'],
                        ['name'=>'Kandy Goods Shed Stand','desc'=>'Central province highland terminal','badge'=>'Hub 02'],
                        ['name'=>'Galle & Matara Terminals','desc'=>'Southern coastal transit hubs','badge'=>'Coast Line'],
                    ];
                    foreach ($terminals as $t):
                    ?>
                    <div style="padding:14px 22px;border-bottom:1px solid var(--qs-border);display:flex;justify-content:space-between;align-items:center;gap:12px">
                        <div>
                            <div style="font-weight:600;font-size:.9rem;color:var(--qs-navy)"><?php echo $t['name']; ?></div>
                            <div style="font-size:.78rem;color:var(--qs-text-muted);margin-top:1px"><?php echo $t['desc']; ?></div>
                        </div>
                        <span class="qs-badge qs-badge-orange flex-shrink-0"><?php echo $t['badge']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     REVIEWS (from DB, if any)
     ══════════════════════════════════════════════════════════ -->
<?php if (!empty($featured_reviews)): ?>
<section class="qs-section" style="background:var(--qs-bg)">
    <div class="container">
        <div class="section-header text-center mx-auto" style="max-width:480px">
            <div class="section-label mx-auto" style="width:fit-content">Passenger Reviews</div>
            <h2>What our passengers say</h2>
            <p>Authentic reviews from travelers across Sri Lanka.</p>
        </div>

        <div class="row g-4">
            <?php foreach ($featured_reviews as $rev): ?>
                <div class="col-md-4">
                    <div class="review-card">
                        <div class="stars">
                            <?php for ($i = 0; $i < (int)$rev['rating']; $i++): ?>&#9733;<?php endfor; ?>
                        </div>
                        <blockquote>"<?php echo e($rev['comment']); ?>"</blockquote>
                        <div class="mt-auto border-top pt-3" style="border-color:var(--qs-border)!important">
                            <div class="reviewer-name"><?php echo e($rev['user_name']); ?></div>
                            <div class="reviewer-route">
                                <?php echo e($rev['origin']); ?> &rarr; <?php echo e($rev['destination']); ?>
                                &bull; <?php echo e($rev['bus_name']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════
     FAQ
     ══════════════════════════════════════════════════════════ -->
<section class="qs-section" style="background:#fff">
    <div class="container">
        <div class="section-header text-center mx-auto" style="max-width:480px">
            <div class="section-label mx-auto" style="width:fit-content">FAQ</div>
            <h2>Frequently asked questions</h2>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="accordion qs-accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How do I book a bus ticket on BookMyBus LK?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Select your origin and destination in the search form, choose an available trip, pick your seat on the interactive layout, enter your passenger details, and confirm. Your digital QR ticket is generated instantly.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                How does BookMyBus LK prevent double-booking?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                All bookings use ACID-compliant MySQL transactions with <code>SELECT ... FOR UPDATE</code> row-level locking. If two passengers attempt to book the same seat simultaneously, the second transaction is safely rolled back.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Can I cancel my reservation?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes. Registered customers can visit <strong>My Bookings</strong> and cancel any eligible confirmed booking. The seat is released back to inventory immediately.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Do I need to print my ticket?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                No. You can show your digital e-ticket on your smartphone screen. The conductor scans your QR code or checks your booking reference upon boarding.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════════
     CTA BANNER
     ══════════════════════════════════════════════════════════ -->
<section class="qs-cta-section">
    <div class="container text-center position-relative" style="z-index:1">
        <div class="section-label mx-auto" style="width:fit-content;background:rgba(255,122,61,.15);border-color:rgba(255,122,61,.3);color:var(--qs-peach)">
            Start Booking
        </div>
        <h2 style="font-size:clamp(1.6rem,3vw,2.3rem);font-weight:800;color:#fff;letter-spacing:-0.5px;margin:16px 0 10px">
            Ready to travel across Sri Lanka?
        </h2>
        <p style="color:rgba(255,255,255,.6);margin-bottom:30px;font-size:1rem">
            Book your seat today and experience seamless intercity travel.
        </p>
        <a href="#search-section" class="btn-qs-primary btn-qs-lg">
            <i class="bi bi-ticket-perforated-fill"></i> Book Your Ticket Now
        </a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- ── Page Scripts ───────────────────────────────────────── -->
<script>
// Swap From/To cities
document.getElementById('swapBtn').addEventListener('click', function() {
    const from = document.getElementById('origin');
    const to   = document.getElementById('destination');
    const tmp  = from.value;
    from.value = to.value;
    to.value   = tmp;
    this.style.transform = 'rotate(180deg)';
    setTimeout(() => { this.style.transform = ''; }, 400);
});

// Search button loading state
document.getElementById('searchForm').addEventListener('submit', function() {
    const btn  = document.getElementById('searchBtn');
    const text = document.getElementById('searchBtnText');
    btn.disabled = true;
    btn.style.opacity = '.8';
    text.textContent = 'Searching...';
});

// Scroll to results on search
<?php if ($is_search): ?>
window.addEventListener('load', function() {
    const el = document.getElementById('results-anchor');
    if (el) {
        setTimeout(function() {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 200);
    }
});
<?php endif; ?>

// Animate bus along route line (simple translateX)
(function() {
    const bus = document.querySelector('.route-bus');
    if (!bus) return;
    let x = -30;
    let dir = 1;
    function move() {
        x += dir * 0.35;
        if (x > 458) dir = -1;
        if (x < -30) dir = 1;
        bus.setAttribute('transform', 'translate(' + x + ', 33)');
        requestAnimationFrame(move);
    }
    move();
})();
</script>
