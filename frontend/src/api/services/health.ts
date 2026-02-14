import { apiBaseUrl } from '../client';

export const healthAPI = {
  async check(): Promise<boolean> {
    try {
      const response = await fetch(`${apiBaseUrl}/health`);
      return response.ok;
    } catch (error) {
      console.error('Health check failed:', error);
      return false;
    }
  },
};
