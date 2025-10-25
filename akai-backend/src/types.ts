// types.ts
export interface User {
  id: number;
  username: string;
  created_at: Date;
}

export interface Preset {
  name: string;
  folder: string;
  java: string;
  added: number;
  id: string;
  description: string;
}

export interface ServerInfo {
  id: number;
  uuid: string;
  owner: number;
  created: number;
  template: string;
  port: number | null;
  status: string;
  nickname: string;
}

export interface ExcessiveServerInfo {
  info: ServerInfo
  preset: Preset
  creator: User
  running: boolean
}