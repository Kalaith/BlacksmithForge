// Inventory utility functions

import { InventoryItem, InventoryFilter, InventorySort, InventoryUtils } from '../types/inventory';

/**
 * Calculate the total value of inventory items
 */
const calculateTotalValue = (items: InventoryItem[]): number => {
  return items.reduce((total, item) => total + item.value, 0);
};

/**
 * Filter inventory items based on criteria
 */
const filterItems = (items: InventoryItem[], filter: InventoryFilter): InventoryItem[] => {
  return items.filter(item => {
    // Type filter
    if (filter.type && item.type !== filter.type) {
      return false;
    }

    // Quality filter
    if (filter.quality && item.quality !== filter.quality) {
      return false;
    }

    // Value range filter
    if (filter.minValue !== undefined && item.value < filter.minValue) {
      return false;
    }

    if (filter.maxValue !== undefined && item.value > filter.maxValue) {
      return false;
    }

    // Search term filter
    if (filter.searchTerm) {
      const searchLower = filter.searchTerm.toLowerCase();
      const nameMatch = item.name.toLowerCase().includes(searchLower);
      const descMatch = item.description?.toLowerCase().includes(searchLower) || false;

      if (!nameMatch && !descMatch) {
        return false;
      }
    }

    return true;
  });
};

/**
 * Sort inventory items based on criteria
 */
const sortItems = (items: InventoryItem[], sort: InventorySort): InventoryItem[] => {
  return [...items].sort((a, b) => {
    let comparison = 0;

    switch (sort.field) {
      case 'name':
        comparison = a.name.localeCompare(b.name);
        break;
      case 'value':
        comparison = a.value - b.value;
        break;
      case 'type':
        comparison = a.type.localeCompare(b.type);
        break;
      case 'quality': {
        const qualityOrder = { Poor: 1, Fair: 2, Good: 3, Excellent: 4 };
        const aQuality = qualityOrder[a.quality as keyof typeof qualityOrder] || 0;
        const bQuality = qualityOrder[b.quality as keyof typeof qualityOrder] || 0;
        comparison = aQuality - bQuality;
        break;
      }
      case 'craftedAt': {
        const aDate = a.craftedAt ? new Date(a.craftedAt).getTime() : 0;
        const bDate = b.craftedAt ? new Date(b.craftedAt).getTime() : 0;
        comparison = aDate - bDate;
        break;
      }
      default:
        comparison = 0;
    }

    return sort.direction === 'desc' ? -comparison : comparison;
  });
};

/**
 * Find an item by name
 */
const findItemByName = (items: InventoryItem[], name: string): InventoryItem | null => {
  return items.find(item => item.name.toLowerCase() === name.toLowerCase()) || null;
};

/**
 * Find an item by ID
 */
const findItemById = (items: InventoryItem[], id: number): InventoryItem | null => {
  return items.find(item => item.id === id) || null;
};

/**
 * Get items by type
 */
const getItemsByType = (items: InventoryItem[], type: InventoryItem['type']): InventoryItem[] => {
  return items.filter(item => item.type === type);
};

/**
 * Get items by quality
 */
const getItemsByQuality = (
  items: InventoryItem[],
  quality: InventoryItem['quality']
): InventoryItem[] => {
  return items.filter(item => item.quality === quality);
};

/**
 * Get inventory statistics
 */
const getInventoryStats = (items: InventoryItem[]) => {
  const stats = {
    totalItems: items.length,
    totalValue: calculateTotalValue(items),
    byType: {} as Record<string, number>,
    byQuality: {} as Record<string, number>,
    averageValue: 0,
    mostValuable: null as InventoryItem | null,
    leastValuable: null as InventoryItem | null,
  };

  // Count by type
  items.forEach(item => {
    stats.byType[item.type] = (stats.byType[item.type] || 0) + 1;
  });

  // Count by quality
  items.forEach(item => {
    if (item.quality) {
      stats.byQuality[item.quality] = (stats.byQuality[item.quality] || 0) + 1;
    }
  });

  // Calculate average value
  stats.averageValue = items.length > 0 ? stats.totalValue / items.length : 0;

  // Find most and least valuable items
  if (items.length > 0) {
    stats.mostValuable = items.reduce((max, item) => (item.value > max.value ? item : max));
    stats.leastValuable = items.reduce((min, item) => (item.value < min.value ? item : min));
  }

  return stats;
};

/**
 * Check if item can be sold
 */
const canSellItem = (item: InventoryItem): boolean => {
  // Items with 0 value or certain types might not be sellable
  return item.value > 0 && item.type !== 'material';
};

/**
 * Check if item can be used/consumed
 */
const canUseItem = (item: InventoryItem): boolean => {
  return item.type === 'consumable' || item.type === 'upgrade';
};

/**
 * Get item rarity color for UI
 */
const getItemRarityColor = (quality?: string): string => {
  switch (quality) {
    case 'Poor':
      return '#9CA3AF'; // Gray
    case 'Fair':
      return '#22C55E'; // Green
    case 'Good':
      return '#3B82F6'; // Blue
    case 'Excellent':
      return '#A855F7'; // Purple
    default:
      return '#6B7280'; // Default gray
  }
};

/**
 * Format item description with quality and value
 */
const formatItemDescription = (item: InventoryItem): string => {
  let description = item.description || item.name;

  if (item.quality) {
    description += ` (${item.quality} Quality)`;
  }

  description += ` - Value: ${item.value} gold`;

  if (item.durability !== undefined && item.maxDurability !== undefined) {
    description += ` - Durability: ${item.durability}/${item.maxDurability}`;
  }

  return description;
};

// Export all functions as an object implementing InventoryUtils interface
export const inventoryUtils: InventoryUtils = {
  calculateTotalValue,
  filterItems,
  sortItems,
  findItemByName,
  findItemById,
  getItemsByType,
  getItemsByQuality,
};

// Export additional utility functions
export {
  calculateTotalValue,
  filterItems,
  sortItems,
  findItemByName,
  findItemById,
  getItemsByType,
  getItemsByQuality,
  getInventoryStats,
  canSellItem,
  canUseItem,
  getItemRarityColor,
  formatItemDescription,
};
