import React, { createContext, useContext, ReactNode } from 'react';
import { useGameData, useAuth } from '../hooks/useAPI';
import { Material, Recipe, Customer, ForgeUpgrade } from '../types/game.d';

type AuthUser = {
  id: number;
  username?: string;
  email?: string;
  [key: string]: unknown;
};

type AuthProfile = Record<string, unknown>;

interface GameDataContextType {
  materials: Material[];
  recipes: Recipe[];
  customers: Customer[];
  upgrades: ForgeUpgrade[];
  loading: boolean;
  error: string | null;
  reload: () => void;
}

interface AuthContextType {
  user: AuthUser | null;
  profile: AuthProfile | null;
  loading: boolean;
  error: string | null;
  register: (username: string, password: string) => Promise<unknown>;
  login: (username: string, password: string) => Promise<unknown>;
  logout: () => Promise<void>;
  refreshSession: () => Promise<void>;
  isAuthenticated: boolean;
}

const GameDataContext = createContext<GameDataContextType | undefined>(undefined);
const AuthContext = createContext<AuthContextType | undefined>(undefined);

interface GameDataProviderProps {
  children: ReactNode;
}

export function GameDataProvider({ children }: GameDataProviderProps) {
  const gameData = useGameData();
  const auth = useAuth() as AuthContextType;

  return (
    <AuthContext.Provider value={auth}>
      <GameDataContext.Provider value={gameData}>{children}</GameDataContext.Provider>
    </AuthContext.Provider>
  );
}

/* eslint-disable-next-line react-refresh/only-export-components */
export function useGameDataContext() {
  const context = useContext(GameDataContext);
  if (context === undefined) {
    throw new Error('useGameDataContext must be used within a GameDataProvider');
  }
  return context;
}

/* eslint-disable-next-line react-refresh/only-export-components */
export function useAuthContext() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuthContext must be used within a GameDataProvider');
  }
  return context;
}

// HOC for components that need game data
/* eslint-disable-next-line react-refresh/only-export-components */
export function withGameData<P extends object>(
  Component: React.ComponentType<P & GameDataContextType>
) {
  return function WithGameDataComponent(props: P) {
    const gameData = useGameDataContext();
    return <Component {...props} {...gameData} />;
  };
}

// HOC for components that need auth
/* eslint-disable-next-line react-refresh/only-export-components */
export function withAuth<P extends object>(Component: React.ComponentType<P & AuthContextType>) {
  return function WithAuthComponent(props: P) {
    const auth = useAuthContext();
    return <Component {...props} {...auth} />;
  };
}
