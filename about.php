<?php
require_once 'includes/auth.php';

$page_title  = 'About BookMyBus LK — Sri Lanka\'s Premium Bus Booking Platform';
$active_page = 'about';
include 'includes/header.php';
?>

<!-- ── Page Hero Banner ────────────────────────────────────── -->
<div class="about-hero-banner mx-3 mx-md-0" style="margin-top:32px;margin-bottom:0;border-radius:0">
    <div style="position:relative;overflow:hidden;max-height:340px;border-radius:0">
        <img src="https://images.unsplash.com/photo-1570125909232-eb263c188f7e?w=1600&q=80"
             alt="Luxury express coach on a Sri Lankan highway"
             class="img-fluid w-100"
             style="height:340px;object-fit:cover;object-position:center 65%;display:block">
        <!-- Dark overlay -->
        <div style="position:absolute;inset:0;background:linear-gradient(135deg,rgba(10,18,32,.88) 0%,rgba(10,18,32,.45) 60%,rgba(10,18,32,.25) 100%)"></div>
        <!-- Content over image -->
        <div style="position:absolute;inset:0;display:flex;align-items:center;padding:0 40px">
            <div style="max-width:640px">
                <span class="section-label mb-3">Our Story</span>
                <h1 style="font-size:clamp(1.8rem,3.5vw,2.6rem);font-weight:800;color:#fff;letter-spacing:-0.8px;line-height:1.2;margin-bottom:14px">
                    Modernizing Passenger Travel<br>Across Sri Lanka
                </h1>
                <p style="color:rgba(255,255,255,.7);font-size:1rem;line-height:1.65;margin:0;max-width:520px">
                    From the coastal expressways of the south to the misty mountain roads of the central highlands — BookMyBus LK connects every corner of the island.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="container" style="padding-top:52px;padding-bottom:72px">

    <!-- ── Mission & Vision ───────────────────────────────── -->
    <div class="row g-4 mb-5">
        <div class="col-lg-6">
            <div class="qs-card h-100 p-4 p-md-5" style="border-left:4px solid var(--qs-orange)">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="about-feature-icon" style="background:var(--qs-orange-light);color:var(--qs-orange)">
                        <i class="bi bi-bullseye"></i>
                    </div>
                    <h4 class="fw-bold mb-0" style="color:var(--qs-navy)">Our Mission</h4>
                </div>
                <p class="text-muted mb-3" style="line-height:1.75">
                    To eliminate the stress and uncertainty of traditional bus travel in Sri Lanka by providing every commuter, tourist, and daily traveller with a fast, transparent, and fully digital seat booking experience — at any hour, from any device.
                </p>
                <p class="text-muted mb-0" style="line-height:1.75">
                    We believe that intercity travel should be as seamless as booking a flight. Our platform delivers real-time seat availability, instant QR boarding passes, and guaranteed reservations secured by ACID-compliant database transactions.
                </p>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="qs-card h-100 p-4 p-md-5" style="border-left:4px solid var(--bmb-gold)">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="about-feature-icon" style="background:var(--bmb-gold-light);color:var(--bmb-gold)">
                        <i class="bi bi-eye-fill"></i>
                    </div>
                    <h4 class="fw-bold mb-0" style="color:var(--qs-navy)">Our Vision</h4>
                </div>
                <p class="text-muted mb-3" style="line-height:1.75">
                    To become Sri Lanka's most trusted smart-mobility ecosystem — connecting private luxury coach operators, state transport services, and millions of passengers through a single, unified digital platform.
                </p>
                <p class="text-muted mb-0" style="line-height:1.75">
                    We envision a future where every Sri Lankan can plan, book, and board an intercity bus entirely paperlessly — from Point Pedro in the north to Dondra Head in the south.
                </p>
            </div>
        </div>
    </div>

    <!-- ── Why BookMyBus LK ────────────────────────────────── -->
    <div class="text-center mb-4">
        <span class="section-label">Why Choose Us</span>
        <h2 class="fw-bold" style="color:var(--qs-navy);letter-spacing:-0.4px">The BookMyBus LK Advantage</h2>
        <p class="text-muted" style="max-width:560px;margin:10px auto 0">
            Every feature is built around one goal: making Sri Lankan bus travel reliable, comfortable, and completely hassle-free.
        </p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="about-feature-card">
                <div class="about-feature-icon" style="background:#fff0e8;color:var(--qs-orange)">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>
                <h5 class="fw-bold mb-2" style="color:var(--qs-navy)">Zero Double Bookings</h5>
                <p class="text-muted small mb-0" style="line-height:1.7">
                    Row-level database locking (<code>SELECT FOR UPDATE</code>) guarantees your seat the moment you confirm. No race conditions, no conflicts.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="about-feature-card">
                <div class="about-feature-icon" style="background:#fffbeb;color:var(--bmb-gold)">
                    <i class="bi bi-qr-code"></i>
                </div>
                <h5 class="fw-bold mb-2" style="color:var(--qs-navy)">Contactless QR Boarding</h5>
                <p class="text-muted small mb-0" style="line-height:1.7">
                    Receive a scannable QR e-ticket instantly after booking. Conductors verify in seconds — no paper tickets, no queues, no delays.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="about-feature-card">
                <div class="about-feature-icon" style="background:#e8f8f0;color:var(--qs-green)">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>
                <h5 class="fw-bold mb-2" style="color:var(--qs-navy)">All 9 Provinces Covered</h5>
                <p class="text-muted small mb-0" style="line-height:1.7">
                    From the Southern Expressway coastal belt to the Northern Province hill country — our routes span every major intercity corridor in Sri Lanka.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="about-feature-card">
                <div class="about-feature-icon" style="background:#eff6ff;color:#3B82F6">
                    <i class="bi bi-star-fill"></i>
                </div>
                <h5 class="fw-bold mb-2" style="color:var(--qs-navy)">Verified Passenger Reviews</h5>
                <p class="text-muted small mb-0" style="line-height:1.7">
                    Genuine ratings for driver quality, comfort, punctuality, and cleanliness — collected only from passengers who completed the journey.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="about-feature-card">
                <div class="about-feature-icon" style="background:#fef0f0;color:var(--qs-red)">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <h5 class="fw-bold mb-2" style="color:var(--qs-navy)">Flexible Cancellations</h5>
                <p class="text-muted small mb-0" style="line-height:1.7">
                    Cancel confirmed bookings directly from your account with instant seat release — the seat becomes available to other passengers immediately.
                </p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="about-feature-card">
                <div class="about-feature-icon" style="background:rgba(16,24,40,.06);color:var(--qs-navy)">
                    <i class="bi bi-phone-fill"></i>
                </div>
                <h5 class="fw-bold mb-2" style="color:var(--qs-navy)">Mobile-Responsive Design</h5>
                <p class="text-muted small mb-0" style="line-height:1.7">
                    Fully optimised for smartphones, tablets, and desktops — book your seat from anywhere across the island, even on a slow mobile connection.
                </p>
            </div>
        </div>
    </div>

    <!-- ── Luxury Fleet ────────────────────────────────────── -->
    <div class="fleet-card mb-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <span style="display:inline-block;background:rgba(255,122,61,.15);border:1px solid rgba(255,122,61,.25);color:var(--qs-peach);padding:4px 14px;border-radius:99px;font-size:.72rem;font-weight:700;letter-spacing:.5px;text-transform:uppercase;margin-bottom:16px">Premium Fleet</span>
                <h3 style="color:#fff;font-weight:800;letter-spacing:-0.4px;margin-bottom:12px">
                    Experience the Ultimate<br>Comfort in Island-Wide Travel
                </h3>
                <p style="color:rgba(255,255,255,.6);line-height:1.75;font-size:.9rem;margin-bottom:20px">
                    Our curated fleet of modern A/C luxury coaches features reclining seats, onboard entertainment, USB charging, and GPS-tracked routes — delivering a five-star journey experience from the coastal belts to the hill country.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach (['Air-Conditioned', 'Reclining Seats', 'USB Charging', 'GPS Tracked', 'CCTV Monitored'] as $feat): ?>
                        <span style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);color:rgba(255,255,255,.7);border-radius:20px;padding:4px 12px;font-size:.78rem;font-weight:500"><?php echo $feat; ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="row g-0 text-center">
                    <?php foreach ([
                        ['50+', 'Active Buses'],
                        ['120+', 'Daily Trips'],
                        ['25+', 'Routes'],
                        ['4.7★', 'Avg. Rating'],
                    ] as [$num, $label]): ?>
                        <div class="col-6 col-md-3">
                            <div class="fleet-stat">
                                <div class="number"><?php echo $num; ?></div>
                                <div class="label"><?php echo $label; ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="margin-top:20px;padding:16px;background:rgba(255,255,255,.05);border-radius:12px;border:1px solid rgba(255,255,255,.08)">
                    <div style="font-size:.8rem;color:rgba(255,255,255,.5);line-height:1.6">
                        <i class="bi bi-check-circle-fill me-2" style="color:var(--qs-green)"></i>All operators are registered with the National Transport Commission of Sri Lanka<br>
                        <i class="bi bi-check-circle-fill me-2 mt-1" style="color:var(--qs-green)"></i>Drivers hold valid NTC licences with clean road safety records
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Popular Routes ─────────────────────────────────── -->
    <div class="text-center mb-4">
        <span class="section-label">Island-Wide Coverage</span>
        <h2 class="fw-bold" style="color:var(--qs-navy);letter-spacing:-0.4px">Our Most Popular Routes</h2>
    </div>

    <div class="row g-3 mb-5">
        <?php
        $routes = [
            ['Colombo', 'Kandy', '3h 30m', 'A1 Expressway via Kadugannawa',    'bi-mountain',           '#3B82F6'],
            ['Colombo', 'Galle', '2h 15m', 'Southern Expressway — E01',         'bi-water',              '#27AE60'],
            ['Colombo', 'Jaffna', '7h 00m', 'A9 Highway via Vavuniya',          'bi-compass',            'var(--qs-orange)'],
            ['Colombo', 'Matara', '3h 00m', 'Southern Expressway via Hambantota','bi-signpost-2',        '#8B5CF6'],
            ['Kandy',   'Ella',   '4h 00m', 'Scenic B503 via Nuwara Eliya',     'bi-tree-fill',          '#27AE60'],
            ['Maharagama','Matara','2h 45m', 'Southern Expressway — E01',        'bi-bus-front',          'var(--bmb-gold)'],
        ];
        foreach ($routes as [$from, $to, $dur, $via, $icon, $color]):
        ?>
            <div class="col-md-6 col-lg-4">
                <div class="qs-card p-3 d-flex align-items-center gap-3" style="border-left:3px solid <?php echo $color; ?>">
                    <div style="width:40px;height:40px;border-radius:8px;background:rgba(0,0,0,.04);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.2rem;color:<?php echo $color; ?>">
                        <i class="bi <?php echo $icon; ?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div style="font-weight:700;color:var(--qs-navy);font-size:.9rem"><?php echo $from; ?> <span style="color:var(--qs-orange)">→</span> <?php echo $to; ?></div>
                        <div style="font-size:.74rem;color:var(--qs-text-muted);margin-top:2px"><?php echo $via; ?></div>
                    </div>
                    <div style="flex-shrink:0;text-align:right">
                        <div style="font-size:.78rem;font-weight:700;color:var(--qs-green)"><?php echo $dur; ?></div>
                        <div style="font-size:.7rem;color:var(--qs-text-muted)">avg. journey</div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Tech Stack / Built For ─────────────────────────── -->
    <div class="qs-card p-4 p-md-5 text-center" style="background:var(--qs-navy)">
        <i class="bi bi-cpu-fill" style="font-size:2.2rem;color:var(--qs-orange);display:block;margin-bottom:12px"></i>
        <h4 style="color:#fff;font-weight:800;margin-bottom:10px">Built on Enterprise-Grade Technology</h4>
        <p style="color:rgba(255,255,255,.5);max-width:600px;margin:0 auto;font-size:.88rem;line-height:1.7">
            BookMyBus LK is powered by PHP with a MySQL relational database, secured by ACID-compliant transactions, role-based access control, and CSRF-protected forms. Our infrastructure ensures your booking is guaranteed and your data remains private.
        </p>
        <div class="d-flex justify-content-center flex-wrap gap-3 mt-4">
            <?php foreach (['PHP 8', 'MySQL InnoDB', 'Bootstrap 5', 'ACID Transactions', 'CSRF Security', 'QR Verification'] as $tech): ?>
                <span style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.5);border-radius:20px;padding:5px 14px;font-size:.78rem;font-weight:500"><?php echo $tech; ?></span>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
