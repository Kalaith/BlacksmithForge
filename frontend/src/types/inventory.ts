// Inventory-related types and interfaces

/**
 * Base inventory item interface
 */
export interface InventoryItem {
  id?: number;
  name: string;
  icon: string;
  quality?: 'Poor' | 'Fair' | 'Good' | 'Excellent';
  value: number;
  type: 'weapon' | 'tool' | 'armor' | 'material' | 'consumable' | 'upgrade';
  description?: string;
  durability?: number;
  maxDurability?: number;
  craftedAt?: string;
  craftedBy?: string;
}

/**
 * Crafted item with additional metadata
 */
export interface CraftedInventoryItem extends InventoryItem {
  recipeId: number;
  recipeName: string;
  materialsUsed: Record<string, number>;
  craftingQuality: number;
  craftingAccuracy: number;
  hammerClicks: number;
}

/**
 * Material inventory item
 */
export interface MaterialInventoryItem {
  id: number;
  name: string;
  quantity: number;
  cost: number;
  quality: 'common' | 'rare' | 'legendary';
  description: string;
  icon: string;
  type: 'ore' | 'fuel' | 'component' | 'reagent';
}

/**
 * Inventory state interface
 */
export interface InventoryState {
  items: InventoryItem[];
  materials: MaterialInventoryItem[];
  totalValue: number;
  totalItems: number;
  loading: boolean;
  error: string | null;
}

/**
 * Inventory filter options
 */
export interface InventoryFilter {
  type?: InventoryItem['type'];
  quality?: InventoryItem['quality'];
  minValue?: number;
  maxValue?: number;
  searchTerm?: string;
}

/**
 * Inventory sort options
 */
export interface InventorySort {
  field: 'name' | 'value' | 'quality' | 'type' | 'craftedAt';
  direction: 'asc' | 'desc';
}

/**
 * Hook return type for useInventory
 */
export interface UseInventoryReturn {
  // State
  inventory: InventoryItem[];
  materials: MaterialInventoryItem[];
  loading: boolean;
  error: string | null;

  // Computed values
  totalValue: number;
  totalItems: number;
  filteredItems: InventoryItem[];

  // Actions
  removeItem: (item: InventoryItem) => Promise<boolean>;
  refreshInventory: () => void;
  clearError: () => void;

  // Filtering and sorting
  applyFilter: (filter: InventoryFilter) => void;
  applySorting: (sort: InventorySort) => void;
  clearFilter: () => void;
}

/**
 * Inventory transaction interface
 */
export interface InventoryTransaction {
  id: number;
  userId: number;
  type: 'add' | 'remove' | 'craft' | 'sell' | 'purchase';
  itemId?: number;
  itemName: string;
  quantity: number;
  value: number;
  timestamp: string;
  description?: string;
}

/**
 * Inventory management utility functions
 */
export interface InventoryUtils {
  calculateTotalValue: (items: InventoryItem[]) => number;
  filterItems: (items: InventoryItem[], filter: InventoryFilter) => InventoryItem[];
  sortItems: (items: InventoryItem[], sort: InventorySort) => InventoryItem[];
  findItemByName: (items: InventoryItem[], name: string) => InventoryItem | null;
  findItemById: (items: InventoryItem[], id: number) => InventoryItem | null;
  getItemsByType: (items: InventoryItem[], type: InventoryItem['type']) => InventoryItem[];
  getItemsByQuality: (items: InventoryItem[], quality: InventoryItem['quality']) => InventoryItem[];
}

/**
 * Inventory API response types
 */
export interface InventoryResponse {
  success: boolean;
  data?: InventoryItem[];
  message?: string;
  totalValue?: number;
  totalItems?: number;
}

export interface MaterialsResponse {
  success: boolean;
  data?: MaterialInventoryItem[];
  message?: string;
}

export interface InventoryActionResponse {
  success: boolean;
  message: string;
  updatedInventory?: InventoryItem[];
  transaction?: InventoryTransaction;
}
