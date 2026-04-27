import React, { useEffect, useRef, useState } from 'react';

interface ResourceStatProps {
  label: string;
  value: string | number;
  icon?: React.ReactNode;
}

const ResourceStat: React.FC<ResourceStatProps> = ({ label, value, icon }) => {
  const previousValue = useRef(value);
  const [delta, setDelta] = useState<number | null>(null);

  useEffect(() => {
    const previousNumber = Number(previousValue.current);
    const nextNumber = Number(value);

    if (!Number.isNaN(previousNumber) && !Number.isNaN(nextNumber) && previousNumber !== nextNumber) {
      setDelta(nextNumber - previousNumber);
      const timeout = window.setTimeout(() => setDelta(null), 1200);
      previousValue.current = value;
      return () => window.clearTimeout(timeout);
    }

    previousValue.current = value;
    return undefined;
  }, [value]);

  return (
    <div className={`resource-stat${delta !== null ? ' has-delta' : ''}`}>
      {icon ? <span className="resource-stat__icon">{icon}</span> : null}
      <div>
        <div className="resource-stat__label">{label}</div>
        <div className="resource-stat__value">{value}</div>
      </div>
      {delta !== null ? (
        <span className={`resource-stat__delta${delta > 0 ? ' is-positive' : ' is-negative'}`}>
          {delta > 0 ? '+' : ''}
          {delta}
          {label === 'Gold' ? 'g' : ''}
        </span>
      ) : null}
    </div>
  );
};

export default ResourceStat;
