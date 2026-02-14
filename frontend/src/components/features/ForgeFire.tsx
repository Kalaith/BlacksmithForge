import React from 'react';

interface ForgeFireProps {
  forgeLit: boolean;
  onLightForge: () => void;
}

const ForgeFire: React.FC<ForgeFireProps> = ({ forgeLit, onLightForge }) => (
  <div className={`forge-fire${forgeLit ? ' lit' : ''}`} id="forge-fire" onClick={onLightForge}>
    <div className="fire-animation">{forgeLit ? '🔥🔥🔥' : '🔥'}</div>
    <p>{forgeLit ? 'Forge is burning hot!' : 'Click to light the forge!'}</p>
  </div>
);

export default ForgeFire;
