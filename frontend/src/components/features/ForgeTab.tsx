import React from 'react';
import ForgeDashboard from '../dashboard/ForgeDashboard';

interface ForgeTabProps {
  active: boolean;
  onTabChange: (tab: string) => void;
}

const ForgeTab: React.FC<ForgeTabProps> = ({ active, onTabChange }) => {
  if (!active) return null;

  return <ForgeDashboard onTabChange={onTabChange} />;
};
export default ForgeTab;
