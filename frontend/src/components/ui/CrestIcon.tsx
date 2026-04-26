import React from 'react';

interface CrestIconProps {
  icon: React.ReactNode;
  label: string;
}

const CrestIcon: React.FC<CrestIconProps> = ({ icon, label }) => (
  <span className="crest-icon" aria-label={label}>
    {icon}
  </span>
);

export default CrestIcon;

