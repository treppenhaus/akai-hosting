
## todos:

create akai-service;


akai-panel:
- hosts a webserver which communicates with the services / e.g the servers
- does authenticate users
- create servers (copy from preset)
- starts / stops the services which start/stop the servers


akai-service:
- is started by the akai-panel
- starts / stops the server

+ why is service needed? cant the panel just directly start the servers?
  - it's needed, because:


how does input/output from panel to server work? because of the services between them.
---

# Akai Server API Documentation

## Endpoints

### GET /create
- Creates a new test server.
- Response:
  ```json
  {
    "success": true,
    "message": "Created testserver",
    "serverID": "<uuid>"
  }
  ```

### GET /start/:id
- Starts server with given ID.
- Response success:
  ```json
  {
    "success": true,
    "message": "Started server with ID: <id>"
  }
  ```
- Response failure (already running or doesn't exist):
  ```json
  {
    "success": false,
    "message": "Server <id> is already running or does not exist."
  }
  ```

### GET /stop/:id
- Stops server with given ID.
- Response success:
  ```json
  {
    "success": true,
    "message": "Stopped server with ID: <id>"
  }
  ```
- Response failure (not found):
  ```json
  {
    "success": false,
    "message": "No running server found with ID: <id>"
  }
  ```

### GET /send/:id/:input
- Sends input command to the running server.
- Response success:
  ```json
  {
    "success": true,
    "message": "Input sent to server <id>"
  }
  ```
- Response failure (server not running):
  ```json
  {
    "success": false,
    "message": "No running server found with ID: <id>"
  }
  ```

### GET /running
- Lists all currently running servers.
- Response:
  ```json
  {
    "success": true,
    "servers": [
      {
        "serverID": "<id>",
        "pid": <pid>,
        "connected": true
      },
      ...
    ]
  }
  ```
