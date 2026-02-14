import { Material, Recipe, Customer, ForgeUpgrade } from '../types/game';

export const MATERIALS: Material[] = [
  {
    name: 'Iron Ore',
    cost: 5,
    quality: 'common',
    description: 'Basic material for most weapons',
    icon: '⛏️',
  },
  {
    name: 'Coal',
    cost: 2,
    quality: 'common',
    description: 'Fuel for the forge',
    icon: '🔥',
  },
  {
    name: 'Wood',
    cost: 3,
    quality: 'common',
    description: 'For handles and hilts',
    icon: '🌳',
  },
  {
    name: 'Leather',
    cost: 4,
    quality: 'common',
    description: 'For grips and armor padding',
    icon: '🐄',
  },
  {
    name: 'Silver Ore',
    cost: 25,
    quality: 'rare',
    description: 'Precious metal for decorative elements',
    icon: '✨',
  },
  {
    name: 'Mythril',
    cost: 100,
    quality: 'legendary',
    description: 'Rare magical metal',
    icon: '💎',
  },
];

export const RECIPES: Recipe[] = [
  {
    name: 'Iron Dagger',
    materials: { 'Iron Ore': 1, Wood: 1, Coal: 1 },
    sellPrice: 15,
    difficulty: 1,
    icon: '🗡️',
  },
  {
    name: 'Iron Sword',
    materials: { 'Iron Ore': 2, Wood: 1, Leather: 1, Coal: 2 },
    sellPrice: 35,
    difficulty: 2,
    icon: '⚔️',
  },
  {
    name: 'Steel Axe',
    materials: { 'Iron Ore': 3, Wood: 2, Coal: 3 },
    sellPrice: 45,
    difficulty: 3,
    icon: '🪓',
  },
  {
    name: 'Silver Blade',
    materials: { 'Silver Ore': 1, 'Iron Ore': 1, Wood: 1, Coal: 2 },
    sellPrice: 80,
    difficulty: 4,
    icon: '🌟',
  },
];

export const CUSTOMERS: Customer[] = [
  {
    name: 'Village Guard',
    budget: 50,
    preferences: 'durability',
    reputation: 0,
    icon: '🛡️',
  },
  {
    name: 'Traveling Merchant',
    budget: 30,
    preferences: 'value',
    reputation: 0,
    icon: '🎒',
  },
  {
    name: 'Noble Knight',
    budget: 150,
    preferences: 'quality',
    reputation: 0,
    icon: '👑',
  },
  {
    name: 'Young Adventurer',
    budget: 25,
    preferences: 'balanced',
    reputation: 0,
    icon: '🗡️',
  },
];

export const forgeUpgrades: ForgeUpgrade[] = [
  {
    name: 'Better Bellows',
    cost: 100,
    effect: 'Reduces coal consumption',
    icon: '💨',
  },
  {
    name: 'Precision Anvil',
    cost: 200,
    effect: 'Improves crafting accuracy',
    icon: '🔨',
  },
  {
    name: 'Master Tools',
    cost: 500,
    effect: 'Unlocks advanced recipes',
    icon: '⚒️',
  },
];

export const tutorialSteps: string[] = [
  "Welcome to your forge! You'll craft weapons and armor to sell to customers.",
  "First, click on the forge fire to light it. You'll need this for crafting.",
  'Check your Materials tab to buy raw materials like Iron Ore, Coal, and Wood.',
  'Visit the Recipes tab to see what you can craft and what materials you need.',
  'Return to the Forge to start crafting your first weapon!',
  'Sell your crafted items to customers in the Customers tab to earn gold.',
  'Use your profits to buy better materials and upgrade your forge!',
];
