<?php
    session_start();
    $token = '$2y$10$yumC4x7Y0SpdlUfsCAEeUOrtNqNOkL2qFSkBBJA9Fg4Phm2jaazSW';
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>cool panel bro</title>
    <link rel="stylesheet" href="dashboard2.css">
    <script>
        const token = "<?php echo $token; ?>";
        const container = document.getElementById('container');

        addServer = (uuid, status, template, consoleLink) => {
            console.log("adding server.")
            document.getElementById("servers").innerHTML += `<div class="server-container">
                    <div class="server-container-grid">
                        <div class="left">
                            <h2>unnamed server</h2>
                            <p>ID: ${uuid}</p>
                            <p>Status: ${status}</p>
                            <p>Type: ${template}</p>
                            <a href="${consoleLink}">open console</a>
                        </div>
                        <div class="right">
                            <img src="more.png" alt="more" onclick="alert('not implemented yet.')">
                        </div>
                    </div>
                    <div class="server-buttons-grid">
                        <div class="start-button" onclick="startServer('${uuid}')">Start</div>
                        <div class="start-button" onclick="stopServer('${uuid}')">Stop</div>
                        <div class="start-button" onclick="restartServer('${uuid}')">Restart</div>
                    </div>
                </div>
            `;
        }

        fetchServers = async() => {
            if (!token) {
                container.textContent = "No user token found.";
                return;
            }

            try {
                const res = await fetch(`http://localhost:3000/myservers?token=${encodeURIComponent(token)}`);
                const data = await res.json();
                if (!data.success) {
                    container.textContent = "Failed to fetch servers.";
                    return;
                }
                const servers = data.servers;
                if (servers.length === 0) {
                    container.textContent = "No servers found.";
                    return;
                }
                console.log(servers);
                servers
                    .sort((a, b) => (b.status === "running") - (a.status === "running"))
                    .forEach(server => {
                        console.log("adding: " + server.uuid);
                        addServer(server.uuid, server.status, server.template, "https://google.com/")
                    });
            }
            catch(e) {}
        }

    </script>
</head>

<body>
    <div class="content">
        <div class="header">
            <h1>server management panel</h1>
        </div>

        <div class="body" id="container">
            <div class="servers" id="servers">

                <!--
                
                -->


            </div>

        </div>
        <div class="footer">

        </div>
    </div>
    </div>
</body>

<script>fetchServers()</script>
</html>