<?php

function telegram_error_log(string $message, array $context = []): void
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if ($context) {
        $line .= ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    $line .= PHP_EOL;
    @file_put_contents(__DIR__ . '/telegram_errors.log', $line, FILE_APPEND);
}

function load_bot_env(string $path): array
{
    if (!is_file($path)) {
        return [];
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return [];
    }

    $env = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $value = trim($value);
        // Remove quotes if present
        if (preg_match('/^["\'](.*)["\']$/', $value, $matches)) {
            $value = $matches[1];
        }
        $env[trim($key)] = $value;
    }

    return $env;
}

function send_telegram_message(string $chatId, string $message): array
{
    $env = load_bot_env(__DIR__ . '/.env');
    $token = trim((string) ($env['BOT_TOKEN'] ?? ''));
    if ($token === '' || $token === 'xxx') {
        telegram_error_log('BOT_TOKEN sozlanmagan', ['chat_id' => $chatId]);
        return [
            'success' => false,
            'status' => 0,
            'data' => null,
            'error' => 'BOT_TOKEN sozlanmagan',
        ];
    }

    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $payload = [
        'chat_id' => $chatId,
        'text' => $message,
    ];

    $responseBody = null;
    $httpCode = 0;
    $error = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 15,
        ]);

        $responseBody = curl_exec($ch);
        if ($responseBody === false) {
            $error = curl_error($ch);
        }
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'timeout' => 15,
            ],
        ]);

        $responseBody = @file_get_contents($url, false, $context);
        if ($responseBody === false) {
            $error = 'HTTP so\'rov bajarilmadi';
        }
        if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
            $httpCode = (int) $matches[1];
        }
    }

    $decoded = null;
    if (is_string($responseBody) && $responseBody !== '') {
        $decoded = json_decode($responseBody, true);
    }

    $ok = ($httpCode >= 200 && $httpCode < 300) && is_array($decoded) && (($decoded['ok'] ?? false) === true);
    if (!$ok) {
        telegram_error_log('Telegram yuborishda xatolik', [
            'chat_id' => $chatId,
            'status' => $httpCode,
            'error' => $error ?: (($decoded['description'] ?? 'Telegram xatoligi')),
        ]);
    }

    return [
        'success' => $ok,
        'status' => $httpCode,
        'data' => $decoded,
        'error' => $ok ? null : ($error ?: (($decoded['description'] ?? 'Telegram xatoligi'))),
    ];
}

if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    header('Content-Type: application/json; charset=utf-8');
    $chatId = trim((string) ($_POST['chat_id'] ?? ''));
    $message = trim((string) ($_POST['message'] ?? ''));

    if ($chatId === '' || $message === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'chat_id va message majburiy']);
        exit;
    }

    $result = send_telegram_message($chatId, $message);
    if (!$result['success']) {
        http_response_code(500);
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}
