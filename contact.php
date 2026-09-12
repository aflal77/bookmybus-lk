<?php
require_once 'includes/auth.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please complete all required fields before sending.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $subject, $message);
            if (mysqli_stmt_execute($stmt)) {
                $success = true;
            } else {
                $error = "Your message could not be sent. Please try again or call our hotline.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

$page_title  = "Contact Us & Passenger Care";
$active_page = "contact";
include 'includes/header.php';
?>

<!-- ── Contact Page Hero Banner ────────────────────────────── -->
<div class="contact-hero-banner mx-3 mx-md-0" style="margin-top:28px;border-radius:0">
    <div style="position:relative;overflow:hidden;max-height:260px">
        <img src="https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=1600&q=80"
             alt="BookMyBus LK Luxury Coach on Sri Lanka Highway"
             class="img-fluid w-100"
             style="height:260px;object-fit:cover;object-position:center 45%;display:block">
        <div class="contact-hero-overlay">
            <div class="container">
                <div style="max-width:600px">
                    <span class="section-label mb-2">24/7 Passenger Care</span>
                    <h1 style="color:#fff;font-weight:800;font-size:clamp(1.7rem,3vw,2.3rem);letter-spacing:-0.5px;margin-bottom:8px">
                        We're Here to Help Your Journey
                    </h1>
                    <p style="color:rgba(255,255,255,.75);font-size:.92rem;margin:0;line-height:1.6">
                        Inquiries regarding bus schedules, seat bookings, baggage, or station locations across Sri Lanka? Reach out to our dedicated support team.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container" style="padding-top:44px;padding-bottom:72px">

    <div class="row g-4 align-items-stretch">
        
        <!-- Left Column: Contact Details & Info Cards -->
        <div class="col-lg-5">
            <div class="d-flex flex-column gap-3 h-100">

                <!-- Main Headquarters Card -->
                <div class="qs-card p-4">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="brand-icon" style="width:40px;height:40px;background:linear-gradient(135deg,var(--qs-orange),#ff9a6c);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.1rem;box-shadow:0 4px 12px rgba(255,122,61,.35)">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0" style="color:var(--qs-navy)">Headquarters & Operations</h5>
                            <span class="text-muted small">Colombo Central Office</span>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <a href="https://maps.google.com" target="_blank" class="contact-info-pill">
                            <div class="pill-icon" style="background:#FFF0E8;color:var(--qs-orange)">
                                <i class="bi bi-building"></i>
                            </div>
                            <div>
                                <div class="pill-label">Corporate Address</div>
                                <div class="pill-value">Level 3, 86 Galle Road, Colombo 03, Sri Lanka</div>
                            </div>
                        </a>

                        <a href="tel:+94112345678" class="contact-info-pill">
                            <div class="pill-icon" style="background:#E8F8F0;color:var(--qs-green)">
                                <i class="bi bi-telephone-inbound-fill"></i>
                            </div>
                            <div>
                                <div class="pill-label">24/7 Island-Wide Hotline</div>
                                <div class="pill-value">+94 11 234 5678 / +94 77 123 4567</div>
                            </div>
                        </a>

                        <a href="mailto:support@bookmybus.lk" class="contact-info-pill">
                            <div class="pill-icon" style="background:#EFF6FF;color:#3B82F6">
                                <i class="bi bi-envelope-at-fill"></i>
                            </div>
                            <div>
                                <div class="pill-label">Email Support</div>
                                <div class="pill-value">support@bookmybus.lk</div>
                            </div>
                        </a>

                        <div class="contact-info-pill">
                            <div class="pill-icon" style="background:#FFFBEB;color:var(--bmb-gold)">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div>
                                <div class="pill-label">Operating Schedule</div>
                                <div class="pill-value" style="font-size:.82rem;font-weight:500">
                                    Online Ticketing & Help Desk: 24/7<br>
                                    Office Desks: Mon–Sat 8:00 AM – 6:00 PM
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Regional Bus Terminal Desks -->
                <div class="qs-card p-4" style="background:linear-gradient(135deg,var(--qs-navy) 0%,#172033 100%);color:#fff">
                    <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color:#fff">
                        <i class="bi bi-pin-map-fill" style="color:var(--qs-orange)"></i> Regional Terminal Desks
                    </h6>
                    <div class="d-flex flex-column gap-2 small" style="color:rgba(255,255,255,.75)">
                        <div class="d-flex justify-content-between border-bottom pb-2" style="border-color:rgba(255,255,255,.1) !important">
                            <span><strong class="text-white">Colombo Bastian Mawatha:</strong></span>
                            <span>+94 11 232 4567</span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-2" style="border-color:rgba(255,255,255,.1) !important">
                            <span><strong class="text-white">Kandy Goods Shed:</strong></span>
                            <span>+94 81 223 4567</span>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-2" style="border-color:rgba(255,255,255,.1) !important">
                            <span><strong class="text-white">Galle Central Stand:</strong></span>
                            <span>+94 91 224 5678</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><strong class="text-white">Jaffna Main Stand:</strong></span>
                            <span>+94 21 222 3456</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Right Column: Interactive Contact Form -->
        <div class="col-lg-7">
            <div class="qs-card p-4 p-md-5 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <span class="section-label mb-2">Send a Message</span>
                            <h3 class="fw-bold mb-1" style="color:var(--qs-navy);letter-spacing:-0.4px">Get in Touch with Our Team</h3>
                            <p class="text-muted small mb-0">Fill in the details below and a passenger service agent will respond promptly.</p>
                        </div>
                        <div class="d-none d-sm-flex align-items-center justify-content-center" style="width:50px;height:50px;border-radius:var(--radius-lg);background:var(--qs-orange-light);color:var(--qs-orange);font-size:1.4rem">
                            <i class="bi bi-chat-left-dots-fill"></i>
                        </div>
                    </div>

                    <hr class="my-4" style="border-color:var(--qs-border)">

                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> <strong>Thank you!</strong> Your message has been sent to the BookMyBus LK passenger support team. We will respond within 2 to 4 hours.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <i class="bi bi-exclamation-circle-fill me-2"></i> <?php echo e($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="contact.php" novalidate>
                        <input type="hidden" name="send_message" value="1">

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="c_name" class="qs-label">Full Name <span class="text-danger">*</span></label>
                                <div class="qs-input-group">
                                    <i class="bi bi-person qs-input-icon"></i>
                                    <input type="text" class="qs-input" id="c_name" name="name" placeholder="Kasun Perera" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="c_email" class="qs-label">Email Address <span class="text-danger">*</span></label>
                                <div class="qs-input-group">
                                    <i class="bi bi-envelope qs-input-icon"></i>
                                    <input type="email" class="qs-input" id="c_email" name="email" placeholder="kasun@example.com" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="c_subject" class="qs-label">Subject / Route Query <span class="text-danger">*</span></label>
                            <div class="qs-input-group">
                                <i class="bi bi-tag qs-input-icon"></i>
                                <input type="text" class="qs-input" id="c_subject" name="subject" placeholder="e.g., Schedule inquiry for Colombo to Kandy Luxury Coach" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="c_message" class="qs-label">Your Message <span class="text-danger">*</span></label>
                            <textarea class="qs-input" id="c_message" name="message" rows="5" placeholder="How can we assist your journey across Sri Lanka? Please include booking references or route dates if applicable..." style="padding-top:.75rem;resize:vertical" required></textarea>
                        </div>

                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-2 small text-muted">
                                <i class="bi bi-shield-check text-success fs-6"></i>
                                <span>Your contact information is protected and private.</span>
                            </div>
                            <button type="submit" class="btn-qs-primary btn-qs-lg">
                                <i class="bi bi-send-fill"></i> Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include 'includes/footer.php'; ?>
