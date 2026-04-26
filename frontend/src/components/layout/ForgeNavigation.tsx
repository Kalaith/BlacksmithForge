import React from 'react';

interface ForgeNavigationProps {
  activeTab: string;
  onTabChange: (tab: string) => void;
}

const TABS = [
  { key: 'forge', label: 'Forge', icon: '🔥' },
  { key: 'recipes', label: 'Recipes', icon: '📖' },
  { key: 'materials', label: 'Materials', icon: '⚒️' },
  { key: 'customers', label: 'Customers', icon: '👥' },
  { key: 'upgrades', label: 'Upgrades', icon: '⬆️' },
];

const ForgeNavigation: React.FC<ForgeNavigationProps> = ({ activeTab, onTabChange }) => (
  <nav className="forge-navigation">
    {TABS.map(tab => (
      <button
        key={tab.key}
        className={`forge-navigation__tab${activeTab === tab.key ? ' is-active' : ''}`}
        data-tab={tab.key}
        onClick={() => onTabChange(tab.key)}
      >
        <span>{tab.icon}</span>
        {tab.label}
      </button>
    ))}
  </nav>
);

export default ForgeNavigation;

