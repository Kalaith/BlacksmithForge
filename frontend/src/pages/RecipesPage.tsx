import React from 'react';
import RecipesTab from '../components/features/RecipesTab';

const RecipesPage: React.FC = () => {
  return (
    <main className="page-content">
      <RecipesTab active={true} />
    </main>
  );
};

export default RecipesPage;
