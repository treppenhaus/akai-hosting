import express from "express";
import path from "path";
import { AkaiDatabaseClient } from './AkaiDatabaseClient';
import { Server as SocketIOServer } from 'socket.io';
import http from 'http';
import { AkaiServerController } from './AkaiServerController';
import cors from 'cors';

const HEALTH_CHECK_INTERVAL = 10000; // every 10 seconds

const app = express();
const server = http.createServer(app);
const io = new SocketIOServer(server);
const PORT = 3000;
const db = new AkaiDatabaseClient({
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'akai',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
});

const controller = new AkaiServerController(db);

io.on('connection', (socket) => {
  console.log('Client connected:', socket.id);

  socket.on('joinServer', (serverID) => {
    console.log(`Socket ${socket.id} joining room: ${serverID}`);
    socket.join(serverID);
  });

  socket.on('disconnect', () => {
    console.log('Client disconnected:', socket.id);
  });
});

// Serve static files
app.use(express.static(path.join(__dirname, "../public")));
app.use(cors({
  origin: '*' // replace with your PHP frontend origin, or '*' to allow all
}));

app.get("/create", async (req, res) => {
  const { presetID, token } = req.query;

  // Basic validation
  if (
    typeof presetID !== "string" ||
    typeof token !== "string" ||
    presetID.length === 0 ||
    token.length === 0 ||
    presetID.length > 200 ||
    token.length > 200
  ) {
    return res.status(400).json({
      success: false,
      message: "Invalid or missing 'presetID' and/or 'token'. Both must be provided."
    });
  }

  try {
    const uuid = await controller.createServer(presetID, token, "new server hi");
    if (uuid) {
      res.json({ success: true, message: "Created testserver", serverID: uuid });
    } else {
      res.status(500).json({ success: false, message: "Error creating server." });
    }
  } catch (error) {
    console.error("Server creation error:", error);
    res.status(500).json({ success: false, message: "Server error during creation." });
  }
});

app.get('/start/:id', async (req, res) => {
  const id = req.params.id;
  const token = req.query.token;

  if (typeof token !== 'string' || token.length === 0 || token.length > 200) {
    return res.status(400).json({ success: false, message: "Token is required and must be valid." });
  }

  const permitted = await controller.isPermitted(id, token);
  if (!permitted) {
    return res.status(403).json({ success: false, message: "Not authorized to start this server." });
  }

  const started = await controller.startServer(id, io);
  if (started) {
    res.json({ success: true, message: `Started server with ID: ${id}` });
  } else {
    res.status(400).json({ success: false, message: `Server ${id} is already running or does not exist.` });
  }
});


app.get('/stop/:id', async (req, res) => {
  const id = req.params.id;
  const stopped = await controller.stopServer(id);
  if (stopped) {
    res.json({ success: true, message: `Stopped server with ID: ${id}` });
  } else {
    res.status(404).json({ success: false, message: `No running server found with ID: ${id}` });
  }
});

app.get('/send/:id/:input', async (req, res) => {
  const { id, input } = req.params;
  const sent = await controller.sendInputToServer(id, input);
  if (sent) {
    res.json({ success: true, message: `Input sent to server ${id}` });
  } else {
    res.status(404).json({ success: false, message: `No running server found with ID: ${id}` });
  }
});

app.get('/serverinfo/:id', async (req, res) => {
  const { id } = req.params;
  controller.getServerById(id).then(server => {
    res.json(server);
    console.log(server);
  })
  // todo: check for per,ms
  // {"id":8,"uuid":"4ce69660-91e1-4550-aa6c-ac7d5cf9b46a","owner":1,"created":"1754792696759","template":"___cb188","port":25565}
});

app.get('/running', (req, res) => {
  res.json({ success: true, servers: controller.getRunningServers() });
});

app.get('/myservers', async (req, res) => {
  const token = req.query.token;
  if (typeof token !== 'string' || token.length === 0 || token.length > 200) {
    return res.status(400).json({ success: false, message: "Token is required and must be valid." });
  }

  try {
    const user = await db.getUserByToken(token);
    if (!user) {
      return res.status(401).json({ success: false, message: "Invalid token." });
    }

    const servers = await db.getServersByUserId(user.id);
    res.json({ success: true, servers });
  } catch (error) {
    console.error("Error fetching user's servers:", error);
    res.status(500).json({ success: false, message: "Server error." });
  }
});

startHealthMonitor();
server.listen(PORT, () => {
  console.log(`Server is running at http://localhost:${PORT}`);
});









async function startHealthMonitor() {
  console.log(`[Monitor] Health check started. Interval: ${HEALTH_CHECK_INTERVAL / 1000}s`);

  setInterval(async () => {
    try {
      const runningServers = controller.getRunningServers();
      if (!runningServers || runningServers.length === 0) return;

      for (const srv of runningServers) {
        const isAlive = await controller.isServerProcessAlive(srv.serverID);

        if (!isAlive) {
          console.log(`[Monitor] Detected stopped server: ${srv.serverID}. Cleaning up...`);

          // Stop it in memory
          controller.forceRemoveServer(srv.serverID);

          // Update DB
          await db.updateServerStatus(srv.serverID, 'stopped');

          // Optionally notify connected sockets
          io.to(srv.serverID).emit('serverStopped', { id: srv.serverID });
        }
      }
    } catch (err) {
      console.error("[Monitor] Health check error:", err);
    }
  }, HEALTH_CHECK_INTERVAL);
}