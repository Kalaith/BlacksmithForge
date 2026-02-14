import { CUSTOMERS } from '../../constants/gameData';
import { CustomerInteraction, SaleResult, SellingPriceInfo } from '../../types';
import { Customer } from '../../types/game.d';
import { inventoryAPI } from './inventory';

const currentCustomerKey = 'bf_current_customer';

const toInteraction = (index: number) =>
  ({
    id: index + 1,
    user_id: 0,
    name: CUSTOMERS[index].name,
    budget: CUSTOMERS[index].budget,
    preferences:
      CUSTOMERS[index].preferences === 'durability'
        ? 'durability'
        : CUSTOMERS[index].preferences === 'value'
          ? 'value'
          : 'quality',
    icon: CUSTOMERS[index].icon,
    description: `${CUSTOMERS[index].name} is browsing your forge`,
    status: 'waiting',
    created_at: new Date().toISOString(),
  }) satisfies CustomerInteraction;

const getAuthedUserId = (): number => {
  try {
    const raw = localStorage.getItem('auth-storage');
    if (!raw) return 0;
    const parsed = JSON.parse(raw) as { state?: { user?: { id?: number } } };
    return Number(parsed.state?.user?.id ?? 0);
  } catch {
    return 0;
  }
};

export const customersAPI = {
  async getAll(): Promise<Customer[]> {
    return CUSTOMERS;
  },

  async getCurrentCustomer(userId: number): Promise<CustomerInteraction | null> {
    const raw = localStorage.getItem(`${currentCustomerKey}_${userId}`);
    if (!raw) return null;
    try {
      return JSON.parse(raw) as CustomerInteraction;
    } catch {
      return null;
    }
  },

  async generateCustomer(): Promise<CustomerInteraction | null> {
    const userId = getAuthedUserId();
    if (!userId) return null;
    const pick = Math.floor(Math.random() * CUSTOMERS.length);
    const customer = { ...toInteraction(pick), user_id: userId };
    localStorage.setItem(`${currentCustomerKey}_${userId}`, JSON.stringify(customer));
    return customer;
  },

  async getSellingPrice(
    userId: number,
    itemId: number,
    customerId: number
  ): Promise<SellingPriceInfo> {
    const customer = (await this.getCurrentCustomer(userId)) ?? {
      ...toInteraction(0),
      id: customerId,
      user_id: userId,
    };
    const item = (await inventoryAPI.getUserInventory(userId)).find(i => i.id === itemId);
    const base = item?.value ?? 0;
    const modifier = customer.preferences === 'quality' ? 1.2 : 1;
    const finalPrice = Math.round(base * modifier);
    return {
      base_price: base,
      final_price: finalPrice,
      modifier,
      reason: 'Customer preference modifier',
      can_afford: customer.budget >= finalPrice,
      customer_budget: customer.budget,
      item: item ?? {
        id: itemId,
        name: 'Unknown Item',
        icon: '??',
        value: 0,
        type: 'weapon',
      },
      customer,
    };
  },

  async sellItem(payload: { itemId: number; customerId: number }): Promise<SaleResult | null> {
    const userId = getAuthedUserId();
    if (!userId) return null;
    const pricing = await this.getSellingPrice(userId, payload.itemId, payload.customerId);
    if (!pricing.can_afford) return null;
    await inventoryAPI.removeItem(userId, pricing.item);

    return {
      sale_price: pricing.final_price,
      base_price: pricing.base_price,
      modifier: pricing.modifier,
      reason: pricing.reason,
      new_gold: pricing.final_price,
      new_reputation: 1,
      item: pricing.item,
      customer: pricing.customer,
    };
  },

  async dismissCustomer(): Promise<boolean> {
    const userId = getAuthedUserId();
    if (!userId) return false;
    localStorage.removeItem(`${currentCustomerKey}_${userId}`);
    return true;
  },
};
