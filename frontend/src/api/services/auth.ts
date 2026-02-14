type SessionResponse = {
  user: { id: number; username?: string };
  profile: { coins: number; level: number };
};

export const authAPI = {
  async session(): Promise<SessionResponse | null> {
    const raw = localStorage.getItem('auth-storage');
    if (!raw) return null;
    try {
      const parsed = JSON.parse(raw) as {
        state?: {
          user?: { id?: number; username?: string };
          profile?: { coins?: number; level?: number };
        };
      };
      if (!parsed.state?.user?.id) return null;
      return {
        user: {
          id: parsed.state.user.id,
          username: parsed.state.user.username,
        },
        profile: {
          coins: parsed.state.profile?.coins ?? 0,
          level: parsed.state.profile?.level ?? 1,
        },
      };
    } catch {
      return null;
    }
  },
};
