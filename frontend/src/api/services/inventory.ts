import { InventoryItem } from '../../types/inventory';
import { apiClient } from '../client';

export const inventoryAPI = {
  async getUserInventory(): Promise<InventoryItem[]> {
    const response = await apiClient.get<InventoryItem[]>('/inventory');
    return response.success ? response.data ?? [] : [];
  },

  async addItem(item: InventoryItem): Promise<boolean> {
    void item;
    throw new Error('Direct inventory mutation is disabled. Use crafting, purchasing, or selling actions.');
  },

  async removeItem(item: InventoryItem): Promise<boolean> {
    void item;
    throw new Error('Direct inventory mutation is disabled. Use crafting, purchasing, or selling actions.');
  },
};
