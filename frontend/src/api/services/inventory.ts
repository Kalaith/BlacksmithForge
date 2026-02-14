import { InventoryItem } from '../../types/inventory';

const getKey = (userId: number) => `bf_inventory_${userId}`;

const readInventory = (userId: number): InventoryItem[] => {
  const raw = localStorage.getItem(getKey(userId));
  if (!raw) return [];
  try {
    return JSON.parse(raw) as InventoryItem[];
  } catch {
    return [];
  }
};

const writeInventory = (userId: number, items: InventoryItem[]): void => {
  localStorage.setItem(getKey(userId), JSON.stringify(items));
};

export const inventoryAPI = {
  async getUserInventory(userId: number): Promise<InventoryItem[]> {
    return readInventory(userId);
  },

  async addItem(userId: number, item: InventoryItem): Promise<boolean> {
    const current = readInventory(userId);
    current.push({ ...item, id: item.id ?? Date.now() });
    writeInventory(userId, current);
    return true;
  },

  async removeItem(userId: number, item: InventoryItem): Promise<boolean> {
    const current = readInventory(userId);
    const next = current.filter(i => i.id !== item.id);
    writeInventory(userId, next);
    return true;
  },
};
