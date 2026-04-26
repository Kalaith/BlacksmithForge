import React from 'react';
import ForgeShell from './ForgeShell';

const GameLayout: React.FC<{ children: React.ReactNode }> = ({ children }) => (
  <ForgeShell>{children}</ForgeShell>
);

export default GameLayout;
