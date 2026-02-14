import React from 'react';
import ForgeTab from '../components/features/ForgeTab';

const ForgePage: React.FC = () => {
  return (
    <main className="page-content">
      <ForgeTab active={true} />
    </main>
  );
};

export default ForgePage;
