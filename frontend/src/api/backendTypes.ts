import { Material, Recipe, Customer, ForgeUpgrade } from '../types/game.d';

export interface ApiResponse<T> {
  success: boolean;
  data?: T;
  message?: string;
  count?: number;
}

export interface BackendMaterial {
  id: number;
  name: string;
  type: string;
  rarity: 'common' | 'uncommon' | 'rare' | 'epic' | 'legendary';
  quantity: number;
  properties: Record<string, unknown>;
  created_at?: string;
  updated_at?: string;
}

export interface BackendRecipe {
  id: number;
  name: string;
  required_materials: { name: string; quantity: number; cost?: number }[];
  result_item: string;
  difficulty: 'easy' | 'medium' | 'hard' | 'expert';
  time: number;
  unlock_level: number;
  created_at?: string;
  updated_at?: string;
}

export interface BackendCustomer {
  id: number;
  name: string;
  avatar?: string;
  order?: string;
  patience?: number;
  reward?: number;
  status?: 'waiting' | 'in_progress' | 'completed' | 'cancelled';
  created_at?: string;
  updated_at?: string;
  budget_range?: [number, number];
  budget_min?: number;
  budget_max?: number;
  preferences?: string;
  icon?: string;
}

export interface BackendUser {
  id: number;
  username: string;
  email?: string;
  created_at: string;
  updated_at?: string;
}

export type { Material, Recipe, Customer, ForgeUpgrade };
