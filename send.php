<?php
// Приём заявок с сайта и отправка в Telegram.
// Токен бота и chat_id лежат в config.php (в репозиторий не попадает, см. config.example.php).
header('Content-Type: application/json; charset=utf-8');

function fail($code) { http_response_code($code); echo json_encode(['ok' => false]); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail(405);
if (!empty($_POST['website'])) { echo json_encode(['ok' => true]); exit; } // honeypot: бот

$config = __DIR__ . '/config.php';
if (!is_file($config)) fail(500);
require $config; // задаёт $TG_BOT_TOKEN и $TG_CHAT_ID

$clean = function ($key) {
    $v = isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
    return mb_substr(strip_tags($v), 0, 200);
};
$first = $clean('first_name'); $last = $clean('last_name'); $phone = $clean('phone');
$email = $clean('email'); $company = $clean('company'); $topic = $clean('topic');
if ($first === '' || $phone === '') fail(422);

// простое ограничение частоты: не чаще 1 заявки в 30 секунд с одного IP
$rl = sys_get_temp_dir() . '/ak_rl_' . md5($_SERVER['REMOTE_ADDR'] ?? '');
if (is_file($rl) && time() - filemtime($rl) < 30) fail(429);
touch($rl);

$text = "🆕 Новая заявка с сайта\n"
      . "Имя: $first $last\n"
      . "Телефон: $phone\n"
      . ($email !== '' ? "Email: $email\n" : '')
      . "Компания: $company\n"
      . "Тема: $topic";

$ch = curl_init("https://api.telegram.org/bot{$TG_BOT_TOKEN}/sendMessage");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => ['chat_id' => $TG_CHAT_ID, 'text' => $text],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
]);
$res = curl_exec($ch);
$ok = $res !== false && (json_decode($res, true)['ok'] ?? false);
curl_close($ch);

if (!$ok) fail(502);
echo json_encode(['ok' => true]);
