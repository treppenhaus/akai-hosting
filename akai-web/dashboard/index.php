<?php
session_start();
$token = '$2y$10$yumC4x7Y0SpdlUfsCAEeUOrtNqNOkL2qFSkBBJA9Fg4Phm2jaazSW';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Dark Mode Server Dashboard</title>
<script>
  const token = "<?php echo $token; ?>";
  
    function startServer(uuid) {
        fetch(`http://localhost:3000/start/${uuid}?token=${encodeURIComponent(token)}`)
          .then(res => res.json())
          .then(data => alert(data.message))
          .catch(() => alert('Failed to start server'));
      }

      function stopServer(uuid) {
        fetch(`http://localhost:3000/stop/${uuid}?token=${encodeURIComponent(token)}`)
          .then(res => res.json())
          .then(data => alert(data.message))
          .catch(() => alert('Failed to stop server'));
      }
</script>
<style>
  
</style>
<link rel="stylesheet" href="dashboard.css">
</head>
<body>
  <div class="container">
    <header>Server Dashboard</header>

    <div class="servers-grid" id="serversContainer">
      Loading server info...
    </div>

    <button class="create-server-button" onclick="alert('soon')">Create New Server</button>
  </div>

<script>
  const container = document.getElementById('serversContainer');

  async function fetchServers() {
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

      container.innerHTML = ''; // Clear loading text

      

      servers.forEach(server => {
        // Default to stopped if no status provided
        const status = server.status || 'stopped';
        const box = document.createElement('div');
        box.className = `server-box ${status}`;

        box.innerHTML = `
          <h2>UUID: ${server.uuid}</h2>
          <div class="server-status">Status: ${status.charAt(0).toUpperCase() + status.slice(1)}</div>
          <div class="server-info">
            <div><strong>Owner:</strong> ${server.owner}</div>
            <div><strong>Created:</strong> ${new Date(parseInt(server.created)).toLocaleString()}</div>
            <div><strong>Template:</strong> ${server.template}</div>
            <div><strong>Port:</strong> ${server.port}</div>
          </div>
          <div class="buttons">
            <button onclick="startServer('${server.uuid}')">Start</button>
            <button class="stop-btn" onclick="stopServer('${server.uuid}')">Stop</button>
            <a class="live-log-link" href="livelog.html?serverID=${server.uuid}" target="_blank">Live Log</a>
          </div>
        `;

        container.appendChild(box);
      });

    } catch (e) {
      console.error(e);
      container.textContent = "Error loading server info.";
    }
  }

  fetchServers();
</script>
</body>
</html>
