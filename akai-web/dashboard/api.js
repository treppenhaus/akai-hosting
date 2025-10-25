
const startServer = (uuid, onSuccess, onFailure) => {
  fetch(`http://localhost:3000/start/${uuid}?token=${token}`)
      .then(res => res.json())
      .then(data => {
          if (data.success) {
              if (onSuccess) onSuccess(data);
          } else {
              if (onFailure) onFailure(data);
          }
      })
      .catch(err => {
          if (onFailure) onFailure({ message: "Network error", error: err });
      });
}

const stopServer = (uuid, onSuccess, onFailure) => {
  fetch(`http://localhost:3000/stop/${uuid}?token=${token}`)
      .then(res => res.json())
      .then(data => {
          if (data.success) {
              if (onSuccess) onSuccess(data);
          } else {
              if (onFailure) onFailure(data);
          }
      })
      .catch(err => {
          if (onFailure) onFailure({ message: "Network error", error: err });
      });
}

const getServerInfo = (uuid, cb) => {
    fetch(`http://localhost:3000/serverinfo/${uuid}?token=${token}`)
        .then(res => {
            if (!res.ok) throw new Error('Network response was not ok');
            return res.json();
        })
        .then(data => cb(data))
        .catch(err => {
            alert('Failed to get server info');
            cb(err); // optionally pass the error to the callback
        });
};


fetchServers = async (callback) => {
    if (!token) {
        container.textContent = "No user token found.";
        return;
    }

    try {
        const res = await fetch(`http://localhost:3000/myservers?token=${token}`);
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
                callback(server.uuid, server.status, server.template, "https://google.com/")
            });
    }
    catch (e) { }
}
createServer = (presetID, serverName, onSuccess = () => {}, onError = () => {}) => {
    if (!presetID || !token || !serverName) {
      onError("Missing presetID, token, or server name");
      return;
    }
  
    fetch(`http://localhost:3000/create?presetID=${encodeURIComponent(presetID)}&token=${encodeURIComponent(token)}&name=${encodeURIComponent(serverName)}`)
      .then(res => {
        if (!res.ok) throw new Error("Network error during server creation");
        return res.json();
      })
      .then(data => {
        if (data.success) {
          onSuccess(data);
        } else {
          onError(data.message);
        }
      })
      .catch(err => {
        console.error("Create server error:", err);
        onError(err.message);
      });
  };
  
