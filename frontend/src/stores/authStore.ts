import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import type { AuthProfile, AuthUser, GuestSessionData } from '../auth/storage';

type AuthMode = 'frontpage' | 'guest' | null;

interface AuthStore {
  user: AuthUser | null;
  profile: AuthProfile | null;
  authMode: AuthMode;
  loginUrl: string | null;
  guestSession: GuestSessionData | null;
  setSession: (user: AuthUser, profile: AuthProfile | null, authMode: AuthMode) => void;
  setGuestSession: (session: GuestSessionData | null) => void;
  setLoginUrl: (loginUrl: string | null) => void;
  clearSession: () => void;
}

export const useAuthStore = create<AuthStore>()(
  persist(
    set => ({
      user: null,
      profile: null,
      authMode: null,
      loginUrl: null,
      guestSession: null,
      setSession: (user, profile, authMode) => set({ user, profile, authMode }),
      setGuestSession: guestSession => set({ guestSession }),
      setLoginUrl: loginUrl => set({ loginUrl }),
      clearSession: () =>
        set({
          user: null,
          profile: null,
          authMode: null,
          guestSession: null,
        }),
    }),
    {
      name: 'blacksmith-forge-auth-state',
      partialize: state => ({
        loginUrl: state.loginUrl,
        guestSession: state.guestSession,
      }),
    }
  )
);
