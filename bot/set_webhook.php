<?php
require_once __DIR__ . '/send.php';

$env = load_bot_env(__DIR__ . '/.env');
$token = $env['BOT_TOKEN'] ?? '';

if (!$token) {
    die("BOT_TOKEN topilmadi.");
}

$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$uri = str_replace('set_webhook.php', 'bot.php', $_SERVER['REQUEST_URI']);
$webhookUrl = "{$protocol}://{$host}{$uri}";

echo "Webhook URL: {$webhookUrl}\n\n";

$url = "https://api.telegram.org/bot{$token}/setWebhook?url=" . urlencode($webhookUrl);
$res = file_get_contents($url);

echo "Natija: " . $res;
?>
