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
  <OrnatePanel
    title="The Forge"
    icon="🔥"
    className="dashboard-panel dashboard-panel--forge"
    variant="primary"
  >
    <button
      className={`forge-action ${forgeLit ? 'is-lit' : ''}`}
      onClick={onLightForge}
      type="button"
      style={{ '--panel-art': `url("${uiAssets.forge}")` } as React.CSSProperties}
    >
      <span className="forge-action__fire" aria-hidden="true">
        🔥
      </span>
      <span className="forge-action__label">{forgeLit ? 'Forge Burning' : 'Light the Forge'}</span>
      <span className="forge-action__hint">Pull heat from the coals</span>
    </button>
    <p className="dashboard-copy">
      {selectedRecipe
        ? `${selectedRecipe} is selected for your next craft.`
        : 'Smelt raw materials and shape your legacy in fire and steel.'}
    </p>
    <div className="dashboard-split-actions">
      <FramedButton icon="📖" onClick={onViewRecipes} disabled={recipes.length === 0}>
        Choose a Recipe
      </FramedButton>
    </div>
  </OrnatePanel>
);

export default ForgePreviewPanel;
