<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Server</title>
    <link rel="stylesheet" href="create.css">
    <script>
        const token = "$2y$10$yumC4x7Y0SpdlUfsCAEeUOrtNqNOkL2qFSkBBJA9Fg4Phm2jaazSW";
    </script>
    <script src="api.js"></script>
</head>

<body>
    <div class="container">
        <div class="card">
            <h1>Create New Server</h1>

            <form id="create-form">
                <label for="serverName">Server Name</label>
                <input type="text" id="serverName" name="serverName" placeholder="Enter server name" required>

                <label for="presetID">Select Preset</label>
                <select id="presetID" name="presetID" required>
                    <option value="" disabled selected>Loading presets...</option>
                </select>

                <button type="submit">Create Server</button>
            </form>

            <p id="status"></p>

            <a href="dashboard.html" class="back-link">Back to Dashboard</a>
        </div>
    </div>

    <script>
        const form = document.getElementById("create-form");
        const status = document.getElementById("status");
        const presetSelect = document.getElementById("presetID");
        const serverNameInput = document.getElementById("serverName");

        async function loadPresets() {
            try {
                const res = await fetch("http://localhost:3000/presets");
                const data = await res.json();
                if (!data.success) throw new Error("Failed to fetch presets");

                presetSelect.innerHTML = "";
                data.presets.forEach(preset => {
                    const option = document.createElement("option");
                    option.value = preset.id;
                    option.textContent = `${preset.name} — ${preset.description}`;
                    presetSelect.appendChild(option);
                });
            } catch (err) {
                presetSelect.innerHTML = `<option value="">Failed to load presets</option>`;
                console.error(err);
            }
        }

        form.addEventListener("submit", (e) => {
            e.preventDefault();
            const presetID = presetSelect.value;
            const serverName = serverNameInput.value.trim();

            if (!presetID) {
                status.textContent = "Please select a preset.";
                return;
            }

            createServer(
                presetID,
                serverName,
                (data) => { // success callback
                    status.textContent = `Server created! ID: ${data.serverID}`;
                    setTimeout(() => {
                        window.location.replace(`server.php?id=${data.serverID}`);
                    }, 400);
                },
                (errMsg) => { // failure callback
                    status.textContent = `Failed to create server: ${errMsg}`;
                }
            );
        });

        loadPresets();
    </script>
</body>

</html>