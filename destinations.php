<?php

// BookMyBus LK – Sri Lankan Travel Destinations (destinations.php)
// Showcase of key regional transport terminals and express routes

require_once 'includes/auth.php';

$destinations = [
    [
        'name' => 'Colombo',
        'sinhala' => '',
        'title' => 'Commercial Capital & Central Transport Hub',
        'terminal' => 'Bastian Mawatha Private Bus Stand & Makumbura Multimodal Center',
        'routes' => 'Kandy, Galle, Matara, Jaffna, Kurunegala, Nuwara Eliya, Negombo',
        'desc' => 'The pulse of Sri Lanka\'s intercity transport network, offering expressway non-stop coaches to Southern and Central provinces.',
        'badge' => 'Major Hub'
    ],
    [
        'name' => 'Kandy',
        'sinhala' => '',
        'title' => 'Hill Capital & Cultural Heart of Sri Lanka',
        'terminal' => 'Goods Shed Bus Stand & Torrington Terminal',
        'routes' => 'Colombo, Nuwara Eliya, Ella, Galle, Kurunegala, Dambulla',
        'desc' => 'Gateway to the central highlands, tea plantations, and heritage sites with daily luxury services running via the expressway corridor.',
        'badge' => 'Highland Hub'
    ],
    [
        'name' => 'Galle',
        'sinhala' => '',
        'title' => 'Historic Southern Fortress & Coastal Hub',
        'terminal' => 'Galle Central Bus Terminal (Opposite Galle Railway Station)',
        'routes' => 'Colombo (Expressway), Matara, Kandy, Hambantota',
        'desc' => 'Connected by the Southern Expressway with rapid nonstop transit to Makumbura Kottawa in under 1 hour and 45 minutes.',
        'badge' => 'Expressway Line'
    ],
    [
        'name' => 'Jaffna',
        'sinhala' => '',
        'title' => 'Northern Provincial Cultural & Commercial Center',
        'terminal' => 'Jaffna Central Bus Stand, Hospital Road',
        'routes' => 'Colombo (Overnight Superline), Vavuniya, Anuradhapura',
        'desc' => 'Served by modern long-distance luxury sleeper buses operating daily through the A9 highway artery.',
        'badge' => 'Northern Line'
    ],
    [
        'name' => 'Nuwara Eliya',
        'sinhala' => '',
        'title' => 'Little England – Scenic Tea Country',
        'terminal' => 'Nuwara Eliya Central Bus Stand',
        'routes' => 'Colombo, Kandy, Badulla, Hatton',
        'desc' => 'High-altitude mountain transit coaches featuring panoramic viewing windows and climate-controlled interiors.',
        'badge' => 'Scenic Mountain'
    ],
    [
        'name' => 'Ella',
        'sinhala' => '',
        'title' => 'Highland Eco-Tourism & Hiking Capital',
        'terminal' => 'Ella Main Street Bus Stop',
        'routes' => 'Kandy, Colombo, Wellawaya, Badulla',
        'desc' => 'Popular international tourist hub seamlessly connected from Kandy and Colombo through lush mountain roads.',
        'badge' => 'Tourism Hotspot'
    ],
    [
        'name' => 'Matara',
        'sinhala' => '',
        'title' => 'Deep Southern Commercial Terminal',
        'terminal' => 'Matara Nilwala Intercity Bus Stand',
        'routes' => 'Colombo (Expressway), Galle, Hambantota, Kataragama',
        'desc' => 'The southern terminus of the E01 expressway, facilitating fast access to university campuses and coastal beaches.',
        'badge' => 'Expressway Line'
    ],
    [
        'name' => 'Anuradhapura',
        'sinhala' => '',
        'title' => 'Sacred Ancient Capital of Sri Lanka',
        'terminal' => 'Anuradhapura New Town Bus Stand',
        'routes' => 'Colombo, Kurunegala, Dambulla, Jaffna, Trincomalee',
        'desc' => 'Central junction connecting Western, North Central, and Northern transit routes for pilgrims and travelers.',
        'badge' => 'Cultural Heritage'
    ]
];

$page_title = "Sri Lankan Destinations & Terminals";
$active_page = "destinations";
include 'includes/header.php';
?>

<div class="container my-5">

    <div class="text-center mb-5">
        <span class="section-label mb-2"> </span>
        <h2 class="fw-bold text-dark">Explore Sri Lanka by Bus</h2>
        <p class="text-muted" style="max-width: 650px; margin: 0 auto;">Discover the primary intercity terminals, express transit corridors, and daily luxury services across Sri Lanka.</p>
    </div>

    <div class="row g-4">
        <?php foreach ($destinations as $d): ?>
            <div class="col-lg-6">
                <div class="card qs-card h-100 p-4">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <span class="section-label mb-1"><?php echo e($d['sinhala']); ?></span>
                            <h4 class="fw-bold text-dark mb-0"><?php echo e($d['name']); ?></h4>
                            <small class="text-primary fw-semibold"><?php echo e($d['title']); ?></small>
                        </div>
                        <span class="badge bg-warning text-dark px-3 py-1"><?php echo e($d['badge']); ?></span>
                    </div>

                    <p class="text-muted small mt-2 mb-3">
                        <?php echo e($d['desc']); ?>
                    </p>

                    <div class="bg-light p-3 rounded-3 border small mb-3">
                        <div class="mb-1">
                            <strong class="text-dark"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Main Terminal:</strong>
                            <span class="text-muted"><?php echo e($d['terminal']); ?></span>
                        </div>
                        <div>
                            <strong class="text-dark"><i class="bi bi-signpost-split-fill text-primary me-1"></i> Direct Connections:</strong>
                            <span class="text-muted"><?php echo e($d['routes']); ?></span>
                        </div>
                    </div>

                    <div class="mt-auto text-end">
                        <a href="index.php?destination=<?php echo urlencode($d['name']); ?>&search=1" class="btn btn-qs-primary btn-sm fw-bold">
                            <i class="bi bi-search me-1"></i> Find Buses to <?php echo e($d['name']); ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
