export const miniGamesAPI = {
  async getHighScore(_gameId: string): Promise<number> {
    return 0;
  },

  async submitScore(_gameId: string, _score: number): Promise<{ success: boolean }> {
    return { success: true };
  },
};
