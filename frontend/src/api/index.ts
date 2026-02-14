import { materialsAPI } from './services/materials';
import { recipesAPI } from './services/recipes';
import { customersAPI } from './services/customers';
import { inventoryAPI } from './services/inventory';
import { craftingAPI } from './services/crafting';
import { upgradesAPI } from './services/upgrades';
import { miniGamesAPI } from './services/minigames';
import { authAPI } from './services/auth';
import { healthAPI } from './services/health';

export * from './services/materials';
export * from './services/recipes';
export * from './services/customers';
export * from './services/inventory';
export * from './services/crafting';
export * from './services/upgrades';
export * from './services/minigames';
export * from './services/auth';
export * from './services/health';

export * from './backendTypes';

export default {
  materials: materialsAPI,
  recipes: recipesAPI,
  customers: customersAPI,
  inventory: inventoryAPI,
  crafting: craftingAPI,
  upgrades: upgradesAPI,
  miniGames: miniGamesAPI,
  auth: authAPI,
  health: healthAPI,
};
