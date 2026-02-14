import { forgeUpgrades } from '../../constants/gameData';
import { ForgeUpgrade } from '../../types/game.d';

const purchasedKey = 'bf_purchased_upgrades';

const upgradesWithIds = (): ForgeUpgrade[] =>
  forgeUpgrades.map((u, i) => ({ ...u, id: u.id ?? i + 1 }));

const readPurchased = (): number[] => {
  const raw = localStorage.getItem(purchasedKey);
  if (!raw) return [];
  try {
    return JSON.parse(raw) as number[];
  } catch {
    return [];
  }
};

const writePurchased = (ids: number[]): void => {
  localStorage.setItem(purchasedKey, JSON.stringify(ids));
};

export const upgradesAPI = {
  async getAll(): Promise<ForgeUpgrade[]> {
    return upgradesWithIds();
  },

  async getPurchased(): Promise<number[]> {
    return readPurchased();
  },

  async purchase(upgradeId: number): Promise<{ success: boolean; message?: string }> {
    const all = upgradesWithIds();
    const exists = all.some(u => u.id === upgradeId);
    if (!exists) return { success: false, message: 'Upgrade not found' };

    const purchased = readPurchased();
    if (!purchased.includes(upgradeId)) {
      purchased.push(upgradeId);
      writePurchased(purchased);
    }
    return { success: true };
  },
};
