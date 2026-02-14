// Customer-related types and interfaces
import { InventoryItem } from './inventory';

export interface CustomerInteraction {
  id: number;
  user_id: number;
  name: string;
  budget: number;
  preferences: 'quality' | 'value' | 'durability';
  icon: string;
  description: string;
  status: 'waiting' | 'browsing' | 'completed' | 'dismissed';
  created_at: string;
}

export interface SellingPriceInfo {
  base_price: number;
  final_price: number;
  modifier: number;
  reason: string;
  can_afford: boolean;
  customer_budget: number;
  item: InventoryItem;
  customer: CustomerInteraction;
}

export interface SaleResult {
  sale_price: number;
  base_price: number;
  modifier: number;
  reason: string;
  new_gold: number;
  new_reputation: number;
  item: InventoryItem;
  customer: CustomerInteraction;
}

export interface CustomerState {
  currentCustomer: CustomerInteraction | null;
  availableCustomers: CustomerInteraction[];
  loading: boolean;
  error: string | null;
}
