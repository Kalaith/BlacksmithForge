import React from 'react';
import ForgeNavigation from './ForgeNavigation';

interface GameNavProps {
  activeTab: string;
  onTabChange: (tab: string) => void;
}

const GameNav: React.FC<GameNavProps> = ({ activeTab, onTabChange }) => (
  <ForgeNavigation activeTab={activeTab} onTabChange={onTabChange} />
);

export default GameNav;
