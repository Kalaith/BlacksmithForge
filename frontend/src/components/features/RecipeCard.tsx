import React from 'react';
import { Recipe } from '../../types';

interface RecipeCardProps {
  recipe: Recipe;
  userMaterials: Record<string, number>;
}

const RecipeCard: React.FC<RecipeCardProps> = ({ recipe, userMaterials }) => (
  <div className="recipe-card">
    <div className="recipe-name">
      {recipe.icon} {recipe.name}
    </div>
    <div className={`recipe-difficulty difficulty-${recipe.difficulty}`}>
      {`Difficulty: ${'★'.repeat(recipe.difficulty)}`}
    </div>
    <div className="recipe-profit">Sell Price: {recipe.sellPrice}g</div>
    <div className="required-materials">
      {Object.entries(recipe.materials).map(([mat, qty]: [string, number]) => (
        <div
          key={mat}
          className={`material-requirement${(userMaterials[mat] ?? 0) < qty ? ' insufficient' : ''}`}
        >
          {mat}: {qty} (Owned: {userMaterials[mat] ?? 0})
        </div>
      ))}
    </div>
  </div>
);

export default RecipeCard;
