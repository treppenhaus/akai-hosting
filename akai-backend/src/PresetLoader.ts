import fs from 'fs/promises';
import path from 'path';
import { Preset } from './types';

export class PresetLoader {
  private presetsFilePath: string;

  constructor(presetsFilePath?: string) {
    // Default path to presets.json in the same directory as this file
    this.presetsFilePath = presetsFilePath || path.resolve(__dirname, 'presets.json');
  }

  async loadPresets(): Promise<Preset[]> {
    try {
      const data = await fs.readFile(this.presetsFilePath, 'utf-8');
      const parsed = JSON.parse(data);

      if (!parsed.presets || !Array.isArray(parsed.presets)) {
        throw new Error('Invalid presets.json format: "presets" key missing or not an array.');
      }

      return parsed.presets as Preset[];
    } catch (error) {
      console.error('Failed to load presets:', error);
      return [];
    }
  }

  async presetExists (presetID: string): Promise<boolean | undefined> {
    const preset = await this.getPresetById(presetID);
    if(preset) return true;
    else {
        console.log("preset not found: " + presetID);
        return false;
    }
}
  
  async getPresetById(id: string): Promise<Preset | undefined> {
    const presets = await this.loadPresets();
    return presets.find(preset => preset.id === id);
  }
}
