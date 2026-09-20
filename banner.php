<?php
header("Content-Type: image/svg+xml");
header("Cache-Control: no-cache, no-store, must-revalidate");
require_once __DIR__ . '/config.php';

$ip = SAMP_SERVER_IP;
$port = SAMP_SERVER_PORT;
$players = 0;
$maxplayers = 50;
$status = "OFFLINE";
$status_color = "#ef4444";

$fp = @fsockopen("udp://" . $ip, $port, $errno, $errstr, 1);
if ($fp) {
    stream_set_timeout($fp, 1);
    $packet = "SAMP";
    foreach (explode('.', $ip) as $val) { $packet .= chr((int)$val); }
    $packet .= chr($port & 0xFF) . chr(($port >> 8) & 0xFF) . 'i';
    fwrite($fp, $packet);
    $res = fread($fp, 2048);
    fclose($fp);

    if ($res && strlen($res) >= 15) {
        $status = "ONLINE";
        $status_color = "#00f5c4";
        $players = ord($res[12]) | (ord($res[13]) << 8);
        $maxplayers = ord($res[14]) | (ord($res[15]) << 8);
        if ($maxplayers > 1000 || $maxplayers == 0) {
            $players = ord($res[11]) | (ord($res[12]) << 8);
            $maxplayers = ord($res[13]) | (ord($res[14]) << 8);
        }
    }
}
?>
<svg xmlns="http://www.w3.org/2000/svg" width="450" height="90" viewBox="0 0 450 90">
  <defs>
    <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#0d1017;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#06070a;stop-opacity:1" />
    </linearGradient>
  </defs>
  <rect width="450" height="90" rx="10" fill="url(#grad)" stroke="#ffffff" stroke-opacity="0.1" stroke-width="1"/>
  <circle cx="25" cy="30" r="5" fill="<?= $status_color ?>" />
  <text x="40" y="34" font-family="'Courier New', monospace" font-size="14" font-weight="bold" fill="#ffffff">BSRP — Bengal State Roleplay</text>
  <text x="40" y="55" font-family="'Courier New', monospace" font-size="12" fill="#79869c">Host: <?= htmlspecialchars($ip . ':' . $port) ?></text>
  <text x="40" y="75" font-family="'Courier New', monospace" font-size="12" fill="<?= $status_color ?>"><?= $status ?> [<?= $players ?> / <?= $maxplayers ?> Players]</text>
  <text x="360" y="75" font-family="'Courier New', monospace" font-size="10" fill="#79869c">knox-akash</text>
</svg>