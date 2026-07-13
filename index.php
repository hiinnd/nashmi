<?php
function loadEnvFile(string $path): void
{
    if (!is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");
        if ($key !== '' && getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}

function isDebugEnabled(): bool
{
    return in_array(strtolower((string) getenv('APP_DEBUG')), ['1', 'true', 'yes', 'on'], true);
}

loadEnvFile(__DIR__ . DIRECTORY_SEPARATOR . '.env');

if (isDebugEnabled()) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

$brand = [
    'name' => 'Nashmi',
    'tagline' => 'Roadside help that gets you moving again.',
    'phone' => '(909) 992-6466',
    'phone_href' => 'tel:9099926466',
    'sms_to' => '+19099926466',
    'email' => 'nashmiroad@gmail.com',
    'mail_from' => 'noreply@nashmi-road.com',
    'email_href' => 'mailto:nashmiroad@gmail.com',
    'website_url' => 'https://www.nashmi-road.com/',
    'address' => 'Nashmi Roadside Assistance LLC, Butterfield Ranch Rd, Chino Hills, CA 91709, United States',
    'area' => 'California',
    'mark' => 'assets/images/n-mark-transparent.png',
    'logo' => 'assets/images/nashmi-logo.png',
];
$brand['maps_href'] = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($brand['address']);
$brand['gmail_href'] = 'https://mail.google.com/mail/?view=cm&fs=1&to=' . rawurlencode($brand['email']);
$brand['yahoo_href'] = 'https://compose.mail.yahoo.com/?to=' . rawurlencode($brand['email']);

$services = [
    ['icon' => 'fa-circle-dot', 'title' => 'Tire Change', 'value' => 'Tire Change', 'image' => 'assets/images/tire-change.jpg', 'text' => 'We swap your flat tire for your spare and help you get rolling safely.'],
    ['icon' => 'fa-screwdriver-wrench', 'title' => 'Tire Patch', 'value' => 'Tire Patch / Plug', 'image' => 'assets/images/tire-patch.webp', 'text' => 'Small puncture? We plug and patch eligible tires on-site so you can avoid a tow.'],
    ['icon' => 'fa-bolt', 'title' => 'Battery Jump', 'value' => 'Battery Jump Start', 'image' => 'assets/images/battery-jump.jpg', 'text' => 'Dead battery? We jump-start your vehicle and help you get back on the road.'],
    ['icon' => 'fa-car-battery', 'title' => 'Battery Replacement', 'value' => 'Battery Replacement (on-site)', 'image' => 'assets/images/battery-replacement.jpg', 'text' => 'Need a new battery? We bring it to you and install it right on the spot.', 'book' => true],
    ['icon' => 'fa-key', 'title' => 'Door Lockout', 'value' => 'Door Unlock / Lockout Service', 'image' => 'assets/images/door-lockout.jpg', 'text' => 'Locked your keys inside? We help unlock your vehicle without damage.'],
    ['icon' => 'fa-gas-pump', 'title' => 'Gas Delivery', 'value' => 'Gas / Fuel Delivery', 'image' => 'assets/images/gas-delivery.webp', 'text' => 'Ran out of fuel? We bring enough gas to get you to the nearest station.'],
];

$heroOpeningSlide = [
    'image' => 'assets/images/bmw-sunset-drive.jpg',
    'title' => 'Roadside Assistance',
];

$heroTowingSlide = [
    'image' => 'https://images.squarespace-cdn.com/content/v1/59eff42eb7411c1c8d06ca3d/1662919813202-35KAT68CXRLM4K7QEAXA/Roadside%2BAssistance%2BNear%2BMe%2C%2BTow%2BTruck%2BNear%2BMe%2C%2BTire%2BChange%2BNear%2BMe%2C%2BJump%2BStart%2Bnear%2Bme%2C%2Blocked%2Bkeys%2Bin%2Bcar%2C%2Bfuel%2Bdelivery%2Bnear%2Bme..jpg?format=1600w',
    'title' => 'Towing',
];

$heroSlides = array_merge([$heroOpeningSlide], array_map(static function ($service) {
    return [
        'image' => $service['image'],
        'title' => $service['title'],
    ];
}, $services), [$heroTowingSlide]);

$steps = [
    ['num' => '01', 'title' => 'Share the Situation', 'text' => 'Send your contact info, vehicle details, exact location, and what happened.'],
    ['num' => '02', 'title' => 'Get a Clear ETA', 'text' => 'We review the request, confirm the service, and give you a straightforward arrival window.'],
    ['num' => '03', 'title' => 'Meet the Technician', 'text' => 'The technician arrives prepared, checks the issue, and works to get you moving.'],
];

$reviews = [
    [
        ['name' => 'Michael R.', 'city' => 'Chino Hills, CA', 'text' => 'Fast response and very professional service. The technician arrived prepared and helped me get back on the road quickly.'],
        ['name' => 'Amanda K.', 'city' => 'Ontario, CA', 'text' => 'Clear communication from the first call. I shared my location and everything was handled smoothly.'],
    ],
    [
        ['name' => 'Jason M.', 'city' => 'Riverside, CA', 'text' => 'Reliable roadside help when I needed it most. The process was simple, direct, and stress-free.'],
        ['name' => 'Emily S.', 'city' => 'Corona, CA', 'text' => 'They were calm, respectful, and quick. I really appreciated the honest timing and clean service.'],
    ],
    [
        ['name' => 'David L.', 'city' => 'Irvine, CA', 'text' => 'Great experience. The request form made it easy to send the right details, and the service felt organized.'],
        ['name' => 'Rachel P.', 'city' => 'Anaheim, CA', 'text' => 'Helpful team and smooth roadside support. I would use Nashmi again if I needed assistance.'],
    ],
];
$serviceAreas = [
    'Orange County' => [
        'Aliso Viejo',
        'Anaheim',
        'Anaheim Hills',
        'Costa Mesa',
        'Cypress',
        'Dana Point',
        'Fountain Valley',
        'Fullerton',
        'Garden Grove',
        'Huntington Beach',
        'Irvine',
        'Laguna Beach',
        'Laguna Niguel',
        'Lake Forest',
        'Long Beach',
        'Mission Viejo',
        'Newport Beach',
        'Orange',
        'Orange County',
        'Placentia',
        'San Clemente',
        'Santa Ana',
        'Tustin',
        'Westminster',
        'Yorba Linda',
    ],
    'San Bernardino County' => [
        'Chino',
        'Chino Hills',
        'Fontana',
        'Montclair',
        'Ontario',
        'Rancho Cucamonga',
        'San Bernardino',
        'Upland',
    ],
    'Riverside County' => [
        'Corona',
        'Eastvale',
        'Jurupa Valley',
        'Moreno Valley',
        'Norco',
        'Riverside',
    ],
    'Los Angeles County' => [
        'Diamond Bar',
        'La Verne',
        'Pomona',
        'Walnut',
    ],
];


$formStatus = null;
$errors = [];

function cleanMailValue(string $value): string
{
    return trim(str_replace(["\r", "\n"], ' ', $value));
}

function buildRequestEmail(array $submission, array $brand): string
{
    $lines = [
        'New Nashmi roadside assistance request',
        '',
        'Customer',
        'Name: ' . ($submission['name'] ?: 'Not provided'),
        'Phone: ' . ($submission['phone'] ?: 'Not provided'),
        'Email: ' . ($submission['email'] ?: 'Not provided'),
        '',
        'Vehicle',
        'Vehicle: ' . ($submission['vehicle'] ?: 'Not provided'),
        'VIN: ' . ($submission['vin'] ?: 'Not provided'),
        '',
        'Service',
        'Service needed: ' . ($submission['service'] ?: 'Not provided'),
        'Appointment date: ' . ($submission['appointment_date'] ?: 'Not selected'),
        'Appointment time: ' . ($submission['appointment_time'] ?: 'Not selected'),
        '',
        'Location',
        'Address/location: ' . ($submission['location'] ?: 'Not provided'),
        'Latitude: ' . ($submission['latitude'] ?: 'Not provided'),
        'Longitude: ' . ($submission['longitude'] ?: 'Not provided'),
        'Accuracy meters: ' . ($submission['location_accuracy_meters'] ?: 'Not provided'),
        'Maps URL: ' . ($submission['maps_url'] ?: 'Not provided'),
        '',
        'Notes',
        $submission['notes'] ?: 'No additional notes.',
        '',
        'Submitted at: ' . $submission['created_at'],
        'Website: ' . $brand['website_url'],
    ];

    return implode(PHP_EOL, $lines);
}

function buildRequestSms(array $submission): string
{
    $lines = [
        'New Nashmi Request',
        'Name: ' . ($submission['name'] ?: 'Not provided'),
        'Phone: ' . ($submission['phone'] ?: 'Not provided'),
        'Email: ' . ($submission['email'] ?: 'Not provided'),
        'Vehicle: ' . ($submission['vehicle'] ?: 'Not provided'),
        'VIN: ' . ($submission['vin'] ?: 'Not provided'),
        'Service: ' . ($submission['service'] ?: 'Not provided'),
        'Appointment: ' . trim(($submission['appointment_date'] ?: 'Not selected') . ' ' . ($submission['appointment_time'] ?: '')),
        'Location: ' . ($submission['location'] ?: 'Not provided'),
        'Maps: ' . ($submission['maps_url'] ?: 'Not provided'),
        'Notes: ' . ($submission['notes'] ?: 'No additional notes.'),
    ];

    return implode("\n", $lines);
}

function sendEmailNotification(array $submission, array $brand): array
{
    $customerEmail = filter_var($submission['email'], FILTER_VALIDATE_EMAIL) ? cleanMailValue($submission['email']) : $brand['email'];
    $customerName = cleanMailValue($submission['name'] ?: 'Nashmi Customer');
    $subjectService = cleanMailValue($submission['service'] ?: 'Roadside Assistance');
    $subjectName = cleanMailValue($submission['name'] ?: 'New Customer');
    $mailSubject = 'New Nashmi Request - ' . $subjectService . ' - ' . $subjectName;
    $mailBody = buildRequestEmail($submission, $brand);
    $mailHeaders = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . cleanMailValue($brand['name']) . ' Website <' . cleanMailValue($brand['mail_from']) . '>',
        'Reply-To: ' . $customerName . ' <' . $customerEmail . '>',
        'X-Mailer: PHP/' . phpversion(),
    ];

    $sent = mail($brand['email'], $mailSubject, $mailBody, implode("\r\n", $mailHeaders));

    if (!$sent) {
        $lastError = error_get_last();
        logDeliveryIssue('email', [
            'to' => $brand['email'],
            'from' => $brand['mail_from'],
            'subject' => $mailSubject,
            'error' => $lastError['message'] ?? 'PHP mail() returned false.',
        ]);
    }

    return [
        'sent' => $sent,
        'to' => $brand['email'],
        'error' => $sent ? '' : 'PHP mail() returned false.',
    ];
}

function logDeliveryIssue(string $channel, array $context): void
{
    $storage = __DIR__ . DIRECTORY_SEPARATOR . 'storage';
    if (!is_dir($storage)) {
        mkdir($storage, 0775, true);
    }

    unset($context['auth_token'], $context['account_sid']);
    $entry = [
        'time' => date('c'),
        'channel' => $channel,
        'context' => $context,
    ];

    file_put_contents($storage . DIRECTORY_SEPARATOR . 'delivery.log', json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function sendSmsNotification(string $to, string $message): array
{
    try {
        $accountSid = getenv('TWILIO_ACCOUNT_SID') ?: '';
        $authToken = getenv('TWILIO_AUTH_TOKEN') ?: '';
        $fromNumber = getenv('TWILIO_FROM_NUMBER') ?: '';

        if (!$accountSid || !$authToken || !$fromNumber) {
            $result = ['sent' => false, 'configured' => false, 'error' => 'SMS provider is not configured.'];
            logDeliveryIssue('sms', ['to' => $to, 'from_configured' => (bool) $fromNumber, 'error' => $result['error']]);
            return $result;
        }

        if (!function_exists('curl_init')) {
            $result = ['sent' => false, 'configured' => true, 'error' => 'PHP cURL extension is not enabled.'];
            logDeliveryIssue('sms', ['to' => $to, 'from' => $fromNumber, 'error' => $result['error']]);
            return $result;
        }

        $ch = curl_init('https://api.twilio.com/2010-04-01/Accounts/' . rawurlencode($accountSid) . '/Messages.json');
        if ($ch === false) {
            throw new RuntimeException('Could not initialize cURL.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'From' => $fromNumber,
                'To' => $to,
                'Body' => $message,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $accountSid . ':' . $authToken,
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $decoded = is_string($response) ? json_decode($response, true) : null;
        $sent = $response !== false && $statusCode >= 200 && $statusCode < 300;
        $error = $curlError ?: ($decoded['message'] ?? ($response ?: 'SMS provider rejected the request.'));

        if (!$sent) {
            logDeliveryIssue('sms', [
                'to' => $to,
                'from' => $fromNumber,
                'status_code' => $statusCode,
                'twilio_code' => $decoded['code'] ?? null,
                'error' => $error,
            ]);
        }

        return [
            'sent' => $sent,
            'configured' => true,
            'status_code' => $statusCode,
            'message_sid' => $decoded['sid'] ?? null,
            'error' => $sent ? '' : $error,
        ];
    } catch (Throwable $e) {
        logDeliveryIssue('sms', ['to' => $to, 'error' => $e->getMessage()]);
        return ['sent' => false, 'configured' => true, 'error' => $e->getMessage()];
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $required = ['name', 'phone', 'vehicle_year', 'vehicle_make', 'vehicle_model', 'service', 'location'];
    foreach ($required as $field) {
        if (empty(trim($_POST[$field] ?? ''))) {
            $errors[] = $field;
        }
    }
    $selectedService = trim($_POST['service'] ?? '');
    if ($selectedService === 'Battery Replacement (on-site)') {
        foreach (['appointment_date', 'appointment_time'] as $field) {
            if (empty(trim($_POST[$field] ?? ''))) {
                $errors[] = $field;
            }
        }
    }

    if (!$errors && empty($_POST['website'] ?? '')) {
        $submission = [
            'created_at' => date('c'),
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'vehicle' => trim(($_POST['vehicle_year'] ?? '') . ' ' . ($_POST['vehicle_make'] ?? '') . ' ' . ($_POST['vehicle_model'] ?? '')),
            'vin' => strtoupper(trim($_POST['vin'] ?? '')),
            'service' => trim($_POST['service'] ?? ''),
            'appointment_date' => trim($_POST['appointment_date'] ?? ''),
            'appointment_time' => trim($_POST['appointment_time'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'latitude' => trim($_POST['latitude'] ?? ''),
            'longitude' => trim($_POST['longitude'] ?? ''),
            'location_accuracy_meters' => trim($_POST['location_accuracy'] ?? ''),
            'maps_url' => trim($_POST['maps_url'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''),
        ];

        $storage = __DIR__ . DIRECTORY_SEPARATOR . 'storage';
        if (!is_dir($storage)) {
            mkdir($storage, 0775, true);
        }

        file_put_contents($storage . DIRECTORY_SEPARATOR . 'requests.jsonl', json_encode($submission, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);

        $emailResult = sendEmailNotification($submission, $brand);
        $smsResult = sendSmsNotification($brand['sms_to'], buildRequestSms($submission));
        $formStatus = ($emailResult['sent'] && $smsResult['sent']) ? 'success' : 'delivery_error';
    } elseif ($errors) {
        $formStatus = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($brand['name']) ?> | Roadside Assistance in California</title>
    <meta name="description" content="<?= htmlspecialchars($brand['name']) ?> provides roadside assistance across California: tire change, tire patch, battery jump, battery replacement, lockout, and gas delivery.">
    <meta name="keywords" content="roadside assistance California, mobile battery replacement, tire change, battery jump start, car lockout, fuel delivery">
    <meta name="theme-color" content="#0b0b0d">
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($brand['mark']) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/style.css?v=27">
</head>
<body>
    <header class="site-header" id="header">
        <div class="container header-inner">
            <a class="brand" href="#home" aria-label="<?= htmlspecialchars($brand['name']) ?>">
                <span class="brand-mark"><img src="<?= htmlspecialchars($brand['mark']) ?>" alt="" aria-hidden="true"></span>
                <span class="brand-word">ashmi</span>
            </a>
            <nav class="nav" id="nav" aria-label="Main navigation">
                <a href="#services">Services</a>
                <a href="#process">How It Works</a>
                <a href="#reviews">Reviews</a>
                <a href="#request">Get Help</a>
                <a class="nav-call" href="<?= htmlspecialchars($brand['phone_href']) ?>"><i class="fa-solid fa-phone"></i><?= htmlspecialchars($brand['phone']) ?></a>
            </nav>
            <button class="menu-toggle" id="menuToggle" aria-label="Open menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>

    <main>
        <section class="hero" id="home">
            <div class="hero-slider">
                <?php foreach ($heroSlides as $index => $slide): ?>
                    <img
                        class="hero-slide <?= $index === 0 ? 'active' : '' ?>"
                        src="<?= htmlspecialchars($slide['image']) ?>"
                        alt="<?= htmlspecialchars($slide['title']) ?>"
                        <?= $index === 0 ? 'fetchpriority="high"' : 'loading="lazy"' ?>
                        data-hero-slide
                    >
                <?php endforeach; ?>
            </div>
            <div class="hero-shade"></div>
            <div class="container hero-content">
                <div class="hero-copy">
                    <div class="hero-badge"><i class="fa-solid fa-shield-halved"></i> Roadside Assistance - California</div>
                    <h1>Roadside help that gets you moving again.</h1>
                    <p>When car trouble interrupts your day, Nashmi keeps the next step simple: send your location, choose the service, and get connected with the fastest roadside support.</p>
               <div class="hero-service-note">
    <i class="fa-solid fa-clock"></i> 
    <div class="note-content">
        <span class="main-text">24/7 ROADSIDE ASSISTANCE</span>
        <span class="sub-text">• ORANGE COUNTY • INLAND EMPIRE • LOS ANGELES COUNTY</span>
    </div>
</div>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="#request">Request Help Now</a>
                        <a class="btn btn-ghost" href="<?= htmlspecialchars($brand['phone_href']) ?>"><i class="fa-solid fa-phone"></i><?= htmlspecialchars($brand['phone']) ?></a>
                        <a class="btn btn-ghost wide" href="#request" data-service="Battery Replacement (on-site)"><i class="fa-solid fa-car-battery"></i>Schedule Battery Replacement</a>
                    </div>
                </div>
                <img class="hero-logo" src="<?= htmlspecialchars($brand['logo']) ?>" alt="<?= htmlspecialchars($brand['name']) ?> Roadside Assistance LLC" fetchpriority="high">
            </div>
            <a class="scroll-cue" href="#services">See what we do <i class="fa-solid fa-chevron-down"></i></a>
        </section>

        <section class="section services" id="services">
            <div class="container">
                <div class="center-head">
                    <h2>Our Services</h2>
                    <p>Explore our selection of reliable roadside assistance services.</p>
                </div>
                <div class="services-grid">
                    <?php foreach ($services as $service): ?>
                        <article class="service-card" data-service-card="<?= htmlspecialchars($service['value']) ?>" tabindex="0">
                            <div class="service-media">
                                <img src="<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['title']) ?> service" loading="lazy">
                                <div class="service-media-shade"></div>
                                <div class="service-media-text">
                                    <div class="service-icon"><i class="fa-solid <?= htmlspecialchars($service['icon']) ?>"></i></div>
                                    <h3><?= htmlspecialchars($service['title']) ?></h3>
                                </div>
                            </div>
                            <div class="service-body">
                                <p><?= htmlspecialchars($service['text']) ?></p>
                                <div class="service-actions">
                                    <a class="service-action <?= !empty($service['book']) ? 'book-action' : '' ?>" href="#request" data-service="<?= htmlspecialchars($service['value']) ?>">
                                        <?= !empty($service['book']) ? 'Book Now' : 'Request Help' ?>
                                    </a>
                                    <a class="service-call" href="<?= htmlspecialchars($brand['phone_href']) ?>" aria-label="Call <?= htmlspecialchars($brand['phone']) ?>"><i class="fa-solid fa-phone"></i>Call</a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section process" id="process">
            <div class="container">
                <div class="center-head">
                    <h2>A clear path from request to arrival</h2>
                    <p>Simple steps, direct communication, and the details we need to reach you quickly.</p>
                </div>
                <div class="steps">
                    <?php foreach ($steps as $step): ?>
                        <article class="step">
                            <div class="step-num"><?= htmlspecialchars($step['num']) ?></div>
                            <h3><?= htmlspecialchars($step['title']) ?></h3>
                            <p><?= htmlspecialchars($step['text']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section reviews" id="reviews">
            <div class="container">
                <div class="center-head">
                    <h2>Trusted by Drivers Across Southern California.</h2>
                    <h2>What Our Customers Say</h2>
                </div>
                <div class="reviews-grid">
                    <?php foreach ($reviews as $reviewGroup): ?>
                        <article class="review-card" data-review-card>
                            <div class="stars">5 out of 5</div>
                            <div class="review-slider">
                                <?php foreach ($reviewGroup as $reviewIndex => $review): ?>
                                    <div class="review-slide <?= $reviewIndex === 0 ? 'active' : '' ?>" data-review-slide>
                                        <blockquote>"<?= htmlspecialchars($review['text']) ?>"</blockquote>
                                        <div class="review-author">
                                            <span><?= htmlspecialchars(substr($review['name'], 0, 1)) ?></span>
                                            <div>
                                                <strong><?= htmlspecialchars($review['name']) ?></strong>
                                                <small><i class="fa-solid fa-location-dot"></i><?= htmlspecialchars($review['city']) ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="section service-area" id="areas">
            <div class="container">
                <div class="center-head">
                    <span class="eyebrow">Service Area</span>
                    <h2>Serving </h2>
                    <h3> • ORANGE COUNTY   • INLAND EMPIRE </h3> <h3>  • LOS ANGELES COUNTY</h3>
                    <p>From Orange County to nearby Inland Empire and Los Angeles County cities - wherever you're stranded, we're on the way.</p>
                </div>
                <div class="area-list">
                    <?php foreach ($serviceAreas as $county => $cities): ?>
                        <section class="area-group">
                            <h3><?= htmlspecialchars($county) ?></h3>
                            <div class="cities">
                                <?php foreach ($cities as $city): ?>
                                    <span><?= htmlspecialchars($city) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="request" id="request">
            <div class="container request-grid">
                <aside class="request-info">
                    <span>Contact Nashmi</span>
                    <h2>Send the details and stay by your phone.</h2>
                    <p>Use the form to share the basics. For battery replacement, choose the appointment date and time that works for you.</p>
                    <a class="contact-line" href="<?= htmlspecialchars($brand['phone_href']) ?>"><i class="fa-solid fa-phone"></i><strong><?= htmlspecialchars($brand['phone']) ?></strong></a>
                    <div class="email-choice">
                        <button class="contact-line email-choice-toggle" type="button" aria-expanded="false" aria-controls="emailModal">
                            <i class="fa-solid fa-envelope"></i><strong><?= htmlspecialchars($brand['email']) ?></strong>
                        </button>
                    </div>
                    <a class="contact-line" href="<?= htmlspecialchars($brand['maps_href']) ?>" target="_blank" rel="noopener"><i class="fa-solid fa-location-dot"></i><strong><?= htmlspecialchars($brand['address']) ?></strong></a>
                    <div class="badges">
                        <span><i class="fa-solid fa-clock"></i> 24/7 Availability</span>
                        <span><i class="fa-solid fa-shield-halved"></i> Trusted Teches</span>
                        <span><i class="fa-solid fa-location-crosshairs"></i>  GPS Location</span>
                        <span><i class="fa-solid fa-lock"></i> Private Request</span>
                    </div>
                </aside>

                <div class="form-panel">
                    <?php if ($formStatus === 'delivery_error'): ?>
                        <div class="alert error">Your request was saved, but email or text message delivery failed. Please call us directly.</div>
                    <?php elseif ($formStatus === 'error'): ?>
                        <div class="alert error">Please fill in all required fields before sending your request.</div>
                    <?php endif; ?>

                    <form method="post" action="#request" id="helpForm" novalidate>
    <input type="text" name="website" tabindex="-1" autocomplete="off" class="honeypot" aria-hidden="true">
    
    <!-- قسم المعلومات الشخصية -->
    <div class="form-row">
        <label for="fname">Full Name <span>*</span><input type="text" id="fname" name="name" placeholder="John Smith" required autocomplete="name"></label>
        <label for="phone">Phone Number <span>*</span><input type="tel" id="phone" name="phone" placeholder="(555) 000-0000" maxlength="14" pattern="\([0-9]{3}\) [0-9]{3}-[0-9]{4}" required autocomplete="tel"></label>
    </div>
    <label for="email">Email Address<input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email"></label>

    <!-- قسم معلومات السيارة -->
    <h3>Car Information</h3>
    <div class="form-row three">
        <label for="make">Make <span>*</span><select name="vehicle_make" id="make" required><option value="">Make</option></select></label>
        <label for="model">Model <span>*</span><select name="vehicle_model" id="model" required><option value="">Select make first</option></select></label>
        <label for="year">Year <span>*</span><select name="vehicle_year" id="year" required><option value="">Year</option></select></label>
    </div>
    
    <label for="vin">VIN Number <strong>(optional)</strong>
        <input type="text" id="vin" name="vin" placeholder="E.G. 1HGBH41JXMN109186" maxlength="17" autocomplete="off">
        <small class="field-hint">17-character Vehicle Identification Number - helps us look up your exact vehicle.</small>
    </label>

    <!-- باقي الفورم -->
    <label for="service">Service Needed <span>*</span>
        <select name="service" id="service" required>
            <option value="">What do you need help with?</option>
            <?php foreach ($services as $service): ?>
                <option value="<?= htmlspecialchars($service['value']) ?>"><?= htmlspecialchars($service['value']) ?></option>
            <?php endforeach; ?>
            <option value="Other">Other - I'll describe below</option>
        </select>
    </label>

    <div class="appointment-fields" id="appointmentFields" hidden>
        <div class="appointment-head">
            <i class="fa-solid fa-calendar-check"></i>
            <div>
                <strong>Battery Replacement Appointment</strong>
                <small>Choose the day and time for on-site installation.</small>
            </div>
        </div>
        <div class="form-row">
            <label for="appointmentDate">Appointment Date <span>*</span><input type="date" id="appointmentDate" name="appointment_date"></label>
            <label for="appointmentTime">Appointment Time <span>*</span><input type="time" id="appointmentTime" name="appointment_time"></label>
        </div>
    </div>

    <label for="location">Street Address / Current Location <span>*</span>
        <div class="location-field">
            <input type="text" name="location" id="location" placeholder="Street address, cross street, or landmark" required autocomplete="street-address">
            <button type="button" id="gpsBtn" aria-label="Use my current location"><i class="fa-solid fa-location-crosshairs"></i></button>
        </div>
        <input type="hidden" name="latitude" id="latitude">
        <input type="hidden" name="longitude" id="longitude">
        <input type="hidden" name="location_accuracy" id="locationAccuracy">
        <input type="hidden" name="maps_url" id="mapsUrl">
        <small id="gpsStatus"></small>
    </label>
    <label for="notes">Additional Details<textarea name="notes" id="notes" rows="4" placeholder="Highway exit number, nearby landmarks, anything else that'll help us find you faster..."></textarea></label>
    <button class="btn submit-btn" type="submit" id="submitBtn"><i class="fa-solid fa-paper-plane"></i> Send Request</button>
    <p class="form-disclaimer"><i class="fa-solid fa-lock"></i> We'll call you within minutes. Your info is never shared.</p>
</form>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container footer-grid">
            <div>
                <a class="brand footer-brand" href="#home"><img class="footer-logo" src="<?= htmlspecialchars($brand['logo']) ?>" alt="<?= htmlspecialchars($brand['name']) ?> Roadside Assistance LLC" loading="lazy"></a>
                <p><?= htmlspecialchars($brand['tagline']) ?></p>
            </div>
            <div>
                <h3>Services</h3>
                <?php foreach ($services as $service): ?>
                    <span><?= htmlspecialchars($service['title']) ?></span>
                <?php endforeach; ?>
            </div>
            <div>
                <h3>Contact</h3>
                <a class="footer-contact-item" href="<?= htmlspecialchars($brand['phone_href']) ?>">
                    <i class="fa-solid fa-phone"></i>
                    <span><?= htmlspecialchars($brand['phone']) ?></span>
                </a>
                <button class="footer-contact-item footer-email email-choice-toggle" type="button" aria-expanded="false" aria-controls="emailModal">
                    <i class="fa-solid fa-envelope"></i>
                    <span><?= htmlspecialchars($brand['email']) ?></span>
                </button>
                <a class="footer-contact-item" href="<?= htmlspecialchars($brand['maps_href']) ?>" target="_blank" rel="noopener">
                    <i class="fa-solid fa-location-dot"></i>
                    <span><?= htmlspecialchars($brand['address']) ?></span>
                </a>
                <a class="footer-contact-item" href="#request">
                    <i class="fa-solid fa-screwdriver-wrench"></i>
                    <span>Request Assistance</span>
                </a>
            </div>
        </div>
        <div class="footer-bottom">&copy; <?= date('Y') ?> <?= htmlspecialchars($brand['name']) ?>. All rights reserved.</div>
    </footer>

    <div class="mobile-cta" aria-label="Quick contact actions">
        <a class="mobile-cta-call" href="<?= htmlspecialchars($brand['phone_href']) ?>"><i class="fa-solid fa-phone"></i> Call Now</a>
        <a class="mobile-cta-help" href="#request">Request Help</a>
    </div>

    <div class="email-modal" id="emailModal" hidden>
        <button class="email-modal-backdrop" type="button" data-email-close aria-label="Close email options"></button>
        <div class="email-modal-card" role="dialog" aria-modal="true" aria-labelledby="emailModalTitle">
            <button class="email-modal-close" type="button" data-email-close aria-label="Close email options"><i class="fa-solid fa-xmark"></i></button>
            <div class="email-modal-icon"><i class="fa-solid fa-envelope-open-text"></i></div>
            <h2 id="emailModalTitle">Choose how to email Nashmi</h2>
            <p><?= htmlspecialchars($brand['email']) ?></p>
            <div class="email-modal-options">
                <a href="<?= htmlspecialchars($brand['gmail_href']) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-google"></i><span>Gmail</span></a>
                <a href="<?= htmlspecialchars($brand['yahoo_href']) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-yahoo"></i><span>Yahoo Mail</span></a>
                <a href="<?= htmlspecialchars($brand['email_href']) ?>"><i class="fa-solid fa-envelope"></i><span>Email App</span></a>
            </div>
        </div>
    </div>

    <div class="request-success-modal" id="requestSuccessModal" <?= $formStatus === 'success' ? 'data-auto-open="true"' : 'hidden' ?>>
        <button class="request-success-backdrop" type="button" data-request-success-close aria-label="Close success message"></button>
        <div class="request-success-card" role="dialog" aria-modal="true" aria-labelledby="requestSuccessTitle">
            <button class="request-success-close" type="button" data-request-success-close aria-label="Close success message"><i class="fa-solid fa-xmark"></i></button>
            <div class="request-success-icon"><i class="fa-solid fa-check"></i></div>
            <h2 id="requestSuccessTitle">Success</h2>
            <p>Your request was sent to Nashmi by email and text message with the details you entered. We will contact you within minutes.</p>
            <button class="btn request-success-action" type="button" data-request-success-close>Done</button>
        </div>
    </div>

    <script src="assets/script.js?v=15"></script>
</body>
</html>
