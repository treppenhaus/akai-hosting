import mysql, { Pool, ResultSetHeader, RowDataPacket } from 'mysql2/promise';
import { ServerInfo, User } from './types';

interface LogEntry {
  line: string;
  timestamp: number;
  serverid: string;
}

export class AkaiDatabaseClient {
  private pool: Pool;

  constructor(
    private config: mysql.PoolOptions
  ) {
    this.pool = mysql.createPool(config);
  }

  // Insert a log entry into mc_logs
  async insertLog(log: LogEntry): Promise<number> {
    const sql = `INSERT INTO mc_logs (line, timestamp, serverid) VALUES (?, ?, ?)`;
    const [result] = await this.pool.execute<mysql.ResultSetHeader>(sql, [
      log.line,
      log.timestamp,
      log.serverid,
    ]);
    return result.insertId;
  }

  // Get all logs for a given serverid
  async getLogsByServerId(serverid: string): Promise<LogEntry[]> {
    const sql = `SELECT * FROM mc_logs WHERE serverid = ? ORDER BY id DESC LIMIT 100`;
    const [rows] = await this.pool.execute<RowDataPacket[]>(sql, [serverid]);
    return rows as LogEntry[];
  }

  // Get user by password hash (token)
  async getUserByToken(token: string): Promise<User | null> {
    const sql = `SELECT id, username, created_at FROM users WHERE password_hash = ? LIMIT 1`;
    const [rows] = await this.pool.execute<RowDataPacket[]>(sql, [token]);

    if (rows.length === 0) {
      return null;
    }

    const userRow = rows[0];
    if (!userRow) return null;
    return {
      id: userRow.id,
      username: userRow.username,
      created_at: new Date(userRow.created_at),
    };
  }

  // Get all servers
  async getAllServers(): Promise<ServerInfo[]> {
    const sql = `SELECT id, uuid, owner, created, template, port, status, nickname FROM serverinfo`;
    const [rows] = await this.pool.execute<RowDataPacket[]>(sql);
    return rows as ServerInfo[];
  }

  // Get all servers for a specific user ID
  async getServersByUserId(userId: number): Promise<ServerInfo[]> {
    const sql = `SELECT id, uuid, owner, created, template, port, status, nickname FROM serverinfo WHERE owner = ?`;
    const [rows] = await this.pool.execute<RowDataPacket[]>(sql, [userId]);
    return rows as ServerInfo[];
  }

  // Get server info by UUID
  async getServerInfoByUuid(uuid: string): Promise<ServerInfo | null> {
    const sql = `SELECT id, uuid, owner, created, template, port, nickname FROM serverinfo WHERE uuid = ? LIMIT 1`;
    const [rows] = await this.pool.execute<RowDataPacket[]>(sql, [uuid]);
    if (rows.length === 0) return null;
    return rows[0] as ServerInfo;
  }

  async addServer(server: Omit<ServerInfo, 'id'>): Promise<number> {
    const sql = `
      INSERT INTO serverinfo (uuid, owner, created, template, port, nickname)
      VALUES (?, ?, ?, ?, ?)
    `;

    const [result] = await this.pool.execute<ResultSetHeader>(sql, [
      server.uuid,
      server.owner,
      server.created,
      server.template,
      server.port
    ]);

    return result.insertId; // auto-increment ID
  }

  async setServerStatus(uuid: string, status: string): Promise<boolean> {
    const sql = `UPDATE serverinfo SET status = ? WHERE uuid = ?`;
    const [result] = await this.pool.execute<ResultSetHeader>(sql, [status, uuid]);
    return result.affectedRows > 0;
  }

  // Graceful shutdown
  async close(): Promise<void> {
    await this.pool.end();
  }

  async updateServerStatus(id: string, status: string) {
    const query = 'UPDATE servers SET status = ? WHERE id = ?';
    await this.pool.query(query, [status, id]);
  }
}
