// Materials and inventory related types

/**
 * Materials state interface
 */
export interface MaterialsState {
  materials: Record<string, number>;
  loading: boolean;
  error: string | null;
}

/**
 * Materials hook return type
 */
export interface UseMaterialsReturn {
  materials: Record<string, number>;
  loading: boolean;
  error: string | null;
  purchaseMaterial: (materialId: number, quantity: number) => Promise<boolean>;
  refreshMaterials: () => void;
  clearError: () => void;
}

/**
 * Material purchase request
 */
export interface MaterialPurchaseRequest {
  user_id: number;
  material_id: number;
  quantity: number;
}

/**
 * Material purchase result
 */
export interface MaterialPurchaseResult {
  success: boolean;
  new_quantity: number;
  cost: number;
  new_gold: number;
  message?: string;
}

/**
 * User materials response from backend
 */
export interface UserMaterialsResponse {
  [materialName: string]: number;
}
