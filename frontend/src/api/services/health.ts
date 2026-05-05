import { axiosClient } from '../apiClient';

export const healthAPI = {
  async check(): Promise<boolean> {
    try {
      const response = await axiosClient.get('/health');
      return response.status >= 200 && response.status < 300;
    } catch (error) {
      console.error('Health check failed:', error);
      return false;
    }
  },
};
