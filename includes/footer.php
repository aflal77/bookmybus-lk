<?php
$base_path = $base_path ?? '';
?>
<footer class="qs-footer no-print mt-auto">
    <div class="container">

        <!-- Top section -->
        <div class="row g-5 pb-5" style="border-bottom:1px solid rgba(255,255,255,.08)">

            <!-- Brand col -->
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="brand-icon" style="width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,var(--qs-orange),#ff9a6c);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 14px rgba(255,122,61,.4)">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:22px;height:22px;fill:#fff">
                            <path d="M4 6C4 4.9 4.9 4 6 4h12c1.1 0 2 .9 2 2v8c0 1.1-.9 2-2 2H6c-1.1 0-2-.9-2-2V6zm2 0v8h12V6H6zM3 18h18v2H3v-2zm3-8h2v4H6v-4zm4 0h2v4h-2v-4zm4 0h2v4h-2v-4z"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-size:1.25rem;font-weight:800;color:#fff;letter-spacing:-0.3px;line-height:1">
                            BookMy<span style="color:var(--qs-orange)">Bus</span><sup class="brand-lk-chip">LK</sup>
                        </div>
                        <div style="font-size:.65rem;color:rgba(255,255,255,.4);letter-spacing:0.3px;margin-top:2px">
                            SRI LANKA'S TRUSTED BUS BOOKING PLATFORM
                        </div>
                    </div>
                </div>
                <p style="color:rgba(255,255,255,.5);font-size:.85rem;line-height:1.75;max-width:290px">
                    Real-time seat reservations, instant QR e-tickets, and safe intercity journeys connecting all 9 provinces of Sri Lanka.
                </p>
                <div class="d-flex align-items-center gap-2 mt-4" style="font-size:.82rem;color:rgba(255,255,255,.35)">
                    <i class="bi bi-shield-lock-fill" style="color:var(--qs-green)"></i>
                    ACID-secure transactions &bull; CSRF protected
                </div>
            </div>

            <!-- Quick links -->
            <div class="col-6 col-lg-2">
                <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.35);letter-spacing:1px;text-transform:uppercase;margin-bottom:16px">
                    Platform
                </div>
                <ul class="list-unstyled mb-0" style="display:flex;flex-direction:column;gap:10px">
                    <?php foreach ([
                        ['index.php', 'Search Buses'],
                        ['schedules.php', 'Schedules'],
                        ['routes.php', 'Route Map'],
                        ['destinations.php', 'Destinations'],
                        ['verify_ticket.php', 'Verify Ticket'],
                    ] as [$href, $label]): ?>
                        <li><a href="<?php echo $base_path . $href; ?>" style="color:rgba(255,255,255,.55);font-size:.85rem;text-decoration:none;transition:.2s" onmouseover="this.style.color='var(--qs-peach)'" onmouseout="this.style.color='rgba(255,255,255,.55)'"><?php echo $label; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Account links -->
            <div class="col-6 col-lg-2">
                <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.35);letter-spacing:1px;text-transform:uppercase;margin-bottom:16px">
                    Passengers
                </div>
                <ul class="list-unstyled mb-0" style="display:flex;flex-direction:column;gap:10px">
                    <?php foreach ([
                        ['register.php', 'Create Account'],
                        ['login.php', 'Sign In'],
                        ['my_bookings.php', 'My Bookings'],
                        ['profile.php', 'My Profile'],
                        ['faq.php', 'Help & FAQ'],
                    ] as [$href, $label]): ?>
                        <li><a href="<?php echo $base_path . $href; ?>" style="color:rgba(255,255,255,.55);font-size:.85rem;text-decoration:none;transition:.2s" onmouseover="this.style.color='var(--qs-peach)'" onmouseout="this.style.color='rgba(255,255,255,.55)'"><?php echo $label; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Contact block -->
            <div class="col-lg-4">
                <div style="font-size:.7rem;font-weight:700;color:rgba(255,255,255,.35);letter-spacing:1px;text-transform:uppercase;margin-bottom:16px">
                    Get in Touch
                </div>
                <div style="display:flex;flex-direction:column;gap:14px">
                    <div style="display:flex;align-items:flex-start;gap:12px">
                        <i class="bi bi-geo-alt-fill" style="color:var(--qs-orange);margin-top:2px;flex-shrink:0"></i>
                        <span style="color:rgba(255,255,255,.55);font-size:.84rem;line-height:1.55">Level 3, 86 Galle Road,<br>Colombo 03, Sri Lanka</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px">
                        <i class="bi bi-telephone-fill" style="color:var(--qs-orange);flex-shrink:0"></i>
                        <a href="tel:+94112345678" style="color:rgba(255,255,255,.55);font-size:.84rem;text-decoration:none">+94 11 234 5678</a>
                    </div>
                    <div style="display:flex;align-items:center;gap:12px">
                        <i class="bi bi-envelope-fill" style="color:var(--qs-orange);flex-shrink:0"></i>
                        <a href="mailto:support@bookmybus.lk" style="color:rgba(255,255,255,.55);font-size:.84rem;text-decoration:none">support@bookmybus.lk</a>
                    </div>
                </div>

                <!-- Popular routes chips -->
                <div style="margin-top:20px">
                    <div style="font-size:.7rem;font-weight:600;color:rgba(255,255,255,.25);text-transform:uppercase;letter-spacing:.8px;margin-bottom:8px">Popular Routes</div>
                    <div style="display:flex;flex-wrap:wrap;gap:6px">
                        <?php foreach (['Colombo → Kandy','Colombo → Galle','Colombo → Jaffna','Kandy → Ella','Galle → Matara'] as $r): ?>
                            <span style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.45);border-radius:20px;padding:3px 10px;font-size:.72rem"><?php echo $r; ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom bar -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 py-4">
            <div style="color:rgba(255,255,255,.3);font-size:.8rem">
                &copy; <?php echo date('Y'); ?> BookMyBus LK. All rights reserved.
            </div>
            <div style="display:flex;gap:20px">
                <a href="<?php echo $base_path; ?>faq.php" style="color:rgba(255,255,255,.3);font-size:.8rem;text-decoration:none">FAQ</a>
                <a href="<?php echo $base_path; ?>about.php" style="color:rgba(255,255,255,.3);font-size:.8rem;text-decoration:none">About</a>
                <a href="<?php echo $base_path; ?>contact.php" style="color:rgba(255,255,255,.3);font-size:.8rem;text-decoration:none">Contact</a>
            </div>
        </div>
    </div>
</footer>

<main id="page-top"></main>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV/XN/WPQ=" crossorigin=""></script>
</body>
</html>
