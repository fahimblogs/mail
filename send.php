<?php
header('Content-Type: application/json');

function fail($message, $code = 400){
  http_response_code($code);
  echo json_encode(['ok' => false, 'message' => $message]);
  exit;
}
function ok($message){
  echo json_encode(['ok' => true, 'message' => $message]);
  exit;
}

// Minimal .env loader (no composer needed)
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
  $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $k = trim($k);
    $v = trim($v, " \t\n\r\0\x0B\"'");
    $_ENV[$k] = $v;
    $_SERVER[$k] = $v;
  }
}
function envv($k, $default=''){
  return $_ENV[$k] ?? $_SERVER[$k] ?? getenv($k) ?: $default;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail('Invalid method', 405);

$senderName  = trim($_POST['sender_name'] ?? '');
$senderAlias = trim($_POST['sender_alias'] ?? '');
$to          = trim($_POST['to'] ?? '');
$subject     = trim($_POST['subject'] ?? '');
$message     = trim($_POST['message'] ?? '');

if (!$senderName || !$senderAlias || !$to || !$subject || !$message) fail('Please fill all required fields.');
if (!preg_match('/^[a-zA-Z0-9._-]{2,40}$/', $senderAlias)) fail('Invalid alias format.');
if (!filter_var($to, FILTER_VALIDATE_EMAIL)) fail('Invalid recipient email.');

$fromDomain = 'fahim.pro.bd';
$fromEmail = $senderAlias . '@' . $fromDomain;
if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) fail('Invalid sender email.');

$brevoApiKey = envv('BREVO_API_KEY');
if (!$brevoApiKey) fail('BREVO_API_KEY missing in .env or server env.', 500);

// optional attachment
$attachments = [];
if (!empty($_FILES['attachment']['tmp_name']) && is_uploaded_file($_FILES['attachment']['tmp_name'])) {
  $size = (int)($_FILES['attachment']['size'] ?? 0);
  if ($size > 10 * 1024 * 1024) fail('Attachment too large (max 10MB).');

  $content = file_get_contents($_FILES['attachment']['tmp_name']);
  if ($content === false) fail('Could not read attachment.');
  $attachments[] = [
    'name' => basename($_FILES['attachment']['name']),
    'content' => base64_encode($content)
  ];
}

$payload = [
  'sender' => ['name' => $senderName, 'email' => $fromEmail],
  'to' => [['email' => $to]],
  'subject' => $subject,
  'htmlContent' => $message
];
if ($attachments) $payload['attachment'] = $attachments;

$ch = curl_init('https://api.brevo.com/v3/smtp/email');
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_POST => true,
  CURLOPT_HTTPHEADER => [
    'accept: application/json',
    'content-type: application/json',
    'api-key: ' . $brevoApiKey
  ],
  CURLOPT_POSTFIELDS => json_encode($payload),
  CURLOPT_TIMEOUT => 30
]);

$resBody = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($err) fail('Server cURL error: ' . $err, 500);

$json = json_decode($resBody, true);
if ($http >= 200 && $http < 300) {
  ok('Email sent successfully.');
}

$apiMsg = $json['message'] ?? 'Brevo API error.';
fail($apiMsg, $http ?: 500);