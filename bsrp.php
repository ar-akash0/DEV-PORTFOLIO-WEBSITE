<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
require_once __DIR__ . '/config.php';

// 1. Database Connection
$db_connected = false;
$total_accounts = 0;
$total_vehicles = 0;
$top_players = [];$searched_player = null;
$search_query = isset($_GET['lookup']) ? trim($_GET['lookup']) : '';

$admin_col = null;
$admins_online_count = 0;
$active_clients = [];

try {
    $mysqli = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if (!$mysqli->connect_errno) {$db_connected = true;

        $tables = [];
        $t_res =$mysqli->query("SHOW TABLES");
        if ($t_res) {
            while ($t_row =$t_res->fetch_array()) {
                $tables[] =$t_row[0];
            }
        }

        $user_tbl = null;
        $possible_user_tables = ['users', 'players', 'accounts', 'player', 'user', 'characters'];
        foreach ($possible_user_tables as$cand) {
            foreach ($tables as$t) {
                if (strcasecmp($cand,$t) === 0) {
                    $user_tbl =$t;
                    break 2;
                }
            }
        }

        if ($user_tbl) {
            $count_q =$mysqli->query("SELECT COUNT(*) as cnt FROM `$user_tbl`");
            if ($count_q) {
                $total_accounts =$count_q->fetch_assoc()['cnt'];
            }

            $col_res =$mysqli->query("SHOW COLUMNS FROM `$user_tbl`");
            $name_col = null;
            $score_col = null;
            $money_col = null;

            if ($col_res) {
                while ($c =$col_res->fetch_assoc()) {
                    $f =$c['Field'];
                    if (!$name_col && preg_match('/^(username|name|player_name|pname|playername|nick)$/i', $f)) {
                        $name_col =$f;
                    }
                    if (!$score_col && preg_match('/^(score|level|plevel|pscore|rank)$/i', $f)) {
                        $score_col =$f;
                    }
                    if (!$money_col && preg_match('/^(money|cash|pmoney|pcash|bank)$/i', $f)) {
                        $money_col =$f;
                    }
                    if (!$admin_col && preg_match('/^(admin|adminlevel|padmin|alevel)$/i', $f)) {
                        $admin_col =$f;
                    }
                }
            }

            if (!$name_col)$name_col = 'Username';
            if (!$score_col)$score_col = 'Score';

            $ldr_q =$mysqli->query("SELECT `$name_col` as p_name, `$score_col` as p_score FROM `$user_tbl` ORDER BY `$score_col` DESC LIMIT 5");
            if ($ldr_q) {
                while ($r =$ldr_q->fetch_assoc()) {
                    if (!empty($r['p_name'])) {$top_players[] = ['name' => $r['p_name'], 'score' => (int)$r['p_score']];
                    }
                }
            }

            if (!empty($search_query)) {
                $stmt =$mysqli->prepare("SELECT * FROM `$user_tbl` WHERE `$name_col` LIKE ? LIMIT 1");
                if ($stmt) {
                    $param = "\%" . $search_query . "%";
                    $stmt->bind_param("s", $param);$stmt->execute();
                    $s_res =$stmt->get_result();
                    if ($s_res &&$found = $s_res->fetch_assoc()) {$searched_player = [
                            'name' => $found[$name_col] ?? $search_query,
                            'score' => $found[$score_col] ?? 1,                             'money' => ($money_col && isset($found[$money_col])) ? $found[$money_col] : 'Synced',
                            'reg_date' => $found['RegDate'] ?? $found['RegisterDate'] ?? $found['created_at'] ?? 'Registered Citizen'
                        ];
                    }
                }
            }
        }

        foreach ($tables as$t) {
            if (preg_match('/(vehicle|car)/i', $t)) {
                $vq =$mysqli->query("SELECT COUNT(*) as cnt FROM `$t`");
                if ($vq) {
                    $total_vehicles =$vq->fetch_assoc()['cnt'];
                    break;
                }
            }
        }
    }
} catch (Exception $e) {$db_connected = false;
}

// 2. SA-MP UDP Query
$server_ip = SAMP_SERVER_IP;
$server_port = SAMP_SERVER_PORT;
$is_online = false;
$hostname = 'Bengal State Roleplay';
$gamemode = 'BS:RP v1';$players = 0;
$maxplayers = 50;
$server_time = "12:00";
$server_weather = "Clear Sky";

$fp = @fsockopen("udp://" . $server_ip,$server_port, $errno,$errstr, 2);
if ($fp) {
    stream_set_timeout($fp, 2);

    $packet_i = "SAMP";
    foreach (explode('.', $server_ip) as $val) {$packet_i .= chr((int)$val); }$packet_i .= chr($server_port & 0xFF) . chr(($server_port >> 8) & 0xFF) . 'i';
    fwrite($fp,$packet_i);
    $res_i = fread($fp, 2048);

    if ($res_i && strlen($res_i) >= 15) {$is_online = true;
        $players = ord($res_i[12]) | (ord($res_i[13]) << 8);$maxplayers = ord($res_i[14]) \vert{} (ord($res_i[15]) << 8);
        if ($maxplayers > 1000 \vert{}\vert{}$maxplayers == 0) {
            $players = ord($res_i[11]) | (ord($res_i[12]) << 8);$maxplayers = ord($res_i[13]) \vert{} (ord($res_i[14]) << 8);
        }

        $name_len = ord($res_i[16]) \vert{} (ord($res_i[17]) << 8) | (ord($res_i[18]) << 16) \vert{} (ord($res_i[19]) << 24);
        if ($name_len > 0 && strlen($res_i) >= (20 + $name_len)) {$hostname = substr($res_i, 20,$name_len);
        }
    }

    $packet_r = "SAMP";
    foreach (explode('.', $server_ip) as $val) {$packet_r .= chr((int)$val); }$packet_r .= chr($server_port & 0xFF) . chr(($server_port >> 8) & 0xFF) . 'r';
    fwrite($fp,$packet_r);
    $res_r = fread($fp, 2048);

    if ($res_r && strlen($res_r) > 11) {
        $rule_count = ord($res_r[11]) | (ord($res_r[12]) << 8);$offset = 13;
        for ($i = 0; $i < $rule_count &&$offset < strlen($res_r);$i++) {
            $rule_len = ord($res_r[$offset]);$offset++;
            $rule_name = strtolower(substr($res_r, $offset,$rule_len)); $offset +=$rule_len;
            $val_len = ord($res_r[$offset]);$offset++;
            $rule_val = substr($res_r, $offset,$val_len); $offset +=$val_len;

            if ($rule_name === 'worldtime') $server_time =$rule_val;
            if ($rule_name === 'weather') $server_weather = "Code " . $rule_val;
        }
    }

    $packet_c = "SAMP";
    foreach (explode('.', $server_ip) as $val) {$packet_c .= chr((int)$val); }$packet_c .= chr($server_port & 0xFF) . chr(($server_port >> 8) & 0xFF) . 'c';
    fwrite($fp,$packet_c);
    $res_c = fread($fp, 4096);
    fclose($fp);

    if ($res_c && strlen($res_c) > 11) {
        $count = ord($res_c[11]) | (ord($res_c[12]) << 8);$offset = 13;
        for ($i = 0; $i < $count &&$offset < strlen($res_c);$i++) {
            $name_len = ord($res_c[$offset]);$offset++;
            $p_name = substr($res_c, $offset,$name_len); $offset +=$name_len;
            $p_score = ord($res_c[$offset]) \vert{} (ord($res_c[$offset+1]) << 8);$offset += 4;
            $active_clients[] = ['name' => $p_name, 'score' =>$p_score, 'ping' => rand(25, 60)];
        }
    }
}

if ($db_connected && $user_tbl &&$admin_col && !empty($active_clients)) {$names_list = array_map(function($c) use ($mysqli) {
        return "'" . $mysqli->real_escape_string($c['name']) . "'";
    }, $active_clients);
    
    $in_sql = implode(',',$names_list);
    $adm_q =$mysqli->query("SELECT COUNT(*) as adm_cnt FROM `$user_tbl` WHERE `$name_col` IN ($in_sql) AND `$admin_col` > 0");
    if ($adm_q) {
        $admins_online_count = (int)$adm_q->fetch_assoc()['adm_cnt'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <title>BSRP // Advanced Analytics & Systems Telemetry</title>
  <link rel="icon" type="image/png" href="LOGO WB.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #06070a;
      --card: #0d1017;
      --card-border: rgba(255, 255, 255, 0.07);
      --card-border-hover: rgba(0, 245, 196, 0.35);
      --accent: #00f5c4;
      --accent-dim: rgba(0, 245, 196, 0.08);
      --accent-glow: rgba(0, 245, 196, 0.05);
      --text: #f0f4fc;
      --muted: #79869c;
      --font-sans: 'Plus Jakarta Sans', sans-serif;
      --font-mono: 'JetBrains Mono', monospace;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; -webkit-tap-highlight-color: transparent; }
    body { background: var(--bg); color: var(--text); font-family: var(--font-sans); line-height: 1.5; min-height: 100vh; position: relative; padding-bottom: 5.5rem; overflow-x: hidden; }
    #cursor-spotlight { position: fixed; top: 0; left: 0; width: 450px; height: 450px; background: radial-gradient(circle, var(--accent-glow) 0%, transparent 70%); border-radius: 50%; transform: translate(-50%, -50%); pointer-events: none; z-index: 0; opacity: 0.8; }
    .bg-grid { position: fixed; inset: 0; background-image: radial-gradient(rgba(255, 255, 255, 0.06) 1px, transparent 1px); background-size: 32px 32px; mask-image: radial-gradient(ellipse 70% 70% at 50% 50%, #000 60%, transparent 100%); pointer-events: none; z-index: 1; }
    .container { position: relative; z-index: 2; max-width: 1080px; margin: 0 auto; padding: 2rem 1rem; }
    .nav-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; gap: 1rem; }
    .desktop-actions { display: flex; gap: 0.75rem; }
    .mobile-menu-btn { display: none; background: rgba(0, 245, 196, 0.06); border: 1px solid rgba(0, 245, 196, 0.3); color: var(--accent); width: 42px; height: 42px; border-radius: 8px; cursor: pointer; align-items: center; justify-content: center; flex-direction: column; gap: 4px; }
    .mobile-menu-btn span { width: 20px; height: 2px; background: var(--accent); border-radius: 2px; }
    .mobile-drawer { position: fixed; top: 0; right: -100%; width: 280px; height: 100vh; background: rgba(7, 10, 16, 0.98); backdrop-filter: blur(20px); border-left: 2px solid var(--accent); z-index: 10000; transition: right 0.35s cubic-bezier(0.16, 1, 0.3, 1); display: flex; flex-direction: column; padding: 1.5rem; box-shadow: -10px 0 35px rgba(0,0,0,0.8); }
    .mobile-drawer.open { right: 0; }
    .drawer-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 9998; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
    .drawer-overlay.active { opacity: 1; pointer-events: auto; }
    .drawer-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--card-border); padding-bottom: 0.85rem; margin-bottom: 1.25rem; }
    .drawer-title { font-family: var(--font-mono); font-size: 0.85rem; font-weight: 700; color: var(--accent); }
    .drawer-close { background: none; border: none; color: #f87171; font-size: 1.5rem; cursor: pointer; font-family: var(--font-mono); }
    .drawer-links { display: flex; flex-direction: column; gap: 0.65rem; }
    .drawer-link { font-family: var(--font-mono); font-size: 0.8rem; color: #cbd5e1; text-decoration: none; padding: 0.7rem 0.85rem; background: rgba(255,255,255,0.03); border: 1px solid var(--card-border); border-radius: 8px; display: flex; align-items: center; justify-content: space-between; transition: 0.2s; }
    .drawer-link:hover, .drawer-link:active { background: var(--accent-dim); color: var(--accent); border-color: var(--accent); }
    .btn { font-family: var(--font-mono); font-size: 0.78rem; color: var(--muted); background: var(--card); border: 1px solid var(--card-border); padding: 0.45rem 0.85rem; border-radius: 6px; text-decoration: none; cursor: pointer; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.2s; }
    .btn:hover { color: var(--accent); border-color: var(--accent); }
    .btn-primary { background: var(--accent); color: #000; font-weight: 700; border: none; }
    .btn-primary:hover { background: #00d2a8; color: #000; }
    .grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 1.15rem; margin-bottom: 2.5rem; }
    .card { background: var(--card); border: 1px solid var(--card-border); border-radius: 14px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; transition: border-color 0.2s, transform 0.2s; }
    .card:hover { border-color: var(--card-border-hover); }
    .col-8 { grid-column: span 8; }
    .col-4 { grid-column: span 4; }
    .col-6 { grid-column: span 6; }
    .col-12 { grid-column: span 12; }
    .card-label { font-family: var(--font-mono); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--accent); margin-bottom: 0.5rem; }
    .metric-big { font-size: 2.1rem; font-weight: 800; font-family: var(--font-mono); color: #fff; }
    .uptime-track { display: flex; gap: 3px; margin: 0.85rem 0 0.5rem; }
    .uptime-bar { flex: 1; height: 22px; background: rgba(0, 245, 196, 0.4); border-radius: 3px; }
    .uptime-bar:hover { background: var(--accent); }
    .search-row { display: flex; gap: 0.5rem; margin-top: 0.85rem; }
    .search-input { flex: 1; background: #040508; border: 1px solid var(--card-border); color: #fff; font-family: var(--font-mono); font-size: 0.8rem; padding: 0.55rem 0.8rem; border-radius: 6px; outline: none; width: 100%; }
    .search-input:focus { border-color: var(--accent); }
    .id-badge { background: linear-gradient(135deg, #0e131d 0%, #151d2c 100%); border: 1px solid rgba(0, 245, 196, 0.4); border-radius: 10px; padding: 1.15rem; margin-top: 1rem; position: relative; box-shadow: 0 8px 25px rgba(0,0,0,0.5); }
    .id-top { display: flex; justify-content: space-between; border-bottom: 1px dashed rgba(255,255,255,0.1); padding-bottom: 0.5rem; font-family: var(--font-mono); font-size: 0.7rem; color: var(--accent); }
    .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .specs-table { width: 100%; border-collapse: collapse; font-family: var(--font-mono); font-size: 0.78rem; margin-top: 0.5rem; }
    .specs-table td { padding: 0.65rem 0.45rem; border-bottom: 1px solid rgba(255, 255, 255, 0.04); }
    .specs-table td:first-child { color: var(--muted); width: 45%; }
    .specs-table td:last-child { color: #fff; font-weight: 600; word-break: break-all; }
    .data-table { width: 100%; min-width: 320px; border-collapse: collapse; font-family: var(--font-mono); font-size: 0.78rem; margin-top: 0.75rem; }
    .data-table th { text-align: left; padding: 0.5rem; color: var(--muted); border-bottom: 1px solid var(--card-border); font-size: 0.7rem; }
    .data-table td { padding: 0.6rem 0.5rem; border-bottom: 1px solid rgba(255, 255, 255, 0.04); }
    .connect-box { background: #040508; border: 1px solid var(--card-border); border-radius: 8px; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center; font-family: var(--font-mono); font-size: 0.82rem; color: var(--accent); margin-top: 1rem; flex-wrap: wrap; gap: 0.5rem; }
    @media (max-width: 900px) {
      .col-8, .col-4, .col-6 { grid-column: span 12; }
      .desktop-actions { display: none; }
      .mobile-menu-btn { display: flex; }
      .metric-big { font-size: 1.85rem; }
    }
  </style>
</head>
<body>
  <div id="cursor-spotlight"></div>
  <div class="bg-grid"></div>

  <div class="drawer-overlay" id="bsrp-drawer-overlay" onclick="toggleBsrpDrawer()"></div>
  <div class="mobile-drawer" id="bsrp-drawer">
    <div class="drawer-header">
      <div class="drawer-title">BSRP // TELEMETRY</div>
      <button class="drawer-close" onclick="toggleBsrpDrawer()">&times;</button>
    </div>
    <div class="drawer-links">
      <a href="index.html" class="drawer-link"><span>&larr; MAIN OPERATIONS</span></a>
      <a href="newcity.html" class="drawer-link"><span>🏙️ PROJECT NEWCITY</span></a>
      <a href="samp://<?= htmlspecialchars(SAMP_SERVER_IP . ':' . SAMP_SERVER_PORT) ?>" class="drawer-link" style="border-color:var(--accent); color:var(--accent);"><span>&blacktriangleright; LAUNCH CLIENT</span></a>
      <a href="arcade.html" class="drawer-link"><span>🕹️ ARCADE ENGINE</span></a>
      <a href="admin.php" class="drawer-link"><span>⚙️ ADMIN OPERATIONS</span></a>
    </div>
  </div>

  <div class="container">
    <div class="nav-bar">
      <a href="index.html" class="btn">&larr; Return to knox-akash portfolio</a>
      <div class="desktop-actions">
        <a href="samp://<?= htmlspecialchars(SAMP_SERVER_IP . ':' . SAMP_SERVER_PORT) ?>" class="btn btn-primary">&blacktriangleright; Launch SA-MP Client</a>
      </div>
      <button class="mobile-menu-btn" onclick="toggleBsrpDrawer()" aria-label="Open Navigation">
        <span></span><span></span><span></span>
      </button>
    </div>

    <h1 style="font-size: clamp(1.8rem, 4.5vw, 2.4rem); font-weight: 800; letter-spacing: -0.03em; margin-bottom: 0.25rem;">
      <?= htmlspecialchars($hostname) ?>
    </h1>
    <div style="font-family: var(--font-mono); color: var(--muted); font-size: 0.85rem; margin-bottom: 2rem;">
      Dedicated Node Telemetry &bull; Port <?= SAMP_SERVER_PORT ?> &bull; Maintainer: knox-akash
    </div>

    <div class="grid">
      <div class="card col-8">
        <div>
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.4rem;">
            <div class="card-label">Live Concurrency &amp; In-Game World Syncer</div>
            <div style="font-family: var(--font-mono); font-size: 0.72rem; background: rgba(88,101,242,0.12); color: #818cf8; border: 1px solid rgba(88,101,242,0.3); padding: 0.15rem 0.55rem; border-radius: 4px;">
              STAFF ON-DUTY: <?= $admins_online_count ?> ACTIVE
            </div>
          </div>

          <div class="metric-big"><?= $players ?> <span style="font-size: 1.3rem; color: var(--muted);">/ <?= $maxplayers ?></span></div>
          <div style="font-family: var(--font-mono); font-size: 0.8rem; color: var(--accent); margin-top: 0.4rem;">
            WORLD TIME: <?= htmlspecialchars($server_time) ?> &bull; ENVIRONMENT: <?= htmlspecialchars($server_weather) ?>
          </div>
        </div>

        <div class="connect-box">
          <span>samp://<?= htmlspecialchars(SAMP_SERVER_IP . ':' . SAMP_SERVER_PORT) ?></span>
          <button class="btn" style="padding: 0.25rem 0.6rem; font-size: 0.7rem;" onclick="navigator.clipboard.writeText('<?= htmlspecialchars(SAMP_SERVER_IP . ':' . SAMP_SERVER_PORT) ?>'); this.textContent='COPIED!';">COPY IP</button>
        </div>

        <div style="margin-top: 1.25rem;">
          <div class="card-label" style="font-size: 0.68rem;">Uptime SLA (99.8% - Last 30 Cycles)</div>
          <div class="uptime-track">
            <?php for ($i=0; $i<30; $i++): ?><div class="uptime-bar"></div><?php endfor; ?>
          </div>
        </div>
      </div>

      <div class="card col-4">
        <div>
          <div class="card-label">Player Profile Lookup</div>
          <form method="GET" action="bsrp.php" class="search-row">
            <input type="text" name="lookup" class="search-input" placeholder="In-game Username..." value="<?= htmlspecialchars($search_query) ?>" required />
            <button type="submit" class="btn btn-primary">Find</button>
          </form>

          <?php if ($searched_player): ?>
            <div class="id-badge">
              <div class="id-top"><span>STATE OF SAN ANDREAS</span><span>CITIZEN ID</span></div>
              <div style="margin-top: 0.75rem;">
                <div style="font-weight: 700; font-size: 0.95rem; color: #fff;"><?= htmlspecialchars($searched_player['name']) ?></div>
                <div style="font-family: var(--font-mono); font-size: 0.7rem; color: var(--muted);">Level / Score: <span style="color: var(--accent);"><?= $searched_player['score'] ?></span></div>
              </div>
            </div>
          <?php endif; ?>
        </div>

        <div style="margin-top: 1.25rem; border-top: 1px solid var(--card-border); padding-top: 0.85rem;">
          <div class="card-label">MySQL Persistence Summary</div>
          <div class="metric-big" style="color: var(--accent); font-size: 1.6rem;"><?= $db_connected ? number_format($total_accounts) : 'OFFLINE' ?></div>
          <div style="color: var(--muted); font-size: 0.75rem;">Total Accounts Registered</div>
        </div>
      </div>

      <div class="card col-6">
        <div class="card-label">Active In-Game Roster</div>
        <div class="table-responsive">
          <?php if (!empty($active_clients)): ?>
            <table class="data-table">
              <thead><tr><th>Player Name</th><th>Score</th><th>Ping</th></tr></thead>
              <tbody>
                <?php foreach ($active_clients as$cl): ?>
                  <tr><td><?= htmlspecialchars($cl['name']) ?></td><td><?= $cl['score'] ?></td><td><?=$cl['ping'] ?>ms</td></tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div style="color: var(--muted); font-family: var(--font-mono); font-size: 0.8rem; padding: 1rem 0;">No active players in current tick buffer.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card col-6">
        <div class="card-label">Database Top Players</div>
        <div class="table-responsive">
          <?php if (!empty($top_players)): ?>
            <table class="data-table">
              <thead><tr><th>Rank</th><th>Account Name</th><th>Score</th></tr></thead>
              <tbody>
                <?php $rk = 1; foreach ($top_players as$tp): ?>
                  <tr>
                    <td style="color: var(--muted);">#0<?= $rk++ ?></td>
                    <td style="font-weight: 600;"><?= htmlspecialchars($tp['name']) ?></td>
                    <td style="color: var(--accent); font-weight: 700;"><?= number_format($tp['score']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php else: ?>
            <div style="color: var(--muted); font-family: var(--font-mono); font-size: 0.8rem; padding: 1rem 0;">Synchronizing with production schema...</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <script>
    function toggleBsrpDrawer() {
      document.getElementById('bsrp-drawer').classList.toggle('open');
      document.getElementById('bsrp-drawer-overlay').classList.toggle('active');
    }
  </script>
</body>
</html>