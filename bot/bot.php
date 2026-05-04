<?php
/**
 * Telegram Bot Webhook Handler
 */

require_once __DIR__ . '/send.php';

// Get incoming update from Telegram
$content = file_get_contents("php://input");
$update = json_decode($content, true);

// Log update for debugging if needed
// file_put_contents(__DIR__ . '/bot_debug.log', $content . PHP_EOL, FILE_APPEND);

if (!$update || !isset($update['message'])) {
    exit;
}

$message = $update['message'];
$chatId = $message['chat']['id'] ?? null;
$text = $message['text'] ?? '';
$from = $message['from'] ?? [];
$firstName = $from['first_name'] ?? 'Foydalanuvchi';

if (!$chatId) {
    exit;
}

// Handle /start command
if (str_starts_with($text, '/start')) {
    $reply = "👋 Assalomu alaykum, {$firstName}!\n\n"
           . "🤖 IT Markaz tizim xabarnomalar botiga xush kelibsiz.\n\n"
           . "🆔 Sizning Telegram ID raqamingiz: `{$chatId}`\n\n"
           . "Ushbu ID raqamni admin panelda o'z profilingizga kiritib qo'ying.";
    
    send_telegram_message((string)$chatId, $reply);
} else {
    // Optional: handle other messages
    $reply = "Sizning Telegram ID: `{$chatId}`";
    send_telegram_message((string)$chatId, $reply);
}
