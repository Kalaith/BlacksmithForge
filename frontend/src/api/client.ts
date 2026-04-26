import { ApiResponse } from './backendTypes';
import { getActiveToken, persistLoginUrl } from '../auth/storage';

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

// HTTP Client
export class ApiClient {
  private baseURL: string;

  constructor(baseURL: string) {
    this.baseURL = baseURL;
  }

  private async request<T>(endpoint: string, options: RequestInit = {}): Promise<ApiResponse<T>> {
    const url = `${this.baseURL}${endpoint}`;

    try {
      const storedToken = getActiveToken();

      const mergedHeaders: Record<string, string> = {
        'Content-Type': 'application/json',
        ...(options.headers as Record<string, string>),
      };
      if (storedToken && !mergedHeaders.Authorization) {
        mergedHeaders.Authorization = `Bearer ${storedToken}`;
      }

      const response = await fetch(url, {
        ...options,
        headers: mergedHeaders,
      });

      if (!response.ok) {
        let payload: unknown = null;
        try {
          payload = await response.clone().json();
        } catch {
          payload = null;
        }

        if (response.status === 401) {
          const loginUrl =
            payload &&
            typeof payload === 'object' &&
            'login_url' in payload &&
            typeof payload.login_url === 'string'
              ? payload.login_url
              : undefined;
          if (loginUrl) {
            persistLoginUrl(loginUrl);
            window.dispatchEvent(
              new CustomEvent('webhatchery:login-required', { detail: { loginUrl } })
            );
          }
        }

        const message =
          payload &&
          typeof payload === 'object' &&
          'message' in payload &&
          typeof payload.message === 'string'
            ? payload.message
            : `HTTP error! status: ${response.status}`;
        throw new ApiRequestError(response.status, message, payload);
      }

      const data = (await response.json()) as ApiResponse<T>;
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
