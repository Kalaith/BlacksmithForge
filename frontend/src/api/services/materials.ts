import { MATERIALS } from '../../constants/gameData';
import { Material } from '../../types/game.d';

const getKey = (userId: number) => `bf_materials_${userId}`;

const withIds = (): Material[] => MATERIALS.map((m, index) => ({ ...m, id: m.id ?? index + 1 }));

const readUserMaterials = (userId: number): Record<string, number> => {
  const raw = localStorage.getItem(getKey(userId));
  if (!raw) {
    const initial: Record<string, number> = {};
    withIds().forEach(m => {
      initial[m.name] = 0;
    });
    return initial;
  }
  try {
    return JSON.parse(raw) as Record<string, number>;
  } catch {
    return {};
  }
};

const writeUserMaterials = (userId: number, materials: Record<string, number>): void => {
  localStorage.setItem(getKey(userId), JSON.stringify(materials));
};

export const materialsAPI = {
  async getAll(): Promise<Material[]> {
    return withIds();
  },

  async getByType(type: string): Promise<Material[]> {
    const t = type.toLowerCase();
    return withIds().filter(m => m.name.toLowerCase().includes(t));
  },

  async getByRarity(rarity: string): Promise<Material[]> {
    return withIds().filter(m => m.quality === rarity);
  },

  async getUserMaterials(userId: number): Promise<Record<string, number>> {
    return readUserMaterials(userId);
  },

  async purchaseMaterial(materialId: number, quantity: number): Promise<boolean> {
    const authRaw = localStorage.getItem('auth-storage');
    if (!authRaw) return false;

    let userId = 0;
    try {
      const parsed = JSON.parse(authRaw) as {
        state?: { user?: { id?: number | string } };
      };
      userId = Number(parsed.state?.user?.id ?? 0);
    } catch {
      return false;
    }

    if (!userId || quantity <= 0) return false;
    const material = withIds().find(m => m.id === materialId);
    if (!material) return false;

    const current = readUserMaterials(userId);
    current[material.name] = (current[material.name] ?? 0) + quantity;
    writeUserMaterials(userId, current);
    return true;
  },
};
