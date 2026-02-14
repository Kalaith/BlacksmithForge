import { Material, Recipe, Customer } from '../types/game.d';
import { BackendMaterial, BackendRecipe, BackendCustomer } from './backendTypes';

export function transformBackendMaterial(backendMaterial: BackendMaterial): Material {
  const props = backendMaterial.properties as Record<string, string | number>;
  return {
    id: backendMaterial.id,
    name: backendMaterial.name,
    cost: typeof props.cost === 'number' ? props.cost : 10,
    quality:
      backendMaterial.rarity === 'epic'
        ? 'legendary'
        : backendMaterial.rarity === 'uncommon'
          ? 'rare'
          : (backendMaterial.rarity as 'common' | 'rare' | 'legendary'),
    description:
      typeof props.description === 'string'
        ? props.description
        : `${backendMaterial.type} material`,
    icon: typeof props.icon === 'string' ? props.icon : getDefaultIcon(backendMaterial.type),
  };
}

export function transformBackendRecipe(backendRecipe: BackendRecipe): Recipe {
  const materials: Record<string, number> = {};
  backendRecipe.required_materials.forEach(material => {
    materials[material.name] = material.quantity;
  });

  return {
    id: backendRecipe.id,
    name: backendRecipe.name,
    materials,
    sellPrice: calculateSellPrice(backendRecipe.required_materials, backendRecipe.difficulty),
    difficulty: getDifficultyNumber(backendRecipe.difficulty),
    icon: getRecipeIcon(backendRecipe.result_item),
  };
}

export function transformBackendCustomer(backendCustomer: BackendCustomer): Customer {
  const budgetRange =
    backendCustomer.budget_range ??
    (backendCustomer.budget_min !== undefined && backendCustomer.budget_max !== undefined
      ? [backendCustomer.budget_min, backendCustomer.budget_max]
      : undefined);
  const budget = backendCustomer.reward
    ? backendCustomer.reward * 2
    : budgetRange
      ? Math.round((budgetRange[0] + budgetRange[1]) / 2)
      : 0;

  return {
    name: backendCustomer.name,
    budget,
    preferences: backendCustomer.preferences || getCustomerPreferences(backendCustomer.order || ''),
    reputation: 0,
    icon: backendCustomer.avatar || backendCustomer.icon || '👤',
  };
}

function getDefaultIcon(type: string): string {
  const iconMap: Record<string, string> = {
    ore: '⛏️',
    fuel: '🔥',
    wood: '🌳',
    metal: '🔩',
    gem: '💎',
    misc: '📦',
  };
  return iconMap[type.toLowerCase()] || '📦';
}

function getDifficultyNumber(difficulty: string): number {
  const difficultyMap: Record<string, number> = {
    easy: 1,
    medium: 2,
    hard: 3,
    expert: 4,
  };
  return difficultyMap[difficulty] || 1;
}

function calculateSellPrice(
  materials: { name: string; quantity: number; cost?: number }[],
  difficulty: string
): number {
  const baseCost = materials.reduce(
    (sum, material) => sum + (material.cost || 10) * material.quantity,
    0
  );
  const difficultyMultiplier = getDifficultyNumber(difficulty) * 0.5 + 1;
  return Math.round(baseCost * difficultyMultiplier);
}

function getRecipeIcon(resultItem: string): string {
  const lowerItem = resultItem.toLowerCase();
  if (lowerItem.includes('sword')) return '⚔️';
  if (lowerItem.includes('dagger')) return '🗡️';
  if (lowerItem.includes('axe')) return '🪓';
  if (lowerItem.includes('ring')) return '💍';
  if (lowerItem.includes('crown')) return '👑';
  if (lowerItem.includes('shield')) return '🛡️';
  return '⚒️';
}

function getCustomerPreferences(order: string): string {
  const lowerOrder = order.toLowerCase();
  if (lowerOrder.includes('sword') || lowerOrder.includes('weapon')) return 'quality';
  if (lowerOrder.includes('ring') || lowerOrder.includes('jewelry')) return 'value';
  if (lowerOrder.includes('crown') || lowerOrder.includes('royal')) return 'luxury';
  return 'balanced';
}
