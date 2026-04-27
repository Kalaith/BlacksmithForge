import React from 'react';
import { Recipe } from '../../types';

interface RecipeCardProps {
  recipe: Recipe;
  userMaterials: Record<string, number>;
  onCraftIntent?: (recipeName: string) => void;
}

const RecipeCard: React.FC<RecipeCardProps> = ({ recipe, userMaterials, onCraftIntent }) => {
  const materialEntries = Object.entries(recipe.materials);
  const readyCount = materialEntries.filter(([mat, qty]) => (userMaterials[mat] ?? 0) >= qty).length;
  const isReady = readyCount === materialEntries.length;
  const readiness = materialEntries.length > 0 ? Math.round((readyCount / materialEntries.length) * 100) : 0;

  return (
  <article className={`recipe-book-card${isReady ? ' is-ready' : ''}`}>
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
    <progress
      className="recipe-book-card__readiness"
      value={readiness}
      max={100}
      aria-label={`${readiness}% material readiness`}
    />
    <div className="recipe-book-card__materials">
      {materialEntries.map(([mat, qty]: [string, number]) => {
        const owned = userMaterials[mat] ?? 0;
        const isInsufficient = owned < qty;
        const materialPercent = Math.min(100, Math.round((owned / qty) * 100));
        return (
          <div
            key={mat}
            className={`recipe-book-card__material${isInsufficient ? ' is-missing' : ''}`}
          >
            <span>
              {mat}
              <progress value={materialPercent} max={100} aria-label={`${materialPercent}% ready`} />
            </span>
            <strong>
              {owned}/{qty}
            </strong>
          </div>
        );
      })}
    </div>
    <button
      className="recipe-book-card__craft"
      disabled={!isReady}
      onClick={() => onCraftIntent?.(recipe.name)}
      type="button"
    >
      {isReady ? 'Craft at Forge' : 'Gather Materials'}
    </button>
  </article>
  );
};

export default RecipeCard;
