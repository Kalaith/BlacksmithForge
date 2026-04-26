import React from 'react';
import { Recipe } from '../../types';

interface RecipeCardProps {
  recipe: Recipe;
  userMaterials: Record<string, number>;
}

const RecipeCard: React.FC<RecipeCardProps> = ({ recipe, userMaterials }) => (
  <article className="recipe-book-card">
    <header className="recipe-book-card__header">
      <div className="recipe-book-card__icon">{recipe.icon}</div>
      <div>
        <h3>{recipe.name}</h3>
        <div className={`recipe-book-card__difficulty difficulty-${recipe.difficulty}`}>
          {'★'.repeat(recipe.difficulty)}
        </div>
      </div>
      <div className="recipe-book-card__price">
        <small>Sell Price</small>
        {recipe.sellPrice}g
      </div>
    </header>
    <div className="recipe-book-card__materials">
      {Object.entries(recipe.materials).map(([mat, qty]: [string, number]) => {
        const owned = userMaterials[mat] ?? 0;
        const isInsufficient = owned < qty;
        return (
          <div
            key={mat}
            className={`recipe-book-card__material${isInsufficient ? ' is-missing' : ''}`}
          >
            <span>{mat}</span>
            <strong>
              {owned}/{qty}
            </strong>
          </div>
        );
      })}
    </div>
  </article>
);

export default RecipeCard;
