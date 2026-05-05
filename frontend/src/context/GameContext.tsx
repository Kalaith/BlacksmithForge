import React from 'react';
import { useGameStore } from '../stores/gameStore';

export const GameProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  return <>{children}</>;
};

/* eslint-disable-next-line react-refresh/only-export-components */
export const useGame = useGameStore;
