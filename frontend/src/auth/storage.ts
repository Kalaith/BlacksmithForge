export interface AuthUser {
  id: number;
  username?: string;
  email?: string;
  display_name?: string;
  auth_provider?: string;
  auth_type?: string;
  is_guest?: boolean;
  guest_user_id?: number | null;
}

export interface AuthProfile {
  forge_name?: string;
  coins?: number;
  level?: number;
  reputation?: number;
  [key: string]: unknown;
}

export interface GuestSessionData {
  token: string;
  user: AuthUser;
  profile?: AuthProfile | null;
}

export const WEBHATCHERY_AUTH_STORAGE_KEY = 'auth-storage';
export const BLACKSMITH_GUEST_STORAGE_KEY = 'blacksmith-forge-guest-session';

export const getFrontpageToken = (): string | null => {
  try {
    const raw = localStorage.getItem(WEBHATCHERY_AUTH_STORAGE_KEY);
    if (!raw) return null;
    const parsed = JSON.parse(raw) as { state?: { token?: string | null } };
    return parsed.state?.token ?? null;
  } catch {
    return null;
  }
};

export const persistLoginUrl = (loginUrl: string): void => {
  useAuthStore.getState().setLoginUrl(loginUrl);
};

export const getGuestSession = (): GuestSessionData | null => {
  return useAuthStore.getState().guestSession;
};

export const saveGuestSession = (session: GuestSessionData): void => {
  useAuthStore.getState().setGuestSession(session);
};

export const clearGuestSession = (): void => {
  useAuthStore.getState().setGuestSession(null);
};

export const getActiveToken = (): string | null => {
  const frontpageToken = getFrontpageToken();
  if (frontpageToken) {
    return frontpageToken;
  }

  const guest = getGuestSession();
  if (guest?.token) {
    return guest.token;
  }

  return null;
};

export const getActiveUserId = (): number => {
  const guest = getGuestSession();
  if (guest?.user?.id) {
    return Number(guest.user.id);
  }

  try {
    const raw = localStorage.getItem(WEBHATCHERY_AUTH_STORAGE_KEY);
    if (!raw) return 0;
    const parsed = JSON.parse(raw) as { state?: { user?: { id?: number | string } } };
    return Number(parsed.state?.user?.id ?? 0);
  } catch {
    return 0;
  }
};
import { useAuthStore } from '../stores/authStore';
