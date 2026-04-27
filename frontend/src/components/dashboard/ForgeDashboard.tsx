import React from 'react';
import { useInventory } from '../../hooks/useAPI';
import { useForge } from '../../hooks/useForge';
import { useAuthContext, useGameDataContext } from '../../providers/GameDataProvider';
import CustomerPreviewPanel from './CustomerPreviewPanel';
import ForgePreviewPanel from './ForgePreviewPanel';
import InventoryPreviewPanel from './InventoryPreviewPanel';
import MarketPreviewPanel from './MarketPreviewPanel';
import RecipePreviewPanel from './RecipePreviewPanel';
import UpgradePreviewPanel from './UpgradePreviewPanel';

interface ForgeDashboardProps {
  onTabChange: (tab: string) => void;
}

const ForgeDashboard: React.FC<ForgeDashboardProps> = ({ onTabChange }) => {
  const { user } = useAuthContext();
  const { customers, materials, recipes, upgrades } = useGameDataContext();
  const { inventory, loading: inventoryLoading } = useInventory(user?.id);
  const { forgeLit, selectedRecipe, handleLightForge } = useForge();

  return (
    <section id="forge-tab" className="tab-content active forge-dashboard">
      <CustomerPreviewPanel customers={customers} onViewAll={() => onTabChange('customers')} />
      <ForgePreviewPanel
        forgeLit={forgeLit}
        recipes={recipes}
        selectedRecipe={selectedRecipe}
        onLightForge={handleLightForge}
        onViewRecipes={() => onTabChange('recipes')}
      />
      <InventoryPreviewPanel inventory={inventory} loading={inventoryLoading} />
      <div className="forge-dashboard__tools">
        <MarketPreviewPanel materials={materials} onBrowse={() => onTabChange('materials')} />
        <RecipePreviewPanel recipes={recipes} onViewRecipes={() => onTabChange('recipes')} />
        <UpgradePreviewPanel upgrades={upgrades} onViewUpgrades={() => onTabChange('upgrades')} />
      </div>
    </section>
  );
};

export default ForgeDashboard;
