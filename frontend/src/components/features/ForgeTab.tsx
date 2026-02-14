import React from 'react';
import ForgeFire from './ForgeFire';
import CraftingArea from './CraftingArea';
import InventoryPanel from './InventoryPanel';
import { useForge } from '../../hooks/useForge';

interface ForgeTabProps {
  active: boolean;
}

const ForgeTab: React.FC<ForgeTabProps> = ({ active }) => {
  const { forgeLit, recipes, selectedRecipe, handleLightForge, handleSelectRecipe, getCanCraft } =
    useForge();

  if (!active) return null;

  return (
    <section id="forge-tab" className="tab-content active">
      <div className="forge-area">
        <div className="forge-main">
          <h2>🔥 Forge</h2>
          <ForgeFire forgeLit={forgeLit} onLightForge={handleLightForge} />
          <CraftingArea
            forgeLit={forgeLit}
            recipes={recipes}
            getCanCraft={getCanCraft}
            selectedRecipe={selectedRecipe}
            onSelectRecipe={handleSelectRecipe}
          />
        </div>
        <div className="inventory-panel">
          <h3>📦 Inventory</h3>
          <InventoryPanel />
        </div>
      </div>
    </section>
  );
};
export default ForgeTab;
