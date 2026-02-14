import React from 'react';

interface GameNavProps {
  activeTab: string;
  onTabChange: (tab: string) => void;
}

const TABS = [
  { key: 'forge', label: '🔥 Forge' },
  { key: 'recipes', label: '📖 Recipes' },
  { key: 'materials', label: '⚒️ Materials' },
  { key: 'customers', label: '👥 Customers' },
  { key: 'upgrades', label: '⬆️ Upgrades' },
];

const GameNav: React.FC<GameNavProps> = ({ activeTab, onTabChange }) => (
  <nav className="game-nav">
    {TABS.map(tab => (
      <button
        key={tab.key}
        className={`nav-btn${activeTab === tab.key ? ' active' : ''}`}
        data-tab={tab.key}
        onClick={() => onTabChange(tab.key)}
      >
        {tab.label}
      </button>
    ))}
  </nav>
);

export default GameNav;
