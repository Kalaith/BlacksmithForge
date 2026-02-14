import React from 'react';
import { Recipe } from '../../types';

interface RecipeSelectorProps {
  recipes: Recipe[];
  onSelectRecipe: (recipeId: string) => void;
  selectedRecipeId?: string;
  getCanCraft: (recipeName: string) => boolean;
}

const RecipeSelector: React.FC<RecipeSelectorProps> = ({
  recipes,
  onSelectRecipe,
  selectedRecipeId,
  getCanCraft,
}) => {
  if (!recipes || recipes.length === 0) {
    return <div className="no-recipes">No recipes available.</div>;
  }

  return (
    <div className="recipe-selector">
      <div className="recipes-grid">
        {recipes.map(recipe => {
          const canCraft = getCanCraft(recipe.name);
          const isSelected = selectedRecipeId === recipe.name;

          return (
            <div
              key={recipe.name}
              className={`recipe-card ${canCraft ? 'craftable' : 'not-craftable'} ${isSelected ? 'selected' : ''}`}
              onClick={() => onSelectRecipe(recipe.name)}
            >
              <div className="recipe-header">
                <div className="recipe-title">
                  <span className="recipe-icon">{recipe.icon}</span>
                  <span className="recipe-name">{recipe.name}</span>
                </div>
              </div>

              <div className="recipe-stats">
                <div className="stat">
                  <span className="stat-label">Sell Price:</span>
                  <span className="stat-value">{recipe.sellPrice}g</span>
                </div>
                <div className="stat">
                  <span className="stat-label">Difficulty:</span>
                  <span className="stat-value">{'★'.repeat(recipe.difficulty)}</span>
                </div>
              </div>

              <div className="recipe-materials">
                <div className="materials-label">Required Materials:</div>
                {Object.entries(recipe.materials).map(([materialName, quantity]) => (
                  <div key={materialName} className="material-requirement">
                    {materialName}: {quantity}
                  </div>
                ))}
              </div>

              {!canCraft && (
                <div className="craft-status not-available">
                  Cannot craft - insufficient materials
                </div>
              )}

              {canCraft && <div className="craft-status available">Ready to craft!</div>}
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default RecipeSelector;
