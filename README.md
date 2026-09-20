# ⚡ Knox Systems & Infrastructure Telemetry Portal

A high-performance infrastructure dashboard, live multiplayer server telemetry engine, and retro cyber arcade hub engineered by **Md. Abdullah Rahman (knox-akash)**[cite: 2, 7].

Designed specifically for San Andreas Multiplayer (SA-MP) and open.mp backend architectures, integrating raw binary socket queries, asynchronous status monitoring, and lightweight centralized administration[cite: 1, 7, 8, 10].

---

## 🌟 Key Features & Modules

### 1. Live Telemetry & Binary Socket Engine (`bsrp.php`, `samp.php`)
- **Direct UDP Handshake**: Interacts directly with the SA-MP daemon using raw network binary socket packets (`SAMP` opcode `i`, `r`, `c`) to fetch live player counts, tickrates, world time, and environmental state without lag[cite: 4, 10].
- **Dynamic Database Synchronization**: Connects to the remote MySQL database to pull live player registries, vehicle assets, and leaderboards[cite: 4].
- **Crash Offset Diagnostic Suite**: Integrated diagnostic tool to analyze standard client exception offsets (e.g., `0x0040FB80`, `0x007F0000`)[cite: 4].

### 2. Master Operations Console (`admin.php`)
- Centralized administrative console protected by session authentication to update global broadcast tickers, manage developer scratchpads, and track visitor metrics[cite: 1].
- Dynamic roadmap controller for **Project NewCity**[cite: 1, 8].

### 3. Cyber Arcade Studio 3D (`arcade.html`)
- Pure HTML5 Canvas and JavaScript arcade engine featuring 3 playable retro games[cite: 2]:
  - **OutRun Cyber Highway**: Pseudo-3D infinite highway cruiser with dynamic traffic and police interceptors[cite: 2].
  - **DDoS Defense Matrix**: Real-time packet-blocking defense simulator against SYN, UDP, and volumetric botnet spikes[cite: 2].
  - **Neon Hyper Stadium**: Fast-paced cyber rally pong with accelerating velocities[cite: 2].

### 4. Forensic Investigation Shell (`cipher.html`)
- Interactive cybersecurity terminal challenge simulating a memory dump breach investigation with multi-stage cryptography puzzles (Base64 decoding, database schema forensics, and memory offset inspection)[cite: 5].

### 5. Dynamic SVG Status Banner (`banner.php`)
- Generates a lightweight, cache-free vector SVG badge displaying real-time server availability and player counts suitable for embedding in external forums or markdown files[cite: 3].

---

## 🛠️ Tech Stack & Infrastructure

- **Backend**: PHP 8.x (Raw Sockets, cURL, JSON file persistence, MySQLi)[cite: 4, 6, 10]
- **Frontend**: HTML5, CSS Grid / Flexbox, Pure Vanilla JavaScript (No heavy frameworks)[cite: 2, 7]
- **Graphics**: HTML5 Canvas 2D Engine with CRT Scanlines & screen shake physics[cite: 2]
- **Target Protocol**: SA-MP 0.3.7-R2 / open.mp modern runtime[cite: 4, 7]
- **Hosting/Daemons**: Linux VPS (Ubuntu/Debian), systemd services, ClouDNS[cite: 1, 4, 7]

---

## 📁 Repository Structure

```text
├── admin.php          # Administrative control room & dynamic configuration editor[cite: 1]
├── arcade.html        # HTML5 Canvas 2D/3D retro arcade game hub[cite: 2]
├── banner.php         # Real-time dynamic SVG server status badge generator[cite: 3]
├── bsrp.php           # Advanced server telemetry, citizen lookup & diagnostic console[cite: 4]
├── cipher.html        # Interactive cryptographic terminal & breach investigation room[cite: 5]
├── config.php         # Centralized configuration helper reading from environment[cite: 11]
├── dispatch.php       # Contact message handler with webhook relay integration[cite: 6]
├── index.html         # Main portfolio, interactive terminal shell & systems showcase
├── newcity.html       # Deep-dive architecture specs & development roadmap for Project NewCity[cite: 8]
├── newcity.json       # JSON data source for Project NewCity roadmap & progress telemetry[cite: 9]
├── samp.php           # Lightweight JSON API endpoint querying server UDP packets[cite: 10]
├── LOGO WB.png        # Official brand emblem & favicon[cite: 1, 2, 4, 7]
├── .env.example       # Template for environment variables and secrets
└── .gitignore         # Prevents local credentials and logs from being committed
