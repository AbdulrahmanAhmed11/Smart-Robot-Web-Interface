# Smart-Robot-Web-Interface
A full-stack IoT web interface to control an ESP32 robot and process voice commands via a local SQL database.

# 🤖 IoT Robot Web Controller & Voice Assistant

## 🔗 Quick Links
You can test and explore the interactive web dashboard live via the link below:
* [🌐 Live Control Panel Dashboard](https://eng-abdulrahman.kesug.com/Robot_Control_Panel/?i=1)
  > **Note for Evaluators:** The live link above allows you to fully test the Frontend UI (D-pad buttons and Speech-to-Text assistant) and observe database updates. For the physical hardware execution (ESP32), the system switches to a local network (LAN/XAMPP) architecture to bypass cloud-hosting bot-mitigation firewalls and ensure real-time M2M communication.
  
## 📌 Project Overview & Motivation
This project is a comprehensive, end-to-end Internet of Things (IoT) ecosystem built entirely from scratch as a personal engineering endeavor. It bridges the gap between modern web development and embedded systems, allowing for the real-time remote control of an **ESP32-based robot** via a responsive web dashboard. Furthermore, it integrates **Speech-to-Text capabilities**, allowing the system to capture and log Arabic voice commands for future natural language processing tasks.

**The core objective** of this project was to understand the complexities of full-stack integration—moving beyond standalone microcontrollers to create a distributed system where edge devices (ESP32) seamlessly communicate with a centralized database via a backend middleware, all with minimal latency.

---

## ✨ Key Features & Capabilities
* **Zero-Latency D-Pad Control:** A responsive HTML/CSS web interface featuring a directional pad. Commands are sent asynchronously to the server, ensuring the UI remains fluid without page reloads.
* **Voice Command Integration:** Utilizes the Web Speech API to capture user voice input (specifically tuned for Arabic), transcribing it into text and storing it securely in the database.
* **Single-Row State Machine (Database):** Engineered a highly optimized MySQL table structure for robot control that updates a single row continuously. This prevents database bloat and allows the microcontroller to query its state instantly.
* **Autonomous Hardware Polling:** The ESP32 is programmed in C++ to act as a resilient HTTP client, continuously polling the local server for movement updates and handling network drops gracefully.
* **Local Subnet Deployment:** Specifically designed to run on a local network (via XAMPP) to bypass the aggressive anti-bot protections of cloud hosting platforms, ensuring uninterrupted, high-speed machine-to-machine (M2M) communication.

---

## ⚙️ System Architecture (Deep Dive)

The system is built on a tightly coupled four-tier architecture:

### 1. The Client-Side (Frontend UI)
Built with **HTML5, CSS3, and JavaScript**. The dashboard features two main panels:
* **The Controller:** Uses JavaScript `fetch()` API (AJAX) to send GET requests containing directional parameters (`forward`, `backward`, `left`, `right`, `stop`) triggered by button clicks.
* **The Voice Assistant:** Leverages the native browser `SpeechRecognition` interface. Upon capturing audio, it translates speech to text and sends a POST request with a JSON/Form-data payload to the backend.

### 2. The Middleware (PHP Backend)
Acts as the secure bridge between the user interface and the database.
* `db.php`: Manages the MySQL connection using `mysqli` and explicitly sets the character set to `utf8mb4` to support Arabic characters.
* `save_direction.php`: Executes an `UPDATE` SQL query to overwrite the current robot state.
* `save_speech.php`: Executes an `INSERT` SQL query to append new voice command logs.
* `get_direction.php`: A minimalist endpoint designed specifically for the ESP32. It returns only raw text (e.g., "forward") to minimize parsing overhead on the microcontroller.

### 3. The Database Tier (MySQL)
Managed via **phpMyAdmin**. 
* **`robot_control` Table:** Uses a "State Machine" concept. Instead of logging every button press, it maintains only `id = 1`. This ensures the ESP32 query `SELECT direction FROM robot_control WHERE id = 1` executes in milliseconds.
* **`speech_data` Table:** An append-only log capturing `spoken_text` and timestamps for historical data analysis.

### 4. The Edge Device (ESP32 Hardware)
Programmed via the **Arduino IDE**. The ESP32 connects to the local Wi-Fi subnet and runs an infinite `loop()`. It utilizes the `HTTPClient` library to send GET requests to the `get_direction.php` endpoint every few hundred milliseconds, reading the payload and outputting the exact command to the Serial Monitor (ready to be mapped to GPIO pins for L298N motor drivers).

---

## 🛠️ Technology Stack & Rationale
* **Backend Environment:** XAMPP (Apache HTTP Server & MariaDB/MySQL).
  * *Why?* Deploying a local server guarantees ultra-low latency and avoids the restrictive firewalls (Cloudflare/Anti-Bot scripts) common on free web hosts that block automated HTTP requests from microcontrollers.
* **Backend Language:** PHP 8.x.
  * *Why?* Unmatched simplicity in processing HTTP requests and executing SQL queries rapidly.
* **Hardware:** ESP32 Development Board.
  * *Why?* Unlike the standard Arduino Uno which requires bulky Ethernet/Wi-Fi shields, the ESP32 features native, high-performance Wi-Fi capabilities and enough RAM to handle heavy HTTP/TCP stacks.
* **Programming Languages:** C++ (Embedded), JavaScript (Frontend), SQL (Database Management).

---

## ⚠️ Engineering Challenges & Troubleshooting

Building a system that traverses web protocols, databases, and bare-metal microcontrollers introduced several complex challenges:

### Challenge 1: Character Encoding & Data Corruption (The `?????` Bug)
* **The Problem:** Voice commands transcribed in Arabic were being saved in the MySQL database as strings of question marks (`????? ? ???`).
* **Root Cause:** A mismatch in character encoding. The database default collation was set to `latin1`, which cannot map complex Unicode characters (like Arabic script) coming from the UTF-8 HTML frontend.
* **The Solution:** 
  1. Altered the database and table collation to `utf8mb4_general_ci` via phpMyAdmin.
  2. Injected the `mysqli_set_charset($conn, "utf8mb4");` directive in the PHP connection file to ensure the entire data pipeline spoke the same Unicode language.

### Challenge 2: The Bot-Mitigation Block (The `aes.js` / 404 Error)
* **The Problem:** Initially deployed on a free cloud hosting service (InfinityFree). The ESP32 received HTML payloads containing `<script src="/aes.js">` instead of the expected raw text command.
* **Root Cause:** Free hosting platforms deploy aggressive browser-integrity checks. They force the client to execute a JavaScript challenge (aes.js) and set a cookie before granting access. The ESP32 is not a web browser; it cannot execute JS or handle complex cookie redirects, resulting in a blocked connection.
* **The Solution:** Pivoted the architecture to a **Local Area Network (LAN)** model using XAMPP. By hosting the backend on the local machine and pointing the ESP32 to the machine's local IPv4 address, all external cloud firewalls were bypassed, resulting in instant, unrestricted M2M communication.

### Challenge 3: Hardware Connection Refusals (`HTTP Code: -1`)
* **The Problem:** During the LAN migration, the ESP32 Serial Monitor reported `Error code: -1` (Connection Refused) and `404 Not Found`.
* **Root Cause:** 
  1. Windows Defender Firewall was blocking incoming TCP requests to the Apache HTTP Server port (Port 80).
  2. The IPv4 address used in the C++ code belonged to a Virtual Machine network adapter (VirtualBox) rather than the physical Wi-Fi adapter.
  3. Case sensitivity in the URL path (`task4` vs `Robot_Control_Panel`).
* **The Solution:** Configured inbound firewall rules to allow Apache (`httpd.exe`) on private networks, ran `ipconfig` to extract the correct Wireless LAN adapter IP, and rigidly sanitized the URL path in the C++ code to match the exact directory structure.

---

## 📸 Project Documentation (Media)

### 1. Web Dashboard & D-Pad Interface

<img width="1301" height="738" alt="Screenshot 2026-08-13 003222" src="https://github.com/user-attachments/assets/b0d631cf-574a-41d6-8997-ac3c67b63f18" />

The responsive Control Panel interface designed for seamless human-robot interaction.

### 2. Speech-to-Text Integration

<img width="1839" height="537" alt="Screenshot 2026-08-13 003200" src="https://github.com/user-attachments/assets/663b3fc8-f228-474f-8fa4-0e146b90c50f" />

Backend verification: demonstrating successful synchronization between the web-captured voice input and the MySQL storage layer.

### 3. ESP32 Serial Monitor Output

<img width="530" height="485" alt="Screenshot 2026-08-13 003256" src="https://github.com/user-attachments/assets/f7eb46fa-4309-402c-8c21-4e027260afd1" />

Hardware execution: The ESP32 successfully polling and decoding real-time movement commands from the local server.
