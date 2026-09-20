# ⚡ Knox Systems & Infrastructure Telemetry Portal

A high-performance infrastructure dashboard, live multiplayer server telemetry engine, and retro cyber arcade hub engineered by **Md. Abdullah Rahman (knox-akash)**.

Designed specifically for San Andreas Multiplayer (SA-MP) and open.mp backend architectures, integrating raw binary socket queries, asynchronous status monitoring, and lightweight centralized administration.

---

## 🌟 Key Features & Modules

### 1. Live Telemetry & Binary Socket Engine (`bsrp.php`, `samp.php`)
- **Direct UDP Handshake**: Interacts directly with the SA-MP daemon using raw network binary socket packets (`SAMP` opcode `i`, `r`, `c`) to fetch live player counts, tickrates, world time, and environmental state without lag.
- **Dynamic Database Synchronization**: Connects to the remote MySQL database to pull live player registries, vehicle assets, and leaderboards.
- **Crash Offset Diagnostic Suite**: Integrated diagnostic tool to analyze standard client exception offsets (e.g., `0x0040FB80`, `0x007F0000`).

### 2. Master Operations Console (`admin.php`)
- Centralized administrative console protected by session authentication to update global broadcast tickers, manage developer scratchpads, and track visitor metrics.
- Dynamic roadmap controller for **Project NewCity**.

### 3. Cyber Arcade Studio 3D (`arcade.html`)
- Pure HTML5 Canvas and JavaScript arcade engine featuring 3 playable retro games:
  - **OutRun Cyber Highway**: Pseudo-3D infinite highway cruiser with dynamic traffic and police interceptors.
  - **DDoS Defense Matrix**: Real-time packet-blocking defense simulator against SYN, UDP, and volumetric botnet spikes.
  - **Neon Hyper Stadium**: Fast-paced cyber rally pong with accelerating velocities.

### 4. Forensic Investigation Shell (`cipher.html`)
- Interactive cybersecurity terminal challenge simulating a memory dump breach investigation with multi-stage cryptography puzzles (Base64 decoding, database schema forensics, and memory offset inspection).

### 5. Dynamic SVG Status Banner (`banner.php`)
- Generates a lightweight, cache-free vector SVG badge displaying real-time server availability and player counts suitable for embedding in external forums or markdown files.

---

## 🛠️ Tech Stack & Infrastructure

- **Backend**: PHP 8.x (Raw Sockets, cURL, JSON file persistence, MySQLi)
- **Frontend**: HTML5, CSS Grid / Flexbox, Pure Vanilla JavaScript (No heavy frameworks)
- **Graphics**: HTML5 Canvas 2D Engine with CRT Scanlines & screen shake physics
- **Target Protocol**: SA-MP 0.3.7-R2 / open.mp modern runtime
- **Hosting/Daemons**: Linux VPS (Ubuntu/Debian), systemd services, ClouDNS

---

## 📁 Repository Structure

```text
├── admin.php          # Administrative control room & dynamic configuration editor
├── arcade.html        # HTML5 Canvas 2D/3D retro arcade game hub
├── banner.php         # Real-time dynamic SVG server status badge generator
├── bsrp.php           # Advanced server telemetry, citizen lookup & diagnostic console
├── cipher.html        # Interactive cryptographic terminal & breach investigation room
├── config.php         # Centralized configuration helper reading from environment
├── dispatch.php       # Contact message handler with webhook relay integration
├── index.html         # Main portfolio, interactive terminal shell & systems showcase
├── newcity.html       # Deep-dive architecture specs & development roadmap for Project NewCity
├── newcity.json       # JSON data source for Project NewCity roadmap & progress telemetry
├── samp.php           # Lightweight JSON API endpoint querying server UDP packets
├── LOGO WB.png        # Official brand emblem & favicon
├── .env.example       # Template for environment variables and secrets
└── .gitignore         # Prevents local credentials and logs from being committed
```
## 🚀 Getting Started Locally

### Prerequisites
- PHP 8.0 or higher with `php-sockets`, `php-mysqli`, and `php-curl` extensions enabled.
- A modern web browser.

### Installation

1. **Clone the repository**:
   ```bash
   git clone [https://github.com/ar-akash0/DEV-PORTFOLIO-WEBSITE.git](https://github.com/ar-akash0/DEV-PORTFOLIO-WEBSITE.git)
   cd DEV-PORTFOLIO-WEBSITE
2. **Configure Environment Variables**:
   Copy the example environment file:
   ```bash
   cp .env.example .env
Open .env (or configure config.php) and insert your database host, server IP, ports, and admin keys:

DB_HOST=127.0.0.1
DB_USER=your_db_user
DB_PASS=your_db_password
DB_NAME=your_db_name
DB_PORT=3306

SAMP_IP=127.0.0.1
SAMP_PORT=7777

ADMIN_PASSWORD=your_secure_password
DISCORD_WEBHOOK_URL=

3. Start the Local PHP Server:
   php -S 127.0.0.1:8000

4. Open your browser and navigate to:
   http://127.0.0.1:8000

---

Security Notice:
This repository contains sanitized code. No production passwords, private database credentials, or secret webhook tokens are tracked in this repository. Always keep your .env file listed inside .gitignore.

---

Developer:
Md. Abdullah Rahman (knox-akash)
- GitHub: https://github.com/ar-akash0
- YouTube: https://www.youtube.com/@knox-empiree
- Discord: https://discord.gg/NanDk8JbtZ

---

License:
This project is licensed under the MIT License.
