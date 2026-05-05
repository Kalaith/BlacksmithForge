import { CustomerInteraction, SaleResult, SellingPriceInfo } from '../../types';
import { Customer } from '../../types/game.d';
import { apiClient } from '../client';
import { BackendCustomer } from '../backendTypes';
import { transformBackendCustomer } from '../transforms';

const requireData = <T>(success: boolean, data: T | undefined, message?: string): T => {
  if (!success || data === undefined) {
    throw new Error(message || 'Backend request failed');
  }
  return data;
};

const uniqueCustomers = (customers: Customer[]): Customer[] =>
  Array.from(
    new Map(customers.map(customer => [`${customer.name}:${customer.preferences}`, customer])).values()
  );

export const customersAPI = {
  async getAll(): Promise<Customer[]> {
    const response = await apiClient.get<BackendCustomer[]>('/customers');
    return uniqueCustomers(
      requireData(response.success, response.data, response.message).map(transformBackendCustomer)
    );
  },

  async getCurrentCustomer(): Promise<CustomerInteraction | null> {
    const response = await apiClient.get<CustomerInteraction>('/customers/current');
    return response.success ? response.data ?? null : null;
  },

  async generateCustomer(): Promise<CustomerInteraction | null> {
    const response = await apiClient.post<CustomerInteraction>('/customers/generate', {});
    return response.success ? response.data ?? null : null;
  },

  async getSellingPrice(
    itemId: number,
    customerId: number
  ): Promise<SellingPriceInfo> {
    const response = await apiClient.get<SellingPriceInfo>(`/customers/price/${itemId}/${customerId}`);
    return requireData(response.success, response.data, response.message);
  },

  async sellItem(payload: { itemId: number; customerId: number }): Promise<SaleResult | null> {
    const response = await apiClient.post<SaleResult>('/customers/sell', {
      item_id: payload.itemId,
      customer_id: payload.customerId,
    });
    return response.success ? response.data ?? null : null;
  },

  async dismissCustomer(): Promise<boolean> {
    const response = await apiClient.post<unknown>('/customers/dismiss', {});
    return response.success;
  },
};
