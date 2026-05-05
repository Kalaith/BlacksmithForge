import { axiosClient } from '../apiClient';
import type { AuthProfile, AuthUser } from '../../auth/storage';

export type SessionResponse = {
  user: AuthUser;
  profile: AuthProfile;
};

export const authAPI = {
  async session(token?: string): Promise<SessionResponse | null> {
    try {
      const response = await axiosClient.get<{ success: boolean; data?: SessionResponse }>('/auth/session', {
        headers: token ? { Authorization: `Bearer ${token}` } : undefined,
      });
      return response.data.data ?? null;
    } catch {
      return null;
    }
  },

  async guestSession(): Promise<{ token: string; user: AuthUser; profile: AuthProfile } | null> {
    try {
      const response = await axiosClient.post<{
        success: boolean;
        data?: { token: string; user: AuthUser; profile: AuthProfile };
      }>('/auth/guest-session', {});
      return response.data.data ?? null;
    } catch {
      return null;
    }
  },

  async linkGuest(guestToken: string, token: string): Promise<SessionResponse | null> {
    try {
      const response = await axiosClient.post<{ success: boolean; data?: SessionResponse }>(
        '/auth/link-guest',
        { guest_token: guestToken },
        { headers: { Authorization: `Bearer ${token}` } }
      );
      return response.data.data ?? null;
    } catch {
      return null;
    }
  },
};
