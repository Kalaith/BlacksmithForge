import { ApiResponse } from './backendTypes';

// API Configuration
const basePath = (import.meta.env.BASE_URL || '/').replace(/\/$/, '');
const defaultApiBase = `${window.location.origin}${basePath}/api/v1`;
export const apiBaseUrl = import.meta.env.VITE_API_URL || defaultApiBase;

// HTTP Client
export class ApiClient {
  private baseURL: string;

  constructor(baseURL: string) {
    this.baseURL = baseURL;
  }

  private async request<T>(endpoint: string, options: RequestInit = {}): Promise<ApiResponse<T>> {
    const url = `${this.baseURL}${endpoint}`;

    try {
      const authStorageRaw = localStorage.getItem('auth-storage');
      let storedToken: string | null = null;
      if (authStorageRaw) {
        try {
          const parsed = JSON.parse(authStorageRaw);
          storedToken = parsed?.state?.token ?? null;
        } catch {
          storedToken = null;
        }
      }

      const mergedHeaders: Record<string, string> = {
        'Content-Type': 'application/json',
        ...(options.headers as Record<string, string>),
      };
      if (storedToken && !mergedHeaders.Authorization) {
        mergedHeaders.Authorization = `Bearer ${storedToken}`;
      }

      const response = await fetch(url, {
        headers: mergedHeaders,
        ...options,
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const data = await response.json();
      return data;
    } catch (error) {
      console.error('API request failed:', error);
      throw error;
    }
  }

  async get<T>(endpoint: string): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, { method: 'GET' });
  }

  async post<T, TBody = unknown>(endpoint: string, data: TBody): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, {
      method: 'POST',
      body: JSON.stringify(data),
    });
  }

  async put<T, TBody = unknown>(endpoint: string, data: TBody): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
  }

  async delete<T>(endpoint: string): Promise<ApiResponse<T>> {
    return this.request<T>(endpoint, { method: 'DELETE' });
  }
}

export const apiClient = new ApiClient(apiBaseUrl);
