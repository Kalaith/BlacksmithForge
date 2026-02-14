import React from 'react';
import MaterialsTab from '../components/features/MaterialsTab';

const MaterialsPage: React.FC = () => {
  return (
    <main className="page-content">
      <MaterialsTab active={true} />
    </main>
  );
};

export default MaterialsPage;
