import { ChildProcess, spawn } from 'child_process';
import { Writable } from 'stream';
import { cp } from 'fs/promises';
import readline from 'readline';
import { v4 as uuidv4 } from 'uuid';
import { AkaiDatabaseClient } from './AkaiDatabaseClient';
import { PresetLoader } from './PresetLoader';
import path from 'path';
import { ServerInfo } from './types';
const PropertiesReader = require('properties-reader');
const PORT_RANGE_MIN = 25600;
const PORT_RANGE_MAX = 25999;

export class AkaiServerController {
    private runningServers = new Map<string, ChildProcess>();
    private db: AkaiDatabaseClient;
    private presetLoader = new PresetLoader();

    constructor(db: AkaiDatabaseClient) {
        this.db = db;
    }

    isServerRunning(serverID: string): boolean {
        const child = this.runningServers.get(serverID);
        return !!child && !child.killed;
    }


    async handleOutStream(serverID: string, line: string, io: any) {
        io.to(serverID).emit('logLine', line);
        await this.log(serverID, line);
    }

    async log(serverID: string, line: string) {
        await this.db.insertLog({
            line,
            timestamp: Date.now(),
            serverid: serverID,
        });
    }

    async copyFolder(src: string, dest: string) {
        try {
            await cp(src, dest, { recursive: true });
            console.log(`Copied folder from ${src} to ${dest}`);
        } catch (err) {
            console.error("Error copying folder:", err);
            throw err;
        }
    }

    async stopServer(serverID: string): Promise<boolean> {
        const child = this.runningServers.get(serverID);
        if (!child) {
            console.log(`No running server found for ID ${serverID}`);
            this.db.setServerStatus(serverID, "stopped");
            return false;
        }

        this.sendInputToServer(serverID, "stop");
        setTimeout(() => {
            if (!child.killed) {
                console.log(`Force killing server ${serverID}`);
                child.kill('SIGKILL');
                this.db.setServerStatus(serverID, "killed");
            }
        }, 10000);
        this.db.setServerStatus(serverID, "stopping");

        return true;
    }

    async createServer(presetID: string, token: string, nickname: string): Promise<string | undefined> {
        if (!this.presetLoader.presetExists(presetID)) return;

        const user = await this.db.getUserByToken(token);
        if (!user) {
            console.log("Invalid token!");
            return undefined;
        }

        const newServerID = uuidv4();
        const preset = await this.presetLoader.getPresetById(presetID);
        if (!preset) {
            console.error("preset not available, can not create server.");
            return undefined;
        }

        const presetPath = `testenv/presets/${preset.folder}`;
        const serverpath = `testenv/server/${newServerID}`;

        try {
            await this.copyFolder(presetPath, serverpath);
            await this.db.addServer({
                uuid: newServerID,
                owner: user.id,
                created: Date.now(),
                template: presetID,
                port: 25565,
                status: "stopped",
                nickname: nickname
            });
            return newServerID;
        } catch (err) {
            console.error("Failed to copy folder:", err);
            return undefined;
        }
    }

    async serverExists(serverID: string): Promise<boolean> {
        return ((await this.db.getServerInfoByUuid(serverID)) != null);
    }

    async startServer(serverID: string, io: any) {

        let serverInfo = await this.db.getServerInfoByUuid(serverID);
        if (!this.serverExists(serverID) || serverInfo == undefined) {
            console.log("server does not exist!");
            return;
        }
        let preset = await this.presetLoader.getPresetById(serverInfo.template);
        if (preset == undefined) {
            console.log("preset was not found, java can not be started.")
            return;
        }

        if (this.isServerRunning(serverID)) {
            console.log(`Server ${serverID} is already running.`);
            return false;
        }


        this.db.setServerStatus(serverID, "starting");
        const serverhome = `testenv/server/${serverID}`;


        let port: number | null = await this.getFreePort();
        if (port == null) {
            console.log(`Server ${serverID} found no valid port`);
            return false;
        }


        // set the port
        this.db.updateServerPort(serverID, port)
        const properties = PropertiesReader(`${serverhome}/server.properties`);
        properties.set("server-port", port);
        properties.save(`${serverhome}/server.properties`);


        console.log(serverhome);
        const child = spawn(`..\\..\\java\\${preset.java}`, ["-jar", `craftbukkit-1.8.8.jar`], {
            cwd: serverhome,
        });

        console.log(`starting ${serverID} on port: ${port}`);

        const stdoutRL = readline.createInterface({ input: child.stdout });
        stdoutRL.on('line', (line: string) => {
            this.handleOutStream(serverID, line, io);
        });

        const stderrRL = readline.createInterface({ input: child.stderr });
        stderrRL.on('line', (line: string) => {
            this.handleOutStream(serverID, line, io);
        });

        child.on('close', (code) => {
            console.log(`Child process exited with code ${code}`);
            this.runningServers.delete(serverID);
        });

        this.runningServers.set(serverID, child);
        this.db.setServerStatus(serverID, "running");
    }

    getFreePort = async (): Promise<number | null> => {
        const usedPorts = new Set<number>();

        // Get all used ports from DB
        const servers = await this.db.getAllServers();
        servers.forEach(server => {
            if (server.port) usedPorts.add(server.port);
        });

        const maxAttempts = 1000; // avoid infinite loops

        for (let i = 0; i < maxAttempts; i++) {
            const port = Math.floor(Math.random() * (PORT_RANGE_MAX - PORT_RANGE_MIN + 1)) + PORT_RANGE_MIN;
            if (!usedPorts.has(port)) {
                // Optionally: check if the port is actually free on the system
                return port;
            }
        }

        return null;
    };

    async sendInputToServer(serverID: string, input: string): Promise<boolean> {
        const child = this.runningServers.get(serverID);
        if (!child) {
            console.error(`No running server found with ID: ${serverID}`);
            return false;
        }

        if (child.stdin instanceof Writable && child.stdin.writable) {
            child.stdin.write(input + '\n');
            return true;
        } else {
            console.error(`stdin is not writable for server ID: ${serverID}`);
            return false;
        }
    }

    async isPermitted(serverID: string, token: string): Promise<boolean> {
        const user = await this.db.getUserByToken(token);
        if (!user) return false;

        const serverInfo = await this.db.getServerInfoByUuid(serverID);
        if (!serverInfo) return false;

        // todo: in the future, add moderators which are other users but they can also manage your server;
        // todo: in the FAR future, implement a permission system so you can manage which moderator can manage which part of a server (can only start/stop, can edit settings, etc.)
        return serverInfo.owner === user.id;
    }

    getRunningServers() {
        return Array.from(this.runningServers.entries()).map(([serverID, child]) => ({
            serverID,
            pid: child.pid,
            connected: !child.killed,
        }));
    }

    getServerById = (id: string): Promise<ServerInfo | null> => {
        return this.db.getServerInfoByUuid(id);
    }

    isServerProcessAlive(id: string): boolean {
        const srv = this.runningServers.get(id);
        if (!srv || typeof srv.pid !== "number") return false; // ensure pid exists

        try {
            process.kill(srv.pid, 0); // check if process exists (throws if not)
            return true;
        } catch {
            return false;
        }
    }

    forceRemoveServer(id: string) {
        this.runningServers.delete(id);
    }

    async getAllPresets(): Promise<any[]> {
        return this.presetLoader.getAllPresets();
    }
}

