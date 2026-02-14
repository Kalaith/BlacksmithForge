import React from 'react';
import UpgradesTab from '../components/features/UpgradesTab';

const UpgradesPage: React.FC = () => {
  return (
    <main className="page-content">
      <UpgradesTab active={true} />
    </main>
  );
};

export default UpgradesPage;
