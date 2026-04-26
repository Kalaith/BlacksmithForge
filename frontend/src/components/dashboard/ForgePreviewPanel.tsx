import React from 'react';
import { uiAssets } from '../../data/ui-assets';
import { Recipe } from '../../types';
import FramedButton from '../ui/FramedButton';
import OrnatePanel from '../ui/OrnatePanel';

interface ForgePreviewPanelProps {
  forgeLit: boolean;
  recipes: Recipe[];
  selectedRecipe: string | null;
  onLightForge: () => void;
  onViewRecipes: () => void;
}

const ForgePreviewPanel: React.FC<ForgePreviewPanelProps> = ({
  forgeLit,
  recipes,
  selectedRecipe,
  onLightForge,
  onViewRecipes,
}) => (
  <OrnatePanel title="The Forge" icon="🔥" className="dashboard-panel dashboard-panel--forge">
    <div
      className={`forge-art ${forgeLit ? 'is-lit' : ''}`}
      style={{ '--panel-art': `url("${uiAssets.forge}")` } as React.CSSProperties}
    >
      <span>{forgeLit ? '🔥🔥🔥' : '🔥'}</span>
    </div>
    <p className="dashboard-copy">
      {selectedRecipe
        ? `${selectedRecipe} is selected for your next craft.`
        : 'Smelt raw materials and shape your legacy in fire and steel.'}
    </p>
    <div className="dashboard-split-actions">
      <FramedButton icon="🔥" variant="primary" onClick={onLightForge}>
        {forgeLit ? 'Forge Lit' : 'Light the Forge'}
      </FramedButton>
      <FramedButton icon="📖" onClick={onViewRecipes} disabled={recipes.length === 0}>
        View Recipes
      </FramedButton>
    </div>
  </OrnatePanel>
);

export default ForgePreviewPanel;

