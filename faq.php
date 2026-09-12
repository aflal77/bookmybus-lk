<?php

// BookMyBus LK – Frequently Asked Questions (faq.php)
// Help Center & Passenger Guide

require_once 'includes/auth.php';

$page_title = "Frequently Asked Questions (FAQ) & Help Center";
$active_page = "faq";
include 'includes/header.php';
?>

<div class="container my-5">

    <div class="text-center mb-5">
        <span class="section-label mb-2"> ‍</span>
        <h2 class="fw-bold text-dark">Frequently Asked Questions</h2>
        <p class="text-muted" style="max-width: 600px; margin: 0 auto;">Everything you need to know about booking, managing seats, digital QR tickets, and bus travel in Sri Lanka.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">

            <div class="accordion shadow-sm" id="mainFaqAccordion">

                <!-- Q1 -->
                <div class="accordion-item qs-card mb-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#q1">
                            <i class="bi bi-ticket-perforated-fill text-warning me-2"></i> How do I book an intercity bus seat online?
                        </button>
                    </h2>
                    <div id="q1" class="accordion-collapse collapse show" data-bs-parent="#mainFaqAccordion">
                        <div class="accordion-body text-muted">
                            Visit the <strong>BookMyBus LK Homepage</strong> or <strong>Schedules</strong> page, select your starting origin and destination terminal, pick your travel date, view available buses, click <strong>Select Seat</strong>, choose your preferred seat from the interactive 2x2 layout, fill in your passenger contact details, and confirm the booking. Your digital boarding pass with a QR code will be generated immediately.
                        </div>
                    </div>
                </div>

                <!-- Q2 -->
                <div class="accordion-item qs-card mb-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#q2">
                            <i class="bi bi-shield-lock-fill text-success me-2"></i> How does BookMyBus LK prevent double bookings?
                        </button>
                    </h2>
                    <div id="q2" class="accordion-collapse collapse" data-bs-parent="#mainFaqAccordion">
                        <div class="accordion-body text-muted">
                            BookMyBus LK uses ACID-compliant MySQL database transactions with row-level locking (<code>SELECT ... FOR UPDATE</code>). When you submit a booking, the system locks that specific seat row while processing your transaction. If two passengers attempt to reserve the same seat within milliseconds of each other, the first passenger's booking succeeds and commits, while the second transaction automatically rolls back with a clear warning to select another seat.
                        </div>
                    </div>
                </div>

                <!-- Q3 -->
                <div class="accordion-item qs-card mb-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#q3">
                            <i class="bi bi-x-circle-fill text-danger me-2"></i> Can I cancel my bus reservation?
                        </button>
                    </h2>
                    <div id="q3" class="accordion-collapse collapse" data-bs-parent="#mainFaqAccordion">
                        <div class="accordion-body text-muted">
                            Yes. Registered passengers can go to <strong>My Bookings</strong> and click the Cancel button for any eligible confirmed booking. The booking status is marked as cancelled, the payment is refunded, and the seat is immediately released back to the platform so another commuter can reserve it.
                        </div>
                    </div>
                </div>

                <!-- Q4 -->
                <div class="accordion-item qs-card mb-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#q4">
                            <i class="bi bi-qr-code-scan text-primary me-2"></i> How does the Conductor verify my digital ticket?
                        </button>
                    </h2>
                    <div id="q4" class="accordion-collapse collapse" data-bs-parent="#mainFaqAccordion">
                        <div class="accordion-body text-muted">
                            Every digital ticket contains a unique booking reference (e.g. <code>QS-2026-0001</code>) and an encrypted QR token. Conductors and station staff can scan the QR code using any smartphone camera or navigate to the <strong>Verify Ticket</strong> page to instantly confirm whether the ticket is valid, paid, and cleared for boarding.
                        </div>
                    </div>
                </div>

                <!-- Q5 -->
                <div class="accordion-item qs-card mb-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#q5">
                            <i class="bi bi-bag-check-fill text-info me-2"></i> What is the baggage allowance on Sri Lankan express buses?
                        </button>
                    </h2>
                    <div id="q5" class="accordion-collapse collapse" data-bs-parent="#mainFaqAccordion">
                        <div class="accordion-body text-muted">
                            Passengers are permitted one standard suitcase (up to 20kg) to be stored in the bus luggage compartment underneath, plus one small personal handbag or backpack inside the overhead passenger rack. Excess or oversized cargo may incur an additional terminal fee.
                        </div>
                    </div>
                </div>

                <!-- Q6 -->
                <div class="accordion-item qs-card mb-3">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#q6">
                            <i class="bi bi-credit-card-2-front-fill text-warning me-2"></i> What payment methods are accepted?
                        </button>
                    </h2>
                    <div id="q6" class="accordion-collapse collapse" data-bs-parent="#mainFaqAccordion">
                        <div class="accordion-body text-muted">
                            BookMyBus LK accepts Visa and MasterCard, eZ Cash and mCash mobile wallets, and Cash at Counter payments at participating bus stand terminals. All transactions are processed securely and recorded in your booking history for reference.
                        </div>
                    </div>
                </div>

            </div>

            <!-- Still Have Questions Card -->
            <div class="card qs-card p-4 text-center mt-5 bg-light border">
                <h5 class="fw-bold mb-1">Still have questions?</h5>
                <p class="text-muted small mb-3">Our passenger care team is available 24 hours a day to assist you.</p>
                <div>
                    <a href="contact.php" class="btn btn-qs-primary btn-sm fw-bold me-2">
                        <i class="bi bi-envelope me-1"></i> Send Support Inquiry
                    </a>
                    <a href="tel:+94112345678" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-telephone me-1"></i> Call +94 11 234 5678
                    </a>
                </div>
            </div>

        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
