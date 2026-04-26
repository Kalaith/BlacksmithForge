import React from 'react';

interface ResourceStatProps {
  label: string;
  value: string | number;
  icon?: React.ReactNode;
}

const ResourceStat: React.FC<ResourceStatProps> = ({ label, value, icon }) => (
  <div className="resource-stat">
    {icon ? <span className="resource-stat__icon">{icon}</span> : null}
    <div>
      <div className="resource-stat__label">{label}</div>
      <div className="resource-stat__value">{value}</div>
    </div>
  </div>
);

export default ResourceStat;

