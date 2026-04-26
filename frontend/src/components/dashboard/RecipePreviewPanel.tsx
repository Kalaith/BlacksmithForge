import React from 'react';
import { uiAssets } from '../../data/ui-assets';
import { Recipe } from '../../types';
import FramedButton from '../ui/FramedButton';
import OrnatePanel from '../ui/OrnatePanel';

interface RecipePreviewPanelProps {
  recipes: Recipe[];
  onViewRecipes: () => void;
}

const RecipePreviewPanel: React.FC<RecipePreviewPanelProps> = ({ recipes, onViewRecipes }) => (
  <OrnatePanel title="Recipe Book" icon="📖" className="dashboard-panel dashboard-panel--summary">
    <div
      className="dashboard-asset"
      style={{ '--panel-art': `url("${uiAssets.recipes}")` } as React.CSSProperties}
    />
    <p className="dashboard-copy">Discover blueprints and craft powerful items.</p>
    <div className="dashboard-ledger">
      {recipes.slice(0, 3).map(recipe => (
        <div key={recipe.name}>
          <span>
            {recipe.icon} {recipe.name}
          </span>
          <strong>{recipe.sellPrice}g</strong>
        </div>
      ))}
    </div>
    <FramedButton icon="📖" onClick={onViewRecipes}>
      View Recipes
    </FramedButton>
  </OrnatePanel>
);

export default RecipePreviewPanel;

