import React from 'react';

const GameLayout: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <div className="game-container">{children}</div>
);

export default GameLayout;
