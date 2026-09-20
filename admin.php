<?php
session_start();
require_once __DIR__ . '/config.php';

$dataFile = __DIR__ . '/broadcast.json';
$msgFile = __DIR__ . '/messages.json';
$analyticsFile = __DIR__ . '/analytics.json';
$notesFile = __DIR__ . '/notes.txt';
$newcityFile = __DIR__ . '/newcity.json';

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([
        "active" => true,
        "text" => "BSRP Dedicated Node healthy • steady 60 FPS • ClouDNS routing verified.",
        "nodes" => ["web" => "ONLINE", "bsrp" => "ONLINE", "mysql" => "ONLINE", "bot" => "ONLINE"]
    ], JSON_PRETTY_PRINT));
}

$data = json_decode(file_get_contents($dataFile), true);
$messages = file_exists($msgFile) ? json_decode(file_get_contents($msgFile), true) : [];
$analytics = file_exists($analyticsFile) ? json_decode(file_get_contents($analyticsFile), true) : ["total_views" => 0];
$notes = file_exists($notesFile) ? file_get_contents($notesFile) : '';

$newcity = file_exists($newcityFile) ? json_decode(file_get_contents($newcityFile), true) : [
    "progress" => 30,
    "badge" => "30% COMPILED",
    "status_text" => "ARCHITECTURE & SCHEMA POOLING",
    "desc" => "Latest-generation SA-MP / open.mp core roleplay framework.",
    "db_cluster" => "db-cluster.example.com",
    "db_schema" => "s1651_newcity",
    "db_user" => "app_user",
    "drive_link" => "#",
    "roadmap" => []
];

if (isset($_GET['logout'])) {
    unset($_SESSION['knox_admin']);
    header('Location: admin.php');
    exit;
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_pass'])) {
    if ($_POST['login_pass'] === ADMIN_PASS) {
        $_SESSION['knox_admin'] = true;
    } else {
        $msg = "ভুল পাসওয়ার্ড!";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['knox_admin'])) {
    if (isset($_POST['update_broadcast'])) {
        $data['active'] = isset($_POST['active']);
        $data['text'] = trim($_POST['broadcast_text']);
        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));
        $msg = "ব্যানার কনফিগারেশন আপডেট হয়েছে!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>KNOX // Master Operations Console</title>
  <link rel="icon" type="image/png" href="LOGO WB.png" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body { background: #06070a; color: #f0f4fc; font-family: 'Plus Jakarta Sans', sans-serif; padding: 2rem 1rem; }
    .container { max-width: 800px; margin: 0 auto; }
    .card { background: #0d1017; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
    .input { width: 100%; background: #030406; border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; padding: 0.65rem 0.85rem; color: #fff; font-family: 'JetBrains Mono', monospace; margin-bottom: 0.75rem; box-sizing: border-box; }
    .btn { background: #00f5c4; color: #000; border: none; font-weight: 700; font-family: 'JetBrains Mono', monospace; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; }
  </style>
</head>
<body>
  <div class="container">
    <?php if (!isset($_SESSION['knox_admin'])): ?>
      <div class="card" style="max-width: 420px; margin: 4rem auto;">
        <h3 style="margin-bottom: 1rem; color: #00f5c4;">Master Authentication</h3>
        <?php if ($msg): ?><div style="color: #f87171; font-size: 0.85rem; margin-bottom: 0.5rem;"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
        <form method="POST">
          <input type="password" name="login_pass" class="input" placeholder="Enter master key..." required autofocus />
          <button type="submit" class="btn" style="width: 100%;">Authenticate &rarr;</button>
        </form>
      </div>
    <?php else: ?>
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="font-size: 1.25rem;">Operations Console</h2>
        <a href="admin.php?logout=1" class="btn" style="background: rgba(248,113,113,0.2); color: #f87171;">Logout</a>
      </div>
      <?php if ($msg): ?><div style="color: #00f5c4; margin-bottom: 1rem;"><?= htmlspecialchars($msg) ?></div><?php endif; ?>
      <form method="POST" class="card">
        <input type="hidden" name="update_broadcast" value="1" />
        <h4 style="margin-bottom: 0.75rem; color: #00f5c4;">Broadcast Marquee</h4>
        <textarea name="broadcast_text" class="input" style="height: 80px;"><?= htmlspecialchars($data['text'] ?? '') ?></textarea>
        <button type="submit" class="btn">Update Broadcast</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>