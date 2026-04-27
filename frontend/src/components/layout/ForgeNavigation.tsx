import React from 'react';

interface ForgeNavigationProps {
  activeTab: string;
  onTabChange: (tab: string) => void;
}

const TABS = [
  { key: 'forge', label: 'Forge', icon: '🔥', station: 'Anvil' },
  { key: 'recipes', label: 'Recipes', icon: '📖', station: 'Book' },
  { key: 'materials', label: 'Materials', icon: '📦', station: 'Crate' },
  { key: 'customers', label: 'Customers', icon: '📌', station: 'Board' },
  { key: 'upgrades', label: 'Upgrades', icon: '🛠️', station: 'Bench' },
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
        <span className="forge-navigation__icon">{tab.icon}</span>
        <span className="forge-navigation__text">
          <strong>{tab.label}</strong>
          <small>{tab.station}</small>
        </span>
      </button>
    ))}
  </nav>
);

export default ForgeNavigation;
