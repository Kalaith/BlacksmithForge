// Crafting-related types and interfaces
import { Recipe } from './game.d';

export interface CraftingState {
  hammerClicks: number;
  hammerAccuracy: number;
  craftingStarted: boolean;
  result: CraftingResult | null;
  loading: boolean;
  error: string | null;
  sessionId: number | null;
  maxHammerClicks: number;
}

export interface CraftingResult {
  success: boolean;
  quality: 'Poor' | 'Fair' | 'Good' | 'Excellent';
  item: {
    name: string;
    icon: string;
    quality: string;
    value: number;
    type: string;
    stats?: {
      attack?: number;
      defense?: number;
      speed?: number;
      durability?: number;
    };
  };
  message?: string;
}

export interface CraftedItem {
  name: string;
  icon: string;
  quality: string;
  value: number;
  type: string;
}

export interface CraftingSession {
  session_id: number;
  user_id: number;
  recipe_id: number;
  status: 'in_progress' | 'completed' | 'failed';
  hammer_clicks: number;
  total_accuracy: number;
  started_at: string;
  completed_at?: string;
  materials_consumed: string;
  result_quality?: string;
  result_value?: number;
  item_id?: number;
}

export interface CraftingValidation {
  can_craft: boolean;
  recipe: Recipe | null;
  user_materials: Record<string, number>;
  missing_materials: Array<{
    name: string;
    required: number;
    available: number;
    missing: number;
  }>;
  message: string;
}

export interface HammerHitResult {
  hit_success: boolean;
  accuracy_increase: number;
  total_accuracy: number;
  hammer_clicks: number;
  max_clicks: number;
  is_complete: boolean;
  remaining_clicks: number;
}
