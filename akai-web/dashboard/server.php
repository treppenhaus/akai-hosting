<?php
session_start();
$token = '$2y$10$yumC4x7Y0SpdlUfsCAEeUOrtNqNOkL2qFSkBBJA9Fg4Phm2jaazSW';

$id = "";
if (isset($_GET["id"])) {
    $id = htmlspecialchars($_GET["id"]);
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Panel</title>

    <script>
        let serverid = "<?php echo $id;?>";
        let token = "<?php echo $token;?>";
    </script>
    <script src="api.js"></script>
    <link rel="stylesheet" href="server.css">
    <style>
        .status-label {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .status-running {
            color: green;
        }
        .status-stopped {
            color: red;
        }
        .button-group .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <div class="content">
        <div class="header-section">
            <h1 id="server-name">Loading server...</h1>
            <p class="server-id">Server ID: <span id="server-id"><?php echo $id ?: "Unknown"; ?></span></p>
            <p class="status-label" id="server-status">Checking status...</p>

            <div class="button-group">
                <button class="btn start-btn" id="btn-start" onclick="startServer(serverid)">Start Server</button>
                <button class="btn stop-btn" id="btn-stop" onclick="stopServer(serverid)">Stop Server</button>
                <button class="btn console-btn" id="btn-console" onclick="window.open('http://localhost:3000/livelog.html')">Open Console</button>
            </div>
        </div>

        <div class="server-info">
            <div class="info-item">
                <div class="label">ID:</div>
                <div class="value" id="info-id">-</div>
            </div>
            <div class="info-item">
                <div class="label">UUID:</div>
                <div class="value" id="info-uuid">-</div>
            </div>
            <div class="info-item">
                <div class="label">Owner:</div>
                <div class="value" id="info-owner">-</div>
            </div>
            <div class="info-item">
                <div class="label">Port:</div>
                <div class="value" id="info-port">-</div>
            </div>
            <div class="info-item">
                <div class="label">Template:</div>
                <div class="value" id="info-template">-</div>
            </div>
            <div class="info-item">
                <div class="label">Preset Name:</div>
                <div class="value" id="info-preset-name">-</div>
            </div>
            <div class="info-item">
                <div class="label">Created:</div>
                <div class="value" id="info-created">-</div>
            </div>
            <div class="info-item">
                <div class="label">Creator:</div>
                <div class="value" id="info-creator">-</div>
            </div>
        </div>

        <a href="dash.php">← Back to Dashboard</a>

        <script>
            function updateServerInfo() {
                getServerInfo(serverid, (response) => {
                    if (!response || !response.success) return;
                    const data = response.data;
                    console.log(data);

                    // Update header with server name
                    const nickname = data.info?.nickname || "Unnamed Server";
                    document.getElementById("server-name").textContent = nickname;

                    // Update status label and button states
                    const running = data.running ?? false;
                    const statusLabel = document.getElementById("server-status");
                    const btnStart = document.getElementById("btn-start");
                    const btnStop = document.getElementById("btn-stop");

                    if (running) {
                        statusLabel.textContent = "RUNNING";
                        statusLabel.className = "status-label status-running";
                        btnStart.disabled = true;
                        btnStop.disabled = false;
                    } else {
                        statusLabel.textContent = "STOPPED";
                        statusLabel.className = "status-label status-stopped";
                        btnStart.disabled = false;
                        btnStop.disabled = true;
                    }

                    // Update info fields
                    document.getElementById("info-id").textContent = data.info?.id ?? "-";
                    document.getElementById("info-uuid").textContent = data.info?.uuid ?? "-";
                    document.getElementById("info-owner").textContent = data.creator?.username ?? data.info?.owner ?? "-";
                    document.getElementById("info-port").textContent = data.info?.port ?? "-";
                    document.getElementById("info-template").textContent = data.info?.template ?? "-";
                    document.getElementById("info-preset-name").textContent = data.preset?.name ?? "-";
                    document.getElementById("info-created").textContent = new Date(Number(data.info?.created)).toLocaleString() ?? "-";
                    document.getElementById("info-creator").textContent = data.creator?.username ?? "-";
                });
            }

            // Initial load
            updateServerInfo();

            // Refresh every 5 seconds
            setInterval(updateServerInfo, 5000);
        </script>
    </div>
</body>
</html>
