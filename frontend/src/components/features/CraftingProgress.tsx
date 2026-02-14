import React from 'react';
import HammerMiniGame from './HammerMiniGame';

interface CraftingProgressProps {
  hammerClicks: number;
  hammerAccuracy: number;
  progressPercentage: number;
  craftingStarted: boolean;
  onHammer: () => Promise<void>;
  maxClicks?: number;
}

const CraftingProgress: React.FC<CraftingProgressProps> = ({
  hammerClicks,
  hammerAccuracy,
  progressPercentage,
  craftingStarted,
  onHammer,
  maxClicks = 4,
}) => (
  <>
    <div className="crafting-progress">
      <div className="progress-info">
        <span>
          Hammer Hits: {hammerClicks} / {maxClicks}
        </span>
        <span>Accuracy: {hammerAccuracy}%</span>
      </div>
      <div className="progress-bar">
        <div className="progress-fill" style={{ width: `${progressPercentage}%` }} />
      </div>
    </div>

    <HammerMiniGame
      maxClicks={maxClicks}
      craftingStarted={craftingStarted}
      onHammerHit={onHammer}
      currentClicks={hammerClicks}
      currentAccuracy={hammerAccuracy}
    />
  </>
);

export default CraftingProgress;
