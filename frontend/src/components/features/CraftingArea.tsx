import React from 'react';
import RecipeSelector from './RecipeSelector';
import CraftingInterface from './CraftingInterface';
import { Recipe } from '../../types';

interface CraftingAreaProps {
  forgeLit: boolean;
  recipes: Recipe[];
  getCanCraft: (recipeName: string) => boolean;
  selectedRecipe: string | null;
  onSelectRecipe: (recipeName: string) => void;
}

const CraftingArea: React.FC<CraftingAreaProps> = ({
  forgeLit,
  recipes,
  getCanCraft,
  selectedRecipe,
  onSelectRecipe,
}) =>
  forgeLit ? (
    <div className="crafting-area" id="crafting-area">
      <h3>Select Recipe to Craft</h3>
      <RecipeSelector
        recipes={recipes}
        onSelectRecipe={onSelectRecipe}
        selectedRecipeId={selectedRecipe || undefined}
        getCanCraft={getCanCraft}
      />
      {selectedRecipe && <CraftingInterface selectedRecipe={selectedRecipe} />}
    </div>
  ) : null;

export default CraftingArea;
