<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once __DIR__ . '/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$message = isset($input['message']) ? trim($input['message']) : '';
$sender = isset($input['contact']) ? trim($input['contact']) : 'Anonymous Visitor';

if (empty($message)) {
    echo json_encode(["success" => false, "error" => "Empty message"]);
    exit;
}

$msg_file = __DIR__ . '/messages.json';
$messages = file_exists($msg_file) ? json_decode(file_get_contents($msg_file), true) : [];
if (!is_array($messages)) $messages = [];

$new_entry = [
    "id" => uniqid(),
    "sender" => htmlspecialchars($sender),
    "message" => htmlspecialchars($message),
    "ip" => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
    "time" => date('Y-m-d H:i:s')
];

array_unshift($messages, $new_entry);
if (count($messages) > 50) array_pop($messages);
file_put_contents($msg_file, json_encode($messages, JSON_PRETTY_PRINT));

if (defined('DISCORD_WEBHOOK') && DISCORD_WEBHOOK !== '') {
    $payload = json_encode([
        "username" => "Knox Relay",
        "embeds" => [[
            "title" => "New Dispatch Received",
            "color" => 62916,
            "fields" => [
                ["name" => "Sender", "value" => $sender, "inline" => true],
                ["name" => "Message", "value" => $message, "inline" => false]
            ]
        ]]
    ]);
    $ch = curl_init(DISCORD_WEBHOOK);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    @curl_exec($ch);
    curl_close($ch);
}

echo json_encode(["success" => true]);