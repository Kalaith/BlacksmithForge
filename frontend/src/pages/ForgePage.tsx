import React from 'react';
import ForgeTab from '../components/features/ForgeTab';

interface ForgePageProps {
  onTabChange: (tab: string) => void;
}

const ForgePage: React.FC<ForgePageProps> = ({ onTabChange }) => {
  return (
    <main className="page-content">
      <ForgeTab active={true} onTabChange={onTabChange} />
    </main>
  );
};

export default ForgePage;
