import { ForgeUpgrade } from '../../types/game.d';
import { apiClient } from '../client';

type BackendUpgrade = {
  id?: number;
  name: string;
  cost: number;
  description?: string;
  effect?: string;
  icon: string;
  unlock_level?: number;
};

const requireData = <T>(success: boolean, data: T | undefined, message?: string): T => {
  if (!success || data === undefined) {
    throw new Error(message || 'Backend request failed');
  }
  return data;
};

const transformBackendUpgrade = (upgrade: BackendUpgrade): ForgeUpgrade => ({
  id: upgrade.id,
  name: upgrade.name,
  cost: Number(upgrade.cost),
  effect: upgrade.effect || upgrade.description || '',
  icon: upgrade.icon,
  unlockLevel: upgrade.unlock_level,
});

const uniqueUpgrades = (upgrades: ForgeUpgrade[]): ForgeUpgrade[] =>
  Array.from(new Map(upgrades.map(upgrade => [upgrade.name, upgrade])).values());

export const upgradesAPI = {
  async getAll(): Promise<ForgeUpgrade[]> {
    const response = await apiClient.get<BackendUpgrade[]>('/upgrades');
    return uniqueUpgrades(
      requireData(response.success, response.data, response.message).map(transformBackendUpgrade)
    );
  },

  async getPurchased(): Promise<number[]> {
    const response = await apiClient.get<number[]>('/upgrades/purchased');
    return requireData(response.success, response.data, response.message);
  },

  async purchase(upgradeId: number): Promise<{ success: boolean; message?: string }> {
    const response = await apiClient.post<unknown>('/upgrades/purchase', {
      upgrade_id: upgradeId,
    });
    return { success: response.success, message: response.message };
  },
};
