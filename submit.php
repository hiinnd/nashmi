<?php
require 'vendor/autoload.php';


use PHPMailer\PHPMailer\Exception as MailerException;
use PHPMailer\PHPMailer\PHPMailer;
use Twilio\Rest\Client as TwilioClient;

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

function envValue(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value === false ? $default : trim((string) $value);
}

function redirectWithStatus(string $status): never
{
    if ($status === 'success') {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $_SESSION['show_success_modal'] = true;
    }

    header('Location: index.php?status=' . rawurlencode($status) . '#request', true, 303);
    exit;
}

function cleanHeaderValue(string $value): string
{
    return trim(str_replace(["\r", "\n"], ' ', $value));
}

function emailText(string $value): string
{
    return trim($value) !== '' ? trim($value) : 'Not provided';
}

function emailHtml(string $value): string
{
    return htmlspecialchars(emailText($value), ENT_QUOTES, 'UTF-8');
}

function logDeliveryIssue(string $channel, array $context): void
{
    $storage = __DIR__ . DIRECTORY_SEPARATOR . 'storage';
    if (!is_dir($storage)) {
        mkdir($storage, 0775, true);
    }

    unset($context['auth_token'], $context['smtp_password'], $context['account_sid']);
    $entry = [
        'time' => date('c'),
        'channel' => $channel,
        'context' => $context,
    ];

    file_put_contents($storage . DIRECTORY_SEPARATOR . 'delivery.log', json_encode($entry, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function buildRequestEmail(array $submission, array $brand): string
{
    $mapsUrl = trim($submission['maps_url'] ?? '');
    $rows = [
        'Customer' => [
            'Full Name' => $submission['name'] ?? '',
            'Phone Number' => $submission['phone'] ?? '',
            'Email Address' => $submission['email'] ?? '',
        ],
        'Car Information' => [
            'Make' => $submission['vehicle_make'] ?? '',
            'Model' => $submission['vehicle_model'] ?? '',
            'Year' => $submission['vehicle_year'] ?? '',
            'Vehicle' => $submission['vehicle'] ?? '',
            'VIN Number' => $submission['vin'] ?? '',
        ],
        'Service' => [
            'Service Needed' => $submission['service'] ?? '',
        ],
        'Location' => [
            'Street Address / Current Location' => $submission['location'] ?? '',
            'Latitude' => $submission['latitude'] ?? '',
            'Longitude' => $submission['longitude'] ?? '',
            'Accuracy meters' => $submission['location_accuracy_meters'] ?? '',
        ],
    ];

    if (($submission['service'] ?? '') === 'Battery Replacement (on-site)') {
        $rows['Service']['Appointment Date'] = $submission['appointment_date'] ?? '';
        $rows['Service']['Appointment Time'] = $submission['appointment_time'] ?? '';
    }

    $detailsHtml = '';
    foreach ($rows as $section => $items) {
        $detailsHtml .= '<tr><td style="padding:22px 0 10px;"><h2 style="margin:0;color:#07162d;font-size:18px;line-height:1.25;">' . htmlspecialchars($section, ENT_QUOTES, 'UTF-8') . '</h2></td></tr>';
        foreach ($items as $label => $value) {
            $detailsHtml .= '<tr><td style="padding:0 0 10px;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f7f8fa;border:1px solid #e7e9ee;border-radius:8px;"><tr><td style="padding:12px 14px;color:#667085;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;width:38%;">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</td><td style="padding:12px 14px;color:#111827;font-size:15px;line-height:1.45;font-weight:700;">' . emailHtml((string) $value) . '</td></tr></table></td></tr>';
        }
    }

    $mapsHtml = $mapsUrl !== ''
        ? '<a href="' . htmlspecialchars($mapsUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;padding:13px 18px;border-radius:8px;background:#df1f2d;color:#ffffff;text-decoration:none;font-weight:800;">Open Location Map</a>'
        : '<span style="color:#667085;">Map link was not provided.</span>';

    return '<!doctype html><html><body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111827;">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f3f4f6;"><tr><td align="center" style="padding:28px 14px;">'
        . '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;border-collapse:collapse;background:#ffffff;border:1px solid #e7e9ee;border-radius:14px;overflow:hidden;">'
        . '<tr><td style="padding:26px 30px 18px;border-bottom:1px solid #edf0f3;"><img src="https://www.nashmi-road.com/assets/images/nashmi-logo.png" width="150" alt="Nashmi" style="display:block;width:150px;max-width:58%;height:auto;margin:0 0 18px;"><p style="margin:0 0 8px;color:#df1f2d;font-size:12px;font-weight:900;letter-spacing:.18em;text-transform:uppercase;">New Roadside Request</p><h1 style="margin:0;color:#07162d;font-size:28px;line-height:1.15;">Nashmi Assistance Request</h1><p style="margin:12px 0 0;color:#5b6472;font-size:16px;line-height:1.55;">A customer submitted a roadside assistance request. Review the details below and contact them as soon as possible.</p></td></tr>'
        . '<tr><td style="padding:6px 30px 4px;">' . $detailsHtml . '</td></tr>'
        . '<tr><td style="padding:10px 30px 24px;"><h2 style="margin:0 0 10px;color:#07162d;font-size:18px;">Additional Details</h2><div style="padding:16px;border:1px solid #e7e9ee;border-radius:8px;background:#f7f8fa;color:#111827;font-size:15px;line-height:1.55;">' . nl2br(emailHtml($submission['notes'] ?? '')) . '</div></td></tr>'
        . '<tr><td style="padding:0 30px 28px;">' . $mapsHtml . '</td></tr>'
        . '<tr><td style="padding:16px 30px;background:#07162d;color:#cdd5df;font-size:12px;line-height:1.55;">Submitted at: ' . emailHtml($submission['created_at'] ?? '') . '<br>Website: ' . emailHtml($brand['website_url'] ?? $brand['name']) . '</td></tr>'
        . '</table></td></tr></table></body></html>';
}

function buildRequestEmailText(array $submission, array $brand): string
{
    $lines = [
        'New Nashmi roadside assistance request',
        '',
        'Customer',
        'Full Name: ' . ($submission['name'] ?: 'Not provided'),
        'Phone Number: ' . ($submission['phone'] ?: 'Not provided'),
        'Email Address: ' . ($submission['email'] ?: 'Not provided'),
        '',
        'Car Information',
        'Make: ' . ($submission['vehicle_make'] ?: 'Not provided'),
        'Model: ' . ($submission['vehicle_model'] ?: 'Not provided'),
        'Year: ' . ($submission['vehicle_year'] ?: 'Not provided'),
        'Vehicle: ' . ($submission['vehicle'] ?: 'Not provided'),
        'VIN Number: ' . ($submission['vin'] ?: 'Not provided'),
        '',
        'Service',
        'Service Needed: ' . ($submission['service'] ?: 'Not provided'),
    ];

    if ($submission['service'] === 'Battery Replacement (on-site)') {
        $lines = array_merge($lines, [
            'Battery Replacement Appointment Date: ' . ($submission['appointment_date'] ?: 'Not selected'),
            'Battery Replacement Appointment Time: ' . ($submission['appointment_time'] ?: 'Not selected'),
        ]);
    }

    $lines = array_merge($lines, [
        '',
        'Location',
        'Street Address / Current Location: ' . ($submission['location'] ?: 'Not provided'),
        'Latitude: ' . ($submission['latitude'] ?: 'Not provided'),
        'Longitude: ' . ($submission['longitude'] ?: 'Not provided'),
        'Accuracy meters: ' . ($submission['location_accuracy_meters'] ?: 'Not provided'),
        'Maps URL: ' . ($submission['maps_url'] ?: 'Not provided'),
        '',
        'Additional Details',
        $submission['notes'] ?: 'No additional notes.',
        '',
        'Submitted at: ' . $submission['created_at'],
        'Website: ' . $brand['website_url'],
    ]);

    return implode("\r\n", $lines);
}

function buildRequestSms(array $submission): string
{
    $lines = [
        'New Nashmi roadside assistance request',
        'Name: ' . ($submission['name'] ?: 'Not provided'),
        'Phone: ' . ($submission['phone'] ?: 'Not provided'),
        'Email: ' . ($submission['email'] ?: 'Not provided'),
        'Vehicle: ' . ($submission['vehicle'] ?: 'Not provided'),
        'VIN: ' . ($submission['vin'] ?: 'Not provided'),
        'Service: ' . ($submission['service'] ?: 'Not provided'),
    ];

    if ($submission['service'] === 'Battery Replacement (on-site)') {
        $lines[] = 'Appointment: ' . trim(($submission['appointment_date'] ?: 'Not selected') . ' ' . ($submission['appointment_time'] ?: ''));
    }

    $lines = array_merge($lines, [
        'Location: ' . ($submission['location'] ?: 'Not provided'),
        'Maps: ' . ($submission['maps_url'] ?: 'Not provided'),
        'Notes: ' . ($submission['notes'] ?: 'No additional notes.'),
    ]);

    return implode("\n", $lines);
}

function sendEmailNotification(array $submission, array $brand): array
{
    $requiredConfig = ['SMTP_HOST', 'SMTP_USERNAME', 'SMTP_PASSWORD', 'SMTP_PORT'];
    foreach ($requiredConfig as $key) {
        if (envValue($key) === '') {
            $error = 'Missing SMTP setting: ' . $key;
            logDeliveryIssue('email', ['to' => $brand['email'], 'error' => $error]);
            return ['sent' => false, 'error' => $error];
        }
    }

    if (!class_exists(PHPMailer::class)) {
        $error = 'PHPMailer is not installed. Run composer install.';
        logDeliveryIssue('email', ['to' => $brand['email'], 'error' => $error]);
        return ['sent' => false, 'error' => $error];
    }

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = envValue('SMTP_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = envValue('SMTP_USERNAME');
        $mail->Password = envValue('SMTP_PASSWORD');
        $mail->Port = (int) envValue('SMTP_PORT', '587');

        $secure = strtolower(envValue('SMTP_SECURE', 'tls'));
        if (in_array($secure, ['tls', 'ssl'], true)) {
            $mail->SMTPSecure = $secure;
        }

        $fromEmail = cleanHeaderValue(envValue('MAIL_FROM_ADDRESS', $brand['mail_from']));
        $fromName = cleanHeaderValue(envValue('MAIL_FROM_NAME', $brand['name'] . ' Website'));
        $toEmail = cleanHeaderValue(envValue('MAIL_TO_ADDRESS', $brand['email']));
        $customerEmail = filter_var($submission['email'], FILTER_VALIDATE_EMAIL) ? cleanHeaderValue($submission['email']) : $toEmail;
        $customerName = cleanHeaderValue($submission['name'] ?: 'Nashmi Customer');
        $service = cleanHeaderValue($submission['service'] ?: 'Roadside Assistance');
        $name = cleanHeaderValue($submission['name'] ?: 'New Customer');

       $mail->CharSet = 'UTF-8';

$mail->setFrom($fromEmail, $fromName);
$mail->addAddress($toEmail);
$mail->addReplyTo($customerEmail, $customerName);
$mail->Subject = 'New Nashmi Request - ' . $service . ' - ' . $name;

$mail->isHTML(true);
$mail->Body = buildRequestEmail($submission, $brand);
$mail->AltBody = buildRequestEmailText($submission, $brand);

        $mail->send();
        return ['sent' => true, 'error' => ''];
    } catch (MailerException|Throwable $e) {
        logDeliveryIssue('email', ['to' => $brand['email'], 'error' => $e->getMessage()]);
        return ['sent' => false, 'error' => $e->getMessage()];
    }
}

function sendSmsNotification(array $submission, array $brand): array
{
    $accountSid = envValue('TWILIO_ACCOUNT_SID');
    $authToken = envValue('TWILIO_AUTH_TOKEN');
    $fromNumber = envValue('TWILIO_FROM_NUMBER');
    $toNumber = envValue('TWILIO_TO_NUMBER', $brand['sms_to']);

    foreach ([
        'TWILIO_ACCOUNT_SID' => $accountSid,
        'TWILIO_AUTH_TOKEN' => $authToken,
        'TWILIO_FROM_NUMBER' => $fromNumber,
        'TWILIO_TO_NUMBER' => $toNumber,
    ] as $key => $value) {
        if ($value === '') {
            $error = 'Missing SMS setting: ' . $key;
            logDeliveryIssue('sms', ['to' => $toNumber ?: $brand['sms_to'], 'error' => $error]);
            return ['sent' => false, 'error' => $error];
        }
    }

    if (!class_exists(TwilioClient::class)) {
        $error = 'Twilio SDK is not installed. Run composer install.';
        logDeliveryIssue('sms', ['to' => $toNumber, 'error' => $error]);
        return ['sent' => false, 'error' => $error];
    }

    try {
        $client = new TwilioClient($accountSid, $authToken);
        $message = $client->messages->create($toNumber, [
            'from' => $fromNumber,
            'body' => buildRequestSms($submission),
        ]);

        return ['sent' => true, 'error' => '', 'message_sid' => $message->sid ?? null];
    } catch (Throwable $e) {
        logDeliveryIssue('sms', ['to' => $toNumber, 'from' => $fromNumber, 'error' => $e->getMessage()]);
        return ['sent' => false, 'error' => $e->getMessage()];
    }
}

loadEnvFile(__DIR__ . DIRECTORY_SEPARATOR . '.env');

if (in_array(strtolower(envValue('APP_DEBUG')), ['1', 'true', 'yes', 'on'], true)) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}



if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    redirectWithStatus('error');
}

$brand = [
    'name' => 'Nashmi',
    'email' => envValue('MAIL_TO_ADDRESS', 'nashmiroad@gmail.com'),
    'mail_from' => envValue('MAIL_FROM_ADDRESS', 'noreply@nashmi-road.com'),
    'sms_to' => envValue('TWILIO_TO_NUMBER', '+19099926466'),
    'website_url' => 'https://www.nashmi-road.com/',
];

$required = ['name', 'phone', 'vehicle_year', 'vehicle_make', 'vehicle_model', 'service', 'location'];
$errors = [];
foreach ($required as $field) {
    if (trim((string) ($_POST[$field] ?? '')) === '') {
        $errors[] = $field;
    }
}

$selectedService = trim((string) ($_POST['service'] ?? ''));
if ($selectedService === 'Battery Replacement (on-site)') {
    foreach (['appointment_date', 'appointment_time'] as $field) {
        if (trim((string) ($_POST[$field] ?? '')) === '') {
            $errors[] = $field;
        }
    }
}

$honeypotValue = trim((string) ($_POST['company_url'] ?? $_POST['website'] ?? ''));
if ($errors || $honeypotValue !== '') {
    logDeliveryIssue('validation', [
        'missing_fields' => $errors,
        'honeypot_filled' => $honeypotValue !== '',
        'posted_fields' => array_keys($_POST),
    ]);

    redirectWithStatus('error');
}

$submission = [
    'created_at' => date('c'),
    'name' => trim((string) ($_POST['name'] ?? '')),
    'phone' => trim((string) ($_POST['phone'] ?? '')),
    'email' => trim((string) ($_POST['email'] ?? '')),
    'vehicle_year' => trim((string) ($_POST['vehicle_year'] ?? '')),
    'vehicle_make' => trim((string) ($_POST['vehicle_make'] ?? '')),
    'vehicle_model' => trim((string) ($_POST['vehicle_model'] ?? '')),
    'vehicle' => trim((string) ($_POST['vehicle_year'] ?? '') . ' ' . (string) ($_POST['vehicle_make'] ?? '') . ' ' . (string) ($_POST['vehicle_model'] ?? '')),
    'vin' => strtoupper(trim((string) ($_POST['vin'] ?? ''))),
    'service' => trim((string) ($_POST['service'] ?? '')),
    'appointment_date' => trim((string) ($_POST['appointment_date'] ?? '')),
    'appointment_time' => trim((string) ($_POST['appointment_time'] ?? '')),
    'location' => trim((string) ($_POST['location'] ?? '')),
    'latitude' => trim((string) ($_POST['latitude'] ?? '')),
    'longitude' => trim((string) ($_POST['longitude'] ?? '')),
    'location_accuracy_meters' => trim((string) ($_POST['location_accuracy'] ?? '')),
    'maps_url' => trim((string) ($_POST['maps_url'] ?? '')),
    'notes' => trim((string) ($_POST['notes'] ?? '')),
];

$storage = __DIR__ . DIRECTORY_SEPARATOR . 'storage';
if (!is_dir($storage)) {
    mkdir($storage, 0775, true);
}

file_put_contents($storage . DIRECTORY_SEPARATOR . 'requests.jsonl', json_encode($submission, JSON_UNESCAPED_SLASHES) . PHP_EOL, FILE_APPEND | LOCK_EX);

$emailResult = sendEmailNotification($submission, $brand);

redirectWithStatus(
    $emailResult['sent']
        ? 'success'
        : 'delivery_error'
);
