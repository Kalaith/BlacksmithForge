import { apiBaseUrl } from '../client';
import type { AuthProfile, AuthUser } from '../../auth/storage';

export type SessionResponse = {
  user: AuthUser;
  profile: AuthProfile;
};

const parseJson = async <T>(response: Response): Promise<T> => {
  return (await response.json()) as T;
};

export const authAPI = {
  async session(token?: string): Promise<SessionResponse | null> {
    const response = await fetch(`${apiBaseUrl}/auth/session`, {
      headers: token ? { Authorization: `Bearer ${token}` } : undefined,
    });

    if (!response.ok) {
      return null;
    }

    const payload = await parseJson<{ success: boolean; data?: SessionResponse }>(response);
    return payload.data ?? null;
  },

  async guestSession(): Promise<{ token: string; user: AuthUser; profile: AuthProfile } | null> {
    const response = await fetch(`${apiBaseUrl}/auth/guest-session`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
    });

    if (!response.ok) {
      return null;
    }

    const payload = await parseJson<{
      success: boolean;
      data?: { token: string; user: AuthUser; profile: AuthProfile };
    }>(response);

    return payload.data ?? null;
  },

  async linkGuest(guestUserId: number, token: string): Promise<SessionResponse | null> {
    const response = await fetch(`${apiBaseUrl}/auth/link-guest`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify({
        guest_user_id: guestUserId,
      }),
    });

    if (!response.ok) {
      return null;
    }

    const payload = await parseJson<{ success: boolean; data?: SessionResponse }>(response);
    return payload.data ?? null;
  },
};
