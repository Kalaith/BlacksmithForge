import { ApiResponse } from './backendTypes';
import { axiosClient } from './apiClient';

const requireEnv = (name: keyof ImportMetaEnv): string => {
  const value = import.meta.env[name];
  if (!value) {
    throw new Error(`Missing required environment variable: ${name}`);
  }
  return value;
};

export const apiBaseUrl = requireEnv('VITE_API_URL');

export class ApiRequestError extends Error {
  readonly status: number;
  readonly payload: unknown;

  constructor(status: number, message: string, payload: unknown) {
    super(message);
    this.name = 'ApiRequestError';
    this.status = status;
    this.payload = payload;
  }
}

export class ApiClient {
  constructor(private readonly baseURL: string) {
  }

  private async request<T>(endpoint: string, method: 'GET' | 'POST' | 'PUT' | 'DELETE', data?: unknown): Promise<ApiResponse<T>> {
    try {
      const response = await axiosClient.request<ApiResponse<T>>({
        url: endpoint,
        method,
        data,
      });
      return response.data;
    } catch (error) {
      if (
        error &&
        typeof error === 'object' &&
        'response' in error &&
        error.response &&
        typeof error.response === 'object' &&
        'status' in error.response
      ) {
        const response = error.response as { status: number; data?: unknown };
        const payload = response.data;
        const message =
          payload &&
          typeof payload === 'object' &&
          'message' in payload &&
          typeof payload.message === 'string'
            ? payload.message
            : `HTTP error! status: ${response.status}`;
        throw new ApiRequestError(response.status, message, payload ?? null);
      }
      throw error;
    }
  }

  async get<T>(endpoint: string): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, 'GET');
  }

  async post<T, TBody = unknown>(endpoint: string, data: TBody): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, 'POST', data);
  }

  async put<T, TBody = unknown>(endpoint: string, data: TBody): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, 'PUT', data);
  }

  async delete<T>(endpoint: string): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, 'DELETE');
  }
}

export const apiClient = new ApiClient(apiBaseUrl);
