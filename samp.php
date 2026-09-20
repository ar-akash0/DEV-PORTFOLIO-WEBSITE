<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
require_once __DIR__ . '/config.php';

$ip = SAMP_SERVER_IP;
$port = SAMP_SERVER_PORT;

$fp = @fsockopen("udp://" . $ip, $port, $errno, $errstr, 2);

if (!$fp) {
    echo json_encode(["online" => false, "players" => 0, "maxplayers" => 50]);
    exit;
}

stream_set_timeout($fp, 2);

$packet = "SAMP";
foreach (explode('.', $ip) as $val) {
    $packet .= chr((int)$val);
}
$packet .= chr($port & 0xFF);
$packet .= chr(($port >> 8) & 0xFF);
$packet .= 'i';

fwrite($fp, $packet);
$response = fread($fp, 2048);
fclose($fp);

if ($response && strlen($response) >= 15) {
    $players = ord($response[12]) | (ord($response[13]) << 8);
    $maxplayers = ord($response[14]) | (ord($response[15]) << 8);
    
    if ($maxplayers > 1000 || $maxplayers == 0) {
        $players = ord($response[11]) | (ord($response[12]) << 8);
        $maxplayers = ord($response[13]) | (ord($response[14]) << 8);
    }

    echo json_encode([
        "online" => true,
        "players" => (int)$players,
        "maxplayers" => (int)$maxplayers
    ]);
} else {
    echo json_encode(["online" => false, "players" => 0, "maxplayers" => 50]);
}