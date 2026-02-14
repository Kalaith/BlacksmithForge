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
        <h2>📖 Recipe Book</h2>
        <div className="recipes-grid">
          {recipes.map(recipe => (
            <RecipeCard key={recipe.name} recipe={recipe} userMaterials={userMaterials} />
          ))}
        </div>
      </div>
    </section>
  );
};

export default RecipesTab;
