<?php
session_start();
$token = '$2y$10$yumC4x7Y0SpdlUfsCAEeUOrtNqNOkL2qFSkBBJA9Fg4Phm2jaazSW';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <script>
        const token = "<?php echo $token; ?>";

        function addServer(uuid, status, template, consoleLink) {
            const serversContainer = document.getElementById('servers');
            const serverHTML = `
                <div class="server-card">
                    <div class="server-header">
                        <div>
                            <h2>Server ${uuid.substring(0, 6)}...</h2>
                            <p class="server-status ${status.toLowerCase()}">${status}</p>
                        </div>
                        <a class="console-link" href="${consoleLink}" target="_blank">Open Console</a>
                    </div>
                    <div class="server-details">
                        <p><b>UUID:</b> ${uuid}</p>
                        <p><b>Template:</b> ${template}</p>
                    </div>
                    <div class="server-actions">
                        <button class="btn start-btn" onclick="startServer('${uuid}', () => alert('starting'), () => alert('could not start!'))">Start</button>
                        <button class="btn stop-btn" onclick="stopServer('${uuid}', () => alert('stopping'), () => alert('could not stop!'))">Stop</button>
                        <!-- <button class="btn restart-btn" onclick="restartServer('${uuid}')">Restart</button> -->
                        <a href="server.php?id=${uuid}" class="btn info-btn">View Info</a>
                    </div>
                </div>`;
            serversContainer.insertAdjacentHTML('beforeend', serverHTML);

            
        }
    </script>
    <script src="api.js"></script>
</head>

<body>
    <div class="container">
        <header>
            <h1>Server Dashboard</h1>
            <button class="btn create-btn" onclick="window.location.replace('create.php')">Create Server</button>
        </header>

        <main id="servers" class="servers-list">
            <!-- Servers will load here -->
        </main>

        <footer>
            <p>cool panel bro © 2025</p>
        </footer>
    </div>

    <script>
        let update = () => {
            fetchServers(addServer);

            setTimeout(() => {
                const serversContainer = document.getElementById('servers');
                serversContainer.insertAdjacentHTML('beforeend', "");
                update();
            }, 3000);
        }
        update();
    </script>
</body>

</html>
