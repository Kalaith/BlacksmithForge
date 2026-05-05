import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { GameState } from '../types/game';
import {
  startingExperience,
  startingGold,
  startingLevel,
  startingReputation,
} from '../constants/gameConfig';

export const defaultGameState: GameState = {
  player: {
    gold: startingGold,
    reputation: startingReputation,
    level: startingLevel,
    experience: startingExperience,
  },
  inventory: [],
  unlockedRecipes: ['Iron Dagger', 'Iron Sword'],
  materials: {
    'Iron Ore': 0,
    Coal: 0,
    Wood: 0,
    Leather: 0,
    'Silver Ore': 0,
    Mythril: 0,
  },
  forgeUpgrades: [],
  forgeLit: false,
  currentCustomer: null,
  tutorialCompleted: false,
  tutorialStep: 0,
};

interface GameStore {
  state: GameState;
  setState: (nextState: GameState | ((current: GameState) => GameState)) => void;
  resetGame: () => void;
}

export const useGameStore = create<GameStore>()(
  persist(
    set => ({
      state: defaultGameState,
      setState: nextState =>
        set(current => ({
          state: typeof nextState === 'function' ? nextState(current.state) : nextState,
        })),
      resetGame: () => set({ state: defaultGameState }),
    }),
    {
      name: 'blacksmith-forge-game-state',
    }
  )
);
