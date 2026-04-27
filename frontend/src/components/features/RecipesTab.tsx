import React, { useEffect, useState } from 'react';
import { useGameDataContext, useAuthContext } from '../../providers/GameDataProvider';
import { materialsAPI } from '../../api/api';
import RecipeCard from './RecipeCard';

interface RecipesTabProps {
  active: boolean;
}

const RecipesTab: React.FC<RecipesTabProps> = ({ active }) => {
  const { recipes } = useGameDataContext();
  const { user, isAuthenticated } = useAuthContext();
  const [userMaterials, setUserMaterials] = useState<Record<string, number>>({});
  const [craftMessage, setCraftMessage] = useState<string | null>(null);

  useEffect(() => {
    const loadUserMaterials = async () => {
      if (!isAuthenticated || !user?.id) return;
      const data = await materialsAPI.getUserMaterials(user.id);
      setUserMaterials(data);
    };
    loadUserMaterials();
  }, [isAuthenticated, user?.id]);

  if (!active) return null;

  return (
    <section id="recipes-tab" className="tab-content active">
      <div className="recipes-container">
        <div className="detail-heading">
          <span>📖</span>
          <div>
            <h2>Recipe Book</h2>
            <p>Study proven patterns before you commit your materials to the fire.</p>
          </div>
        </div>
        {craftMessage ? <div className="status status--info recipe-craft-message">{craftMessage}</div> : null}
        {recipes.length > 0 ? (
          <div className="recipe-book-grid">
            {recipes.map(recipe => (
              <RecipeCard
                key={recipe.name}
                recipe={recipe}
                userMaterials={userMaterials}
                onCraftIntent={recipeName => {
                  setCraftMessage(`${recipeName} is ready. Return to the Forge and pull the heat.`);
                  window.setTimeout(() => setCraftMessage(null), 2400);
                }}
              />
            ))}
          </div>
        ) : (
          <div className="empty-state">No recipes available.</div>
        )}
      </div>
    </section>
  );
};

export default RecipesTab;
